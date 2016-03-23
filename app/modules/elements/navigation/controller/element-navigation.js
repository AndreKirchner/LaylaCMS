'use strict';

/**
 * @ngdoc function
 * @name laylaCmsApp.controller:LoginCtrl
 * @description
 * # LoginCtrl
 * Controller of the laylaCmsApp
 */
var app = angular.module('laylaCmsApp');

app.controller('ElementNavigationCtrl', function ($scope, $location, $anchorScroll) {

	$scope.scrollTo = function(id) {

        var target = angular.element('#' + id);
        var offset = 0;

        angular.element('html, body').stop().animate({
            'scrollTop' : (target.offset().top - offset)
        }, 600);
    }
});