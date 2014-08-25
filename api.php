<?php
/*
 * This file is distributed under the terms of the ISC License.
 * See the file LICENSE at https://github.com/zozs/paperjam
 */

require_once('db.php');

require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

function response_created($app, $name, $idname, $id) {
  $created = [$idname => $id];
  //$app->response->headers->set('Location', $app->urlFor($name, $created));
  response_json($app, 201, $created);
}

function response_json($app, $status, $data) {
  response_json_string($app, $status, json_encode($data));
}

function response_json_string($app, $status, $jsonString) {
  $app->response->setStatus($status);
  $app->response->headers->set('Content-Type', 'application/json');
  $app->response->setBody($jsonString);
}

function response_server_error($app, $message) {
  $error = ['errors' => [$message]];
  response_json($app, 500, $error);
  $app->stop();
}

function response_validation_error($app, $message) {
  $error = ['errors' => [$message]];
  response_json($app, 400, $error);
  $app->stop();
}

$app = new \Slim\Slim(['debug' => true]);

/* Global error handling */

$app->error(function (\PDOException $e) use ($app) {
    response_server_error($app, 'Internal server error');
});

/* Matrix is [ ['post_field', validator_function, 'error_message'], ... ] */
function validate_params($app, $data, $matrix) {
  foreach ($matrix as list($post, $validator, $error)) {
    if (!isset($data[$post]) || !$validator($data[$post])) {
      response_validation_error($app, $error);
    }
  }
}

class Validators {
    public static $DATE =
      ['date', 'valid_date', 'Invalid date! Use YYYY-MM-DD.'];
    public static $PAGES = ['pages', 'valid_pages', 'Invalid pages!'];
    public static $SENDER = ['sender', 'valid_non_null', 'Invalid sender.'];
    public static $TAGS = ['tags', 'valid_tags', 'Invalid tags.'];
}

function valid_date($date) {
 $date_matches = [];
  if (preg_match("/^(\d{4})-(0[1-9]|1[0-2])-([0-2][0-9]|3[0-1])$/",
                 $date, $date_matches) === 1) {
    // Looks ok so far, but check that the date is actually ok.
    list(, $year, $month, $day) = $date_matches;
    if (checkdate($month, $day, $year)) {
      return TRUE;
    }
  }
  return FALSE;
}

function valid_non_null($x) {
  return $x !== NULL;
}

function valid_pages($pages) {
  return is_array($pages) && count($pages) > 0;
}

function valid_tags($tags) {
  return is_array($tags);
}

/* URI Handlers */
/* GET request handlers. */

$app->get('/documents', function() use ($db, $app) {
  $sql = "SELECT ROW_TO_JSON(y) AS json FROM
            (SELECT ARRAY_TO_JSON(ARRAY_AGG(ROW_TO_JSON(x))) AS documents FROM
              (SELECT documents.id, received AS date, senders.name AS sender,
              ARRAY(SELECT tags.name FROM documents_tags
                JOIN tags ON documents_tags.tid=tags.id AND
                documents_tags.did=documents.id) AS tags,
              ARRAY(SELECT pages.file FROM pages
                WHERE pages.document=documents.id ORDER BY page_order) AS pages
              FROM documents JOIN senders ON documents.sender=senders.id
              ORDER BY date DESC) x) y;";
  $stmt = $db->query($sql);
  response_json_string($app, 200, $stmt->fetchColumn());
});

$app->get('/documents/:document_id', function($document_id) use ($db, $app) {
  $sql = "SELECT ROW_TO_JSON(y) AS json FROM
            (SELECT ROW_TO_JSON(x) AS document FROM
              (SELECT documents.id, received AS date, senders.name AS sender,
              ARRAY(SELECT tags.name FROM documents_tags
                JOIN tags ON documents_tags.tid=tags.id AND
                documents_tags.did=documents.id) AS tags,
              ARRAY(SELECT pages.file FROM pages
                WHERE pages.document=documents.id ORDER BY page_order) AS pages
              FROM documents JOIN senders ON documents.sender=senders.id AND
              documents.id=?) x) y;";
  $stmt = $db->prepare($sql);
  $stmt->execute([$document_id]);
  response_json_string($app, 200, $stmt->fetchColumn());
});

$app->get('/unorganised', function() use ($db, $app) {
  $stmt = $db->query('SELECT * FROM unorganised_pages;');
  response_json($app, 200, ['unorganised' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
});

$app->get('/senders', function() use ($db, $app) {
  $sql = "SELECT ROW_TO_JSON(x) AS json FROM (SELECT ARRAY_TO_JSON(ARRAY(
            SELECT name FROM senders)) AS senders) x;";
  $stmt = $db->query($sql);
  response_json_string($app, 200, $stmt->fetchColumn());
});

$app->get('/tags', function() use ($db, $app) {
  $sql = "SELECT ROW_TO_JSON(x) AS json FROM (SELECT ARRAY_TO_JSON(ARRAY(
            SELECT name FROM tags)) AS tags) x;";
  $stmt = $db->query($sql);
  response_json_string($app, 200, $stmt->fetchColumn());
});


/* POST request handlers and helpers. */
function insert_or_select($app, $db, $dbname, $value) {
  $sql = "WITH new_row AS (
          INSERT INTO $dbname (name) SELECT ? WHERE NOT EXISTS
          (SELECT id FROM $dbname WHERE lower(name)=lower(?)) RETURNING id)
          SELECT id FROM new_row UNION
          SELECT id FROM $dbname WHERE lower(name)=lower(?);";
  $stmt = $db->prepare($sql);
  $stmt->execute([$value, $value, $value]);
  return $stmt->fetchColumn();
}

function insert_sender($app, $db, $sender) {
  return insert_or_select($app, $db, 'senders', $sender);
}

