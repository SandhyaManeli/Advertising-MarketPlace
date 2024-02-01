angular.module("bbManager").service('ManagementReportService', 
['$http', '$q', 'config', 
  function($http, $q, config){
    return {
      getReportCounts: function(){
        var dfd = $q.defer();
        $http.get(config.apiPath + '/get-counts').success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      getOwnerReportCounts: function(){
        var dfd = $q.defer();
        $http.get(config.apiPath + '/get-owner-counts').success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      getBuyerReportCounts: function(){
        var dfd = $q.defer();
        $http.get(config.apiPath + '/get-buyer-counts').success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      getUserProfileData: function(){
        var dfd = $q.defer();
        $http.get(config.apiPath + '/user-profile').success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      searchAreas: function(query){
        var dfd = $q.defer();
        $http.get(config.apiPath + '/search-areas/' + query).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      getUsersDetail: function() {
        var dfd = $q.defer();
        $http.get(config.apiPath + '/get-user-details').success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      getProductsDetail: function() {
        var dfd = $q.defer();
        $http.get(config.apiPath + '/get-product-details').success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      getCampaignsDetail: function() {
        var dfd = $q.defer();
        $http.get(config.apiPath + '/get-campaign-details').success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      getFilteredUsers: function(payload) {
        var dfd = $q.defer();
        $http.post(config.apiPath + '/filter-users', payload).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      getFilteredProducts: function(payload) {
        var dfd = $q.defer();
        $http.post(config.apiPath + '/filter-products-report', payload).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      getFilteredCampaigns: function(payload) {
        var dfd = $q.defer();
        $http.post(config.apiPath + '/filter-campaigns-report', payload).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      downloadFilteredUsers: function(payload) {
        var dfd = $q.defer();
        $http.post(config.apiPath + '/filter-users-download', payload,{responseType: 'arraybuffer'}).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      downloadFilteredProudcts: function(payload) {
        var dfd = $q.defer();
        $http.post(config.apiPath + '/filter-products-report-download', payload,{responseType: 'arraybuffer'}).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      downloadFilteredCampaigns: function(payload) {
        var dfd = $q.defer();
        $http.post(config.apiPath + '/filter-campaigns-report-download', payload,{responseType: 'arraybuffer'}).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      downloadUsersCSVFile: function(payload) {
        var dfd = $q.defer();
        $http.post(config.apiPath + '/filter-users-excel-download', payload,{responseType: 'arraybuffer'}).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      downloadProductsCSVFile: function(payload) {
        var dfd = $q.defer();
        $http.post(config.apiPath + '/filter-products-excel-download', payload,{responseType: 'arraybuffer'}).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      downloadCampaignsCSVFile: function(payload) {
        var dfd = $q.defer();
        $http.post(config.apiPath + '/filter-campaigns-excel-download', payload,{responseType: 'arraybuffer'}).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      searchUserDetails: function(payload) {
        var dfd = $q.defer();
        $http.post(config.apiPath + '/users-details-filters-search', payload).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      searchProductDetails: function(payload) {
        var dfd = $q.defer();
        $http.post(config.apiPath + '/products-details-filters-search', payload).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      searchCampaignDetails: function(payload) {
        var dfd = $q.defer();
        $http.post(config.apiPath + '/campaigns-details-filters-search', payload).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      notifyMe: function(payload) {
        var dfd = $q.defer();
        $http.post(config.apiPath + '/product-expiry-notify', payload).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      headers: [
        {items:[ //users
          {field: 'first_name', heading: 'First Name',type:'string'},
          {field: 'last_name', heading: 'Last Name',type:'string'},
          {field: 'email', heading: 'Email',type:'string'},
          {field: 'phone', heading: 'Phone',type:'number'},
          {field: 'company_name', heading: 'Company',type:'string'},
          {field: 'verified', heading: 'Approved',type:'boolean'},
          {field: 'created_at', heading: 'Created On',type:'date'}
        ]},
        {items:[ //products
          {field: 'siteNo', heading: 'ID',type:'string'},
          {field: 'type', heading: 'Type',type:'string'},
          {field: 'title', heading: 'Title',type:'string'},
          {field: 'lat', heading: 'Lat',type:'number'},
          {field: 'lng', heading: 'Long',type:'number'},
          {field: 'state', heading: 'State',type:'string'},
          {field: 'city_name', heading: 'City',type:'string'},
          {field: 'zipcode', heading: 'Zipcode',type:'number'},
          {field: 'rateCard', heading: 'Price',type:'number'}
        ]},
        {items:[ //campaigns
          {field: 'cid', heading: 'Campaign ID',type:'string'},
          {field: 'name', heading: 'Name',type:'string'},
          {field: 'status', heading: 'Status',type:'string'}
        ]}
      ],
      campaignStatus: {
        100: "Campaign Preparing",
        200: "Campaign Created",
        300: "Quote Created",
        400: "Quote Given",
        500: "Change Requested",
        600: "Requested",
        700: "Scheduled",
        800: "Running",
        900: "Suspended",
        1000: "Stopped",
        1200: "Deleted",
        1300: "RFP Campaign"
      },
      campaignStatusCode: {
        "campaign preparing": 100,
        "campaign created": 200,
        "quote created": 300,
        "quote given": 400,
        "change requested": 500,
        "requested": 600,
        "scheduled": 700,
        "running": 800,
        "suspended": 900,
        "stopped": 1000,
        "deleted": 1200,
        "rfp campaign": 1300
      },
      pageTitle: {
        10: "Users",
        11: "Products",
        12: "Campaigns"
      }
    }
}]);