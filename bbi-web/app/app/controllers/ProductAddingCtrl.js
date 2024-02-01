angular.module("bbManager").controller('ProductAddCtrl', function ($scope) {
    $scope.statuses = ['Planned', 'Confirmed', 'Cancelled'];
    $scope.options = ['Option 1', 'Option 2', 'Option 3', 'Option 4', '...'];
    $scope.submit = function() {
      // submit code goes here
    };
    $scope.reset = function() {
      $scope.obj = {
        name: "",
        myselect: "",
        status: ""
      }
    };
    $scope.reset();

})