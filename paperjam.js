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
      controller: 'OrganiseCtrl',
      controllerAs: 'vm'
    })
    .when('/view/:documentId', {
      templateUrl: 'view.html',
      controller: 'ViewDocumentCtrl',
      controllerAs: 'vm'
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

paperjamApp.factory('urls', function () {
  var urls = {};

  urls.documentUrl = function (documentId) {
    return 'api/documents/' + documentId;
  };

  urls.fileUrl = function (filename) {
    return 'files/' + filename;
  };

  urls.largeUrl = function (filename) {
    return 'files/large/' + filename;
  };

  urls.pageUrl = function (pageId) {
    return 'api/pages/' + pageId;
  };

  urls.thumbnailUrl = function (filename) {
    return 'files/thumbnails/' + filename;
  };

  urls.viewUrl = function (documentId) {
    return 'view/' + documentId;
  };

  return urls;
});

paperjamApp.factory('viewPage', function ($modal) {
  var viewPage = {};

  viewPage.viewPage = function (large, original) {
    var modalInstance = $modal.open({
      animation: false,
      templateUrl: 'viewPage.html',
      controller: 'ViewPageModalCtrl',
      controllerAs: 'vm',
      size: 'lg',
      resolve: {
        page: function () { return {large: large, original: original}; }
      }
    });

    modalInstance.result.then(function () {}, function () {});
  };

  return viewPage;
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

/* this should be deleted when every controller has been converted to controllerAs */
paperjamApp.controller('CommonCtrl', function ($scope) {
  $scope.viewUrl = function (documentId) {
    return 'view/' + documentId;
  };
});

paperjamApp.controller('ViewPageModalCtrl', function ($modalInstance, $window, page, urls) {
  this.page = page;
  this.urls = urls;

  this.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

  this.openTab = function () {
    $window.open(urls.fileUrl(page.original), '_blank');
    $modalInstance.dismiss('cancel');
  };
});
