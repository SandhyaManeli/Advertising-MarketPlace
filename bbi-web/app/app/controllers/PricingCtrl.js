angular.module("bbManager").controller('PricingCtrl', function($scope, $mdDialog) {

    $scope.showTabDialog = function (ev) {
        $mdDialog.show({
          templateUrl: 'views/sign-in.html',
          fullscreen: $scope.customFullscreen,
          clickOutsideToClose: true
        })
      };
});