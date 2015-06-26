/*
 * This file is distributed under the terms of the ISC License.
 * See the file LICENSE at https://github.com/zozs/paperjam
 */

var paperjamApp = angular.module('paperjamApp', ['ui.bootstrap']);

paperjamApp.controller('UnorganisedCtrl', function($scope, $http) {
  $http.get('unorganised').success(function(data) {
    $scope.unorganised = data.unorganised;
  });
});
