angular.module("bbManager").service('uploadDataService', 
['$http', '$q', 'config', 
  function($http, $q, config){
    return {
        getUploadedDataList: function () {
            var dfd = $q.defer();
           $http.get(config.apiPath + '/bulk-upload-products').success(dfd.resolve).error(dfd.reject);
           return dfd.promise;
       },
    }
}]);