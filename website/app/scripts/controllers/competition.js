'use strict';

/**
 * @ngdoc function
 * @name robocodecupApp.controller:CompetitionCtrl
 * @description
 * # CompetitionCtrl
 * Controller of the robocodecupApp
 */
angular.module('robocodecupApp')
  .controller('CompetitionCtrl', function ($scope, $http, config) {
    $scope.competition = '';

    //Get the competition
    $http({
      method : 'GET',
      url : config.api + '/competition.json'
    }).then(function mySucces(response) {
      $scope.competition = response.data.response[0];
    });
  });
