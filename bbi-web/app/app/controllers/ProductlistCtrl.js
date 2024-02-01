angular.module("bbManager").controller('ProductlistCtrl', function ($scope,MapService,$mdSidenav,$mdDialog,$state,ProductService,CampaignService,$rootScope,toastr, ManagementReportService, $timeout) {
    MapService.mapProducts().then(function (markers) {
        $scope.actualDataCopy=markers;
        $scope.productmarkerslist = markers;
    })
  ProductService.getFormatList().then(function (formats) {
      // $scope.formatList = formats;
      $scope.formatGrid = [];
      $scope.selectedFormats = [];
      var x = 3;
      var y = formats.length / x;
      var k = 0;
      for (var i = 0; i < y; i++) {
        var tempArr = [];
        for (var j = 0; j < x; j++) {
          tempArr.push(formats[k]);
          if (formats[k]) {
            $scope.selectedFormats.push(formats[k].id);
            k++;
          }
        }
        $scope.formatGrid.push(tempArr);
      }
    });
   $scope.FormatData=function (selectedZone) {
       $scope.productmarkerslist=$scope.actualDataCopy.filter(function (item) {
           return item.product_details[0].format_name===selectedZone;
       });
   };
   $scope.resetData=function(){
       $scope.productmarkerslist=$scope.actualDataCopy;
       $scope.siteNo='';
       $scope.area_name='';
   };
   $scope.getproddata = function (proddetails) {            
    $scope.productListDetails = proddetails;      
    $mdSidenav('productDetails').toggle();
  }
      $scope.formats = function () {
      $scope.filter = false;
      $scope.format = !$scope.format;
      $scope.shortlist = false;
      $scope.savedcampaign = false;
    }
    /*================================
| Multi date range picker options
================================*/
$scope.mapProductOpts = {
  multipleDateRanges: true,
  opens: 'center',
  locale: {
    applyClass: 'btn-green',
    applyLabel: "Select Dates",
    fromLabel: "From",
    format: "DD-MMM-YY",
    toLabel: "To",
    cancelLabel: 'X',
    customRangeLabel: 'Custom range'
  },
  isInvalidDate: function (dt) {
    for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
        if (moment(dt) >= moment($scope.unavailalbeDateRanges[i].booked_from) && moment(dt) <= moment($scope.unavailalbeDateRanges[i].booked_to)) {
            return true;
        }
    }
    if(moment(dt) < moment()){
        return true;
    }
},
isCustomDate: function (dt) {
    for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
        if (moment(dt) >= moment($scope.unavailalbeDateRanges[i].booked_from) && moment(dt) <= moment($scope.unavailalbeDateRanges[i].booked_to)) {
            if (moment(dt).isSame(moment($scope.unavailalbeDateRanges[i].booked_from), 'day')) {
                return ['red-blocked', 'left-radius'];
            } else if (moment(dt).isSame(moment($scope.unavailalbeDateRanges[i].booked_to), 'day')) {
                return ['red-blocked', 'right-radius'];
            } else {
                return 'red-blocked';
            }
        }
    }
    if(moment(dt) < moment()){
        return 'gray-blocked';
    }
},
eventHandlers: {
    'apply.daterangepicker': function(ev, picker) { 
        //selectedDateRanges = [];
    }
} 
};
/*====================================
| Multi date range picker options end
====================================*/      
$scope.addProductToExistingCampaign = function (existingCampaignId, productId, selectedDateRanges) {
  var productToCampaign = {
      product_id: productId,
      campaign_id: existingCampaignId
  };
  if (selectedDateRanges.length > 0) {
      productToCampaign.dates = selectedDateRanges;
  } else {
      toastr.error("Please select dates.");
      return false;
  }
  CampaignService.addProductToExistingCampaign(productToCampaign).then(function (result) {
      if (result.status == 1) {
          toastr.success(result.message);
          $mdSidenav('productDetails').close();
      } else {
          toastr.error(result.message);
      }
  });
}
$scope.IsDisabled = true;
$scope.EnableDisable = function () {
  $scope.IsDisabled = $scope.campaign.name.length == 0;
}
$scope.FilterProductlist = function(booked_from,booked_to){
  MapService.filterProducts(booked_from,booked_to).then(function (result) {
   productList = [];
              locArr = [];
              uniqueMarkers = [];
              concentricMarkers = {};
              var filterObj = {area: $scope.selectedAreas, product_type: $scope.selectedFormats, booked_from,booked_to};
              $scope.plottingDone = false;
              MapService.filterProducts(filterObj).then(function (markers) {
                  _.each(markersOnMap, function (v, i) {
                      v.setMap(null);
                      $scope.Clusterer.removeMarker(v);
                  });
                  markersOnMap = Object.assign([]);
                  $scope.filteredMarkers = markers;
                  $scope.processMarkers();
                  if (markers.length > 0) {
                      var bounds = new google.maps.LatLngBounds();
                      _.each(markersOnMap, function (v, i) {
                          bounds.extend(v.getPosition());
                      });
                  } else {
                      toastr.error("no marker found for the criteria you selected");
                  }
              });
});
}
    // SHORT-LIST
    $scope.shortlistSelected = function (productId, selectedDateRanges, ev) {
      var sendObj = {
        product_id: productId,
        dates: selectedDateRanges
      }
      MapService.shortListProduct(sendObj).then(function (response) {
        $mdDialog.show(
          $mdDialog.alert()
            .parent(angular.element(document.querySelector('body')))
            .clickOutsideToClose(true)
            .title('Cart Product')
            .textContent(response.message)
            .ariaLabel('shortlist-success')
            .ok('Confirmed!')
            .targetEvent(ev),
          $mdSidenav('productDetails').close()          
        );
        $state.reload();
        getShortListedProducts();
        $mdSidenav('productDetails').close();
      });
    }
    function getShortListedProducts() {
        MapService.getshortListProduct(JSON.parse(localStorage.loggedInUser).id).then(function (response) {
            shortListedProductsLength = response.shortlisted_products.length;
            $scope.shortListedProducts = response;
            $scope.shortListedProducts = response.shortlisted_products;
            $scope.shortListedTotal = response.shortlistedsum;
            $rootScope.$emit("shortListedProducts", shortListedProductsLength)
        });
    }
    getShortListedProducts();
    $scope.getProductUnavailableDates = function(productId, ev){
      MapService.getProductUnavailableDates(productId).then(function(dateRanges){
        $('.warn-text').text('');
                    $scope.unavailalbeDateRanges = dateRanges;
                    localStorage.setItem('mindays',$scope.product.minimumdays)
                    localStorage.setItem('fxed', $scope.product.fix)
                    $(ev.target).parents().eq(3).find('input').trigger('click');
      });
    }
    // SHORT-LIST ENDs
    // Save-camp
    $scope.toggleExistingCampaignSidenav = function () {
      $scope.showSaveCampaignPopup = !$scope.showSaveCampaignPopup;
    }
    // Save-camp-end
    // SAVE-CAMPPP
    $scope.saveCampaign = function (product_id, selectedDateRanges) {
      if (product_id) {
          $scope.campaign.products = [];
          var sendObj = {
              product_id: product_id,
          }

          if (selectedDateRanges.length > 0) {
              sendObj.dates = selectedDateRanges;
          } else {
              toastr.error("Please select dates.");
              return false;
          }
          $scope.campaign.products.push(sendObj);
          $form = $scope.forms.mySaveCampaignForm;
      } else {
          if ($scope.shortListedProducts.length > 0) {
              $scope.campaign.products = [];
              _.each($scope.shortListedProducts, function (v, i) {
                  $scope.campaign.products.push(v.id);
              });
              $form = $scope.forms.viewAndSaveCampaignForm;
          } else {
              toastr.error("Please shortlist some products first.");
          }

      }
      if ($scope.campaign.products) {
          CampaignService.saveUserCampaign($scope.campaign).then(function (response) {
              if (response.status == 1) {
                  //$scope.campaignSavedSuccessfully = true;
                  $timeout(function () {
                      $scope.campaign = {};
                      $form.$setPristine();
                      $form.$setUntouched();
                      toastr.success(response.message);
                      //$scope.campaignSavedSuccessfully = false;
                  }, 3000);
                  $scope.loadActiveUserCampaigns();
                  getShortListedProducts();
              } else {
                  $scope.saveUserCampaignErrors = response.message;
              }
          });
      }

  }
    // SAVE-CAMPPP END

    // Product list Functionality
    function correctLatLng(marker) {
        try {
            if (!marker.lat || marker.lat.toString().search(/[a-zA-Z]/i) > -1)
                marker.lat = 12.971599;
            if (!marker.lng || marker.lng.toString().search(/[a-zA-Z]/i) > -1)
                marker.lng = 77.594566;

            if (!marker._id.lat || marker._id.lat.toString().search(/[a-zA-Z]/i) > -1)
                marker._id.lat = 12.971599;
            if (!marker._id.lng || marker._id.lng.toString().search(/[a-zA-Z]/i) > -1)
                marker._id.lng = 77.594566;

            marker.lat = parseFloat(marker.lat);
            marker.lng = parseFloat(marker.lng);
            marker._id.lat = parseFloat(marker._id.lat);
            marker._id.lng = parseFloat(marker._id.lng);

            marker.product_details.forEach(p => {
                if (!p.lat || p.lat.toString().search(/[a-zA-Z]/i) > -1)
                p.lat = 12.971599;
                if (!p.lng || p.lng.toString().search(/[a-zA-Z]/i) > -1)
                    p.lng = 77.594566;

                p.lat = parseFloat(p.lat);
                p.lng = parseFloat(p.lng);
            });
        } catch(ex) {
            console.log('correctLatLng: Exception: '+ex.message);
        }
    }
    $scope.decreaseQuantity = function() {
        $scope.selectedQuantity -= 1;
        $scope.selectedQuantity1 -= 1;
    }

    $scope.increaseQuantity = function() {
        $scope.selectedQuantity += 1;
        $scope.selectedQuantity1 += 1;
    }
    $scope.productListData=[];
    $scope.loading= true;

    $scope.page_params = {
        page_no: 1,
        page_size: 5
    };

    $scope.searchTermAry = [
        {
            searchTerm: ''
        }
    ];
    $scope.searchTerms = [];

    /* Debounce */

    function debounce(func, timeout = 700){
        let timer;
        return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => { func.apply(this, args); }, timeout);
        };
    }
    
    /* End of Debounce */

    function keyUpHandler(searchTerm) {
        if(searchTerm == '' || searchTerm.length >= 3) {
            $scope.page_params.page_no = 1;
            $scope.searchTerms = $scope.searchTermAry.map(item => item.searchTerm).filter(item => item !=='');
            if ($scope.searchTerms && $scope.searchTerms.length>1) {
                $scope.searchTerms = $scope.searchTerms.join('::');
                mapProductsfiltered($scope.searchTerms, false);
            } else if($scope.searchTerms.length == 1) {
                mapProductsfiltered($scope.searchTerms[0], false);
            } else {
                mapProductsfiltered('', false);
            }
        }
    }

    $scope.searchTermChanged = debounce((searchTerm) => keyUpHandler(searchTerm))
  
    $scope.addNewSearchField = function() {
        $scope.searchTermAry.push({searchTerm: ''});
    }

    $scope.removeLastSearchField = function() {
        $scope.searchTermAry.pop();
        $scope.searchTermChanged("");
    }
  

    $scope.markers = [];
    function mapProductsfiltered(search_param = '', isFromScroll = true) {
        $scope.isProductsLoading = true;
        $scope.loading= false;
        MapService.mapProductsfiltered({page_params: $scope.page_params, search_param}).then(function (resMarkers) {
            if(isFromScroll) {
                $scope.markers = $scope.markers.concat(resMarkers);
            } else {
                $scope.markers = resMarkers;
            }
            var markers = $scope.markers;
            $scope.filteredMarkers = markers;
            $scope.actualDataCopy = markers;
            $scope.productmarkerslist = markers;
            $scope.productListData = [];
            // angular.forEach($scope.productmarkerslist, function (item) {
            angular.forEach(markers, function (item) {
                angular.forEach(item.product_details, function (newItem) {
                    newItem.isExpired = true;
                    const date = new Date();
                    if (newItem.to_date && newItem.to_date.$date && newItem.to_date.$date.$numberLong && newItem.to_date.$date.$numberLong >= date) {
                        newItem.isExpired = false;
                    } 
                    $scope.productListData.push(newItem);
                })
            })
            console.log('ProductListData ' + $scope.productListData.length);
            $mdSidenav('productList').toggle();
            $scope.loading= false;
            $scope.isProductsLoading = false;
        });
    }

    // $scope.isProductsLoading = false;
    mapProductsfiltered();

    const productListDiv = jQuery('#prodlist');
    productListDiv.on('scroll', (event) => {
        if (productListDiv.scrollTop() + Math.round(productListDiv.outerHeight()) >= event.target.scrollHeight) {
            console.log('scroll');
          if (!$scope.isProductsLoading) {
            $scope.page_params.page_no = parseInt($scope.page_params.page_no) + 1;
            if($scope.searchTerms.length) {
                mapProductsfiltered($scope.searchTerms);
            } else {
                mapProductsfiltered();
            }
          } 
        }
    });
    
    $scope.notifyMeProduct = null;
    $scope.notifyMeMessage = '';
    $scope.openNotifyMePopup = function(product) {
      $scope.notifyMeMessage = '';
      $scope.notifyMeProduct = product;
      $('#notifyMeModal-test').show();
    }

    $scope.hideNotifyMeModal = function() {
      $('#notifyMeModal-test').hide();
    }

    $scope.notifyMe = function(prodId, msg) {
      const payload = {
        "user_message": msg,
        /*"loggedinUser": JSON.parse(localStorage.loggedInUser).user_id,*/
        "loggedinUser": JSON.parse(localStorage.loggedInUser),
        "product_id": prodId
      };
      console.log(payload);
      ManagementReportService.notifyMe(payload).then(function(result) {
        if (result && result.status) {
          toastr.success(result.message);
          $scope.hideNotifyMeModal();
        } else {
          toastr.error(result.message);
        }
      });
    }


    $scope.getImageUrl = function(url) {
        if (url && (url.includes('.png') || url.includes('.PNG') || url.includes('.jpg') || url.includes('.jpeg') || url.includes('.JPEG') || url.includes('.JPG') || url.includes('.svg') || url.includes('.SVG'))) {
            return url;
        }
        return 'assets/images/no-image.jpg';
    }
    $scope.toggleProductDetailSidenav = function () {
        $scope.ranges.selectedDateRanges = [];
        $scope.totalPriceUserSelected = 0;
        $scope.totalnumDays = 0;
       // $scope.removeSelection();
        $mdSidenav('productDetails').close();
    }
    $scope.$watch('ranges.selectedDateRanges', function () {
        //$scope.totalPriceUserSelected = 0;
        $scope.totalnumDays = 0;
        $scope.newratecard = 0;
        var productPerDay = $scope.product?.rateCard / 28;
        console.log('productPerDay: => '+productPerDay);
        localStorage.removeItem('mindays');
        for (item in $scope.ranges?.selectedDateRanges) {
            console.log('index: => '+item);
            var startDate = moment($scope.ranges.selectedDateRanges[item].startDate).format('YYYY-MM-DD');
            var endDate = moment($scope.ranges.selectedDateRanges[item].endDate).format('YYYY-MM-DD');
            console.log('startDate: => '+startDate);
            console.log('endDate: => '+endDate);

            var slotDays = moment(endDate).diff(startDate, 'days') + 1;
            console.log('Slot Days: => '+slotDays);
            $scope.totalnumDays += slotDays;
            console.log('Total Days: => '+$scope.totalnumDays);
            //$scope.totalPriceUserSelected = productPerDay * $scope.totalnumDays;

            if ($scope.product.fix == 'Variable'){
                var tempValue = productPerDay * (Number($scope.product.minimumdays) > slotDays ? $scope.product.minimumdays : slotDays);
                console.log('tempValue: '+tempValue);
                $scope.newratecard += tempValue;
                //$scope.newratecard += Math.ceil(tempValue);
            } else { //for fixed products
                var pricePerSlot = productPerDay * $scope.product.minimumdays;
                console.log('pricePerSlot: '+pricePerSlot);
                var slotsCount = Math.ceil(slotDays / $scope.product.minimumdays);
                console.log('slotsCount: => '+slotsCount);
                $scope.newratecard += slotsCount * pricePerSlot;
                console.log('newratecard: => '+$scope.newratecard);
            }
        }
        if($scope.product?.minimumbooking) {
            if ($scope.product?.minimumbooking != 'No' && $scope.totalnumDays < $scope.product?.minimumbooking.split(" ")[0] && $scope.ranges.selectedDateRanges.length > 0) {
                $scope.ranges.selectedDateRanges = [];
                toastr.error("please select minimum " + $scope.product?.minimumbooking)
                return false;
            }
        }
        // Get quantity
        if ($scope.ranges && $scope.ranges.selectedDateRanges && $scope.ranges.selectedDateRanges.length) {
            if($scope.product && $scope.product.id) {
                var startDate = moment($scope.ranges.selectedDateRanges[0].startDate).format('YYYY-MM-DD');
                var endDate = moment($scope.ranges.selectedDateRanges[$scope.ranges.selectedDateRanges.length - 1].endDate).format('YYYY-MM-DD');
                if (startDate && endDate) {
                    $scope.quantity = 0;
                    $scope.selectedQuantity = 0;
                    $scope.selectedQuantity1 = 0;
                    var isLoading = false;
                    if (!isLoading) {
                        isLoading = true;
                        MapService.getQuantity($scope.product.id, startDate, endDate).then(function(result) {
                            console.log('Available quantity: '+result);
                            isLoading = false;
                            $scope.quantity = parseInt(result);
                        });
                    }
                }
            }
        }
    });
    $scope.selectFromTabIdSearch = function (marker) {
        $scope.product={};
        try {
          //  $scope.toggleProductDetailSidenav();
            if (marker.id) {
                // debugger;
                $mdSidenav('productDetails').open();
                $scope.currentImage = "";
                $scope.currentImage = config.serverUrl + marker['image'][0];
                $scope.$parent.alreadyShortlisted = false;
                $scope.product.id = marker['id'];
                $scope.product.image = config.serverUrl + marker['image'][0];
                $scope.product.siteNo = marker['siteNo'];
                $scope.product.area_name = marker['area_name'];
                $scope.product.panelSize = marker['panelSize'];
                $scope.product.Comments = marker['Comments'];
                $scope.product.isCommentsExist = $scope.product.Comments === ""? false:true;
                $scope.product.height = marker['height'];
                $scope.product.width = marker['width'];
                $scope.product.cancellation_policy = marker['cancellation_policy'];
                $scope.product.isCanelExist = $scope.product.cancellation_policy === ""? false:true;
                $scope.product.billing = marker['billingNo'] == 'no'? 'No':'Yes';
                $scope.product.servicing = marker['servicingNo'] == 'no'? 'No':'Yes';
                $scope.product.ageloopLength = marker['ageloopLength'];
                $scope.product.isLoopLengthExist = $scope.product.looplength === ""? false:true;
                $scope.product.spotLength = marker['spotLength'];
                $scope.product.isSpotLengthExist = $scope.product.spotLength === ""? false:true;
                $scope.product.unitQty = marker['unitQty'];
                $scope.product.mediahhi = marker['mediahhi'];
                $scope.product.isMediaHiExist = $scope.product.mediahhi === ""? false:true;
                $scope.product.vendor = marker['vendor'];
                $scope.product.strengths = marker['strengths'];
                $scope.product.address = marker['address'];
                $scope.product.city = marker['city'];
                $scope.product.notes = marker['notes'];
                $scope.product.isNotesExist = $scope.product.notes === ""? false:true;
                $scope.product.title = marker['title'];
                $scope.product.type = marker['type'];
                $scope.product.negotiatedCost = marker['negotiatedCost'];
                $scope.product.secondImpression = marker['secondImpression'];
                $scope.product.firstImpression = marker['firstImpression'];
                $scope.product.isFirstImpExist = $scope.product.firstImpression === ""? false:true;
                $scope.product.thirdImpression = marker['thirdImpression'];
                $scope.product.isThirdImpExist = $scope.product.thirdImpression === ""? false:true;
                $scope.product.forthImpression = marker['forthImpression'];
                $scope.product.isForthImpExist = $scope.product.forthImpression === ""? false:true;
                
                //$scope.product.lat = marker['lat'];
                //$scope.product.lng = marker['lng'];
                $scope.product.lat = marker.lat;
                $scope.product.lng = marker.lng;
                
                $scope.product.productioncost = marker['productioncost'];
                $scope.product.installCost = marker['installCost'];
                $scope.product.sound = marker['sound'];
                $scope.product.staticMotion = marker['staticMotion'];
                // $scope.product.isStaticMotionExist = $scope.product.staticMotion == $scope.product.type.name != 'Static';
                $scope.product.locationDesc = marker['locationDesc'];
                $scope.product.isLocationDescExist = $scope.product.locationDesc === ""? false:true;
                $scope.product.zipcode = marker['zipcode'];
                $scope.product.cpm = marker['cpm'];
                $scope.product.firstcpm = marker['firstcpm'];
                $scope.product.isFirstCpmExist = $scope.product.firstcpm === ""? false:true;
                $scope.product.thirdcpm = marker['thirdcpm'];
                $scope.product.isThirdCpmExist = $scope.product.thirdcpm === ""? false:true;
                $scope.product.forthcpm = marker['forthcpm'];
                $scope.product.isForthCpmExist = $scope.product.forthcpm === ""? false:true;
                $scope.product.sellerId = marker['sellerId'];
                $scope.product.isSellerIdExist = $scope.product.sellerId === ""? false:true;
                $scope.product.audited = marker['audited'];
                $scope.product.fix = marker['fix'];
                $scope.product.description = marker['description'];
                // $scope.product.demographicsage = marker['demographicsage'];
                // $scope.product.isDescExist = $scope.product.demographicsage === ""? false:true;
                $scope.product.fliplength = marker['fliplength'];
                $scope.product.fliplength = marker['fliplength'];
                $scope.product.weekPeriod = marker['weekPeriod'];
                $scope.product.rateCard = marker['rateCard'];
                $scope.product.venue = marker['venue'];
                $scope.product.lighting = marker['lighting'];
                $scope.product.direction = marker['direction'];
                $scope.product.availableDates = marker['availableDates'];
                $scope.product.slots = marker['slots'];
                $scope.product.file_type = marker['file_type'];
                $scope.product.isFileTypeExist = $scope.product.file_type === ""? false:true;
                $scope.product.medium = marker['medium'];
                $scope.product.isMediumExist = $scope.product.medium === ""? false:true;
                $scope.product.product_newMedia = marker['product_newMedia'];
                $scope.product.isProductNewAgeExist = $scope.product.product_newAge === ""? false:true;
                $scope.product.placement = marker['placement'];
                $scope.product.isPlacementExist = $scope.product.placement === ""? false:true;
                $scope.product.minimumbooking = marker['minimumbooking'];
                $scope.product.imgdirection = marker['imgdirection'];
                $scope.product.state = marker['state'];
                $scope.product.fix = marker['fix'];
                $scope.product.minimumdays = marker['minimumdays'];
                $scope.product.length = marker['length'];
                $scope.product.network = marker['network'];
                $scope.product.nationloc = marker['nationloc'];
                $scope.product.daypart = marker['daypart'];
                $scope.product.genre = marker['genre'];
                $scope.product.reach = marker['reach'];
                // $scope.product.costperpoint = marker['costperpoint'];
                var fromtime = marker.from_date.$date.$numberLong;
                $scope.product.fromTime = moment(new Date(+fromtime)).format('YYYY-MM-DD');
                var endTime =   marker.to_date.$date.$numberLong;
                $scope.product.endTime = moment(new Date(+endTime)).format('YYYY-MM-DD');
                $scope.hideSelectedMarkerDetail = false;
               

            } else {
                toastr.error('No product found with that tab id', 'error');
            }
        } catch(ex) {
            console.log('SelectFromTabSearch: Exception: '+ex.message);
        }
    }
        //Lazy Loading
        $scope.limit = 6;
        $scope.loadMore = function (last, inview) {
            if (last && inview) {
                $scope.limit += 6
            }   
        };
    
    $scope.showProductImagePopup = function (ev, proddetails) {
        $scope.specProductDetail = proddetails
        $mdDialog.show({
            locals: {
                specProductDetail: proddetails
            },
            templateUrl: 'views/image-popup-large.html',
            fullscreen: $scope.customFullscreen,
            clickOutsideToClose: true,
            controller: function ($scope, specProductDetail) {
                $scope.specDetails = specProductDetail;
                // $scope.productDetails = proddetails
                //$scope.img_src_1 = src.f_image;
                $scope.closeMdDialog = function () {
                    $mdDialog.hide();
                    localStorage.removeItem('mindays');
                }
            },
            resolve: { 
                imageCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
                  return $ocLazyLoad.load('./controllers/ImageCtrl.js');
                }],
            }
        });
    };
    //cart button
    $scope.shortlistSelected = function (productId, selectedDateRanges, ev, quantity) {
        var sendObj = {
            product_id: productId,
            dates: selectedDateRanges,
            booked_slots: 1,
            newratecard: $scope.newratecard,
            quantity: quantity
        }
        MapService.shortListProduct(sendObj).then(function (response) {
            if (response.status == 1) {
                $mdDialog.show(
                    $mdDialog.alert()
                    .parent(angular.element(document.querySelector('body')))
                    .clickOutsideToClose(true)
                    .title('Cart Product')
                    .textContent(response.message)
                    .ariaLabel('shortlist-success')
                    .ok('Confirmed!')
                    .targetEvent(ev),
                    $mdSidenav('productDetails').close()
                );
                setTimeout(() => {
                    $mdDialog.hide();
                } ,2000)
                $scope.removeSelection();
                getShortListedProducts();
                $mdSidenav('productDetails').close();
                $scope.productListData=[];
                $scope.loading=true;
                MapService.mapProductsfiltered().then(function (markers) {
                    $scope.filteredMarkers = markers;
                    $scope.actualDataCopy = markers;
                    $scope.productmarkerslist = markers;
                    angular.forEach($scope.productmarkerslist, function (item) {
                        angular.forEach(item.product_details, function (newItem) {
                            $scope.productListData.push(newItem);
                        })
                    })
                    $scope.productListData = $scope.productListData.filter(function (item) {
                        var date= new Date().getTime();
                        if(item.to_date.$date.$numberLong >= date){
                            return true;
                        }
                    });
                    console.log('640 ',$scope.productListData);
                   // $mdSidenav('productList').toggle();
                   $scope.loading=false;
                });
            } else if (response.status == 0) {
                toastr.error(response.message)
            }
        });
    }
    $scope.customOptions = {};
    $scope.removeSelection = function () {
        $scope.customOptions.clearSelection();
    }
    function getShortListedProducts() {
        MapService.getshortListProduct(JSON.parse(localStorage.loggedInUser).id).then(function (response) {
            shortListedProductsLength = response.shortlisted_products.length;
            $scope.shortListedProducts = response;
            $scope.shortListedProducts = response.shortlisted_products;
            $scope.shortListedTotal = response.shortlistedsum;
            $rootScope.$emit("shortListedProducts", shortListedProductsLength)
        });
    }
    getShortListedProducts();
    //zooom-in zoom-out
    $scope.isHover = true;
    $scope.zoomIn = function(event, imge) {
        imageZoom('myimage', 'myresult');
    }
    $scope.counter = 0;  
    $scope.zoomOut = function () {
        console.log('zoom out....'+ ++($scope.counter));
        result = document.getElementById('myresult');
        result.style.display = 'none';
        $scope.isHover = false;
    };
    $scope.inCounter = 0;
    function imageZoom(imgID, resultID) {
        $scope.isHover = true;
        //$timeout(function() {
            var img, lens, result, cx, cy;
            img = document.getElementById(imgID);
            result = document.getElementById(resultID);
            if(result) {
                result.style.display = 'block';
            }
            lens = document.getElementById('img-zoom-lens');
            /* Create lens: */
            if(!lens) {
                lens = document.createElement("DIV");
                lens.setAttribute("id", "img-zoom-lens");
            }
            /* Insert lens: */
            img.parentElement.insertBefore(lens, img);
            /* Calculate the ratio between result DIV and lens: */
            cx = result.offsetWidth / lens.offsetWidth;
            cy = result.offsetHeight / lens.offsetHeight;
            /* Set background properties for the result DIV */
            result.style.backgroundImage = "url('" + img.src + "')";
            result.style.backgroundSize = (img.width * cx) + "px " + (img.height * cy) + "px";
            /* Execute a function when someone moves the cursor over the image, or the lens: */
            lens.addEventListener("mousemove", moveLens);
            img.addEventListener("mousemove", moveLens);
            /* And also for touch screens: */
            lens.addEventListener("touchmove", moveLens);
            img.addEventListener("touchmove", moveLens);
            function moveLens(e) {
            var pos, x, y;
            /* Prevent any other actions that may occur when moving over the image */
            e.preventDefault();
            /* Get the cursor's x and y positions: */
            pos = getCursorPos(e);
            /* Calculate the position of the lens: */
            x = pos.x - (lens.offsetWidth / 2);
            y = pos.y - (lens.offsetHeight / 2);
            /* Prevent the lens from being positioned outside the image: */
            if (x > img.width - lens.offsetWidth) {x = img.width - lens.offsetWidth;}
            if (x < 0) {x = 0;}
            if (y > img.height - lens.offsetHeight) {y = img.height - lens.offsetHeight;}
            if (y < 0) {y = 0;}
            /* Set the position of the lens: */
            lens.style.left = x + "px";
            lens.style.top = y + "px";
            /* Display what the lens "sees": */
            result.style.backgroundPosition = "-" + (x * cx) + "px -" + (y * cy) + "px";
            }
            function getCursorPos(e) {
            var a, x = 0, y = 0;
            e = e || window.event;
            /* Get the x and y positions of the image: */
            a = img.getBoundingClientRect();
            /* Calculate the cursor's x and y coordinates, relative to the image: */
            x = e.pageX - a.left;
            y = e.pageY - a.top;
            /* Consider any page scrolling: */
            x = x - window.pageXOffset;
            y = y - window.pageYOffset;
            return {x : x, y : y};
            }
      //  });
      }
      //zoom
      
      $scope.sortAsc =function(headingName, type){
        $scope.upArrowColour = headingName;
        $scope.sortType ="Asc";
        if (type=="string"){
          $scope.newOfferData = $scope.productListData.map(e=>{
          return {
          ...e,
          first_name: e.first_name,
          company_type: e.company_type,
          email: e.email,
          company_name: e.company_name
          }
          })
          $scope.productListData = [];
          $scope.productListData = $scope.newOfferData.sort((a,b) =>{
            console.log(a[headingName])
            if(a[headingName] != null){
            return  a[headingName].localeCompare(b[headingName], undefined, {
              numeric: true,
              sensitivity: 'base'
            });
        }
          })
          
          // $scope.productList = $scope.newOfferData;
          }
        $scope.productListData = $scope.productListData.sort((a,b)=>{
      
             if(type == 'boolean'){
               return a[headingName] ? 1 : -1 
           }
           else if(type == 'date'){
            return new Date(a[headingName].date) - new Date(b[headingName].date)
      
        }
            else {
               return a[headingName] - b[headingName]
            }
        })
        console.log($scope.productListData)
      }
      
      
      $scope.sortDsc =function(headingName, type){
      $scope.downArrowColour = headingName;
      $scope.sortType ="Dsc";
      if (type=="string"){
      $scope.newOfferData = $scope.productListData.map(e=>{
      return {
      ...e,
      first_name: e.first_name,
      company_type: e.company_type,
      email: e.email,
      company_name: e.company_name
      }
      })
      $scope.productListData = [];
      $scope.productListData = $scope.newOfferData.sort((a,b) =>{
        console.log(a[headingName])
        if(b[headingName] != null){
        return  b[headingName].localeCompare(a[headingName], undefined, {
          numeric: true,
          sensitivity: 'base'
        });
      }
      })
      
      // $scope.RfpData = $scope.newOfferData;
      }
      $scope.productListData = $scope.productListData.sort((a,b)=>{
      
        if(type == 'boolean'){
           return a[headingName] ? -1 : 1 
       }
       else if(type == 'date'){
                return new Date(b[headingName].date) - new Date(a[headingName].date)
        
                    }
       else {
           return  b[headingName] - a[headingName] 
       }
      })
      console.log($scope.productListData)
      };
      //  Sorting 
})