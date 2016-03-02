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
    var filteringenabled = false;

    filteroptions.filterpools = {};

    filteroptions.setFilterPools = function(pools) {
      filteroptions.filterpools = pools;
    };

    filteroptions.doFiltering = function(scores) {
      $log.log('Do filtering on pools ' + filteroptions.filterpools);

      var newscores = [];
      var count = 1;
      scores.forEach(function(score) {
        if (filteroptions.filterpools.indexOf(score.pool_id) != -1) {
          score.rank = count;
          newscores.push(score);
          count++;
        }
      });

      return newscores;
    };

    filteroptions.hasFilter = function() {
      return filteroptions.filterpools.length !== 0;
    };

    filteroptions.disableFiltering = function() {
      filteroptions.filteringenabled = false;
    };

    filteroptions.enableFiltering = function() {
      filteroptions.filteringenabled = false;
    };

    return filteroptions;
  });
