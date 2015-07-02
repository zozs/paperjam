/*
 * This file is distributed under the terms of the ISC License.
 * See the file LICENSE at https://github.com/zozs/paperjam
 */

var paperjamApp = angular.module('paperjamApp', ['ui.bootstrap']);

paperjamApp.factory('unorganised', function ($http) {
  var unorganised = {};

  unorganised.data = {};
  unorganised.data.unorganised = [];

  unorganised.loadData = function () {
    $http.get('unorganised').success(function (data) {
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
});

paperjamApp.controller('NavbarCtrl', function ($scope, $window) {
  $scope.isCurrentPage = function (equalTo) {
    var filename = $window.location.pathname.split('/').pop();
    return filename === equalTo;
  };
});

paperjamApp.controller('UnorganisedCtrl', function ($scope, $http, unorganised) {
  $scope.unorganisedData = unorganised.data;
});

paperjamApp.controller('CommonCtrl', function ($scope) {
  $scope.documentUrl = function (documentId) {
    return 'documents/' + documentId;
  };

  $scope.fileUrl = function (filename) {
    return 'files/' + filename;
  };

  $scope.pageUrl = function (pageId) {
    return 'pages/' + pageId;
  };

  $scope.viewUrl = function (documentId) {
    return 'view.html#/?id=' + documentId;
  };
});
