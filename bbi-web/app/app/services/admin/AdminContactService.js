angular.module("bbManager").factory('AdminContactService', ['$http', '$q', 'config', function($http, $q, config){
  return {
    userQuery: function(){
      var dfd = $q.defer();
      $http.get(config.apiPath + '/customer-query/user-query', {skipInterceptor: true}).success(dfd.resolve).error(dfd.reject);
      return dfd.promise;

    },
    requestCallBack: function(){
      var dfd = $q.defer();
      $http.get(config.apiPath + '/customer-query/request-callback', {skipInterceptor: true}).success(dfd.resolve).error(dfd.reject);
      return dfd.promise;
    },
    subscribe: function(){
      var dfd = $q.defer();
      $http.get(config.apiPath + '/customer-query/newsletter-subscription', {skipInterceptor: true}).success(dfd.resolve).error(dfd.reject);
      return dfd.promise;
    },
    updateCustomerData: function(updateID, obj){
        var dfd = $q.defer();
        $http.put(config.apiPath + '/update-customer-data/' + updateID, obj).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
    },
  }
}]);
