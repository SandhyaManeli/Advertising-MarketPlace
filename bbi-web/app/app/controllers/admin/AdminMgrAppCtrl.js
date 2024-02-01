angular.module("bbManager").controller('AdminMgrAppCtrl', function ($scope, $mdDialog, $mdSidenav, $rootScope, $interval, $timeout, $location, $auth,NotificationService, AdminNotificationService, toastr,$state, config,$window, AdminUserService) {

  /*=================================
  | mdDilalog close function
  =================================*/
  $scope.isAuthenticated = $auth.isAuthenticated();
  $rootScope.selectAllChk = false;
  if(typeof $rootScope.closeMdDialog !== 'function'){
    $rootScope.closeMdDialog = function(){
       $mdDialog.hide();
     }
   }
   $scope.getAdminNotifictaions = function() {
      $scope.bulkSelect = true
      $scope.selectedids = [];
      $scope.selectedDupIds = [];
      $rootScope.selectAllChk = false;
      if ($scope.getAdminNotifictaionss && $scope.getAdminNotifictaionss.length) {
        $scope.getAdminNotifictaionss = $scope.getAdminNotifictaionss.map( e=> {
          return {
            ...e,
            valueChecked: false
          }
        });
      }

      AdminNotificationService.viewAdminNotification().then((result) => {
        $scope.getAdminNotifictaionss = result.notifications;
        $scope.getAdminNotifictaionss = $scope.getAdminNotifictaionss.map( e=> {
          return {
            ...e,
            valueChecked: false
          }
        });
        $scope.unReadNotify = result.notifications.filter(function(item){
          if(item.status == 0){
              return true;
          }
        });
    });
  }
  $scope.getAdminNotifictaions();

  $scope.updateNotifyStatus = function(notificationType,campaignId,notifyId,productIds){
  localStorage.setItem('productId',JSON.stringify(productIds))
    NotificationService.updateNotification(notifyId).then(function(result){
      if(result){
        AdminNotificationService.viewAdminNotification().then((result) => {
          if(result){
            $scope.getAdminNotifictaionss = result.notifications;
          $scope.unReadNotify = result.notifications.filter(function(item){
            if(item.status == 0){
                return true;
            }
          })
            if(notificationType == 'metro-campaign'){
              $location.path("admin/metro-campaign/" +campaignId)
            }
            else if(notificationType == 'campaign'){
              //$location.path("admin/campaign-proposal-summary/" + campaignId)
              $location.path("admin/campaign-proposal-summary/" + campaignId + '/10');
            }
			else if(notificationType == 'campaign_product_offer'){
              //$location.path("admin/campaign-proposal-summary/" + campaignId)
              $location.path("admin/campaign-proposal-summary/" + campaignId + '/30');
            }
            else if(notificationType == 'delete_campaign'){
              //$location.path("admin/campaign-proposal-summary/" + campaignId)
              $location.path("admin/campaign-proposal-summary/" + campaignId + '/15');
            }
            else if(notificationType == 'delete_campaign'){
              //$location.path("admin/campaign-proposal-summary/" + campaignId)
              $location.path("admin/campaign-proposal-summary/" + campaignId + '/16');
            }
            else if(notificationType == 'product'){
              $location.path("admin/hoarding-list" )
            }
            else if(notificationType == 'product-request'){
              $location.path("admin/hoarding-list" )
            }
			else if(notificationType == 'user-query'){
              $location.path("admin/admin-feeds" )
            }
			else if(notificationType == 'SubSeller_registartion'){
              $location.path("admin/user-management" )
            }
            else if(notificationType == 'Owner_registartion' || notificationType == 'User_registartion'){
              $location.path("admin/user-management" )
            }
          }
        });
      }
        
        
    }) 
 }


//  $scope.localStorageProductId = function(productIds){
//   console.log(productIds);
// }

// Hb Menu redirections code start
$scope.ReddirectNotification = function(){
  $window.location.href = '/admin/admin-notifications';
 }
 $scope.ReddirectAdminProfile = function(){
  $window.location.href = '/admin/profile';
 }
$scope.ReddirectAdminChangePassword = function(){
  $window.location.href = '/admin/reset_password';
 }
//  Hb Menu redirections code end

 $scope.selectAll = function (event) {
    var selectedids = [];
    $scope.bulkSelect = false;
    if(event.target.checked){
      $scope.getAdminNotifictaionss = $scope.getAdminNotifictaionss.map( e=> {
        if(e.status != 1){
          $scope.selectedids.push(e.id)
          return {
            ...e,
            valueChecked: true
          }
        }
        else {
          return {
            ...e
          }
        }
      });
    }
    else {
      $scope.bulkSelect = true
      $scope.getAdminNotifictaionss = $scope.getAdminNotifictaionss.map( e=> {
        if(e.status != 1){
          $scope.selectedids.splice(0)
          return {
            ...e,
            valueChecked: false
          }
        } else {
          return {
            ...e
          }
        }
      });
    }
 }

 $scope.selectSingle= function (id,status) {
  
  var Existingid = $scope.selectedids.indexOf(id)
  var ExistingDupId = $scope.selectedDupIds.indexOf(id)
  $scope.bulkSelect = false
  if(ExistingDupId >-1){
    $scope.selectedDupIds.splice(ExistingDupId, 1)
    if($scope.selectedDupIds.length == 0) {
      $scope.bulkSelect = true
    }
  }
  else {
    if(status == 1){
    $scope.selectedDupIds.push(id)
    }
    
  }
  if(Existingid >-1){
    $scope.selectedids.splice(Existingid, 1)
    if($scope.selectedids.length == 0) {
      $scope.bulkSelect = true
    }
  } else {
    if(status != 1){
    $scope.selectedids.push(id)
    }
    
  }
  
 

//  $scope.AdminNotificationService(selectedids)
}
$scope.bulkUplodData= function(){
  $scope.AdminNotificationService($scope.selectedids)
  // $scope.valueChecked = false
}
$scope.AdminNotificationService = function (campaignOfferParams){
  AdminNotificationService.updatenotificationsstatus(campaignOfferParams).then(function (result) {
    if(result.status == 1) {
      $scope.getAdminNotifictaions();
    }
  });  
}
   /* Notification start */
   if($auth.isAuthenticated()){
          var user = localStorage.getItem("loggedInUser");
          var parsedData = JSON.parse(user);
          var user_type = parsedData.user_type;
          if ($auth.getPayload().userMongo.user_type == 'bbi') {
              var user_id = '-superAdmin';
          } else if ($auth.getPayload().userMongo.user_type == 'basic') {
              var user_id = parsedData.user_id;
          } else if ($auth.getPayload().userMongo.user_type == 'owner') {
              var user_id = '-' + parsedData.mong_id;
          }

        var pusher = new Pusher('c8b414b2b7d7c918a011', {
            cluster: 'ap2',
            forceTLS: true
        });
        var channel = pusher.subscribe('CampaignLaunch' + user_id);
        var channel1 = pusher.subscribe('campaignClosed' + user_id);
        var channel2 = pusher.subscribe('CampaignLaunchRequested' + user_id);
        var channel3 = pusher.subscribe('CampaignQuoteProvided' + user_id);
        var channel4 = pusher.subscribe('CampaignQuoteRequested' + user_id);
        var channel5 = pusher.subscribe('CampaignQuoteRevision' + user_id);
        var channel6 = pusher.subscribe('CampaignSuggestionRequest' + user_id);
        var channel7 = pusher.subscribe('CampaignSuspended' + user_id);
        var channel8 = pusher.subscribe('ProductApproved' + user_id);
        var channel9 = pusher.subscribe('ProductRequested' + user_id);
        var channel10 = pusher.subscribe('metroCampaignClosed' + user_id);
        var channel11 = pusher.subscribe('metroCampaignLaunched' + user_id);
        var channel12 = pusher.subscribe('metroCampignLocked' + user_id);

        channel.bind('CampaignLaunchEvent', function (data) {
          $scope.$apply(function(){
            $scope.unReadNotify.unshift(data);
            if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
              $scope.getAdminNotifictaions();
          }
        })

        });
        channel1.bind('campaignClosedEvent', function (data) {
          $scope.$apply(function(){
            $scope.unReadNotify.unshift(data);
            if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
              $scope.getAdminNotifictaions();
          }
        })       
       });
        channel2.bind('CampaignLaunchRequestedEvent', function (data) {
          $scope.$apply(function(){
            $scope.unReadNotify.unshift(data);
            if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
              $scope.getAdminNotifictaions();
          }
        })
             });
        channel3.bind('CampaignQuoteProvidedEvent', function (data) {
          $scope.$apply(function(){
            $scope.unReadNotify.unshift(data);
            if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
              $scope.getAdminNotifictaions();
          }
        })
        });
        channel4.bind('CampaignQuoteRequestedEvent', function (data) {
          $scope.$apply(function(){
            $scope.unReadNotify.unshift(data);
            if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
              $scope.getAdminNotifictaions();
          }
        })
        });
        channel5.bind('CampaignQuoteRevisionEvent', function (data) {
          $scope.$apply(function(){
            $scope.unReadNotify.unshift(data);
            if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
              $scope.getAdminNotifictaions();
          }
        })
        });
        channel6.bind('CampaignSuggestionRequestEvent', function (data) {
          $scope.$apply(function(){
            $scope.unReadNotify.unshift(data);
            if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
              $scope.getAdminNotifictaions();
          }
        })
        });
        channel7.bind('CampaignSuspendedEvent', function (data) {
          $scope.$apply(function(){
            $scope.unReadNotify.unshift(data);
            if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
              $scope.getAdminNotifictaions();
          }
        })
        });
        channel8.bind('ProductApprovedEvent', function (data) {
          $scope.$apply(function(){
            $scope.unReadNotify.unshift(data);
            if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
              $scope.getAdminNotifictaions();
          }
        })
        });
        channel9.bind('ProductRequestedEvent', function (data) {
          $scope.$apply(function(){
            $scope.unReadNotify.unshift(data);
            if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
              $scope.getAdminNotifictaions();
          }
        })
        });
        channel10.bind('metroCampaignClosedEvent', function (data) {
          $scope.$apply(function(){
            $scope.unReadNotify.unshift(data);
            if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
              $scope.getAdminNotifictaions();
          }
        })
        });
        channel11.bind('metroCampaignLaunchedEvent', function (data) {
          $scope.$apply(function(){
            $scope.unReadNotify.unshift(data);
            if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
              $scope.getAdminNotifictaions();
          }
        })
        });
        channel12.bind('metroCampignLockedEvent', function (data) {
          $scope.$apply(function(){
            $scope.unReadNotify.unshift(data);
            if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
              $scope.getAdminNotifictaions();
          }
        })
        });
      }
      /* Notification Ends */
  /*=================================
  | mdDilalog close function ends
  =================================*/

  /*========================
  | Notification types
  |==================
  
  'campaign-suggestion-requested'   =>    0,
  'campaign-quote-requested'        =>    1,
  'campaign-quote-provided'         =>    2,
  'campaign-launch-requested'       =>    3,
  'campaign-launched'               =>    4,
  'campaign-suspended'              =>    5,
  'campaign-closed'                 =>    6 
  
  =========================*/

  $rootScope.serverUrl = config.serverUrl;

  if(localStorage.loggedInUser){
    $rootScope.loggedInUser = JSON.parse(localStorage.loggedInUser);
  }

  // $scope.closeSidenav = function () {
  //   $mdSidenav('left').toggle();
  // };
  // $scope.closeSideNavPanel = function () {
  //   $mdSidenav('right').toggle();
  // };
  // toggle menu 
  $scope.toggleAdminLeftSidenav = function () {
    $mdSidenav('adminLeftSidenav').toggle();
  };

  $scope.closeMenuSidenavIfMobile = function(){
    if($window.innerWidth <=420){
      $mdSidenav('adminLeftSidenav').close();
    }
  }

  $scope.logout = function(){
    $auth.logout().then(function(result){
      // console.log(result);
      $rootScope.isAuthenticated = false;
      $location.path('/');
      localStorage.clear();
      toastr.warning('You have successfully signed out!');        
    });
  }

  $scope.showFindForMeDialog = function (ev) {
    $scope.queryTxt = '';
    $mdDialog.show({
      templateUrl: 'views/find-for-me.html',
      fullscreen: $scope.customFullscreen,
      clickOutsideToClose: true, 
      preserveScope: true, 
      scope: $scope,
      controller: 'FindForMeCtrl',
      resolve: { 
        js: ['$ocLazyLoad', function($ocLazyLoad) {
          return $ocLazyLoad.load('./controllers/FindForMeCtrl.js');
        }],
        userService: ['$ocLazyLoad', function($ocLazyLoad) {
          return $ocLazyLoad.load('./services/UserService.js');
        }],
      },
    });
  };

  $scope.showFormats = false;
  $scope.toogelMenu = function () {
    $scope.showFormats = !$scope.showFormats;
  }
   $scope.showLocation = false;
  $scope.toogelLocation = function () {
    $scope.showLocation = !$scope.showLocation;
  }
  $scope.showArea = false;
  $scope.toogelLocation = function () {
    $scope.showArea = !$scope.showArea;
  }
  $scope.showCampagin = false;
  $scope.toogelCampagin = function () {
    $scope.showCampagin = !$scope.showCampagin;
  }
  $scope.showPayments = false;
  $scope.toogelPayments = function () {
    $scope.showPayments = !$scope.showPayments;
  }
  if($rootScope.currStateName == 'admin.admin-notifications'){
    $scope.getAdminNotifictaions();
}




