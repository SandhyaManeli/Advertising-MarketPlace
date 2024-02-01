angular.module("bbManager").controller('OwnerMngrCtrl', function ($scope, $mdSidenav, $log, $mdDialog, $stateParams, $rootScope, $location, $timeout, $auth, $window, config, OwnerNotificationService,CampaignService, OwnerProductService,$state, toastr,NotificationService, AdminUserService) {
  $scope.isAuthenticated = $auth.isAuthenticated();
  $scope.getOwnerNotifictaions = function() {
    $scope.bulkSelect = true
      $scope.selectedids = [];
      $scope.selectedDupIds = [];
      $rootScope.selectAllChk = false;
      if ($scope.getOwnerNotifictaionss && $scope.getOwnerNotifictaionss.length) {
        $scope.getOwnerNotifictaionss = $scope.getOwnerNotifictaionss.map( e=> {
          return {
            ...e,
            valueChecked: false
          }
        });
      }
    OwnerNotificationService.viewOwnerNotification().then((result) => {
      $scope.getOwnerNotifictaionss = result.notifications;
      $scope.getOwnerNotifictaionss = $scope.getOwnerNotifictaionss.map( e=> {
        return {
          ...e,
          valueChecked: false
        }
      });
      $scope.loading=true;
      $scope.unReadNotify = result.notifications.filter(function(item){
        if(item.status == 0){
            return true;
        }
        $scope.loading=false;
      })
    });
  }
  $scope.getOwnerNotifictaions();

  $scope.updateNotifyStatus = function(notificationType,campaignId,notifyId){
    
    NotificationService.updateNotification(notifyId).then(function(result){
      if(result){
        OwnerNotificationService.viewOwnerNotification().then((result) => {
          if(result){
          $scope.getOwnerNotifictaionss = result.notifications;
          $scope.loading=true;
          $scope.unReadNotify = result.notifications.filter(function(item){
            if(item.status == 0){
                return true;
            }
            $scope.loading=false;
          })
        }
        });
      }
    if(notificationType == 'campaign'){
      $location.path("owner/{{clientSlug}}/campaign-details/" +campaignId + "/0" )
    } else if(notificationType == 'offer_request'){
      $location.path("owner/{{clientSlug}}/campaign-details/" +campaignId + "/0" )
    }
	else if(notificationType == 'campaign_product_offer'){
      $location.path("owner/{{clientSlug}}/campaign-details/" +campaignId + "/0" )
    }
    else if(notificationType == 'product'){
      $location.path("owner/{{clientSlug}}/hoarding-list")
    }
    else if(notificationType == 'product-request'){
      $location.path("owner/{{clientSlug}}/requested-hoardings" )
    }
        
    }) 
 }
 $scope.selectAll = function (event) {
  var selectedids = [];
  $scope.bulkSelect = false;
  if(event.target.checked){
    $scope.getOwnerNotifictaionss = $scope.getOwnerNotifictaionss.map( e=> {
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
    $scope.getOwnerNotifictaionss = $scope.getOwnerNotifictaionss.map( e=> {
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
  
}
$scope.bulkUplodData= function(){
  $scope.OwnerNotificationService($scope.selectedids)
  // $scope.valueChecked = false
}
$scope.OwnerNotificationService = function (campaignOfferParams){
  OwnerNotificationService.updatenotificationsstatus(campaignOfferParams).then(function (result) {
    if(result.status == 1) {
      $scope.getOwnerNotifictaions();
    }
  });  
}
 if($rootScope.currStateName == 'owner.owner-notifications'){
  $scope.getOwnerNotifictaions();
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
        $scope.getOwnerNotifictaions();
    }
    })
  });
  channel1.bind('campaignClosedEvent', function (data) {
    $scope.$apply(function(){
      $scope.unReadNotify.unshift(data);
      if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
        $scope.getOwnerNotifictaions();
    }
    })
  });
  channel2.bind('CampaignLaunchRequestedEvent', function (data) {
    $scope.$apply(function(){
      $scope.unReadNotify.unshift(data);
      if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
        $scope.getOwnerNotifictaions();
    }
    })
  });
  channel3.bind('CampaignQuoteProvidedEvent', function (data) {
    $scope.$apply(function(){
      $scope.unReadNotify.unshift(data);
      if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
        $scope.getOwnerNotifictaions();
    }
    })
  });
  channel4.bind('CampaignQuoteRequestedEvent', function (data) {
    $scope.$apply(function(){
      $scope.unReadNotify.unshift(data);
      if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
        $scope.getOwnerNotifictaions();
    }
    })
  });
  channel5.bind('CampaignQuoteRevisionEvent', function (data) {
    $scope.$apply(function(){
      $scope.unReadNotify.unshift(data);
      if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
        $scope.getOwnerNotifictaions();
    }
    })
  });
  channel6.bind('CampaignSuggestionRequestEvent', function (data) {
    $scope.$apply(function(){
      $scope.unReadNotify.unshift(data);
      if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
        $scope.getOwnerNotifictaions();
    }
    })
  });
  channel7.bind('CampaignSuspendedEvent', function (data) {
    $scope.$apply(function(){
      $scope.unReadNotify.unshift(data);
      if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
        $scope.getOwnerNotifictaions();
    }
    })
  });
  channel8.bind('ProductApprovedEvent', function (data) {
    $scope.$apply(function(){
      $scope.unReadNotify.unshift(data);
      if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
        $scope.getOwnerNotifictaions();
    }
    })
  });
  channel9.bind('ProductRequestedEvent', function (data) {
    $scope.$apply(function(){
      $scope.unReadNotify.unshift(data);
      if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
        $scope.getOwnerNotifictaions();
    }
    })
  });
  channel10.bind('metroCampaignClosedEvent', function (data) {
    $scope.$apply(function(){
      $scope.unReadNotify.unshift(data);
      if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
        $scope.getOwnerNotifictaions();
    }
    })
  });
  channel11.bind('metroCampaignLaunchedEvent', function (data) {
    $scope.$apply(function(){
      $scope.unReadNotify.unshift(data);
      if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
        $scope.getOwnerNotifictaions();
    }
    })
  });
  channel12.bind('metroCampignLockedEvent', function (data) {
    $scope.$apply(function(){
      $scope.unReadNotify.unshift(data);
      if($state.current.url == 'user-notifications' || $state.current.url == 'admin-notifications' || $state.current.url == 'owner-notifications'){
        $scope.getOwnerNotifictaions();
    }
    })
  });

  
}
  
