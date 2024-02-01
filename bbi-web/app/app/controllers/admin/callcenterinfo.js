angular.module("bbManager").controller('callCenterCtrl', function ($scope, $http, toastr, AdminContactService, $mdDialog, $rootScope) {
  // $scope.gridcallCenter = {
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
  // };
  // $scope.gridcallCenter.columnDefs = [
  //   { name: 'id', displayName: 'Sr.no', enableCellEdit: false, enableFiltering: false, cellTemplate: '<span>{{rowRenderIndex+1}}</span>', },
  //   { name: 'phoneNo', displayName: 'Phone Number', enableCellEdit: false },
  //   {
  //     name: 'Action', field: 'Action', enableCellEdit: false,
  //     cellTemplate: '<div class="ui-grid-cell-contents"><button id="feedbackButton{{row.entity.id}}" ng-style="row.entity.call_feedback && {\'background-color\':\'limegreen\'} " class="btn btn-small" ng-click="grid.appScope.CallFeedback(row.entity)">Call Feedback</button></div>',
  //     enableFiltering: false,
  //   }
  // ];
  
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
    var dataId = data.id;
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
});