'use strict';

describe('Controller: RoundCtrl', function () {

  // load the controller's module
  beforeEach(module('robocodecupApp'));

  var RoundCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    RoundCtrl = $controller('RoundCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(RoundCtrl.awesomeThings.length).toBe(3);
  });
});
