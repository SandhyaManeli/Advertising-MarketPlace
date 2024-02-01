angular.module("bbManager").controller('outSourcing', function($scope,$mdDialog,$mdSidenav) {
  // sidenav 
     $scope.addOutsourcingAgent = function () {
    $mdSidenav('ownerAddAgency').toggle();
    };

    $scope.showBillboardsData = true;
    $scope.showUnipoleData = false;
    $scope.showdigitalData = false;

    $scope.showBillboards = function(){
        $scope.showBillboardsData = true;
        $scope.showUnipoleData = false;
        $scope.showdigitalData = false;
    }
    $scope.unipole = function(){
        $scope.showBillboardsData = false;
        $scope.showUnipoleData = true;
        $scope.showdigitalData = false;
    }
    $scope.showtransit = function(){
        $scope.showBillboardsData = false;
        $scope.showUnipoleData = false;
        $scope.showdigitalData = true;
    }

      $scope.teamlist =[
      {
        sno:'1',
        comapanyname:'Chanikya',
        typeofcompany:'Neon',
        agentname:'srikanth',
        phone:'8899667755',
        email:'chanikya@Billboards.com',
        address:'Amreepet',
      },
      {
        sno:'2',
        comapanyname:'Chanikya',
        typeofcompany:'Neon',
        agentname:'srikanth',
        phone:'8899667755',
        email:'chanikya@Billboards.com',
        address:'Amreepet',
      },
      {
        sno:'3',
        comapanyname:'Chanikya',
        typeofcompany:'Neon',
        agentname:'srikanth',
        phone:'8899667755',
        email:'chanikya@Billboards.com',
        address:'Amreepet',
      },
      ]

      // $scope.agentForm = function(ev) {
      //   $mdDialog.show({
      //     templateUrl:'views/owner/agentform.html',
      //     clickOutsideToClose:true,
      //     fullscreen:$scope.customFullscreen,
      //   });
      // };

    });