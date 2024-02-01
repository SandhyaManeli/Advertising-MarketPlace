angular.module("bbManager").controller('subscriberCtrl', function ($scope, $http, $templateCache, $window, AdminContactService, toastr) {

  $templateCache.put('ui-grid/selectionRowHeaderButtons',
    "<div class=\"ui-grid-selection-row-header-buttons \" ng-class=\"{'ui-grid-row-selected': row.isSelected}\" ><input style=\"margin: 0; vertical-align: middle\" type=\"checkbox\" ng-model=\"row.isSelected\" ng-click=\"row.isSelected=!row.isSelected;selectButtonClick(row, $event)\">&nbsp;</div>"
  );

  $templateCache.put('ui-grid/selectionSelectAllButtons',
    "<div class=\"ui-grid-selection-row-header-buttons \" ng-class=\"{'ui-grid-all-selected': grid.selection.selectAll}\" ng-if=\"grid.options.enableSelectAll\"><input style=\"margin: 0; vertical-align: middle\" type=\"checkbox\" ng-model=\"grid.selection.selectAll\" ng-click=\"grid.selection.selectAll=!grid.selection.selectAll;headerButtonClick($event)\"></div>"
  ); 

  // $scope.gridSubscribers  = {
  //   paginationPageSizes: [25, 50, 75],
  //   paginationPageSize: 25,
  //   enableCellEditOnFocus: false,
  //   multiSelect: true,
  //   enableFiltering: true,
  //   enableSorting: true,
  //   showColumnMenu: false,
  //   enableGridMenu: true,
  //   enableRowSelection: true,
  //   enableRowHeaderSelection: true,
  //   // { name: 'id', displayName: 'Sr.no', enableCellEdit: false, width: '20%', enableFiltering: false, cellTemplate: '<span>{{rowRenderIndex+1}}</span>', },
  //   columnDefs : [
  //   { name: 'email', displayName: 'Email Id', width: '40%', enableCellEdit: false, enableFiltering: false },
  //   { name: 'Action', field: 'Action', width: '40%', enableCellEdit: false,
  //     cellTemplate: '<div class="ui-grid-cell-contents"><span><a ng-click="grid.appScope.ShowConfirm(row.entity)"><md-icon><i class="material-icons">unsubscribe</i></md-icon></a></span></div>',
  //     enableFiltering: false,
  //   }],
  //   onRegisterApi: function(gridApi){ 
  //     $scope.gridApi = gridApi;
  //   },
  //   exporterPdfDefaultStyle: {fontSize: 9},
  //   exporterPdfTableStyle: {margin: [10, 10, 10, 10]},
  //   exporterPdfTableHeaderStyle: {fontSize: 10, bold: true, italics: true, color: 'red'},
  //   exporterPdfOrientation: 'portrait',
  //   exporterPdfPageSize: 'LETTER',
  //   exporterPdfMaxGridWidth: 500, 
  // }; 
 
  // Select all checkbox---
  // $scope.checkAll = function() {
  //   angular.forEach($scope.Subscribersdata, function(Subscribers) {
  //     Subscribers.select = $scope.selectAll;
  //   });
  // };
  // Select all checkbox---END

  AdminContactService.subscribe().then(function (response) {    
    $scope.Subscribersdata = response.data;
    //$scope.gridSubscribers.data = response.data;
  });
   
  $scope.export = function(){
    if ($scope.export_format == 'csv') {
      var myElement = angular.element(document.querySelectorAll(".custom-csv-link-location"));
      $scope.gridApi.exporter.csvExport( $scope.export_row_type, $scope.export_column_type, myElement );
    }else if ($scope.export_format == 'pdf') {
      $scope.gridApi.exporter.pdfExport( $scope.export_row_type, $scope.export_column_type );
    }
  };

  $scope.selected_rows = function(){
    var mySelectedRows = $scope.gridApi.selection.getSelectedRows();
    angular.forEach(mySelectedRows, function(value, key) {
      $scope.unsubscribedata(value)
    });
  };

  $scope.unsubscribedata = function(value){
    var response = {};
    response.softdelete = 'true';
    AdminContactService.updateCustomerData(value.id, response).then(function(result){
      if(result.status == 1){
        var index = $scope.Subscribersdata.data.indexOf(value);
        $scope.Subscribersdata.data.splice(index, 1);
        toastr.success("CallCenterInfo Unsubscribed successfully");      
      }
      else{
        toastr.error(result.message);
      }
    });    
  }

  $scope.ShowConfirm = function (value) {
    if ($window.confirm("Please confirm?")) {
      $scope.unsubscribedata(value)
    } 
  }
 
});