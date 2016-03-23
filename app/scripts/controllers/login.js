'use strict';

/**
 * @ngdoc function
 * @name laylaCmsApp.controller:LoginCtrl
 * @description
 * # LoginCtrl
 * Controller of the laylaCmsApp
 */
var app = angular.module('laylaCmsApp');

app.controller('LoginCtrl', function ($scope, $rootScope, $http, $cookies, $state) {

$scope.login = function() {
	// Send Login Data
	$http.get(
		app.config.api + '?request=login&username=' + $scope.login.username + '&password=' + $scope.login.password
	).then(
		function(response) {
			$rootScope.bearer = response.data;
			$cookies.bearer   = response.data;
			$state.go('backend');
		},
		function() {
			$scope.login.error = true;
		});
	};
});