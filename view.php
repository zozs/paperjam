<?php
if (!isset($_GET['id'])) {
  echo "No id given!";
  exit;
}

if (!preg_match('/^\d+$/', $_GET['id'])) {
  echo "Invalid id!";
  exit;
}
?>

<!DOCTYPE html>

<html>
  <head>
  	<meta charset="utf-8" />
    <title>View document</title>
    <link rel="stylesheet" href="paperjam.css" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script>
      function file_img(file) {
        return $('<img>', {
          src: file_img_url(file),
          alt: file
        });
      }

      function file_img_url(file) {
        return 'files/' + file;
      }
      var db_id = <?= $_GET['id']; ?>;
      $.ajax({
        url: 'documents/' + db_id,
        dataType: 'json',
        timeout: 5000
      }).done(function(data) {
        var d = data.document;
        $('#date').text(d.date);
        $('#sender').text(d.sender);
        var tag_list = $('#tags');
        $.each(d.tags, function(j, t) {
          tag_list.append($('<span>').addClass('tag').text(t));
        });
        
        $.each(d.pages, function(j, page) {
          var p = $('<div>').addClass('page')
            .append($('<a>', {
              href: file_img_url(page),
              target: '_blank'
            })
              .append(file_img(page)));
          p.appendTo($('#pages'));
        });
      });
    </script>
  </head>

  <body class="view-page">
    <div id="content">
      <a href="index.html">
        <h1>PaperJam</h1>
      </a>

      <p class="property">
        <span class="property-name">Date:</span>
        <span class="property-value" id="date"></span>
      </p>
      <p class="property">
        <span class="property-name">Sender:</span>
        <span class="property-value" id="sender"></span>
      </p>
      <p class="property">
        <div id="tags">
        </div>
      </p>

      <div id="pages">

      </div>
    </div>
  </body>
</html>

