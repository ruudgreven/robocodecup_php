'use strict';

/**
 * @ngdoc function
 * @name robocodecupApp.controller:RoundCtrl
 * @description
 * # RoundCtrl
 * Controller of the robocodecupApp
 */
angular.module('robocodecupApp')
  .controller('RoundCtrl', function ($scope, $http, $location, $log, config) {
    $scope.tabs = [];
    $scope.rounds = [];
    $scope.selectedIndex = -1;
    $scope.selectedRound = undefined;

    //Add home tab
    $scope.tabs.push({title: 'Home / Totals'});

    //Get the rounds from the api
    $http({
      method : 'GET',
      url : config.api + '/round.json'
    }).then(function mySucces(response) {
      $scope.rounds = response.data.response.rounds;

      var currentRound = response.data.response.current;

      var counter = 0;
      $scope.rounds.forEach(function(round) {
        $scope.tabs.push({title: 'Round ' + round.number});

        //If there is a round given in the url make that the default
        if (round.number == currentRound) {
          $scope.selectedIndex = counter + 1;
        }
        counter++;
      });

      if ($scope.selectedIndex == -1) {
        $scope.selectedIndex = 0;
      }
    });

    $scope.$watch('selectedIndex', function(current, old) {
      if (current !== -1) {
        if (current === 0) {
          $location.url('home');
        } else {
          $scope.selectedRound = $scope.rounds[current - 1];
          $location.url('round/' + $scope.selectedRound.number);
        }

      }
    });

  });
