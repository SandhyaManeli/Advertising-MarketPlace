angular.module("bbManager").controller("FindForMeCtrl", function ($scope, $mdDialog, toastr, UserService) {
    var user = localStorage.getItem("loggedInUser");
    var parsedData = JSON.parse(user);
    var user_id = parsedData.user_id;
    var user_email = parsedData.user_email;
    $scope.user = {
        email: user_email
    };
    $scope.queryTxt = '';
    $scope.findForMe = function(query) {
        UserService.findForMe({user_query: query, loggedinUser: parsedData}).then((result) => {
            if (result && result.status)
                toastr.success(result.message);
            else
                toastr.error(result.message);
            $mdDialog.hide();
        });
    }
});
