angular.module("bbManager").factory("MapService", [
  "$http",
  "$q",
  "config",
  function ($http, $q, config) {
    var searchFilterData = {};
    return {
      mapProducts: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/map-products")
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      mapAllProducts: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/get-all-map-products")
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      mapSingleProduct: function (id) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + `/get-single-product-details?product_id=${id}`)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      mapProductsfiltered: function (params) {
        var dfd = $q.defer();
        if (params)
          if (params.search_param)
            $http
              .get(
                config.apiPath +
                  `/map-products-filter-shortlist?page_no=${params.page_params.page_no}&page_size=${params.page_params.page_size}&search_param=${params.search_param}`
              )
              .success(dfd.resolve)
              .error(dfd.reject);
          else
            $http
              .get(
                config.apiPath +
                  `/map-products-filter-shortlist?page_no=${params.page_params.page_no}&page_size=${params.page_params.page_size}`
              )
              .success(dfd.resolve)
              .error(dfd.reject);
        else
          $http
            .get(config.apiPath + `/map-products-filter-shortlist`)
            .success(dfd.resolve)
            .error(dfd.reject);
        return dfd.promise;
      },
      markers: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/products")
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      saveMarker: function () {
        markerObj = { lat: 61.62182, lng: 20.306683 };
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/product", markerObj)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getMarkers: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/products")
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      shortListProduct: function (obj) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/shortlistProduct", obj)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getshortListProduct: function (userMongoId) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/shortlistedProducts")
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      // deleteShortlistedProduct: function (productId) {
      //   var dfd = $q.defer();
      //   $http.delete(config.apiPath + '/shortlistedProduct', productId).success(dfd.resolve).error(dfd.reject);
      //   return dfd.promise;
      // },
      deleteShortlistedProduct: function (productId) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/deleteshortlistedProducts", productId)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      deleteCampaignProduct: function (product_booking_id) {
        var dfd = $q.defer();
        $http
          .post(
            config.apiPath + "/delete-multiple-products-from-campaign",
            product_booking_id
          )
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      filterProducts: function (criteria) {
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/filterProducts", criteria)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getIndustrySectors: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/Sectors")
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      getDurationSectors: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/DurationSectors")
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      searchBySiteNo: function (siteNo) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/searchBySiteNo/" + siteNo)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      searchBySecondImpression: function (secondImpression) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/searchBySecondImpression/" + secondImpression)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      searchByCpm: function (cpm) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/searchByCpm/" + cpm)
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
      getQuantity: function (prodId, startDate, endDate) {
        var dfd = $q.defer();
        $http
          .get(
            config.apiPath +
              "/product-availabilty-quantity/" +
              prodId +
              "/" +
              startDate +
              "/" +
              endDate
          )
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      },
      //   filterSaveProducts: function (postData) {
      //     var dfd = $q.defer();
      //     // Check if the postData has an 'id' property
      //     if (postData.id) {
      //         // If 'id' is present, it's an update operation
      //         $http.post(config.apiPath + '/product-search-criteria/', postData)
      //             .then(function (response) {
      //                 dfd.resolve(response.data);
      //             })
      //             .catch(function (error) {
      //                 dfd.reject(error.data);
      //             });
      //     } else {
      //         // If 'id' is not present, it's a create operation
      //         $http.post(config.apiPath + '/product-search-criteria', postData)
      //             .then(function (response) {
      //                 dfd.resolve(response.data);
      //             })
      //             .catch(function (error) {
      //                 dfd.reject(error.data);
      //             });
      //     }

      //     return dfd.promise;
      // },
      filterSaveProducts: function (postData) {
        var url = postData.id
          ? config.apiPath + "/product-search-criteria"
          : config.apiPath + "/product-search-criteria";
        return $http
          .post(url, postData)
          .then(function (response) {
            return response.data;
          })
          .catch(function (error) {
            throw error.data;
          });
      },
      getSearchFilterProduct: function () {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/product-search-filters-data")
          .success(function(data){
            searchFilterData = data;
            dfd.resolve(data);
          })
          .error(dfd.reject);
        return dfd.promise;
      },
      getStoredSearchFilterData: function () {
        return searchFilterData;
      },
      productSearchFilterById: function (id) {
        var dfd = $q.defer();
        $http
          .get(config.apiPath + "/product-search-filters-data-by-id/" + id)
          .then(function (response) {
            dfd.resolve(response.data);
          })
          .catch(function (error) {
            dfd.reject(error);
          });
        return dfd.promise;
      },
      searchAreas: function(query){
        var dfd = $q.defer();
        $http.get(config.apiPath + '/search-areas/' + query).
        success(dfd.resolve).error(dfd.reject);
        return dfd.promise;
      },

      filterCoordinatorsProducts: function(payload){
        var dfd = $q.defer();
        $http
          .post(config.apiPath + "/get-products-by-lat-long", payload)
          .success(dfd.resolve)
          .error(dfd.reject);
        return dfd.promise;
      }
    };
  },
]);
