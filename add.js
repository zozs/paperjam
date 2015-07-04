/*
 * This file is distributed under the terms of the ISC License.
 * See the file LICENSE at https://github.com/zozs/paperjam
 */

paperjamApp.controller('AddPageCtrl', function($scope, $modal, alerter, unorganised) {
  $scope.uploadActive = false;
  $scope.uploadState = null; /* or 'success' */
  $scope.totalSize = 100;
  $scope.currentUploaded = 0;

  $scope.addFileField = function () {
    var file_row = $('<div/>').addClass('row top10');
    var file_control = $('<input/>', {
      type: 'file',
      name: 'file[]',
      'multiple': 'multiple'
    }).addClass('pull-left');
    var file_remove  = $('<button/>', {
      type: 'button',
      click: function() {
        // Deletes this file form the form.
        file_control.remove();
        $(this).remove();
        file_row.remove();
      }
    }).addClass('btn btn-primary btn-xs').append('<span class="glyphicon glyphicon-minus"></span>');
    file_row.append(file_remove).append(file_control);
    $('form').append(file_row);
  };

  $scope.upload = function () {
    var formData = new FormData($('form')[0]);
    /* If we want individual progress for each file, we'll have to fire
       one AJAX request per file, let's do that sometime in the future. */
    $scope.uploadActive = true;
    $.ajax({
      url: 'api/pages',
      type: 'POST',
      xhr: function() {
        var myXhr = $.ajaxSettings.xhr();
        if (myXhr.upload) {
          myXhr.upload.addEventListener('progress', $scope.uploadProgress, false);
        }
        return myXhr;
      },
      success: $scope.uploadComplete,
      error: $scope.uploadFailed,
      data: formData,
      cache: false,
      contentType: false,
      processData: false
    });
  };

  $scope.uploadComplete = function () {
    $scope.uploadActive = false;
    $scope.uploadState = 'success';
    alerter.addAlert('success', 'Upload completed successfully');
    unorganised.loadData();
  }

  $scope.uploadFailed = function (e, textStatus) {
    $scope.uploadActive = false;
    $scope.uploadState = 'danger';
    alerter.addAlert('danger', 'Upload failed with message: ' + textStatus);
  }

  $scope.uploadProgress = function (e) {
    if (e.lengthComputable) {
      $scope.totalSize = e.total;
      $scope.currentUploaded = e.loaded;
    }
  }
});

