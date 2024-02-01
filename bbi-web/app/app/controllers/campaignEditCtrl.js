angular.module("bbManager").controller('campaiginEditController', function ($scope,$interval,$mdDialog) {


$scope.hoardingitems=[
      {
        id:'AD-001',
        type:'Billboard',
        area:'Amreepet',
        size:'20*30',
        light:'Yes',
        sdate:'20-Fed-2017',
        edate:'20-April-2017',
        price:'25,000',
      },
      {
        id:'AD-002',
        type:'Unipole',
        area:'Amreepet',
        size:'20*30',
        light:'Yes',
        sdate:'20-Fed-2017',
        edate:'20-April-2017',
        price:'25,000',
      },
      {
        id:'AD-003',
        type:'Digital',
        area:'Amreepet',
        size:'20*30',
        light:'Yes',
        sdate:'20-Fed-2017',
        edate:'20-April-2017',
        price:'25,000',
      }
      ]

      $scope.limit= 5;
      $scope.loadMore = function() {
        $scope.limit = $scope.items.length
      };

      //update payment dailog

    $scope.showPaymentdailog = function () { 
    $mdDialog.show({
      templateUrl: 'views/updatepaymentDailog.html',
      fullscreen: $scope.customFullscreen,
      clickOutsideToClose: true
    })
  };

  $scope.cancel = function () {
    $mdDialog.cancel();
  };

});