'use strict';

describe('Service: filter', function () {

  // load the service's module
  beforeEach(module('robocodecupApp'));

  // instantiate service
  var filter;
  beforeEach(inject(function (_filter_) {
    filter = _filter_;
  }));

  it('should do something', function () {
    expect(!!filter).toBe(true);
  });

});
