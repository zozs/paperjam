<?php
/*
 * This file is distributed under the terms of the ISC License.
 * See the file LICENSE at https://github.com/zozs/paperjam
 */

require_once('db.php');
require 'vendor/autoload.php';

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

function response_not_found($app) {
  response_json($app, 404, []);
  $app->stop();
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
    public static $ROTATION = ['rotation', 'valid_rotation', 'Invalid rotation.'];
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

function valid_rotation($rotation) {
  return is_int($rotation) && $rotation >= 0 && $rotation < 360;
}

function valid_tags($tags) {
  return is_array($tags);
}

function escape_like($like) {
  /* escapes LIKE statements by converting % to \% and _ to \_. Thus assuming
   * that the escape char is the backslash. Also escapes the escape char. */
  $e = str_replace('\\', '\\\\', $like);
  $e = str_replace('%', '\\%', $e);
  $e = str_replace('_', '\\_', $e);
  return $e;
}

function json_sql_multiple($sql, $what) {
  return "SELECT ROW_TO_JSON(y) AS json FROM
            (SELECT ARRAY_TO_JSON(COALESCE(ARRAY_AGG(ROW_TO_JSON(x)), '{}'))
            AS $what FROM ($sql) x) y;";
}

function json_sql_multiple_array($sql, $what) {
  return "SELECT ROW_TO_JSON(x) AS json FROM (SELECT ARRAY_TO_JSON(ARRAY(
            $sql)) AS $what) x;";
}

function json_sql_multiple_documents($sql) {
  return json_sql_multiple($sql, 'documents');
}

function sql_document() {
  return "SELECT documents.id, received AS date, senders.name AS sender,
            ARRAY(SELECT tags.name FROM documents_tags
              JOIN tags ON documents_tags.tid=tags.id AND
              documents_tags.did=documents.id) AS tags,
            (SELECT ARRAY_AGG(p) FROM
              (SELECT pages.id, pages.file, pages.page_count FROM pages
              WHERE pages.document=documents.id ORDER BY page_order) p) AS pages
            FROM documents JOIN senders ON documents.sender=senders.id
            WHERE documents.id=? ORDER BY date DESC";
}

function sql_documents($where_clause = NULL) {
  return "SELECT documents.id, received AS date, senders.name AS sender,
            ARRAY(SELECT tags.name FROM documents_tags
              JOIN tags ON documents_tags.tid=tags.id AND
              documents_tags.did=documents.id) AS tags,
            (SELECT SUM(pt.page_count) FROM (SELECT page_count FROM pages
              WHERE pages.document=documents.id) pt) AS pages
            FROM documents JOIN senders ON documents.sender=senders.id " .
            (is_null($where_clause) ? "" : " WHERE $where_clause ") .
            "ORDER BY date DESC";
}

/* Path functions */
function original_path($filename) {
  global $PATH;
  return $PATH . "/" . $filename;
}

function large_path($filename) {
  global $PATH;
  return $PATH . "/large/" . $filename;
}

function thumbnail_path($filename) {
  global $PATH;
  return $PATH . "/thumbnails/" . $filename;
}

function filename_from_original($original, $page_count, $page_index, $ext) {
  // will drop path information.
  $info = pathinfo($original);
  if ($page_count > 1) {
    // multi-page, we must change e.g. file_2.pdf to file_2_index.jpg
    return $info['filename'] . "_" . $page_index . $ext;
  } else {
    // just change file extension from .whatever to .jpg
    return $info['filename'] . $ext;
  }
}

function large_filename_from_original($original, $page_count, $page_index) {
  return filename_from_original($original, $page_count, $page_index, ".jpg");
}

function thumbnail_filename_from_original($original, $page_count, $page_index) {
  return filename_from_original($original, $page_count, $page_index, ".png");
}

