paperjamApp.controller('OrganiseCtrl', function ($scope, $http, $modal, $q, unorganised, alerter) {
  $scope.selectedPages = [];
  $scope.unorganisedData = unorganised.data;

  $http.get('senders').success(function (data) {
    $scope.senders = data.senders;
  });

  $http.get('tags').success(function (data) {
    $scope.tags = data.tags;
  });

  $scope.selectedPagesChanged = function (oldValues, newValues) {
    // We need to maintain the same array, not create a new one.
    $scope.selectedPages.length = 0;
    for (var i = 0; i < newValues.length; i++) {
      $scope.selectedPages.push($scope.unorganisedData.unorganised[newValues[i]]);
    }
  };

  $scope.organiseTabs = {
    select: true,
    order: false,
    sender: false
  };

  $scope.datePicker = {
    open: function ($event) {
      $event.preventDefault();
      $event.stopPropagation();

      $scope.datePicker.opened = true;
    },
    opened: false,
    options: {
      formatYear: 'yyyy',
      startingDay: 1
    },
    dt: new Date()
  };

  $scope.newEntryInfo = {
    sender: '',
    tags: [],
    tag: '',
    relatedTags: [],
    relatedTagsCanceler: null
  };

  $scope.$watch('newEntryInfo.sender', function (value) {
    // When sender is changed, do request to find related tags.
    if (value === '') {
      return;
    }
    var encoded_sender = encodeURIComponent(value);
    if ($scope.newEntryInfo.relatedTagsCanceler) {
      // Abort previous query.
      $scope.newEntryInfo.relatedTagsCanceler.resolve();
    }

    $scope.newEntryInfo.relatedTagsCanceler = $q.defer();
    $http.get('senders/' + encoded_sender + '/relatedtags',
      {timeout: $scope.newEntryInfo.relatedTagsCanceler })
      .success(function (data) {
        $scope.newEntryInfo.relatedTagsCanceler = null;
        $scope.newEntryInfo.relatedTags.length = 0;
        for (var i = 0; i < data.related.length; i++) {
          $scope.newEntryInfo.relatedTags.push(data.related[i]);
        }
      }).error(function (err) {
        console.log('got err from reltag: ', err);
      });
  });

  $scope.addTag = function (tag) {
    if (tag != '' && $scope.newEntryInfo.tags.indexOf(tag) === -1) {
      $scope.newEntryInfo.tags.push(tag);
      $scope.newEntryInfo.tag = '';
    }
  };
  
  $scope.removeTag = function (index) {
    $scope.newEntryInfo.tags.splice(index, 1);
  };

  $scope.createDocument = function () {
    alerter.clearAlerts();
    var pages = $scope.selectedPages.map(function (p) { return p.id; });

    /* First validate the data. Perhaps this should be done in a more Angular way? */
    var validationFailed = false;
    if ($scope.selectedPages.length == 0) {
      alerter.addAlert('warning', "You must select at least one page!");
      validationFailed = true;
    }
    if ($scope.newEntryInfo.sender == '') {
      alerter.addAlert('warning', "You must supply a sender!");
      validationFailed = true;
    }
    if ($scope.datePicker.dt === undefined) {
      alerter.addAlert('warning', "You must provide a valid date!");
      validationFailed = true;
    }
    if (validationFailed) {
      return;
    }

    $http.post('documents', {
      pages: pages,
      tags: $scope.newEntryInfo.tags,
      sender: $scope.newEntryInfo.sender,
      date: $scope.datePicker.dt.toISOString().slice(0, 10)
    }).success(function () {
      // We should probably reset everything here.
      $scope.selectedPages.length = 0;
      unorganised.loadData();
      $scope.newEntryInfo.sender = '';
      $scope.newEntryInfo.tags.length = 0;
      $scope.newEntryInfo.tag = '';
      $scope.organiseTabs.select = true; // activate start tab again.

      alerter.addAlert('success', 'Document was successfully created');
    }).error(function (err) {
      if (err.errors) {
        for (var i = 0; i < err.errors.length; i++) {
          alerter.addAlert('danger', err.errors[i]);
        }
      }
    });
  };

  $scope.deleteSelected = function () {
    // Show modal dialog.
    var modalInstance = $modal.open({
      animation: false,
      templateUrl: 'confirmDeleteContent.html',
      controller: 'ConfirmDeleteInstanceCtrl',
      size: 'sm'
    });

    modalInstance.result.then(function () {
      // OK! Delete everything.
      $scope.doDeletePages($scope.selectedPages);
    }, function () {
      // Cancel.
    });
  };

  $scope.doDeletePages = function (pages) {
    var requests = pages.map(function (p) {
      return $http.delete($scope.pageUrl(p.id));
    });

    $q.all(requests).then(function () {
      // succeeded, reload:
      unorganised.loadData();
    }, function (reason) {
      // something failed.
      alert('Failed to delete at least one page. See console');
      console.log('Failed to delete, reject response was:', reason);
      unorganised.loadData();
    });
  }

  $scope.moveDown = function (index) {
    if (index == $scope.selectedPages.length - 1) return; // sanity check.
    $scope.selectedPages.splice(index + 1, 0, $scope.selectedPages.splice(index, 1)[0]);
  };

  $scope.moveUp = function (index) {
    if (index == 0) return; // sanity check.
    $scope.selectedPages.splice(index - 1, 0, $scope.selectedPages.splice(index, 1)[0]);
  };
});

paperjamApp.controller('ConfirmDeleteInstanceCtrl', function ($scope, $modalInstance) {
  $scope.ok = function () {
    $modalInstance.close();
  };

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };
});

paperjamApp.directive('imagePickerRepeatDone', function ($timeout) {
  return function (scope, element, attrs) {
    if (scope.$last) {
      $timeout(function () {
        $('.image-picker').imagepicker({
          show_label: true,
          changed: scope.selectedPagesChanged
        });
      }, 0);
    }
  };
});

