/*
 * This file is distributed under the terms of the ISC License.
 * See the file LICENSE at https://github.com/zozs/paperjam
 */

paperjamApp.controller('FindDocumentsCtrl', function ($scope, $http, $q, $location, alerter) {
  $scope.filterBy = function (val) {
    var eval = encodeURIComponent(val);
    return $http.get('api/searchFor/' + eval).then(function (response) {
      /* Always allow the 'search' option. */
      response.data.matches.unshift({
        name: val,
        type: 'search'
      });
      return response.data.matches;
    });
  };

  $scope.filterByCanceler = null;
  $scope.searchResults = [];

  $scope.routeUpdateCallback = function () {
    // Check that the search params are given. If not, clear search results.
    if (!($location.search().hasOwnProperty('name') && $location.search().hasOwnProperty('type'))) {
      $scope.searchResults = [];
      $scope.filterBySelected = '';
      return;
    }

    $scope.filterBySelected = $location.search().name;

    if ($scope.filterByCanceler) {
      // Abort previous query.
      $scope.filterByCanceler.resolve();
    }

    $scope.filterByCanceler = $q.defer();
    $scope.searchResults = [];

    // Use different urls depending on the type of filter we have.
    var encodedName = encodeURIComponent($location.search().name);
    var url = '';
    switch ($location.search().type) {
      case 'sender': url = 'api/senders/' + encodedName + '/documents'; break;
      case 'tag': url = 'api/tags/' + encodedName + '/documents'; break;
      case 'date': url = 'api/dates/' + encodedName + '/documents'; break;
      case 'search': url = 'api/search/' + encodedName; break;
    }

    $http.get(url, { timeout: $scope.filterByCanceler })
      .success(function (data) {
        $scope.filterByCanceler = null;
        $scope.searchResults.length = 0;
        for (var i = 0; i < data.documents.length; i++) {
          $scope.searchResults.push(data.documents[i]);
        }
      }).error(function (err) {
        alerter.addAlert('danger', 'Unknown failure when searching!');
        console.log('Unknown failure when searching!:', err);
      });
  };

  $scope.$on('$routeUpdate', $scope.routeUpdateCallback);
  $scope.routeUpdateCallback();

  $scope.$watch('filterBySelected', function (value) {
    // When the user has selected a filter option, do a search based on it.
    if (value !== undefined && value.hasOwnProperty('name')
        && value.hasOwnProperty('type')) {
      // Only run the code if we selected a valid option.
      $location.search('name', value.name);
      $location.search('type', value.type);
    }
  });
});
