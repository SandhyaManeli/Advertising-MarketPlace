angular.module("bbManager").controller('customerQueriesCtrl', function ($scope, $http, AdminContactService, $mdDialog, $templateCache, $window, toastr, $rootScope) {

  // $scope.gridQueries  = {
  //   paginationPageSizes: [25, 50, 75],
  //   paginationPageSize: 25,
  //   enableCellEditOnFocus: false,
  //   multiSelect: false,
  //   enableFiltering: true,
  //   enableSorting: true,
  //   showColumnMenu: false,
  //   enableGridMenu: true,
  //   enableRowSelection: true,
  //   enableRowHeaderSelection: false,
  //   columnDefs : [
  //   { name: 'id', displayName: 'Sr.no', enableCellEdit: false, width: '5%', enableFiltering: false, cellTemplate: '<span>{{rowRenderIndex+1}}</span>', },
  //   { name: 'name', displayName: 'Name',  enableCellEdit: false },
  //   { name: 'email', displayName: 'Email Id',  enableCellEdit: false },
  //   { name: 'companyName', displayName: 'Company name',  enableCellEdit: false },
  //   { name: 'message', displayName: 'Message', enableCellEdit: false },

  //   { name: 'Action', field: 'Action', 
  //     cellTemplate: '<div class="ui-grid-cell-contents"><span><a ng-click=""><md-icon><i class="material-icons" id="feedbackButton{{row.entity.id}}" ng-style="row.entity.viewed && {\'color\':\'limegreen\'} " ng-click="grid.appScope.showConfirm(row.entity)">remove_red_eye</i></md-icon></a></span></div>',
  //     enableFiltering: false,
  //   }]
  // };


  AdminContactService.userQuery().then(function (response) {
    //$scope.gridQueries.data = response.data;
    $scope.QueriesData = response.data;
  });

  $scope.showConfirm = function (data) {
    var response = {};
    var dataId = data.id;
    if (data.viewed) {
      $mdDialog.show(
        $mdDialog.alert()
          .title('Have you view this query?')
          .textContent('Message: ' + data.message)
          .ok('Already viewed')
          .targetEvent(data)
      );
    } else {
      var confirm = $mdDialog.confirm()
        .title('Have you checked this query?')
        .textContent('Message: ' + data.message)
        .targetEvent(data)
        .ok('Yes,its checked')
        .cancel('Not yet');

      $mdDialog.show(confirm).then(function () {
        response.viewed = 'true';
        AdminContactService.updateCustomerData(dataId, response).then(function (result) {
          if (result.status == 1) {
            $scope.qerieviewed = true;
            toastr.success(result.message);
            $("#feedbackButton" + dataId).css('color', 'limegreen');
            $mdDialog.hide();
          }
          else {
            toastr.error(result.message);
          }
        });
      });
    }
  };
  // ------------------Subscriber Controller Code ---------------------------

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

  $scope.export = function () {
    if ($scope.export_format == 'csv') {
      var myElement = angular.element(document.querySelectorAll(".custom-csv-link-location"));
      $scope.gridApi.exporter.csvExport($scope.export_row_type, $scope.export_column_type, myElement);
    } else if ($scope.export_format == 'pdf') {
      $scope.gridApi.exporter.pdfExport($scope.export_row_type, $scope.export_column_type);
    }
  };

  $scope.selected_rows = function () {
    var mySelectedRows = $scope.gridApi.selection.getSelectedRows();
    angular.forEach(mySelectedRows, function (value, key) {
      $scope.unsubscribedata(value)
    });
  };

  $scope.unsubscribedata = function (value) {
    var response = {};
    response.softdelete = 'true';
    AdminContactService.updateCustomerData(value.id, response).then(function (result) {
      if (result.status == 1) {
        var index = $scope.Subscribersdata.data.indexOf(value);
        $scope.Subscribersdata.data.splice(index, 1);
        toastr.success("CallCenterInfo Unsubscribed successfully");
      }
      else {
        toastr.error(result.message);
      }
    });
  }

  $scope.ShowConfirm = function (value) {
    if ($window.confirm("Please confirm?")) {
      $scope.unsubscribedata(value)
    }
  }
  // ---------------- Subscriber Controller Code Ends -----------------------------
  //----------------- Call Center Info JS Code ------------------------------------
  //deleteCallCenterInf
  $scope.deleteCallCenterInfo = function (row) {
    var index = $scope.callCenterData.data.indexOf(row);
    $scope.callCenterData.data.splice(index, 1);
    toastr.success("CallCenterInfo deleted successfully");
  }

  AdminContactService.requestCallBack().then(function (response) {
    //$scope.gridcallCenter.data = response.data;
    $scope.callCenterData = response.data;
  });

  $scope.CallFeedback = function (data) {
    var dataId = data;
    $mdDialog.show({
      templateUrl: 'templateId',
      clickOutsideToClose: false,
      controller: function ($scope, $mdDialog, toastr) {
        $scope.FeedbackData = data;
        $scope.response = {};
        if (data.call_feedback) {
          $scope.response.call_feedback = data.call_feedback;
        }
        if ($rootScope.mydataid == dataId) {
          $scope.response.call_feedback = $rootScope.updated_feedback;
        }
        $scope.savefeedback = function (response) {
          AdminContactService.updateCustomerData(dataId, response).then(function (result) {
            if (result.status == 1) {
              $scope.callinfoviewed = true;
              toastr.success(result.message);
              $("#feedbackButton" + dataId).css('background-color', 'limegreen');
              $rootScope.updated_feedback = result.data.call_feedback;
              $rootScope.mydataid = dataId;
              $mdDialog.hide();
            }
            else {
              toastr.error(result.message);
            }
          });
        }
        $scope.cancelFeedbackBox = function () {
          $mdDialog.hide();
        }
      }
    });
  };
  //-----------------Call Center Info JS Code Ends ------------------------------
});