  angular.module("bbManager").factory('ContactService', ['$http', '$q', 'config', function($http, $q, config){
  return {
    sendQuery: function(query){
      var dfd = $q.defer();
      $http.post(config.apiPath + '/user-query', query).success(dfd.resolve).error(dfd.reject);
      return dfd.promise;
    },
    requestCallBack: function(callbackRequestData){
      var dfd = $q.defer();
      $http.post(config.apiPath + '/request-callback', callbackRequestData).success(dfd.resolve).error(dfd.reject);
      return dfd.promise;
    },
    subscribe: function(subscriberData){
      var dfd = $q.defer();
      $http.post(config.apiPath + '/subscription', subscriberData).success(dfd.resolve).error(dfd.reject);
      return dfd.promise;
    },
  }
}]);
