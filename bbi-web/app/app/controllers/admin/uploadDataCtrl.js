angular.module("bbManager").controller('uploadDataCtrl', function ($scope, $http, uploadDataService,$auth,ProductService) {
  var getUploadedDataList = function () {
    uploadDataService.getUploadedDataList().then(function (result) {
      $scope.uploadedDataList = result;
    });
  }
  getUploadedDataList();
  });