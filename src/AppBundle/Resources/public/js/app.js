var cardApp = angular.module('cardApp', ["ngRoute"]);
	cardApp.config(['$interpolateProvider',function($interpolateProvider) {
        $interpolateProvider.startSymbol('{[{').endSymbol('}]}');	
	}])