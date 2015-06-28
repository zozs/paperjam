paperjamApp.controller('ViewDocumentCtrl', function($scope, $http, $modal, alerter, unorganised) {
  $http.get($scope.documentUrl(db_id)).success(function (data) {
    $scope.document = data.document;
  }).error(function () {
    $scope.visible = false;
    alerter.addAlert('warning', 'No such document exists');
  });

  $scope.visible = true;

  $scope.deleteDocument = function () {
    var modalInstance = $modal.open({
      animation: false,
      templateUrl: 'confirmDeleteDocument.html',
      controller: 'ConfirmDeleteDocumentCtrl'
    });

    modalInstance.result.then(function () {
      // OK! Delete everything.
      $http.delete($scope.documentUrl(db_id))
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
