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

<html ng-app="paperjamApp">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="paperjam.css" />
    <link rel="icon" href="images/favicon.png" />
    <title>Paperjam - view document</title>
  </head>

  <body ng-controller="ViewDocumentCtrl">
    <?php $navbarCurrent = basename(__FILE__); require('navbar.php'); ?>
    
    <div class="container">
      <div class="row">
        <div class="col-md-4">
          <h3>Date</h3>
          <p>{{ document.date }}</p>
        </div>
        <div class="col-md-4">
          <h3>Sender</h3>
          <p>{{ document.sender }}</p>
        </div>
        <div class="col-md-4">
          <h3>Tags</h3>
          <span class="tag" ng-repeat="tag in document.tags">{{ tag }}</span>
        </div>
      </div>
      <div class="row">
        <h3>Pages</h3>
        <div id="pages">
          <div class="page" ng-repeat="page in document.pages">
            <a href="files/{{ page }}" target="_blank">
              <img src="files/{{ page }}" alt="{{ page }}">
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- scripts and stuff -->
    <script src="bower_components/angular/angular.min.js"></script>
    <script src="bower_components/angular-bootstrap/ui-bootstrap-tpls.min.js"></script>
    <script src="paperjam.js"></script>
    <script>
      var db_id = <?= $_GET['id']; ?>;
      paperjamApp.controller('ViewDocumentCtrl', function($scope, $http) {
        $http.get('documents/' + db_id).success(function(data) {
          $scope.document = data.document;
        });
      });
    </script>
  </body>
</html>
