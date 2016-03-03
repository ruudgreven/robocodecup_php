'use strict';

describe('Controller: TeamdetailsCtrl', function () {

  // load the controller's module
  beforeEach(module('robocodecupApp'));

  var TeamdetailsCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    TeamdetailsCtrl = $controller('TeamdetailsCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(TeamdetailsCtrl.awesomeThings.length).toBe(3);
  });
});
