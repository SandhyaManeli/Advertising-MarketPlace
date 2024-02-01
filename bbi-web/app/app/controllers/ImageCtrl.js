angular.module("bbManager").controller("ImageCtrl", function ($scope, $mdDialog) {

	// $scope.forms = {};
	// $scope.unavailalbeDateRanges = [];
	/*
	* ========== Switching registration forms between User and Agency ===========
	*/
	// $scope.currentNavItem = 'users';
	// $scope.showUserForm = true;
	// $scope.showOwnerForm = false;
	// $scope.showUserRegPanel = function () {
	// 	$scope.currentNavItem = 'users';
	// 	$scope.showUserForm = true;
	// 	$scope.showOwnerForm = false;
	// 	$scope.showAgencyForm = false;
	// }
	// $scope.showOwnerRegPanel = function () {
	// 	$scope.currentNavItem = 'owner';
	// 	$scope.showUserForm = false;
	// 	$scope.showOwnerForm = true;
	// 	$scope.showAgencyForm = false;
	// }
	// $scope.showAgencyRegPanel = function () {
	// 	$scope.currentNavItem = 'agency';
	// 	$scope.showUserForm = false;
	// 	$scope.showOwnerForm = false ;
	// 	$scope.showAgencyForm = true;
	// }
	/*
	* ========== Switching registration forms between User and Agency ends ===========
	*/

	/*
	* ============ User Registration ============
    */
  
//    $scope.getProductUnavailableDatesEdit = function (productId, ev) {
 
// 	var productId = $stateParams.id;
// 	OwnerProductService.getProductUnavailableDates(productId).then(function (dateRanges) {
// 	  $scope.unavailalbeDateRanges = dateRanges;
// 	  $(ev.target).parent().parent().find('input').trigger('click');
// 	  productDatesCalculator()
// 	});
// }

$scope.showProductImage = function (ev, proddetails) {
    console.log(proddetails, "hello")
    $scope.specProductDetail = proddetails
    $mdDialog.show({
        locals: {
            specProductDetail: proddetails
        },
        templateUrl: 'views/image-only.html',
        fullscreen: $scope.customFullscreen,
        clickOutsideToClose: true,
        controller: function ($scope, specProductDetail) {
            $scope.specDetails = specProductDetail;
            // $scope.productDetails = proddetails
            //$scope.img_src_1 = src.f_image;
            $scope.closeMdDialog = function () {
                $mdDialog.hide();
                $mdDialog.show({
                    locals: {
                        specProductDetail: proddetails
                    },
                    templateUrl: 'views/image-popup-large.html',
                    fullscreen: $scope.customFullscreen,
                    clickOutsideToClose: true,
                    controller: function ($scope, specProductDetail) {
                        $scope.specDetails = specProductDetail;
                        // $scope.productDetails = proddetails
                        //$scope.img_src_1 = src.f_image;
                        $scope.closeMdDialog = function () {
                            $mdDialog.hide();
                        }
                    }
                });
            };

			$scope.getImageUrl = function(url) {
				if (url && (url.includes('.png') || url.includes('.PNG') || url.includes('.jpg') || url.includes('.JPG') || url.includes('.jpeg') || url.includes('.JPEG')|| url.includes('.svg') || url.includes('.SVG'))) {
					return url;
				}
				return 'assets/images/no-image.jpg';
			};

        }
    });
};

 










//   $scope.rfpNewUser = function (user) {
//     if($scope.areaObj == null){
//       toastr.error("No DMA Found");
//     }else{
//       for (var item in user.date) {
//         user.date[item].endDate = moment(user.date[item].endDate).format('YYYY-MM-DD')
//         user.date[item].startDate = moment(user.date[item].startDate).format('YYYY-MM-DD')
//       };
     
//       // product.type = product.type.name;
//       // product.area = $scope.areaObj.id;
//       Upload.upload({
//         url: config.apiPath + '/rfp-user-campaign',
//         data: { startDate:$scope.user.date[0].startDate,
// 			endDate:$scope.user.date[0].endDate,
// 			area:$scope.areaObj.id,
// 			producttype : $scope.user.producttype,
// 			campaign_name:$scope.user.campaign_name
//         }
//       }).then(function (result) {
// 		if (result.data.status == 1) {
// 			$scope.resetProduct();			
// 			toastr.success(result.data.message);
// 			$window.location.href = '/user-saved-campaigns';
			
//           }
//           else if (result.data.status == 0) {
//             toastr.error(result.data.message);
//           }
//         //   $scope.hordinglistform.$setPristine();
//         //   $scope.hordinglistform.$setUntouched();
//         }, function (resp) {
//         }, function (evt) {
//         //   var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
//         });
//       }
       
//   };
//   .then(function(result){
// 	if(result.status == 1){
// 		$mdDialog.hide();
// 		toastr.success(result.message);
// 		// if($scope.regNewUserErrors){
// 		// 	$scope.regNewUserErrors.length = 0;
// 		// }
// 	}
// 	else if(result.status == 0){
// 		// $scope.regNewUserErrors = result.message;
// 		toastr.error(result.message);
// 	}
// }, function(result){
// 	toastr.error(result);
// });
 





	// $scope.user = {};
	// $scope.rfpNewUser = function () {
	// 	UserService.registerUser($scope.user).then(function(result){
	// 		if(result.status == 1){
	// 			$mdDialog.hide();
	// 			toastr.success(result.message);
	// 			// if($scope.regNewUserErrors){
	// 			// 	$scope.regNewUserErrors.length = 0;
	// 			// }
	// 		}
	// 		else if(result.status == 0){
	// 			// $scope.regNewUserErrors = result.message;
	// 			toastr.error(result.message);
	// 		}
	// 	}, function(result){
	// 		toastr.error(result);
	// 	});
	// }

	
	/*
	* ============ User Registration Ends ============
	*/


	/*
	* ============ Company Registration ============
	*/

	// function getClientTypes(){
	// 	CompanyService.getClientTypes().then(function(result){
	// 		$scope.clientTypes = result;
	// 	});
	// }
	// getClientTypes();

	// $scope.client = {};
	// $scope.registerClient = function (client) {
	// 	CompanyService.registerClient(client).then(function(result){
	// 		if(result.status == 1){
	// 			$scope.forms.registerClientForm.$setUntouched();
	// 			$scope.forms.registerClientForm.$setPristine();
	// 			toastr.success(result.message);
	// 			// if($scope.clientErrorMessages){
	// 			// 	$scope.clientErrorMessages = null;
	// 			// }
	// 			$mdDialog.hide();
	// 		}
	// 		else if(result.status == 0){
	// 			// $scope.clientErrorMessages = result.message;
	// 			toastr.error(result.message);
	// 		}
	// 	});
	// }
	
	/*
	* ============ Company Registration Ends ============
	*/

	$scope.close = function () {
		$mdDialog.hide();
	}
	// $scope.resetProduct = function() {
	// 	$scope.forms.registerUser.$setPristine();
	// 	$scope.forms.registerUser.$setUntouched();
	// 	document.getElementById("myDropdown").classList.toggle("show");
	// 	$scope.user.producttype = 'Static';
	// 	$scope.areaObj = null;
	// 	$scope.user.campaign_name = '';	
	// 	$scope.user.dates = '';
	// 	$mdDialog.hide();
	//   }

	$scope.getImageUrl = function(url) {
		if (url && (url.includes('.png') || url.includes('.PNG') || url.includes('.jpg') || url.includes('.JPG') || url.includes('.svg') || url.includes('.SVG'))) {
			return url;
		}
		return 'assets/images/no-image.jpg';
	}

});