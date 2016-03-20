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
    $scope.allpool= {};

    //Get the pools from the api
    $http({
      method : 'GET',
      url : config.api + '/pool.json'
    }).then(function mySucces(response) {
      var pools = response.data.response;

      //Walk through pools
      pools.forEach(function(pool) {
        if (pool.id == 'ALL') {
          $scope.allpool = {id: pool.id, name: pool.name, description: pool.description, selected: true, teams: pool.teams};
        } else {
          $scope.pools.push({id: pool.id, name: pool.name, description: pool.description, selected: true, teams: pool.teams});
        }
      });
      $scope.updateTeams();
    });

    $scope.toggleAllTeams = function(toggle) {
      $scope.pools.forEach(function(pool) {
          pool.selected = toggle;
      });
      $scope.updateTeams();
    };

    $scope.updateTeams = function() {
      //Create filterpools
      var filterpools = [];

      if ($scope.allpool.selected) {
        filterpools.push($scope.allpool.id);
      }

      //Walk through all pools
      $scope.pools.forEach(function(pool) {
        if (pool.selected) {
          filterpools.push(pool.id);
        } else {
          $scope.allpool.selected = false;
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
