angular.module("bbManager").controller("AuthCtrl", function ($scope,$window, $mdDialog, $location, $rootScope, $auth, $state, toastr, UserService) {

	$scope.showSignin = true;
	$scope.forgotPasswordpage = false;
	$scope.signInUserError = '';
	$scope.user = {};
	$scope.showPassword = false;
	$scope.passwordEmailSentSuccess = false;
	$scope.togglePassword = function () { 
		$scope.showPassword = !$scope.showPassword; 
	};

	$scope.hasInput = false;
	$scope.clearErrMsg = function (event) { 
		if (event.target.value) {
			$scope.hasInput = true;
			if (event.target.value.length >= 8) {
				$scope.signInUserError = ''; 
			}
		} else
			$scope.hasInput = false;
		//console.log(event.target.value);
		//console.log($scope.user.password);
	};
	
	$scope.closeMdDialog = function () {
		$mdDialog.hide();
	}
	$scope.returnToLogin = function(){
		$scope.user.email = '';
		$scope.user.password = '';
		$scope.errorMsg = '';
		$scope.signInUserError = '';

		$scope.forgotPasswordpage = false;
		$scope.passwordEmailSentSuccess = false;
	}
	$scope.signInUser = function () {		
		$scope.signInUserError = '';
		$scope.errorMsg = '';
		$auth.login($scope.user).then(function (res) {
			if ($auth.isAuthenticated()) {
                localStorage.removeItem('loggedInUser');
				var loggedInUser = {};
				var payload = $auth.getPayload();
				var userData = payload.user;
				var userMongoData = payload.userMongo;
				loggedInUser.clientId = userData.client_id;
				loggedInUser.user_id = userMongoData.id;
                                                                                          loggedInUser.mong_id = userMongoData.client_mongo_id;
				loggedInUser.client_slug = userData.client_slug;
				loggedInUser.email = userData.email;
				loggedInUser.firstName = userMongoData.first_name;
				loggedInUser.lastName = userMongoData.last_name;
				loggedInUser.user_type = userMongoData.user_type;
                                                                                         loggedInUser.user_role = userMongoData.user_type;
				loggedInUser.avatar = userMongoData.user_avatar;
				$rootScope.isAuthenticated = true;
				$rootScope.loggedInUser = loggedInUser;
				localStorage.loggedInUser = JSON.stringify(loggedInUser);
				toastr.success('You have successfully signed in!');
				// if($window.innerWidth <=768){
				// 	toastr.success('Kindly turn phone sideways to use AMP site');
				// }
				$("#mobileMenu").hide();
				$("#closeBtn").hide();
				$mdDialog.hide();
				$rootScope.$emit("listeningActiveUserCampaigns")
				/*if($rootScope.postLoginState){
					$state.go($rootScope.postLoginState, null);
				}
				else */if($auth.getPayload().userMongo.user_type == "bbi"){
					//$state.go("admin.home", null);
					$state.go("admin.report", null);
				}
				else if($auth.getPayload().userMongo.user_type == "owner"){
					//$location.path("/owner/" + payload.userMongo.client_slug + "/feeds");
					//$state.go("owner.report", null);
					$location.path("/owner/" + payload.userMongo.client_slug + "/report");
				}
				else{
					//$state.go("index.location", null,{reload: true});
					$state.go("index.report", null);
				}
			}
			else if(res.data.status == 0){
				$scope.signInUserError = res.data.message;
				//toastr.error(res.data.message);
				console.log('Not authenticated');
			}
			//$mdDialog.hide();
		}).catch(function (error) {
			toastr.error(error.data.message, error.status);
			$mdDialog.hide();
		});
	}

	// $scope.close = function(){
	// 	$mdDialog.hide();		
	// }
	
	///Agency Sign In functionolity

	$scope.userAgencyHeader = true;

	/// Register Dailog start here
	$scope.showRegisterDialog = function (ev) {
		$mdDialog.show({
			templateUrl: 'views/register.html',
			fullscreen: $scope.customFullscreen,
			clickOutsideToClose: true, 
			preserveScope: true, 
			scope: $scope,
			controller: 'RegistrationCtrl',
			resolve: { 
				userService: ['$ocLazyLoad', function($ocLazyLoad) {
					return $ocLazyLoad.load('./services/UserService.js');
				}],
				companyService: ['$ocLazyLoad', function($ocLazyLoad) {
					return $ocLazyLoad.load('./services/admin/CompanyService.js');
				}],
				js: ['$ocLazyLoad', function($ocLazyLoad) {
					return $ocLazyLoad.load('./controllers/RegistrationCtrl.js');
				}],
			},
		})
	};
	$scope.showRfpDialog = function (ev) {
		$mdDialog.show({
			templateUrl: 'views/rfp.html',
			fullscreen: $scope.customFullscreen,
			clickOutsideToClose: true,
			preserveScope: true,
			scope: $scope,
			controller: 'RfpCtrl',
			resolve: { 
				js: ['$ocLazyLoad', function($ocLazyLoad) {
					return $ocLazyLoad.load('./controllers/RfpCtrl.js');
				}],
				ownerLocationService: ['$ocLazyLoad', function($ocLazyLoad) {
					return $ocLazyLoad.load('./services/owner/LocationService.js');
				}],
				ownerProductService: ['$ocLazyLoad', function($ocLazyLoad) {
					return $ocLazyLoad.load('./services/owner/ProductService.js');
				}],
			},
		})
	};
	$scope.showRfpWithoutLoginDialog = function (ev) {
		$mdDialog.show({
			templateUrl: 'views/rfp-without.html',
			fullscreen: $scope.customFullscreen,
			clickOutsideToClose: true,
			preserveScope: true,
			scope: $scope,
			controller: 'RfpWithoutLoginCtrl',
			resolve: { 
				userService: ['$ocLazyLoad', function($ocLazyLoad) {
					return $ocLazyLoad.load('./services/UserService.js');
				}],
				ownerLocationService: ['$ocLazyLoad', function($ocLazyLoad) {
					return $ocLazyLoad.load('./services/owner/LocationService.js');
				}],
				js: ['$ocLazyLoad', function($ocLazyLoad) {
					return $ocLazyLoad.load('./controllers/RfpWithoutLoginCtrl.js');
				}],
			},
		})
	};
	$scope.showForgotPasswordDialog = function () {
		// $scope.userForm = false;
		$scope.forgotPasswordpage = true;
		$scope.forgotPwd.email = '';
		$scope.errorMsg = '';
		// $scope.agencyForm = false;
		// $scope.userAgencyHeader = false;
	}
	//form
	$scope.currentNavItem = 'users';
	$scope.userForm = true;
	$scope.agencyForm = false;
	$scope.users = function () {
		$scope.userForm = true;
		$scope.agencyForm = false;
		$scope.forgotPasswordpage = false;
	}
	$scope.agency = function () {
		$scope.agencyForm = true;
		$scope.userForm = false;
		$scope.forgotPasswordpage = false;
	}

	$scope.invalidEmail = false;
	$scope.errorMsg = '';
	$scope.forgotPwd = {
		email: ''
	};
	$scope.requestResetPassword = function(forgotPwd){
		var sendObj = {
			email : forgotPwd.email
		};
		UserService.requestResetPassword(sendObj).then(function(result){
			if(result.status == 1){
				$scope.passwordEmailSentSuccess = true;
				$scope.errorMsg = '';
				return false;
				//toastr.success(result.message);			
			}			
			else{
				//toastr.error(result.message);
				$scope.invalidEmail = true;
				$scope.errorMsg = result.message;
			}
		});
		//$mdDialog.hide();
	}

	// $scope.close = function () {
	// 	$mdDialog.hide();
	// 	$state.reload();
	// }
})
