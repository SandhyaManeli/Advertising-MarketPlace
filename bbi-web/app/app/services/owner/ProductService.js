angular.module("bbManager").factory('OwnerProductService', ['$http', '$q', 'config',
	function ($http, $q, config) {
		return {
			getApprovedProductList: function (data) {
				var dfd = $q.defer();
				$http.post(config.apiPath + '/approved-owner-products' ,data).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			changeProductPrice: function (data) {
				var dfd = $q.defer();
				$http.post(config.apiPath + '/change-product-price' ,data).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			getBulkUploadList: function () {
				var dfd = $q.defer();
				$http.get(config.apiPath + '/get-bulk-upload-images').success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			changeCampaignProductPrice: function (data) {
				var dfd = $q.defer();
				$http.post(config.apiPath + '/change-campaign-product-price' ,data).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			getSearchProductList: function (pageNo, pageSize, search) {
				var pageData = "";
				if(pageNo && pageSize){
					var pageData = "?page_no=" + pageNo + "&page_size=" + pageSize;
				}
				if(search){
					pageData += "&searchkey=" + search;
				}
				var dfd = $q.defer();
				$http.get(config.apiPath + '/search-products' + pageData).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			getProductForPage: function(pageNo){
				var dfd = $q.defer();
				$http.get(config.apiPath + '/products/' + pageNo).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			getFormatList: function () {
				var dfd = $q.defer();
				$http.get(config.apiPath + '/formats').success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			deleteProduct: function(productId){
				var dfd = $q.defer();
				$http.delete(config.apiPath + '/product/' + productId).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			deleteFormat: function(formatId){
				var dfd = $q.defer();
				$http.delete(config.apiPath + '/format/' + formatId).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			getProductDetails: function(productId){
				var dfd = $q.defer();
				$http.get(config.apiPath + '/product/' + productId).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			searchOwnerProducts: function(word){
				var dfd = $q.defer();
				$http.get(config.apiPath + '/search-owner-products/' + word).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			getRequestedProductList: function (pageNo, pageSize) {
				var pageData = "";
				if(pageNo && pageSize){
					var pageData = "?page_no=" + pageNo + "&page_size=" + pageSize;
				}
				var dfd = $q.defer();
				$http.get(config.apiPath + '/requested-hoardings-for-owner' + pageData).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			getOwnerProductReport: function(){
				var dfd = $q.defer();
				$http.get(config.apiPath + '/owner-products-report').success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			getOwnerProductDetails: function(productId){
				var dfd = $q.defer();
				$http.get(config.apiPath + '/owner-product-details/' + productId).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			shortListProductByOwner: function (productId) {
				var dfd = $q.defer();
				$http.post(config.apiPath + '/shortlistProduct', { product_id: productId }).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			getShortlistedProductsByOwner: function(){
				var dfd = $q.defer();
				$http.get(config.apiPath + '/shortlistedProducts').success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			deletedShortListedByOwner: function(productId){
				var dfd = $q.defer();
				$http.delete(config.apiPath + '/shortlistedProduct/' + productId).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			shareShortlistedProductsByOwner: function(obj){
				var dfd = $q.defer();
				$http.post(config.apiPath + '/share-shortlisted', obj).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			getApprovedProductListByDates: function(startDate, endDate){
				var pageData = "";
				if(startDate && endDate){
					var pageData = "?start_date=" + startDate + "&end_date=" + endDate;
				}
				var dfd = $q.defer();
				$http.get(config.apiPath + '/approved-owner-products' + pageData).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			getProductUnavailableDates: function(productId){
				var dfd = $q.defer();
				$http.get(config.apiPath + '/product-unavailable-dates/' + productId).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			}, 
			changeProductVisibility: function (product_id,visibility) {
				var dfd = $q.defer();
				$http.put(config.apiPath + '/product-visibility/' + product_id,visibility).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			getProductDigitalUnavailableDates : function(productId){
				var dfd = $q.defer();
				$http.post(config.apiPath + '/digital-product-unavailable-dates', {product_id: productId}).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			  },
			  createSubSeller: function(obj){
				 // console.log(obj,'obj.........') 
				var dfd = $q.defer();
				$http.post(config.apiPath + '/subseller', obj).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			getSubSeller: function () {
				var dfd = $q.defer();
				$http.get(config.apiPath + '/get-subseller-details').success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			getAllUsers: function(){
			  var dfd = $q.defer();
			  $http.get(config.apiPath + '/users').success(dfd.resolve).error(dfd.reject);
			  return dfd.promise;
			},
			getRoleDetails: function(roleId){
			  var dfd = $q.defer();
			  $http.get(config.apiPath + '/role-details/' + roleId).success(dfd.resolve).error(dfd.reject);
			  return dfd.promise;
			},
			getUserDetailsWithRoles: function(userMId){
			  var dfd = $q.defer();
			  $http.get(config.apiPath + '/user-details-with-roles/' + userMId).success(dfd.resolve).error(dfd.reject);
			  return dfd.promise;
			},
			getAllClients: function(){
			  var dfd = $q.defer();
			  $http.get(config.apiPath + '/all-clients').success(dfd.resolve).error(dfd.reject);
			  return dfd.promise;
			},
			deletedSubSeller: function(subseller_id){
				var dfd = $q.defer();
				$http.delete(config.apiPath + '/delete-subseller/' + subseller_id).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			 toggleActivationUser: function(userMID){
			var dfd = $q.defer();
			$http.get(config.apiPath + '/switch-activation-user/' + userMID).success(dfd.resolve).error(dfd.reject);
			return dfd.promise;
		  },
		  /*sendInviteToBBIUser: function(obj){ 
			  var dfd = $q.defer();
			  $http.post(config.apiPath + '/invite-bbi-user', obj).success(dfd.resolve).error(dfd.reject);
			  return dfd.promise;
			}*/
			sendInviteToBBIUser: function(obj){ 
			  var dfd = $q.defer();
			  $http.post(config.apiPath + '/invite-sub-seller', obj).success(dfd.resolve).error(dfd.reject);
			  return dfd.promise;
			},
			getSellerList: function(){
				var dfd = $q.defer();
				$http.get(config.apiPath + '/get-all-sellers').success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			getBUProductsList: function(){
				var dfd = $q.defer();
				$http.get(config.apiPath + '/get-bu-products-by-seller').success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			}
		}
	}
]);