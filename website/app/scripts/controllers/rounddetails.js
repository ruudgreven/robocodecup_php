'use strict';

/**
 * @ngdoc function
 * @name robocodecupApp.controller:RounddetailsCtrl
 * @description
 * # RounddetailsCtrl
 * Controller of the robocodecupApp
 */
angular.module('robocodecupApp')
  .controller('RounddetailsCtrl', function ($scope, $routeParams) {
    $scope.roundnumber = $routeParams.roundnumber;
  });
