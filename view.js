paperjamApp.controller('ViewDocumentCtrl', function($scope, $http, $modal, $location, alerter, unorganised) {
  $scope.locationChangeSuccessHandler = function () {
    $scope.dbId = null;

    if ($location.search().hasOwnProperty('id')) {
      $scope.dbId = $location.search().id;
    }

    $scope.document = null;

    if ($scope.dbId !== null) {
      $http.get($scope.documentUrl($scope.dbId)).success(function (data) {
        $scope.document = data.document;
      }).error(function () {
        alerter.addAlert('warning', 'No such document exists');
      });
    } else {
      alerter.addAlert('warning', 'You must provide a document id');
    }
  };

  $scope.$on('$locationChangeSuccess', $scope.locationChangeSuccessHandler);

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
          unorganised.loadData();
          $scope.visible = false;
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
