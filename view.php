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

  <body ng-controller="CommonCtrl">
    <?php $navbarCurrent = basename(__FILE__); require('navbar.php'); ?>
    
    <div class="container" ng-controller="ViewDocumentCtrl">
      <div class="row">
        <div class="col-md-4">
          <label>Date</label>
          <p>{{ document.date }}</p>
        </div>
        <div class="col-md-4">
          <label>Sender</label>
          <p>{{ document.sender }}</p>
        </div>
        <div class="col-md-4">
          <label>Tags</label>
          <p>
            <span ng-repeat="tag in document.tags">
              <span class="label label-primary">{{ tag }}</span>
            </span>
          </p>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <label>Pages</label>
          <div id="pages">
            <div class="page" ng-repeat="page in document.pages">
              <a href="{{ fileUrl(page) }}" target="_blank">
                <img src="{{ fileUrl(page) }}" alt="{{ page }}">
              </a>
            </div>
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
