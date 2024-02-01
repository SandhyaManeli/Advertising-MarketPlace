angular.module("bbManager").service("AdminCampaignService", [
  "$http",
  "$q",
  "config",
  function ($http, $q, config) {
    // var shortlistProduct;
    return {
      getAllCampaigns: function (tab_status) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + `/get-all-campaigns${tab_status ? '?tab_status=' + tab_status : ''}`)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getRequestedCampaigns: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/get-requested-campaigns", {
            skipInterceptor: true,
          })
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getProductCampaigns: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/delete-products-list-from-campaign", {
            skipInterceptor: true,
          })
          .success(dfd.resolve)
          .error(dfd.reject);
        // shortlistProduct = dfd.promise;
        return dfd.promise;
      },
      getProductCampaignsShortlist: function () {
        return shortlistProduct;
      },
      confirmCampaignRequest: function (campaignParam) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/stripeRefund", campaignParam)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      confirmProductRequest: function (campaignPara) {
        var dfd = $q.defer();
        $http
          .post(
            config.apiPath + "/stripeRefund-for-delete-product",
            campaignPara
          )
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getAllCampaignRequests: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/all-campaign-requests")
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      changeProductPrice: function (data) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/change-product-price", data)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getProposedCampaigns: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/all-campaigns/planning")
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getPlannedCampaigns: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/all-campaigns/planned")
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      saveUserCampaign: function (campaign) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/user-campaign", campaign)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      sendSuggestionRequest: function (suggestionRequest) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/suggestion-request", suggestionRequest)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      deleteUserCampaign: function (campaignId) {
        var dfd = $q.defer();
        $http
          .delete(config.apiPath + "/campaign/" + campaignId)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      deleteNonUserCampaign: function (campaignId) {
        var dfd = $q.defer();
        $http
          .delete(config.apiPath + "/non-user-campaign/" + campaignId)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      proposeProductForCampaign: function (obj) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/propose-product-for-campaign", obj)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      deleteProductFromCampaign: function (campaignId, productId) {
        var dfd = $q.defer();
        $http
          .delete(
            config.apiPath + "/campaign/" + campaignId + "/product/" + productId
          )
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      shareandDownloadCampaignToEmail: function (campaignToDownloadEmail) {
        var dfd = $q.defer();
        $http
          .post(
            config.apiPath + "/shareCampaigndownloadQuote",
            campaignToDownloadEmail
          )
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getCampaignWithProducts: function (campaignId) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/user-campaign/" + campaignId)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getRFPCampaignWithProducts: function (campaignId) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/user-campaign-rfp/" + campaignId)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      updateProposedProduct: function (campaignId, obj) {
        var dfd = $q.defer();
        $http
          .put(
            config.apiPath + "/proposed-product-for-campaign/" + campaignId,
            obj
          )
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      launchCampaign: function (campaignId) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/launch-campaign/" + campaignId)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      finalizeCampaignByAdmin: function (campaignId, flag, GST) {
        var dfd = $q.defer();
        $http
          .get(
            config.apiPath +
              "/quote-campaign/" +
              campaignId +
              "/" +
              flag +
              "/" +
              GST
          )
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      confirmCampaignBooking: function (campaignId) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/confirm-campaign-booking/" + campaignId)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getCampaignPaymentDetails: function (campaignId) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/campaign-payments/" + campaignId)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      updateCampaignPayment: function (obj) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/campaign-payment", obj)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      closeCampaign: function (campaignId) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/close-campaign/" + campaignId)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      searchCampaigns: function (searchTerm) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/search-campaigns/" + searchTerm)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      saveCampaignByAdmin: function (campaign) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/non-user-campaign", campaign)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getSuggestionRequestDetails: function (campaignId) {
        var dfd = $q.defer();
        $http
          .get(
            config.apiPath +
              "/campaign-suggestion-request-details/" +
              campaignId
          )
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getChangeRequestHistory: function (campaignId) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/quote-change-request-history/" + campaignId)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      updateMetroCampaignStatus: function (obj) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/update-metro-campaigns-status", obj)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      notifyProductOwnersForQuote: function (campaignId) {
        var dfd = $q.defer();
        $http
          .get(
            config.apiPath + "/notify-product-owners-for-quote/" + campaignId
          )
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      launchMetroCampaign: function (campaignId) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/launch-metro-campaign/" + campaignId)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getCampaignsFromProducts: function (productId) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/campaigns-from-products/" + productId)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getProductUnavailableDates: function (productId) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/product-unavailable-dates/" + productId)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getApprovedProductList: function (data) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/approved-owner-products", data)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getFormatList: function (obj = null) {
        var filterData = obj != null ? "?type=" + obj.type : "?type=ooh";
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/formats" + filterData)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      downloadQuote: function (campaignId) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/download-quote/" + campaignId, {
            responseType: "arraybuffer",
          })
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      downloadRFPsearchCriteria: function (campaignId) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/rfp-search-criteria-download/" + campaignId, {
            responseType: "arraybuffer",
          })
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      downloadPdf: function (campaignId) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/download-inerstion-order/" + campaignId, {
            responseType: "arraybuffer",
          })
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      checkoutMetroCampaign: function (metroCampaignId, flag, GST) {
        var dfd = $q.defer();
        $http
          .get(
            config.apiPath +
              "/checkout-metro-campaign/" +
              metroCampaignId +
              "/" +
              flag +
              "/" +
              GST
          )
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getProductDigitalUnavailableDates: function (productId) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/digital-product-unavailable-dates", {
            product_id: productId,
          })
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      userOffers: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/get-offers", {
            skipInterceptor: true,
          })
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      rfpWithoutLogin: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/rfp-campaign-records", {
            skipInterceptor: true,
          })
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      updateRfpstatus: function (obj) {
        var dfd = $q.defer();
        $http.post(config.apiPath + '/update-rfp-status', obj).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
    },
      findForMe: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/find-for-me-list", {
            skipInterceptor: true,
          })
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getAdminCampaigns: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/get-admin-campaigns")
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      allCampaignRequests: function (products) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/bulk-shortlist-product", products)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      deleteAdminOwnerCampaign: function (campaign_id) {
        var dfd = $q.defer();
        $http
          .delete(
            config.apiPath + "/delete-admin-owner-campaign/" + campaign_id
          )
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getCampaignOffers: function (campaign_id) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/user-offer/" + campaign_id, {
            skipInterceptor: true,
          })
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      acceptRejectOffer: function (offer) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/accept-reject-offer", offer)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      updateTodoStatus: function (todoId) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/update-todolist-status", {'todolist_id': todoId})
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getChatMessages: function (campaign_id) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/get-chat-messages/" + campaign_id, {
            skipInterceptor: true,
          })
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getCreateMessages: function (payload) {
        var dfd = $q.defer();
        $http
          .post(
            config.apiPath + "/get-create-messages",
            payload
          )
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getProfile: function(){
        var dfd = $q.defer();
        $http.get(config.apiPath + '/user-profile').success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
    },
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
        1300: "RFP Campaign",
      },
    };
  },
]);
