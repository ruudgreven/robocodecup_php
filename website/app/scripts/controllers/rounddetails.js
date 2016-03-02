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

    $scope.roundnumber = $routeParams.roundnumber;
    $scope.headertext = 'Showing round ' + $scope.roundnumber;
    $scope.filterapplied = false;

    $scope.scores = [];
    $scope.order = '-totalscore';

    $http({
      method : 'GET',
      url : config.api + '/round/' + $scope.roundnumber + '/battles.json'
    }).then(function mySucces(response) {
      response.data.response.forEach(function(pool) {

        //Walk through every battle
        pool.battles.forEach(function(battle) {
          battle.scores.forEach(function(score) {
            score.pool_id = pool.id;
            allscores.push(score);
          });
        });

        $scope.scores = filter.doFiltering(allscores);
      });
    });

    var applyFilter = function() {
      $scope.filterapplied = filter.hasFilterTeam();
      $scope.scores = filter.doFiltering(allscores);
    };

    $scope.removeFilterTeam = function() {
      filter.setFilterTeam('');
    };

    //Watch filterpool for changes
    $scope.$watch(function(){
        return filter.filterpools;
      }, function(current, old) {
        applyFilter();
      });

    //Watch filterteam for changes
    $scope.$watch(function(){
      return filter.filterteam;
    }, function(current, old) {
      applyFilter();
    });
  });
