'use strict';

/**
 * @ngdoc overview
 * @name robocodecupApp
 * @description
 * # robocodecupApp
 *
 * Main module of the application.
 */
angular
  .module('robocodecupApp', [
    'ngAnimate',
    'ngAria',
    'ngCookies',
    'ngMessages',
    'ngResource',
    'ngRoute',
    'ngSanitize',
    'ngMaterial'
  ])
  .config(function ($routeProvider) {
    $routeProvider
      .when('/', {
        templateUrl: 'views/main.html',
        controller: 'MainCtrl',
        controllerAs: 'main'
      })
      .when('/round/:roundnumber', {
        templateUrl: 'views/rounddetails.html',
        controller: 'RounddetailsCtrl',
        controllerAs: 'rounddetails'
      })
      .otherwise({
        redirectTo: '/'
      });
  });
