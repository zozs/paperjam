/*
 * This file is distributed under the terms of the ISC License.
 * See the file LICENSE at https://github.com/zozs/paperjam
 */

var paperjamApp = angular.module('paperjamApp', ['ngRoute', 'ui.bootstrap']);

paperjamApp.config(function ($routeProvider, $locationProvider) {
  $routeProvider
    .when('/list', {
      templateUrl: 'list.html',
      controller: 'ListDocumentsCtrl'
    })
    .when('/find', {
      templateUrl: 'find.html',
      controller: 'FindDocumentsCtrl',
      reloadOnSearch: false
    })
    .when('/add', {
      templateUrl: 'add.html',
      controller: 'AddPageCtrl'
    })
    .when('/organise', {
      templateUrl: 'organise.html',
      controller: 'OrganiseCtrl'
    })
    .when('/view/:documentId', {
      templateUrl: 'view.html',
      controller: 'ViewDocumentCtrl'
    });
  $locationProvider.html5Mode(true);
});

paperjamApp.factory('unorganised', function ($http) {
  var unorganised = {};

  unorganised.data = {};
  unorganised.data.unorganised = [];

  unorganised.loadData = function () {
    $http.get('api/unorganised').success(function (data) {
      unorganised.data.unorganised = data.unorganised;
    });
  };
  unorganised.loadData();

  return unorganised;
});

paperjamApp.factory('alerter', function () {
  var alerter = {};

  alerter.alerts = [];
  alerter.addAlert = function (type, msg) {
    alerter.alerts.push({ type: type, msg: msg });
  };

  alerter.addApiErrors = function (errors) {
    for (var i = 0; i < errors.length; i++) {
      alerter.addAlert('danger', errors[i]);
    }
  };

  alerter.clearAlerts = function () {
    alerter.alerts.length = 0;
  };

  return alerter;
});

paperjamApp.controller('AlertCtrl', function ($scope, alerter) {
  $scope.alerts = alerter.alerts;

  $scope.closeAlert = function (index) {
    $scope.alerts.splice(index, 1);
  };
  
  $scope.$on('$routeChangeSuccess', function () {
    // Clear all alerts when changing to a new page.
    alerter.clearAlerts();
  });
});

paperjamApp.controller('NavbarCtrl', function ($scope, $location) {
  $scope.isCurrentPage = function (viewLocation) { 
    return $location.path().indexOf(viewLocation) == 0;
  };
});

paperjamApp.controller('UnorganisedCtrl', function ($scope, $http, unorganised) {
  $scope.unorganisedData = unorganised.data;
});

paperjamApp.controller('CommonCtrl', function ($scope) {
  $scope.documentUrl = function (documentId) {
    return 'api/documents/' + documentId;
  };

  $scope.fileUrl = function (filename) {
    return 'files/' + filename;
  };

  $scope.pageUrl = function (pageId) {
    return 'api/pages/' + pageId;
  };

  $scope.viewUrl = function (documentId) {
    return 'view/' + documentId;
  };
});
