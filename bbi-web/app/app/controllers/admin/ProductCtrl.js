angular.module("bbManager").controller("ProductCtrl", [
  "$scope",
  "$mdDialog",
  "$rootScope",
  "$stateParams",
  "$window",
  "ProductService",
  "AdminLocationService",
  "OwnerLocationService",
  "CompanyService",
  "MapService",
  "config",
  "Upload",
  "toastr",
  "$state",
  "CampaignService",
  function (
    $scope,
    $mdDialog,
    $rootScope,
    $stateParams,
    $window,
    ProductService,
    AdminLocationService,
    OwnerLocationService,
    CompanyService,
    MapService,
    config,
    Upload,
    toastr,
    $state,
    CampaignService,
  ) {
    var vm = this;
    $scope.msg = {};
    $scope.countryList = [];
    $scope.stateList = [];
    $scope.cityList = [];
    $scope.areaList = [];
    $scope.hoardingCompaniesList = [];
    $scope.isShowAvailable = false;
    $scope.item = {
      city_name: ''
    }

    /*===================
  | Pagination
  ===================*/
    $scope.pagination = {};
    $scope.pagination.pageNo = 1;
    $scope.pagination.pageSize = 15;
    $scope.pagination.pageCount = 0;
    var pageLinks = 20;
    var lowest = 1;
    var highest = lowest + pageLinks - 1;

    function createPageLinks() {
      var mid = Math.ceil(pageLinks / 2);
      if ($scope.pagination.pageCount < $scope.pagination.pageSize) {
        lowest = 1;
      } else if (
        $scope.pagination.pageNo >= $scope.pagination.pageCount - mid &&
        $scope.pagination.pageNo <= $scope.pagination.pageCount
      ) {
        lowest = $scope.pagination.pageCount - pageLinks;
        if (lowest === 0) {
          lowest = 1
        }
      } else if (
        $scope.pagination.pageNo > 0 &&
        $scope.pagination.pageNo <= pageLinks / 2
      ) {
        lowest = 1;
      } else {
        lowest = $scope.pagination.pageNo - mid + 1;
        if (lowest === 0) {
          lowest = 1
        }
      }
      highest =
        $scope.pagination.pageCount < $scope.pagination.pageSize
          ? $scope.pagination.pageCount
          : lowest + (pageLinks - 1);
      $scope.pagination.pageArray = _.range(lowest, highest + 1);
    }

    $scope.product_visibility1 = function (product_visibility, product_id){
      visibility = {};
      visibility.product_visibility = product_visibility;
      ProductService.changeProductVisibility(
        product_id,
        visibility
      ).then(function (result){
        if (result.status == 1) {
          toastr.success(result.message);
        } else {
          toastr.error(result.data.message);
        }
      });
    };

    /*===================
  | Pagination Ends
  ===================*/
    $scope.VendorName =
      JSON.parse(localStorage.loggedInUser).firstName +
      " " +
      JSON.parse(localStorage.loggedInUser).lastName;
    $scope.test = "test";
    /*
  ======== Formats section ========
  */

    // Opens the format form pop up
    $scope.showFormatForm = function (ev) {
      $mdDialog.show({
        templateUrl: "views/admin/add-format-popup.html",
        fullscreen: $scope.customFullscreen,
        clickOutsideToClose: true,
        preserveScope: true,
        scope: $scope,
      });
    };

    $scope.generateImageTemplate = function (image) {
      var imagePath = config.serverUrl + image;
      return imagePath;
    };

    // Get Formats list
    function getFormatList() {
      ProductService.getFormatList().then(function (result) {
        $scope.formatList = result;
      });
    }
    getFormatList();

    $scope.format = {};
    $scope.addFormat = function () {
      Upload.upload({
        url: config.apiPath + "/format",
        data: {
          image: $scope.files.image,
          format: $scope.format,
        },
      }).then(
        function (result) {
          if (result.data.status == 1) {
            $scope.format = {};
            toastr.success(result.data.message);
            getFormatList();
            $mdDialog.cancel();
          } else if (result.data.status == 0) {
            $scope.addFormatErrors = result.data.message;
          }
        },
        function (resp) {
          toastr.error("somthing went wrong please try again later");
        },
        function (evt) {
          var progressPercentage = parseInt((100.0 * evt.loaded) / evt.total);
        }
      );
    };

    $scope.editFormat = function (format) {
      $scope.format = format;
      $mdDialog.show({
        templateUrl: "views/admin/add-format-popup.html",
        fullscreen: $scope.customFullscreen,
        clickOutsideToClose: true,
        preserveScope: true,
        scope: $scope,
      });
    };

    $scope.deleteFormat = function (format) {
      ProductService.deleteFormat(format.id).then(function (result) {
        if (result.status == 1) {
          getFormatList();
          toastr.success(result.message);
        } else {
          toastr.error(result.message);
        }
      });
    };

    /**Bulk Upload* */
    $scope.download = [
      { name: "Select product type" },
        { name: "Static" },
        { name: "Digital" },
        { name: "Digital/Static" },
        { name: "Media"}
      ];
 $scope.staticContent=false;
  $scope.digitalContent=false;
  $scope.staticDigitalContent=false;
  $scope.mediaContent=false;
  $scope.displayErrorMsg=false;
$scope.displaySuccessMsg=false;
 $scope.productTypeDownload=function(name){
   if(name=='Static'){
  $scope.staticContent=true;
  $scope.digitalContent=false;
  $scope.staticDigitalContent=false;
  $scope.mediaContent=false;
}
else if(name=='Digital'){
  $scope.digitalContent=true;
  $scope.staticContent=false;
  $scope.staticDigitalContent=false;
  $scope.mediaContent=false;
}
else if(name=='Digital/Static'){
  $scope.staticDigitalContent=true;
  $scope.staticContent=false;
  $scope.digitalContent=false;
  $scope.mediaContent=false;
}
else if(name=='Media'){
  $scope.mediaContent=true;
  $scope.staticContent=false;
  $scope.digitalContent=false;
  $scope.staticDigitalContent=false;
}
else{
   $scope.staticContent=false;
  $scope.digitalContent=false;
  $scope.staticDigitalContent=false;
  $scope.mediaContent=false;
}
 }
 $scope.showUploadForm= false;
 $scope.displayErrorMsg=false;
$scope.displaySuccessMsg=false;
 $scope.productTypeUpload=function(type){
  $scope.displayErrorMsg=false;
$scope.displaySuccessMsg=false;
if(type=="Static" || type=="Digital" || type=="Digital/Static" || type=="Media"){
  $scope.showUploadForm= true;
}
else{
  $scope.showUploadForm= false;
}
 }
 $scope.sellerList=[];
 function getSellerList() {
  ProductService.getSellerList().then(function (result) {
    $scope.sellerList = result.sellers_data;
    // console.log($scope.sellerList)
  });
}
// Initialize an empty array to store the retrieved data
//Get BU Products By Seller
$scope.BUProductsList=[];
function getBUProductsList() {
 ProductService.getBUProductsBySeller().then(function (result) {
 $scope.BUProductsList = result;
 // console.log($scope.sellerList)
 });
}
getBUProductsList();

getSellerList();
 $scope.displayErrorMsg=false;
 $scope.displaySuccessMsg=false;
 $scope.errorMsg=[];
 $scope.excelfiles = {};
 $scope.uploadBulkExcelFile=function(excelfiles,sellerId){
  Upload.upload({
    url: config.apiPath + '/import',
    data: { 
      file:$scope.excelfiles.file,
      type:$scope.uploadProductType.name,
      seller_id:sellerId
    }
  }).then(function (result) {
    if (result.status == 200){
      $scope.errormsg = result.data;
      if (result.data.status == 1) {
        toastr.success(result.message);
        $scope.excelfiles = {};
        addbulkProduct()
      }
      else {
        $scope.errormsg = result.data;
        //toastr.error(result.message);
      }
    }
    $scope.downloadProductType=$scope.download[0];
    $scope.uploadProductType=$scope.download[0];
    $scope.showUploadForm= false;
    $scope.staticContent=false;
  $scope.digitalContent=false;
  $scope.staticDigitalContent=false;
  $scope.mediaContent=false;
  },function(errorCallback){
    if (errorCallback.status == 400) {
        $scope.errormsg = errorCallback.data;
        if (errorCallback.data.status == 1) {
          toastr.success(errorCallback.data.message);
		  if(errorCallback.data.error_status == 1){
			  $scope.displayErrorMsg=true;
			  $scope.displaySuccessMsg=true;
			  $scope.errorMsg=errorCallback.data.error_message;
			  $scope.staticContent=false;
			  $scope.digitalContent=false;
			  $scope.staticDigitalContent=false;
			  $scope.mediaContent=false;
			  $scope.downloadProductType=$scope.download[0];
			  $scope.successMsg=errorCallback.data.message;
		  }else{
			addbulkProduct()  
		  }
        }
        else {
          $scope.displayErrorMsg=true;
          $scope.errorMsg=errorCallback.data.message;
          //toastr.error(errorCallback.data.message);
          $scope.staticContent=false;
          $scope.digitalContent=false;
          $scope.staticDigitalContent=false;
          $scope.mediaContent=false;
          $scope.downloadProductType=$scope.download[0];
         // $scope.uploadProductType=$scope.download[0];
         // $scope.showUploadForm= false;
        }
    }
});
 }
    $scope.addbulkProduct = function () {
      $scope.displayErrorMsg=false;
	  $scope.displaySuccessMsg=false;
      $scope.downloadProductType=$scope.download[0];
      $scope.uploadProductType=$scope.download[0];
      $scope.showUploadForm= false;
      $scope.staticContent=false;
    $scope.digitalContent=false;
    $scope.staticDigitalContent=false;
    $scope.mediaContent=false;
      document.getElementById("bulkUpload").classList.toggle("show");
      
  }
  $scope.clearerrorMsg=function(){
    $scope.displayErrorMsg=false;
	  $scope.displaySuccessMsg=false;
  }
    /*
  ======== Formats section ends ========
  */
    $scope.searchAreas = function (query) {
      return ProductService.searchAreas(query.toLowerCase()).then(function (
        res
      ) {
        return res;
      });
    };
    $scope.applyFiltersmethod = function (product,dateType) {
      $scope.pagination.pageNo = 1;
      if (dateType == "startDate") {
         $scope.product.end_date='';
      }
      ProductService.getProductList(
        $scope.pagination.pageNo,
        $scope.pagination.pageSize,
        product.formType.name,
        product.budgetprice,
        product.product_name,
        product.start_date,
        product.end_date,
        $scope.isShowAvailable,
        $scope.item?.city_name,
      ).then(function (result) {
        $scope.productList = result.products;
				$scope.productList.map((product) => {
					const startDate = parseInt(product.from_date.$date.$numberLong);
          const endDate = parseInt(product.to_date.$date.$numberLong);
          const areaTimeZoneType = product.area_time_zone_type;
					if(areaTimeZoneType) {
						const splitStartDate = new Date(startDate)
							.toLocaleString('en-GB',{ timeZone: areaTimeZoneType}).slice(0,10).split('/');
						[splitStartDate[0], splitStartDate[1]] = [splitStartDate[1], splitStartDate[0]];
	
						const splitEndDate = new Date(endDate)
							.toLocaleString('en-GB', { timeZone: areaTimeZoneType}).slice(0,10).split('/');
						[splitEndDate[0], splitEndDate[1]] = [splitEndDate[1], splitEndDate[0]];
	
						product.from_date.$date.$numberLong = splitStartDate.join('-');
						product.to_date.$date.$numberLong = splitEndDate.join('-');
					}
        });
        $scope.pagination.pageCount = result.page_count;
        // sortByRateCard(product.budgetprice);
        createPageLinks();
      });
    };

    $scope.getNumber = function (str) {
      var num = 0;
      str = str.trim();
      if (str == "") num = 0;
      else {
        if (str.indexOf(",") > -1) str = str.replace(",", "");
        num = parseFloat(str);
      }
      return num;
    };
    function sortByRateCard(type) {
      if (type == "1") {
        $scope.productList.sort(function (a, b) {
          if ($scope.getNumber(a.rateCard) < $scope.getNumber(b.rateCard))
            return 1;
          else if ($scope.getNumber(a.rateCard) > $scope.getNumber(b.rateCard))
            return -1;
          else return 0;
        });
      } else {
        $scope.productList.sort(function (a, b) {
          if ($scope.getNumber(a.rateCard) > $scope.getNumber(b.rateCard))
            return 1;
          else if ($scope.getNumber(a.rateCard) < $scope.getNumber(b.rateCard))
            return -1;
          else return 0;
        });
      }
    }

    AdminLocationService.getCountries().then(function (result) {
      $scope.countryList = result;
    });
    CompanyService.getAllClients().then(function (result) {
      $scope.allClients = result;
    });

    $scope.getStateList = function (product) {
      AdminLocationService.getStates($scope.product.country).then(function (
        result
      ) {
        $scope.stateList = result;
      });
    };
    $scope.getCityList = function () {
      AdminLocationService.getCities($scope.product.state).then(function (
        result
      ) {
        $scope.cityList = result;
      });
    };
    $scope.getAreaList = function () {
      AdminLocationService.getAreas($scope.product.city).then(function (
        result
      ) {
        $scope.areaList = result;
      });
    };

    /*
  ======== Products section ========
  */
    $scope.prodDetails = function (productID) {
      console.log("prod id: " + productID);
      ProductService.getProductDetails(productID).then(function (result) {
        $scope.productdetails = result.product_details;
        $window.location.href =
          "/admin/product-camp-details/" + $scope.productdetails.id;
      });
    };

    $scope.getRequestedHoardings = function () {
      return new Promise((resolve, reject) => {
        ProductService.getRequestedHoardings(
          $scope.pagination.pageNo,
          $scope.pagination.pageSize
        ).then(
          (result) => {
            $scope.requestedProductList = result.products;
            $scope.pagination.pageCount = result.page_count;
            createPageLinks();
            resolve(result);
          },
          (result) => {
            reject();
          }
        );
      });
    };

    // Opens the product form popup
    $scope.showProductForm = function (ev) {
      $scope.product = {};
      $mdDialog.show({
        templateUrl: "views/admin/add-product-popup.html",
        fullscreen: $scope.customFullscreen,
        clickOutsideToClose: true,
        preserveScope: true,
        scope: $scope,
      });
    };

    // Calenders code

    // $scope.rqstHrdngsOpts = {
    //   multipleDateRanges: false,
    //   locale: {
    //     applyClass: 'btn-green',
    //     applyLabel: "Book Now",
    //     fromLabel: "From",
    //     format: "MMM-DD-YY",
    //     toLabel: "To",
    //     cancelLabel: 'X',
    //     customRangeLabel: 'Custom range'
    //   },
    //   isInvalidDate: function (dt) {
    //     for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
    //       if (moment(dt) >= moment($scope.unavailalbeDateRanges[i].booked_from) && moment(dt) <= moment($scope.unavailalbeDateRanges[i].booked_to)) {
    //         return true;
    //       }
    //     }
    //     if (moment(dt) < moment()) {
    //       return true;
    //     }
    //   },
    //   isCustomDate: function (dt) {
    //     for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
    //       if (moment(dt) >= moment($scope.unavailalbeDateRanges[i].booked_from) && moment(dt) <= moment($scope.unavailalbeDateRanges[i].booked_to)) {
    //         if (moment(dt).isSame(moment($scope.unavailalbeDateRanges[i].booked_from), 'day')) {
    //           return ['red-blocked', 'left-radius'];
    //         } else if (moment(dt).isSame(moment($scope.unavailalbeDateRanges[i].booked_to), 'day')) {
    //           return ['red-blocked', 'right-radius'];
    //         } else {
    //           return 'red-blocked';
    //         }
    //       }
    //     }
    //     if (moment(dt) < moment()) {
    //       return 'gray-blocked';
    //     }
    //   },
    //   eventHandlers: {
    //     'apply.daterangepicker': function (ev, picker) {
    //       //selectedDateRanges = [];
    //     }
    //   }
    // };
    $scope.ranges = {
      selectedDateRanges: [],
    };

    /*================================
       | Multi date range picker options
       ================================*/
    $scope.rqstHrdngsOpts = {
      multipleDateRanges: true,
      opens: "center",
      locale: {
        applyClass: "btn-green",
        applyLabel: "Select Dates",
        fromLabel: "From",
        format: "DD-MMM-YY",
        toLabel: "To",
        cancelLabel: "X",
        customRangeLabel: "Custom range",
      },
      isInvalidDate: function (dt) {
        if ($scope.unavailalbeDateRanges) {
          for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
            if (
              moment(dt) >=
              moment($scope.unavailalbeDateRanges[i].booked_from) &&
              moment(dt) <= moment($scope.unavailalbeDateRanges[i].booked_to)
            ) {
              return true;
            }
          }
        }
        let today;
        if ($scope.selectedTimezone) {
          today = moment().tz($scope.selectedTimezone).format(
            "MM/DD/YYYY"
          );
          console.log('dileep10', today)
        } else {
          today = moment().format("MM/DD/YYYY");
        }
        var isAfter = moment(today).isAfter((moment(dt).format("MM/DD/YYYY")));
        if (isAfter) {
          return true;
        }
      },
      isCustomDate: function (dt) {
        if ($scope.unavailalbeDateRanges) {
          for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
            if (
              moment(dt) >=
              moment($scope.unavailalbeDateRanges[i].booked_from) &&
              moment(dt) <= moment($scope.unavailalbeDateRanges[i].booked_to)
            ) {
              if (
                moment(dt).isSame(
                  moment($scope.unavailalbeDateRanges[i].booked_from),
                  "day"
                )
              ) {
                return ["red-blocked", "left-radius"];
              } else if (
                moment(dt).isSame(
                  moment($scope.unavailalbeDateRanges[i].booked_to),
                  "day"
                )
              ) {
                return ["red-blocked", "right-radius"];
              } else {
                return "red-blocked";
              }
            }
          }
        }
        if (moment(dt) < moment()) {
          return "gray-blocked";
        }
      },
      eventHandlers: {
        "apply.daterangepicker": function (ev, picker) {
          //selectedDateRanges = [];
          // console.log(ev);
        },
      },
    };
    /*====================================
       | Multi date range picker options end
       ====================================*/
    $scope.getProductUnavailableDates = function (product, ev) {
      if($scope.areaObj) {
        MapService.getProductUnavailableDates(product.id).then(function (
          dateRanges
        ) {
          $scope.unavailalbeDateRanges = dateRanges;
          $(ev.target).parents().eq(3).find("input").trigger("click");
        });
      } else {
        toastr.error("Please select the DMA first before selecting the date range");
      }
      // if (product.type == "Static") {
      //   MapService.getProductUnavailableDates(product.id).then(function (dateRanges) {
      //     $scope.unavailalbeDateRanges = dateRanges;
      //     $(ev.target).parents().eq(3).find('input').trigger('click');
      //   });
      // } else if (product.type == "Digital" || product.type == "Digital/Static") {
      //   MapService.getProductDigitalUnavailableDates(product.id).then(function (blockedDatesAndSlots) {
      //     $scope.unavailalbeDateRanges = [];
      //     blockedDatesAndSlots.forEach((item) => {
      //       if (item.booked_slots >= product.slots) {
      //         $scope.unavailalbeDateRanges.push(item);
      //       }
      //     })
      //     $(ev.target).parents().eq(3).find('input').trigger('click');
      //   })
      // } else {
      //   $scope.unavailalbeDateRanges = [];
      //   $(ev.target).parents().eq(3).find('input').trigger('click');
      // }
    };
    // $scope.getProductUnavailableDates = function(productId, ev){
    //   ProductService.getProductList(productId).then(function(dateRanges){
    //     $scope.unavailalbeDateRanges = dateRanges;
    //     $(ev.target).parent().parent().find('input').trigger('click');
    //   });
    // }

    // Calenders code ends

    $scope.applymethod = function (product,dateType) {
     
      ProductService.getProductList(
        $scope.pagination.pageNo,
        $scope.pagination.pageSize,
        product.formType.name,
        product.product_name,
        product.budgetprice
      ).then(function (result) {
        $scope.productList = result.products;
        $scope.pagination.pageCount = result.page_count;
        if ($window.innerWidth >= 420) {
          createPageLinks();
        } else {
          $scope.getRange(0, result.page_count);
        }
      });
    };

    // Get products list
    $scope.clearAdminProductFilt = function (product) {
      $scope.item.city_name='';
      $scope.isShowAvailable = false;
      $scope.getProductList("All");
      $scope.product = {};
      $scope.product.type = $scope.ProductTypes[0];
      $scope.product.formType = $scope.ProductTypesFilter[0];
      $scope.product.audited = "No";
    };
    /*
    $scope.getProductList = function (product) {
      $scope.searchText = null;
      ProductService.getProductList(
        $scope.pagination.pageNo,
        $scope.pagination.pageSize,
        product.formType.name,
        product.budgetprice
      ).then(function (result) {
        $scope.productList = result.products;
        $scope.pagination.pageCount = result.page_count;
        createPageLinks();
      });
    };
    */

    $scope.getProductList = function (product) {
      $scope.searchText = null;
			$rootScope.http2_loading = true;
      ProductService.getProductList(
        $scope.pagination.pageNo,
        $scope.pagination.pageSize,
        product?.formType?.name,
        product?.budgetprice,
        product?.product_name,
        product?.start_date,
        product?.end_date,
        $scope.isShowAvailable,
        $scope.item?.city_name,
        $scope.sort_name,
        $scope.sort_value
      ).then(function (result) {
        $scope.productList = result.products;
        $scope.productList.map((product) => {
          const startDate = parseInt(product.from_date.$date.$numberLong);
          const endDate = parseInt(product.to_date.$date.$numberLong);
          const areaTimeZoneType = product.area_time_zone_type;
          if(areaTimeZoneType) {
            const splitStartDate = new Date(startDate)
            .toLocaleString('en-GB',{ timeZone: areaTimeZoneType}).slice(0,10).split('/');
            [splitStartDate[0], splitStartDate[1]] = [splitStartDate[1], splitStartDate[0]];

            const splitEndDate = new Date(endDate)
              .toLocaleString('en-GB', { timeZone: areaTimeZoneType}).slice(0,10).split('/');
            [splitEndDate[0], splitEndDate[1]] = [splitEndDate[1], splitEndDate[0]];

            product.from_date.$date.$numberLong = splitStartDate.join('-');
            product.to_date.$date.$numberLong = splitEndDate.join('-');
          }
        })
        $scope.pagination.pageCount = result.page_count;
        $scope.product.formType = $scope.ProductTypesFilter[0];
        createPageLinks();
        // if ($scope.sortType == "Asc") {
        //   $scope.sortAsc(
        //     $scope.upArrowColour,
        //     $scope.upArrowColour == "siteNo" ? "string" : "number"
        //   );
        // } else if ($scope.sortType == "Dsc") {
        //   $scope.sortDsc(
        //     $scope.downArrowColour,
        //     $scope.downArrowColour == "siteNo" ? "string" : "number"
        //   );
        // } else {
        //   sortByRateCard("1"); //default sorting
        // }
        // $scope.productList.sort((prod1, prod2) => {
        //   return (prod1.from_date.$date.$numberLong - prod2.from_date.$date.$numberLong);
        // })

        // $scope.productList = [
				// 	...$scope.productList.filter((prod) =>  prod.to_date.$date.$numberLong >= Date.now()),
				// 	...$scope.productList.filter((prod) =>  prod.to_date.$date.$numberLong < Date.now()),
				// ]
			  $rootScope.http2_loading = false;
      });
    };
    $scope.getProductList();
    $scope.product = {
      budgetprice: "1",
    };

    // $scope.ProductTypes = [
    //   { name: "Bulletin" },
    //   { name: "Digital" },
    //   { name: "Transit Digital" }
    // ];
    $scope.ProductTypes = [
      {
        name: "Static",
      },
      {
        name: "Digital",
      },
      {
        name: "Digital/Static",
      },
      {
        name: "Media",
      },
    ];
    $scope.ProductTypesFilter = [
      {
        name: "All",
      },
      {
        name: "Static",
      },
      {
        name: "Digital",
      },
      {
        name: "Digital/Static",
      },
      {
        name: "Media",
      },
    ];
    $scope.Staticresult = true;
    $scope.newAgeResult = false;
    $scope.DigitalResult = false;
    $scope.DigitalStaticResult = false;
    $scope.product.type = $scope.ProductTypes[0];
    $scope.product.formType = $scope.ProductTypesFilter[0];
    $scope.getdetails = function () {
      if ($scope.product.type.name == "Static") {
        $scope.Staticresult = true;
        $scope.newAgeResult = false;
        $scope.DigitalResult = false;
        $scope.DigitalStaticResult = false;
      } else if ($scope.product.type.name == "Digital") {
        $scope.DigitalResult = true;
        $scope.newAgeResult = false;
        $scope.Staticresult = false;
        $scope.DigitalStaticResult = false;
      } else if ($scope.product.type.name == "Media") {
        $scope.newAgeResult = true;
        $scope.Staticresult = false;
        $scope.DigitalResult = false;
        $scope.DigitalStaticResult = false;
      } else if ($scope.product.type.name == "Digital/Static") {
        $scope.newAgeResult = false;
        $scope.Staticresult = false;
        $scope.DigitalResult = false;
        $scope.DigitalStaticResult = true;
      } else {
        $scope.Staticresult = false;
      }
    };
    $scope.selectSearchedArea = function () {
      if ($scope.areaObj == null) {
        toastr.error("No DMA Found");
      }
      $scope.adminProductEdit.city_name = $scope.areaObj;
      console.log($scope.areaObj.id);
    };
    $scope.files = {};

    $scope.resizeTextImg = false;
    $scope.$watch("files.image", function (ctrl) {
      var fileUpload = ctrl;
	  var invalidImageFormats = [];
	  $scope.imageFileTypes = ["image/jpg","image/jpeg"];
      if (typeof fileUpload[0] != "undefined") {
        $scope.resizeTextImg = false;
        var reader = new FileReader();
        reader.readAsDataURL(fileUpload[0]);
        reader.onload = function (e) {
          var image = new Image();
          image.src = e.target.result;
          image.onload = function () {
            var height = this.height;
            var width = this.width;
            if (width >= 1280 && height >= 960) {
				 $scope.resizeTextImg = false;
              // alert("At least you can upload a 1280 px *960 px size.");
              // return false;
			  if ($scope.files != null && $scope.files.image.length !== 0) {
					angular.forEach($scope.files.image, function (imageFile) {
						var tempObj = {};
						if (
							$scope.imageFileTypes.indexOf(imageFile.type) === -1
						) {
							//toastr.error("Please select image."); 
							toastr.error("Please select JPG/JPEG image.");
							$scope.files.image = "";
							tempObj.image = imageFile;
							invalidImageFormats.push(tempObj);
						}
					});
				}
            } 
			else {
              $scope.resizeTextImg = true;
              $scope.files.image = "";
              toastr.error(
                "Uploaded image has valid Width 1280px and Height 960px."
              );
              return true;
            }
          };
        };
      }
    });

    //Latitude and Longitude Validation
    $scope.isValidLatLong = {
      lat: true,
      lng: true
    }
    
    $scope.validateLat = function (lat) {  
      const regEx = /^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/
      if (isNaN(lat)) {
        $scope.isValidLatLong.lat = false;
      } else {
        $scope.isValidLatLong.lat = regEx.test(lat);
      }
    }

    $scope.validateLng = function (lng) { 
      const regEx = new RegExp('^(\\+|-)?((\\d((\\.)|\\.\\d{1,6})?)|(0*?\\d\\d((\\.)|\\.\\d{1,6})?)|(0*?1[0-7]\\d((\\.)|\\.\\d{1,6})?)|(0*?180((\\.)|\\.0{1,6})?))$');
      if (isNaN(lng)) {
        $scope.isValidLatLong.lng = false;
      } else {
        $scope.isValidLatLong.lng = regEx.test(lng); 
      }
    }
    //End of Latitude and Longitude Validation

    // validatations for the tax percantage
		$scope.product.tax_percentage = 0;
		$scope.percantage = 100;

		$scope.prevQuantity = 0;
		$scope.percantageHandler = function () {
		  $scope.prevQuantity = $scope.product.tax_percentage;
		}
	
		$scope.percantageChangeHandler = function() {
		  if ($scope.product.tax_percentage > $scope.percantage || $scope.product.tax_percentage < 0) {
			toastr.warning(`Please enter percantage in between 1 to 100`);        
			$scope.product.tax_percentage = $scope.prevQuantity; 
		  }
		}
    $scope.addProduct = function (product) {
      if(!$scope.checked){
      //validating the selected upload images
      var invalidImageFormats = [];
      $scope.imageFileTypes = [
        "image/jpg",
        "image/jpeg",
        //"image/png",
        //"image/gif",
        //"image/svg+xml",
      ];
      if ($scope.files!=null && $scope.files.image.length !== 0) {
        angular.forEach($scope.files.image, function (imageFile) {
          var tempObj = {};
          if ($scope.imageFileTypes.indexOf(imageFile.type) === -1) {
            //toastr.error("Please select image");
            toastr.error("Please select JPG/JPEG image");
            tempObj.image = imageFile;
            invalidImageFormats.push(tempObj);
          }
        });
      }
    }

      if (product?.dates.length == 0) {
        toastr.error("Please Select Dates");
        return;
      }
	if ($scope.areaObj == null) {
		toastr.error("No DMA Found");
	} else {
		
		/*if($scope.product.billingYesNo == 'yes'){
			$scope.product.billingYes = 'yes';
			$scope.product.billingNo = undefined;
		}else if($scope.product.billingYesNo == 'no'){
			$scope.product.billingYes = undefined;
			$scope.product.billingNo = 'no';
		}else{
			toastr.error("No Billing Found.");
		} 
		
		if($scope.product.servicingYesNo == 'yes'){
			$scope.product.servicingYes = 'yes';
			$scope.product.servicingNo = undefined;
		}else if($scope.product.servicingYesNo == 'no'){
			$scope.product.servicingYes = undefined;
			$scope.product.servicingNo = 'no';
		}else{
			toastr.error("No Servicing Found.");
		} */
		
		//save product only if there are no invalid image formats
	if (invalidImageFormats ==undefined || invalidImageFormats.length == 0) {
		product?.dates.forEach(function (item) {
            const startDate = moment(item.startDate);   
			if($scope.areaObj.area_time_zone_type != null){
				const offset = startDate.tz($scope.areaObj.area_time_zone_type).format().slice(19,25); 
				
				const finalStartDate = moment(item.startDate).format("YYYY-MM-DD")
				  + `T00:00:00.000${offset}`;
				const finalEndDate = moment(item.endDate).format("YYYY-MM-DD") 
				  +  `T23:59:59.000${offset}`;
				item.startDate = moment.utc(finalStartDate).format();
				item.endDate = moment.utc(finalEndDate).format();
			}else{
				item.startDate = moment(item.startDate).format("YYYY-MM-DD");
				item.endDate = moment(item.endDate).format("YYYY-MM-DD");
			}
          });
          // adminProductEdit.area = $scope.areaObj.id; 
          // adminProductEdit.type = adminProductEdit.type.name;
          var data = {
            image: $scope.files.image,
            // type:$scope.adminProductEdit.type,
            // bookingDates : $scope.adminProductEdit.bookingDates,
            //cancellation : $scope.product.cancellation,
            title: $scope.product.title,
            height: $scope.product.height + " " + $scope.productType,
            width: $scope.product.weight + " " + $scope.productType,
            venue: $scope.product.venue,
            address: $scope.product?.address,
            minimumdays: $scope.product.minimumdays,
            network: $scope.product.network,
            nationloc: $scope.product.nationloc,
            daypart: $scope.product.daypart,
            reach: $scope.product.reach,
            genre: $scope.product.genre,
            // costperpoint: $scope.product.costperpoint,
            length: $scope.product.length,
            city: $scope.product.city,
            direction: $scope.product.direction,
            impressions: $scope.product.impressions,
            zipcode: $scope.product.zipcode,
            state: $scope.product.state,
            audited: $scope.product.audited,
            vendor: JSON.parse(localStorage.loggedInUser),
            sellerId: $scope.product.sellerId,
            mediahhi: $scope.product.mediahhi,
            firstDay: $scope.product.firstDay,
            lastDay: $scope.product.lastDay,
            weekPeriod: $scope.noOffourweeks,
            rateCard: $scope.product.rateCard,
            fix: $scope.product.fix,
            installCost: $scope.product.installCost,
            // buses: $scope.product.buses,
            price: $scope.product.price,
            negotiatedCost: $scope.product.negotiatedCost,
            productioncost: $scope.product.productioncost,
            unitQty: $scope.product.unitQty,
            billingYes: $scope.product.billing == "yes" ? "yes" : "",
			billingNo: $scope.product.billing == "no" ? "no" : "",
			servicingYes: $scope.product.servicing == "yes" ? "yes" : "",
			servicingNo: $scope.product.servicing == "no" ? "no" : "",
            firstImpression: $scope.product.firstImpression,
            secondImpression: $scope.product.secondImpression,
            thirdImpression: $scope.product.thirdImpression,
            forthImpression: $scope.product.forthImpression,
            tax_percentage: $scope.product.tax_percentage,
            firstcpm:
              ((($scope.product.rateCard / 28) * $scope.totalnumDays) /
                $scope.dsts1) *
              1000,
            cpm:
              ((($scope.product.rateCard / 28) * $scope.totalnumDays) /
                $scope.dsts2) *
              1000,
            thirdcpm:
              ((($scope.product.rateCard / 28) * $scope.totalnumDays) /
                $scope.dsTs) *
              1000,
            forthcpm:
              ((($scope.product.rateCard / 28) * $scope.totalnumDays) /
                $scope.dsts3) *
              1000,
            cancellationPolicy: $scope.product.cancellationPolicy,
            notes: $scope.product.notes,
            cancellation_policy: $scope.product.cancellation_policy,
            cancellation_terms: $scope.product.cancellation_terms,
            imgdirection: $scope.product.imgdirection,
            Comments: $scope.product.Comments,
            lat: $scope.product.lat,
            lng: $scope.product.lng,
            lighting: $scope.product.lighting,
            placement: $scope.product.placement,
            description: $scope.product.description,
            title: $scope.product.title,
            fliplength: $scope.product.fliplength,
            // looplength: $scope.product.looplength,
            spotLength: $scope.product.spotLength,
            ageloopLength: $scope.product.ageloopLength,
            locationDesc: $scope.product.locationDesc,
            staticMotion: $scope.product.staticMotion,
            sound: $scope.product.sound,
            file_type: $scope.product.file_type,
            stripe_percent: $scope.product.stripe_percent,
            product_newMedia: $scope.product.product_newMedia,
            medium: $scope.product.medium,
            area: $scope.areaObj.id,
            type: $scope.product.type.name,
            dates: $scope.product?.dates,
            // dates : $scope.adminProductEdit.dates
            //product: JSON.parse(angular.toJson(adminProductEdit)),
            default_image_status: $scope.checked
          };
          // adminProductEdit.DemographicsAge = formdata;
          // adminProductEdit.Strengths = Strengths;
          // adminProductEdit.type = adminProductEdit.type.name;
          // adminProductEdit.area = $scope.areaObj.id;
          // if (adminProductEdit.type == "Bulletin") {
          //   var data = {
          //     image: $scope.files.image,
          //     symbol: $scope.files.symbol,
          //     product: JSON.parse(angular.toJson(adminProductEdit))
          //   }
          // } else {
          //   var data = {
          //     image: $scope.files.image,
          //     symbol: $scope.files.symbol,
          //     product: JSON.parse(angular.toJson(adminProductEdit)),
          //     booked_slots: 1
          //   }
          // }
          Upload.upload({
            url: config.apiPath + "/save-product-details",
            data: data,
          }).then(
            function (result) {
              if (result.data.status == "1") {
                $scope.getProductList();
                if ($rootScope.currStateName == "admin.requested-hoardings") {
                  $scope.getRequestedHoardings();
                }
                toastr.success(result.data.message);
                $state.reload();
              } else if (result.data.status == 0) {
                $scope.addProductErrors = result.data.message;
                toastr.error(result.data.message);
              }
              $scope.hordinglistAdminform.$setPristine();
              $scope.hordinglistAdminform.$setUntouched();
            }
            // function (resp) {
            //   toastr.error("somthing went wrong try again later");
            // },
            // function (evt) {
            //   var progressPercentage = parseInt((100.0 * evt.loaded) / evt.total);
            // }
          );
        }
      }

      function addnewProduct() {
        document.getElementById("myDropdown").classList.toggle("show");
        // document.getElementById("stripe").innerText = '5%';
        $scope.removeSelection();
      }
    };

    //SORTING FOR INVENTORY LIST
    $scope.sortAsc = function (headingName, type) {
      $scope.sort_name = headingName;
      $scope.sort_value = "Asc";
      $scope.upArrowColour = headingName;
      $scope.sortType = "Asc";
      if (type == "string") {
        $scope.newOfferData = $scope.productList.map((e) => {
          return {
            ...e,
            name: e.name,
            user_email: e.user_email,
          };
        });
        $scope.productList = [];
        $scope.productList = $scope.newOfferData.sort((a, b) => {
          console.log(a[headingName]);
          return a[headingName].localeCompare(b[headingName], undefined, {
            numeric: true,
            sensitivity: "base",
          });
        });

        // $scope.productList = $scope.newOfferData;
      }
      $scope.productList = $scope.productList.sort((a, b) => {
        if (type == "boolean") {
          return a[headingName] ? 1 : -1;
        } else {
          return a[headingName] - b[headingName];
        }
      });
      console.log($scope.productList);
    };
    $scope.sortDsc = function (headingName, type) {
      $scope.sort_name = headingName;
      $scope.sort_value = "Desc";
      $scope.downArrowColour = headingName;
      $scope.sortType = "Dsc";
      if (type == "string") {
        $scope.newOfferData = $scope.productList.map((e) => {
          return {
            ...e,
            name: e.name,
            user_email: e.user_email,
          };
        });
        $scope.productList = [];
        $scope.productList = $scope.newOfferData.sort((a, b) => {
          console.log(a[headingName]);
          return b[headingName].localeCompare(a[headingName], undefined, {
            numeric: true,
            sensitivity: "base",
          });
        });

        // $scope.RfpData = $scope.newOfferData;
      }
      $scope.productList = $scope.productList.sort((a, b) => {
        if (type == "boolean") {
          return a[headingName] ? -1 : 1;
        } else {
          return b[headingName] - a[headingName];
        }
      });
      console.log($scope.productList);
    };
	

	$scope.onAllowTransfer= function (id) {
	  
		console.log('dileep',id);
		var Existingid = $scope.selectedids.indexOf(id)
		$scope.bulkSelect = false
		if(Existingid >-1){
			$scope.selectedids.splice(Existingid, 1)
			if($scope.selectedids.length == 0) {
				$scope.bulkSelect = true
			} 
		} else {
			$scope.selectedids.push(id)
		}
	};
	
	
    $scope.TransferProduct = function () {
		$scope.displayErrorMsg=false;
		$scope.displaySuccessMsg=false;
		document.getElementById("TransferProductDiv").classList.toggle("show");
	}
	
	$scope.TransferProductSeller=function(sellerId,bulkupload_uniqueID){
		if(bulkupload_uniqueID == undefined){
			bulkupload_uniqueID = '';
		}
		Upload.upload({
			url: config.apiPath + '/products-transfer',
			data: {
				seller_id:sellerId,
				bulkupload_uniqueID: bulkupload_uniqueID,
				product_id_arr : $scope.selectedids
			}
		}).then(function (result) {
			console.log(result);
			if (result.data.status == 1) {
				$scope.getProductList();
                toastr.success(result.data.message);
                $state.reload();
			}else{
				toastr.error(result.data.message);
			}
		}),function(errorCallback){
			if (errorCallback.status == 400) {
				toastr.success(errorCallback.data.message);
			}
		}
	};
	
	
	$scope.uploadBulkExcelFile=function(excelfiles,sellerId){
		  Upload.upload({
			url: config.apiPath + '/import',
			data: { 
			  file:$scope.excelfiles.file,
			  type:$scope.uploadProductType.name,
			  seller_id:sellerId
			}
		  }).then(function (result) {
			if (result.status == 200){
			  $scope.errormsg = result.data;
			  if (result.data.status == 1) {
				toastr.success(result.message);
				$scope.excelfiles = {};
				addbulkProduct()
			  }
			  else {
				$scope.errormsg = result.data;
				//toastr.error(result.message);
			  }
			}
			$scope.downloadProductType=$scope.download[0];
			$scope.uploadProductType=$scope.download[0];
			$scope.showUploadForm= false;
			$scope.staticContent=false;
		  $scope.digitalContent=false;
		  $scope.staticDigitalContent=false;
		  $scope.mediaContent=false;
		  },function(errorCallback){
			if (errorCallback.status == 400) {
				$scope.errormsg = errorCallback.data;
				if (errorCallback.data.status == 1) {
				  toastr.success(errorCallback.data.message);
				  if(errorCallback.data.error_status == 1){
					  $scope.displayErrorMsg=true;
					  $scope.displaySuccessMsg=true;
					  $scope.errorMsg=errorCallback.data.error_message;
					  $scope.staticContent=false;
					  $scope.digitalContent=false;
					  $scope.staticDigitalContent=false;
					  $scope.mediaContent=false;
					  $scope.downloadProductType=$scope.download[0];
					  $scope.successMsg=errorCallback.data.message;
				  }else{
					addbulkProduct()  
				  }
				}
				else {
				  $scope.displayErrorMsg=true;
				  $scope.errorMsg=errorCallback.data.message;
				  //toastr.error(errorCallback.data.message);
				  $scope.staticContent=false;
				  $scope.digitalContent=false;
				  $scope.staticDigitalContent=false;
				  $scope.mediaContent=false;
				  $scope.downloadProductType=$scope.download[0];
				 // $scope.uploadProductType=$scope.download[0];
				 // $scope.showUploadForm= false;
				}
			}
		});
	}

    //SORTING FOR INVENTORY LIST ENDS

    $scope.resetProduct = function () {
      $scope.isValidLatLong.lat = true;
      $scope.isValidLatLong.lng = true;
      $scope.hordinglistAdminform.$setPristine();
      $scope.hordinglistAdminform.$setUntouched();
      document.getElementById("myDropdown").classList.toggle("show");
      //  console.log($scope.ProductTypes);
      $scope.product.title = "";
      $scope.product.type = $scope.ProductTypes[0];
      $scope.areaObj = null;
      $scope.files.image = "";
      $scope.product.stripe_percent = "5";
      $scope.product.title = "";
      $scope.product.address = "";
      $scope.product.city = "";
      $scope.product.state = "";
      $scope.product.zipcode = "";
      $scope.product.height = "";
      $scope.product.weight = "";
      $scope.product.audited = "No";
      $scope.product.sellerId = "";
      $scope.product.mediahhi = "";
      $scope.product.rateCard = "";
      $scope.product.dates = "";
      ($scope.product.fix = ""),
        ($scope.product.minimumdays = ""),
        ($scope.product.network = ""),
        ($scope.product.nationloc = ""),
        ($scope.product.reach = ""),
        ($scope.product.daypart = ""),
        ($scope.product.genre = ""),
        //  $scope.product.costperpoint = '',
        ($scope.product.length = ""),
        ($scope.product.installCost = "");
      $scope.product.negotiatedCost = "";
      $scope.product.productioncost = "";
      $scope.product.unitQty = "";
      $scope.product.tax_percentage,
      $scope.product.billingYes = "";
      $scope.product.billingNo = "";
      $scope.product.servicingYes = "";
      $scope.product.servicingNo = "";
      $scope.product.firstImpression = "";
      $scope.product.secondImpression = "";
      $scope.product.thirdImpression = "";
      $scope.product.forthImpression = "";
      $scope.product.imgdirection = "";
      $scope.product.cancellation_policy = "";
      $scope.product.cancellation_terms = "";
      $scope.product.notes = "";
      $scope.product.description = "";
      $scope.product.direction = "";
      $scope.product.lat = "";
      $scope.product.lng = "";
      $scope.product.lighting = "";
      $scope.product.placement = "";
      $scope.product.Comments = "";
      $scope.product.fliplength = "";
      //  $scope.product.looplength = '';
      $scope.product.spotLength = "";
      $scope.product.ageloopLength = "";
      $scope.product.medium = "";
      $scope.product.product = "";
      $scope.product.fileType = "";
      $scope.product.locationDesc = "";
      $scope.product.staticMotion = "";
      $scope.product.sound = "";
      $scope.product.type = $scope.ProductTypes[0];
    };

    $scope.ranges = {
      selectedDateRanges: [],
    };
    $scope.customOptions = {};
    $scope.removeSelection = function () {
      $scope.customOptions.clearSelection();
    };
    $scope.$on("removeSelection", function () {
      $scope.removeSelection();
    });

    $scope.$watch("product.dates", function () {
      //$scope.totalPriceUserSelected = 0;
      $scope.totalnumDays = 0;
      $scope.noOffourweeks = 0;
      //var productPerDay = $scope.product.rateCard / 28;
      for (item in $scope.product?.dates) {
        var startDate = moment($scope.product?.dates[item].startDate).format(
          "MM-DD-YYYY"
        );
        var endDate = moment($scope.product?.dates[item].endDate).format(
          "MM-DD-YYYY"
        );
        var totalDays = moment(endDate).diff(startDate, "days") + 1;
        $scope.totalnumDays = $scope.totalnumDays + totalDays;
        $scope.noOffourweeks = $scope.totalnumDays / 28;

        // $scope.totalPriceUserSelected = productPerDay * $scope.totalnumDays;
      }
      if (
        $scope.product.firstImpression != null ||
        $scope.product.firstImpression != undefined
      ) {
        $scope.dsts1 =
          Math.floor($scope.product.firstImpression / 7) * $scope.totalnumDays;
      }
      if (
        $scope.product.secondImpression != null ||
        $scope.product.secondImpression != undefined
      ) {
        $scope.dsts2 =
          Math.floor($scope.product.secondImpression / 7) * $scope.totalnumDays;
      }
      if (
        $scope.product.thirdImpression != null ||
        $scope.product.thirdImpression != undefined
      ) {
        $scope.dsTs =
          Math.floor($scope.product.thirdImpression / 7) * $scope.totalnumDays;
      }
      if (
        $scope.product.forthImpression != null ||
        $scope.product.forthImpression != undefined
      ) {
        $scope.dsts3 =
          Math.floor($scope.product.forthImpression / 7) * $scope.totalnumDays;
      }
    });

    $scope.$watch("product.firstImpression", function () {
      $scope.dsts1 =
        Math.floor($scope.product.firstImpression / 7) * $scope.totalnumDays;
    });
    $scope.$watch("product.secondImpression", function () {
      $scope.dsts2 =
        Math.floor($scope.product.secondImpression / 7) * $scope.totalnumDays;
    });
    $scope.$watch("product.thirdImpression", function () {
      $scope.dsTs =
        Math.floor($scope.product.thirdImpression / 7) * $scope.totalnumDays;
    });
    $scope.$watch("product.forthImpression", function () {
      $scope.dsts3 =
        Math.floor($scope.product.forthImpression / 7) * $scope.totalnumDays;
    });

    $scope.$watch("adminProductEdit.firstImpression", function () {
      if (
        $scope.adminProductEdit?.firstImpression != null ||
        $scope.adminProductEdit?.firstImpression != undefined
      ) {
        $scope.oneAdminProd =
          Math.floor(+$scope.adminProductEdit.firstImpression / 7) *
          $scope.totalnumDays;
      }
    });
    $scope.$watch("adminProductEdit.secondImpression", function () {
      if (
        $scope.adminProductEdit?.secondImpression != null ||
        $scope.adminProductEdit?.secondImpression != undefined
      ) {
        $scope.oneAdminProd2 =
          Math.floor($scope.adminProductEdit.secondImpression / 7) *
          $scope.totalnumDays;
      }
    });
    $scope.$watch("adminProductEdit.thirdImpression", function () {
      if (
        $scope.adminProductEdit?.thirdImpression != null ||
        $scope.adminProductEdit?.thirdImpression != undefined
      ) {
        $scope.oneAdminProd3 =
          Math.floor($scope.adminProductEdit.thirdImpression / 7) *
          $scope.totalnumDays;
      }
    });
    $scope.$watch("adminProductEdit.forthImpression", function () {
      if (
        $scope.adminProductEdit?.forthImpression != null ||
        $scope.adminProductEdit?.forthImpression != undefined
      ) {
        $scope.oneAdminProd4 =
          Math.floor($scope.adminProductEdit.forthImpression / 7) *
          $scope.totalnumDays;
      }
    });
    $scope.$watch("adminProductEdit.dates", function () {
      //$scope.totalPriceUserSelected = 0;
      $scope.totalnumDays = 0;
      $scope.noOffourweeks = 0;
      var startDate;
      var endDate;
      // if ($scope.adminProductEdit) {
      //var productPerDay = $scope.product.rateCard / 28;
     
      for (item in $scope.product?.dates) {
        var startDate = moment($scope.product?.dates[item].startDate).format(
          "MM-DD-YYYY"
        );
        var endDate = moment($scope.product?.dates[item].endDate).format(
          "MM-DD-YYYY"
        );
        var totalDays = moment(endDate).diff(startDate, "days") + 1;
        $scope.totalnumDays = $scope.totalnumDays + totalDays;
        $scope.noOffourweeks = $scope.totalnumDays / 28;

        // $scope.totalPriceUserSelected = productPerDay * $scope.totalnumDays;
      }
      if (startDate == undefined && endDate == undefined) {
        startDate = $scope.fromTime;
        endDate = $scope.endTime;
      }
      //var totalDays = moment(endDate).diff(startDate, "days") + 1;
      //$scope.totalnumDays = $scope.totalnumDays + totalDays;
     // $scope.noOffourweeks = $scope.totalnumDays / 28;
      if (
        $scope.adminProductEdit?.firstImpression != null ||
        $scope.adminProductEdit?.firstImpression != undefined
      ) {
        $scope.oneAdminProd =
          Math.floor($scope.adminProductEdit.firstImpression / 7) *
          $scope.totalnumDays;
      }
      if (
        $scope.adminProductEdit.secondImpression != null ||
        $scope.adminProductEdit.secondImpression != undefined
      ) {
        $scope.oneAdminProd2 =
          Math.floor($scope.adminProductEdit.secondImpression / 7) *
          $scope.totalnumDays;
      }
      if (
        $scope.adminProductEdit.thirdImpression != null ||
        $scope.adminProductEdit.thirdImpression != undefined
      ) {
        $scope.oneAdminProd3 =
          Math.floor($scope.adminProductEdit.thirdImpression / 7) *
          $scope.totalnumDays;
      }
      if (
        $scope.adminProductEdit.forthImpression != null ||
        $scope.adminProductEdit.forthImpression != undefined
      ) {
        $scope.oneAdminProd4 =
          Math.floor($scope.adminProductEdit.forthImpression / 7) *
          $scope.totalnumDays;
      }
      // }
    });

    $scope.$watch("adminProductEdit.rateCard", function () {
      //$scope.totalPriceUserSelected = 0;
      $scope.totalnumDays = 0;

      //var productPerDay = $scope.product.rateCard / 28;

      var startDate = $scope.fromTime;
      var endDate = $scope.endTime;
      var totalDays = moment(endDate).diff(startDate, "days") + 1;
      $scope.totalnumDays = $scope.totalnumDays + totalDays;
      $scope.noOffourweeks = $scope.totalnumDays / 28;

      if ($scope.adminProductEdit) {
        if (
          $scope.adminProductEdit.firstImpression != null ||
          $scope.adminProductEdit.firstImpression != undefined
        ) {
          $scope.oneAdminProd =
            Math.floor($scope.adminProductEdit.firstImpression / 7) *
            $scope.totalnumDays;
        }
        if (
          $scope.adminProductEdit.secondImpression != null ||
          $scope.adminProductEdit.secondImpression != undefined
        ) {
          $scope.oneAdminProd2 =
            Math.floor($scope.adminProductEdit.secondImpression / 7) *
            $scope.totalnumDays;
        }
        if (
          $scope.adminProductEdit.thirdImpression != null ||
          $scope.adminProductEdit.thirdImpression != undefined
        ) {
          $scope.oneAdminProd3 =
            Math.floor($scope.adminProductEdit.thirdImpression / 7) *
            $scope.totalnumDays;
        }
        if (
          $scope.adminProductEdit.forthImpression != null ||
          $scope.adminProductEdit.forthImpression != undefined
        ) {
          $scope.oneAdminProd4 =
            Math.floor($scope.adminProductEdit.forthImpression / 7) *
            $scope.totalnumDays;
        }
      }
    });

    // strip-payment
    //   var stripe = Stripe('pk_test_n5DnV8z3P2j751J2JqDyeX6r006Om1UNEK');
    // var elements = stripe.elements();

    // var card = elements.create('card', {
    //   hidePostalCode: true,
    //   style: {
    //     base: {
    //       iconColor: '#666EE8',
    //       color: '#31325F',
    //       lineHeight: '40px',
    //       fontWeight: 300,
    //       fontFamily: 'Helvetica Neue',
    //       fontSize: '15px',

    //       '::placeholder': {
    //         color: '#CFD7E0',
    //       },
    //     },
    //   }
    // });
    //  card.mount('#card-element');

    // function setOutcome(result) {
    //   var successElement = document.querySelector('.success');
    //   var errorElement = document.querySelector('.error');
    //   successElement.classList.remove('visible');
    //   errorElement.classList.remove('visible');

    //   if (result.token) {
    //     successElement.querySelector('.token').textContent = result.token.id;
    //     successElement.classList.add('visible');
    //     var form = document.querySelector('form');
    //     form.querySelector('input[name="token"]').setAttribute('value', result.token.id);
    //     // form.submit();
    //   } else if (result.error) {
    //     errorElement.textContent = result.error.message;
    //     errorElement.classList.add('visible');
    //   }
    // }

    // card.on('change', function(event) {
    //   setOutcome(event);
    // });

    // document.querySelector('form').addEventListener('submit', function(e) {
    //   e.preventDefault();
    //   var options = {
    //     name: document.getElementById('first-name').value + " " + document.getElementById('last-name').value,
    //     address_line1: document.getElementById('address-line1').value,
    //     address_line2: document.getElementById('address-line2').value,
    //     address_city: document.getElementById('address-city').value,
    //     address_state: document.getElementById('address-state').value,
    //     address_zip: document.getElementById('address-zip').value,
    //     address_country: document.getElementById('address-country').value,
    //   };
    //   stripe.createToken(card, options).then(setOutcome);
    //});

    // strip payment
    // $scope.paymentDetails = function(event){
    //   ProductService.adminPaymentDetails(event).then(function (result) {
    //     if (result.status == 1) {
    //       toastr.success(result.message);
    //     } else {
    //       toastr.error(result.data.message);
    //     }
    //   });
    // }

     // validatations for the tax percantage
			$scope.percantage = 100;
			$scope.prevQuantity = 0;
			$scope.percantagesHandling = function () {
			  $scope.prevQuantity = $scope.adminProductEdit.tax_percentage;
			}
		
			$scope.percantageChangeHandling = function() {
			  if ($scope.adminProductEdit.tax_percentage > $scope.percantage || $scope.adminProductEdit.tax_percentage < 0) {
				toastr.warning(`Please enter percantage in between 1 to 100`);        
				$scope.adminProductEdit.tax_percentage = $scope.prevQuantity; 
			  }
			}

    $scope.addEditedProduct = function (adminProductEdit) {
      if(!$scope.checked){
      var invalidImageFormat = [];
      $scope.imageFileTypes = [
        "image/jpg",
        "image/jpeg",
        //"image/png",
        //"image/gif",
        //"image/svg+xml",
      ];
      if (
        $scope.files!=null &&
        $scope.files &&
        $scope.files.image &&
        $scope.files.image.length !== 0
      ) {
        angular.forEach($scope.files.image, function (imageFile) {
          var tempObj = {};
          if ($scope.imageFileTypes.indexOf(imageFile.type) === -1) {
            //toastr.error("Please select image");
            toastr.error("Please select JPG/JPEG image");
            tempObj.image = imageFile;
            invalidImageFormat.push(tempObj);
          }
        });
      }
    }  
      if ($scope.adminProductEdit.city_name == null) {
        toastr.error("No DMA Found");
      } else {
        if ( invalidImageFormat ==undefined || invalidImageFormat.length == 0) {
          adminProductEdit?.dates.forEach(function (item) {
            const startDate = moment(item.startDate);   
            const offset = startDate.tz(adminProductEdit.area_time_zone_type).format().slice(19,25); 

            const finalStartDate = moment(item.startDate).format("YYYY-MM-DD")
              + `T00:00:00.000${offset}`;
            item.startDate = moment.utc(finalStartDate).format()
            const finalEndDate = moment(item.endDate).format("YYYY-MM-DD") 
              +  `T23:59:59.000${offset}`;
            item.endDate = moment.utc(finalEndDate).format();
          });
          var data = {
            image: $scope.files.image,
            //cancellation : $scope.adminProductEdit.cancellation,
            id: $scope.adminProductEdit.id,
            title: $scope.adminProductEdit.title,
            height: $scope.adminProductEdit.height,
            width: $scope.adminProductEdit.width,
            venue: $scope.adminProductEdit.venue,
            address: $scope.adminProductEdit?.address,
            minimumdays: $scope.adminProductEdit.minimumdays,
            network: $scope.adminProductEdit.network,
            nationloc: $scope.adminProductEdit.nationloc,
            daypart: $scope.adminProductEdit.daypart,
            reach: $scope.adminProductEdit.reach,
            genre: $scope.adminProductEdit.genre,
            // costperpoint: $scope.adminProductEdit.costperpoint,
            length: $scope.adminProductEdit.length,
            city: $scope.adminProductEdit.city,
            fix: $scope.adminProductEdit.fix,
            stripe_percent: $scope.adminProductEdit.stripe_percent,
            direction: $scope.adminProductEdit.direction,
            impressions: $scope.adminProductEdit.impressions,
            zipcode: $scope.adminProductEdit.zipcode,
            state: $scope.adminProductEdit.state,
            audited: $scope.adminProductEdit.audited,
            vendor: $scope.adminProductEdit.vendor,
            sellerId: $scope.adminProductEdit.sellerId,
            mediahhi: $scope.adminProductEdit.mediahhi,
            firstDay: $scope.adminProductEdit.firstDay,
            lastDay: $scope.adminProductEdit.lastDay,
            weekPeriod:
              $scope.noOffourweeks == 0
                ? $scope.adminProductEdit.weekPeriod
                : $scope.noOffourweeks,
            rateCard: $scope.adminProductEdit.rateCard,
            installCost: $scope.adminProductEdit.installCost,
            buses: $scope.adminProductEdit.buses,
            price: $scope.adminProductEdit.price,
            negotiatedCost: $scope.adminProductEdit.negotiatedCost,
            productioncost: $scope.adminProductEdit.productioncost,
            unitQty: $scope.adminProductEdit.unitQty,
            billingYes: $scope.adminProductEdit.billing
              ? $scope.adminProductEdit.billing == "yes"
                ? "yes"
                : ""
              : $scope.adminProductEdit.billingYes,
            billingNo: $scope.adminProductEdit.billing
              ? $scope.adminProductEdit.billing == "no"
                ? "no"
                : ""
              : $scope.adminProductEdit.billingNo,
            servicingYes: $scope.adminProductEdit.servicing
              ? $scope.adminProductEdit.servicing == "yes"
                ? "yes"
                : ""
              : $scope.adminProductEdit.servicingYes,
            servicingNo: $scope.adminProductEdit.servicing
              ? $scope.adminProductEdit.servicing == "no"
                ? "no"
                : ""
              : $scope.adminProductEdit.servicingNo,
            firstImpression: $scope.adminProductEdit.firstImpression,
            secondImpression: $scope.adminProductEdit.secondImpression,
            thirdImpression: $scope.adminProductEdit.thirdImpression,
            forthImpression: $scope.adminProductEdit.forthImpression,
            firstcpm:
              $scope.oneAdminProd == 0
                ? $scope.adminProductEdit.firstcpm
                : ((($scope.adminProductEdit.rateCard / 28) *
                    $scope.totalnumDays) /
                    $scope.oneAdminProd) *
                  1000,
            cpm:
              $scope.oneAdminProd2 == 0
                ? $scope.adminProductEdit.cpm
                : ((($scope.adminProductEdit.rateCard / 28) *
                    $scope.totalnumDays) /
                    $scope.oneAdminProd2) *
                  1000,
            thirdcpm:
              $scope.oneAdminProd3 == 0
                ? $scope.adminProductEdit.thirdcpm
                : ((($scope.adminProductEdit.rateCard / 28) *
                    $scope.totalnumDays) /
                    $scope.oneAdminProd3) *
                  1000,
            forthcpm:
              $scope.oneAdminProd4 == 0
                ? $scope.adminProductEdit.forthcpm
                : ((($scope.adminProductEdit.rateCard / 28) *
                    $scope.totalnumDays) /
                    $scope.oneAdminProd4) *
                  1000,
            cancellation_policy: $scope.adminProductEdit.cancellation_policy,
            cancellation_terms: $scope.adminProductEdit.cancellation_terms,
            imgdirection: $scope.adminProductEdit.imgdirection,
            notes: $scope.adminProductEdit.notes,
            Comments: $scope.adminProductEdit.Comments,
            lat: $scope.adminProductEdit.lat,
            lng: $scope.adminProductEdit.lng,
            lighting: $scope.adminProductEdit.lighting,
            placement: $scope.adminProductEdit.placement,
            description: $scope.adminProductEdit.description,
            title: $scope.adminProductEdit.title,
            fliplength: $scope.adminProductEdit.fliplength,
            // looplength: $scope.adminProductEdit.looplength,
            spotLength: $scope.adminProductEdit.spotLength,
            ageloopLength: $scope.adminProductEdit.ageloopLength,
            locationDesc: $scope.adminProductEdit.locationDesc,
            staticMotion: $scope.adminProductEdit.staticMotion,
            sound: $scope.adminProductEdit.sound,
            file_type: $scope.adminProductEdit.file_type,
            product_newMedia: $scope.adminProductEdit.product_newMedia,
            medium: $scope.adminProductEdit.medium,
            area: $scope.areaObj.id,
            type: $scope.adminProductEdit.type.name,
            dates: $scope.adminProductEdit?.dates,
            tax_percentage: $scope.adminProductEdit.tax_percentage,
            default_image_status: $scope.checked
          };
          Upload.upload({
            url: config.apiPath + "/save-product-details",
            data: data,
          }).then(function (result) {
            if (result.data.status == "1") {
              $scope.getProductList();
              if ($rootScope.currStateName == "admin.requested-hoardings") {
                $scope.getRequestedHoardings();
              }
              toastr.success(result.data.message);
              $window.location.href = "/admin/hoarding-list";
              $mdDialog.hide();
            } else if (result.data.status == 0) {
              $scope.addProductErrors = result.data.message;
            }
            $scope.hordinglistEditform.$setPristine();
            $scope.hordinglistEditform.$setUntouched();
          });
        }
      }
    };
    $scope.searchableAreas = function (query) {
      return OwnerLocationService.searchAreas(query.toLowerCase()).then(
        function (res) {
          return res;
        }
      );
    };
    //     function addnewProduct() {
    //       document.getElementById("hoardingDrop").classList.toggle("show");
    // }
    $scope.editProduct = function (product) {
      // if (product.status != 0) {
      //   product.country = null;
      //   product.state = null;
      //   product.city = null;
      //   product.area = null;
      //   product.company = null;
      // }
      //debugger;
      $scope.selectedTimezone = product.area_time_zone_type;
      $scope.isValidLatLong.lat = true;
      $scope.isValidLatLong.lng = true;
      $scope.flag = 1;
      $scope.checked = false;
      if (product.default_image_status) {
        $scope.checked = true;
      }
      $scope.adminProductEdit = product;
      $scope.areaObj = $scope.adminProductEdit.city_name;
      var fromtime = $scope.adminProductEdit.from_date.$date.$numberLong;
      $scope.fromTime = moment(new Date(+fromtime)).format("YYYY-MM-DD");
      var endTime = $scope.adminProductEdit.to_date.$date.$numberLong;
      $scope.endTime = moment(new Date(+endTime)).format("YYYY-MM-DD");
      $scope.location =
        $scope.adminProductEdit.area_name +
        ", " +
        $scope.adminProductEdit.city_name +
        ", " +
        $scope.adminProductEdit.country_name;
      $mdDialog.show({
        templateUrl: "views/admin/add-product-popup.html",
        fullscreen: $scope.customFullscreen,
        clickOutsideToClose: true,
        preserveScope: true,
        scope: $scope,
      });
    };

    $scope.deleteProduct = function (product) {
      ProductService.deleteProduct(product.id).then(function (result) {
        if (result.status == 1) {
          toastr.success(result.message);
          $scope.getProductList();
        } else {
          toastr.error(result.message);
        }
      });
    };

    $scope.$watch("areaObj", function () {
      if ($scope.areaObj?.city_id == "5f1a633aec7e5") {
        ($scope.product.address = $scope.areaObj.name),
          ($scope.product.city = $scope.areaObj.city_name),
          ($scope.product.state = $scope.areaObj.state_name),
          ($scope.product.zipcode = $scope.areaObj.pincode),
          ($scope.product.lat = $scope.areaObj.lat),
          ($scope.product.lng = $scope.areaObj.lng);
      } else {
        ($scope.product.address = ""),
          ($scope.product.city = ""),
          ($scope.product.state = ""),
          ($scope.product.zipcode = ""),
          ($scope.product.lat = ""),
          ($scope.product.lng = "");
      }
    });

    $scope.$watch("areaObj", function () {
      if ($scope.areaObj.area_time_zone_type) {
				$scope.selectedTimezone = $scope.areaObj.area_time_zone_type;
			}
      if ($scope.areaObj?.city_id == "5f1a633aec7e5") {
        ($scope.adminProductEdit.address = $scope.areaObj.name),
          ($scope.adminProductEdit.city = $scope.areaObj.city_name),
          ($scope.adminProductEdit.state = $scope.areaObj.state_name),
          ($scope.adminProductEdit.zipcode = $scope.areaObj.pincode),
          ($scope.adminProductEdit.lat = $scope.areaObj.lat),
          ($scope.adminProductEdit.lng = $scope.areaObj.lng);
      } else if ($scope.flag != 1) {
        ($scope.adminProductEdit.address = ""),
          ($scope.adminProductEdit.city = ""),
          ($scope.adminProductEdit.state = ""),
          ($scope.adminProductEdit.zipcode = ""),
          ($scope.adminProductEdit.lat = ""),
          ($scope.adminProductEdit.lng = "");
      }
      $scope.flag++;
    });

    $scope.viewProductImage = function (image) {
      var imagePath = config.serverUrl + image;
      $mdDialog.show({
        locals: {
          src: imagePath,
        },
        templateUrl: "views/image-popup-large.html",
        fullscreen: $scope.customFullscreen,
        clickOutsideToClose: true,
        controller: function ($scope, src) {
          $scope.img_src = src;
        },
        resolve: { 
          imageCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load('./controllers/ImageCtrl.js');
          }],
        }
      });
    };

    // $scope.simulateQuery = false;
    $scope.isDisabled = false;
    // $scope.querySearch   = querySearch;
    // $scope.selectedItemChange = selectedItemChange;
    // $scope.searchTextChange   = searchTextChange;

    $scope.productSearch = function (query) {
      return ProductService.searchProducts(query.toLowerCase()).then(function (
        res
      ) {
        $scope.productList = res;
        $scope.pagination.pageCount = 1;
        return res;
      });
    };

    $scope.viewSelectedProduct = function (product) {
      $scope.pagination.pageCount = 1;
      $scope.productList = [product];
    };

    function selectedItemChange(item) {
      //console.log('Item changed to ' + JSON.stringify(item));
    }
    /*===================
    Form Age  ===================*/

    $scope.FromTo = [
      {
        id: "From",
        name: "From",
      },
    ];

    $scope.addNewFromTo = function () {
      var newItemNo = $scope.FromTo.length + 1;
      $scope.FromTo.push({
        id: "From" + newItemNo,
        name: "From ",
        id: "To" + newItemNo,
        name2: "To ",
      });
    };
    //  $scope.removeNewChoice = function() {
    //    var newItemNo = $scope.FromTo.length-1;
    //    if ( newItemNo !== 0 ) {
    //     $scope.FromTo.pop();
    //    }
    //  };
    $scope.showAddFromTo = function (from) {
      return from.id === $scope.FromTo[$scope.FromTo.length - 1].id;
    };

    /*===================
    Colipos  ===================*/
    $scope.Strengths = [
      {
        id: "Strength 1",
        name: "Strength 1",
      },
    ];

    $scope.addNewChoice = function () {
      var newItemNo = $scope.Strengths.length + 1;
      $scope.Strengths.push({
        id: "Strength" + newItemNo,
        name: "Strength " + newItemNo,
      });
    };
    //  $scope.removeNewChoice = function() {
    //    var newItemNo = $scope.Strengths.length-1;
    //    if ( newItemNo !== 0 ) {
    //     $scope.Strengths.pop();
    //    }
    //  };
    $scope.showAddChoice = function (strength) {
      return strength.id === $scope.Strengths[$scope.Strengths.length - 1].id;
    };

    /*
  ======== Products section ends ========
  */

    $scope.cancel = function () {
      $mdDialog.cancel();
    };
    var getAdminProductDetails = function (productId) {
      $rootScope.loading = true;
      ProductService.getProductDetails(productId).then(function (result) {
        $scope.productDetails = result.product_details;
        $rootScope.loading = false;
      });
    };

    if ($rootScope.currStateName == "admin.product-camp-details") {
      if (typeof $stateParams.productId !== "undefined") {
        getAdminProductDetails($stateParams.productId);
      } else {
        toastr.error("Product not found.");
      }
    }
    // tables code start
    // var vm = $scope;
    // vm.limit = 5;
    // $scope.loadMore = function() {
    //   var increamented = vm.limit + 5;
    //   vm.limit = increamented > $scope.hoardinglistdata.length ? $scope.hoardinglistdata.length : increamented;
    // };
    // tables code end

    // var callAndWait = function(fn){
    //   return new Promise((resolve, reject) => {
    //     setTimeout(function(){
    //       fn();
    //       resolve();
    //     });
    //   });
    // }
    if ($rootScope.currStateName == "admin.hoarding-list") {
      $scope.getdetails();
    }
    if ($rootScope.currStateName == "admin.product-camp-details") {
      $scope.getProductList($stateParams.productId);
    }
    if ($rootScope.currStateName == "admin.cloneproduct-details") {
      getAdminProductDetails($stateParams.id);
    }
    if ($rootScope.currStateName == "admin.requested-hoardings") {
      if ($stateParams.productId) {
        $scope.getRequestedHoardings().then((requestedProducts) => {
          var product = _.filter(requestedProducts.products, function (prod) {
            return prod.id == $stateParams.productId;
          });
          typeof product != "undefined" && $scope.editProduct(product[0]);
        });
      } else {
        $scope.getRequestedHoardings();
      }
    }

    $scope.exportCsvHandler = () => {
      $scope.openExportCsvSlushBucket({
        products: $scope.productList
      });
    }

    //Export to CSV
    $scope.openExportCsvSlushBucket = function (campaignDetails) {
      $mdDialog.show({
        templateUrl: "views/slush-bucket-popup.html",
        fullscreen: $scope.customFullscreen,
        clickOutsideToClose: false,
        preserveScope: true,
        controller: function ($scope) {
          $scope.activeOption = {
            isAvailable: false,
            option: null,
          };

          $scope.changeSelection = function (isAvailable, option) {
            $scope.activeOption.isAvailable = isAvailable;
            $scope.activeOption.option = option;
          };

          $scope.closeMdDialog = function () {
            $mdDialog.hide();
          };

          $scope.changePosition = function (position) {
            switch (position) {
              case "right":
                const opt = $scope.activeOption.option;
                $scope.activeOption.isAvailable = false;
                $scope.personalizeColumns.available_columns.splice(
                  $scope.personalizeColumns.available_columns.indexOf(opt),
                  1
                );
                $scope.personalizeColumns.selected_columns.push(opt);
                break;
              case "left":
                const cho = $scope.activeOption.option;
                $scope.activeOption.isAvailable = true;
                $scope.personalizeColumns.selected_columns.splice(
                  $scope.personalizeColumns.selected_columns.indexOf(cho),
                  1
                );
                $scope.personalizeColumns.available_columns.push(cho);
                break;
              case "up":
                console.log($scope.activeOption);
                console.log($scope.personalizeColumns.selected_columns);
                var selectedIndex =
                  $scope.personalizeColumns.selected_columns.findIndex(
                    (item) =>
                      item.field_name === $scope.activeOption.option.field_name
                  );
                console.log(selectedIndex);
                if (selectedIndex) {
                  var tempOption = {
                    ...$scope.personalizeColumns.selected_columns[
                      selectedIndex - 1
                    ],
                  };
                  $scope.personalizeColumns.selected_columns[
                    selectedIndex - 1
                  ] = {
                    ...$scope.personalizeColumns.selected_columns[
                      selectedIndex
                    ],
                  };
                  $scope.personalizeColumns.selected_columns[selectedIndex] = {
                    ...tempOption,
                  };
                } else {
                  var tempOption = {
                    ...$scope.personalizeColumns.selected_columns[
                      selectedIndex
                    ],
                  };
                  $scope.personalizeColumns.selected_columns.splice(
                    selectedIndex,
                    1
                  );
                  $scope.personalizeColumns.selected_columns.push(tempOption);
                }
                break;
              case "down":
                console.log($scope.activeOption);
                console.log($scope.personalizeColumns.selected_columns);
                var selectedIndex =
                  $scope.personalizeColumns.selected_columns.findIndex(
                    (item) =>
                      item.field_name === $scope.activeOption.option.field_name
                  );
                console.log(selectedIndex);
                if (
                  selectedIndex !==
                  $scope.personalizeColumns.selected_columns.length - 1
                ) {
                  var tempOption = {
                    ...$scope.personalizeColumns.selected_columns[
                      selectedIndex + 1
                    ],
                  };
                  $scope.personalizeColumns.selected_columns[
                    selectedIndex + 1
                  ] = {
                    ...$scope.personalizeColumns.selected_columns[
                      selectedIndex
                    ],
                  };
                  $scope.personalizeColumns.selected_columns[selectedIndex] = {
                    ...tempOption,
                  };
                } else {
                  var tempOption = {
                    ...$scope.personalizeColumns.selected_columns[
                      selectedIndex
                    ],
                  };
                  $scope.personalizeColumns.selected_columns.splice(
                    selectedIndex,
                    1
                  );
                  $scope.personalizeColumns.selected_columns.unshift(
                    tempOption
                  );
                }
                break;
              default:
                console.log("Invalid Move");
            }
          };

          function formatDate(dt) {
            var dt = new Date(dt);
            var year = dt.getFullYear();
            var month = dt.getMonth() + 1;
            var day = dt.getDate();
            return (
              year +
              "-" +
              (month < 0 ? "0" + month : month) +
              "-" +
              (day < 0 ? "0" + day : day)
            );
          }

          function getDate (dt) {
            const dtValue = dt.$date.$numberLong;
            return moment(new Date(+dtValue)).format("YYYY-MM-DD");
          };

          $scope.convertToCSV = function (headers) {
            try {
              var csvData = Object.values(headers).join(",");
              csvData += "\r\n";
              campaignDetails.products.forEach((row) => {
                var rowData = [];
                Object.keys(headers).forEach((col) => {
                  var fieldData = "";
                  if (col == "booked_from" || col == "booked_to") {
                    fieldData = getDate(row[col == "booked_from" ? "from_date" : "to_date"]); //YYYY-MM-DD
                  } else if (
                    col == "price" ||
                    col == "cpm"
                  ) {
                    var offerPrice = row[col == "price" ? "rateCard" : col];
                    fieldData = "$" + Number(offerPrice).toFixed(2);
                  } else if (col == "impressionsperselectedDates") {
                    var impression = row["secondImpression"];
                    fieldData = Number(impression).toFixed(0);
                  } else if (col == "sold_status") {
                    fieldData = row[col] ? "Sold out" : "Available";
                  } else if (col == "offerprice") {
                    fieldData = "$" + (parseInt(row["unitQty"]) * parseInt(row["rateCard"])).toFixed(2);
                  } else if(col == "quantity") {
                    fieldData = row["unitQty"];
                  } else {
                    fieldData = row[col];
                  }
                  rowData.push(fieldData);
                });
                csvData += rowData.join(",");
                csvData += "\r\n";
              });
              return csvData;
            } catch (ex) {
              console.log("exception: " + ex.message);
            }
          };

          $scope.downloadCSVFile = function (fileName, headers) {
            const csvData = this.convertToCSV(headers);
            //console.log(csvData);
            let blob = new Blob(["\ufeff" + csvData], {
              type: "text/csv;charset=utf-8;",
            });
            let dwldLink = document.createElement("a");
            let url = URL.createObjectURL(blob);
            let isSafariBrowser =
              navigator.userAgent.indexOf("Safari") != -1 &&
              navigator.userAgent.indexOf("Chrome") == -1;
            if (isSafariBrowser) {
              //if Safari open in new window to save file with random filename.
              dwldLink.setAttribute("target", "_blank");
            }
            dwldLink.setAttribute("href", url);
            dwldLink.setAttribute("download", fileName + ".csv");
            dwldLink.style.visibility = "hidden";
            document.body.appendChild(dwldLink);
            dwldLink.click();
            document.body.removeChild(dwldLink);
          };
          // get columns
          $rootScope.loading = true;
          CampaignService.getColumnsToExport().then(function (result) {
            $scope.personalizeColumns = result;
            $rootScope.loading = false;
          });

          //save columns
          $scope.saveColumns = function () {
            const payload = {
              report_type: "report_campaign",
              selected_columns_post: JSON.stringify(
                $scope.personalizeColumns.selected_columns.map((item) => {
                  return {
                    field_name: item.field_name,
                    label: item.label,
                  };
                })
              ),
            };
            CampaignService.saveColumnsToExport(payload).then(function (
              result
            ) {
              if (result.status == "1") {
                const headers = {};
                $scope.personalizeColumns.selected_columns.forEach((item) => {
                  headers[item.field_name] = item.label;
                });
                $scope.downloadCSVFile("campaign-products", headers);
                toastr.success(result.message);
              } else {
                toastr.error(result.message);
              }
              $mdDialog.hide();
            });
          };



        },
      });
    };
  },
]);
