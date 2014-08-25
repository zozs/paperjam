<!DOCTYPE html>

<html>
  <head>
  	<meta charset="utf-8" />
    <title>Add document</title>
    <link rel="stylesheet" href="paperjam.css" />
    <script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script src="add.js"></script>
  </head>

  <body>
    <div id="content">
      <a href="index.html">
        <h1>PaperJam</h1>
      </a>
      <form enctype="multipart/form-data">
        <div class="upload-row">
          <input name="file[]" type="file" multiple="multiple" /><input type="button" value="-" disabled="disabled" />
        </div>
      </form>
      <input type="button" id="add-more-button" value="Add more" />
      <input id="upload-button" type="button" value="Upload" />
      <div id="upload-status">
        <progress></progress>
        <p>
        </p>
      </div>
      <a href="organise.html">
        <div class="status-complete" id="upload-status-complete">
          <h3>Upload complete!</h3>
          <p>
            Click this box to organise the uploaded files.
          </p>
        </div>
      </a>
    </div>
  </body>
</html>