// SORTING FOR admin notifications

$scope.sortAsccrfqacca =function(headingName, type){
  $scope.upArrowColour = headingName;
  $scope.sortType ="Asccrfqacca";
  if (type=="string"){
    $scope.newOfferData = $scope.getAdminNotifictaionss.map(e=>{
    return {
    ...e,
    name: e.name,
    user_email: e.user_email
    }
    })
    $scope.getAdminNotifictaionss = [];
    $scope.getAdminNotifictaionss = $scope.newOfferData.sort((a,b) =>{
      //console.log(a[headingName])
      if(b[headingName] != undefined){
      return  a[headingName].localeCompare(b[headingName], undefined, {
        numeric: true,
        sensitivity: 'base'
      });
    }
    })
    
    // $scope.RfpData = $scope.newOfferData;
    }
  $scope.getAdminNotifictaionss = $scope.getAdminNotifictaionss.sort((a,b)=>{

       if(type == 'boolean'){
         return a[headingName] ? 1 : -1 
     }
      else {
         return a[headingName] - b[headingName]
      }
  })
  console.log($scope.getAdminNotifictaionss)
}
$scope.sortDsccrfqacca =function(headingName, type){
  $scope.downArrowColour = headingName;
  $scope.sortType ="Dsccrfqacca";
  if (type=="string"){
    $scope.newOfferData = $scope.getAdminNotifictaionss.map(e=>{
    return {
    ...e,
    name: e.name,
    user_email: e.user_email
    }
    })
    $scope.getAdminNotifictaionss = [];
    $scope.getAdminNotifictaionss = $scope.newOfferData.sort((a,b) =>{
      //console.log(a[headingName])
      if(b[headingName] != undefined){
      return  b[headingName].localeCompare(a[headingName], undefined, {
        numeric: true,
        sensitivity: 'base'
      });
    }
    })
    
    // $scope.RfpData = $scope.newOfferData;
    }
 $scope.getAdminNotifictaionss = $scope.getAdminNotifictaionss.sort((a,b)=>{

      if(type == 'boolean'){
         return a[headingName] ? -1 : 1 
     }
     else {
         return  b[headingName] - a[headingName] 
     }
 })
 //console.log($scope.getAdminNotifictaionss)
};