/* Quite ugly, probably should indent the functions following. */
$app->group('/api', function () use ($db, $app) {

/* URI Handlers */
/* GET request handlers. */

$app->get('/dates/:date/documents', function($date) use ($db, $app) {
  $sql = sql_documents("TO_CHAR(received, 'YYYY-MM-DD')=?");
  $sql = json_sql_multiple_documents($sql);
  $stmt = $db->prepare($sql);
  $stmt->execute([$date]);
  response_json_string($app, 200, $stmt->fetchColumn());
});

$app->get('/documents', function() use ($db, $app) {
  $sql = sql_documents();
  $sql = json_sql_multiple_documents($sql);
  $stmt = $db->query($sql);
  response_json_string($app, 200, $stmt->fetchColumn());
});

$app->get('/documents/:document_id', function($document_id) use ($db, $app) {
  $sql = "SELECT ROW_TO_JSON(y) AS json FROM
            (SELECT ROW_TO_JSON(x) AS document FROM
              (" . sql_document() . ") x) y;";
  $stmt = $db->prepare($sql);
  $stmt->execute([$document_id]);
  $document = $stmt->fetchColumn();
  if ($document == "") {
    response_not_found($app);
  }
  // Somewhat of an ugly hack, now parse the JSON here so we can add the
  // thumbnail filenames and urls, as well.
  $document_parsed = json_decode($document, true);
  $pages = [];
  foreach ($document_parsed['document']['pages'] as $page) {
    $page_count = $page['page_count'];
    for ($i = 0; $i < $page_count; $i++) {
      $thumbnail =
        thumbnail_filename_from_original($page['file'], $page_count, $i);
      $large = large_filename_from_original($page['file'], $page_count, $i);
      $url = $app->urlFor('rotate',
        ['page_id' => $page['id'], 'page_count_id' => $i]);
      $pages[] = ["original" => $page['file'], "thumbnail" => $thumbnail,
                  "large" => $large, "rotateUrl" => $url];
    }
  }
  $document_parsed['document']['pages'] = $pages;
  response_json($app, 200, $document_parsed);
});

$app->get('/unorganised', function() use ($db, $app) {
  $stmt = $db->query('SELECT id, file AS original, page_count FROM pages 
                      WHERE document IS NULL ORDER BY file;');
  $unorganised = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach ($unorganised as &$page) {
    $page_count = $page['page_count'];
    $page['large'] = [];
    $page['thumbnails'] = [];
    for ($i = 0; $i < $page_count; $i++) {
      $thumbnail =
        thumbnail_filename_from_original($page['original'], $page_count, $i);
      $large = large_filename_from_original($page['original'], $page_count, $i);
      $page['large'][] = $large;
      $page['thumbnails'][] = $thumbnail;
    }
  }
  response_json($app, 200, ['unorganised' => $unorganised]);
});

/* Global search which tries to find tags, dates, or senders matching the
   query. It does not search for documents which actually has the
   tag/sender/date etc. */
$app->get('/searchFor/:query', function($search_query) use ($db, $app) {
  $search_query = '%' . escape_like($search_query) . '%';
  $sql = "SELECT name, 'tag' AS type FROM tags WHERE name ILIKE ? UNION ALL
          SELECT name, 'sender' AS type FROM senders WHERE name ILIKE ? UNION ALL
          SELECT DISTINCT TO_CHAR(received, 'YYYY-MM-DD') AS name, 'date' AS type
              FROM documents WHERE TO_CHAR(received, 'YYYY-MM-DD') ILIKE ?
          ORDER BY name";
  $sql = json_sql_multiple($sql, 'matches');
  $stmt = $db->prepare($sql);
  $stmt->execute([$search_query, $search_query, $search_query]);
  response_json_string($app, 200, $stmt->fetchColumn());
});

$app->get('/search/:query', function($search_query) use ($db, $app) {
  /* Match either tag, sender, or date. */
  $sql = sql_documents(
    "ARRAY_LENGTH(ARRAY(SELECT tags.name FROM documents_tags
       JOIN tags ON documents_tags.tid=tags.id AND
       documents_tags.did=documents.id AND tags.name ILIKE ?), 1) > 0
     OR senders.name ILIKE ? OR TO_CHAR(received, 'YYYY-MM-DD') ILIKE ?");
  $sql = json_sql_multiple_documents($sql);
  $search_query = '%' . escape_like($search_query) . '%';
  $stmt = $db->prepare($sql);
  $stmt->execute([$search_query, $search_query, $search_query]);
  response_json_string($app, 200, $stmt->fetchColumn());
});

$app->get('/senders', function() use ($db, $app) {
  $sql = "SELECT name FROM senders ORDER BY name";
  $sql = json_sql_multiple_array($sql, 'senders');
  $stmt = $db->query($sql);
  response_json_string($app, 200, $stmt->fetchColumn());
});

$app->get('/senders/:sender/documents', function($sender) use ($db, $app) {
  $sql = sql_documents("senders.name=?");
  $sql = json_sql_multiple_documents($sql);
  $stmt = $db->prepare($sql);
  $stmt->execute([$sender]);
  response_json_string($app, 200, $stmt->fetchColumn());
});

$app->get('/senders/:sender/relatedtags', function($sender) use ($db, $app) {
  $sql = "SELECT tags.name, COUNT(tid) AS count FROM documents_tags
          JOIN tags ON documents_tags.tid=tags.id
          WHERE did IN (SELECT documents.id FROM documents JOIN senders ON
          senders.name=? AND senders.id=documents.sender) GROUP BY tags.name
          ORDER BY count DESC";
  $sql = json_sql_multiple($sql, 'related');
  $stmt = $db->prepare($sql);
  $stmt->execute([$sender]);
  response_json_string($app, 200, $stmt->fetchColumn());
});

$app->get('/tags', function() use ($db, $app) {
  $sql = "SELECT name FROM tags ORDER BY name";
  $sql = json_sql_multiple_array($sql, 'tags');
  $stmt = $db->query($sql);
  response_json_string($app, 200, $stmt->fetchColumn());
});

$app->get('/tags/:tag/documents', function($tag) use ($db, $app) {
  $sql = sql_documents("? IN (
              SELECT tags.name FROM documents_tags JOIN tags ON
              documents_tags.tid=tags.id AND documents_tags.did=documents.id)");
  $sql = json_sql_multiple_documents($sql);
  $stmt = $db->prepare($sql);
  $stmt->execute([$tag]);
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

function check_file_type($app, $filePath) {
  // Checks that we can handle the filetype, and return file extension to use.
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mimeType = $finfo->file($filePath);
  switch ($mimeType) {
    case "image/jpeg": return "jpg";
    case "image/png": return "png";
    case "application/pdf": return "pdf";
    default:
      response_validation_error($app,
        "Documents with MIME type $mimeType is not supported.");
  }
}

function generate_image($imagePath, $width, $height, $bestFit, $image_format,
                        callable $filename_func, callable $path_func,
                        $rotate, $page_count_id) {
  // Creates thumbnails for every page in $imagePath. Returns page count.
  // Returns FALSE if $page_count_id is larger than the page_count of the image.
  $imagick = new \Imagick();
  $imagick->setResolution(300, 300);
  $imagick->readImage(realpath($imagePath));
  $page_count = $imagick->getNumberImages();
  if ($page_count_id !== NULL && $page_count_id >= $page_count) {
    return FALSE;
  }

  if ($page_count === 1) {
    // Single page PDF or regular image. Dont append any suffix on thumbnail.
    $imagick->rotateImage(new \ImagickPixel(), $rotate);
    $imagick->setImageFormat($image_format);
    $imagick->thumbnailImage($width, $height, $bestFit);
    $thumbnail = $filename_func($imagePath, $page_count, 0);
    // flatten image to remove transparency.
    $im = $imagick->flattenImages();
    $im->writeImage($path_func($thumbnail));
    $im->clear();
    $imagick->clear();
  } else {
    // Probably a PDF. Iterate through all pages, unless $page_count_id is set.
    $imagick->clear();
    if ($page_count_id === NULL) {
      for ($i = 0; $i < $page_count; $i++) {
        $imagick = new \Imagick();
        $imagick->setResolution(300, 300);
        $imagick->readImage(realpath($imagePath) . "[" . $i . "]");
        $imagick->rotateImage(new \ImagickPixel(), $rotate);
        $imagick->setImageFormat($image_format);
        $imagick->thumbnailImage($width, $height, $bestFit);
        $thumbnail = $filename_func($imagePath, $page_count, $i);
        $im = $imagick->flattenImages();
        $im->writeImage($path_func($thumbnail));
        $im->clear();
        $imagick->clear();
      }
    } else {
      $imagick = new \Imagick();
      $imagick->setResolution(300, 300);
      $imagick->readImage(realpath($imagePath) . "[" . $page_count_id . "]");
      $imagick->rotateImage(new \ImagickPixel(), $rotate);
      $imagick->setImageFormat($image_format);
      $imagick->thumbnailImage($width, $height, $bestFit);
      $thumbnail = $filename_func($imagePath, $page_count, $page_count_id);
      $im = $imagick->flattenImages();
      $im->writeImage($path_func($thumbnail));
      $im->clear();
      $imagick->clear();
    }
  }
  return $page_count;
}

function large_image($imagePath, $rotate = 0, $page_count_id = NULL) {
  return generate_image($imagePath, 868, 0, false, 'jpg',
    'large_filename_from_original', 'large_path', $rotate, $page_count_id);
}

function thumbnail_image($imagePath, $rotate = 0, $page_count_id = NULL) {
  return generate_image($imagePath, 142, 200, true, 'png',
    'thumbnail_filename_from_original', 'thumbnail_path',
    $rotate, $page_count_id);
}

$app->post('/pages', function() use ($db, $app) {
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

  $i = 0;
  foreach ($files as $file) {
    // Find file type, ensure that we can/want to handle it, return extension.
    $extension = check_file_type($app, $file['tmp_name']);

    // Avoid getting a filename that is already used. Iterate until unique
    // filename is found.
    do {
      /* Move and rename file. */
      $seqno = sprintf('%03d', $i++);
      $dest_filename = $timestamp . '_' . $seqno . "." . strtolower($extension);
      $destination = original_path($dest_filename);
    } while (file_exists($destination));
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
      // Attack or weird failure.
      response_server_error($app, 'Could not move uploaded file!');
    }
    chmod($destination, 0644);

    // Now generate a thumbnail image/images.
    $page_count = thumbnail_image($destination);
    large_image($destination);

    /* Add page to database. */
    $sql = 'INSERT INTO pages (file, page_count) VALUES (?,?);';
    $stmt = $db->prepare($sql);
    $stmt->execute([$dest_filename, $page_count]);
  }

  /* Return data? */
  response_json($app, 200, []);
});


/* PUT handlers */

$app->put('/pages/:page_id/:page_count_id/rotation',
           function ($page_id, $page_count_id) use ($db, $app) {
  /*
   * Perform a rotation of the large and thumbnail images, but do not
   * alter the original file. Instead, to prevent loss of image quality,
   * create the (rotated) thumbnail/large image from the original file.
   */
  $data = json_decode($app->request->getBody(), TRUE);
  validate_params($app, $data, [Validators::$ROTATION]);

  $stmt = $db->prepare('SELECT id, file AS original FROM pages WHERE id=? AND 
                        ?<page_count;');
  $stmt->execute([$page_id, $page_count_id]);
  $page = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($page === FALSE) {
    response_not_found($app);
  }

  // Recreate the large/thumbnail images, but with correct rotation.
  $original_filename = $page['original'];
  $original_path = original_path($original_filename);
  thumbnail_image($original_path, $data['rotation'], $page_count_id);
  large_image($original_path, $data['rotation'], $page_count_id);
  response_json($app, 204, []);
})->name('rotate');


/* DELETE handlers */

$app->delete('/documents/:document_id', function($document_id) use ($db, $app) {
  /* This should remove the metadata about the document, but not remove the
   * files from disk. Instead they should be marked as unorganised again. */
  $db->beginTransaction();
  $sql1 = "UPDATE pages SET document=NULL, page_order=NULL WHERE document=?;";
  $stmt1 = $db->prepare($sql1);
  $stmt2 = $db->prepare("DELETE FROM documents_tags WHERE did=?;");
  $stmt3 = $db->prepare("DELETE FROM documents WHERE id=? ");

  $stmt1->execute([$document_id]);
  $stmt2->execute([$document_id]);
  $stmt3->execute([$document_id]);
  if ($stmt3->rowCount() != 1) {
    /* Nothing deleted, assume 404. */
    $db->rollBack();
    response_not_found($app);
  }
  $db->commit();
  response_json($app, 204, []); /* Done */
});

$app->delete('/pages/:page_id', function($page_id) use ($db, $app) {
  $db->beginTransaction();
  $stmt = $db->prepare("DELETE FROM pages WHERE id=? RETURNING file, page_count;");
  $stmt->execute([$page_id]);
  $deleted = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($deleted === FALSE) {
    $db->rollBack(); /* no such database id */
    response_not_found($app);
  }

  $filename = $deleted['file'];
  $page_count = $deleted['page_count'];

  // Delete thumbnails from disk.
  for ($i = 0; $i < $page_count; $i++) {
    $thumbnail = thumbnail_filename_from_original($filename, $page_count, $i);
    if (!unlink(thumbnail_path($thumbnail))) {
      $db->rollBack();
      response_server_error($app, 'Failed to delete thumbnail from disk!');
    }
  }

  // Delete large thumbnails from disk.
  for ($i = 0; $i < $page_count; $i++) {
    $thumbnail = large_filename_from_original($filename, $page_count, $i);
    if (!unlink(large_path($thumbnail))) {
      $db->rollBack();
      response_server_error($app, 'Failed to delete large thumbnail from disk!');
    }
  }

  // Delete original from disk.
  if (!unlink(original_path($filename))) {
    $db->rollBack();
    response_server_error($app, 'Failed to delete file from disk!');
  }

  $db->commit();
  response_json($app, 204, []); /* Alles gut. */
});

}); /* end of $app->group('/api', ...); */

$app->run();

?>
