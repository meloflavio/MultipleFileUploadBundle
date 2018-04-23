(function () {
  'use strict';

  var app = angular.module('Controllers', ['angularFileUpload']);

  app.controller('MultipleFileUploadCtrl', ['$scope', '$http', 'FileUploader', function ($scope, $http, FileUploader) {

    var vm = this;
    vm.medias = [];
    vm.singleUpload = false;
    vm.allowedExtensionsFilter = '';
    vm.allowImages = false;
    vm.allowFiles = false;
    vm.maxFileSizeInMB = 5;
    vm.fileNames = [];

    vm.entity = '';
    vm.id = '';
    vm.field = '';

    vm.showFileTypeAlert = false;

    var responseMediaId = null;

    vm.initUploader = function () {

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

      uploader.onAfterAddingFile = function (item) {
        vm.showWrongFormatAlert = false;

        console.log(item.file);
        var fileSizeInMB = item.file.size / 1024 / 1024;

        if (fileSizeInMB > vm.maxFileSizeInMB) {
          vm.showMaxFileSizeAlert = true;
          uploader.removeFromQueue(item);
          return;
        }

        vm.showMaxFileSizeAlert = false;
        vm.disableSaveButtons();
      };

      uploader.onWhenAddingFileFailed = function (item, filter, options) {
        vm.showWrongFormatAlert = true;
        vm.showMaxFileSizeAlert = false;
      };

      uploader.onAfterRemoveFile = function () {
        vm.enableSaveButtons();
      };

      uploader.onSuccessItem = function (fileItem, response, status, headers) {
        responseMediaId = response.mediaId;
        vm.showWrongFormatAlert = false;

        // Clear list to show only one image
        if (vm.singleUpload) {
          vm.medias = [];
        }

        vm.medias.push(responseMediaId);

        if (vm.allowFiles) {
          vm.initFileNames();
        }
      };

      uploader.onCompleteAll = function () {
        uploader.clearQueue();
        vm.enableSaveButtons();
      };
    };

    vm.disableSaveButtons = function () {
      var $button = $('button[type=submit].btn-success');
      $button.prop('title', 'Es befinden sich noch Elemente in der Upload Warteschlange');
      $button.prop('disabled', true);
    };

    vm.enableSaveButtons = function () {
      if ($scope.uploader.queue.length == 0) {
        var $button = $('button[type=submit].btn-success');
        $button.removeAttr('title');
        $button.prop('disabled', false);
      }
    };

    vm.initFileNames = function () {
      var ids = vm.medias.join(',');
      $http.get('/api/multiple-file-upload/media/' + ids + '/name').then(function (response) {
        vm.fileNames = response.data;
      })
    };

    vm.removeMedia = function (imageId) {
      $http.delete('/api/multiple-file-upload/media/' + imageId).then(function () {
        vm.medias.splice(vm.medias.indexOf(imageId), 1);
      });
    };

  }]);

  app.controller('MultipleImageViewCtrl', [function () {

    var vm = this;
    vm.medias = [];

  }]);

})();
