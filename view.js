/*
 * This file is distributed under the terms of the ISC License.
 * See the file LICENSE at https://github.com/zozs/paperjam
 */

paperjamApp.controller('ViewDocumentCtrl', function($scope, $http, $modal, $routeParams, alerter, unorganised) {
  $scope.dbId = $routeParams.documentId;
  $scope.data = { document: null };

  if ($scope.dbId !== null) {
    $http.get($scope.documentUrl($scope.dbId)).success(function (data) {
      $scope.data.document = data.document;
    }).error(function () {
      alerter.addAlert('warning', 'No such document exists');
    });
  } else {
    alerter.addAlert('warning', 'You must provide a document id');
  }

  $scope.deleteDocument = function () {
    var modalInstance = $modal.open({
      animation: false,
      templateUrl: 'confirmDeleteDocument.html',
      controller: 'ConfirmDeleteDocumentCtrl'
    });

    modalInstance.result.then(function () {
      // OK! Delete everything.
      $http.delete($scope.documentUrl($scope.dbId))
        .success(function () {
          alerter.addAlert('success', 'Document sucessfully removed');
          $scope.data.document = null;
          unorganised.loadData();
        }).error(function (err) {
          if (err.errors) {
            alerter.addApiErrors(err.errors);
          }
        });
    }, function () {
      // Cancel.
    });
  };

  $scope.viewPage = function (page) {
    var modalInstance = $modal.open({
      animation: false,
      templateUrl: 'viewPage.html',
      controller: 'ViewPageModalCtrl',
      size: 'lg',
      resolve: {
        page: function () { return page; }
      }
    });

    modalInstance.result.then(function () {}, function () {});
  };
});

paperjamApp.controller('ConfirmDeleteDocumentCtrl', function ($scope, $modalInstance) {
  $scope.ok = function () {
    $modalInstance.close();
  };

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };
});

paperjamApp.controller('ViewPageModalCtrl', function ($scope, $modalInstance, $window, page) {
  $scope.page = page;
  console.log('got page:', page);

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

  $scope.openTab = function () {
    $window.open(page, '_blank');
    $modalInstance.dismiss('cancel');
  };
});
