'use strict';

describe('Controller: BackendCtrl', function () {

  // load the controller's module
  beforeEach(module('laylaCmsApp'));

  var BackendCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    BackendCtrl = $controller('BackendCtrl', {
      $scope: scope
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(scope.awesomeThings.length).toBe(3);
  });
});
