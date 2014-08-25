<?php
// do stuff with $_FILES

/*
 * Iterate over all POSTed files, and save them to the real storage.
 * Name them according to the following pattern:
 *   YYYYMMDDTHHMMSS_NNN.ext
 * where the ISO 8601 timestamp is the current UTC time on the server, and NNN
 * is a serial number for the current uploading, starting at 000.
 * The files are first sorted according to their original (client-side) filename
 * before they are assigned their sequential serial number.
 */

function sort_by_name_cmp($a, $b) {
	return strcmp($a['name'], $b['name']);
}

function sort_by_name($files) {
	$objs = [];
	foreach ($files as $propname => $props) {
		foreach ($props as $fileindex => $value) {
			$objs[$fileindex][$propname] = $value;
		}
	}
	uasort($objs, 'sort_by_name_cmp');
  return $objs;
}

function fail_message($message) {
  http_response_code(400);
  echo $message;
  exit;
}

print_r($_FILES);
exit();

require_once '../db.php';

$timestamp = gmdate('Ymd\THis');

if (count($_FILES['file']) == 0) {
	// Something failed.
  fail_message('Upload failed!');
} 

foreach ($_FILES['file']['error'] as $key => $value) {
	if ($value != UPLOAD_ERR_OK) {
		// Something failed.
    fail_message('An upload failed!');
	}
}

/**/
$file_count = count($_FILES['file']['name']);
$files = sort_by_name($_FILES['file']);

for ($i = 0; $i < $file_count; $i++) {
	/* Move and rename file. */
	$seqno = sprintf('%03d', $i);
	/* Should probably validate the file extension and contents for security
	   reasons. Let's assume the user is nice (yeah, right...) */
	$extension = pathinfo($files[$i]['name'], PATHINFO_EXTENSION);
  $dest_filename = $timestamp . '_' . $seqno . "." . strtolower($extension);
	$destination = $PATH . '/' . $dest_filename;
	if (!move_uploaded_file($files[$i]['tmp_name'], $destination)) {
		// Attack or weird failure.
    fail_message('Could not move uploaded file!');
	}
	chmod($destination, 0644);

  /* Add page to database. */
  $sql = 'INSERT INTO pages (file) VALUES (?);';
  $stmt = $db->prepare($sql);
  $stmt->execute([$dest_filename]);
}

?>
