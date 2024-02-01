angular.module("bbManager").service('CompanyService', 
['$http', '$q', 'config', 
  function($http, $q, config){
    return {
      getClientTypes: function(){
        var dfd = $q.defer();
        $http.get(config.apiPath + '/client-types').success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      getCompanies: function(){
        var dfd = $q.defer();
        $http.get(config.apiPath + '/companies').success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },      
      getAllClients: function(){
        var dfd = $q.defer();
        $http.get(config.apiPath + '/all-clients').success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      saveCompany: function(company){
        var dfd = $q.defer();
        $http.post(config.apiPath + '/company', company).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      saveClient: function(client){
        var dfd = $q.defer();
        $http.post(config.apiPath + '/client', client).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      deleteHoardingCompanies: function(hoardingCompanyId){
				var dfd = $q.defer();
				$http.delete(config.apiPath + '/hoarding-companies/' + formatId).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
      },
      registerClient: function(client){
        var dfd = $q.defer();
        $http.post(config.apiPath + '/client', client).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      grantPasswordGeneration: function(code){
        var dfd = $q.defer();
        $http.get(config.apiPath + '/check-pwd-generation/' + code).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      }
    }
    
}]);