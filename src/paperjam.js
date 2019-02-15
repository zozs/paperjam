/*
 * This file is distributed under the terms of the ISC License.
 * See the file LICENSE at https://github.com/zozs/paperjam
 */

var paperjamApp = angular.module('paperjamApp', ['ngRoute', 'ngSanitize', 'ui.bootstrap']);

paperjamApp.config(function ($routeProvider, $locationProvider) {
  $routeProvider
    .when('/list', {
      templateUrl: 'list.html',
      controller: 'ListDocumentsCtrl',
      controllerAs: 'vm'
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
  alerter.addAlert = function (type, msg, link, linkText) {
    alerter.alerts.push({ type: type, msg: msg, link: link,
      linkText: linkText });
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

  urls.rotateUrl = function (pageId, pageCountId) {
    return urls.pageUrl(pageId) + '/' + pageCountId + '/rotation';
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

  viewPage.viewPage = function (pages, index) {
    var modalInstance = $modal.open({
      animation: false,
      templateUrl: 'viewPage.html',
      controller: 'ViewPageModalCtrl',
      controllerAs: 'vm',
      size: 'lg',
      resolve: {
        pages: function () { return pages; },
        index: function () { return index; }
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

paperjamApp.controller('UnorganisedCtrl', function ($scope, unorganised) {
  $scope.unorganisedData = unorganised.data;
});

/* this should be deleted when every controller has been converted to controllerAs */
paperjamApp.controller('CommonCtrl', function ($scope) {
  $scope.viewUrl = function (documentId) {
    return 'view/' + documentId;
  };
});

paperjamApp.filter('degreeRotate', function() {
  return function (input) {
    if (input === undefined) {
      input = 0;
    }
    switch (input) {
      case 0:   return "No rotation";
      case 180: return "180°";
      case 270: return "90° counter-clockwise";
      case 90:  return "90° clockwise";
      default:  return input + "°";
    }
  };
})

paperjamApp.controller('ViewPageModalCtrl', function ($modalInstance, $window, $http, pages, index, urls){
  var self = this;
  this.pages = pages;
  this.currentPage = index + 1;
  this.urls = urls;

  this.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

  this.openTab = function () {
    $window.open(urls.fileUrl(self.pages[self.currentPage - 1].original), '_blank');
    $modalInstance.dismiss('cancel');
  };

  this.rotate = function (degrees) {
    self.pages[self.currentPage - 1].rotateLoading = true;
    $http.put(self.pages[self.currentPage - 1].rotateUrl, {'rotation': degrees})
      .then(function () {
        // We must also force a reload of the image. Do this by appending a
        // random query string at the end of the image paths.
        var addDecacheFunc = function (current) {
          if (current === undefined) return undefined;
          if (current.indexOf('?') !== -1) {
            current = current.substring(0, current.indexOf('?'));
          }
          current = current + '?decache=' + new Date().getTime();
          return current;
        };
        self.pages[self.currentPage - 1].large =
          addDecacheFunc(self.pages[self.currentPage - 1].large);
        self.pages[self.currentPage - 1].thumbnail =
          addDecacheFunc(self.pages[self.currentPage - 1].thumbnail);

        self.pages[self.currentPage - 1].rotateLoading = false;
      }, function () {
        self.pages[self.currentPage - 1].rotateLoading = false;
        console.log('Failed to rotate image!');
      });
  };
});
