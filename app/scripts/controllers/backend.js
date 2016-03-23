'use strict';

Array.prototype.remove = function(from, to) {
  var rest = this.slice((to || from) + 1 || this.length);
  this.length = from < 0 ? this.length + from : from;
  return this.push.apply(this, rest);
};

/**
 * @ngdoc function
 * @name laylaCmsApp.controller:BackendCtrl
 * @description
 * # BackendCtrl
 * Controller of the laylaCmsApp
 */
var app = angular.module('laylaCmsApp');

app.controller('BackendCtrl', function ($rootScope, $scope, $compile, $http, $translate, $translatePartialLoader) {

  	// Load Backend Config and
  	// apply Backend Language and
  	// add Frontend Modules
  	var backendConfig = {};
	$http.get(app.config.api + '?request=loadBEConfig').then(function(result) {
		backendConfig = result.data;

		// Apply BE preconfigured language || fallback language
		$translatePartialLoader.addPart('i18n');
		if(backendConfig.language) {
			$translate.use(backendConfig.language);
		}
		else {
			$translate.use('en_US');
		}

		// Load the frontend modules
		$scope.loadFrontendModules();
	});

  	// Load Basic Content File
  	$http.get(app.config.api + '?request=loadFEContent').then(function(result) {
		$scope.globalSettings = result.data.global;
		$scope.contentMain    = result.data.content;
    });

	// Load Frontend Modules
	$scope.loadFrontendModules = function() {
		$scope.elementTypes = [];
		$scope.pathList     = [];

		$http.get('/modules/modules.json').then(function(result) {
			var modules = result.data.modules || [];

			if(modules.length > 0) {
				angular.forEach(modules, function(module) {

					var moduleDir = 'modules/elements/' + module.name;

					// Make Modules/Templates available
					$scope.elementTypes.push(module);
					$scope.pathList[module.name] = moduleDir + '/templates/' + module.beView;

					// Extend language settings
					if(backendConfig.language) {
						$translatePartialLoader.addPart(moduleDir + '/languages');
					}
				});
			}
			$translate.refresh();
		});
	};

	/**
  	 * Function will open the meta-info section for
  	 * Elements
  	 */
  	$scope.openSectionMetaInfo = function(e) {
  		var toggleElem = angular.element(e.target);
  		var metaInfo   = toggleElem.parents('.section').find('.meta-section');

  		if(metaInfo) {
  			if(metaInfo.hasClass('hide')) {
  				metaInfo.removeClass('hide');
  			}
  			else {
  				metaInfo.addClass('hide');
  			}
  		}
  	};

  	/**
  	 * Function will open the meta-info section for
  	 * Elements
  	 */
  	$scope.openElementMetaInfo = function(e) {
  		var toggleElem = angular.element(e.target);
  		var metaInfo   = toggleElem.parents('.element').find('.meta');

  		if(metaInfo) {
  			if(metaInfo.hasClass('hide')) {
  				metaInfo.removeClass('hide');
  			}
  			else {
  				metaInfo.addClass('hide');
  			}
  		}
  	};

  	$scope.editElement = function(type, e) {
		var trigger        = angular.element(e.target);
		var elemCurrent    = trigger.parents('.element');
		var elemList       = trigger.parents('.section').find('.element');
		var elemIndex      = elemList.index(elemCurrent);
		var sectionCurrent = trigger.parents('.section');
		var sectionList    = trigger.parents('body').find('.section');
		var sectionIndex   = sectionList.index(sectionCurrent);
		var _tmp;

		if(type === 'moveUp') {
			if(elemIndex > 0) {
				_tmp                                                               = $scope.contentMain[1].content[sectionIndex].content[elemIndex - 1];
				$scope.contentMain[1].content[sectionIndex].content[elemIndex - 1] = $scope.contentMain[1].content[sectionIndex].content[elemIndex];
				$scope.contentMain[1].content[sectionIndex].content[elemIndex]     = _tmp;
	  		}
		}
		else if(type === 'moveDown') {
			if(elemIndex < (elemList.length - 1)) {
				_tmp                                                               = $scope.contentMain[1].content[sectionIndex].content[elemIndex + 1];
				$scope.contentMain[1].content[sectionIndex].content[elemIndex + 1] = $scope.contentMain[1].content[sectionIndex].content[elemIndex];
				$scope.contentMain[1].content[sectionIndex].content[elemIndex]     = _tmp;
	  		}
		}
		else if(type === 'remove') {
			if($scope.contentMain[1].content[sectionIndex].content.length === 1) {
				$scope.contentMain[1].content.remove(sectionIndex);
			}
			else {
				$scope.contentMain[1].content[sectionIndex].content.remove(elemIndex);
			}
		}
		else if(type === 'add') {
			$scope.contentMain[1].content[sectionIndex].content.splice((elemIndex + 1), 0, {});
		}
  	};

  	$scope.editSection = function(type, e) {
  		var trigger        = angular.element(e.target);
		var sectionCurrent = trigger.parents('.section');
		var sectionList    = trigger.parents('body').find('.section');
		var sectionIndex   = sectionList.index(sectionCurrent);
		var _tmp;

		if(type === 'moveUp') {
			if(sectionIndex > 0) {
				_tmp                                            = $scope.contentMain[1].content[sectionIndex - 1];
				$scope.contentMain[1].content[sectionIndex - 1] = $scope.contentMain[1].content[sectionIndex];
				$scope.contentMain[1].content[sectionIndex]     = _tmp;
	  		}
		}
		else if(type === 'moveDown') {
			if(sectionIndex < (sectionList.length - 1)) {
				_tmp                                            = $scope.contentMain[1].content[sectionIndex + 1];
				$scope.contentMain[1].content[sectionIndex + 1] = $scope.contentMain[1].content[sectionIndex];
				$scope.contentMain[1].content[sectionIndex]     = _tmp;
	  		}
		}
		else if(type === 'remove') {
			sectionCurrent.animate({
				'height'	    : 0,
				'margin-bottom' : 0,
				'opacity'       : 0
			}, 400, function() {
				sectionCurrent.remove();
				$scope.contentMain[1].content.remove(sectionIndex);
			});
		}
		else if(type === 'add') {
			$scope.contentMain[1].content.splice((sectionIndex + 1), 0, { content: [{}]});
		}
  	};

  	$scope.save = function() {
  		// Todo validate object
  		// Todo SAVE
  		var payload = {
  			'meta' : {
  				'title' : 'Neue Version'
  			},
  			'global'  : $scope.globalSettings,
  			'content' : $scope.contentMain
  		};

  		$http.post(
  			app.config.api,
  			'request=writeFEContent&content=' + JSON.stringify(payload)
  		);

  		// Animate saved info
  		angular.element('.info-save')
  		.stop(true, true)
  		.css({
  			'opacity' : 1
  		})
  		.animate({
  			'opacity' : 0
  		}, 5000);
  	};
});