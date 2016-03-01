'use strict';

describe('Controller: PoolCtrl', function () {

  // load the controller's module
  beforeEach(module('robocodecupApp'));

  var PoolCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    PoolCtrl = $controller('PoolCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(PoolCtrl.awesomeThings.length).toBe(3);
  });
});
