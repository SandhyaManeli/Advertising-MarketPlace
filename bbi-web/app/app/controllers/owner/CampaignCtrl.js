angular.module("bbManager").controller('OwnerCampaignCtrl', function ($scope, $mdDialog, $mdSidenav, $timeout, $stateParams, $window, $rootScope, $location, Upload, OwnerCampaignService, OwnerProductService, toastr, CampaignService, ProductService, config, $state, FileSaver) {
    // MetroService
    $scope.forms = [];
    $scope.serverUrl = config.serverUrl;
    $scope.downloadDataDisplay= false;
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
        } else if ($scope.pagination.pageNo >= ($scope.pagination.pageCount - mid) && $scope.pagination.pageNo <= $scope.pagination.pageCount) {
            lowest = $scope.pagination.pageCount - pageLinks;
        } else if ($scope.pagination.pageNo > 0 && $scope.pagination.pageNo <= pageLinks / 2) {
            lowest = 1;
        } else {
            lowest = $scope.pagination.pageNo - mid + 1;
        }
        highest = $scope.pagination.pageCount < $scope.pagination.pageSize ? $scope.pagination.pageCount : lowest + pageLinks;
        $scope.pagination.pageArray = _.range(lowest, highest + 1);
    }

    /*===================
     | Pagination Ends
     ===================*/
    //  With Service
    //  $scope.downloadPdf = function () {
    //     var fileName = "file_name.pdf";
    //     var a = document.createElement("a");
    //     document.body.appendChild(a);
    //     OwnerCampaignService.downloadPdf().then(function (result) {
    //         var file = new Blob([result.data], {type: 'application/pdf'});
    //         var fileURL = window.URL.createObjectURL(file);
    //         a.href = fileURL;
    //         a.download = fileName;
    //         a.click();
    //     });
    // };

    // With out Service
    // $scope.getConvas=function()
    // 		{
    // 			html2canvas($("#barcodeHtml"), {
    // 				onrendered: function(canvas) {
    // 					document.body.appendChild(canvas);

    // 				}
    // 			});
    // 		}

    /*=======================
     | MdDialogs and sidenavs
     =======================*/
    $scope.showPaymentdailog = function () {
        $mdDialog.show({
            templateUrl: 'views/updatepaymentDailog.html',
            fullscreen: $scope.customFullscreen,
            clickOutsideToClose: true
        })
    };
    // $scope.addOwnerCampagin = function (ev) {
    //   $mdDialog.show({
    //     templateUrl: 'views/owner/addcampaign.html',
    //     fullscreen: $scope.customFullscreen,
    //     clickOutsideToClose: true
    //   })
    // };
    $scope.ownerCampaign = {};
    $scope.toggleAddCamapginSidenav = function () {
        $mdSidenav('ownerAddCmapginSidenav').toggle();
    };

    $scope.cancel = function () {
        $mdDialog.hide();
    };
    $scope.sharePerson = false;
    $scope.shareCampaign = function () {
        $scope.sharePerson = !$scope.sharePerson;
    }

    $scope.showCampaignPaymentSidenav = function () {
        $mdSidenav('campaignPaymentDetailsSidenav').toggle();
    };

    //campaign share
    // $scope.toggleShareCampaignSidenav = function (campaignDetails) {
    //     $scope.OwnerShareCampaign = campaignDetails;
    //     $mdSidenav('shareCampaignSidenav').toggle();
    // };

    $scope.paymentrefImage = function (img_src) {
        $mdDialog.show({
            locals: {
                src: config.serverUrl + img_src
            },
            templateUrl: 'views/owner/image-large.html',
            fullscreen: $scope.customFullscreen,
            clickOutsideToClose: true,
            controller: function ($scope, src) {
                $scope.img_src = src;
                $scope.closeMdDialog = function () {
                    $mdDialog.hide();
                }
            }
        });
    };

    /*===========================
     | MdDialogs and sidenavs end
     ===========================*/
    $scope.hidebutton = function () {
        if ($scope.campaignDetails.status >= 700) {
            return false;
        } else {
            return true;
        }
    }
    /*================================
     | Offer Response
    ================================*/
    $scope.offerProduct = null;

    $scope.offerRequestHandler = function(selectedProductId) {
        $scope.selectedProductId = selectedProductId;
    }

    $scope.sendOfferResponse = function(offerResp, msg) {
        const offer_id = $scope.offerDetails.offers[$scope.offerDetails.offers.length - 1].id;
        const payload = { 
            "campaign_id": $scope.campaignDetails.id, 
            "booking_id": $scope.selectedProductId, 
            "offer_id": offer_id,
            "status": offerResp == 'accept' ? 2 : 3 ,
            "comment": msg
        }
        CampaignService.sendOfferResponse(payload).then((result) => {
            console.log(result);
			toastr.success(result.message);
            $scope.AcceptedComments = "";
            $scope.commentsRejected = "";
            $scope.getUserCampaignDetails($scope.campaignDetails.id);
        })
    } 
    /*================================
     | End of Offer Response
    ================================*/

    /*================================
     | Multi date range picker options
     ================================*/
    $scope.suggestProductOpts = {
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
            if (moment(dt) < moment()) {
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
            if (moment(dt) < moment()) {
                return 'gray-blocked';
            }
        },
        eventHandlers: {
            'apply.daterangepicker': function (ev, picker) {
                //selectedDateRanges = [];
            }
        },
    };
    /*====================================
     | Multi date range picker options end
     ====================================*/

    ////data for image uploading 

    // $scope.data = {};
    // $scope.uploadFile = function (input) {
    //   $scope.data.fileName = input.files[0].name;
    //   if (input.files && input.files[0]) {
    //     var reader = new FileReader();
    //     reader.onload = function (e) {
    //       //Sets the Old Image to new New Image
    //       $('#photo-id').attr('src', e.target.result);
    //       //Create a canvas and draw image on Client Side to get the byte[] equivalent
    //       var canvas = document.createElement("canvas");
    //       var imageElement = document.createElement("img");
    //       imageElement.setAttribute('src', e.target.result);
    //       canvas.width = imageElement.width;
    //       canvas.height = imageElement.height;
    //       var context = canvas.getContext("2d");
    //       context.drawImage(imageElement, 0, 0);
    //       var base64Image = canvas.toDataURL("image/jpeg");
    //       //Removes the Data Type Prefix 
    //       //And set the view model to the new value
    //       $scope.data.uploadedPhoto = e.target.result.replace(/data:image\/jpeg;base64,/g, '');
    //     }
    //     //Renders Image on Page
    //     reader.readAsDataURL(input.files[0]);
    //   }
    // };
    $scope.ProductTypes = [{
            name: "Static"
        },
        {
            name: "Digital"
        },
        {
            name: "Digital/Static"
        },
        {
            name: "New Media"
        }
    ];

    function setDatesForOwnerProductsToSuggest(campaign) {
        $scope.SuggestprodStartDate = new Date(campaign.start_date);
        $scope.SuggestprodEndDate = new Date(campaign.end_date);
        $scope.SuggestprodfromMinDate = moment(campaign.start_date).toDate();
        $scope.SuggestprodfromMaxDate = moment(campaign.end_date).toDate();
        $scope.SuggestprodfromMaxDate = moment(campaign.end_date).toDate();
    }
    function setMinMaxDatesForCamapign() {
        $scope.minStartDate = new Date();
        $scope.minEndDate = moment($scope.ownerCampaign.start_date).add(1, 'days').toDate();
        $scope.ownerCampaign.end_date = $scope.minEndDate;
        $scope.defaultStartDate = new Date();
    }
    $scope.updateEndDateValidations = function () {
        $scope.minEndDate = moment($scope.ownerCampaign.start_date).add(1, 'days').toDate();
        if ($scope.ownerCampaign.end_date <= $scope.ownerCampaign.start_date) {
            $scope.ownerCampaign.end_date = $scope.minEndDate;
        }
    }
    /*==================================================
      | Get Campaigns List For Campaign Management Page  
    ===================================================*/

    $scope.tabHandle = function(tab_status = 'insertion_order') {
        $scope.getUserCampaignsForOwner(tab_status);
    }

    $scope.getUserCampaignsForOwner = function (tab_status) {
        $rootScope.http2_loading= true;
        return new Promise((resolve, reject) => {
            OwnerCampaignService.getUserCampaignsForOwner(tab_status).then(function (result) {
                if (result && result[0] === "") {
                    result.splice(0,1);
                }
                $scope.userCampaignPayments = result;
                /*
                // $scope.ownerSavedCampaigns = _.filter(result, function (c) {
                //     return c.status == 100 || c.status == 200;
                //   });                
                $scope.plannedCampaigns = _.filter(result, function (c) {
                    $scope.loading= false;
                    $scope.downloadDataDisplay= false;
                    // return c.status < 800 ;
                    // return c.status == 300 || c.status == 400 || c.status == 500 || c.status == 600;
                    return c.status == 600;
                });
                $scope.scheduledCampaigns = _.filter(result, function (c) { 
                    $scope.loading= false;
                    $scope.downloadDataDisplay= false;
                    return (c.status == 700 && HelperService.Campaign.isScheduled(c.start_date, c.end_date));
                });
                //        $scope.runningCampaigns = _.where(result, {
                //            status: 600
                //        });
                $scope.runningCampaigns = _.filter(result, function (c) {
                    $scope.loading= false;
                    $scope.downloadDataDisplay= false;
                    //    return c.status == 1141 && typeof c.name !== "undefined";
                    return (c.status == 800 && HelperService.Campaign.isRunning(c.start_date, c.end_date));
                });
                $scope.closedCampaigns = _.filter(result, function (c) {
                    $scope.loading= false;
                    $scope.downloadDataDisplay= false;
                    //return c.status > 800;
                    return (c.status == 1000 || c.status == 900 || HelperService.Campaign.isClosed(c.end_date));
                });
                */
                switch (tab_status) {
                    case 'insertion_order':
                      $scope.plannedCampaigns = result;
                      break;
                    case 'scheduled':
                    //   $scope.SheduledCampaigns = result.filter((c) => HelperService.Campaign.isScheduled(c.start_date, c.end_date));
                        $scope.SheduledCampaigns = result;
                      break;
                    case 'running':
                    //   $scope.runningCampaigns = result.filter((c) => HelperService.Campaign.isRunning(c.start_date, c.end_date));
                        $scope.runningCampaigns = result
                      break;
                    case 'closed':
                    //   $scope.closedCampaigns = result.filter((c) => HelperService.Campaign.isClosed(c.end_date));
                        $scope.closedCampaigns = result
                      break;
                }
                $scope.downloadDataDisplay= false;
                $rootScope.http2_loading= false;
                resolve(result);
            });
        });
    }
    /*=========================================================
      | End of Get Campaigns List For Campaign Management Page  
    ===========================================================*/
    var loadOwnerCampaigns = function () {
        return new Promise((resolve, reject) => {
            OwnerCampaignService.getOwnerCampaigns().then(function (result) {
                $scope.ownerCampaigns = result;
                $scope.loading= true;
                $scope.downloadDataDisplay= true;
                $scope.ownerCampaigns = _.filter(result, function (c) {
                    $scope.loading= false;
                    return c.status < 800;
                });
                $scope.scheduledCampaigns = _.filter(result, function (c) {
                    $scope.loading= false;
                    return c.status >= 800;
                });
                resolve(result);
            });
        });
    }
    // var loadMetroCampaigns = function () {
    //     return new Promise((resolve, reject) => {
    //         MetroService.getMetroCampaigns().then(function (result) {
    //             $scope.metrocampaign = _.filter(result, function (c) {
    //                 return c.status >= 1101;
    //             });
    //             resolve(result);
    //         });
    //     });
    // }
    var loadOwnerProductList = function () {
        OwnerProductService.getApprovedProductList($scope.pagination.pageNo, $scope.pagination.pageSize).then(function (result) {
            if (localStorage.selectedOwnerCampaign) {
                var selectedOwnerCampaign = JSON.parse(localStorage.selectedOwnerCampaign);
                $scope.campaignStartDate = selectedOwnerCampaign.start_date;
                $scope.campaignEndDate = selectedOwnerCampaign.end_date;
                $scope.campaignEstBudget = selectedOwnerCampaign.est_budget;
                $scope.campaignActBudget = selectedOwnerCampaign.act_budget;
                if (selectedOwnerCampaign.products && selectedOwnerCampaign.products.length > 0) {
                    _.map(result.products, function (p) {
                        if (_.find(JSON.parse(localStorage.selectedOwnerCampaign).products, {
                                id: p.id
                            }) !== undefined) {
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
    }
    // get all Campaigns by a user to show it in campaign management page ends  
    $scope.saveOwnerCampaign = function () {
        OwnerCampaignService.saveOwnerCampaign($scope.ownerCampaign).then(function (result) {
            if (result.status == 1) {
                // $scope.forms.ownerCampaignForm.$setPristine();
                // $scope.forms.ownerCampaignForm.$setUntouched();
                loadOwnerCampaigns();
                $scope.ownerCampaign = {};
                toastr.success(result.message);
                OwnerCampaignService.getCampaignWithProductsForOwner(result.camp_id).then(function (result) {
                    localStorage.selectedOwnerCampaign = JSON.stringify(result);
                    $location.path('/owner/' + $rootScope.clientSlug + '/campaigns');
					return loadData(result);
                });
                CreatecampFunction();
            } else if (result.status == 0) {
                // $rootScope.closeMdDialog();
                // if (result.message.constructor == Array) {
                //     $scope.ownerCampaignErrors = result.message;
                // } else {
                toastr.error(result.message);
                // }
            }
            // CreatecampFunction();
        });
    }

    function CreatecampFunction() {
        document.getElementById("createcampDropdown").classList.toggle("show");
    }




//  Sorting owner request insertion order
$scope.sortAsco =function(headingName, type){
    $scope.upArrowColour = headingName;
    $scope.sortType ="Asco";
    if (type=="string"){
      $scope.newOfferData = $scope.ownerCampaigns.map(e=>{
      return {
      ...e,
      cid: e.cid,
      name: e.name,
      email: e.email,
      company_name: e.company_name
      }
      })
      $scope.ownerCampaigns = [];
      $scope.ownerCampaigns = $scope.newOfferData.sort((a,b) =>{
        console.log(a[headingName])
        // if(a[headingName] != null){
        return  a[headingName].localeCompare(b[headingName], undefined, {
          numeric: true,
          sensitivity: 'base'
        });
    // }
      })
      
      // $scope.productList = $scope.newOfferData;
      }
    $scope.ownerCampaigns = $scope.ownerCampaigns.sort((a,b)=>{
  
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
    console.log($scope.ownerCampaigns)
  }
  
  
  $scope.sortDsco =function(headingName, type){
  $scope.downArrowColour = headingName;
  $scope.sortType ="Dsco";
  if (type=="string"){
  $scope.newOfferData = $scope.ownerCampaigns.map(e=>{
  return {
  ...e,
  cid: e.cid,
  name: e.name,
  email: e.email,
  company_name: e.company_name
  }
  })
  $scope.ownerCampaigns = [];
  $scope.ownerCampaigns = $scope.newOfferData.sort((a,b) =>{
    console.log(a[headingName])
    // if(b[headingName] != null){
    return  b[headingName].localeCompare(a[headingName], undefined, {
      numeric: true,
      sensitivity: 'base'
    });
//   }
  })
  
  // $scope.RfpData = $scope.newOfferData;
  }
  $scope.ownerCampaigns = $scope.ownerCampaigns.sort((a,b)=>{
  
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
  console.log($scope.ownerCampaigns)
  };
  //  Sorting 



  
//  Sorting owner planned
$scope.sortAsco =function(headingName, type){
    $scope.upArrowColour = headingName;
    $scope.sortType ="Asco";
    if (type=="string"){
      $scope.newOfferData = $scope.plannedCampaigns.map(e=>{
      return {
      ...e,
      cid: e.cid,
      name: e.name,
      email: e.email,
      company_name: e.company_name
      }
      })
      $scope.plannedCampaigns = [];
      $scope.plannedCampaigns = $scope.newOfferData.sort((a,b) =>{
        console.log(a[headingName])
        // if(a[headingName] != null){
        return  a[headingName].localeCompare(b[headingName], undefined, {
          numeric: true,
          sensitivity: 'base'
        });
    // }
      })
      
      // $scope.productList = $scope.newOfferData;
      }
    $scope.plannedCampaigns = $scope.plannedCampaigns.sort((a,b)=>{
  
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
    console.log($scope.plannedCampaigns)
  }
  
  
  $scope.sortDsco =function(headingName, type){
  $scope.downArrowColour = headingName;
  $scope.sortType ="Dsco";
  if (type=="string"){
  $scope.newOfferData = $scope.plannedCampaigns.map(e=>{
  return {
  ...e,
  cid: e.cid,
  name: e.name,
  email: e.email,
  company_name: e.company_name
  }
  })
  $scope.plannedCampaigns = [];
  $scope.plannedCampaigns = $scope.newOfferData.sort((a,b) =>{
    console.log(a[headingName])
    // if(b[headingName] != null){
    return  b[headingName].localeCompare(a[headingName], undefined, {
      numeric: true,
      sensitivity: 'base'
    });
//   }
  })
  
  // $scope.RfpData = $scope.newOfferData;
  }
  $scope.plannedCampaigns = $scope.plannedCampaigns.sort((a,b)=>{
  
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
  console.log($scope.plannedCampaigns)
  };
  //  Sorting 


  

//  Sorting owner planned
$scope.sortAscop =function(headingName, type){
    $scope.upArrowColour = headingName;
    $scope.sortType ="Ascop";
    if (type=="string"){
      $scope.newOfferData = $scope.plannedCampaigns.map(e=>{
      return {
      ...e,
      cid: e.cid,
      name: e.name,
      email: e.email,
      company_name: e.company_name
      }
      })
      $scope.plannedCampaigns = [];
      $scope.plannedCampaigns = $scope.newOfferData.sort((a,b) =>{
        console.log(a[headingName])
        // if(a[headingName] != null){
        return  a[headingName].localeCompare(b[headingName], undefined, {
          numeric: true,
          sensitivity: 'base'
        });
    // }
      })
      
      // $scope.productList = $scope.newOfferData;
      }
    $scope.plannedCampaigns = $scope.plannedCampaigns.sort((a,b)=>{
  
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
    console.log($scope.plannedCampaigns)
  }
  
  
  $scope.sortDscop =function(headingName, type){
  $scope.downArrowColour = headingName;
  $scope.sortType ="Dscop";
  if (type=="string"){
  $scope.newOfferData = $scope.plannedCampaigns.map(e=>{
  return {
  ...e,
  cid: e.cid,
  name: e.name,
  email: e.email,
  company_name: e.company_name
  }
  })
  $scope.plannedCampaigns = [];
  $scope.plannedCampaigns = $scope.newOfferData.sort((a,b) =>{
    console.log(a[headingName])
    // if(b[headingName] != null){
    return  b[headingName].localeCompare(a[headingName], undefined, {
      numeric: true,
      sensitivity: 'base'
    });
//   }
  })
  
  // $scope.RfpData = $scope.newOfferData;
  }
  $scope.plannedCampaigns = $scope.plannedCampaigns.sort((a,b)=>{
  
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
  console.log($scope.plannedCampaigns)
  };
  //  Sorting 

//  Sorting owner secheduled
$scope.sortAscs =function(headingName, type){
    $scope.upArrowColour = headingName;
    $scope.sortType ="Ascs";
    if (type=="string"){
      $scope.newOfferData = $scope.scheduledCampaigns.map(e=>{
      return {
      ...e,
      cid: e.cid,
      name: e.name,
      email: e.email,
      company_name: e.company_name
      }
      })
      $scope.scheduledCampaigns = [];
      $scope.scheduledCampaigns = $scope.newOfferData.sort((a,b) =>{
        console.log(a[headingName])
        // if(a[headingName] != null){
        return  a[headingName].localeCompare(b[headingName], undefined, {
          numeric: true,
          sensitivity: 'base'
        });
    // }
      })
      
      // $scope.productList = $scope.newOfferData;
      }
    $scope.scheduledCampaigns = $scope.scheduledCampaigns.sort((a,b)=>{
  
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
    console.log($scope.scheduledCampaigns)
  }
  
  
  $scope.sortDscs =function(headingName, type){
  $scope.downArrowColour = headingName;
  $scope.sortType ="Dscs";
  if (type=="string"){
  $scope.newOfferData = $scope.scheduledCampaigns.map(e=>{
  return {
  ...e,
  cid: e.cid,
  name: e.name,
  email: e.email,
  company_name: e.company_name
  }
  })
  $scope.scheduledCampaigns = [];
  $scope.scheduledCampaigns = $scope.newOfferData.sort((a,b) =>{
    console.log(a[headingName])
    // if(b[headingName] != null){
    return  b[headingName].localeCompare(a[headingName], undefined, {
      numeric: true,
      sensitivity: 'base'
    });
//   }
  })
  
  // $scope.RfpData = $scope.newOfferData;
  }
  $scope.scheduledCampaigns = $scope.scheduledCampaigns.sort((a,b)=>{
  
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
  console.log($scope.scheduledCampaigns)
  };
  //  Sorting 




  
//  Sorting owner secheduled
$scope.sortAscops =function(headingName, type){
    $scope.upArrowColour = headingName;
    $scope.sortType ="Ascops";
    if (type=="string"){
      $scope.newOfferData = $scope.scheduledCampaigns.map(e=>{
      return {
      ...e,
      cid: e.cid,
      name: e.name,
      email: e.email,
      company_name: e.company_name
      }
      })
      $scope.scheduledCampaigns = [];
      $scope.scheduledCampaigns = $scope.newOfferData.sort((a,b) =>{
        console.log(a[headingName])
        // if(a[headingName] != null){
        return  a[headingName].localeCompare(b[headingName], undefined, {
          numeric: true,
          sensitivity: 'base'
        });
    // }
      })
      
      // $scope.productList = $scope.newOfferData;
      }
    $scope.scheduledCampaigns = $scope.scheduledCampaigns.sort((a,b)=>{
  
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
    console.log($scope.scheduledCampaigns)
  }
  
  
  $scope.sortDscops =function(headingName, type){
  $scope.downArrowColour = headingName;
  $scope.sortType ="Dscops";
  if (type=="string"){
  $scope.newOfferData = $scope.scheduledCampaigns.map(e=>{
  return {
  ...e,
  cid: e.cid,
  name: e.name,
  email: e.email,
  company_name: e.company_name
  }
  })
  $scope.scheduledCampaigns = [];
  $scope.scheduledCampaigns = $scope.newOfferData.sort((a,b) =>{
    console.log(a[headingName])
    // if(b[headingName] != null){
    return  b[headingName].localeCompare(a[headingName], undefined, {
      numeric: true,
      sensitivity: 'base'
    });
//   }
  })
  
  // $scope.RfpData = $scope.newOfferData;
  }
  $scope.scheduledCampaigns = $scope.scheduledCampaigns.sort((a,b)=>{
  
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
  console.log($scope.scheduledCampaigns)
  };
  //  Sorting 

//  Sorting owner running
$scope.sortAscsr =function(headingName, type){
    $scope.upArrowColour = headingName;
    $scope.sortType ="Ascsr";
    if (type=="string"){
      $scope.newOfferData = $scope.runningCampaigns.map(e=>{
      return {
      ...e,
      cid: e.cid,
      name: e.name,
      email: e.email,
      company_name: e.company_name
      }
      })
      $scope.runningCampaigns = [];
      $scope.runningCampaigns = $scope.newOfferData.sort((a,b) =>{
        console.log(a[headingName])
        // if(a[headingName] != null){
        return  a[headingName].localeCompare(b[headingName], undefined, {
          numeric: true,
          sensitivity: 'base'
        });
    // }
      })
      
      // $scope.productList = $scope.newOfferData;
      }
    $scope.runningCampaigns = $scope.runningCampaigns.sort((a,b)=>{
  
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
    console.log($scope.runningCampaigns)
  }
  
  
  $scope.sortDscsr =function(headingName, type){
  $scope.downArrowColour = headingName;
  $scope.sortType ="Dscsr";
  if (type=="string"){
  $scope.newOfferData = $scope.runningCampaigns.map(e=>{
  return {
  ...e,
  cid: e.cid,
  name: e.name,
  email: e.email,
  company_name: e.company_name
  }
  })
  $scope.runningCampaigns = [];
  $scope.runningCampaigns = $scope.newOfferData.sort((a,b) =>{
    console.log(a[headingName])
    // if(b[headingName] != null){
    return  b[headingName].localeCompare(a[headingName], undefined, {
      numeric: true,
      sensitivity: 'base'
    });
//   }
  })
  
  // $scope.RfpData = $scope.newOfferData;
  }
  $scope.runningCampaigns = $scope.runningCampaigns.sort((a,b)=>{
  
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
  console.log($scope.runningCampaigns)
  };
  //  Sorting 


  



//  Sorting owner running
$scope.sortAscopsr =function(headingName, type){
    $scope.upArrowColour = headingName;
    $scope.sortType ="Ascopsr";
    if (type=="string"){
      $scope.newOfferData = $scope.runningCampaigns.map(e=>{
      return {
      ...e,
      cid: e.cid,
      name: e.name,
      email: e.email,
      company_name: e.company_name
      }
      })
      $scope.runningCampaigns = [];
      $scope.runningCampaigns = $scope.newOfferData.sort((a,b) =>{
        console.log(a[headingName])
        // if(a[headingName] != null){
        return  a[headingName].localeCompare(b[headingName], undefined, {
          numeric: true,
          sensitivity: 'base'
        });
    // }
      })
      
      // $scope.productList = $scope.newOfferData;
      }
    $scope.runningCampaigns = $scope.runningCampaigns.sort((a,b)=>{
  
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
    console.log($scope.runningCampaigns)
  }
  
  
  $scope.sortDscopsr =function(headingName, type){
  $scope.downArrowColour = headingName;
  $scope.sortType ="Dscopsr";
  if (type=="string"){
  $scope.newOfferData = $scope.runningCampaigns.map(e=>{
  return {
  ...e,
  cid: e.cid,
  name: e.name,
  email: e.email,
  company_name: e.company_name
  }
  })
  $scope.runningCampaigns = [];
  $scope.runningCampaigns = $scope.newOfferData.sort((a,b) =>{
    console.log(a[headingName])
    // if(b[headingName] != null){
    return  b[headingName].localeCompare(a[headingName], undefined, {
      numeric: true,
      sensitivity: 'base'
    });
//   }
  })
  
  // $scope.RfpData = $scope.newOfferData;
  }
  $scope.runningCampaigns = $scope.runningCampaigns.sort((a,b)=>{
  
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
  console.log($scope.runningCampaigns)
  };
  //  Sorting 

//  Sorting owner closed
$scope.sortAscsrc =function(headingName, type){
    $scope.upArrowColour = headingName;
    $scope.sortType ="Ascsrc";
    if (type=="string"){
      $scope.newOfferData = $scope.closedCampaigns.map(e=>{
      return {
      ...e,
      cid: e.cid,
      name: e.name,
      email: e.email,
      company_name: e.company_name
      }
      })
      $scope.closedCampaigns = [];
      $scope.closedCampaigns = $scope.newOfferData.sort((a,b) =>{
        console.log(a[headingName])
        // if(a[headingName] != null){
        return  a[headingName].localeCompare(b[headingName], undefined, {
          numeric: true,
          sensitivity: 'base'
        });
    // }
      })
      
      // $scope.productList = $scope.newOfferData;
      }
    $scope.closedCampaigns = $scope.closedCampaigns.sort((a,b)=>{
  
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
    console.log($scope.closedCampaigns)
  }
  
  
  $scope.sortDscsrc =function(headingName, type){
  $scope.downArrowColour = headingName;
  $scope.sortType ="Dscsrc";
  if (type=="string"){
  $scope.newOfferData = $scope.closedCampaigns.map(e=>{
  return {
  ...e,
  cid: e.cid,
  name: e.name,
  email: e.email,
  company_name: e.company_name
  }
  })
  $scope.closedCampaigns = [];
  $scope.closedCampaigns = $scope.newOfferData.sort((a,b) =>{
    console.log(a[headingName])
    // if(b[headingName] != null){
    return  b[headingName].localeCompare(a[headingName], undefined, {
      numeric: true,
      sensitivity: 'base'
    });
//   }
  })
  
  // $scope.RfpData = $scope.newOfferData;
  }
  $scope.closedCampaigns = $scope.closedCampaigns.sort((a,b)=>{
  
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
  console.log($scope.closedCampaigns)
  };
  //  Sorting 



  
//  Sorting owner closed
$scope.sortAscopsrc =function(headingName, type){
    $scope.upArrowColour = headingName;
    $scope.sortType ="Ascopsrc";
    if (type=="string"){
      $scope.newOfferData = $scope.closedCampaigns.map(e=>{
      return {
      ...e,
      cid: e.cid,
      name: e.name,
      email: e.email,
      company_name: e.company_name
      }
      })
      $scope.closedCampaigns = [];
      $scope.closedCampaigns = $scope.newOfferData.sort((a,b) =>{
        console.log(a[headingName])
        // if(a[headingName] != null){
        return  a[headingName].localeCompare(b[headingName], undefined, {
          numeric: true,
          sensitivity: 'base'
        });
    // }
      })
      
      // $scope.productList = $scope.newOfferData;
      }
    $scope.closedCampaigns = $scope.closedCampaigns.sort((a,b)=>{
  
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
    console.log($scope.closedCampaigns)
  }
  
  
  $scope.sortDscopsrc =function(headingName, type){
  $scope.downArrowColour = headingName;
  $scope.sortType ="Dscopsrc";
  if (type=="string"){
  $scope.newOfferData = $scope.closedCampaigns.map(e=>{
  return {
  ...e,
  cid: e.cid,
  name: e.name,
  email: e.email,
  company_name: e.company_name
  }
  })
  $scope.closedCampaigns = [];
  $scope.closedCampaigns = $scope.newOfferData.sort((a,b) =>{
    console.log(a[headingName])
    // if(b[headingName] != null){
    return  b[headingName].localeCompare(a[headingName], undefined, {
      numeric: true,
      sensitivity: 'base'
    });
//   }
  })
  
  // $scope.RfpData = $scope.newOfferData;
  }
  $scope.closedCampaigns = $scope.closedCampaigns.sort((a,b)=>{
  
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
  console.log($scope.closedCampaigns)
  };
  //  Sorting 
    // function CreatecampFunctions() {
    //     document.getElementById("myDropdownView").classList.toggle("show");
    // }
    // $scope.saveMetroCampaign = function (metroCampagin) {
    //     MetroService.saveMetroCampaign(metroCampagin).then(function (result) {
    //         if (result.status == 1) {
    //             $scope.metroCampagin = {};
    //             // $scope.forms.MetroCampaign.$setPristine();
    //             // $scope.forms.MetroCampaign.$setUntouched();
    //             loadMetroCampaigns();
    //             $window.location.href = '/owner/{{clientSlug}}/metro-campaign-details/' + result.metro_camp_id;
    //             toastr.success(result.message);
    //         } else if (result.status == 0) {
    //             $rootScope.closeMdDialog();
    //             if (result.message.constructor == Array) {
    //                 $scope.MetroCampaignErrors = result.message;
    //             } else {
    //                 toastr.error(result.message);
    //             }
    //         } else {
    //             toastr.error(result.message);
    //         }
    //         CreatecampFunction();
    //     });
    // }

    $scope.toggleShareCampaignSidenav = function (campaign) {
        $scope.currentOwnerShareCampaign = campaign;
        $mdSidenav('shareCampaignSidenav').toggle();
    };

    $scope.changeQuoteRequest = function (campaignId, remark, type) {
        $scope.changeRequest = {};
        $scope.changeRequest.for_campaign_id = campaignId;
        $scope.changeRequest.remark = remark;
        $scope.changeRequest.type = type;
        OwnerCampaignService.requestChangeInQuote($scope.changeRequest).then(function (result) {
            if (result.status == 1) {
                $scope.getUserCampaignDetails(campaignId);
                //$mdDialog.hide();
                toastr.success(result.message);
            } else {
                toastr.error(result.message);
            }
        });
    }
    $scope.addToCampaign = function (products) {
        console.log('Added: '+products.length);
        console.log(products);
        //alert('Yes');

        OwnerCampaignService.allCampaignRequests(products).then(function(result) {
            console.log(result);
            console.log(result);
            if (result && result.status) {
                console.log('success');
            } else if (result.status == 0) {
                toastr.error(result.message);
            }
        });
    }

    $scope.DeleteSellCampaign = function () {
        var params =  $location.$$url.split("/")[4]
        OwnerCampaignService.deleteSellerCampaign(params).then(function (result) {
          if (result.status == 1) {
            toastr.success(result.message);
            if($location.$$url.split("/")[5] == "2") {
              $window.location.href = "/owner/demo---landmark-ooh/campaigns";
            } else {
              $window.location.href = "/owner/demo---landmark-ooh/bba-campaigns";
            }
            
          } else {
            toastr.error(result.message);
          }
        });
      }


    $scope.suggestProductForOwnerCampaign = function (ownerProduct) {
        if (!localStorage.selectedOwnerCampaign) {
            toastr.error("No Campaign is seleted. Please select which campaign you're adding this product in to.")
        } else {
            var postObj = {
                campaign_id: JSON.parse(localStorage.selectedOwnerCampaign).id,
                product: {
                    id: ownerProduct.id,
                    booking_dates: ownerProduct.booking_dates,
                    price: ownerProduct.rateCard,
                    booked_slots: 1
                }
            };
            OwnerCampaignService.proposeProductForCampaign(postObj).then(function (result) {
                if (result.status == 1) {
                    OwnerCampaignService.getOwnerCampaignDetails(JSON.parse(localStorage.selectedOwnerCampaign).id).then(function (updatedCampaignData) {
                        localStorage.selectedOwnerCampaign = JSON.stringify(updatedCampaignData);
                        $scope.campaignActBudget = updatedCampaignData.act_budget;
                        _.map($scope.productList, function (product) {
                            if (product.id == ownerProduct.id) {
                                product.alreadyAdded = true;
                            }
                            return product;
                        });
                    });
                    if ($scope.selectedOwnerCampaign.products || $scope.selectedOwnerCampaign.products.length >= 0) {
                        $scope.selectedOwnerCampaign.products.length++;
                    }
                    toastr.success(result.message);
                } else if (result.status == 0) {
                    toastr.error(result.message);
                }
            });
        }
    }

    $scope.getProductUnavailableDates = function (productId, ev, index) {
        // if (typeof index == "number") {
        //     $scope.productList[index].focusProduct = true
        // }
        // if (productId.type == "Bulletin") {
        //     OwnerProductService.getProductUnavailableDates(productId.id).then(function (dateRanges) {
        //         $scope.unavailalbeDateRanges = dateRanges;
        //         $(ev.target).parent().parent().find('input').trigger('click');
        //     });

        // } else {
            OwnerProductService.getProductDigitalUnavailableDates(productId.id).then(function (blockedDatesAndSlots) {
                $scope.unavailalbeDateRanges = [];
                blockedDatesAndSlots.forEach((item) => {
                    if (item.booked_slots >= productId.slots) {
                        $scope.unavailalbeDateRanges.push(item);
                    }
                })
                $(ev.target).parent().parent().find('input').trigger('click');
            })
        //}
    }


     /* ============================
     | Offer details section
     ============================= */
    
    $scope.offerDetails = {
        offers: [],
        status: {
        10: "Requested",
        20: "Accepted",
        30: "Accepted",
        40: "Rejected",
        50: "Rejected",
        },
        isAccepted: false,
    };

    $scope.getCampaignOffers = function(id) {
        CampaignService.getCampaignOffers(id).then((result) => {
            console.log(result);
            $scope.offerDetails.offers = result;
            if (result.length >= 2) {
                $scope.offerDetails.isAccepted =
                  result[1].status == 20 || result[1].status == 30;
            } else if (result.length == 1) {
                $scope.offerDetails.isAccepted =
                  result[0].status == 20 || result[0].status == 30;
            }
        })
    }

    /* ============================
     | End Offer details section
     ============================= */

    /* ============================
     | Campaign details section
     ============================= */

    function convertDateToMMDDYYYY (dates,areaTimeZoneType) {
        const startDate = dates.booked_from;
        const endDate = dates.booked_to;
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

    $scope.campaignDetails = {};

    $scope.getUserCampaignDetails = function (campaignId) {
        OwnerCampaignService.getCampaignWithProductsForOwner(campaignId).then(function (result) {
            $scope.campaignDetails = result;
					return loadData(result);
            $scope.campaignDetails.products.forEach((product) => {
                product.mapImageUrl = generateMapImageUrl(product.lat, product.lng);
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
            if($scope.campaignDetails.products_in_campaign != null){
                $scope.campaignDetails.products_in_campaign.forEach(
                    (product_data) => {
                        product_data.mapImageUrl = generateMapImageUrl(product_data.lat, product_data.lng);
                    });
            }
            console.log($scope.campaignDetails.products);
            if (typeof result.act_budget === 'number' && result.act_budget % 1 == 0) {
                // $scope.campaignDetails.gst = result.act_budget * 18 / 100;
                $scope.campaignDetails.subTotal = result.act_budget;
                $scope.campaignDetails.grandTotal = $scope.campaignDetails.subTotal;
                $scope.PendingPay = $scope.campaignDetails.act_budget - result.total_paid;
            }
            // if ($scope.campaignDetails.gst_price != "0") {
            //     $scope.onchecked = true;
            // $scope.GST = ($scope.campaignDetails.act_budget / 100) * 18;
            // $scope.TOTAL = $scope.campaignDetails.act_budget;
            //   } else {
            //     $scope.onchecked = false;
            //     $scope.GST = "0";
            //     $scope.TOTAL = $scope.campaignDetails.act_budget;
            //   }  
            $scope.TOTAL = $scope.campaignDetails.act_budget;
        });
        $scope.getCampaignOffers(campaignId);
    }
    $scope.getOwnerCampaignDetails = function (campaignId) {
        OwnerCampaignService.getOwnerCampaignDetails(campaignId).then(function (result) {
            $scope.campaignDetails = result;
            $scope.campaignDetails.products.forEach((product) => {
                const {startDate, endDate} = convertDateToMMDDYYYY(product,product.area_time_zone_type);
                product["date"] = {
                  startDate,endDate
                }
            });
            console.log($scope.campaignDetails.products);
            if (typeof result.act_budget === 'number' && result.act_budget % 1 == 0) {
                // $scope.campaignDetails.gst = result.act_budget * 18 / 100;
                // $scope.campaignDetails.subTotal = result.act_budget ;
                // $scope.campaignDetails.grandTotal = $scope.campaignDetails.subTotal;
                //$scope.PendingPay = $scope.campaignDetails.act_budget - result.total_paid;
            }
            // if ($scope.campaignDetails.gst_price != "0") {
            //     $scope.onchecked = true;
            //     $scope.GST = ($scope.campaignDetails.act_budget / 100) * 18;
            // $scope.TOTAL = $scope.campaignDetails.act_budget + $scope.GST;
            //   } else {
            //     $scope.onchecked = false;
            //     $scope.GST = "0";
            //     $scope.TOTAL = $scope.campaignDetails.act_budget +  parseInt($scope.GST);
            //   }
        });
    }
    // $scope.uncheck = function(checked) {
    //     if (!checked) {
    //       $scope.GST = "0";
    //       $scope.TOTAL = $scope.campaignDetails.act_budget + parseInt($scope.GST);
    //     }else{
    //       $scope.GST = ($scope.campaignDetails.act_budget / 100) * 18;
    //         $scope.TOTAL = $scope.campaignDetails.act_budget + $scope.GST;
    //     }
    // };

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

    // function getMetroCampaignDetails() {
    //     MetroService.getMetroCampaigns().then((result) => {
    //         $scope.metrocampaign = result;
    //     });
    // }

    // function getMetroCampDetails(metroCampaignId) {
    //     MetroService.getMetroCampDetails(metroCampaignId).then((result) => {
    //         $scope.metroCampaginDetails = result;
    //     });
    // }


    $scope.viewProductImage = function (image) {
        var imagePath = config.serverUrl + image;
        $mdDialog.show({
            locals: {
                src: imagePath
            },
            templateUrl: 'views/image-popup-large.html',
            preserveScope: true,
            scope: $scope,
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
    }
    $scope.closeDialog = function () {
        $mdDialog.hide();
    }
    $scope.finalizeCampaign = function () {
        OwnerCampaignService.finalizeCampaignByOwner($scope.campaignDetails.id).then(function (result) {
            if (result.status == 1) {
                toastr.success("Campaign Finalized!");
            } else {
                toastr.error(result.message);
            }
        });
    }

    $scope.editProposedProduct = function (productId, from_date, to_date, price) {
        var productObj = {
            id: productId,
            from_date: $scope.campaignDetails.start_date,
            to_date: $scope.campaignDetails.end_date,
            price: price
        };
        $mdDialog.show({
            locals: {
                campaign: $scope.campaignDetails,
                productObj: productObj,
                ctrlScope: $scope
            },
            templateUrl: 'views/owner/edit-proposed-product.html',
            fullscreen: $scope.customFullscreen,
            clickOutsideToClose: true,
            controller: function ($scope, $mdDialog, ctrlScope, campaign, productObj) {
                $scope.product = productObj;
                $scope.OwnerProposalStartDate = new Date(campaign.start_date);
                $scope.OwnerProposalEndDate = new Date(campaign.end_date);
                $scope.OwnerProposalFromMinDate = moment(campaign.start_date).toDate();
                $scope.OwnerProposalFromMaxDate = moment(campaign.end_date).toDate();
                $scope.OwnerProposaltoMinDate = moment($scope.product.start_date).toDate();
                $scope.OwnerProposalToMaxDate = moment(campaign.end_date).toDate();
                $scope.updateProposedProduct = function (product) {
                    OwnerCampaignService.updateProposedProduct(campaign.id, $scope.product).then(function (result) {
                        if (result.status == 1) {
                            // update succeeded. update the grid now.
                            if (campaign.type != "2") {
                                ctrlScope.getUserCampaignDetails(campaign.id);
                            } else {
                                ctrlScope.getOwnerCampaignDetails(campaign.id);
                            }
                            $mdDialog.hide();
                            toastr.success(result.message);
                        } else {
                            toastr.error(result.message);
                        }
                    });
                }
                $scope.closeMdDialog = function () {
                    $mdDialog.hide();
                }
            }
        });
    }
    $scope.addNewProductToCampaign = function () {
        localStorage.selectedOwnerCampaign = JSON.stringify($scope.campaignDetails);
        $location.path('/owner/' + $rootScope.clientSlug + '/add-campagin-product');
    }

    $scope.removeProductFromCampaignSuggestion = function (productId) {
        var campaignId = JSON.parse(localStorage.selectedOwnerCampaign).id;
        OwnerCampaignService.deleteProductFromCampaign(campaignId, productId).then(function (result) {
            if (result.status == 1) {
                OwnerCampaignService.getOwnerCampaignDetails(JSON.parse(localStorage.selectedOwnerCampaign).id).then(function (updatedCampaignData) {
                    localStorage.selectedOwnerCampaign = JSON.stringify(updatedCampaignData);
                    $scope.campaignActBudget = updatedCampaignData.act_budget;
                    _.map($scope.productList, function (product) {
                        if (product.id == productId) {
                            product.alreadyAdded = false;
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

    $scope.bookOwnerCampaign = function (campaignId, ev) {
        // if ($scope.onchecked === true) {
        //     $scope.flag = 1;
        //     // $scope.GST = ($scope.campaignDetails.act_budget / 100) * 18;
        //   } else if ($scope.onchecked === false) {
        //     $scope.flag = 0;
        //     // $scope.GST = "0";
        //   } else{
        //     $scope.flag = 1;
        //   }    
        OwnerCampaignService.bookNonUserCampaign(campaignId).then(function (result) {
            if (result.status == 1) {
                $mdDialog.show(
                    $mdDialog.alert()
                    .parent(angular.element(document.querySelector('body')))
                    .clickOutsideToClose(true)
                    .title("Congrats!!")
                    .textContent(result.message)
                    .ariaLabel('Alert Dialog Demo')
                    .ok('Confirmed!')
                    .targetEvent(ev)
                );
                $scope.getOwnerCampaignDetails(campaignId);
                $state.reload();
            } else {
                if (result.product_ids && result.product_ids.length > 0) {
                    toastr.error(result.message);
                    _.map($scope.campaignDetails.products, (p) => {
                        if (_.contains(result.product_ids, p.product_id)) {
                            p.unavailable = true;
                        }
                    });
                } else {
                    toastr.error(result.message);
                }
            }
        });
    }

    $scope.deleteOwnerCampaign = function (campaignId) {
        OwnerCampaignService.deleteOwnerCampaign(campaignId).then(function (result) {
            if (result.status == 1) {
                loadOwnerCampaigns();
                toastr.success(result.message);
            } else {
                toastr.error(result.message);
            }
        })
    }

    $scope.closeCampaign = function (campaignId, ev) {
        OwnerCampaignService.closeCampaign(campaignId).then(function (result) {
            if (result.status == 1) {
                $mdDialog.show(
                    $mdDialog.alert()
                    .parent(angular.element(document.querySelector('body')))
                    .clickOutsideToClose(true)
                    .title("Success!!")
                    .textContent(result.message)
                    .ariaLabel('Alert Dialog Demo')
                    .ok('Confirmed!')
                    .targetEvent(ev)
                );
                $scope.getOwnerCampaignDetails(campaignId);
            } else {
                toastr.error(result.message);
            }
        });
    }


    //SORTING FOR INVENTORY LIST
$scope.sortAsc =function(headingName, type){
    $scope.upArrowColour = headingName;
    $scope.sortType ="Asc";
    if (type=="string"){
      $scope.newOfferData = $scope.userCampaignPayments.map(e=>{
      return {
      ...e,
      name: e.name,
      user_email: e.user_email
      }
      })
      $scope.userCampaignPayments = [];
      $scope.userCampaignPayments = $scope.newOfferData.sort((a,b) =>{
        console.log(a[headingName])
        if(a[headingName] != undefined){
        return  a[headingName].localeCompare(b[headingName], undefined, {
          numeric: true,
          sensitivity: 'base'
        });
    }
      })
    
      
      // $scope.productList = $scope.newOfferData;
      }
    $scope.userCampaignPayments = $scope.userCampaignPayments.sort((a,b)=>{
  
         if(type == 'boolean'){
           return a[headingName] ? 1 : -1 
       }
        else {
           return a[headingName] - b[headingName]
        }
    })
    console.log($scope.userCampaignPayments)
  }
  $scope.sortDsc =function(headingName, type){
    $scope.downArrowColour = headingName;
    $scope.sortType ="Dsc";
    if (type=="string"){
      $scope.newOfferData = $scope.userCampaignPayments.map(e=>{
      return {
      ...e,
      name: e.name,
      user_email: e.user_email
      }
      })
      $scope.userCampaignPayments = [];
      $scope.userCampaignPayments = $scope.newOfferData.sort((a,b) =>{
        console.log(a[headingName])
        if(b[headingName]){
        return  b[headingName].localeCompare(a[headingName], undefined, {
          numeric: true,
          sensitivity: 'base'
        });
    }
      })
      
      // $scope.RfpData = $scope.newOfferData;
      }
   $scope.userCampaignPayments = $scope.userCampaignPayments.sort((a,b)=>{
  
        if(type == 'boolean'){
           return a[headingName] ? -1 : 1 
       }
       else {
           return  b[headingName] - a[headingName] 
       }
   })
   console.log($scope.userCampaignPayments)
  };

    $scope.deleteProductFromCampaign = function (campaignId, productId) {
        OwnerCampaignService.deleteProductFromCampaign(campaignId, productId).then(function (result) {
            if (result.status == 1) {
                if ($stateParams.campaignType == 2) {
                    $scope.getOwnerCampaignDetails(campaignId);
                } else {
                    $scope.getUserCampaignDetails(campaignId);
                }
                toastr.success(result.message);
            } else {
                toastr.error(result.message);
            }
        });
    }

    $scope.productOwnerPrice = function (productPrice, productId) {
        $scope.campaignDetails.products.forEach(element => {
            if (element.owner_price === undefined && element.id === productId) {
                element.owner_price = productPrice;
            }
        });
    }
    /* ==============================
     | Campaign details section ends
     =============================== */
    // filter-code
    $scope.viewSelectedProduct = function (product) {
        $scope.pagination.pageCount = 1;
        $scope.productList = [product];
    }
    $scope.productSearch = function (query) {
        return ProductService.searchProducts(query.toLowerCase()).then(function (res) {
            $scope.productList = res;
            $scope.pagination.pageCount = 1;
            return res;
        });
    }

    $scope.applymethod = function (product) {
        var data = {};
        var pageNo = $scope.pagination.pageNo;
        var pageSize = $scope.pagination.pageSize;
        var format = product.type;
        var budget = product.budgetprice;
        var start_date = product.start_date;
        var end_date = product.end_date;
        if (!format) {
            format = '';
        }
        if (!budget) {
            budget = '';
        }
        if (pageNo || pageSize || format || budget || start_date || end_date) {
            data.page_no = pageNo;
            data.page_size = pageSize;
            data.format = format;
            data.budget = budget;
            data.start_date = start_date;
            data.end_date = end_date;
        }
        OwnerProductService.getApprovedProductList(data).then(function (result) {
            $scope.productList = result.products;
            $scope.pagination.pageCount = result.page_count;
            if ($window.innerWidth >= 420) {
                createPageLinks();
            } else {
                $scope.getRange(0, result.page_count);
            }
        });
    }
    var getFormatList = function () {
        OwnerProductService.getFormatList().then(function (result) {
            $scope.formatList = result;
        });
    }
    getFormatList();
    // Filter-code ends
    function getActiveUserCampaigns() {
        CampaignService.getActiveUserCampaigns().then(function (result) {
            $scope.ownerSaved = result;
            $scope.loading= true;
            $scope.ownerSavedCampaigns = _.filter(result, function (c) {
                $scope.loading= false;
                return c.status == 100 || c.status == 200;
            });
        });
    }
    $scope.clearOwnerProductFilter = function (product) {
        $scope.product = {};
        loadOwnerProductList("All")
    }
    /* ==============================
     | Campaign payment section
     =============================== */
    function getCampaignWithPayments() {
        OwnerCampaignService.getCampaignWithPayments().then(function (result) {
            $scope.campaignsWithPayments = result;
        });
    }

    $scope.getCampaignPaymentDetails = function (campaignId) {
        // localStorage.campaignPaymentDetailsCampaignId= campaignId;
        OwnerCampaignService.getCampaignPaymentDetails(campaignId).then(function (result) {
            $scope.campaignPaymentDetails = result;
            var campaignPayments = $scope.campaignPaymentDetails.payment_details;
            $scope.paid = 0;
            _.each(campaignPayments, function (p) {
                $scope.paid += p.amount;
            });
            //$scope.unpaid = $scope.campaignPaymentDetails.act_budget + parseInt($scope.campaignPaymentDetails.gst_price);
        });
    }

    $scope.payAmount = function (campaignId) {
        $scope.amountPay = _.filter($scope.ownerSaved, function (c) {
            return c.cid == campaignId;
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
    $scope.autopayOwnerCampaignPayment = function (id) {
        $scope.campaignPayment.campaign_id = $scope.amountPay[0].id;
        Upload.upload({
            url: config.apiPath + '/update-campaign-payment-owner',
            data: {
                image: $scope.files.image,
                campaign_payment: $scope.campaignPayment
            }
        }).then(function (result) {
            if (result.data.status == "1") {
                toastr.success(result.data.message);
                $scope.campaignPayment = {};
                $scope.files.image = "";
                // setTimout(() => {
                //     $location.path('/owner/' + $rootScope.clientSlug + '/payments');
                // }, 2500);
                document.getElementById("addpaydrop").classList.toggle("show");
                loadOwnerCampaigns();
            } else {
                if (result.data.message.constructor == Array) {
                    $scope.updateCampaignPaymentErrors = result.data.message;
                } else {
                    toastr.error(result.data.message);
                }
            }
        }, function (resp) {
            toastr.error("somthing went wrong try again later");
        }, function (evt) {
            var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
        });
    }
    $scope.updateOwnerCampaignPayment = function (id) {
        $scope.campaignPayment.campaign_id = id;
        Upload.upload({
            url: config.apiPath + '/update-campaign-payment-owner',
            data: {
                image: $scope.files.image,
                campaign_payment: $scope.campaignPayment
            }
        }).then(function (result) {
            if (result.data.status == "1") {
                toastr.success(result.data.message);
                $scope.campaignPayment = {};
                $scope.files.image = "";
                // setTimout(() => {
                //     $location.path('/owner/' + $rootScope.clientSlug + '/payments');
                // }, 2500);
                addPayment();
                $state.reload();
            } else {
                if (result.data.message.constructor == Array) {
                    $scope.updateCampaignPaymentErrors = result.data.message;
                } else {
                    toastr.error(result.data.message);
                }
            }
        }, function (resp) {
            toastr.error("somthing went wrong try again later");
        }, function (evt) {
            var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
        });
    }

    function addPayment() {
        document.getElementById("addpaymentdrop").classList.toggle("show");
    }

    /* ==============================
     | Campaign payment section ends
     =============================== */


    /*==============================
     | Campaign Search
     ==============================*/
    // $scope.simulateQuery = false;
    $scope.isDisabled = false;
    // $scope.querySearch   = querySearch;
    // $scope.selectedItemChange = selectedItemChange;
    // $scope.searchTextChange   = searchTextChange;


    $scope.campaignSearch = function (query) {
        return OwnerCampaignService.searchCampaigns(query.toLowerCase()).then(function (res) {
            return res;
        });
    }

    // function shareEmailCampaign() {
    //     document.getElementById("myDropdown").classList.toggle("hide");
    //   }
    //   function close() {
    //     angular.element(document.querySelector("#shareDropdown")).addClass("hide");
    //     angular.element(document.querySelector("#shareDropdown")).removeClass("show");
    // }
    $scope.viewSelectedCampaign = function (campaign) {
        $location.path('/owner/' + $rootScope.clientSlug + '/campaign-details/' + campaign.id + "/" + campaign.type);
    }
    $scope.shareCampaignToEmail = function (ev, shareCampaign, campaignID) {
        $scope.campaignToShare = $scope.campaignDetails;
        var campaignToEmail = {
            campaign_id: campaignID,
            email: shareCampaign.email,
            receiver_name: shareCampaign.receiver_name,
            campaign_type: $scope.campaignToShare.type
        };
        CampaignService.shareCampaignToEmail(campaignToEmail).then(function (result) {
            if (result.status == 1) {
                $mdSidenav('shareCampaignSidenav').close();
                $mdDialog.show(
                    $mdDialog.alert()
                    .parent(angular.element(document.querySelector('body')))
                    .clickOutsideToClose(true)
                    .title(result.message)
                    // .textContent('You can specify some description text in here.')
                    .ariaLabel('Alert Dialog Demo')
                    .ok('Confirmed!')
                    .targetEvent(ev)
                );
                //close();  
                shareOwnerCampaign();
                shareUpdatePopCampaign();
                shareOwnerPopCampaign();
            } else {
                toastr.error(result.message);
            }
            $scope.shareCampaign = '';
        });
    }
    //campaign share closed
    function selectedItemChange(item) {}
    /*==============================
     | Campaign Search
     ==============================*/
    function shareOwnerCampaign() {
        document.getElementById("shareownercampDrop").classList.toggle("show");
    }
    //   function shareOwnerCampaign() {
    //     document.getElementById("shareownercampDrop").classList.toggle("show");   
    //   }
    function shareOwnerPopCampaign() {
        document.getElementById("sharePopup").classList.toggle("show");
    }

    function shareUpdatePopCampaign() {
        document.getElementById("sharePopupdate").classList.toggle("show");
    }
    /*=========================
     | Page based initial loads
     =========================*/
    if ($rootScope.currStateName == "owner.campaigns") {
        $scope.getUserCampaignsForOwner('insertion_order');
        loadOwnerCampaigns();
        setMinMaxDatesForCamapign();
    }
    if ($rootScope.currStateName == "owner.bbi-campaigns") {
        $scope.getUserCampaignsForOwner('insertion_order');
    }
    if ($rootScope.currStateName == "owner.add-campagin-product") {
        loadOwnerProductList();
        $scope.selectedOwnerCampaign = JSON.parse(localStorage.selectedOwnerCampaign);
        if (!$scope.selectedOwnerCampaign.products) {
            $scope.selectedOwnerCampaign.products = []
        }
    }
    if (typeof $stateParams.campaignId !== 'undefined' && typeof $stateParams.campaignType !== 'undefined') {
        if ($stateParams.campaignType == 2) {
            $scope.getOwnerCampaignDetails($stateParams.campaignId);
        } else {
            $scope.getUserCampaignDetails($stateParams.campaignId);
        }
    }

    if ($rootScope.currStateName == 'owner.payments') {
        $scope.getUserCampaignsForOwner();
        loadOwnerCampaigns();
    }

    if ($rootScope.currStateName == 'owner.updatepayment') {
        $scope.getCampaignPaymentDetails($stateParams.id)
        getCampaignWithPayments();
        $scope.allCampaignsForOwner = [];
        loadOwnerCampaigns().then(function (result) {
            $scope.getUserCampaignsForOwner().then(function (result2) {
                $scope.allCampaignsForOwner = _.filter(result.concat(result2), function (c) {
                    return c.status >= 600;
                });
            });
        })
    }

    //call campaign count for hoarding list
    $scope.getCampaignList = function () {
        var productId = $stateParams.productId;
        OwnerCampaignService.getCampaignsFromProducts(productId).then(function (result) {
            if (result) {
                $scope.shortlistedproduct = result;
                //toastr.success(result.message);        
            } else {
                toastr.error(result.data.message);
            }
        });
    }
    if ($location.$$path.search("product-shortlist-campagin") !== -1) {
        $scope.getCampaignList();
    }


    $scope.changeCampaignProductPrice = function (campaign_id, owner_price, id, product_id) {
        product = {};
        product.campaign_id = campaign_id;
        product.owner_price = owner_price;
        product.product_id = product_id;
        product.product = id;
        OwnerProductService.changeCampaignProductPrice(product).then(function (result) {
            if (result.status == 1) {
                toastr.success(result.message);
                $state.reload();
            } else {
                toastr.error(result.data.message);
            }
        });

    }

    $scope.isdlPdfClicked = 'false';
    $scope.isdlQuotClicked = 'false';

    $scope.downloadOwnerQuote = function (campaignId) {
        $scope.isdlPdfClicked = 'false';
        $scope.isdlQuotClicked = 'true';
        OwnerCampaignService.downloadQuote(campaignId).then(function (result) {
            var campaignPdf = new Blob([result], {
                type: 'application/pdf;charset=utf-8'
            });
            FileSaver.saveAs(campaignPdf, 'campaigns.pdf');
            if (result.status) {
                toastr.error(result.meesage);
            }
        });
        var downloadToEmail = {
            campaign_id: campaignId,
            email: JSON.parse(localStorage.loggedInUser).email,
        };
        OwnerCampaignService.shareandDownloadCampaignToEmail(downloadToEmail).then(function (result) {
            if (result.status == 1) {
                $mdSidenav('shareCampaignSidenav').close();
                $mdDialog.show(
                    $mdDialog.alert()
                    .parent(angular.element(document.querySelector('body')))
                    .clickOutsideToClose(true)
                    .title(result.message)
                    //.textContent('You can specify some description text in here.')
                    .ariaLabel('Alert Dialog Demo')
                    .ok('Confirmed!')
                    .targetEvent(ev)
                );
                UsershareCampaign();
            } else {
                toastr.error(result.message);
            }
        });
    };

    // Download Owner PDF

    $scope.downloadOwnerPdf = function (campaignId) {
        $scope.isdlQuotClicked = 'false';
        $scope.isdlPdfClicked = 'true';
        OwnerCampaignService.downloadPdf(campaignId).then(function (result) {
            var campaignPdf = new Blob([result], {
                type: 'application/pdf;charset=utf-8'
            });
            FileSaver.saveAs(campaignPdf, 'campaigns.pdf');
            if (result.status) {
                toastr.error(result.meesage);
            }
        });
    };

    // End Download Owner PDF

    $scope.downloadOwnerReciepts = function (campaignId) {
        OwnerCampaignService.downloadOwnerReciepts(campaignId).then(function (result) {
            var campaignPdf = new Blob([result], {
                type: 'application/pdf;charset=utf-8'
            });
            FileSaver.saveAs(campaignPdf, 'campaigns.pdf');
            if (result.status) {
                toastr.error(result.meesage);
            }
        });
    }
    $scope.downloadOwnerPop = function (campaignId) {
        OwnerCampaignService.generatepop(campaignId).then(function (result) {
            var campaignPdf = new Blob([result], {
                type: 'application/pdf;charset=utf-8'
            });
            FileSaver.saveAs(campaignPdf, 'POP_Report.pdf');
            if (result.status) {
                toastr.error(result.meesage);
            }
        });
    };
    // if ($rootScope.currStateName == 'owner.update-payments') {

    // }

    /*=============================
     | Page based initial loads end
     =============================*/



    /* ----------------------------
                   New Hording profuct Nav bars starts
               -------------------------------*/
    // $scope.yearlyWeeks =[];
    // $scope.weeksArray = [];
    // var  weeklyPackageValue = 4;
    // var selectWeekValue = 0;
    // for(var i=1;i<=25;i++){
    //     $scope.yearlyWeeks.push({weeklyPackage : weeklyPackageValue})
    //     weeklyPackageValue +=2
    // }

    // for(var i=1;i<=26;i++){
    //     $scope.weeksArray.push({twoWeeks : 2})
    // }
    // var currentDay =  moment().format('LLLL').split(',')[0];

    // function productDatesCalculator(){
    //     var slotPrices =0;
    //     for(item in $scope.yearlyWeeks){
    //         if(item == 0){
    //             slotPrices = $scope.ownerProductPrice;
    //             $scope.yearlyWeeks[item].price = slotPrices;
    //         }else{
    //             slotPrices = parseInt(slotPrices) + (parseInt($scope.ownerProductPrice)/2)
    //             $scope.yearlyWeeks[item].price = slotPrices;
    //         }
    //     }
    //     var unavailBoundaries = [];
    //     $scope.unavailalbeDateRanges.forEach((dates) => {
    //         unavailBoundaries.push(moment(dates.booked_from))
    //         unavailBoundaries.push(moment(dates.booked_to))
    //     });
    //     if(currentDay == 'Monday'){
    //         var startDay = moment(new Date()).add(7,'days').format('LLLL');
    //         var endDay = moment(new Date()).add(7+13,'days').format('LLLL');
    //         $scope.weeksArray[0].startDay = startDay;
    //         $scope.weeksArray[0].endDay = endDay;
    //         unavailBoundaries.forEach((date) => {
    //             $scope.weeksArray[0].isBlocked = date.isSameOrAfter(startDay) && date.isSameOrBefore(endDay);
    //         });

    //     }else{
    //         var tempDay;
    //         for(i=1;i<=6;i++){
    //              tempDay = moment(new Date()).add(i,'days').format('LLLL').split(',')[0];
    //              tempDay = moment(new Date()).add(i,'days').format('LLLL').split(',')[0];
    //              if(tempDay == 'Monday'){
    //                 var startDay = moment(new Date()).add(i+7,'days').format('LLLL');
    //                 var endDay = moment(new Date()).add(i+7+13,'days').format('LLLL');
    //                 $scope.weeksArray[0].startDay = startDay;
    //                 $scope.weeksArray[0].endDay = endDay;
    //                 var isBlocked = false;
    //                 for (var date of unavailBoundaries) {
    //                     if (date.isSameOrAfter(startDay) && date.isSameOrBefore(endDay)) {
    //                         isBlocked = true;
    //                         break;
    //                     }
    //                 }
    //                 $scope.weeksArray[0].isBlocked = isBlocked;
    //              }

    //         }

    //     }

    //     // var tempororyStartDate = $scope.weeksArray[0].endDay;
    //     // $scope.weeksArray.forEach(function(item,index){
    //     //     if(index > 0){
    //     //         item.startDay = moment(tempororyStartDate).add(1,'days').format('LLLL');
    //     //         item.endDay = moment(tempororyStartDate).add(14,'days').format('LLLL');
    //     //         tempororyStartDate = item.endDay;
    //     //         var isBlocked = false;
    //     //         for (var date of unavailBoundaries) {
    //     //             if (date.isSameOrAfter(item.startDay) && date.isSameOrBefore(item.endDay)) {
    //     //                 isBlocked = true;
    //     //                 break;
    //     //             }
    //     //         }
    //     //         $scope.weeksArray[index].isBlocked = isBlocked;
    //             // unavailBoundaries.forEach((date) => {
    //             //     $scope.weeksArray[index].isBlocked = date.isSameOrAfter(moment(tempororyStartDate).add(1,'days').format('LLLL')) && date.isSameOrBefore(moment(tempororyStartDate).add(14,'days').format('LLLL'));
    //             // });
    //     //     }

    //     // })

    // }
    // productDatesCalculator();
    // $scope.selectHordingWeeks = function(weeks) {
    //     $scope.yearlyWeeks.filter((week) => week.selectedWeek).forEach((week) => {
    //         week.selectedWeek = false;
    //     });
    //     for(var i=0;i<$scope.yearlyWeeks.length; i++){
    //         if($scope.yearlyWeeks[i].weeklyPackage == weeks.weeklyPackage){
    //             $scope.yearlyWeeks[i].selectedWeek = true;
    //             selectWeekValue = $scope.yearlyWeeks[i];
    //             $scope.ownerTotalPrice = selectWeekValue.price;
    //         }
    //     }
    // $scope.weeksArray.filter((week) => week.selected).forEach((week) => {
    //     week.selected = false;
    // });
    // var countWeeks = 0;
    // for(var nextSelected in $scope.weeksArray){
    //     if($scope.weeksArray[nextSelected].isBlocked && $scope.weeksArray[nextSelected].isBlocked == true){
    //         $scope.weeksArray.filter((week) => week.selected).forEach((week) => {
    //              week.selected = false;
    //         });
    //         countWeeks =0;
    //     }else{ 
    //         $scope.weeksArray[nextSelected].selected = true;
    //         countWeeks +=1;
    //         var leftPos = $('#scrollFind').scrollLeft();
    //         $("#scrollFind").animate({scrollLeft: leftPos*0}, 0);
    //         if(countWeeks == weeks.weeklyPackage/2){
    //             $("#scrollFind").animate({scrollLeft: leftPos + ((nextSelected - (weeks.weeklyPackage/2)) * 115)}, 800);
    //             break;
    //         }
    //     }
    // }
    // if(weeks.weeklyPackage/2 > countWeeks ){
    //     $scope.packagePopUp = true;
    //     $scope.weeksArray.filter((week) => week.selected).forEach((week) => {
    //         week.selected = false;
    //     });
    //     alert("we don't have slots please select another slots")
    // $scope.selectPackageAbove = {head : "slots are not available",dsc : "we don't have slots please select another slots"}
    // $scope.totalSlotAmount = 0
    // return false;
    // } 
    // $scope.totalSlotAmount = selectWeekValue.price;

    // }
    // $scope.packagePopUp = false;
    // $scope.closeSelectedPopup = function(){
    //     $scope.packagePopUp = false;
    // }
    // $scope.selectUserWeeks = function(weeks,index,ev){
    //     if(!(Object.prototype.toString.call(selectWeekValue) == "[object Object]") || Object.keys(selectWeekValue).length == 0 ){
    //         alert("please select package above")
    //         // $scope.packagePopUp = true;
    //         // $scope.selectPackageAbove = {head : "select the package above",dsc : "please Select Campaign Duration"}
    //         return false;
    //     }else{
    //         $scope.weeksArray.filter((week) => week.selected).forEach((week) => {
    //             week.selected = false;
    //         });
    //         if((selectWeekValue.weeklyPackage/2) > ($scope.weeksArray.length - index)){

    //             alert("please Select Campaign Duration")
    //             // $scope.packagePopUp = true;
    //             // $scope.selectPackageAbove = {head : "select the package above",dsc : "please Select Campaign Duration"}    
    //             return false;
    //         };
    //         for(var i=index; i < (selectWeekValue.weeklyPackage/2 + index); i++) {   
    //             if($scope.weeksArray[i].isBlocked && $scope.weeksArray[i].isBlocked == true){
    //                 $scope.weeksArray.filter((week) => week.selected).forEach((week) => {
    //                     week.selected = false;
    //                 });
    //                 alert("select the another slots")
    //                 // $scope.packagePopUp = true;
    //                 // $scope.selectPackageAbove = {head : "select the another slots",dsc : "please Select another slots these are not available"}
    //                 return false;                                
    //             } else{
    //                 $scope.weeksArray[i].selected = true;
    //             }
    //         };
    //     };
    // };
    // $scope.toggleProductDetailSidenav = function(type){
    //     if(type == "Bulletin"){
    //         $("#exampleModalcalendar").modal("hide"); 
    //         selectWeekValue = 0;
    //         $scope.yearlyWeeks.filter((week) => week.selectedWeek).forEach((week) => {
    //             week.selectedWeek = false;
    //         });
    //         $scope.weeksArray.filter((week) => week.selected).forEach((week) => {
    //             week.selected = false;
    //         });
    //     }else{
    //         $scope.weeksDigitalArray.filter(function(week) {
    //             if(week.selected || week.isBlocked){
    //                 return true;
    //             }
    //         }).forEach((week) => {
    //             week.selected = false;
    //             week.isBlocked = false;
    //             // week.availableSlots = 0;
    //         });
    //         $scope.digitalNumOfSlots.value = 0;

    //         $("#digitalTransitCalender").modal("hide"); 
    //     }


    // }

    /* ----------------------------
        New Hording profuct Nav bars ends
    -------------------------------*/


    /* ----------------------------
              New Hording digital bullitin product Nav bars Start
          -------------------------------*/

    //   var digitalSlots = 0;
    //   $scope.digitalSlots = [];
    //   $scope.weeksDigitalArray = [];
    //   $scope.digitalSlotsClosed = false;

    //   for (var i = 1; i <= 26; i++) {
    //     $scope.weeksDigitalArray.push({ twoWeeks: 1 })
    //   }
    //   $scope.digitalNumOfSlots = {value : 0};
    //   $scope.blockSlotChange = function () {
    //     $scope.weeksDigitalArray.forEach((item) => { item.selected = false; item.isBlocked = false; $scope.totalDigitalSlotAmount = 0 })
    //     $scope.weeksDigitalArray.forEach(function (item) {
    //       $scope.unavailalbeDateRanges.forEach(function (unAvailable) {
    //         if ((moment(item.startDay).format('DD-MM-YYYY') == moment(unAvailable.booked_from).format('DD-MM-YYYY')) && (moment(item.endDay).format('DD-MM-YYYY') == moment(unAvailable.booked_to).format('DD-MM-YYYY'))) {
    //             item.availableSlots = ($scope.digitalSlots.length - unAvailable.booked_slots)
    //           if (item.availableSlots == 0) {
    //             item.isBlocked = true;
    //           }
    //         } else if ((moment(unAvailable.booked_from).isSameOrAfter(moment(item.startDay).format('YYYY-MM-DD')) && moment(unAvailable.booked_from).isSameOrBefore(moment(item.endDay).format('YYYY-MM-DD'))) || (moment(moment(unAvailable.booked_to).format('YYYY-MM-DD')).isSameOrAfter(moment(item.startDay).format('YYYY-MM-DD')) && moment(moment(unAvailable.booked_to).format('YYYY-MM-DD')).isSameOrBefore(moment(item.endDay).format('YYYY-MM-DD')))) {
    //           item.availableSlots = ($scope.digitalSlots.length - unAvailable.booked_slots)
    //           if (item.availableSlots == 0) {
    //             item.isBlocked = true;
    //           }
    //         }
    //       })
    //     })
    //   }
    //   function productDatesDigitalCalculator() {
    //     for (var i = 1; i <= digitalSlots; i++) {
    //       $scope.digitalSlots.push(i)
    //     }
    //     var slotPrices =0;
    //     for (item in $scope.weeksDigitalArray) {
    //       $scope.weeksDigitalArray[item].price = $scope.ownerProductPrice;
    // }
    // if (currentDay == 'Monday') {
    //   var startDay = moment(new Date()).add(7, 'days').format('LLLL');
    //   var endDay = moment(new Date()).add(7 + 6, 'days').format('LLLL');
    //   $scope.weeksDigitalArray[0].startDay = startDay;
    //   $scope.weeksDigitalArray[0].endDay = endDay;
    // } else {
    //   var tempDay;
    //   for (i = 1; i <= 6; i++) {
    //     tempDay = moment(new Date()).add(i, 'days').format('LLLL').split(',')[0];
    //     if (tempDay == 'Monday') {
    //       var startDay = moment(new Date()).add(i, 'days').format('LLLL');
    //       var endDay = moment(new Date()).add(i + 6, 'days').format('LLLL');
    //       $scope.weeksDigitalArray[0].startDay = startDay;
    //       $scope.weeksDigitalArray[0].endDay = endDay;
    //     }

    //       }

    //     }
    //     var tempororyStartDate = $scope.weeksDigitalArray[0].endDay;
    //     $scope.weeksDigitalArray.forEach(function (item, index) {
    //       if (index > 0) {
    //         item.startDay = moment(tempororyStartDate).add(1, 'days').format('LLLL');
    //         item.endDay = moment(tempororyStartDate).add(7, 'days').format('LLLL');
    //         tempororyStartDate = item.endDay;
    //       }

    //     })
    //   }


    //   $scope.totalDigitalSlotAmount = 0;
    //   $scope.selectUserDigitalWeeks = function (weeks, index, ev) {
    //     if ($scope.digitalNumOfSlots.value == 0) {
    //       alert("please select no. of slots")
    //       return false;
    //     }
    //     if ($scope.digitalNumOfSlots.value > weeks.availableSlots) {
    //       alert("As you are exceeding the slots. you can't book it");
    //       return false;
    //     }
    //     if ($scope.weeksDigitalArray[index].selected == true) {
    //       $scope.weeksDigitalArray[index].selected = false;
    //       $scope.totalDigitalSlotAmount -= parseInt(parseInt($scope.digitalNumOfSlots.value) * parseInt($scope.weeksDigitalArray[index].price));

    //     } else {
    //       $scope.totalDigitalSlotAmount += parseInt(parseInt($scope.digitalNumOfSlots.value) * parseInt($scope.weeksDigitalArray[index].price));
    //       $scope.weeksDigitalArray[index].selected = true;

    //     }
    //   };
    //   $scope.digitalSelectUserWeeks = function (weeks, index, ev) {

    //     if ($scope.weeksDigitalArray[index].selected && $scope.weeksDigitalArray[index].selected == true) {
    //       $scope.weeksDigitalArray[index].selected = false;

    //     } else {
    //       $scope.weeksDigitalArray[index].selected = true;
    //     }
    //   }
    //   $scope.digitalSlotedDatesPopupClosed = function () {
    //     $scope.digitalSlotsClosed = false;
    //   }
    //   $scope.digitalBlockedSlotesbtn = function (weeksArray) {
    //     $scope.product.dates = [];
    //     weeksArray.filter((week) => week.selected).forEach(function (item) {
    //       var startDate = moment(item.startDay).format('YYYY-MM-DD')
    //       var endDate = moment(item.endDay).format('YYYY-MM-DD')

    //       $scope.product.dates.push({ startDate: startDate, endDate: endDate })
    //       $scope.digitalSlotedDatesPopupClosed();
    //     })

    //   }

    /* ----------------------------
  New Hording Digital bullitin product Nav bars Ends
-------------------------------*/




    //page width
    $scope.innerWidth = $window.innerWidth;
    // loadMetroCampaigns();
    // getMetroCampaignDetails();
    getActiveUserCampaigns();
    //getMetroCampDetails($stateParams.metroCampaignId);
        
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
			user_type: "owner",
		};
		CampaignService.getCreateMessages(payload).then((result) => {
			console.log("message sent", result);
			if (result.status) {
			    $scope.message = "";
                CampaignService.getChatMessages($stateParams.campaignId).then((result) => {
                    $scope.messageData = result.messages_data;
                    $scope.el = document.getElementById("dialogContent_0");
                    console.log($scope.el);
                    if ($scope.el) {
                        $scope.el.scrollTop = $scope.el.scrollHeight;
                    }
                });
			}
		});
	};

    $scope.getChatMessages = function () {
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
	};

    $scope.openChat = function () {
        const uniqueProd = new Set($scope.campaignDetails.products.map((prod) => prod.siteNo));
        $scope.uniqueProducts = Array.from(uniqueProd);
        $scope.currentUser = 'owner';
        $scope.selectedProductId = '';
        $scope.selectedUser = '';
        $scope.message = '';
        $scope.messageData = [];
        $mdDialog.show({
            templateUrl: "views/chat-popup.html",
            clickOutsideToClose: false,
            preserveScope: true,
            fullscreen: $scope.customFullscreen,
            scope: $scope,
            controller: function ($scope) {
            $scope.closeDialog = function () {
                if ($scope.timeout) {
                    clearTimeout($scope.timeout);
                    $scope.timeout = null;
                }
                $mdDialog.hide();
            };
            }
        }); 
        $scope.getChatMessages();
    }
    /*=============================
      | End of Chat Popup  
    =============================*/

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
  
            function getDate (dt,timeZone) {
            //   const dtValue = dt.$date.$numberLong;
            //   return moment(new Date(+dtValue)).format("YYYY-MM-DD");
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
      $scope.lat = "";
   	  $scope.lng = "";
	  // Generate the map image URL
	  function generateMapImageUrl(lat, lng) {
	  var apiKey = 'AIzaSyAl05ze0VsbHB2lnp2VRXbNQHNyRzVWUQQ'; // Replace with your Google Maps API key
	  var zoomLevel = 12;
	  var imageSize = '380x280';
	  var color = 'color:red%7Clabel:C%7C' + lat + ',' + lng;
	 
	  var mapImageUrl = "https://maps.googleapis.com/maps/api/staticmap?";
	  mapImageUrl += 'center=' + lat + ',' + lng;
	  mapImageUrl += '&markers=' + color;	
	  mapImageUrl += '&zoom=' + zoomLevel;
	  mapImageUrl += '&size=' + imageSize;
      mapImageUrl += '&key=' + apiKey;	  
	  return mapImageUrl;
	}

    $scope.userProfileApi = function(){
        CampaignService.getProfile().then(function(result){
            $scope.userProfile = result; 
            $scope.loading= false;
        })
      }
    
      $scope.cdate = new Date()
	  
    //RFP Search Criteria
	
			var rfpSearchCriteria = function () {
			$scope.bulkSelect = true;
			$scope.selectedids = [];
			$scope.selectedDupIds = [];
			$rootScope.selectAllChk = false;
			$scope.loading = true;
			OwnerCampaignService.rfpSearchCriteria().then(function (result) {
				$rootScope.selectAllChk = false;
				$scope.RfpData = result;
				// $scope.RfpData = result.filter(function(product) {
				//   return product.status == "1300";
				// });
				$scope.RfpData = $scope.RfpData.map((e) => {
					return {
						...e,
						valueChecked: false,
					};
				});
				$scope.loading = false;
			});
		};
		rfpSearchCriteria();
		$scope.selectAll = function (event) {
			var selectedids = [];
			$scope.bulkSelect = false;
			if (event.target.checked) {
				$scope.RfpData = $scope.RfpData.map((e) => {
					if (e.status_read != 1) {
						$scope.selectedids.push(e.id);
						return {
							...e,
							valueChecked: true,
						};
					} else {
						return {
							...e,
						};
					}
				});
			} else {
				$scope.bulkSelect = true;
				$scope.RfpData = $scope.RfpData.map((e) => {
					if (e.status_read != 1) {
						$scope.selectedids.splice(0);
						return {
							...e,
							valueChecked: false,
						};
					} else {
						return {
							...e,
						};
					}
				});
			}
		};
		$scope.selectSingle = function (id, status_read) {
			var Existingid = $scope.selectedids.indexOf(id);
			var ExistingDupId = $scope.selectedDupIds.indexOf(id);
			$scope.bulkSelect = false;
			if (ExistingDupId > -1) {
				$scope.selectedDupIds.splice(ExistingDupId, 1);
				if ($scope.selectedDupIds.length == 0) {
					$scope.bulkSelect = true;
				}
			} else {
				if (status_read == 1) {
					$scope.selectedDupIds.push(id);
				}
			}
			if (Existingid > -1) {
				$scope.selectedids.splice(Existingid, 1);
				if ($scope.selectedids.length == 0) {
					$scope.bulkSelect = true;
				}
			} else {
				if (status_read != 1) {
					$scope.selectedids.push(id);
				}
			}
		};
		$scope.bulkUplodData = function () {
			$scope.updateRfpstatus($scope.selectedids);
			// $scope.valueChecked = false
		};
		$scope.updateRfpstatus = function (selectedCamp) {
			var obj = {
				rfp_ids: selectedCamp,
			};
			OwnerCampaignService.updateRfpstatus(obj).then(function (result) {
				// if(result.status_read == 1) {
				//   $scope.rfpSearchCriteria();  
				// }
				rfpSearchCriteria();
			});
		};
		
		$scope.showRfpCampaignDetails = function ($event, campaign) {
			let selectedId = [];
			selectedId.push(campaign.id);
			$scope.updateRfpstatus(selectedId);
			try {
				//$location.path('/admin/' + $rootScope.clientSlug + '/campaign-proposal-summary/' + campaign.id + "/" + campaign.type);
				//$location.path('/owner/' + $rootScope.clientSlug + '/campaigns');
				var path =
					'/owner/' + $rootScope.clientSlug + '/campaign-details/' +
					(campaign.campaign_id
						? campaign.campaign_id
						: campaign.id) +
					"/40";
				$location.path(path);
			} catch (ex) {
				alert("exception: " + ex.message);
			}
			//$location.path('/admin/campaign-proposal-summary/' + (campaign.campaign_id?campaign.campaign_id:campaign.id)) + '/3';
		};
	
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
  
  //RFP Search Criteria PDF
			($scope.downloadRFPsearch = function (campaignId) {
				OwnerCampaignService.downloadRFPsearchCriteria(campaignId).then(function (
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
			});
	  
});