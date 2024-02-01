angular.module("bbManager").controller('UserProductCtrl', function ($scope, $rootScope, $mdSidenav, $mdDialog, $window, $timeout, MapService, config, CampaignService, toastr, $location) {

  $scope.config = config;

  /*==============================
  | Pagination
  ==============================*/

  /*==============================
  | Pagination ends
  ==============================*/


  /*==============================
  | Sidenavs and mdDialogs
  ==============================*/
  $scope.productview = function () {
    $mdSidenav('productDetailsList').toggle();
  };

  $scope.showSaveCampaignPopup = false;
  $scope.hasCampaignId = true;
  $scope.activeUserCampaignsLoaded = false;
  $scope.toggleSaveCampaignPopup = function (type) {
    var popup = angular.element( document.querySelector( '#showDetails' ) );

    if(type=='addClassTop'){
      popup.addClass('PopupTop');
      popup.removeClass('Popupbottom');
    }
    if(type=='addClassBottom'){
      popup.addClass('Popupbottom');
      popup.removeClass('PopupTop');
    }
    if(type=='close'){
      popup.removeClass('PopupTop');
      popup.removeClass('Popupbottom');
    }
    $scope.showSaveCampaignPopup = !$scope.showSaveCampaignPopup;
    var campaignId = $location.$$url.split("/")[2];
    $scope.campaignId = campaignId;
        // debugger;
    if (campaignId && $scope.activeUserCampaignsLoaded) {
      $scope.hasCampaignId = true;
      //$timeout(function() {
        var selectedItem = $scope.activeUserCampaigns.find(item => item.id == campaignId);
        if (selectedItem)  {
            $scope.existingCampaign.id = selectedItem.id.trim();
        //  $scope.$digest();
        } else {
          console.error('selected campaign donot exist!');
        }
      //},1000);
    } else
      $scope.hasCampaignId = false;
  }
  $scope.gotoLocationPage = function() {
    var campaignId = $location.$$url.split("/")[2];
    $scope.campaignId = campaignId;
    if (campaignId)
      $location.path('/location/'+campaignId);
    else
      $location.path('/location');
  };
  /*==============================
  | Sidenavs and mdDialogs ends
  ==============================*/
  if ($rootScope.formatSelected) {
    $scope.selectedFormatIndex = $rootScope.formatSelected;
  } else {
    $scope.selectedFormatIndex = 0;
  }

  /*=================================
  | Product section
  =================================*/
  function convertDateToMMDDYYYY (dates,areaTimeZoneType) {
    const startDate = dates.from_date;
    const endDate = dates.to_date;
	if(areaTimeZoneType != null){
		const splitStartDate = new Date(startDate)
			.toLocaleString('en-GB',{ timeZone: areaTimeZoneType}).slice(0,10).split('/');
		[splitStartDate[0], splitStartDate[1]] = [splitStartDate[1], splitStartDate[0]];
		
		const splitEndDate = new Date(endDate)
			.toLocaleString('en-GB', { timeZone: areaTimeZoneType}).slice(0,10).split('/');
		[splitEndDate[0], splitEndDate[1]] = [splitEndDate[1], splitEndDate[0]];
		
		return {
			startDate: splitStartDate.join('-'),
			endDate: splitEndDate.join('-')
		}
	}else{
		return {
			startDate: moment(startDate).format("MM-DD-YYYY"),
			endDate: moment(endDate).format("MM-DD-YYYY")
		}
	}
  } 

  function getShortListedProducts() {
    MapService.getshortListProduct(JSON.parse(localStorage.loggedInUser).id).then(function (response) {
      //$scope.shortListedProducts = response;
      shortListedProductsLength = response.shortlisted_products.length;
      $scope.shortListedProducts = response.shortlisted_products;
      $scope.shortListedProducts.forEach((product) => {
        // debugger;
        const {startDate, endDate} = convertDateToMMDDYYYY(product,product.area_time_zone_type);
        product["date"] = {
          startDate,endDate
        }
      });
      $scope.shortlistProductsTotal = response.shortlistedsum;
      $scope.impressionTotal = response.impressionSum
      $scope.totalCpm = response.cpmval;
      $rootScope.$emit("shortListedProducts", shortListedProductsLength)
      console.log(response);
    });
  }


  // Select All

  $scope.selected = [];

  $scope.exist = function (item) {
    return $scope.selected.indexOf(item) > -1
  }
  $scope.toggleSelection = function (item) {
    var idx = $scope.selected.indexOf(item)
    if (idx > -1) {
      $scope.selected.splice(idx, 1);
    } else {
      $scope.selected.push(item);
    }
  }
  $scope.shortlistedProductcheckAll = function (selectAll) {
    if (selectAll) {
      angular.forEach($scope.shortListedProducts, function (item) {
        idx = $scope.selected.indexOf(item);
        if (idx >= 0) {
          return true;
        } else {
          $scope.selected.push(item);
        }
      })
    } else {
      $scope.selected = [];
    }
  }
  $scope.shortlisteddetails = [];
  var deletedListArray = [];
  $scope.shortlistproductlist = [];
  $scope.bulkDelete = function () {
    $scope.shortlisteddetails = $scope.selected;
    angular.forEach($scope.shortlisteddetails, function (shortlistproductIds) {
      deletedListArray.push(shortlistproductIds.id);
    })
    $scope.shortlistproductlist = deletedListArray;
  }
  // Select All

  // $scope.conformDeleteShortlisted = function (shortlistId) {
  //   $scope.shortlistId = deletedListArray
  // }
  $scope.deleteShortlisted = function (ev) {
    var sendObj = {
      "product_id": $scope.shortlistproductlist,
    }
    MapService.deleteShortlistedProduct(sendObj).then(function (response) {
      $mdDialog.show(
        $mdDialog.alert()
          .parent(angular.element(document.querySelector('body')))
          .clickOutsideToClose(true)
          .title('Cart Product')
          .textContent(response.message)
          .ariaLabel('delete-shortlisted')
          .ok('Confirmed!')
          .targetEvent(ev)
      );
      setTimeout(() => {
        $mdDialog.hide();
      }, 2000)
      getShortListedProducts();
    });
  };
  /*=================================
  | Product section ends
  =================================*/
  $scope.IsDisabled = true;
  $scope.EnableDisable = function () {
    $scope.IsDisabled = $scope.campaign.name.length == 0;
  }

  /*=================================
  | Campaign section
  =================================*/
  $scope.payAndLaunch = function (campaignName, productId) {
    var paylaunchProduct = {
      name: campaignName,
      shortlisted_products: []
    };
    productId.forEach(function (item) {
      paylaunchProduct.shortlisted_products.push(item.id)
    })
    CampaignService.payAndLaunch(paylaunchProduct).then(function (res) {
      if (res.status == 1) {
        toastr.success(res.message)
      } else if (res.status == 0) {
        toastr.error(res.message)
      }
    })
  }
  $scope.saveCampaign = function () {
    // If we finally decide to use selecting products for a campaign
    // if($scope.selectedForNewCampaign.length == 0){
    //   // add all shortlisted products to campaign
    //   console.log($scope.shortListedProducts);
    //   // CampaignService.saveCampaign($scope.shortListedProducts).then(function(response){
    //   //   $scope.campaignSavedSuccessfully = true;
    //   // });
    // }
    // else{
    //   // add all shortlisted products for new campaign
    //   console.log($scope.selectedForNewCampaign);
    //   // CampaignService.saveCampaign($scope.selectedForNewCampaign).then(function(response){
    //   //   $scope.campaignSavedSuccessfully = true;
    //   // });
    // }
    // campaign.products = $scope.selectedForNewCampaign;
    if ($scope.shortListedProducts.length > 0) {
      $scope.campaign.shortlisted_products = [];
      _.each($scope.shortListedProducts, function (v, i) {
        $scope.campaign.shortlisted_products.push(v.id);
      });
      CampaignService.saveUserCampaign($scope.campaign).then(function (response) {
        if (response.status == 1) {
          $scope.campaignSavedSuccessfully = true;
          document.getElementById("savecampdropdown").classList.toggle("show");
          // $timeout(function () {
          //   $mdSidenav('saveCampaignSidenav').close();
          //   $mdSidenav('shortlistAndSaveSidenav').close();
          //   $scope.campaign = {};
          //   $scope.forms.viewAndSaveCampaignForm.$setPristine();
          //   $scope.forms.viewAndSaveCampaignForm.$setUntouched();
          //   $scope.campaignSavedSuccessfully = false;
          // }, 3000);
          $scope.loadActiveUserCampaigns();
          getShortListedProducts();
          // $window.location.href = '/user-saved-campaigns';
          $location.path('/user-saved-campaigns');
        } else if (response.status == 0) {
          toastr.error(response.message);
        } else {
          $scope.saveUserCampaignErrors = response.message;
        }
      });
    } else {
      toastr.error("Please shortlist some products first.");
    }
  }
  $scope.activeUserCampaigns = [];
  $scope.loadActiveUserCampaigns = function () {
    $scope.activeUserCampaignsLoaded = false;
    $rootScope.loading = true;
    CampaignService.getUserSavedCampaigns("saved_campaign").then(function (result) {
      $scope.activeUserCampaigns = result.filter(function (item) {
        return true;
        // if (item.status == 100 || item.status == 1300) {
        //   return true;
        // }
      });
      $rootScope.loading = false;
      $scope.activeUserCampaignsLoaded = true;
    });
  }
  $scope.existingCampaign = {};
  $scope.loadActiveUserCampaigns();
  $scope.addProductToExistingCampaign = function (existingCampaignId) {
    var productToCampaign = {
      campaign_id: existingCampaignId
    };
    $scope.loading= true;
    if ($scope.shortListedProducts.length > 0) {
      productToCampaign.shortlisted_products = [];
      _.each($scope.shortListedProducts, function (v, i) {
        productToCampaign.shortlisted_products.push(v.id);
        $scope.loading= false;
      });
      CampaignService.addProductToExistingCampaign(productToCampaign).then(function (result) {
        if (result.status == 1) {
          toastr.success(result.message);
          // $mdSidenav('productDetails').close();
          // $scope.toggleSaveCampaignPopup();
          // $scope.shortListedProducts = null;
          $window.location.href = '/user-saved-campaigns';
          getShortListedProducts();
        } else {
          toastr.error(result.message);
        }
      });
    }
  }

  //view campaign details
  // $scope.viewCampaignDetails = function (campaignId) {
  //   CampaignService.getCampaignWithProducts(campaignId).then(function (campaignDetails) {
  //     $scope.campaignDetails = campaignDetails;
  //     $scope.$parent.alreadyShortlisted = true;
  //     // $scope.toggleCampaignDetailSidenav();
  //   });
  // }
  /*=================================
  | Campaign section ends
  =================================*/


  /*=======================================
  | Route based initial loads
  =======================================*/
  // if ($rootScope.currStateName == "index.campaign-details") {
  //   $scope.viewCampaignDetails(localStorage.viewCampaignDetailsId)
  // }

  if ($rootScope.currStateName == "index.shortlisted-products" || $rootScope.currStateName == 'owner.location') {
    getShortListedProducts();
  }
  /*=======================================
  | Route based initial loads end
  =======================================*/


  $scope.loading= true;
  $scope.gotoProductDetails = function(id) {
    $location.path('product-camp-details/'+id);
    $scope.loading= false;
  }

});