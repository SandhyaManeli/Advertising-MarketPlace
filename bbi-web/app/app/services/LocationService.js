angular.module("bbManager").factory('LocationService', 
  ['$http', '$q', 'config', 
    function($http, $q, config){
    
      return {
        getCountries: function(){
          var dfd = $q.defer();
          $http.get(config.apiPath + '/countries').success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },  
        getStates: function(countryId){
          var dfd = $q.defer();
          $http.get(config.apiPath + '/states/' + countryId).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        getCities: function(stateIds){
          var dfd = $q.defer();
          $http.get(config.apiPath + '/cities/' + stateIds).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        getAreas: function(citiIds){
          var dfd = $q.defer();
          $http.get(config.apiPath + '/areas/' + citiIds).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        getAreasWithAutocomplete: function(searchText){
          var dfd = $q.defer();
          $http.get(config.apiPath + '/autocomplete-area/' + searchText).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        }
      }
    }
  ]
);