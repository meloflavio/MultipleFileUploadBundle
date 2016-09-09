(function() {
    'use strict';

    var app = angular.module('Controllers', ['angularFileUpload']);

    app.controller('MultipleFileUploadCtrl', ['$scope','$http', 'FileUploader', function($scope, $http, FileUploader) {

        var vm = this;
        vm.medias = [];
        vm.singleUpload = false;
        vm.allowedExtensionsFilter = '';
        vm.allowImages = false;
        vm.allowFiles = false;
        vm.fileNames = [];

        vm.entity = '';
        vm.id = '';
        vm.field = '';

        var responseMediaId = null;

        vm.initUploader = function() {

            var fileUploadOptions = {};
            if (vm.singleUpload) {
                fileUploadOptions.queueLimit = 1;
            }

            var uploader = $scope.uploader = new FileUploader({
                formData: [{
                    entity: vm.entity,
                    id: vm.id,
                    field: vm.field
                }]
            });

            if (vm.singleUpload) {
                uploader.queueLimit = 1;
            }

            // Filters
            uploader.filters.push({
                name: 'fileFilter',
                fn: function (item) {
                    var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
                    return vm.allowedExtensionsFilter.indexOf(type) !== -1;
                }
            });

            uploader.onSuccessItem = function (fileItem, response, status, headers) {
                responseMediaId = response.mediaId;

                // Clear list to show only one image
                if (vm.singleUpload) {
                    vm.medias = [];
                }

                vm.medias.push(responseMediaId);

                if (vm.allowFiles) {
                    vm.initFileNames();
                }

                console.info('onSuccessItem', fileItem, response, status, headers);
            };

            uploader.onCompleteAll = function() {
                uploader.clearQueue();
            };
        };

        vm.initFileNames = function() {
            var ids = vm.medias.join(',');
            $http.get('/api/multiple-file-upload/media/' + ids + '/name').then(function(response) {
                vm.fileNames = response.data;
            })
        };

        vm.removeMedia = function(imageId) {
            $http.delete('/api/multiple-file-upload/media/' + imageId).then(function() {
                vm.medias.splice(vm.medias.indexOf(imageId), 1);
            });
        };

    }]);

    app.controller('MultipleImageViewCtrl', [function() {

        var vm = this;
        vm.medias = [];

    }]);

})();
