'use strict';

/**
* @ngdoc overview
* @name laylaCmsApp
* @description
* # laylaCmsApp
*
* Main module of the application.
*/
var app = angular
.module('laylaCmsApp', [
	'ngAnimate',
	'ngCookies',
	'ngResource',
	'ui.router',
	'ngSanitize',
	'ngTouch',
	'permission',
	'jerryhsia.minieditor',
	'pascalprecht.translate',
	'angularFileUpload'
]);

// stateProvider config
app.config(function ($stateProvider) {
	$stateProvider
	.state('main', {
		url         : '/',
		controller  : 'MainCtrl',
		templateUrl : 'views/main.html'
	})
	.state('login', {
		url         : '/login',
		controller  : 'LoginCtrl',
		templateUrl : 'views/login.html'
	})
	.state('backend', {
		url         : '/backend',
		controller  : 'BackendCtrl',
		templateUrl : 'views/backend.html',
		data: {
			permissions: {
				only       : ['admin'],
				redirectTo : 'login'
			}
		}
	});
});

// urlRouterProvider config
app.config(function ($urlRouterProvider) {
	$urlRouterProvider.otherwise('/');
});

// translationProvider config
app.config(['$translateProvider', function($translateProvider) {
	$translateProvider.useLoader('$translatePartialLoader', {
		urlTemplate: '{part}/{lang}.json'
	});
}]);

// controllerProvider config
app.config(['$controllerProvider', function($controllerProvider) {
	// Add new controllers on runtime
	app._controller = app.controller;

	app.controller = function (name, constructor) {
		$controllerProvider.register(name, constructor);
		return (this);
	};
}]);

/* App RUN */
app.run([
	'$rootScope',
	'$http',
	'$q',
	'$cookies',
	'Permission',
	 function(
	 $rootScope,
	 $http,
	 $q,
	 $cookies,
	 Permission
	 ) {

	// Load local angular config (JSON)
	var configLoader = $http.get('/system/localconfig.json');

	// Provide config
	configLoader.then(function($config) {
		app.config = $config.data;
	});

	// Check permissions
	Permission.defineRole('admin', function () {

		var deferred = $q.defer();

		configLoader.then(function() {

			// Bearer Cookied? Always take the freshest one
			$rootScope.bearer = $rootScope.bearer || $cookies.bearer;

			// Authetication setup
			if($rootScope.bearer) {

				// Basic http header setup
				$http.defaults.headers.common['Auth-Token'] = $rootScope.bearer;

				// Authenticate
				$http.get(app.config.api + '?request=checkAuth').then(
					function() {
						deferred.resolve();
					},
					function() {
						deferred.reject();
					}
				);
			}
			else {
				deferred.reject();
			}
		});

		return deferred.promise;
	});
}]);