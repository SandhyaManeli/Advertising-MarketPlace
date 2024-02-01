angular.module("bbManager").service("CampaignService", [
  "$http",
  "$q",
  "config",
  function ($http, $q, config) {
    return {
      suggestedData: {},
      getActiveUserCampaigns: function (tab_status,page_no,page_size) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + `/active-user-campaigns${tab_status ? '?tab_status=' + tab_status : ''}${page_no ? '&page_no=' + page_no : ''}${page_size ? '&page_size=' + page_size : ''}`)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getUserSavedCampaigns: function(tab_status){
        var dfd = $q.defer();
        $http
          .get(config.apiPath + `/user-saved-campaigns${tab_status ? '?tab_status=' + tab_status : ''}`)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getPaymentForUserCampaigns: function (campaignId) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/campaign-payments/" + campaignId)
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
      saveUserCampaign: function (campaign) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/user-campaign", campaign)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },

      CloneCampaignRequest: function (payLoad) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/clone-saved-campaign", payLoad)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      
      EditCampaignRequest: function (campaignID) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/update-campaign", campaignID)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      EditCampaigRequest: function (campaignID) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/update-campaign", campaignID)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      ProductIdsCampaign: function (campaign) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/products-price-in-campaigns", campaign)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      addProductToExistingCampaign: function (productCampaignBundle) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/product-to-campaign", productCampaignBundle)
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
      sendComment: function (SendComment) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/post-campaign-comment", SendComment)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getComment: function (id) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/get-campaign-comment", id)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      deleteCampaign: function (campaignId) {
        var dfd = $q.defer();
        $http
          .delete(config.apiPath + "/campaign/" + campaignId)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      shareCampaignToEmail: function (campaignToEmail) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/share-campaign", campaignToEmail)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      makeOfferCampaignTo: function (campaignOfferParams) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/offer-price", campaignOfferParams)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      deleteCampaignRequest: function (campaigndeleteParams) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/cancel-campaign", campaigndeleteParams)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      deleteProductRequest: function (campaigndeleteParam) {
        var dfd = $q.defer();
        $http
          .post(
            config.apiPath + "/request-delete-product-from-campaign",
            campaigndeleteParam
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

      updateGrossPercantage: function(grossFeeUpdate){
        var dfd = $q.defer();
        $http.post( config.apiPath +"/campaign-gross-fee-update", grossFeeUpdate)
          .success(dfd.resolve)
          .error(dfd.reject);
          return dfd.promise;
      },

      getGrossPercantage: function (campaignId) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/campaign-gross-fee-get/" + campaignId)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },

      shareShortListedProducts: function (obj) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/share-shortlisted", obj)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      deleteProductFromUserCampaign: function (campaignId, productId, price) {
        var dfd = $q.defer();
        $http
          .delete(
            config.apiPath +
              "/user-campaign/" +
              campaignId +
              "/product/" +
              productId +
              "/price/" +
              price
          )
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      deleteUserCampaign: function (campaignId) {
        var dfd = $q.defer();
        $http
          .delete(config.apiPath + "/delete-campaign/" + campaignId)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      cancelProductFromUserCampaign: function (campaignId, productId) {
        var dfd = $q.defer();
        $http
          .get(
            config.apiPath +
              "/cancel-campaign-product/" +
              campaignId +
              "/product/" +
              productId
          )
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      deleteMetroCampaign: function (campaignId) {
        var dfd = $q.defer();
        $http
          .delete(config.apiPath + "/metro-campaign/" + campaignId)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      exportCampaignsPdf: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/export-all-campaigns", {
            responseType: "arraybuffer",
          })
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      requestCampaignProposal: function (campaignId) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/request-proposal/" + campaignId)
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
      notifyUsershortlistedProduct: function (ProductIDList) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/notifyUsershortlistedProduct", ProductIDList)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      requestChangeInQuote: function (sendObj) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/request-quote-change", sendObj)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      shareMetroCampaignToEmail: function (campaignToEmail) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/share-metro-campaign", campaignToEmail)
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
      downloadPDF: function (campaignId) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/download-inerstion-order/" + campaignId, {
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
      exportCampaignCSV: function (campaignId) {
        var dfd = $q.defer();
        $http
          .get(
            config.apiPath +
              "/campaigns-buyer-export-excel-download/" +
              campaignId,
            { responseType: "arraybuffer" }
          )
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      payAndLaunch: function (campaignObj) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/pay-launch-campaign", campaignObj)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      downloadOwnerReciepts: function (campaign_id) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/payments-info-download/" + campaign_id, {
            responseType: "arraybuffer",
          })
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      userPaymentDetails: function (details) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/stripePost", details)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      deleteRequestedCampaign: function (activeUserCampaignID) {
        console.log("activeUserCampaignID", activeUserCampaignID);
        // var dfd = $q.defer();
        // $http.delete(config.apiPath +  '/metro-campaign/' + campaignId).success(dfd.resolve).error(dfd.reject);
        // return dfd.promise;
      },
      getProductDetails: function (productId) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/product/" + productId)
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
      getColumnsToExport: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/exported-columns/report_campaign", {
            skipInterceptor: true,
          })
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },

      getViewComments: function(campaignProduct){
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/offer-accept-reject-comments/" + campaignProduct)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;

      },
      saveColumnsToExport: function (payload) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/exported-columns-update", payload)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      updateProductDatesQuantity: function (payload) {
        var dfd = $q.defer();
        $http
          .post(
            config.apiPath + "/update-campaign-product-date-quantity",
            payload
          )
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      sendUnavailableMail: function (payload) {
        var dfd = $q.defer();
        $http
          .post(
            config.apiPath + "/unit-quantity-unavailable-mail",
            payload
          )
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
      sendOfferResponse: function (payload) {
        var dfd = $q.defer();
        $http.post(config.apiPath + '/offer-accept-reject-for-seller', payload)
        .success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },

      searchBuyers : function(query){
        var dfd = $q.defer();
        $http.get(config.apiPath + '/search-buyers/' + query).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      transferCampaignTo: function (transferCampaignParam) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/transfer-campaign-to-buyer", transferCampaignParam)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      findForMe: function(userData){
        var dfd = $q.defer();
        $http.post(config.apiPath + '/find-for-me', userData).success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
      getProfile: function(){
        var dfd = $q.defer();
        $http.get(config.apiPath + '/user-profile').success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },
	  getAssignedRfpProducts: function (campaign_id) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/get-assaign-rfp-products/" + campaign_id)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
    };
  },
]);
