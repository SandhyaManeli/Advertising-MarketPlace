'user strict'
angular.module("bbManager").controller("ContactCtrl", function ($scope,$http) {


    ///** Queries Start **///

    $scope.gridQueries = {  
        paginationPageSizes: [25, 50, 75],
        paginationPageSize: 25,
        enableCellEditOnFocus: false,
        multiSelect: false,
        enableFiltering: true,
        enableSorting: true,
        showColumnMenu: false,
        enableGridMenu: true,
        enableRowSelection: true,
        enableRowHeaderSelection: false,
   };
    
   $scope.gridQueries.columnDefs = [
        { name: 'id', displayName: 'Sr.no', enableCellEdit: false, width: '10%',enableFiltering: false,},
        { name: 'name', displayName: 'Name', width: '20%', enableCellEdit: false },
        { name: 'email', displayName: 'Email Id', width: '20%', enableCellEdit: false },
        { name: 'Company', displayName: 'Company Name' , width: '20%' },
        { name: 'message', displayName: 'Message' , width: '20%',enableFiltering: false, },
        {
        name: 'Action', field: 'Action', width: '10%',
        cellTemplate: '<div class="ui-grid-cell-contents"><span><a ng-href="#" ng-click=""><md-icon><i class="material-icons">remove_red_eye</i></md-icon></a></span></div>',
        enableFiltering: false,
                        }
    ];
  
$scope.msg = {};
$scope.gridQueries.onRegisterApi = function(gridApi){
      //set gridApi on scope
      
      $scope.gridApi = gridApi;
      gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){
        $scope.msg.lastCellEdited = 'edited row id:' + rowEntity.id + ' Column:' + colDef.name + ' newValue:' + newValue + ' oldValue:' + oldValue ;
        $scope.$apply();
      });
    };

    $http.get('fakedb/company.json')
.success(function(data) {
  for(i = 0; i < data.length; i++){
    data[i].registered = new Date(data[i].registered);
  }
  $scope.gridQueries.data = data;
});

///** Queries End **///

///** Subscribers Start  **///

$scope.gridSubscribers = {  
    paginationPageSizes: [25, 50, 75],
    paginationPageSize: 25,
    enableCellEditOnFocus: false,
    multiSelect: false,
    enableFiltering: true,
    enableSorting: true,
    showColumnMenu: false,
    enableGridMenu: true,
    enableRowSelection: true,
    enableRowHeaderSelection: false,
};

$scope.gridSubscribers.columnDefs = [
    { name: 'id', displayName: 'Sr.no', enableCellEdit: false, width: '20%',enableFiltering: false,},
    { name: 'email', displayName: 'Email Id', width: '40%', enableCellEdit: false },
  
    {
    name: 'Action', field: 'Action', width: '40%',
    cellTemplate: '<div class="ui-grid-cell-contents"><span><a ng-href="#" ng-click=""><md-icon><i class="material-icons">remove_red_eye</i></md-icon></a></span></div>',
    enableFiltering: false,
                    }
];

$scope.msg = {};
$scope.gridSubscribers.onRegisterApi = function(gridApi){
  //set gridApi on scope
  
  $scope.gridApi = gridApi;
  gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){
    $scope.msg.lastCellEdited = 'edited row id:' + rowEntity.id + ' Column:' + colDef.name + ' newValue:' + newValue + ' oldValue:' + oldValue ;
    $scope.$apply();
  });
};

$http.get('fakedb/company.json')
.success(function(data) {
for(i = 0; i < data.length; i++){
data[i].registered = new Date(data[i].registered);
}
$scope.gridSubscribers.data = data;
});


///** Subscribers End  **///
})