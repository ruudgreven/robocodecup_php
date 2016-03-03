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

    filteroptions.filterpools = {};

    filteroptions.setFilterPools = function(pools) {
      filteroptions.filterpools = pools;
      filteroptions.filterteam = '';
    };

    filteroptions.doFiltering = function(objects) {
      $log.log('Do filtering on pools ' + filteroptions.filterpools);

      var newobjects = [];
      var counter = 1;
      objects.forEach(function(object) {
        if (filteroptions.filterpools.indexOf(object.pool_id) != -1) {
          if (object.scores) {
            //If there is a subobject with scores
            var scorecounter = 1;
            object.scores.forEach(function(score) {
              score.rank = scorecounter;
              scorecounter++;
            });
          } else {
            //If there is no subobject with scores
            object.rank = counter;
          }
          newobjects.push(object);
          counter++
        }
      });
      return newobjects;
    };

    filteroptions.hasFilter = function() {
      return filteroptions.filterpools.length !== 0;
    };

    return filteroptions;
  });
