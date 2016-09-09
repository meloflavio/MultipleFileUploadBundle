(function() {
    'use strict';

    var app = angular.module('app', ['Controllers', 'ui.bootstrap']);

    app.config(['$interpolateProvider', function ($interpolateProvider) {
        $interpolateProvider.startSymbol('[[');
        $interpolateProvider.endSymbol(']]');
    }]);

})();
