'use strict';

/**
 * @ngdoc function
 * @name robocodecupApp.controller:RounddetailsCtrl
 * @description
 * # RounddetailsCtrl
 * Controller of the robocodecupApp
 */
angular.module('robocodecupApp')
  .controller('RounddetailsCtrl', function ($scope, $routeParams, $http, $log, config, filter) {
    var allscores = [];
    var lastknownfilter;

    $scope.loading = true;
    $scope.roundnumber = $routeParams.roundnumber;

    $scope.scores = [];
    $scope.order = 'rank';

    $http({
      method : 'GET',
      url : config.api + '/round/' + $scope.roundnumber + '/ranking.json'
    }).then(function mySucces(response) {
      response.data.response.forEach(function(score) {
        allscores.push(score);
      });

      $scope.scores = filter.doFiltering(allscores);
      $scope.loading = false;
    });

    var applyFilter = function() {
      $scope.scores = filter.doFiltering(allscores);
    };

    //Watch filterpool for changes
    $scope.$watch(function(){
        return filter.filterpools;
      }, function(current, old) {
          applyFilter();
      });
  });
