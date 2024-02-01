angular
	.module("bbManager")
	.controller(
		"RfpWithoutLoginCtrl",
		function (
			$scope,
			$mdDialog,
			Upload,
			UserService,
			OwnerLocationService,
			$window,
			UserService,
			toastr
		) {
			$scope.forms = {};
			$scope.unavailalbeDateRanges = [];

			/*$scope.getProductUnavailableDate = function (user, ev) {
				if ($scope.user.city_name) {
					UserService.getProductUnavailableDates(user.id).then(
						function (dateRanges) {
							$(".warn-text").text("");
							$scope.unavailalbeDateRanges = dateRanges;
							$(ev.target)
								.parents()
								.eq(3)
								.find("input")
								.trigger("click");
						}
					);
				} else {
					toastr.error(
						"Please select the DMA first before selecting the date range"
					);
				}
			}*/

			$scope.customOptions = {};
			$scope.removeSelection = function () {
				$scope.customOptions.clearSelection();
			};
			$scope.$watch("user.dates", function () {
				for (item in $scope.user.dates) {
					var startDate = moment(
						$scope.user.dates[item].startDate
					).format("MM-DD-YYYY");
					var endDate = moment(
						$scope.user.dates[item].endDate
					).format("MM-DD-YYYY");
					var totalDays = moment(endDate).diff(startDate, "days") + 1;

					// $scope.totalPriceUserSelected = productPerDay * $scope.totalnumDays;
				}
			});

			$scope.rqstHrdngsOpts = {
				multipleDateRanges: true,
				opens: "center",
				locale: {
					applyClass: "btn-green",
					applyLabel: "Select Dates",
					fromLabel: "From",
					format: "DD-MMM-YY",
					toLabel: "To",
					cancelLabel: "X",
					customRangeLabel: "Custom range",
				},

				eventHandlers: {
					"apply.daterangepicker": function (ev, picker) {
						//selectedDateRanges = [];
						// console.log(ev);
					},
				},
			};

			$scope.searchableAreas = function (query) {
				if (query) {
					return OwnerLocationService.searchArea(
						query.toLowerCase()
					).then(function (res) {
						return res;
					});
				}
			};

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

			// $scope.selectedDMAs = [];
			$scope.saveDataDMA= {};
			$scope.saveDataDates= {};
			$scope.newArrayDmaDelete = [];
			$scope.newArrayDateDelete = [];
			newArrayDmaF = [];
			newArrayDateF = [];
			$scope.rfpWithoutLogin = function (user) {
				//if($scope.areaObj == null){
				if ($scope.saveDataDMA == null) {
					toastr.error("No DMA Found");
				} 
				else {
					for (var items in $scope.saveDataDMA) {
						//newArrayDmaF.push($scope.saveDataDMA[items].id);
						$scope.newArrayDmaDelete = newArrayDmaF;
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
						$scope.newArrayDateDelete = newArrayDateF;
					}

					Upload.upload({
						url: config.apiPath + "/rfp-campaign-without-login",
						data: {
							//area: $scope.selectedDMAs.map((x) => x.id), //$scope.areaObj.id
							area: newArrayDmaF || null,
							date_ranges: newArrayDateF || null,
							due_date: $scope.user.due_date,
							user_email: $scope.user.user_email,
							producttype: $scope.user.producttype,
							campaign_name: $scope.user.campaign_name,
							demo: $scope.user.demo,
							instructions: $scope.user.instructions,
							budget: $scope.user.budget
						},
					}).then(
						function (result) {
							newArrayDmaF = [];
							newArrayDateF = [];
							if (result.data.status == 1) {
								$scope.resetProduct();
								toastr.success(result.data.message);
								// $window.history.back();
								$timeout(function () {
									$window.location.href = "/home";
								}, 2000);
							} else if (result.data.status == 0) {
								toastr.error(result.data.message);
							}
							//   $scope.hordinglistform.$setPristine();
							//   $scope.hordinglistform.$setUntouched();
						},
						function (resp) {},
						function (evt) {
							//   var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
						}
					);
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

			/*
			 * ============ Company Registration Ends ============
			 */

			$scope.close = function () {
				$mdDialog.hide();
			};
			$scope.resetProduct = function () {
				$scope.forms.registerUser.$setPristine();
				$scope.forms.registerUser.$setUntouched();
				document.getElementById("myDropdow").classList.toggle("show");
				$scope.user.producttype = "Static";
				$scope.areaObj = "";
				$scope.user.campaign_name = "";
				$scope.user.user_email = "";
				$scope.user.due_date = "";
				$scope.user.dates = "";
				$scope.user.instructions = "";
				$scope.user.demo = "";
				$scope.user.budget = "";
				$mdDialog.hide();
			};

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
				//debugger 
				$("#div_plusClass").addClass("rfp_plus_minus");
				//$scope.saveDataDMA.splice(index,1);
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
			$scope.dirtyFix = function () {
				$(".md-scroll-mask").css("z-index", "899");
				$(".md-select-menu-container").css("display", "show");
				$(".md-scroll-mask").click(function () {
					$(".md-scroll-mask").css("z-index", -1);
					$(".md-select-menu-container").css("display", "none");
				});
			}
		}
	);
