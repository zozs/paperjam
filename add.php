<!DOCTYPE html>

<html ng-app="paperjamApp">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="paperjam.css" />
    <link rel="icon" href="images/favicon.png" />
    <title>Paperjam - add document</title>
    <!-- temp, until adapted this code to angular -->
    <script src="bower_components/jquery/dist/jquery.min.js"></script>
  </head>

  <body>
    <?php $navbarCurrent = basename(__FILE__); require('navbar.php'); ?>
    
    <div class="container">
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
    
    <!-- scripts and stuff -->
    <script src="bower_components/angular/angular.min.js"></script>
    <script src="bower_components/angular-bootstrap/ui-bootstrap-tpls.min.js"></script>
    <script src="paperjam.js"></script>
    <script src="add.js"></script>
  </body>
</html>
