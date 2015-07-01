<!DOCTYPE html>

<html ng-app="paperjamApp">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="paperjam.css" />
    <link rel="icon" href="images/favicon.png" />
    <title>Paperjam - find documents</title>
  </head>

  <body ng-controller="CommonCtrl">
    <?php $navbarCurrent = basename(__FILE__); require('navbar.php'); ?>

    <div class="container" ng-controller="FindDocumentsCtrl">
      <div class="row">
        <div class="col-md-12">
          <label for="filterBy">Search or filter</label>
          <input type="text" id="filterBy" ng-model="filterBySelected" typeahead="match as match.name for match in filterBy($viewValue)" typeahead-template-url="filterByTemplate.html" typeahead-loading="loadingFilterBy" class="form-control">
        </div>
      </div>

      <div class="row top10">
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
              <tr ng-repeat="document in searchResults">
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

    <!-- templates -->
    <script type="text/ng-template" id="filterByTemplate.html">
      <a>
        <span bind-html-unsafe="match.label | typeaheadHighlight:query"></span>
        <small><em><span class="match-type pull-right">&nbsp;&nbsp;&nbsp;<span bind-html-unsafe="match.model.type"></span></span></em></small>
      </a>
    </script>

    <!-- scripts and stuff -->
    <script src="bower_components/angular/angular.min.js"></script>
    <script src="bower_components/angular-bootstrap/ui-bootstrap-tpls.min.js"></script>
    <script src="paperjam.js"></script>
    <script src="find.js"></script>
  </body>
</html>
