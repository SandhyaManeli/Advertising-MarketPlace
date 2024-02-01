angular.module("bbManager").service("AdminUserService", [
  "$http",
  "$q",
  "config",
  function ($http, $q, config) {
    return {
      getUsers: function (pageNo, pageSize) {
        var pageData = "";
        if (pageNo && pageSize) {
          var pageData = "?page_no=" + pageNo + "&page_size=" + pageSize;
        }
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/users" + pageData)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getAgencies: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/agencies")
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      saveUser: function (user) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/userByAdmin", user)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      saveAgency: function (agency) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/agencyByAdmin", agency)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      toggleActivationUser: function (userMID) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/switch-activation-user/" + userMID)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      deleteUser: function (userMID) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/delete-user/" + userMID)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      verifyLogin: function (user_id) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/user-single-record/" + user_id)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getEnquiries: function (productId) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/get-expiry-products")
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getContracts: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/get-contracts")
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getInvoices: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/get-invoices")
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      saveContracts: function (data) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/save-contracts", data)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      saveInvoice: function (data) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/save-invoices", data)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      deleteContracts: function (data) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/delete-contracts", data)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      deleteInvoice: function (data) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/delete-invoices", data)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      downloadInvoice: function (data) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/download-invoices", data,{
            responseType: "arraybuffer",
          })
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      downloadContract: function (data) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/download-contracts", data,{
            responseType: "arraybuffer",
          })
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
    };
  },
]);
