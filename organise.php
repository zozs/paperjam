<!DOCTYPE html>

<html ng-app="paperjamApp">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="paperjam.css" />
    <link rel="icon" href="images/favicon.png" />
    <!-- Image picker -->
    <script src="bower_components/jquery/dist/jquery.min.js"></script>
    <link rel="stylesheet" href="bower_components/image-picker/image-picker/image-picker.css" />
    <script src="bower_components/image-picker/image-picker/image-picker.min.js"></script>
    <title>Paperjam - organise</title>
  </head>

  <body ng-controller="CommonCtrl">
    <?php $navbarCurrent = basename(__FILE__); require('navbar.php'); ?>

    <div class="container">
      <div ng-controller="OrganiseCtrl">
      <tabset>
        <tab heading="Select pages" active="organiseTabs.select">
          <p>Select the pages you want to staple together to form a document.</p>
          <div>
            <select multiple="multiple" class="image-picker">
              <option data-img-src="{{ fileUrl(page.file) }}" value="{{ $index }}" ng-repeat="page in unorganisedData.unorganised" data-img-label="<a class='image-picker-thumbnail-link' href='{{ fileUrl(page.file) }}' target='_blank'>{{ fileUrl(page.file) }}</a>" image-picker-repeat-done></option>
            </select>
          </div>
          <p>
            Proceed to the next tab to proceed with creating a document, or <button class="btn btn-danger" type="button" ng-click="deleteSelected()">Delete the selected pages</button>
          </p>
        </tab>
        <tab heading="Order" active="organiseTabs.order">
          <table class="table">
            <thead>
              <tr>
                <th>#</th>
                <th>Filename</th>
                <th>Reorder</th>
              </tr>
            </thead>
            <tbody>
              <tr ng-repeat="page in selectedPages">
                <td>{{ $index + 1 }}</td>
                <td><a href="{{ fileUrl(page.file) }}" target="_blank">{{ page.file }}</a></td>
                <td>
                  <div class="btn-group btn-group-xs" role="group">
                    <button type="button" class="btn btn-primary" ng-disabled="$first" ng-click="moveUp($index)"><span class="glyphicon glyphicon-arrow-up"></span></button>
                    <button type="button" class="btn btn-primary" ng-disabled="$last" ng-click="moveDown($index)"><span class="glyphicon glyphicon-arrow-down"></span></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </tab>
        <!--<tab heading="Sender, date, tags" disable="selectedPages.length == 0">-->
        <tab heading="Sender, date, tags" active="organiseTabs.sender">
          <form name="newDocumentForm">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="newEntrySender">Sender</label>
                  <input type="text" class="form-control" id="newEntrySender" ng-model="newEntryInfo.sender" typeahead="sender for sender in senders | filter:$viewValue | limitTo:8" required>
                </div>
              </div>
              <div class="col-md-6">
                <label>Related tags</label>
                <!-- TODO: return the amount of times a tag has been used for this sender,
                     order the tags by this number, and perhaps show the number as a
                     badge next to the tag? -->
                <p>
                  <span ng-repeat="tag in newEntryInfo.relatedTags">
                    <span class="label label-primary clickable-label" ng-click="addTag(tag)">{{ tag }}</span>
                  </span>
                </p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="newEntryDate">Date</label>
                  <p class="input-group">
                    <input type="text" class="form-control" datepicker-popup="yyyy-MM-dd" ng-model="datePicker.dt" is-open="datePicker.opened" datepicker-options="datePicker.options" ng-required="true" close-text="Close" id="newEntryDate" />
                    <span class="input-group-btn">
                      <button type="button" class="btn btn-default" ng-click="datePicker.open($event)"><i class="glyphicon glyphicon-calendar"></i></button>
                    </span>
                  </p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="newEntryTags">Tags</label>
                  <p class="input-group">
                    <input type="text" class="form-control" id="newEntryTags" ng-model="newEntryInfo.tag" typeahead="tag for tag in tags | filter:$viewValue | limitTo:8">
                    <span class="input-group-btn">
                      <button type="button" class="btn btn-default" ng-click="addTag(newEntryInfo.tag)"><i class="glyphicon glyphicon-plus"></i></button>
                    </span>
                  </p>
                  <p>
                    <span ng-repeat="tag in newEntryInfo.tags">
                      <span class="label label-primary clickable-label" ng-click="removeTag($index)">{{ tag }}</span>
                    </span>
                  </p>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <button type="button" class="btn btn-primary" ng-click="createDocument()">Create document</button>
              </div>
            </div>
          </form>
        </tab>
      </tabset>
      </div>
    </div>

    <!-- DIALOGS -->
    <script type="text/ng-template" id="confirmDeleteContent.html">
      <div class="modal-header">
        <h3 class="modal-title">Delete selected pages?</h3>
      </div>
      <div class="modal-body">
        The selected pages will be permanently deleted from both disk and database,
        and can not be recovered! Are you sure?
      </div>
      <div class="modal-footer">
        <button class="btn btn-danger" ng-click="ok()">Delete</button>
        <button class="btn btn-primary" ng-click="cancel()">Cancel</button>
      </div>
    </script>    

    <!-- scripts and stuff -->
    <script src="bower_components/angular/angular.min.js"></script>
    <script src="bower_components/angular-bootstrap/ui-bootstrap-tpls.min.js"></script>
    <script src="paperjam.js"></script>
    <script src="organise.js"></script>
  </body>
</html>
