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
    <!-- AngularJS's support for file uploads is limited, we must use JQuery. -->
    <script src="bower_components/jquery/dist/jquery.min.js"></script>
  </head>

  <body>
    <?php $navbarCurrent = basename(__FILE__); require('navbar.php'); ?>
    
    <div class="container" ng-controller="AddPageCtrl">
      <form enctype="multipart/form-data">
        <div class="row top10">
          <button type="button" class="btn btn-primary btn-xs" disabled="disabled"><span class="glyphicon glyphicon-minus"></span></button><input class="pull-left" name="file[]" type="file" multiple="multiple" />
        </div>
      </form>
      <div class="row top30">
        <button type="button" class="btn btn-primary" ng-click="addFileField()">Add more</button>
        <button type="button" class="btn btn-primary" ng-click="upload()">Upload</button>
      </div>
      <div class="row top30">
        <progressbar max="totalSize" value="currentUploaded" ng-class="{'progress-striped': true, 'active': uploadActive}" type="{{uploadState}}"></progressbar>
      </div>
    </div>
    
    <!-- scripts and stuff -->
    <script src="bower_components/angular/angular.min.js"></script>
    <script src="bower_components/angular-bootstrap/ui-bootstrap-tpls.min.js"></script>
    <script src="paperjam.js"></script>
    <script src="add.js"></script>
  </body>
</html>
