'use strict';

/**
 * @ngdoc function
 * @name robocodecupApp.controller:PoolCtrl
 * @description
 * # PoolCtrl
 * Controller of the robocodecupApp
 */
angular.module('robocodecupApp')
  .controller('PoolCtrl', function ($scope, $http, $log, config, filter) {
    $scope.enabled = true;
    $scope.pools = [];

    //Get the pools from the api
    $http({
      method : 'GET',
      url : config.api + '/pool.json'
    }).then(function mySucces(response) {
      var pools = response.data.response;

      //Walk through pools
      pools.forEach(function(pool) {
        $scope.pools.push({id: pool.id, name: pool.name, description: pool.description, selected: true, teams: pool.teams});
      });
      $scope.updateTeams();
    });

    $scope.updateTeams = function() {
      //Create filterpools
      var filterpools = [];

      //Walk through all pools
      $scope.pools.forEach(function(pool) {
        if (pool.selected) {
          filterpools.push(pool.id);
        }
      });

      //Set filter pools
      filter.setFilterPools(filterpools);
    };

    //Watch filterpool for changes
    $scope.$watch(function(){
      return filter.filteringenabled;
    }, function(current, old) {
      $log.log('Filtering changed to ' + current);
    });
  });
