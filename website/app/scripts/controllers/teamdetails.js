'use strict';

/**
 * @ngdoc function
 * @name robocodecupApp.controller:TeamdetailsCtrl
 * @description
 * # TeamdetailsCtrl
 * Controller of the robocodecupApp
 */
angular.module('robocodecupApp')
  .controller('TeamdetailsCtrl', function ($scope, $routeParams, $http, $log, config, filter) {
    var allbattles = [];

    $scope.loading = true;
    $scope.roundnumber = $routeParams.roundnumber;
    $scope.team = {};
    $scope.teamid = $routeParams.teamid;

    $scope.battles = [];
    $scope.order = 'rank';

    //Get team information
    $http({
      method : 'GET',
      url : config.api + '/team/' + $scope.teamid + '.json'
    }).then(function mySucces(response) {
      $scope.team = response.data.response[0];
    });

    //Get all battles for the team
    $http({
      method : 'GET',
      url : config.api + '/round/' + $scope.roundnumber + '/' + $scope.teamid + '/battles.json'
    }).then(function mySucces(response) {
      response.data.response.forEach(function(battle) {
        allbattles.push(battle);
      });
      $scope.battles = filter.doFiltering(allbattles);
      $scope.loading = false;
    });

    //Watch filterpool for changes
    $scope.$watch(function(){
      return filter.filterpools;
    }, function(current, old) {
      $scope.battles = filter.doFiltering(allbattles);
    });
  });
