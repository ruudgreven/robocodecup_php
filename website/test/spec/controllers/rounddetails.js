'use strict';

describe('Controller: RounddetailsCtrl', function () {

  // load the controller's module
  beforeEach(module('robocodecupApp'));

  var RounddetailsCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    RounddetailsCtrl = $controller('RounddetailsCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(RounddetailsCtrl.awesomeThings.length).toBe(3);
  });
});
