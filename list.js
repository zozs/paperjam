/*
 * This file is distributed under the terms of the ISC License.
 * See the file LICENSE at https://github.com/zozs/paperjam
 */

paperjamApp.controller('ListDocumentsCtrl', function ($scope, $http) {
  $http.get('api/documents').success(function(data) {
    $scope.documents = data.documents;
  });
});
