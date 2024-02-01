angular.module("bbManager").controller("CampaignProposalCtrl", function (
  $scope,
  $mdDialog,
  $stateParams,
  $mdSidenav,
  $location,
  $rootScope,
  $window,
  $filter,
  $timeout,
  CampaignService,
  AdminCampaignService,
  ProductService,
  config,
  toastr,
  OwnerProductService,
  Upload,
  FileSaver
) {
  $scope.productList = [];
 
   if ($stateParams.from) {
    $scope.comingFrom = $stateParams.from;
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
    } else if (
      $scope.pagination.pageNo > 0 &&
      $scope.pagination.pageNo <= pageLinks / 2
    ) {
      lowest = 1;
    } else {
      lowest = $scope.pagination.pageNo - mid + 1;
    }
    highest =
      $scope.pagination.pageCount < $scope.pagination.pageSize ?
      $scope.pagination.pageCount :
      lowest + pageLinks;
    $scope.pagination.pageArray = _.range(lowest, highest + 1);
  }

  /*===================
  | Pagination Ends
  ===================*/

  /*=======================
  | MdDialogs and sidenavs
  =======================*/

  $scope.toggleQuoteChangeRequestDetailsSidenav = function () {
    $mdSidenav("quoteChangeRequestDetailsSidenav").toggle();
  };

  $scope.toggleShareCampaignSidenav = function () {
    $mdSidenav("shareCampaignSidenav").toggle();
  };

  /*===========================
  | MdDialogs and sidenavs end
  ===========================*/

  $scope.loadProductList = function () {
    if ($scope.searchAll) {
      var search = $scope.searchAll;
    } else {
      search = "";
    }
    ProductService.getSearchProductList(
      $scope.pagination.pageNo,
      $scope.pagination.pageSize,
      search
    ).then(function (result) {
      if (localStorage.campaignForSuggestion) {
        var campaignForSuggestion = JSON.parse(
          localStorage.campaignForSuggestion
        );
        $scope.campaignId = campaignForSuggestion.id;
        $scope.campaignStartDate = campaignForSuggestion.start_date;
        $scope.campaignEndDate = campaignForSuggestion.end_date;
        $scope.campaignEstBudget = campaignForSuggestion.est_budget;
        $scope.campaignActBudget = campaignForSuggestion.act_budget;
        if (
          campaignForSuggestion.products &&
          campaignForSuggestion.products.length > 0
        ) {
          _.map(result.products, function (p) {
            if (
              _.find(JSON.parse(localStorage.campaignForSuggestion).products, {
                id: p.id
              }) !== undefined
            ) {
              p.alreadyAdded = true;
              return p;
            }
          });
        }
      }
      $scope.productList = result.products;
      $scope.pagination.pageCount = result.page_count;
      createPageLinks();
    });
  };

  /****** Search ************/
  $scope.searchAll = "";

  $scope.clearSearch = function () {
    $scope.searchAll = "";
    $scope.pageNo = 1;
    $scope.loadProductList();
  };
  $scope.searchHoardingData = function () {
    $scope.pageNo = 1;
    $scope.loadProductList();
  };

  function setDatesForProductsToSuggest(campaign) {
    $scope.campaignStartDate = new Date(campaign.start_date);
    $scope.campaignEndDate = new Date(campaign.end_date);
    $scope.fromMinDate = moment(campaign.start_date).toDate();
    $scope.fromMaxDate = moment(campaign.end_date).toDate();
    $scope.toMaxDate = moment(campaign.end_date).toDate();
  }
  
  $scope.loadCampaignData = function (campaignId) {
    //return new Promise(function (resolve, reject) {
      if ($scope.campaignFrom == 40) {
        AdminCampaignService.getRFPCampaignWithProducts(campaignId).then(function (result) {
          return loadData(result);
        });
      } else {
        AdminCampaignService.getCampaignWithProducts(campaignId).then(function (result) {
          return loadData(result);
        });
      }
   // });
  };

  function convertDateToMMDDYYYY (dates,areaTimeZoneType) {
    try {   
      if(!areaTimeZoneType) {
        areaTimeZoneType = Intl.DateTimeFormat().resolvedOptions().timeZone;
      }
      const startDate = dates.booked_from;
      const endDate = dates.booked_to;
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
    } catch (error) {
      console.log(error);
    }
  }

  function convertSingleDateToMMDDYYYY(campaignDate,areaTimeZoneType) {
    try {
      if(!areaTimeZoneType) {
        areaTimeZoneType = Intl.DateTimeFormat().resolvedOptions().timeZone;
      }
      const date = campaignDate;
      const splitStartDate = new Date(date)
        .toLocaleString('en-GB',{ timeZone: areaTimeZoneType}).slice(0,10).split('/');
      [splitStartDate[0], splitStartDate[1]] = [splitStartDate[1], splitStartDate[0]];
      return splitStartDate.join('-');
    } catch (error) {
      console.log(error);
    }
  }


  function loadData(result) {
    return new Promise(function (resolve, reject) {
      $scope.productsArray = [];
      var productList='';
      $scope.deleteProductList=[];
        if($location.$$url.split('/')[4] == '30' || $location.$$url.split('/')[4] != '16') {
          localStorage.removeItem('productbookingid');
        }
          $scope.newProducts = JSON.parse(localStorage.getItem('productbookingid'));
          if($scope.newProducts != null){
            $scope.newProducts.forEach(pid=>{
              if(result.products){
                result.products.forEach(x=> {
                  if (x.productbookingid == pid){
                   productList=x.product_id +"-" + x.productbookingid;
                   productList=x.product_id;
                    $scope.deleteProductList.push(productList);
                    if($scope.productsArray.filter(e => e.product_id == pid).length == 0){
                      $scope.productsArray.push(x);
                    }
                    // $scope.productsArray.push(x);
                }
              })
              }             
            })
          }            
          if($location.$$url.split('/')[4] == '30' || $location.$$url.split('/')[4] != '16') {
            $scope.productsArray = result.products;
          }
          $scope.campaignProducts = $scope.productsArray;
          if($scope.campaignProducts != null){
            $scope.timezones = {
              startDateZone: $scope.campaignProducts[0].area_time_zone_type,
              endDateZone: $scope.campaignProducts[0].area_time_zone_type,
              smallestStartDate: $scope.campaignProducts[0].booked_from,
              largestEndDate: $scope.campaignProducts[0].booked_to,
            };
            $scope.campaignProducts.forEach((product) => {
              if (moment(product.booked_from) < moment($scope.timezones.smallestStartDate)) {
                $scope.timezones.smallestStartDate = product.booked_from;
                $scope.timezones.startDateZone = product.area_time_zone_type;
              }
              if (moment(product.booked_to)  > moment($scope.timezones.largestEndDate)) {
                $scope.timezones.largestEndDate = product.booked_to;
                $scope.timezones.endDateZone = product.area_time_zone_type;
              }
              const {startDate, endDate} = convertDateToMMDDYYYY(product,product.area_time_zone_type);
              product["date"] = {
                startDate,endDate
              }
              product = {
                billingYes: 'Yes',
                billingNo: '',
                servicingNo: '',
                servicingYes : 'Yes'
              }
            });
            

						if(result.products_in_campaign != null){
							result.products_in_campaign.forEach(
								(product_data) => {
									product_data.mapImageUrl = generateMapImageUrl(product_data.lat, product_data.lng);
								});
						}
         }else if($scope.campaignProducts == null){ 
		 $scope.campaignDetails = result;
          $scope.RFPsearchArray = result.rfp_search_criteria_preview;
          currentDateData = [];
          dates_dma_diff = [];
          $scope.RFPsearchArray.dma_dates.forEach((product,index) => {
              dates_dma = product.split('::');
              currentDateData.push({dates_dma});
              var startDate = moment(currentDateData[index].dates_dma[0]);
              var endDate = moment(currentDateData[index].dates_dma[1]);
              dates_dma_diff.push(moment(endDate).diff(startDate, 'days')+1);
          });
          $scope.currentDateDataString = currentDateData;
          $scope.dates_dma_diff_data = dates_dma_diff;
          console.log('dileep',$scope.currentDateDataString[0].dates_dma[0]);
         }
          console.log($scope.timezones);
          // Offers
          $scope.activeOffer = null;
          $scope.isOfferAcceptable = true;
          $scope.showCheckboxHeader = false;
          AdminCampaignService.getCampaignOffers(campaignId).then(function(result) {
            $scope.offerDetails.offers = result;
            if (result.length >= 2) {
              $scope.offerDetails.newTotal = $scope.campaignDetails.shortlistedsum - result[1].price;
              var discountPrice = $scope.campaignDetails.shortlistedsum - result[1].price;
              $scope.offerDetails.offerPercentage = (discountPrice / $scope.campaignDetails.shortlistedsum) * 100;
              $scope.activeOffer = result[1];
            } else if (result.length == 1) {
              $scope.offerDetails.newTotal = $scope.campaignDetails.shortlistedsum - result[0].price;
              var discountPrice = $scope.campaignDetails.shortlistedsum - result[0].price;
              $scope.offerDetails.offerPercentage = (discountPrice / $scope.campaignDetails.shortlistedsum) * 100;
              $scope.activeOffer = result[0];
            } else {
              $scope.offerDetails.offerPercentage = 0; 
              $scope.activeOffer = null;
            }

            if ($scope.offerDetails.offers && $scope.offerDetails.offers.length) {
              $scope.campaignProducts.forEach(function(prod) {
                if (prod.negotiatedpriceperselectedDates > $scope.getOfferedPrice(prod.price)) {
                  prod.isBelowNegotiatedCost = true;
                  prod.isAllowed = false;
                  $scope.showCheckboxHeader = true;
                  $scope.isOfferAcceptable = false;
                } else {
                  prod.isBelowNegotiatedCost = false;
                  prod.isAllowed = true;
                }
              });
              var len = $scope.campaignProducts.length;
              for(var i=0; i<len; i++) {
                if ($scope.campaignProducts[i].isBelowNegotiatedCost) {
                  var obj = Object.assign({}, $scope.campaignProducts[i]);
                  $scope.campaignProducts.splice(i, 1);
                  $scope.campaignProducts.unshift(obj);
                }
              }
            }
            $timeout(() => {
              $scope.onAllowChange(true);
            })
          });
        $scope.campaignDetails = result;
        $scope.campaignDetails.startDate = convertSingleDateToMMDDYYYY($scope.campaignDetails.startDate,
          $scope.timezones.startDateZone);
        $scope.campaignDetails.endDate = convertSingleDateToMMDDYYYY($scope.campaignDetails.endDate,
          $scope.timezones.endDateZone);
        setDatesForProductsToSuggest($scope.campaignDetails);
        $scope.TOTAL = $scope.campaignDetails.act_budget
        
        // if ($scope.campaignDetails.gst_price != "0") {
        //   $scope.onchecked = true;
        //   $scope.GST = ($scope.campaignDetails.act_budget / 100) * 18;
        //   $scope.TOTAL = $scope.campaignDetails.act_budget + $scope.GST;
        // } else {
        //   $scope.onchecked = false;
        //   $scope.GST = "0";
        //   $scope.TOTAL = $scope.campaignDetails.act_budget + parseInt($scope.GST);
        // }

        if (result.status > 7) {
          loadCampaignPayments(campaignId);
        }
      resolve(result);
    });
  }

  $scope.uncheck = function (checked) {
    if (!checked) {
      $scope.GST = "0";
      $scope.onchecked = false;
      $scope.TOTAL = $scope.campaignDetails.act_budget + parseInt($scope.GST);
    } else {
      $scope.GST = ($scope.campaignDetails.act_budget / 100) * 18;
      $scope.TOTAL = $scope.campaignDetails.act_budget + $scope.GST;
      $scope.onchecked = true;
    }
  };
  $scope.downloadAdminQuote = function (campaignId) {
    AdminCampaignService.downloadQuote(campaignId).then(function (result) {
      var campaignPdf = new Blob([result], {
        type: 'application/pdf;charset=utf-8'
      });
      FileSaver.saveAs(campaignPdf, 'campaigns.pdf');
      if (result.status) {
        toastr.error(result.meesage);
      }
    });
    var campaignToEmail = {
      campaign_id: campaignId,
      email: JSON.parse(localStorage.loggedInUser).email,
      // receiver_name: shareCampaign.receiver_name,
      // campaign_type: $scope.campaignToShare.type
    };
    AdminCampaignService.shareandDownloadCampaignToEmail(campaignToEmail).then(function (
      result
    ) {
      if (result.status == 1) {
        $mdSidenav("shareCampaignSidenav").close();
        $mdDialog.show(
          $mdDialog
          .alert()
          .parent(angular.element(document.querySelector("body")))
          .clickOutsideToClose(true)
          .title(result.message)
          // .textContent('You can specify some description text in here.')
          .ariaLabel("Alert Dialog Demo")
          .ok("Confirmed!")
          .targetEvent(ev)
        );
        AdminshareCampaign();
      } else {
        toastr.error(result.message);
      }
    });
  };
  $scope.downloadAdminPdf = function (campaignId) {
    AdminCampaignService.downloadPdf(campaignId).then(function (result) {
      var campaignPdf = new Blob([result], {
        type: 'application/pdf;charset=utf-8'
      });
      FileSaver.saveAs(campaignPdf, 'campaigns.pdf');
      if (result.status) {
        toastr.error(result.meesage);
      }
    });
  };

  // server-code
  // AdminCampaignService.downloadQuote(campaignId).then(function (result) {
  //   var campaignPdf = new Blob([result], {
  //     type: 'application/pdf;charset=utf-8'
  //   });
  //   FileSaver.saveAs(campaignPdf, 'campaigns.pdf');
  //   if (result.status) {
  //     toastr.error(result.meesage);
  //   }
  // });

  // Server


  function loadCampaignPayments(campaignId) {
    if ($scope.campaignDetails.status >= 6) {
      AdminCampaignService.getCampaignPaymentDetails(campaignId).then(function (result) {
        $scope.campaignPayments = result;
        $scope.PendingPay = $scope.campaignPayments.campaign_details.totalamount - $scope.campaignPayments.campaign_details.total_paid;
      });
    } else {
      toastr.error(
        "Payments are only available for running or stopped campaigns."
      );
    }
  }

  $scope.offerDetails = {
    offers: [],
    newTotal: 0,
    offerPercentage: 0,
    status: {
      10: 'Requested',
      20: 'Accepted',
      30: 'Accepted',
      40: 'Rejected',
      50: 'Rejected'
    }
  };
  if ($stateParams.campaignId) {
    var campaignId = $stateParams.campaignId;
    $scope.campaignFrom = $stateParams.from;
    //alert($scope.campaignFrom);
    $scope.loadCampaignData(campaignId);
  }

  $scope.addNewProductToCampaign = function () {
    $scope.campaignForSuggestion = {};
    localStorage.campaignForSuggestion = JSON.stringify($scope.campaignDetails);
    $location.path("/admin/suggest-products");
  };

  // adds a product in the campaign
  $scope.suggestProductForCampaign = function (suggestedProduct) {
    if (!localStorage.campaignForSuggestion) {
      toastr.error(
        "No Campaign is seleted. Please select which campaign you're adding this product in to."
      );
    } else {
      var postObj = {
        campaign_id: JSON.parse(localStorage.campaignForSuggestion).id,
        product: {
          id: suggestedProduct.id,
          from_date: suggestedProduct.start_date,
          to_date: suggestedProduct.end_date,
          price: suggestedProduct.price
        }
      };
      AdminCampaignService.proposeProductForCampaign(postObj).then(function (
        result
      ) {
        if (result.status == 1) {
          AdminCampaignService.getCampaignWithProducts(
            JSON.parse(localStorage.campaignForSuggestion).id
          ).then(function (updatedCampaignData) {
            localStorage.campaignForSuggestion = JSON.stringify(
              updatedCampaignData
            );
            $scope.campaignActBudget = updatedCampaignData.act_budget;
            _.map($scope.productList, function (product) {
              if (product.id == suggestedProduct.id) {
                product.alreadyAdded = true;
              }
              return product;
            });
          });
          toastr.success(result.message);
        } else {
          toastr.error(result.message);
        }
      });
    }
  };

  $scope.deleteCampaign = function(campaign_id) {
    AdminCampaignService.deleteAdminOwnerCampaign(campaign_id).then(function(result) {
      if (result.status == 1) {
        toastr.success(result.message);
        $window.history.back();
      } else {
        toastr.error(result.message);
      }
    });
  }

  $scope.removeProductFromCampaignSuggestion = function (productId) {
    var campaignId = JSON.parse(localStorage.campaignForSuggestion).id;
    AdminCampaignService.deleteProductFromCampaign(campaignId, productId).then(
      function (result) {
        if (result.status == 1) {
          AdminCampaignService.getCampaignWithProducts(
            JSON.parse(localStorage.campaignForSuggestion).id
          ).then(function (updatedCampaignData) {
            localStorage.campaignForSuggestion = JSON.stringify(
              updatedCampaignData
            );
            $scope.campaignActBudget = updatedCampaignData.act_budget;
          });
          _.map($scope.productList, function (product) {
            if (product.id == productId) {
              product.alreadyAdded = false;
            }
            return product;
          });
          toastr.success("Product removed from campaign");
        } else {
          toastr.error(result.message);
        }
      }
    );
  };

  $scope.viewProductImage = function (image) {
    var imagePath = config.serverUrl + image;
    $mdDialog.show({
      locals: {
        src: imagePath
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

  $scope.changeProductPrice = function (data) {
    product = {};
    product.id = data.product_id;
    product.default_price = data.default_price;
    AdminCampaignService.changeProductPrice(product).then(function (result) {
      if (result.status == 1) {
        toastr.success(result.message);
      } else {
        toastr.error(result.data.message);
      }
    });
  };
  $scope.editProposedProduct = function (bookingId, price) {
    var bookingObj = {
      booking_id: bookingId,
      price: price
    };
    $mdDialog.show({
      locals: {
        campaign: $scope.campaignDetails,
        bookingObj: bookingObj,
        ctrlScope: $scope
      },
      templateUrl: "views/admin/edit-proposed-product.html",
      fullscreen: $scope.customFullscreen,
      clickOutsideToClose: true,
      controller: function (
        $scope,
        $mdDialog,
        CampaignService,
        AdminCampaignService,
        ctrlScope,
        campaign,
        bookingObj
      ) {
        $scope.booking = bookingObj;
        $scope.updateProposedProduct = function () {
          AdminCampaignService.updateProposedProduct(
            campaign.id,
            $scope.booking
          ).then(function (result) {
            if (result.status == 1) {
              // update succeeded. update the grid now.
              $mdDialog.hide();
              CampaignService.getCampaignWithProducts(campaign.id).then(
                function (result) {
                  ctrlScope.campaignDetails = result;
                  ctrlScope.campaignProducts = result.products;
                  // setDatesForAdminProposalToSuggest($scope.campaignDetails);
                }
              );
              toastr.success(result.message);
            } else {
              toastr.error(result.message);
            }
          });
        };
        $scope.closeMdDialog = function () {
          $mdDialog.hide();
        };
      }
    });
  };

  $scope.shareCampaignToEmail = function (ev, shareCampaign) {
    $scope.campaignToShare = $scope.campaignDetails;
    var campaignToEmail = {
      campaign_id: $scope.campaignToShare.id,
      email: shareCampaign.email,
      receiver_name: shareCampaign.receiver_name,
      campaign_type: $scope.campaignToShare.type,
      message: shareCampaign.message
    };
    CampaignService.shareCampaignToEmail(campaignToEmail).then(function (
      result
    ) {
      if (result.status == 1) {
        $mdSidenav("shareCampaignSidenav").close();
        $mdDialog.show(
          $mdDialog
          .alert()
          .parent(angular.element(document.querySelector("body")))
          .clickOutsideToClose(true)
          .title(result.message)
          // .textContent('You can specify some description text in here.')
          .ariaLabel("Alert Dialog Demo")
          .ok("Confirmed!")
          .targetEvent(ev)
        );
        AdminshareCampaign();
      } else {
        toastr.error(result.message);
      }
    });
  };
  function AdminshareCampaign() {
    document.getElementById("shareadmincampDrop").classList.toggle("show");   
  }
  /*===========OFFER===========*/
  $scope.offerActionMessage = '';
  $scope.acceptRejectPopup= function(type) {
    $scope.offerActionMessage = '';
    var dropdownId = type + 'Dropdown';
    var dropDownCloseId;
 
    if(type === "accept"){
        dropDownCloseId = "rejectDropdown"
    }else{
        dropDownCloseId = "acceptDropdown"
    }
    document.getElementById(dropDownCloseId).classList.remove("show");
    document.getElementById(dropdownId).classList.toggle("show"); 
  }
  $scope.acceptRejectOffer = function(type) {
    const id = $scope.offerDetails.offers.length>=2?$scope.offerDetails.offers[1].id:$scope.offerDetails.offers[0].id;
    var offerData = {
      id: id,
      type: type,
      percentage: $scope.offerDetails.offerPercentage,
      message: $scope.offerActionMessage
    };
    if (type == 'accept') {
      offerData.newPrice = ($scope.campaignProducts[0].price - ($scope.campaignProducts[0].price*$scope.offerDetails.offerPercentage/100));
    }
    AdminCampaignService.acceptRejectOffer(offerData).then(function (result) {
      $scope.acceptRejectPopup(type);
      if (result.status == 1) {
        toastr.success("Offer "+(type=='accept'?'Accepted':'Rejected')+" Successfully!");
        if ($scope.offerDetails.offers.length>=2) {
          $scope.offerDetails.offers[1].status = result.OfferStatus;
        } else {
          $scope.offerDetails.offers[0].status = result.OfferStatus;
        }
        if (result.OfferStatus == 20 || result.OfferStatus == 30) {
          $scope.loadCampaignData($scope.campaignDetails.id);
        }
      } else {
        toastr.error(result.message);
      }
    });
  }
  $scope.getOfferedPrice = function(prodPrice) {
    return (prodPrice - (prodPrice * $scope.offerDetails.offerPercentage / 100));
  };
  $scope.onAllowChange = function(flag) {
    $scope.isOfferAcceptable = flag;
    const filteredProducts = $scope.campaignProducts.filter(prod => {
      return prod.isBelowNegotiatedCost && 
        !($scope.activeOffer.status!=10 || $scope.activeOffer.AdminOfferAcceptReject[prod.id] == 2 && prod.client_id != 1);
    });
    filteredProducts.forEach(function(product) {
      if (product.isAllowed) {
        if ($scope.activeOffer.AdminOfferAcceptReject[product.id] == 2) {
          $scope.isOfferAcceptable = false;
        }
      } else {
        $scope.isOfferAcceptable = false;
      }
    });
  }
  /*===========//OFFER===========*/
  $scope.notifyProductOwnersForQuote = function () {
    AdminCampaignService.notifyProductOwnersForQuote(
      $scope.campaignDetails.id
    ).then(function (result) {
      if (result.status == 1) {
        $scope.campaignDetails.status = 2;
        toastr.success("Owners notified!"); // now we wait for launch request from user.
      } else {
        toastr.error(result.message);
      }
    });
  };
  
  
	//RFP Search Criteria PDF
	($scope.downloadRFPsearch = function (campaignId) {
		AdminCampaignService.downloadRFPsearchCriteria(campaignId).then(function (
			result
		) {
			var campaignPdf = new Blob([result], {
				type: "application/pdf;charset=utf-8",
			});
			FileSaver.saveAs(campaignPdf, "campaigns.pdf");
			if (result.status) {
				toastr.error(result.meesage);
			}
		});
	}),

  $scope.finalizeCampaign = function () {
    if ($scope.onchecked === true) {
      $scope.flag = 1;
      $scope.GST = ($scope.campaignDetails.act_budget / 100) * 18;
    } else if ($scope.onchecked === false) {
      $scope.flag = 0;
      $scope.GST = "0";
    } else {
      $scope.flag = 1;
    }
    AdminCampaignService.finalizeCampaignByAdmin(
      $scope.campaignDetails.id, $scope.flag, $scope.GST
    ).then(function (result) {
      if (result.status == 1) {
        $scope.campaignDetails.status = 3;
        $scope.loadCampaignData($scope.campaignDetails.id);
        toastr.success("Quote Sent!"); // now we wait for launch request from user.
      } else {
        toastr.error(result.message);
      }
    });
    // if ($scope.campaignDetails.act_budget > $scope.campaignDetails.exp_budget) {
    //   var budget_check = confirm(
    //     "Actual budget is larger than Expected budget. Are you sure you want to finalize this campaign?"
    //   );
    //   if (budget_check) {
    //     AdminCampaignService.finalizeCampaignByAdmin(
    //       $scope.campaignDetails.id
    //     ).then(function(result) {
    //       console.log(result);
    //       if (result.status == 1) {
    //         $scope.campaignDetails.status = 3;
    //         $scope.loadCampaignData($scope.campaignDetails.id);
    //         toastr.success("Quote Sent!"); // now we wait for launch request from user.
    //       } else {
    //         toastr.error(result.message);
    //       }
    //     });
    //   }
    // } else {}
  };

  $scope.confirmCampaignBooking = function (campaignId, ev) {
    AdminCampaignService.confirmCampaignBooking(campaignId).then(function (
      result
    ) {
      if (result.status == 1) {
        $mdDialog.show(
          $mdDialog
          .alert()
          .parent(angular.element(document.querySelector("body")))
          .clickOutsideToClose(true)
          .title("Congrats!!")
          .textContent(result.message)
          .ariaLabel("Alert Dialog Demo")
          .ok("Confirmed!")
          .targetEvent(ev)
        );
        $scope.loadCampaignData(campaignId);
      } else {
        $scope.loadCampaignData(campaignId).then(function () {
          if (result.product_ids && result.product_ids.length > 0) {
            toastr.error(result.message);
            _.map($scope.campaignProducts, p => {
              if (_.contains(result.product_ids, p.product_id)) {
                p.unavailable = true;
              }
            });
          }
        });
      }
    });
  };
  $scope.paymentTypes = [{
      name: "Cash"
    },
    {
      name: "Cheque"
    },
    {
      name: "Online"
    },
    {
      name: "Transfer"
    }
  ];
  $scope.files = {};
  $scope.updateCampaignPayment = function (cid) {
    $scope.campaignPayment.campaign_id = cid;
    Upload.upload({
      url: config.apiPath + "/campaign-payment",
      data: {
        image: $scope.files.image,
        campaign_payment: $scope.campaignPayment
      }
    }).then(
      function (result) {
        if (result.data.status == "1") {
          toastr.success(result.data.message);
          $scope.campaignPayment = {};
          $scope.files.image = "";
          /*setTimout(() => {
                    $location.path('/owner/' + $rootScope.clientSlug + '/payments');
                }, 2500);*/
        } else {
          if (result.data.message.constructor == Array) {
            $scope.updateCampaignPaymentErrors = result.data.message;
          } else {
            toastr.error(result.data.message);
          }
        }
      },
      function (resp) {
        toastr.error("somthing went wrong try again later");
      },
      function (evt) {
        var progressPercentage = parseInt((100.0 * evt.loaded) / evt.total);
      }
    );
  };

  /* $scope.showUpdatePaymentForm = function(){
    $mdDialog.show({
      locals:{ campaignId: $scope.campaignDetails.id, ctrlScope : $scope },
      templateUrl: 'views/admin/update-campaign-payment.html',
      fullscreen: $scope.customFullscreen,
      clickOutsideToClose:true,
      controller:function($scope, $mdDialog, CampaignService, AdminCampaignService, ctrlScope, campaignId){
        $scope.paymentTypes = [
          {name: "Cash"},
          {name: "Cheque"},
          {name: "Online"},
          {name: "Transfer"}
        ];
        $scope.updateCampaignPayment = function(){
          $scope.campaignPayment.campaign_id = campaignId;
          AdminCampaignService.updateCampaignPayment($scope.campaignPayment).then(function(result){
            if(result.status == 1){
              // update succeeded. update the grid now.
              loadCampaignPayments(campaignId);
              toastr.success(result.message);
              $rootScope.closeMdDialog();
            }
            else{
              toastr.error(result.message);
            }
          });
        }
      }
    });
  }*/

  $scope.launchCampaign = function (campaignId, ev) {
    AdminCampaignService.launchCampaign(campaignId).then(function (result) {
      if (result.status == 1) {
        $mdDialog.show(
          $mdDialog
          .alert()
          .parent(angular.element(document.querySelector("body")))
          .clickOutsideToClose(true)
          //.title("Congrats! Campaign Confirmed!")
          .title("Congratulations! Campaign Confirmed!")
          .ariaLabel("Alert Dialog Demo")
          .ok("Confirmed!")
          .targetEvent(ev)
        );
        $scope.loadCampaignData(campaignId);
      } else {
        toastr.error(result.message);
      }
    });
  };

  $scope.closeCampaign = function (campaignId, ev) {
    AdminCampaignService.closeCampaign(campaignId).then(function (result) {
      if (result.status == 1) {
        $mdDialog.show(
          $mdDialog
          .alert()
          .parent(angular.element(document.querySelector("body")))
          .clickOutsideToClose(true)
          .title("Success!!")
          .textContent(result.message)
          .ariaLabel("Alert Dialog Demo")
          .ok("Confirmed!")
          .targetEvent(ev)
        );
        $scope.loadCampaignData(campaignId);
      } else {
        toastr.error(result.message);
      }
    });
  };

  $scope.deleteProductFromCampaign = function (campaignId, productId) {
    AdminCampaignService.deleteProductFromCampaign(campaignId, productId).then(
      function (result) {
        if (result.status == 1) {
          $scope.loadCampaignData(campaignId);
          toastr.success(result.message);
        } else {
          toastr.error(result.message);
        }
      }
    );
  };

  $scope.getChangeRequestHistory = function (campaignId) {
    AdminCampaignService.getChangeRequestHistory(campaignId).then(function (
      result
    ) {
      $scope.changeRequestHistory = result;
      $scope.toggleQuoteChangeRequestDetailsSidenav();
    });
  };

  $scope.changeCampaignProductPrice = function (
    campaign_id,
    admin_price,
    product_id,
    product_n
  ) {
    product = {};
    product.campaign_id = campaign_id;
    product.admin_price = admin_price;
    product.product_id = product_id;
    product.product = product_n;
    OwnerProductService.changeCampaignProductPrice(product).then(function (
      result
    ) {
      if (result.status == 1) {
        $scope.loadCampaignData(campaign_id);
        toastr.success(result.message);
      } else {
        toastr.error(result.data.message);
      }
    });
  };
  $scope.productAdminPrice=function(productPrice,productId ) {
    $scope.campaignDetails.products.forEach(element => {
        if(element.admin_price === undefined && element.product_id === productId){
            element.admin_price = productPrice;
        }
    });
}

  $scope.changeQuoteRequest = function (campaignId, remark, type) {
    $scope.changeRequest = {};
    $scope.changeRequest.for_campaign_id = campaignId;
    $scope.changeRequest.remark = remark;
    $scope.changeRequest.type = type;
    CampaignService.requestChangeInQuote($scope.changeRequest).then(function (
      result
    ) {
      if (result.status == 1) {
        $scope.loadCampaignData(campaignId);
        //$mdDialog.hide();
        toastr.success(result.message);
      } else {
        toastr.error(result.message);
      }
    });
  };

  // $scope.loggedinUser = JSON.parse(localStorage.loggedInUser).firstName + ' ' + JSON.parse(localStorage.loggedInUser).lastName;
  $scope.confirmCampaignRequest = function (campaignID) {
    $scope.campaignShare = $scope.campaignDetails;
    var campaignParam = {      
      campaign_id: campaignID,
      // loggedinUser: JSON.parse(localStorage.loggedInUser),
      Comments: this.Comments,
      pric: this.pric  
    };
    AdminCampaignService.confirmCampaignRequest(campaignParam).then(function (result) {
      if (result.status == 1) {
        $mdDialog.hide();
        // $mdSidenav('shareCampaignSidenav').toggle();
        $mdDialog.show(
          $mdDialog.alert()
          .parent(angular.element(document.querySelector('body')))
          .clickOutsideToClose(true)
          .title(result.message)
          //.textContent('You can specify some description text in here.')
          .ariaLabel('Alert Dialog Demo')
          .ok('Confirmed!')
          // .targetEvent(ev)
        );
        ConfirmCampaign();
        $scope.loadCampaignData(campaignId);
      } else {
        toastr.error(result.message);
      }
    });
  }
  function ConfirmCampaign() {
    document.getElementById("confirmDrop").classList.toggle("show");
}



$scope.confirmProductRequest = function (campaignID) {
  $scope.campaignShare = $scope.campaignDetails;
  var campaignPara = {      
    campaign_id: campaignID,
    // loggedinUser: JSON.parse(localStorage.loggedInUser),
    comments: this.comments,
    price: this.price,
    product_id: $scope.newProducts  
  };
  AdminCampaignService.confirmProductRequest(campaignPara).then(function (result) {
    if (result.status == 1) {
      $mdDialog.hide();
      // $mdSidenav('shareCampaignSidenav').toggle();
      $mdDialog.show(
        $mdDialog.alert()
        .parent(angular.element(document.querySelector('body')))
        .clickOutsideToClose(true)
        .title(result.message)
        //.textContent('You can specify some description text in here.')
        .ariaLabel('Alert Dialog Demo')
        .ok('Confirmed!')
        // .targetEvent(ev)
      );
      ConfirmProduct();
      $scope.loadCampaignData(campaignID)
    } else {
      toastr.error(result.message);
    }
  });
}

$scope.getImageUrl = function(url) {
  if (url && (url.includes('.png') || url.includes('.PNG') || url.includes('.jpg') || url.includes('.JPG') || url.includes('.jpeg') || url.includes('.JPEG')|| url.includes('.svg') || url.includes('.SVG'))) {
    return url;
  }
  return 'assets/images/no-image.jpg';
};

function ConfirmProduct() {
  document.getElementById("confirmDro").classList.toggle("show");
}

  /*=========================
  | Page based initial loads
  =========================*/

  if ($rootScope.currStateName == "admin.suggest-products") {
    if (!localStorage.campaignForSuggestion) {
      toastr.error(
        "No Campaign is seleted. Please select which campaign you're adding this product in to."
      );
    } else {
      $scope.loadProductList();
      setDatesForProductsToSuggest(
        JSON.parse(localStorage.campaignForSuggestion)
      );
    }
  }
  /*=============================
  | Page based initial loads end
  =============================*/

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

        function getDate (dt,timeZone) {
          // return $filter('date')(dt, "yyyy-MM-dd");
          return convertSingleDateToMMDDYYYY(dt,timeZone);
        };

        function formateDateForNum(dt,timeZone) {
          const dtValue = dt.$date.$numberLong;
          // return moment(new Date(+dtValue)).format("YYYY-MM-DD");
          return convertSingleDateToMMDDYYYY(dtValue,timeZone);
        };

        $scope.convertToCSV = function (headers) {
          try {
            var csvData = Object.values(headers).join(",");
            csvData += "\r\n";
            campaignDetails.products.forEach((row) => {
              var rowData = [];
              Object.keys(headers).forEach((col) => {
                var fieldData = "";
                if (col.indexOf('::') > -1) {
                  const fieldName = col.split('::');
                  fieldData = row[fieldName[0]] ? row[fieldName[0]] : row[fieldName[1]];
                } else if (col == "booked_from" || col == "booked_to") {
                  fieldData = getDate(row[col == "booked_from" ? "booked_from" : "booked_to"],row["area_time_zone_type"]); //YYYY-MM-DD
                } else if(col == "from_date" || col == "to_date") {
                  fieldData = formateDateForNum(row[col],row["area_time_zone_type"]);
                }
                else if (
                  col == "price" ||
                  col == "cpm"
                ) {
                  var offerPrice = row[col == "price" ? "negotiatedpriceperselectedDates" : col];
                  fieldData = "$" + Number(offerPrice).toFixed(2);
                } else if (col == "impressionsperselectedDates") {
                  var impression = row["secondImpression"];
                  fieldData = Number(impression).toFixed(0);
                } else if (col == "sold_status") {
                  fieldData = row[col] ? "Sold out" : "Available";
                } else if (col == "offerprice") {
                  fieldData = "$" + (parseInt(row["quantity"]) * Number(row["negotiatedpriceperselectedDates"])).toFixed(2);
                } else if(col == "quantity") {
                  fieldData = row["quantity"];
                } else if(col == "state") {
                  fieldData = row["state_name"]
                } else if (col == 'state_name_dma') {
                  fieldData = row['state_name'];
                } else if (col == 'ageloopLength') {
                  if (row.type == "Digital" || row.type == 'Digital/Static') {
                    fieldData = row['ageloopLength'];
                  } else {
                    fieldData = "";
                  }
                } else if(col == 'break_ageloopLength') {
                  if (row.type == "Media") {
                    fieldData = row['ageloopLength'];
                  } else {
                    fieldData = "";
                  }
                } else {
                  fieldData = row[col];
                }
                fieldData = fieldData?.toString().replace(/,/g,'__');
                fieldData = fieldData?.toString().replace(/:/g,'__');
                fieldData = fieldData?.toString().replace(/;/g,'__');
                fieldData = fieldData?.toString().replace(/\r\n/g,'__');
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
                if(item.label === 'DMA') {
                  headers[`${item.field_name}_dma`] = item.label;
                } else if(item.label === 'Break/Loop Length') {
                  headers[`break_${item.field_name}`] = item.label;
                } else {
                  headers[item.field_name] = item.label;
                }
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
   // viewed comments collumn
  $scope.openViewedComments = function(campaignProduct){
    $mdDialog.show({
      templateUrl: "views/viewed-comments.html",
      fullscreen: $scope.customFullscreen,
      clickOutsideToClose: false,
      preserveScope: true,
      controller: function ($scope) {
        CampaignService.getViewComments(campaignProduct).then(
          function(response){
            $scope.viewComments = response;
            console.log($scope);
          }
        )
        $scope.closeMdDialog = function () {
          $mdDialog.hide();
        };
      }
    })
  }
  /*=============================
    | Chat Popup  
  =============================*/
  $scope.sendMessageOnEnter = function (ev) {
		if (ev.originalEvent.key == "Enter") {
			$scope.sendMessage();
		}
  };
  $scope.sendMessage = function () {
		const payload = {
			campaign_id: $stateParams.campaignId,
      product_id: $scope.campaignDetails.products.find((item) => $scope.selectedProductId == item.siteNo).product_id,
			message: $scope.message,
			user_type: $scope.selectedUser,
		};
		try {
			CampaignService.getCreateMessages(payload).then((result) => {
				if (result) {
  				$scope.message = "";
          CampaignService.getChatMessages($stateParams.campaignId).then((result) => {
            $scope.messageData = result.messages_data;
            $timeout(() => {
              $scope.el = document.getElementById("dialogContent_0");
              console.log($scope.el);
              if ($scope.el) {
                $scope.el.scrollTop = $scope.el.scrollHeight;
              }
            })
          });
				}
			});
		} catch (error) {
			console.log(error);
		}
  };

  $scope.getChatMessages = function() {
    try {
			CampaignService.getChatMessages($stateParams.campaignId).then(
        (result) => {
          if (result.messages_data.length != $scope.messageData.length) {             
            $scope.messageData = result.messages_data;
            $timeout(() => {
              $scope.el = document.getElementById("dialogContent_0");
              console.log($scope.el);
              if ($scope.el) {
                $scope.el.scrollTop = $scope.el.scrollHeight;
              } 
            })
          }
          if($scope.timeout) {
            clearTimeout($scope.timeout);
            $scope.timeout = null;
          }
          $scope.timeout = setTimeout($scope.getChatMessages,15000);
        }
			);
		} catch (error) {
			console.log(error);
		}
  }

  $scope.openChat = function () {
    const uniqueProd = new Set($scope.campaignDetails.products.map((prod) => prod.siteNo));
    $scope.uniqueProducts = Array.from(uniqueProd);
		$scope.currentUser = "admin";
		$scope.selectedProductId = "";
		$scope.selectedUser = "";
		$scope.message = "";
    $scope.messageData = [];
    $mdDialog.show({
      templateUrl: "views/chat-popup.html",
      clickOutsideToClose: false,
      preserveScope: true,
      fullscreen: $scope.customFullscreen,
      scope: $scope,
      controller: function ($scope) {
        $scope.closeDialog = function () {
          if($scope.timeout) {
            clearTimeout($scope.timeout);
            $scope.timeout = null;
          }
          $mdDialog.hide();
        };
      },
    });
    $scope.getChatMessages();
  };
  /*=============================
    | End of Chat Popup  
  =============================*/

  /*================================
  | Offer Request
  ================================*/
  $scope.offerProduct = null;

  $scope.offerRequestHandler = function(selectedProductId) {
    $scope.selectedProductId = selectedProductId;
  }

  $scope.sendOfferResquest = function(msg) {
    const payload = { 
      "campaign_id": $scope.campaignDetails.id, 
      "booking_id": $scope.selectedProductId, 
      "offer_id": $scope.activeOffer.id,
      "status": "1" ,
      "comment": msg
    }
    console.log(payload);
    try {
      CampaignService.sendOfferResponse(payload).then((result) => {
        toastr.success(result.message);
        $scope.commentsRequested = "";
        $scope.loadCampaignData($scope.campaignDetails.id);
      }).catch((error) => {
        toastr.error("Some error occurs please contact to admin");
        $scope.loadCampaignData($scope.campaignDetails.id);
      });
    } catch (error) {
      toastr.error("Some error occurs please contact to admin");
      $scope.loadCampaignData($scope.campaignDetails.id);
    }
  } 
  /*================================
  | End of Offer Request
  ================================*/
  $scope.lat = "";
  $scope.lng = "";
  // Generate the map image URL
  function generateMapImageUrl(lat, lng) {
    var apiKey = "AIzaSyAl05ze0VsbHB2lnp2VRXbNQHNyRzVWUQQ"; // Replace with your Google Maps API key
    var zoomLevel = 12;
    var imageSize = "380x280";
    var color = "color:red%7Clabel:C%7C" + lat + "," + lng;

    var mapImageUrl = "https://maps.googleapis.com/maps/api/staticmap?";
    mapImageUrl += "center=" + lat + "," + lng;
    mapImageUrl += "&markers=" + color;
    mapImageUrl += "&zoom=" + zoomLevel;
    mapImageUrl += "&size=" + imageSize;
    mapImageUrl += "&key=" + apiKey;
    return mapImageUrl;
  }
  
  $scope.userProfileApi = function(){
    CampaignService.getProfile().then(function(result){
        $scope.userProfile = result; 
        $scope.loading= false;
    })
  }
  // local Date for Pdf view
  $scope.cdate = new Date();
});