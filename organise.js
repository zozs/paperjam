/*
 * This file is distributed under the terms of the ISC License.
 * See the file LICENSE at https://github.com/zozs/paperjam
 */

paperjamApp.controller('OrganiseCtrl', function ($scope, $http, $modal, $q, unorganised, alerter, viewPage, urls) {
  var organise = this;
  this.urls = urls;
  this.selectedPages = [];
  this.unorganisedData = unorganised.data;

  $http.get('api/senders').success(function (data) {
    organise.senders = data.senders;
  });

  $http.get('api/tags').success(function (data) {
    organise.tags = data.tags;
  });

  this.selectedPagesChanged = function (oldValues, newValues) {
    // We need to maintain the same array, not create a new one.
    organise.selectedPages.length = 0;
    for (var i = 0; i < newValues.length; i++) {
      organise.selectedPages.push(organise.unorganisedData.unorganised[newValues[i]]);
    }
  };
  
  this.selectedPageIndices = [];

  this.organiseTabs = {
    select: true,
    order: false,
    sender: false
  };

  this.datePicker = {
    open: function ($event) {
      $event.preventDefault();
      $event.stopPropagation();

      organise.datePicker.opened = true;
    },
    opened: false,
    options: {
      formatYear: 'yyyy',
      startingDay: 1
    },
    dt: new Date()
  };

  this.newEntryInfo = {
    sender: '',
    tags: [],
    tag: '',
    relatedTags: [],
    relatedTagsCanceler: null
  };

  $scope.$watch('vm.newEntryInfo.sender', function (value) {
    // When sender is changed, do request to find related tags.
    if (value === '') {
      return;
    }
    var encoded_sender = encodeURIComponent(value);
    if (organise.newEntryInfo.relatedTagsCanceler) {
      // Abort previous query.
      organise.newEntryInfo.relatedTagsCanceler.resolve();
    }

    organise.newEntryInfo.relatedTagsCanceler = $q.defer();
    $http.get('api/senders/' + encoded_sender + '/relatedtags',
      {timeout: organise.newEntryInfo.relatedTagsCanceler })
      .success(function (data) {
        organise.newEntryInfo.relatedTagsCanceler = null;
        organise.newEntryInfo.relatedTags.length = 0;
        for (var i = 0; i < data.related.length; i++) {
          organise.newEntryInfo.relatedTags.push(data.related[i]);
        }
      }).error(function (err) {
        console.log('got err from reltag: ', err);
      });
  });

  this.addTag = function (tag) {
    if (tag != '' && organise.newEntryInfo.tags.indexOf(tag) === -1) {
      organise.newEntryInfo.tags.push(tag);
      organise.newEntryInfo.tag = '';
    }
  };
  
  this.removeTag = function (index) {
    organise.newEntryInfo.tags.splice(index, 1);
  };

  this.createDocument = function () {
    alerter.clearAlerts();
    var pages = organise.selectedPages.map(function (p) { return p.id; });

    /* First validate the data. Perhaps this should be done in a more Angular way? */
    var validationFailed = false;
    if (organise.selectedPages.length == 0) {
      alerter.addAlert('warning', "You must select at least one page!");
      validationFailed = true;
    }
    if (organise.newEntryInfo.sender == '') {
      alerter.addAlert('warning', "You must supply a sender!");
      validationFailed = true;
    }
    if (organise.datePicker.dt === undefined) {
      alerter.addAlert('warning', "You must provide a valid date!");
      validationFailed = true;
    }
    if (validationFailed) {
      return;
    }

    $http.post('api/documents', {
      pages: pages,
      tags: organise.newEntryInfo.tags,
      sender: organise.newEntryInfo.sender,
      date: organise.datePicker.dt.toISOString().slice(0, 10)
    }).success(function (data) {
      // We should probably reset everything here.
      organise.selectedPages.length = 0;
      organise.selectedPageIndices.length = 0;
      unorganised.loadData();
      organise.newEntryInfo.sender = '';
      organise.newEntryInfo.tags.length = 0;
      organise.newEntryInfo.tag = '';
      organise.organiseTabs.select = true; // activate start tab again.
      organise.organiseTabs.order= false; // activate start tab again.
      organise.organiseTabs.sender = false; // activate start tab again.

      var new_url = organise.urls.viewUrl(data.id);
      alerter.addAlert('success', 'Document was successfully created.', new_url,
        'Click here to view.');
    }).error(function (err) {
      if (err.errors) {
        alerter.addApiErrors(err.errors);
      }
    });
  };

  this.deleteSelected = function () {
    // Show modal dialog.
    var modalInstance = $modal.open({
      animation: false,
      templateUrl: 'confirmDeleteContent.html',
      controller: 'ConfirmDeleteInstanceCtrl',
      controllerAs: 'vm',
      size: 'sm'
    });

    modalInstance.result.then(function () {
      // OK! Delete everything.
      organise.doDeletePages(organise.selectedPages);
    }, function () {
      // Cancel.
    });
  };

  this.doDeletePages = function (pages) {
    var requests = pages.map(function (p) {
      return $http.delete(organise.urls.pageUrl(p.id));
    });

    $q.all(requests).then(function () {
      // succeeded, reload:
      organise.selectedPageIndices.length = 0;
      unorganised.loadData();
    }, function (reason) {
      // something failed.
      alert('Failed to delete at least one page. See console');
      console.log('Failed to delete, reject response was:', reason);
      unorganised.loadData();
    });
  }

  this.moveDown = function (index) {
    if (index == organise.selectedPages.length - 1) return; // sanity check.
    organise.selectedPages.splice(index + 1, 0, organise.selectedPages.splice(index, 1)[0]);
  };

  this.moveUp = function (index) {
    if (index == 0) return; // sanity check.
    organise.selectedPages.splice(index - 1, 0, organise.selectedPages.splice(index, 1)[0]);
  };

  this.selectPage = function (index) {
    var exists = organise.selectedPageIndices.indexOf(index);
    if (exists == -1) {
      // the page is not selected, make it selected.
      organise.selectedPageIndices.push(index);
    } else {
      // remove index from list of selected.
      organise.selectedPageIndices.splice(exists, 1);
    }
    organise.selectedPagesChanged(null, organise.selectedPageIndices);
  };

  this.viewPage = viewPage.viewPage;
});

paperjamApp.controller('ConfirmDeleteInstanceCtrl', function ($modalInstance) {
  this.ok = function () {
    $modalInstance.close();
  };

  this.cancel = function () {
    $modalInstance.dismiss('cancel');
  };
});