function insert_tag($app, $db, $tag) {
  return insert_or_select($app, $db, 'tags', $tag);
}

$app->post('/documents', function() use ($db, $app) {
  try {
    $data = json_decode($app->request->getBody(), TRUE);

    validate_params($app, $data, [Validators::$DATE, Validators::$SENDER,
      Validators::$PAGES, Validators::$TAGS]);
    $db->beginTransaction();
    
    /* First insert necessary senders and tags. Also filter duplicate tags. */
    $sender_id = insert_sender($app, $db, $data['sender']);
    $data['tags'] = array_unique($data['tags']);
    $tags_ids = array_map(function($t) use ($db, $app) {
      return insert_tag($app, $db, $t);
    }, $data['tags']);

    /* Next create the document. */
    $sql = "INSERT INTO documents (received, sender) VALUES (?,?) RETURNING id;";
    $stmt = $db->prepare($sql);
    $stmt->execute([$data['date'], $sender_id]);
    $document_id = $stmt->fetchColumn();

    /* Now associate tags with the document. */
    $stmt = $db->prepare("INSERT INTO documents_tags (did,tid) VALUES (?,?);");
    foreach ($tags_ids as $tag_id) {
      $stmt->execute([$document_id, $tag_id]);
    }

    /* Now associate each page with the created document. */
    $stmt = $db->prepare("UPDATE pages SET document=?,page_order=?
                          WHERE id=? AND document IS NULL;");
    foreach ($data['pages'] as $page_order => $page_id) {
      $stmt->execute([$document_id, $page_order, $page_id]);
      if ($stmt->rowCount() != 1) {
        response_validation_error($app, 'Tried to reassign page!');
      }
    }

    $db->commit();
    response_created($app, 'document', 'id', $document_id);
  } catch (PDOException $e) {
    $db->rollBack();
    response_validation_error($app, 'Failed to insert document!' . $e->getMessage());
  }
});

$app->post('/pages', function() use ($db, $app, $PATH) {
  if (count($_FILES) == 0) {
    response_validation_error($app, 'No files given');
  }

  /*
   * First walk-through all elements of the $_FILES array, including array-
   * type form fields. Convert everything to a flattened list of files.
   */
  $files = [];
  foreach ($_FILES as $form_field => $field_data) {
    if (is_array($field_data) && count($field_data) > 0) {
      // Check if this is an array of multiple files with the same form name.
      $first = reset($field_data);
      if (is_array($first)) {
        /* Multiple files with same form name. */
        $objs = [];
        foreach ($field_data as $propname => $props) {
          foreach ($props as $fileindex => $value) {
            $objs[$fileindex][$propname] = $value;
          }
        }

        $files = array_merge($files, $objs);
      } else {
        /* Just a single file. */
        $files[] = $field_data;
      }
    } else {
      response_validation_error($app, 'Invalid file input posted.');
    }
  }

  if (count($files) == 0) {
    response_validation_error($app, 'No file uploaded.');
  }

  /* Check for error codes from PHP. */
  foreach ($files as $file) {
    if ($file['error'] != UPLOAD_ERR_OK) {
      response_validation_error($app, 'An upload failed!');
    }
  }

  /* Now, sort the $files array by (original) file name. */
  usort($files, function($a, $b) {
    return strcmp($a['name'], $b['name']);
  });

  /* Now do the interesting stuff, i.e. renaming files, add to db, etc. */
  $timestamp = gmdate('Ymd\THis');

  foreach ($files as $i => $file) {
    /* Move and rename file. */
    $seqno = sprintf('%03d', $i);
    /*
     * Should probably validate the file extension and contents for security
     * reasons. Let's assume the user is nice (yeah, right...)
     */
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $dest_filename = $timestamp . '_' . $seqno . "." . strtolower($extension);
    $destination = $PATH . '/' . $dest_filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
      // Attack or weird failure.
      response_server_error($app, 'Could not move uploaded file!');
    }
    chmod($destination, 0644);

    /* Add page to database. */
    $sql = 'INSERT INTO pages (file) VALUES (?);';
    $stmt = $db->prepare($sql);
    $stmt->execute([$dest_filename]);
  }

  /* Return data? */
  response_json($app, 200, []);
});

/* PUT handlers */
$app->put('/unseen/:unseen_id', function($unseen_id) use ($db, $app) {
  validate_params($app, [Validators::$AVAILABLE, Validators::$COMMENT]);
  $sql = "UPDATE unseen SET comment=?, available=? WHERE id=?;";
  execute_single_nonselect($app, $db, $sql, [$app->request->params('comment'),
    $app->request->params('available'), $unseen_id]);
});

$app->put('/watched/:watched_id', function($watched_id) use ($db, $app) {
  validate_params($app, [Validators::$DATE, Validators::$RATING]);
  $sql = "UPDATE watched SET date_watched=?, rating=? WHERE id=?;";
  execute_single_nonselect($app, $db, $sql, [$app->request->params('date'),
    $app->request->params('rating'), $watched_id]);
});

/* DELETE handlers */

$app->delete('/pages/:page_id', function($page_id) use ($db, $app, $PATH) {
  $db->beginTransaction();
  $stmt = $db->prepare("DELETE FROM pages WHERE id=? RETURNING file;");
  $stmt->execute([$page_id]);
  $filename = $stmt->fetchColumn();
  if ($filename === FALSE) {
    $db->rollBack();
    response_json($app, 404, []); /* No such database id. */
  }
  if (!unlink($PATH . '/' . $filename)) {
    $db->rollBack();
    response_server_error($app, 'Failed to delete file from disk!');
  }
  $db->commit();
  response_json($app, 204, []); /* Alles gut. */
});

$app->run();

?>
