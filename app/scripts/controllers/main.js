'use strict';

/**
 * @ngdoc function
 * @name laylaCmsApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the laylaCmsApp
 */
var app = angular.module('laylaCmsApp');

app.controller('MainCtrl', function ($scope, $http) {

    // Load Basic Content File
    $http.get(app.config.api + '?request=loadFEContent').then(function(basicData) {
    	$scope.globalSettings = basicData.data.global;
    	$scope.contentMain    = basicData.data.content;
    });

    // Load Frontend Modules
	// (and their backend templates)
	$scope.elementTypes = [];
	$scope.pathList     = [];

	$http.get('/modules/modules.json').then(function(result) {
		var modules = result.data.modules || [];

		if(modules.length > 0) {
			angular.forEach(modules, function(module) {

				var moduleDir = 'modules/elements/' + module.name;

				$scope.elementTypes.push(module);
				$scope.pathList[module.name] = moduleDir + '/templates/' + module.feView;
			});
		}
	});
});