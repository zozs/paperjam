<!DOCTYPE html>

<html ng-app="paperjamApp">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="paperjam.css" />
    <link rel="icon" href="images/favicon.png" />
    <title>Paperjam - list documents</title>
  </head>

  <body ng-controller="CommonCtrl">
    <?php $navbarCurrent = basename(__FILE__); require('navbar.php'); ?>

    <div class="container" ng-controller="ListDocumentsCtrl">
      <div class="row">
        <div class="col-md-12">
          <table class="table table-condensed table-hover">
            <thead>
              <tr>
                <th></th>
                <th>Date</th>
                <th>Sender</th>
                <th>Pages</th>
                <th>Tags</th>
            </thead>
            <tbody ng-cloak>
              <tr ng-repeat="document in documents">
                <td>
                  <a href="{{ viewUrl(document.id) }}">
                    <span class="glyphicon glyphicon-file" aria-hidden="true"></span>
                  </a>
                </td>
                <td>
                  <a href="{{ viewUrl(document.id) }}">
                    {{ document.date }}
                  </a>
                </td>
                <td>
                  <a href="{{ viewUrl(document.id) }}">
                    {{ document.sender }}
                  </a>
                </td>
                <td>{{ document.pages.length }}</td>
                <td>
                  <span ng-repeat="tag in document.tags">
                    <span class="label label-primary" >{{ tag }}</span>
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- scripts and stuff -->
    <script src="bower_components/angular/angular.min.js"></script>
    <script src="bower_components/angular-bootstrap/ui-bootstrap-tpls.min.js"></script>
    <script src="paperjam.js"></script>
    <script src="list.js"></script>
  </body>
</html>
