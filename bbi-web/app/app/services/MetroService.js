app.factory('MetroService', 
  ['$http', '$q', 'config', 
    function($http, $q, config){
      return {
        getMetroCorridors: function(){
          var dfd = $q.defer();
          $http.get(config.apiPath + '/metro-corridors').success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        getMetroPackages: function(corridorId){
          if(!corridorId){
            corrData = "";
          }
          else{
            corrData = "?corridor_id=" + corridorId;
          }
          var dfd = $q.defer();
          $http.get(config.apiPath + '/metro-packages' + corrData).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        shortlistPackage: function(packages){
          var dfd = $q.defer();
          $http.post(config.apiPath + '/shortlist-metro-package', packages).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        getShortlistPackages: function(){
          var dfd = $q.defer();
          $http.get(config.apiPath + '/shortlisted-metro-packages').success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        deleteShortlistedMetroPackage: function(pkgId){
          var dfd = $q.defer();
          $http.delete(config.apiPath + '/shortlisted-metro-package/' + pkgId).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        saveMetroCampaign: function(campaign){
          var dfd = $q.defer();
          $http.post(config.apiPath + '/metro-campaign', campaign).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        getMetroCampaigns: function(){
          var dfd = $q.defer();
          $http.get(config.apiPath + '/metro-campaigns').success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        checkoutMetroCampaign: function(metroCampaignId, flag, GST){
          var dfd = $q.defer();
          $http.get(config.apiPath + '/checkout-metro-campaign/' + metroCampaignId + '/' + flag + '/' + GST).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        getMetroCampDetails: function(metroCampaignId){
          var dfd = $q.defer();
          $http.get(config.apiPath + '/metro-campaign/' + metroCampaignId).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        deleteMetroPackageFromCampaign: function(campaignId, productId){
          var dfd = $q.defer();
          $http.delete(config.apiPath + '/metro-campaign-product/' + campaignId + '/product/' + productId).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        addPackageInMetroCampaign: function(package){
          var dfd = $q.defer();
          $http.post(config.apiPath + '/package-to-metro-campaign', package).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        downloadQuote: function (campaignId) {
          var dfd = $q.defer();
          $http.get(config.apiPath + '/download-quote/' + campaignId,{responseType: 'arraybuffer'}).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
      },
      }
    }
  ]
);