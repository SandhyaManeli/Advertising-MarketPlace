angular.module("bbManager").service('OwnerCampaignService',
        ['$http', '$q', 'config',
            function ($http, $q, config) {
                return {
                    getUserCampaignsForOwner: function (tab_status) {
                        var dfd = $q.defer();
                        $http.get(config.apiPath + `/user-campaigns-for-owner${tab_status ? '?tab_status=' + tab_status : ''}`).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    getCampaignWithProductsForOwner: function (campaignId) {
                        var dfd = $q.defer();
                        $http.get(config.apiPath + '/campaign-for-owner/' + campaignId).success(dfd.resolve).error(dfd.reject);
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
                    updateProposedProduct: function (campaignId, obj) {
                        var dfd = $q.defer();
                        $http.put(config.apiPath + '/proposed-product-for-campaign/' + campaignId, obj).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    finalizeCampaignByOwner: function (campaignId) {
                        var dfd = $q.defer();
                        $http.put(config.apiPath + '/quote-campaign/' + campaignId).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    saveOwnerCampaign: function (obj) {
                        var dfd = $q.defer();
                        $http.post(config.apiPath + '/non-user-campaign', obj).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    getOwnerCampaigns: function () {
                        var dfd = $q.defer();
                        $http.get(config.apiPath + '/owner-campaigns').success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    getOwnerCampaignDetails: function (campaignId) {
                        var dfd = $q.defer();
                        $http.get(config.apiPath + '/non-user-campaign/' + campaignId).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    proposeProductForCampaign: function (obj) {
                        var dfd = $q.defer();
                        $http.post(config.apiPath + '/propose-product-for-campaign', obj).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    deleteProductFromCampaign: function (campaignId, productId) {
                        var dfd = $q.defer();
                        $http.delete(config.apiPath + '/campaign/' + campaignId + '/product/' + productId).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    deleteSellerCampaign: function (campaignId) {
                        var dfd = $q.defer();
                        $http.delete(config.apiPath + '/delete-admin-owner-campaign/' + campaignId).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    bookNonUserCampaign: function (campaignId) {
                        var dfd = $q.defer();
                        $http.get(config.apiPath + '/book-non-user-campaign/' + campaignId).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    getCampaignWithPayments: function () {
                        var dfd = $q.defer();
                        $http.get(config.apiPath + '/campaigns-with-payments-owner').success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    getCampaignPaymentDetails: function (campaignId) {
                        var dfd = $q.defer();
                        $http.get(config.apiPath + '/campaign-payment-details-owner/' + campaignId).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    getOwnerFeeds: function () {
                        var dfd = $q.defer();
                        $http.get(config.apiPath + '/owner-feeds').success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    searchCampaigns: function (searchTerm) {
                        var dfd = $q.defer();
                        $http.get(config.apiPath + '/search-campaigns/' + searchTerm).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    deleteOwnerCampaign: function (campaignId) {
                        var dfd = $q.defer();
                        $http.delete(config.apiPath + '/non-user-campaign/' + campaignId).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    closeCampaign: function (campaignId) {
                        var dfd = $q.defer();
                        $http.get(config.apiPath + '/close-campaign/' + campaignId).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    getCampaignsFromProducts: function (productId) {
                        var dfd = $q.defer();
                        $http.get(config.apiPath + '/campaigns-from-products/' + productId).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    downloadQuote: function (campaignId) {
                        var dfd = $q.defer();
                        $http.get(config.apiPath + '/download-quote/' + campaignId,{responseType: 'arraybuffer'}).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    downloadPdf: function (campaignId) {
                        var dfd = $q.defer();
                        $http.get(config.apiPath + '/download-inerstion-order/' + campaignId,{responseType: 'arraybuffer'}).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    downloadOwnerReciepts: function (campaign_id) {
                        var dfd = $q.defer();
                        $http.get(config.apiPath + '/payments-info-download/' + campaign_id,{responseType: 'arraybuffer'}).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    generatepop: function (campaignId) {
                        var dfd = $q.defer();
                        $http.get(config.apiPath + '/generate-pop/' + campaignId,{responseType: 'arraybuffer'}).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    requestChangeInQuote: function (sendObj) {
                        var dfd = $q.defer();
                        $http.post(config.apiPath + '/request-quote-change', sendObj).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    allCampaignRequests: function (products) {
                        var dfd = $q.defer();
                        $http.post(config.apiPath + '/bulk-shortlist-product', products).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                    shareandDownloadCampaignToEmail: function (campaignToDownloadEmail) {
                        var dfd = $q.defer();
                        $http.post(config.apiPath + '/shareCampaigndownloadQuote', campaignToDownloadEmail).success(dfd.resolve).error(dfd.reject);
                        return dfd.promise;
                    },
                      
                    // downloadPdf: function () {
                    //   var dfd = $q.defer();
                    //    $http.get(config.apiPath + 'api/my-pdf', { responseType: 'arraybuffer' }).success(dfd.resolve).error(dfd.reject);
                    //    return dfd.promise;
                    //   },
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
					rfpSearchCriteria: function () {
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
					getRFPCampaignWithProducts: function (campaignId) {
						var dfd = $q.defer();
						$http
						  .get(config.apiPath + "/user-campaign-rfp/" + campaignId)
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
                }
            }
        ]
        );