'use strict';

/**
 * @ngdoc function
 * @name laylaCmsApp.controller:LoginCtrl
 * @description
 * # LoginCtrl
 * Controller of the laylaCmsApp
 */
var app = angular.module('laylaCmsApp');

app.controller('ElementImageCtrl', function ($scope, $http, $upload) {

	// Get Frontend Ressource Path
    $scope.ressources = app.config.ressources;

    // Get filelist from API
	$scope.updateFileList = function() {
    	$http.get(app.config.api + '?request=showDir').then(function(result) {
			$scope.fileList = result.data;
	    });
    };

	// Initially load filelist
  	$scope.updateFileList();

    // Watch for file list changes
    $scope.$watch('files', function () {
        $scope.upload($scope.files);
    });

    // Upload function
	$scope.upload = function (files) {
        if (files && files.length) {

            $scope.uploadRunning = true;
            $scope.uploadSuccess = false;

            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                $upload.upload({
                    url   	: app.config.api + '?request=uploadFile',
                    headers : {'Content-Type': file.type},
                    method  : 'POST',
   					data 	: file,
    				file 	: file
                }).progress(function (evt) {
                    var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
                    $scope.uploadProgress = progressPercentage + ' %';
                    if(progressPercentage == 100) {
                    	$scope.uploadRunning = false;
                    }
                }).success(function (data, status, headers, config) {
                    $scope.uploadRunning = false;
                    $scope.uploadSuccess = true;
                    $scope.updateFileList();
                });
            }
        }
    };
});