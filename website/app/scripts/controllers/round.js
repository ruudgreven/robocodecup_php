'use strict';

/**
 * @ngdoc function
 * @name robocodecupApp.controller:RoundCtrl
 * @description
 * # RoundCtrl
 * Controller of the robocodecupApp
 */
angular.module('robocodecupApp')
  .controller('RoundCtrl', function ($scope, $http, $location, $log) {
    $scope.tabs = [];
    $scope.rounds = [];
    $scope.selectedIndex = -1;
    $scope.selectedRound = undefined;

    //Get the rounds from the api
    $http({
      method : 'GET',
      url : 'http://localhost/robocodecupapi/api/round.json'
    }).then(function mySucces(response) {
      $scope.rounds = response.data.response[0].rounds;

      var currentRound = response.data.response[0].current;

      var counter = 0;
      $scope.rounds.forEach(function(round) {
        $scope.tabs.push({title: 'Round ' + round.number});

        //If there is a round given in the url make that the default
        if (round.number == currentRound) {
          $scope.selectedIndex = counter;
        }
        counter++;
      });
    });

    $scope.$watch('selectedIndex', function(current, old) {
      if (current !== -1) {
        $scope.selectedRound = $scope.rounds[current];
        $location.url('round/' + $scope.selectedRound.number);
      }
    });

  });
