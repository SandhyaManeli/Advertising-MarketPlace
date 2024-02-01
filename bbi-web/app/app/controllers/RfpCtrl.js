angular.module("bbManager")
.controller("RfpCtrl", 
function (
	$scope,
	$mdDialog, 
	Upload, 
	OwnerProductService, 
	OwnerLocationService, 
	$window, 
	$stateParams, 
	$state, 
	toastr
	) 
	{
	$scope.forms = {};
	$scope.unavailalbeDateRanges = [];
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
	
	$scope.getProductUnavailableDates = function (productId, ev) {
		// if (productId.type == "Bulletin") {
		OwnerProductService.getProductUnavailableDates(productId.id).then(function (dateRanges) {
			$scope.unavailalbeDateRanges = dateRanges;
			$(ev.target).parent().parent().find('input').trigger('click');
		});
	}
	$scope.customOptions = {};
	$scope.removeSelection = function () {
		$scope.customOptions.clearSelection();
	}
	$scope.$watch('user.dates', function () {
		for (item in $scope.user.dates) {
			var startDate = moment($scope.product.dates[item].startDate).format('MM-DD-YYYY')
			var endDate = moment($scope.product.dates[item].endDate).format('MM-DD-YYYY')
			var totalDays = moment(endDate).diff(startDate, 'days') + 1

			// $scope.totalPriceUserSelected = productPerDay * $scope.totalnumDays;
		}
	})

	$scope.rqstHrdngsOpts = {
		multipleDateRanges: true,
		opens: 'center',
		locale: {
			applyClass: 'btn-green',
			applyLabel: "Select Dates",
			fromLabel: "From",
			format: "DD-MMM-YY",
			toLabel: "To",
			cancelLabel: 'X',
			customRangeLabel: 'Custom range'
		},
		// isInvalidDate: function (dt) {
		// 	for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
		// 		if (moment(dt) >= moment($scope.unavailalbeDateRanges[i].booked_from) && moment(dt) <= moment($scope.unavailalbeDateRanges[i].booked_to)) {
		// 			return true;
		// 		}
		// 	}
		// 	if(moment(dt) < moment()){
		// 		return true;
		// 	}
		// },
		// isCustomDate: function (dt) {
		// 	for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
		// 		if (moment(dt) >= moment($scope.unavailalbeDateRanges[i].booked_from) && moment(dt) <= moment($scope.unavailalbeDateRanges[i].booked_to)) {
		// 			if (moment(dt).isSame(moment($scope.unavailalbeDateRanges[i].booked_from), 'day')) {
		// 				return ['red-blocked', 'left-radius'];
		// 			} else if (moment(dt).isSame(moment($scope.unavailalbeDateRanges[i].booked_to), 'day')) {
		// 				return ['red-blocked', 'right-radius'];
		// 			} else {
		// 				return 'red-blocked';
		// 			}
		// 		}
		// 	}
		// 	if(moment(dt) < moment()){
		// 		return 'gray-blocked';
		// 	}
		// },
		eventHandlers: {
			'apply.daterangepicker': function (ev, picker) {
				//selectedDateRanges = [];
				// console.log(ev);
			}
		}
	};




	$scope.searchableAreas = function (query) {
		if (query) {
			return OwnerLocationService.searchAreas(query.toLowerCase()).then(function (res) {
				return res;
			});
		}
	}

	$scope.selectedDMAs = [];
	$scope.saveDataDMA= {};
	$scope.saveDataDates= {};
	newArrayDmaF = [];
	newArrayDateF = [];


	$scope.selectSearchedArea = function () {
		if ($scope.areaObj == null) {
			toastr.error("No DMA Found");
		}
		$scope.user.city_name = $scope.areaObj
	}

	function convertDateToMMDDYYYY(dates,areaTimeZoneType) {
		console.log('prasad_sel_date_st',dates[0].startDate);
		if(!areaTimeZoneType) {
			areaTimeZoneType = Intl.DateTimeFormat().resolvedOptions().timeZone;
		}
		const startDate = parseInt(dates[0].startDate.$date.$numberLong);
		const endDate = parseInt(dates[0].endDate.$date.$numberLong);
		const splitStartDate = new Date(startDate).toLocaleString('en-GB',{ timeZone: areaTimeZoneType}).slice(0,10).split('/');
		[splitStartDate[0], splitStartDate[1]] = [splitStartDate[1], splitStartDate[0]];
		const splitEndDate = new Date(endDate).toLocaleString('en-GB', { timeZone: areaTimeZoneType}).slice(0,10).split('/');
		[splitEndDate[0], splitEndDate[1]] = [splitEndDate[1], splitEndDate[0]];
		return {
			startDate: splitStartDate.join('-'),
			endDate: splitEndDate.join('-')
		}
	}

	$scope.rfpNewUser = function (user) {
		// if($scope.areaObj == null){
		// if ((!$scope.selectedDMAs) || (!$scope.selectedDMAs.length)) {
		// 	toastr.error("No DMA Found1");
		// } else {
		// 	for (var item in user.date) {
		// 		user.date[item].endDate = moment(user.date[item].endDate).format('YYYY-MM-DD')
		// 		user.date[item].startDate = moment(user.date[item].startDate).format('YYYY-MM-DD')
		// 	};
		if ($scope.saveDataDMA == null) {
			console.log('prasad1',$scope.saveDataDMA)
			toastr.error("No DMA Found");
		} else {
			for (var items in $scope.saveDataDMA) {
				//newArrayDmaF.push($scope.saveDataDMA[items].id);
				var dmaId = $scope.saveDataDMA[items].id;
                if (newArrayDmaF.indexOf(dmaId) === -1) {
                    newArrayDmaF.push(dmaId);
                }
			}
			for (var item in $scope.saveDataDates) {
				if($scope.saveDataDates[item].length == 0){
					toastr.error("Please Select Dates");
					return;
				}
				const areaTimeZoneType = $scope.saveDataDMA[item].area_time_zone_type;

				if(areaTimeZoneType != null){
					const startDate = moment($scope.saveDataDates[item][0].startDate);   
					const offset = startDate.tz(areaTimeZoneType).format().slice(19,25); 
					
					const finalStartDate = moment($scope.saveDataDates[item][0].startDate).format("YYYY-MM-DD")
					  + `T00:00:00.000${offset}`;
					const finalEndDate = moment($scope.saveDataDates[item][0].endDate).format("YYYY-MM-DD") 
					  +  `T23:59:59.000${offset}`;
					$scope.saveDataDates[item][0].startDate = moment.utc(finalStartDate).format();
					$scope.saveDataDates[item][0].endDate = moment.utc(finalEndDate).format();
				}else{
					$scope.saveDataDates[item][0].startDate = moment($scope.saveDataDates[item][0].startDate).format('YYYY-MM-DD')
					$scope.saveDataDates[item][0].endDate = moment($scope.saveDataDates[item][0].endDate).format('YYYY-MM-DD')
				}
				user_f_date = $scope.saveDataDates[item][0].startDate+'::'+$scope.saveDataDates[item][0].endDate;
				newArrayDateF.push(user_f_date);
			}

			// product.type = product.type.name;
			// product.area = $scope.areaObj.id;
			Upload.upload({
				url: config.apiPath + '/rfp-user-campaign',
				data: {
					// startDate: $scope.user.date[0].startDate || null,
					// endDate: $scope.user.date[0].endDate,
					// area: $scope.selectedDMAs.map(x => x.id),	//$scope.areaObj.id,
					area: newArrayDmaF || null,
					date_ranges: newArrayDateF || null,
					producttype: $scope.user.producttype,
					due_date: $scope.user.due_date,
					campaign_name: $scope.user.campaign_name,
					demo: $scope.user.demo,
					instructions: $scope.user.instructions,
					budget: $scope.user.budget
				}
			}).then(function (result) {
				newArrayDmaF = [];
				newArrayDateF = [];
				if (result.data.status == 1) {
					$scope.resetProduct();
					toastr.success(result.data.message);
					$window.location.href = '/user-saved-campaigns';

				}
				else if (result.data.status == 0) {
					toastr.error(result.data.message);
				}
				//   $scope.hordinglistform.$setPristine();
				//   $scope.hordinglistform.$setUntouched();
			}, function (resp) {
			}, function (evt) {
				//   var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
			});
		}

	};
	
	$scope.getProductUnavailableDate = function (user, ev, index) {
		for (var items in $scope.saveDataDMA) {
			if(items == index){
				if($scope.saveDataDMA[items] != null){
					if($scope.saveDataDMA[items].id != ''){
						$(ev.target).parent().parent().find("input").trigger("click");
					} else {
						toastr.error(
							"Please select the DMA first before selecting the date range"
						);
					}
				} else {
					toastr.error(
						"Please select the DMA first before selecting the date range"
					);
				}
			}
		} 
		
	};
	
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
	$scope.resetProduct = function () {
		$scope.forms.registerUser.$setPristine();
		$scope.forms.registerUser.$setUntouched();
		document.getElementById("myDropdown").classList.toggle("show");
		$scope.user.producttype = 'Static';
		$scope.areaObj = null;
		$scope.user.campaign_name = '';
		$scope.user.dates = '';
		$scope.user.due_date = "";
		$scope.user.instructions = "";
		$scope.user.demo = "";
		$scope.user.budget = "";
		$mdDialog.hide();
	}
	$scope.dirtyFix = function () {
		$(".md-scroll-mask").css("z-index", "899");
		$(".md-select-menu-container").css("display", "show");
		$(".md-scroll-mask").click(function () {
			$(".md-scroll-mask").css("z-index", -1);
			$(".md-select-menu-container").css("display", "none");
		});
	}

	function debounce(func, timeout = 700){
		let timer;
		return (...args) => {
		  clearTimeout(timer);
		  timer = setTimeout(() => { func.apply(this, args); }, timeout);
		};
	  }

	$scope.searchTermAry = [
		{
			searchTerm: ''
		}
	  ];
	  $scope.searchTerms = [];
	  $scope.selectedIndex = '';

	  $scope.searchTermChanged = debounce((searchTerm) => keyUpHandler(searchTerm))

	$scope.addNewSearchField = function() {
	  $scope.searchTermAry.push({searchTerm: ''});
	}
	$scope.removeLastSearchField = function(index) {
		$("#div_plusClass").addClass("rfp_plus_minus");
		$scope.saveDataDMA = Object.keys($scope.saveDataDMA).map(key => $scope.saveDataDMA[key])
		$scope.saveDataDMA.splice(index,1);
		var arr_dma = [];
		Object.keys($scope.saveDataDMA).forEach(function(key)
		{
			arr_dma.push($scope.saveDataDMA[key]);
		});
		$scope.saveDataDMA =  arr_dma ;
		
		$scope.saveDataDates = Object.keys($scope.saveDataDates).map(key => $scope.saveDataDates[key])
		$scope.saveDataDates.splice(index,1);
		var arr_dates = [];
		Object.keys($scope.saveDataDates).forEach(function(key)
		{
			arr_dates.push($scope.saveDataDates[key]);
		});
		$scope.saveDataDates =  arr_dates ;
		
		$scope.searchTermAry.splice(index,1);
		$scope.searchTermChanged('');
	}

}
);