'use strict';

/**
 * @ngdoc function
 * @name robocodecupApp.controller:PoolCtrl
 * @description
 * # PoolCtrl
 * Controller of the robocodecupApp
 */
angular.module('robocodecupApp')
  .controller('PoolCtrl', function ($scope, $http, $log) {
    var pools;
    $scope.pools = [];
    $scope.teams = [];

    //Get the pools from the api
    $http({
      method : 'GET',
      url : 'http://localhost/robocodecupapi/api/pool.json'
    }).then(function mySucces(response) {
      pools = response.data.response;

      //Walk through pools
      pools.forEach(function(pool) {
        $scope.pools.push({name: pool.name, description: pool.description, selected: true, teams: pool.teams});
      });
      $scope.updateTeams();
    });


    $scope.updateTeams = function() {
      //Walk through all pools and build team list
      $scope.teams = [];
      $scope.pools.forEach(function(pool) {
        if (pool.selected) {
          pool.teams.forEach(function(team) {
            $scope.teams.push(team);
          });
        }
      });
    };
  });
