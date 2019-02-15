/*
 * This file is distributed under the terms of the ISC License.
 * See the file LICENSE at https://github.com/zozs/paperjam
 */

paperjamApp.controller('ViewDocumentCtrl', function($http, $modal, $routeParams, alerter, unorganised, viewPage, urls) {
  var view = this;
  this.urls = urls;
  this.dbId = $routeParams.documentId;
  this.data = { document: null };

  if (this.dbId !== null) {
    $http.get(urls.documentUrl(view.dbId)).success(function (data) {
      view.data.document = data.document;
    }).error(function () {
      alerter.addAlert('warning', 'No such document exists');
    });
  } else {
    alerter.addAlert('warning', 'You must provide a document id');
  }

  this.deleteDocument = function () {
    var modalInstance = $modal.open({
      animation: false,
      templateUrl: 'confirmDeleteDocument.html',
      controller: 'ConfirmDeleteDocumentCtrl',
      controllerAs: 'vm'
    });

    modalInstance.result.then(function () {
      // OK! Delete everything.
      $http.delete(urls.documentUrl(view.dbId))
        .success(function () {
          alerter.addAlert('success', 'Document sucessfully removed');
          view.data.document = null;
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

  this.viewPage = viewPage.viewPage;
});

paperjamApp.controller('ConfirmDeleteDocumentCtrl', function ($modalInstance) {
  this.ok = function () {
    $modalInstance.close();
  };

  this.cancel = function () {
    $modalInstance.dismiss('cancel');
  };
});

