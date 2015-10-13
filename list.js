/*
 * This file is distributed under the terms of the ISC License.
 * See the file LICENSE at https://github.com/zozs/paperjam
 */

paperjamApp.controller('ListDocumentsCtrl', function ($http) {
  var self = this;
  $http.get('api/documents').success(function (data) {
    self.documents = data.documents;
  });
});
