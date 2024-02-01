'use strict';

myApp.controller('HomeController', ['$scope', 'NgMap', function($scope, NgMap) {
  NgMap.getMap().then(function(map) {
    // console.log(map.getCenter());
    // console.log('markers', map.markers);
    // console.log('shapes', map.shapes);
  });
  $scope.click = function() {
    alert(1);
  };


  $scope.firstThing = 'AAAAAAAAAAAAAA';
  $scope.otherThings = ['BBBBBBB','CCCCCCCC'];


  $scope.topDirections = ['left', 'up'];
       $scope.bottomDirections = ['down', 'right'];

       $scope.isOpen = false;

       $scope.availableModes = ['md-fling', 'md-scale'];
       $scope.selectedMode = 'md-fling';

       $scope.availableDirections = ['up', 'down', 'left', 'right'];
       $scope.selectedDirection = 'up';


}]);