/* Notification Ends */
// Hb Menu redirections code start
$scope.ReddirectNotificationOwner = function(){
  $window.location.href = '/owner/demo---landmark-ooh/owner-notifications';
 }
 $scope.ReddirectOwnerProfile = function(){
  $window.location.href = '/owner/demo---landmark-ooh/profile';
 }
 $scope.ReddirecampaignsOwner = function(){
  $window.location.href = '/owner/demo---landmark-ooh/campaigns';
 }
 $scope.ReddirePaymentsOwner = function(){
  $window.location.href = '/owner/demo---landmark-ooh/payments';
 }
 $scope.ReddireResetPasswordOwner = function(){
  $window.location.href = '/owner/demo---landmark-ooh/reset_password';
 }
// Hb Menu redirections code end

  /*=================================
  | mdDilalog close function
  =================================*/

  if (typeof $rootScope.closeMdDialog !== 'function') {
    $rootScope.closeMdDialog = function () {
      $mdDialog.hide();
    }
  }
  $scope.activeUserCampaigns = [];
  $scope.loadActiveUserCampaigns = function () {
    CampaignService.getActiveUserCampaigns().then(function (result) {
      $scope.activeUserCampaigns = result;
      $scope.loading=true;
      $scope.activeOwnerCampaignsList = result.filter(function(item){
        if(item.status == 100){
          return true;
        }
        $scope.loading=false;
      })
    });
  }
  $scope.loadActiveUserCampaigns();
  $scope.closeMenuSidenavIfMobile = function(){
    if($window.innerWidth <=420){
      $mdSidenav('ownerLeftSidenav').close();
    }
  }

  $scope.closeMenuSidenavIfMobile = function(){
    if($window.innerWidth <=768){
      $mdSidenav('ownerLeftSidenav').close();
    }
  }

  $scope.showCampagin = false;
    $scope.toogelCampagin = function () {
      $scope.showCampagin = !$scope.showCampagin;
    }
  /*=================================
  | mdDilalog close function
  =================================*/

  $rootScope.config = config;

  if ($stateParams.client_slug) {
    $rootScope.clientSlug = $stateParams.client_slug;
  }

  if (localStorage.loggedInUser) {
    $rootScope.loggedInUser = JSON.parse(localStorage.loggedInUser);
  }

  $scope.getAvatar = function () {
    var payload = $auth.getPayload();
    var userMongo = typeof payload !== 'undefined' ? payload.userMongo : undefined;
    if (typeof userMongo !== 'undefined' && typeof userMongo.profile_pic !== 'undefined' && userMongo.profile_pic != '') {
      return {
        present: true,
        profile_pic: userMongo.profile_pic
      }
    }
    else {
      return {
        present: false
      }
    }
  }

  $scope.logout = function () {
    $auth.logout().then(function (result) {
      $rootScope.isAuthenticated = false;
      $location.path('/');
      localStorage.clear();
      toastr.warning('You have successfully signed out!');
    });
  }
  // var user = localStorage.getItem("loggedInUser");
  // var parsedData = JSON.parse(user);
  // var user_email = parsedData.user_email;
  // $scope.user = {
  //   email: user_email
  // };
  // $scope.queryTxt = '';
  // $scope.findForMe = function(query) {
  //   var user = localStorage.getItem("loggedInUser");
  //   var parsedData = JSON.parse(user);
  //   var user_id = parsedData.user_id;
  //   UserService.findForMe({user_query: query, loggedinUser: user_id}).then((result) => {
  //     if (result && result.status)
  //       toastr.success(result.message);
  //     else
  //       toastr.error(result.message);
  //     $mdDialog.hide();
  //   });
  // }
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



