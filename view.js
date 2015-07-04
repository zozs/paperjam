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
});

paperjamApp.controller('ConfirmDeleteDocumentCtrl', function ($scope, $modalInstance) {
  $scope.ok = function () {
    $modalInstance.close();
  };

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };
});
