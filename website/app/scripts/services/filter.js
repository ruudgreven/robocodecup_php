'use strict';

/**
 * @ngdoc service
 * @name robocodecupApp.filter
 * @description
 * # filter
 * Service in the robocodecupApp.
 */
angular.module('robocodecupApp')
  .service('filter', function ($log) {
    var filteroptions = {};

    filteroptions.filterteam = '';
    filteroptions.filterpools = {};

    filteroptions.setFilterTeam = function(value) {
      if (filteroptions.filterteam == value) {
        filteroptions.filterteam = '';
      } else {
        filteroptions.filterteam = value;
      }
    };

    filteroptions.setFilterPools = function(pools) {
      filteroptions.filterpools = pools;
      filteroptions.filterteam = '';
    };

    filteroptions.doFiltering = function(scores) {
      $log.log('Do filtering on pools ' + filteroptions.filterpools + ' and team ' + filteroptions.filterteam);

      var newscores = [];
      scores.forEach(function(score) {
        if (filteroptions.filterpools.indexOf(score.pool_id) != -1) {
          if (filteroptions.filterteam !== '') {
            if (score.id == filteroptions.filterteam) {
              newscores.push(score);
            }
          } else {
            newscores.push(score);
          }
        }
      });
      return newscores;
    };

    filteroptions.hasFilterTeam = function() {
      return filteroptions.filterteam !== '';
    };

    filteroptions.hasFilter = function() {
      return !(filteroptions.filterteam === '' && filteroptions.filterpools.length === 0);
    };

    return filteroptions;
  });