// SORTING FOR owner notifications

$scope.sortAsccrfqaccao =function(headingName, type){
  $scope.upArrowColour = headingName;
  $scope.sortType ="Asccrfqaccao";
  if (type=="string"){
    $scope.newOfferData = $scope.getOwnerNotifictaionss.map(e=>{
    return {
    ...e,
    name: e.name,
    user_email: e.user_email
    }
    })
    $scope.getOwnerNotifictaionss = [];
    $scope.getOwnerNotifictaionss = $scope.newOfferData.sort((a,b) =>{
      console.log(a[headingName])
      if(b[headingName] != undefined){
      return  a[headingName].localeCompare(b[headingName], undefined, {
        numeric: true,
        sensitivity: 'base'
      });
    }
    })
    
    // $scope.RfpData = $scope.newOfferData;
    }
  $scope.getOwnerNotifictaionss = $scope.getOwnerNotifictaionss.sort((a,b)=>{

       if(type == 'boolean'){
         return a[headingName] ? 1 : -1 
     }
      else {
         return a[headingName] - b[headingName]
      }
  })
  console.log($scope.getOwnerNotifictaionss)
}
$scope.sortDsccrfqaccao =function(headingName, type){
  $scope.downArrowColour = headingName;
  $scope.sortType ="Dsccrfqaccao";
  if (type=="string"){
    $scope.newOfferData = $scope.getOwnerNotifictaionss.map(e=>{
    return {
    ...e,
    name: e.name,
    user_email: e.user_email
    }
    })
    $scope.getOwnerNotifictaionss = [];
    $scope.getOwnerNotifictaionss = $scope.newOfferData.sort((a,b) =>{
      console.log(a[headingName])
      if(b[headingName] != undefined){
      return  b[headingName].localeCompare(a[headingName], undefined, {
        numeric: true,
        sensitivity: 'base'
      });
    }
    })
    
    // $scope.RfpData = $scope.newOfferData;
    }
 $scope.getOwnerNotifictaionss = $scope.getOwnerNotifictaionss.sort((a,b)=>{

      if(type == 'boolean'){
         return a[headingName] ? -1 : 1 
     }
     else {
         return  b[headingName] - a[headingName] 
     }
 })
 console.log($scope.getOwnerNotifictaionss)
};