//SORTING FOR admin notifications
  /*================================
  === Long polling notifications ===
  ================================*/
  // $scope.adminNotifs = [];
  // var getAdminNotifs = function(){
  //   var last_notif = 0;
  //   if($scope.adminNotifs && $scope.adminNotifs.length > 0){
  //     last_notif = moment.utc($scope.adminNotifs[0].updated_at).valueOf();
  //   }
  //   AdminNotificationService.getAllAdminNotifications(last_notif).then(function(result){
  //     $scope.adminNotifs = result.concat($scope.adminNotifs);
  //     $timeout(getAdminNotifs, 1000);
  //   });
  // }
  // getAdminNotifs();
  // $interval(getAdminNotifs, 10000);
  // getAdminNotifs();

  /*===============================
  |   Notification navigation 
  ===============================*/
  // $scope.viewNotification = function(notification){
  //   if(notification.type == 9){
  //     // hoarding requested
  //     $location.path('admin/requested-hoardings/' + notification.data.product_id);
  //   }
  //   else if(notification.type == 0){
  //     // campaign suggestion requested
  //     $location.path('admin/home/' + notification.data.campaign_sugg_req_id);
  //   }
  //   else if(notification.type > 0 && notification.type < 8){
  //     // campaign state changed
  //     $location.path('admin/campaign-proposal-summary/' + notification.data.campaign_id);
  //   }
  //   else if(notification.type == 8){
  //     // a new compnay joined. set up the super admin
  //     $location.path('admin/user-management/' + notification.data.client_m_id);
  //   }
  //   AdminNotificationService.updateNotifRead(notification.id).then(function(result){
  //     if(result.status == 1){
  //       // remove notif from list
  //       $scope.adminNotifs = _.filter($scope.adminNotifs, function(notif){ return notif.id != notification.id; })
  //     }
  //     else{
  //       toastr.error(result.message);
  //     }
  //   });
  //   $mdSidenav('right').toggle();
  // }

  /*===============================
  |   Notification navigation ends
  ===============================*/

  /*===============================
  | add new metro campagin         |
  ********************************/
  $scope.AddMetroCampaign = function () {
    $mdSidenav('metroAddCmapginSidenav').toggle();
  };
  $scope.AddMetroProduct = function () {
    $mdSidenav('metro-product').toggle();
  };
  $scope.getAvatar = function(){
    var payload = $auth.getPayload();
    var userMongo =  typeof payload !== 'undefined' ? payload.userMongo : undefined;
    if(typeof userMongo !== 'undefined' && typeof userMongo.profile_pic !== 'undefined' && userMongo.profile_pic != ''){
      return {
        present: true,
        profile_pic: userMongo.profile_pic
      }
    }
    else{
      return {
        present: false
      }
    }
  }
  $scope.query = {};
  $scope.sendQuery = function(query){
          ContactService.sendQuery(query).then(function(result){
              if(result.status == 1){
                  toastr.success(result.message)
              }else{
                  toastr.error = result.message;
              }
          });
          $scope.query = {};
          $state.reload();
  }

  // hb menu heilight code start
  $scope.isActive = function(route){
    //console.log("Hello",$location.path(),route === $location.path(),route)
    return route === $location.path();
  }
  // hb menu heilight code end

  // Verify User Login
  function verifyUserLogin() {
    try {
      var loggedInUser = localStorage.getItem('loggedInUser');
      var client_id;
      var user_type;
      if (loggedInUser) {
        loggedInUser = JSON.parse(loggedInUser);
        client_id = loggedInUser.clientId;
        user_type = loggedInUser.user_type;

        AdminUserService.verifyLogin(client_id, user_type).then(result => {
          if (result.status == 1 && result.message == 'User record found.') {
            //do nothing
            console.log('Admin verified successfully!');
          } else {
            $auth.logout().then(function (result) {
              $rootScope.isAuthenticated = false;
              $location.path('/');
              localStorage.clear();
              toastr.warning('You have successfully signed out!');
            });
          }
        });
      }
    } catch(ex) {
      console.log(ex.message);
    }
  }
  //verifyUserLogin();
});