'use strict';

/**
 * @ngdoc function
 * @name robocodecupApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the robocodecupApp
 */
angular.module('robocodecupApp')
  .controller('MainCtrl', function ($scope, $http, $log, filter, config) {
    $scope.competition = {};
    $scope.showlivestream = config.showlivestream;
    $scope.loading = true;
    $scope.roundnumber = 0;
    $scope.scores = [];
    $scope.featuredmessages = [];

    //Get the competition
    $http({
      method : 'GET',
      url : config.api + '/competition.json'
    }).then(function mySucces(response) {
      $scope.competition = response.data.response[0];
    });

    //Get featured messages
    $http({
      method : 'GET',
      url : config.api + '/messages/featured.json'
    }).then(function mySucces(response) {
      $scope.messages = response.data.response;
    });

    //Get the ranking for the current round
    $http({
      method : 'GET',
      url : config.api + '/round.json'
    }).then(function mySucces(response) {
      var round = 0;

      if (response.data.response.current == -1) {
        if (response.data.response.previous != -1) {
          round = response.data.response.previous;
        }
      } else {
        round = response.data.response.current;
      }

      $scope.roundnumber = round;

      $http({
        method : 'GET',
        url : config.api + '/round/' + round + '/ranking.json'
      }).then(function mySucces(response) {
        var counter = 1;
        response.data.response.forEach(function(score) {
          score.rank = counter;
          $scope.scores.push(score);
          counter++;
        });
        $scope.loading = false;
      });
    });

  });
