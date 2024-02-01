angular.module("bbManager").controller(
  "EnquiriesCtrl",
  function ($scope, toastr, AdminUserService, $window) {
    $scope.enquiries = [];
    $scope.getEnquiries = function () {
      AdminUserService.getEnquiries().then((result) => {
        if (result) {
          $scope.enquiries = result;
        } else toastr.error(result.message);
      });
    };
    $scope.getEnquiries();

    $scope.goBack = function() {
      $window.history.back();
    }
  }
);
