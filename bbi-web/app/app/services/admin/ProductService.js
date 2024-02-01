angular.module("bbManager").factory('ProductService', ['$http', '$q', 'config',
	function ($http, $q, config) {
		return {
			getProductList: function (pageNo, pageSize,format,budget,product_name,start_date, end_date, ShowAvailable,dma, sort_value,sort_name) {
				var pageData = "";
				if(!format){
					format = '';
				}
				if(!budget){
					budget = '';
				}
				
				if(pageNo || pageSize || format || budget){
					pageData = "?page_no=" + pageNo + "&page_size=" + pageSize + "&format=" + format +"&budget=" + budget + "&show_available="+ShowAvailable+ "&dma="+dma ;
				}
				if(product_name){
					pageData+="&product_name=" + product_name;
				}
				if(sort_value && sort_name){
					pageData+="&sort_value=" + sort_value +"&sort_name=" +sort_name;
				}
				var stDate;
				var stDateStr;
				if (start_date) {
					stDate = new Date(start_date);
					var month = stDate.getMonth()+1;
					var date = stDate.getDate();
					var year = stDate.getFullYear();
					stDateStr = ((month<10?'0'+month:month) + '-' + (date<10?'0'+date:date) + '-' + year);
					pageData += "&start_date=" + stDateStr;
				}

				var edDate;
				var edDateStr;
				if (end_date) {
					edDate = new Date(end_date);
					var month = edDate.getMonth()+1;
					var date = edDate.getDate();
					var year = edDate.getFullYear();
					edDateStr = ((month<10?'0'+month:month) + '-' + (date<10?'0'+date:date) + '-' + year);
					pageData += "&end_date=" + edDateStr;
				}

				var dfd = $q.defer();
				$http.get(config.apiPath + '/products' + pageData).success(dfd.resolve).error(dfd.reject);
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
			getFormatList: function (obj = null) {
				var filterData = obj != null ? "?type=" + obj.type : "?type=ooh";
				var dfd = $q.defer();
				$http.get(config.apiPath + '/formats' + filterData).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			deleteProduct: function(productId){
				var dfd = $q.defer();
				$http.delete(config.apiPath + '/product/' + productId).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			getProductDetails: function(productId){
				var dfd = $q.defer();
				$http.get(config.apiPath + '/product/' + productId).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			getSellerList: function(){
				var dfd = $q.defer();
				$http.get(config.apiPath + '/get-all-sellers').success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			getBUProductsBySeller: function () {
                var dfd = $q.defer();
                $http.get(config.apiPath + '/get-bu-products-by-seller').success(dfd.resolve).error(dfd.reject);
                return dfd.promise;
            },
			deleteFormat: function(formatId){
				var dfd = $q.defer();
				$http.delete(config.apiPath + '/format/' + formatId).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			searchProducts: function(word){
				var dfd = $q.defer();
				$http.get(config.apiPath + '/search-products/' + word).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			getRequestedHoardings: function(pageNo, pageSize){
				var pageData = "";
				if(pageNo && pageSize){
					var pageData = "?page_no=" + pageNo + "&page_size=" + pageSize;
				}
				var dfd = $q.defer();
				$http.get(config.apiPath + '/requested-hoardings' + pageData).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			getMetroPackages: function(){
				var dfd = $q.defer();
				$http.get(config.apiPath + '/metro-packages').success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			searchAreas: function(query){
				var dfd = $q.defer();
				$http.get(config.apiPath + '/search-areas/' + query).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			adminPaymentDetails: function (data) {
				var dfd = $q.defer();
				$http.post(config.apiPath + '/stripePost', data).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
			changeProductVisibility: function (product_id,visibility) {
				var dfd = $q.defer();
				$http.put(config.apiPath + '/product-visibility/' + product_id,visibility).success(dfd.resolve).error(dfd.reject);
				return dfd.promise;
			},
		}
	}
]);