//SORTING FOR owner notifications




  // $scope.toggleLeft = buildToggler('left');

  // function buildToggler(navID) {
  //   return function() {
  //     // Component lookup should always be available since we are not using `ng-if`
  //     $mdSidenav(navID)
  //       .toggle()
  //       .then(function () {
  //         $log.debug("toggle " + navID + " is done");
  //       });
  //   };
  // }


  // Dummy chart data

  var chart1 = {};
  chart1.type = "google.charts.Bar";
  chart1.displayed = false;
  chart1.data = {
    "cols": [{
      id: "month",
      label: "Month",
      type: "string"
    }, {
      id: "Available-id",
      label: "Available",
      type: "number"
    }, {
      id: "Booked-id",
      label: "Booked",
      type: "number"
    }, {
      id: "Total-id",
      label: "Total",
      type: "number"
    }],
    "rows": [
      {
        c: [
          {
            v: "January"
          }, {
            v: 19,
            f: "19 hoardings"
          }, {
            v: 12,
            f: "12 hoardings"
          }, {
            v: 31,
            f: "31 hoardings"
          }
        ]
      },
      {
        c: [
          {
            v: "February"
          }, {
            v: 13,
            f: "13 hoardings"
          }, {
            v: 19,
            f: "19 hoardings"
          }, {
            v: 32,
            f: "32 hoardings"
          }
        ]
      },
      {
        c: [
          {
            v: "March"
          }, {
            v: 24,
            f: "24 hoardings"
          }, {
            v: 5,
            f: "5 hoardings"
          }, {
            v: 29,
            f: "29 hoardings"
          }
        ]
      },
      {
        c: [
          {
            v: "April"
          }, {
            v: 13,
            f: "13 hoardings"
          }, {
            v: 19,
            f: "19 hoardings"
          }, {
            v: 32,
            f: "32 hoardings"
          }
        ]
      }
    ]
  };

  chart1.options = {
    "title": "Sales per month",
    "isStacked": "true",
    "fill": 20,
    "displayExactValues": true,
    "vAxis": {
      "title": "Sales unit",
      "gridlines": {
        "count": 10
      }
    },
    "hAxis": {
      "title": "Date"
    }
  };
  $scope.myChart = chart1;
  // Dummy chart data ends


  $scope.toggleOwnerLeftSidenav = function () {
    $mdSidenav('ownerLeftSidenav').toggle();
  };

  $scope.toggleOwnerRightSidenav = function () {
    $mdSidenav('ownerRightSidenav').toggle();
  };
  $scope.editProfieSidenav = function () {
    $mdSidenav('ownereditProfile').toggle();
  };
  $scope.openHelpScreen = function (ev) {
    $mdDialog.show({
      templateUrl: 'views/owner/helpnsupport.html',
      clickOutsideToClose: true,
    });
  };


  /*================================
  === Long polling notifications ===
  ================================*/
  // $scope.ownerNotifs = [];
  // var getOwnerNotifs = function () {
  //   var last_notif = 0;
  //   if ($scope.ownerNotifs && $scope.ownerNotifs.length > 0) {
  //     last_notif = moment.utc($scope.ownerNotifs[0].updated_at).valueOf();
  //   }
  //   OwnerNotificationService.getAllOwnerNotifications(last_notif).then(function (result) {
  //     $scope.ownerNotifs = result.concat($scope.ownerNotifs);
  //     $timeout(getOwnerNotifs, 1000);
  //   });
  // }
  // getOwnerNotifs();

  /*===============================
  |   Notification navigation 
  ===============================*/
  // $scope.viewNotification = function (notification) {
  //   if (notification.type == 9) {
  //     $location.path('owner/' + $rootScope.clientSlug + '/requested-hoardings/' + notification.data.product_id);
  //   }
  //   else if (notification.type > 0 && notification.type < 8) {
  //     $location.path('owner/' + $rootScope.clientSlug + '/campaign-details/' + notification.data.campaign_id + "/0");
  //   }
  //   OwnerNotificationService.updateNotifRead(notification.id).then(function (result) {
  //     if (result.status == 1) {
  //       // remove notif from list
  //       $scope.ownerNotifs = _.filter($scope.ownerNotifs, function (notif) { return notif.id != notification.id; })
  //     }
  //     else {
  //       toastr.error(result.message);
  //     }
  //   });
  // }

  /*=================================
  | Product search
  =================================*/
  // $scope.simulateQuery = false;
  $scope.isDisabled = false;
  // $scope.querySearch   = querySearch;
  // $scope.selectedItemChange = selectedItemChange;
  // $scope.searchTextChange   = searchTextChange;


  $scope.ownerProductSearch = function (query) {
    return OwnerProductService.searchOwnerProducts(query.toLowerCase()).then(function (res) {
      return res;
    });
  }

  $scope.viewSelectedProduct = function (product) {
    if (typeof product !== 'undefined') {
      $location.path('/owner/' + $rootScope.clientSlug + '/product-details/' + product.id);
    }
    else {
      $location.path('/owner/' + $rootScope.clientSlug + '/hoarding-list');
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
  //for highlighting menu checking route path
  $scope.isActive = function(route){
    console.log("Hello",$location.path(),route === $location.path(),route)
    return route === $location.path();
  }

  // Verify User Login
  function verifyUserLogin() {
    try {
      var loggedInUser = localStorage.getItem('loggedInUser');
      var user_id;
      if (loggedInUser) {
        loggedInUser = JSON.parse(loggedInUser);
        user_id = loggedInUser.user_id;

        AdminUserService.verifyLogin(user_id).then(result => {
          if (result.status == 1 && result.message == 'User record found.') {
            //do nothing
            console.log('Owner verified successfully!');
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


// .value('googleChartApiConfig', {
//   version: '1.1',
//   optionalSettings: {
//     packages: ['bar'],
//     language: 'en'
//   }
// });