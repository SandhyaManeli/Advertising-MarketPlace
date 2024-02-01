angular.module("bbManager").controller("RegistrationCtrl", function ($scope, $mdDialog, UserService, CompanyService, toastr, $location) {

	$scope.forms = {};
	$scope.showPassword = false;
	$scope.togglePassword = function () { 
		$scope.showPassword = !$scope.showPassword; 
	};
	/*
	* ========== Switching registration forms between User and Agency ===========
	*/
	$scope.chkselct=false;
	$scope.currentNavItem = 'users';
	var params =  $location.$$url;
	console.log(params);
	if(params=="/Advertising-Marketplace-Platform"){
		$scope.myModel = "users"
		$scope.showUserForm = true;
	}
	else{
		$scope.myModel = "default"
	$scope.showUserForm = false;
	}
	$scope.showOwnerForm = false;
	$scope.showUserRegPanel = function () {
		if($scope.myModel == "users"){
			$scope.currentNavItem = 'users';
			$scope.showUserForm = true;
			$scope.showOwnerForm = false;
			$scope.showAgencyForm = false;
		} else {
			$scope.currentNavItem = 'owner';
			$scope.showUserForm = false;
			$scope.showOwnerForm = true;
			$scope.showAgencyForm = false;
		}
		if($scope.myModel == "default"){
		
			$scope.showUserForm = false;
			$scope.showOwnerForm = false;
			$scope.showAgencyForm = false;
		} 
	}
	$scope.showOwnerRegPanel = function () {
		$scope.currentNavItem = 'owner';
		$scope.showUserForm = false;
		$scope.showOwnerForm = true;
		$scope.showAgencyForm = false;
	}
	$scope.showAgencyRegPanel = function () {
		$scope.currentNavItem = 'agency';
		$scope.showUserForm = false;
		$scope.showOwnerForm = false ;
		$scope.showAgencyForm = true;
	}
	/*
	* ========== Switching registration forms between User and Agency ends ===========
	*/

	/*
	* ============ User Registration ============
	*/
	var strongRegularExp = new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})");
    var mediumRegularExp = new RegExp("^(((?=.*[a-z])(?=.*[A-Z]))|((?=.*[a-z])(?=.*[0-9]))|((?=.*[A-Z])(?=.*[0-9])))(?=.{6,})");
	$scope.checkpwdStrength = {
		"width": "150px",
		"height": "25px",
		"float": "right"
	};
	$scope.validateInputPwdText = function(value) {
		if (strongRegularExp.test(value)) {
			$scope.checkpwdStrength["background-color"] = "green";
		} else if (mediumRegularExp.test(value)) {
			$scope.checkpwdStrength["background-color"] = "orange";
		} else {
			$scope.checkpwdStrength["background-color"] = "red";
		}
	};
	$scope.user = {};
	$scope.user.account_type = "Individual Account";
	$scope.buyerRegErrorMsg = '';
	$scope.registerNewUser = function () {
		UserService.registerUser($scope.user).then(function(result){
			if(result.status == 1){
				$mdDialog.hide();
				$scope.user = {};
				$scope.chkselct=false;
				$scope.forms.registerUser.$setUntouched();
				$scope.forms.registerUser.$setPristine();
				toastr.success(result.message);
				$scope.buyerRegErrorMsg = '';
				if(params=="/Advertising-Marketplace-Platform"){
					$scope.myModel = "users"
					$scope.showUserForm = true;
				}
				else{
					$scope.myModel = "default"
				$scope.showUserForm = false;
				}
		$scope.showOwnerForm = false;
		$scope.showAgencyForm = false;
				// if($scope.regNewUserErrors){
				// 	$scope.regNewUserErrors.length = 0;
				// }
			}
			else if(result.status == 0){
				// $scope.regNewUserErrors = result.message;
				//toastr.error(result.message);
				$scope.buyerRegErrorMsg = result.message[0];
			}
		}, function(result){
			toastr.error(result);
		});
	}
	$scope.showSignInDialog = function (ev) {
		$mdDialog.show({
				templateUrl: 'views/sign-in.html',
				fullscreen: $scope.customFullscreen,
				clickOutsideToClose: true,
				preserveScope: true,
				scope: $scope,
				controller: 'AuthCtrl',
				resolve: { 
          js: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load('./controllers/AuthCtrl.js');
          }],
          userService: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load('./services/UserService.js');
          }],
        },
		})
	};
	/*
	* ============ User Registration Ends ============
	*/


	/*
	* ============ Company Registration ============
	*/

	function getClientTypes(){
		CompanyService.getClientTypes().then(function(result){
			$scope.clientTypes = result;
		});
	}
	getClientTypes();

	$scope.client = {};
	$scope.client.account_type = "Individual Account";
	$scope.clientRegErrorMsg = '';
	$scope.registerClient = function (client) {
		CompanyService.registerClient(client).then(function(result){
			if(result.status == 1){
				$mdDialog.hide();
				$scope.client = {};
				$scope.forms.registerClientForm.$setUntouched();
				$scope.forms.registerClientForm.$setPristine();
				toastr.success(result.message);
				$scope.clientRegErrorMsg = '';
				$scope.showOwnerForm = false;
				$scope.showAgencyForm = false;
				$scope.chkselct=false;
				if(params=="/Advertising-Marketplace-Platform"){
					$scope.myModel = "users"
					$scope.showUserForm = true;
				}
				else{
					$scope.myModel = "default"
				$scope.showUserForm = false;
				}
				// if($scope.clientErrorMessages){
				// 	$scope.clientErrorMessages = null;
				// }
				
				
			}
			else if(result.status == 0){
				// $scope.clientErrorMessages = result.message;
				//toastr.error(result.message);
				$scope.clientRegErrorMsg = result.message;

			}
		});
	}
	
	/*
	* ============ Company Registration Ends ============
	*/

	$scope.close = function () {
		$mdDialog.hide();
	}

	$scope.redirectTo = function(url) {
		$location.path(url);
		$mdDialog.hide();
	}
});