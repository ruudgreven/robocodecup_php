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
    'ngMaterial',
    'md.data.table'
  ])
  .config(function($mdThemingProvider) {
    $mdThemingProvider.theme('default')
      .primaryPalette('orange')
      .accentPalette('pink');
    $mdThemingProvider.theme('sidebar')
      .primaryPalette('indigo')
      .accentPalette('pink')
      .backgroundPalette('indigo');
    $mdThemingProvider.theme('top')
      .primaryPalette('deep-purple');
  })
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
  }).constant('config', {
    api: 'http://localhost/robocodecupapi/api'
  });
