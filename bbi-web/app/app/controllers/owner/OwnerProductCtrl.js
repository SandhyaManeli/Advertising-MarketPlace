angular.module("bbManager").controller(
	"OwnerProductCtrl",
	function (
		$scope,
		$mdDialog,
		$mdSidenav,
		$stateParams,
		$rootScope,
		$window,
		MapService,
		OwnerProductService,
		$auth,
		OwnerLocationService,
		OwnerCampaignService,
		Upload,
		config,
		toastr,
		$state,
		$location,
		CampaignService
	) {
		$scope.unavailalbeDateRanges = [];
		$scope.isShowAvailable = false;
		$scope.item = {
			city_name: "",
		};

		// $scope.loadCalendar = false;

		/*===================
  | Sidenavs and popups
  ===================*/

		$scope.toggleRequestHoardingFormSidenav = function () {
			$mdSidenav("request-hoarding-sidenav").toggle();
		};

		$scope.openScreen = function (ev) {
			$mdDialog.show({
				templateUrl: "views/owner/requesthoardingadd.html",
				clickOutsideToClose: true,
			});
		};

		$scope.viewImage = function () {
			$mdDialog.show({
				templateUrl: "views/owner/view-image.html",
				fullscreen: $scope.customFullscreen,
				clickOutsideToClose: true,
			});
		};

		$scope.toggleShareProductSidenav = function () {
			$mdSidenav("shareProductSidenav").toggle();
		};

		$scope.toggleShortlistProductsSidenav = function () {
			$mdSidenav("shortlistedProductsSidenav").toggle();
		};

		$scope.toggleShareProductsSidenav = function () {
			$mdSidenav("shareProductsSidenav").toggle();
		};

		$scope.toggleOwnerAddCampaignSidenav = function () {
			$mdSidenav("ownerAddCampaignSidenav").toggle();
		};
		/*========================
  | Sidenavs and popups ends
  ========================*/

		$scope.showBlockDate = function () {
			$mdDialog.show({
				templateUrl: "views/map-calendar-popup.html",
				fullscreen: $scope.customFullscreen,
				clickOutsideToClose: true,
				controller: function ($scope) {
					$scope.closeMdDialog = function () {
						$mdDialog.hide();
					};
				},
			});
		};

		/*===================
  Form Age  ===================*/

		$scope.FromTo = [{ id: "From", name: "From" }];

		$scope.addNewFromTo = function () {
			var newItemNo = $scope.FromTo.length + 1;
			$scope.FromTo.push({
				id: "From" + newItemNo,
				name: "From ",
				id: "To" + newItemNo,
				name2: "To ",
			});
		};
		$scope.removeNewChoice = function (index) {
			var newItemNo = $scope.FromTo.length - 1;
			if (newItemNo !== 0) {
				// $scope.FromTo.pop();
				$scope.FromTo.splice(index, 1);
			}
		};
		$scope.showAddFromTo = function (from) {
			return from.id === $scope.FromTo[$scope.FromTo.length - 1].id;
		};

		/*===================
  Colipos  ===================*/
		$scope.Strengths = [{ id: "Strength 1", name: "Strength 1" }];
		$scope.addNewChoice = function () {
			var newItemNo = $scope.Strengths.length + 1;
			$scope.Strengths.push({ id: "Strength" + newItemNo });
		};
		$scope.removeStrength = function (index) {
			var newItemNo = $scope.Strengths.length - 1;
			if (newItemNo !== 0) {
				//$scope.Strengths.pop();
				$scope.Strengths.splice(index, 1);
			}
		};

	/*===================
  | Pagination
  ===================*/
		$scope.pagination = {};
		$scope.pagination.pageNo = 1;
		$scope.pagination.pageSize = 15;
		$scope.pagination.pageCount = 0;
		var pageLinks = 20;
		var lowest = 1;
		var highest = lowest + pageLinks - 1;
		function createPageLinks() {
			var mid = Math.ceil(pageLinks / 2);
			if ($scope.pagination.pageCount < $scope.pagination.pageSize) {
				lowest = 1;
			} else if (
				$scope.pagination.pageNo >= $scope.pagination.pageCount - mid &&
				$scope.pagination.pageNo <= $scope.pagination.pageCount
			) {
				lowest = $scope.pagination.pageCount - pageLinks;
			} else if (
				$scope.pagination.pageNo > 0 &&
				$scope.pagination.pageNo <= pageLinks / 2
			) {
				lowest = 1;
			} else {
				lowest = $scope.pagination.pageNo - mid + 1;
			}
			highest =
				$scope.pagination.pageCount < $scope.pagination.pageSize
					? $scope.pagination.pageCount
					: lowest + pageLinks;
			$scope.pagination.pageArray = _.range(lowest, highest + 1);
		}
		$scope.getRange = function (b, e) {
			$scope.pageRange = [];
			for (i = b + 1; i <= e; i++) {
				$scope.pageRange.push(i);
			}
			return $scope.pageRange;
		};
	/*===================
  | Pagination Ends
  ===================*/

		$scope.getProductByFormat = function (format) {
			$scope.format = format;
			var pageData = {};
			pageData.page_no = $scope.pagination.pageNo;
			pageData.page_size = $scope.pagination.pageSize;
			pageData.show_available = $scope.isShowAvailable;
			pageData.format = {name: format};
			OwnerProductService.getApprovedProductList(
				pageData
			).then(function (result) {
				$scope.productList = result.products;
				$scope.productList.map((product) => {
					const startDate = parseInt(product.from_date.$date.$numberLong);
          const endDate = parseInt(product.to_date.$date.$numberLong);
          const areaTimeZoneType = product.area_time_zone_type;
					if(areaTimeZoneType) {
						const splitStartDate = new Date(startDate)
							.toLocaleString('en-GB',{ timeZone: areaTimeZoneType}).slice(0,10).split('/');
						[splitStartDate[0], splitStartDate[1]] = [splitStartDate[1], splitStartDate[0]];
	
						const splitEndDate = new Date(endDate)
							.toLocaleString('en-GB', { timeZone: areaTimeZoneType}).slice(0,10).split('/');
						[splitEndDate[0], splitEndDate[1]] = [splitEndDate[1], splitEndDate[0]];
	
						product.from_date.$date.$numberLong = splitStartDate.join('-');
						product.to_date.$date.$numberLong = splitEndDate.join('-');
					}
        });
				// console.log($scope.productList);
				$scope.product.formType = $scope.ProductTypesFilter[0];
				$scope.pagination.pageCount = result.page_count;
				if ($window.innerWidth >= 420) {
					createPageLinks();
				} else {
					$scope.getRange(0, result.page_count);
				}
				// sortByRateCard("1");
			});
		};
		$scope.getBudget = function (price) {
			$scope.price = price;
			OwnerProductService.getApprovedProductList(
				$scope.pagination.pageNo,
				$scope.pagination.pageSize,
				format,
				price
			).then(function (result) {
				$scope.productList = result.products;
				console.log($scope.productList);
				$scope.pagination.pageCount = result.page_count;
				if ($window.innerWidth >= 420) {
					createPageLinks();
				} else {
					$scope.getRange(0, result.page_count);
				}
			});
		};
		$scope.clearOwnerProductFilter = function (product) {
			$scope.product = { budgetprice: "1" };
			$scope.getProductByFormat("All");
		};
		$scope.clearOwnerProductFilt = function (product) {
			// $scope.getApprovedProductList();
			$scope.product = { budgetprice: "1" };
			$scope.item.city_name = "";
			$scope.isShowAvailable = false;
			$scope.product.type = $scope.ProductTypes[0];
			$scope.product.formType = $scope.ProductTypesFilter[0];
			$scope.product.audited = "No";
			$scope.page_no = 1;
			$scope.page_size = 15;
			$scope.getProductByFormat("All");
		};
		$scope.applymethod = function (product, dateType) {
			$scope.pagination.pageNo = 1;
			$scope.pagination.pageSize = 15;
			var data = {};
			var pageNo = $scope.pagination.pageNo;
			var pageSize = $scope.pagination.pageSize;
			var format = product.formType;
			var budget = product.budgetprice;
			var start_date = product.start_date;
			var end_date = product.end_date;
			var product_name = product.product_name;
			if (dateType == "startDate") {
				//to clear end date field
				end_date = null;
				$scope.product.end_date = "";
				// $scope.product.formType = $scope.ProductTypesFilter[0];
			}
			if (!format) {
				format = "";
			}
			if (!budget) {
				budget = "";
			}
			if (!product_name) {
				product_name = "";
			}
			if (
				pageNo ||
				pageSize ||
				format ||
				budget ||
				start_date ||
				end_date ||
				product_name
			) {
				data.page_no = pageNo;
				data.page_size = pageSize;
				data.format = format;
				data.budget = budget;
				data.start_date = start_date;
				data.end_date = end_date;
				data.product_name = product_name;
				data.dma = $scope.item?.city_name;
				data.show_available = $scope.isShowAvailable;
			}
			OwnerProductService.getApprovedProductList(data).then(function (
				result
			) {
				$scope.productList = result.products;
				$scope.productList.map((product) => {
					const startDate = parseInt(product.from_date.$date.$numberLong);
          const endDate = parseInt(product.to_date.$date.$numberLong);
          const areaTimeZoneType = product.area_time_zone_type;
					if(areaTimeZoneType) {
						const splitStartDate = new Date(startDate)
							.toLocaleString('en-GB',{ timeZone: areaTimeZoneType}).slice(0,10).split('/');
						[splitStartDate[0], splitStartDate[1]] = [splitStartDate[1], splitStartDate[0]];
	
						const splitEndDate = new Date(endDate)
							.toLocaleString('en-GB', { timeZone: areaTimeZoneType}).slice(0,10).split('/');
						[splitEndDate[0], splitEndDate[1]] = [splitEndDate[1], splitEndDate[0]];
	
						product.from_date.$date.$numberLong = splitStartDate.join('-');
						product.to_date.$date.$numberLong = splitEndDate.join('-');
					}
        });
				$scope.pagination.pageCount = result.page_count;
				if ($window.innerWidth >= 420) {
					createPageLinks();
				} else {
					$scope.getRange(0, result.page_count);
				}
				// sortByRateCard(product.budgetprice);
			});
		};

		$scope.getNumber = function (str) {
			var num = 0;
			str = str.trim();
			if (str == "") num = 0;
			else {
				if (str.indexOf(",") > -1) str = str.replace(",", "");
				num = parseFloat(str);
			}
			return num;
		};
		function sortByRateCard(type) {
			if (type == "1") {
				$scope.productList.sort(function (a, b) {
					if (
						$scope.getNumber(a.rateCard) <
						$scope.getNumber(b.rateCard)
					)
						return 1;
					else if (
						$scope.getNumber(a.rateCard) >
						$scope.getNumber(b.rateCard)
					)
						return -1;
					else return 0;
				});
			} else {
				$scope.productList.sort(function (a, b) {
					if (
						$scope.getNumber(a.rateCard) >
						$scope.getNumber(b.rateCard)
					)
						return 1;
					else if (
						$scope.getNumber(a.rateCard) <
						$scope.getNumber(b.rateCard)
					)
						return -1;
					else return 0;
				});
			}
		}

		$scope.ranges = {
			selectedDateRanges: [],
		};
		$scope.customOptions = {};
		$scope.removeSelection = function () {
			$scope.customOptions.clearSelection();
		};
		$scope.$watch("product.dates", function () {
			//$scope.totalPriceUserSelected = 0;
			$scope.totalnumDays = 0;
			$scope.noOffourweeks = 0;
			//var productPerDay = $scope.product.rateCard / 28;
			for (item in $scope.product.dates) {
				var startDate = moment(
					$scope.product.dates[item].startDate
				).format("MM-DD-YYYY");
				var endDate = moment($scope.product.dates[item].endDate).format(
					"MM-DD-YYYY"
				);
				var totalDays = moment(endDate).diff(startDate, "days") + 1;
				$scope.totalnumDays = $scope.totalnumDays + totalDays;
				$scope.noOffourweeks = $scope.totalnumDays / 28;
				// $scope.totalPriceUserSelected = productPerDay * $scope.totalnumDays;
			}
			if (
				$scope.product.firstImpression != null ||
				$scope.product.firstImpression != undefined
			) {
				$scope.newFirstImpression =
					Math.floor($scope.product.firstImpression / 7) *
					$scope.totalnumDays;
			}
			if (
				$scope.product.secondImpression != null ||
				$scope.product.secondImpression != undefined
			) {
				$scope.newSecond =
					Math.floor($scope.product.secondImpression / 7) *
					$scope.totalnumDays;
			}
			if (
				$scope.product.thirdImpression != null ||
				$scope.product.thirdImpression != undefined
			) {
				$scope.newThree =
					Math.floor($scope.product.thirdImpression / 7) *
					$scope.totalnumDays;
			}
			if (
				$scope.product.forthImpression != null ||
				$scope.product.forthImpression != undefined
			) {
				$scope.newFour =
					Math.floor($scope.product.forthImpression / 7) *
					$scope.totalnumDays;
			}
		});
		$scope.$watch("product.firstImpression", function () {
			$scope.newFirstImpression =
				Math.floor($scope.product.firstImpression / 7) *
				$scope.totalnumDays;
		});
		$scope.$watch("product.secondImpression", function () {
			$scope.newSecond =
				Math.floor($scope.product.secondImpression / 7) *
				$scope.totalnumDays;
		});
		$scope.$watch("product.thirdImpression", function () {
			$scope.newThree =
				Math.floor($scope.product.thirdImpression / 7) *
				$scope.totalnumDays;
		});
		$scope.$watch("product.forthImpression", function () {
			$scope.newFour =
				Math.floor($scope.product.forthImpression / 7) *
				$scope.totalnumDays;
		});
		$scope.$watch("editRequestedhordings.firstImpression", function () {
			$scope.newFirst =
				Math.floor($scope.editRequestedhordings.firstImpression / 7) *
				$scope.totalnumDays;
		});
		$scope.$watch("editRequestedhordings.secondImpression", function () {
			$scope.editRequestedhordings.editNotCpm = true;
			$scope.newFirsts =
				Math.floor($scope.editRequestedhordings.secondImpression / 7) *
				$scope.totalnumDays;
		});
		$scope.$watch("editRequestedhordings.thirdImpression", function () {
			$scope.newFirstst =
				Math.floor($scope.editRequestedhordings.thirdImpression / 7) *
				$scope.totalnumDays;
		});
		$scope.$watch("editRequestedhordings.forthImpression", function () {
			$scope.newFirstsf =
				Math.floor($scope.editRequestedhordings.forthImpression / 7) *
				$scope.totalnumDays;
		});

		$scope.$watch("editRequestedhordings.dates", function () {
			//$scope.totalPriceUserSelected = 0;
			$scope.totalnumDays = 0;
			//var productPerDay = $scope.product.rateCard / 28;
			for (item in $scope.editRequestedhordings.dates) {
				var startDate = moment(
					$scope.editRequestedhordings.dates[item].startDate
				).format("MM-DD-YYYY");
				var endDate = moment(
					$scope.editRequestedhordings.dates[item].endDate
				).format("MM-DD-YYYY");
				var totalDays = moment(endDate).diff(startDate, "days") + 1;
				$scope.totalnumDays = $scope.totalnumDays + totalDays;
				$scope.noOffourweeks = $scope.totalnumDays / 28;
				// $scope.totalPriceUserSelected = productPerDay * $scope.totalnumDays;
			}
			if (
				$scope.editRequestedhordings.firstImpression != null ||
				$scope.editRequestedhordings.firstImpression != undefined
			) {
				$scope.newFirst =
					Math.floor(
						$scope.editRequestedhordings.firstImpression / 7
					) * $scope.totalnumDays;
			}
			if (
				$scope.editRequestedhordings.secondImpression != null ||
				$scope.editRequestedhordings.secondImpression != undefined
			) {
				$scope.newFirsts =
					Math.floor(
						$scope.editRequestedhordings.secondImpression / 7
					) * $scope.totalnumDays;
			}
			if (
				$scope.editRequestedhordings.thirdImpression != null ||
				$scope.editRequestedhordings.thirdImpression != undefined
			) {
				$scope.newFirstst =
					Math.floor(
						$scope.editRequestedhordings.thirdImpression / 7
					) * $scope.totalnumDays;
			}
			if (
				$scope.editRequestedhordings.forthImpression != null ||
				$scope.editRequestedhordings.forthImpression != undefined
			) {
				$scope.newFirstsf =
					Math.floor(
						$scope.editRequestedhordings.forthImpression / 7
					) * $scope.totalnumDays;
			}
		});
		$scope.$watch("editRequestedhordings.rateCard", function () {
			$scope.totalnumDays = 0;
			//var productPerDay = $scope.product.rateCard / 28;

			var startDate = $scope.fromTime;
			var endDate = $scope.endTime;
			var totalDays = moment(endDate).diff(startDate, "days") + 1;
			$scope.totalnumDays = $scope.totalnumDays + totalDays;
			$scope.noOffourweeks = $scope.totalnumDays / 28;
			// $scope.totalPriceUserSelected = productPerDay * $scope.totalnumDays;

			if (
				$scope.editRequestedhordings.firstImpression != null ||
				$scope.editRequestedhordings.firstImpression != undefined
			) {
				$scope.newFirst =
					Math.floor(
						$scope.editRequestedhordings.firstImpression / 7
					) * $scope.totalnumDays;
			}
			if (
				$scope.editRequestedhordings.secondImpression != null ||
				$scope.editRequestedhordings.secondImpression != undefined
			) {
				$scope.newFirsts =
					Math.floor(
						$scope.editRequestedhordings.secondImpression / 7
					) * $scope.totalnumDays;
			}
			if (
				$scope.editRequestedhordings.thirdImpression != null ||
				$scope.editRequestedhordings.thirdImpression != undefined
			) {
				$scope.newFirstst =
					Math.floor(
						$scope.editRequestedhordings.thirdImpression / 7
					) * $scope.totalnumDays;
			}
			if (
				$scope.editRequestedhordings.forthImpression != null ||
				$scope.editRequestedhordings.forthImpression != undefined
			) {
				$scope.newFirstsf =
					Math.floor(
						$scope.editRequestedhordings.forthImpression / 7
					) * $scope.totalnumDays;
			}
		});

		$scope.$watch("areaObj", function () {
			if ($scope.areaObj.area_time_zone_type) {
				$scope.selectedTimezone = $scope.areaObj.area_time_zone_type;
			}
			if ($scope.areaObj.city_id == "5f1a633aec7e5") {
				($scope.product.address = $scope.areaObj.name),
					($scope.product.city = $scope.areaObj.city_name),
					($scope.product.state = $scope.areaObj.state_name),
					($scope.product.zipcode = $scope.areaObj.pincode),
					($scope.product.lat = $scope.areaObj.lat),
					($scope.product.lng = $scope.areaObj.lng);
			} else {
				($scope.product.address = ""),
					($scope.product.city = ""),
					($scope.product.state = ""),
					($scope.product.zipcode = ""),
					($scope.product.lat = ""),
					($scope.product.lng = "");
			}
		});

		$scope.$watch("areaObj", function () {
			if ($scope.areaObj.city_id == "5f1a633aec7e5") {
				($scope.editRequestedhordings.address = $scope.areaObj.name),
					($scope.editRequestedhordings.city =
						$scope.areaObj.city_name),
					($scope.editRequestedhordings.state =
						$scope.areaObj.state_name),
					($scope.editRequestedhordings.zipcode =
						$scope.areaObj.pincode),
					($scope.editRequestedhordings.lat = $scope.areaObj.lat),
					($scope.editRequestedhordings.lng = $scope.areaObj.lng);
			} else if ($scope.flag != 1) {
				($scope.editRequestedhordings.address = ""),
					($scope.editRequestedhordings.city = ""),
					($scope.editRequestedhordings.state = ""),
					($scope.editRequestedhordings.zipcode = ""),
					($scope.editRequestedhordings.lat = ""),
					($scope.editRequestedhordings.lng = "");
			}
			$scope.flag++;
		});

		/*================================
       | Multi date range picker options
       ================================*/
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
			isInvalidDate: function (dt) {
				for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
					if (
						moment(dt) >=
							moment(
								$scope.unavailalbeDateRanges[i].booked_from
							) &&
						moment(dt) <=
							moment($scope.unavailalbeDateRanges[i].booked_to)
					) {
						return true;
					}
				}
				let today;
        if ($scope.selectedTimezone) {
          today = moment().tz($scope.selectedTimezone).format(
            "MM/DD/YYYY"
          );
        } else {
          today = moment().format("MM/DD/YYYY");
        } 
        var isAfter = moment(today).isAfter((moment(dt).format("MM/DD/YYYY")));
        if (isAfter) {
					return true;
				}
			},
			isCustomDate: function (dt) {
				for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
					if (
						moment(dt) >=
							moment(
								$scope.unavailalbeDateRanges[i].booked_from
							) &&
						moment(dt) <=
							moment($scope.unavailalbeDateRanges[i].booked_to)
					) {
						if (
							moment(dt).isSame(
								moment(
									$scope.unavailalbeDateRanges[i].booked_from
								),
								"day"
							)
						) {
							return ["red-blocked", "left-radius"];
						} else if (
							moment(dt).isSame(
								moment(
									$scope.unavailalbeDateRanges[i].booked_to
								),
								"day"
							)
						) {
							return ["red-blocked", "right-radius"];
						} else {
							return "red-blocked";
						}
					}
				}
				if (moment(dt) < moment()) {
					return "gray-blocked";
				  }
			},
			eventHandlers: {
				"apply.daterangepicker": function (ev, picker) {
					//selectedDateRanges = [];
					// console.log(ev);
				},
			},
		};
		/*====================================
     | Multi date range picker options end
     ====================================*/
		$scope.rqstEditedHrdngsOpts = {
			multipleDateRanges: true,
			locale: {
				applyClass: "btn-green",
				applyLabel: "Change Dates",
				fromLabel: "From",
				format: "MMM-DD-YY",
				toLabel: "To",
				cancelLabel: "X",
				customRangeLabel: "Custom range",
			},
			isInvalidDate: function (dt) {
				for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
					if (
						moment(dt) >=
							moment(
								$scope.unavailalbeDateRanges[i].booked_from
							) &&
						moment(dt) <=
							moment($scope.unavailalbeDateRanges[i].booked_to)
					) {
						return true;
					}
				}
				let today;
        if ($scope.selectedTimezone) {
          today = moment().tz($scope.selectedTimezone).format(
            "MM/DD/YYYY"
          );
        } else {
          today = moment().format("MM/DD/YYYY");
        } 
		var isAfter = moment(today).isAfter((moment(dt).format("MM/DD/YYYY")));
        if (isAfter) {
					return true;
				}
			},
			isCustomDate: function (dt) {
				for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
					if (
						moment(dt) >=
							moment(
								$scope.unavailalbeDateRanges[i].booked_from
							) &&
						moment(dt) <=
							moment($scope.unavailalbeDateRanges[i].booked_to)
					) {
						if (
							moment(dt).isSame(
								moment(
									$scope.unavailalbeDateRanges[i].booked_from
								),
								"day"
							)
						) {
							return ["red-blocked", "left-radius"];
						} else if (
							moment(dt).isSame(
								moment(
									$scope.unavailalbeDateRanges[i].booked_to
								),
								"day"
							)
						) {
							return ["red-blocked", "right-radius"];
						} else {
							return "red-blocked";
						}
					}
				}
				if (moment(dt) < moment()) {
					return "gray-blocked";
				}
			},
			eventHandlers: {
				"apply.daterangepicker": function (ev, picker) {
					//selectedDateRanges = [];
				},
			},
		};
		$scope.addProdOpts = {
			multipleDateRanges: true,
			locale: {
				applyClass: "btn-green",
				applyLabel: "Book Now",
				fromLabel: "From",
				format: "DD-MMM-YY",
				toLabel: "To",
				cancelLabel: "X",
				customRangeLabel: "Custom range",
			},
			isInvalidDate: function (dt) {
				for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
					if (
						moment(dt) >=
							moment(
								$scope.unavailalbeDateRanges[i].booked_from
							) &&
						moment(dt) <=
							moment($scope.unavailalbeDateRanges[i].booked_to)
					) {
						return true;
					}
				}
				let today;
        if ($scope.selectedTimezone) {
          today = moment().tz($scope.selectedTimezone).format(
            "MM/DD/YYYY"
          );
        } else {
          today = moment().format("MM/DD/YYYY");
        } 
        if (moment(dt).format('MM/DD/YYYY') < today) {
					return true;
				}
			},
			isCustomDate: function (dt) {
				for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
					if (
						moment(dt) >=
							moment(
								$scope.unavailalbeDateRanges[i].booked_from
							) &&
						moment(dt) <=
							moment($scope.unavailalbeDateRanges[i].booked_to)
					) {
						if (
							moment(dt).isSame(
								moment(
									$scope.unavailalbeDateRanges[i].booked_from
								),
								"day"
							)
						) {
							return ["red-blocked", "left-radius"];
						} else if (
							moment(dt).isSame(
								moment(
									$scope.unavailalbeDateRanges[i].booked_to
								),
								"day"
							)
						) {
							return ["red-blocked", "right-radius"];
						} else {
							return "red-blocked";
						}
					}
				}
				if (moment(dt) < moment()) {
					return "gray-blocked";
				}
			},
			eventHandlers: {
				"apply.daterangepicker": function (ev, picker) {
					//selectedDateRanges = [];
				},
			},
		};
		$scope.inventoryListOpts = {
			multipleDateRanges: true,
			opens: "center",
			locale: {
				applyClass: "btn-green",
				applyLabel: "Book Now",
				fromLabel: "From",
				format: "DD-MMM-YY",
				toLabel: "To",
				cancelLabel: "Cancel",
				customRangeLabel: "Custom range",
			},
			isInvalidDate: function (dt) {
				for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
					if (
						moment(dt) >=
							moment(
								$scope.unavailalbeDateRanges[i].booked_from
							) &&
						moment(dt) <=
							moment($scope.unavailalbeDateRanges[i].booked_to)
					) {
						return true;
					}
				}
			},
			isCustomDate: function (dt) {
				for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
					if (
						moment(dt) >=
							moment(
								$scope.unavailalbeDateRanges[i].booked_from
							) &&
						moment(dt) <=
							moment($scope.unavailalbeDateRanges[i].booked_to)
					) {
						if (
							moment(dt).isSame(
								moment(
									$scope.unavailalbeDateRanges[i].booked_from
								),
								"day"
							)
						) {
							return ["red-blocked", "left-radius"];
						} else if (
							moment(dt).isSame(
								moment(
									$scope.unavailalbeDateRanges[i].booked_to
								),
								"day"
							)
						) {
							return ["red-blocked", "right-radius"];
						} else {
							return "red-blocked";
						}
					}
				}
			},
		};
		/*====================================
  | Multi date range picker options end
  ====================================*/
		// SHORT-LIST
		$scope.shortlistSelected = function (
			productId,
			selectedDateRanges,
			ev
		) {
			var sendObj = {
				product_id: productId,
				dates: selectedDateRanges,
			};
			MapService.shortListProduct(sendObj).then(function (response) {
				$mdDialog.show(
					$mdDialog
						.alert()
						.parent(angular.element(document.querySelector("body")))
						.clickOutsideToClose(true)
						.title("Cart Product")
						.textContent(response.message)
						.ariaLabel("shortlist-success")
						.ok("Confirmed!")
						.targetEvent(ev),
					$mdSidenav("productDetails").close()
				);
				getShortListedProducts();
				$mdSidenav("productDetails").close();
			});
		};
		function getShortListedProducts() {
			MapService.getshortListProduct(
				JSON.parse(localStorage.loggedInUser).id
			).then(function (response) {
				shortListedProductsLength = response.length;
				$scope.shortListedProducts = response;
				$rootScope.$emit(
					"shortListedProducts",
					shortListedProductsLength
				);
			});
		}
		getShortListedProducts();
		// $scope.getProductUnavailableDates = function (productId, ev) {
		//   MapService.getProductUnavailableDates(productId).then(function (dateRanges) {
		//     $scope.unavailalbeDateRanges = dateRanges;
		//     $(ev.target).parents().eq(3).find('input').trigger('click');
		//   });
		// }
		// SHORT-LIST ENDs
		// Save-camp
		$scope.toggleExistingCampaignSidenav = function () {
			$scope.showSaveCampaignPopup = !$scope.showSaveCampaignPopup;
		};
		// Save-camp-end

		var getFormatList = function () {
			OwnerProductService.getFormatList().then(function (result) {
				$scope.formatList = result;
			});
		};
		getFormatList();

		var getCountryList = function () {
			OwnerLocationService.getCountries().then(function (result) {
				$scope.countryList = result;
			});
		};
		getCountryList();


		$scope.getApprovedProductList = function () {
			$rootScope.http2_loading = true;
			var pageData = {};
			pageData.page_no = $scope.pagination.pageNo;
			pageData.page_size = $scope.pagination.pageSize;
			pageData.show_available = $scope.isShowAvailable;
			pageData.dma = $scope.item?.city_name;
			pageData.product_name = $scope.product?.product_name;
			OwnerProductService.getApprovedProductList(
				pageData
			).then(function (result) {
				$scope.productList = result.products;
				$scope.productList.map((product) => {
					const startDate = parseInt(product.from_date.$date.$numberLong);
          const endDate = parseInt(product.to_date.$date.$numberLong);
          const areaTimeZoneType = product.area_time_zone_type;
					if (areaTimeZoneType) {
						const splitStartDate = new Date(startDate)
							.toLocaleString('en-GB',{ timeZone: areaTimeZoneType}).slice(0,10).split('/');
						[splitStartDate[0], splitStartDate[1]] = [splitStartDate[1], splitStartDate[0]];
	
						const splitEndDate = new Date(endDate)
							.toLocaleString('en-GB', { timeZone: areaTimeZoneType}).slice(0,10).split('/');
						[splitEndDate[0], splitEndDate[1]] = [splitEndDate[1], splitEndDate[0]];
	
						product.from_date.$date.$numberLong = splitStartDate.join('-');
						product.to_date.$date.$numberLong = splitEndDate.join('-');
					}
        })
				$scope.pagination.pageCount = result.page_count;
				if ($window.innerWidth >= 420) {
					createPageLinks();
				} else {
					$scope.getRange(0, result.page_count);
				}
				// if ($scope.sortType == "Asc") {
				// 	$scope.sortAsc(
				// 		$scope.upArrowColour,
				// 		$scope.upArrowColour == "siteNo" ? "string" : "number"
				// 	);
				// } else if ($scope.sortType == "Dsc") {
				// 	$scope.sortDsc(
				// 		$scope.downArrowColour,
				// 		$scope.downArrowColour == "siteNo" ? "string" : "number"
				// 	);
				// } else {
				// 	sortByRateCard("1"); //default sorting
				// }
				// $scope.productList.sort((prod1, prod2) => {
        //   return (prod1.from_date.$date.$numberLong - prod2.from_date.$date.$numberLong);
        // });

				// $scope.productList = [
				// 	...$scope.productList.filter((prod) =>  prod.to_date.$date.$numberLong >= Date.now()),
				// 	...$scope.productList.filter((prod) =>  prod.to_date.$date.$numberLong < Date.now()),
				// ]
				$rootScope.http2_loading = false;
			});
		};

		//clone product Details

		$scope.cloneProductDetails = function (product) {
			$scope.editRequestedhordings = {};
			$scope.editRequestedhordings.type = product.type;
			$scope.editRequestedhordings.area_name = product.area_name;
			$scope.editRequestedhordings.lighting = product.lighting;
			$scope.editRequestedhordings.title = product.title;
			$scope.editRequestedhordings.address = product.address;
			$scope.editRequestedhordings.city = product.city;
			$scope.editRequestedhordings.state = product.state;
			$scope.editRequestedhordings.audited = product.audited;
			$scope.editRequestedhordings.sellerId = product.sellerId;
			$scope.editRequestedhordings.mediahhi = product.mediahhi;
			$scope.editRequestedhordings.rateCard = product.rateCard;
			$scope.editRequestedhordings.installCost = product.installCost;
			$scope.editRequestedhordings.taxPercentage = product.taxPercentage;
			$scope.editRequestedhordings.negotiatedCost =
				product.negotiatedCost;
			$scope.editRequestedhordings.productioncost =
				product.productioncost;
			$scope.editRequestedhordings.firstImpression =
				product.firstImpression;
			$scope.editRequestedhordings.secondImpression =
				product.secondImpression;
			$scope.editRequestedhordings.thirdImpression =
				product.thirdImpression;
			$scope.editRequestedhordings.forthImpression =
				product.forthImpression;
			($scope.editRequestedhordings.firstcpm = product.cpm1),
				($scope.editRequestedhordings.cpm = product.cpm),
				($scope.editRequestedhordings.thirdcpm = product.cpm3),
				($scope.editRequestedhordings.forthcpm = product.cpm4),
				($scope.editRequestedhordings.cancellation_policy =
					product.cancellation_policy);
			$scope.editRequestedhordings.cancellation_terms =
				product.cancellation_terms;
			$scope.editRequestedhordings.notes = product.notes;
			$scope.editRequestedhordings.Comments = product.Comments;
			$scope.editRequestedhordings.lat = product.lat;
			$scope.editRequestedhordings.lng = product.lng;
			$scope.editRequestedhordings.locationDesc = product.locationDesc;
			$scope.editRequestedhordings.staticMotion = product.staticMotion;
			$scope.editRequestedhordings.sound = product.sound;
			$scope.editRequestedhordings.impressions = product.impressions;
			$scope.editRequestedhordings.zipcode = product.zipcode;
			$scope.editRequestedhordings.height = product.height;
			$scope.editRequestedhordings.width = product.width;
			$scope.editRequestedhordings.imgdirection = product.imgdirection;
			$scope.editRequestedhordings.minimumbooking =
				product.minimumbooking;
			$scope.editRequestedhordings.cancellation = product.cancellation;
			$scope.editRequestedhordings.direction = product.direction;
			$scope.editRequestedhordings.price = product.default_price;
			$scope.editRequestedhordings.default_price = product.default_price;
			$scope.editRequestedhordings.ethnicity = product.ethnicity;
			$scope.editRequestedhordings.hour = product.hour;
			$scope.editRequestedhordings.flipsloops = product.flipsloops;
			$scope.editRequestedhordings.slots = product.slots;
			$scope.editRequestedhordings.area = product.area;
			$scope.editRequestedhordings.dates = product.dates;
		};

		$scope.filterOwnerProductsWithDates = function (dateFilter) {
			OwnerProductService.getApprovedProductListByDates(
				moment(dateFilter.start_date).toISOString(),
				moment(dateFilter.end_date).toISOString()
			).then(function (result) {
				$scope.productList = result.products;
				$scope.pagination.pageCount = result.page_count;
				if ($window.innerWidth >= 420) {
					createPageLinks();
				} else {
					$scope.getRange(0, result.page_count);
				}
			});
		};

		var getRequestedProductList = function () {
			OwnerProductService.getRequestedProductList(
				$scope.pagination.pageNo,
				$scope.pagination.pageSize
			).then(function (result) {
				$scope.requestedProductList = result.products;
				$scope.pagination.pageCount = result.page_count;
				if ($window.innerWidth >= 420) {
					createPageLinks();
				} else {
					$scope.getRange(0, result.page_count);
				}
			});
		};

		$scope.getStateList = function (product) {
			OwnerLocationService.getStates($scope.product.country).then(
				function (result) {
					$scope.stateList = result;
				}
			);
		};
		$scope.getCityList = function () {
			OwnerLocationService.getCities($scope.product.state).then(function (
				result
			) {
				$scope.cityList = result;
			});
		};
		$scope.getAreaList = function () {
			OwnerLocationService.getAreas($scope.product.city).then(function (
				result
			) {
				$scope.areaList = result;
			});
		};

		$scope.searchableAreas = function (query) {
			return OwnerLocationService.searchAreas(query.toLowerCase()).then(
				function (res) {
					return res;
				}
			);
		};
		$scope.productdetails = [
			{
				id: 1,
				price: "60000",
			},
		];

		$scope.requestedAddProduct = function (product) {};

		$scope.editUtterance = function (data) {
			data.edit = true;
		};
		$scope.save = function (data) {
			data.edit = false;
		};

		$scope.VendorName =
			JSON.parse(localStorage.loggedInUser).firstName +
			" " +
			JSON.parse(localStorage.loggedInUser).lastName;
		$scope.SellerName =
			JSON.parse(localStorage.loggedInUser).firstName +
			" " +
			JSON.parse(localStorage.loggedInUser).lastName;

		/*=====================
  | Product Section
  =====================*/
		$scope.product = {
			budgetprice: "1",
		};
		$scope.editRequestedhordings = {};
		$scope.addProductType = [
			// { name: "All" },
			{ name: "Bulletin" },
			{ name: "Digital" },
			{ name: "Transit Digital" },
		];
		$scope.ProductTypes = [
			//  { name: "All" },
			{ name: "Static" },
			{ name: "Digital" },
			{ name: "Digital/Static" },
			{ name: "Media" },
		];
		$scope.download = [
			{ name: "Select product type" },
			{ name: "Static" },
			{ name: "Digital" },
			{ name: "Digital/Static" },
			{ name: "Media" },
		];
		$scope.ProductTypesFilter = [
			{ name: "All" },
			{ name: "Static" },
			{ name: "Digital" },
			{ name: "Digital/Static" },
			{ name: "Media" },
		];
		$scope.typeOptions = [
			{ name: "General", value: "general" },
			{ name: "Hispanic", value: "hispanic" },
		];
		$scope.product = {
			ethnicity: $scope.typeOptions[0].value,
			budgetprice: "1",
		};
		$scope.editRequestedhordings = {
			ethnicity: $scope.typeOptions[0].value,
		};
		$scope.edittypeOptions = [
			{ name: "General", value: "general" },
			{ name: "Hispanic", value: "hispanic" },
		];
		//$scope.editRequestedhordings = {ethnicity : $scope.typeOptions[0].value};
		$scope.Staticresult = true;
		$scope.newAgeResult = false;
		$scope.DigitalResult = false;
		$scope.DigitalStaticResult = false;
		$scope.product.type = $scope.ProductTypes[0];
		$scope.product.formType = $scope.ProductTypesFilter[0];
		$scope.getdetails = function () {
			if ($scope.product.type.name == "Static") {
				$scope.Staticresult = true;
				$scope.newAgeResult = false;
				$scope.DigitalResult = false;
				$scope.DigitalStaticResult = false;
			} else if ($scope.product.type.name == "Digital") {
				$scope.DigitalResult = true;
				$scope.newAgeResult = false;
				$scope.Staticresult = false;
				$scope.DigitalStaticResult = false;
			} else if ($scope.product.type.name == "Media") {
				$scope.newAgeResult = true;
				$scope.Staticresult = false;
				$scope.DigitalResult = false;
				$scope.DigitalStaticResult = false;
			} else if ($scope.product.type.name == "Digital/Static") {
				$scope.newAgeResult = false;
				$scope.Staticresult = false;
				$scope.DigitalResult = false;
				$scope.DigitalStaticResult = true;
			} else {
				$scope.Staticresult = false;
			}
		};

		$scope.files = {};

		// $scope.requestAddProduct = function (product) {
		//   console.log('Product',product)
		//   product.type = product.type.name;
		//   product.area = $scope.areaObj.id;
		//   Upload.upload({
		//     url: config.apiPath + '/save-product-details',
		//     data: {
		//       image: $scope.files.image,
		//       symbol: $scope.files.symbol,
		//       product: $scope.product
		//     }
		//   }).then(function (result) {
		//     if (result.data.status == "1") {
		//       // getRequestedProductList();
		//       $scope.product = null;
		//       // document.getElementById("myDropdown").classList.toggle("show");
		//       toastr.success(result.data.message);
		//       $state.reload();
		//     }
		//     else if (result.data.status == 0) {
		//       $scope.requestProductErrors = result.data.message;
		//       toastr.error(result.data.message);
		//     }
		//     $scope.hordinglistform.$setPristine();
		//     $scope.hordinglistform.$setUntouched();
		//   }, function (resp) {
		//   }, function (evt) {
		//     var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
		//   });
		// };
		$scope.selectSearchedArea = function () {
			if ($scope.areaObj == null) {
				toastr.error("No DMA Found");
			}
			$scope.editRequestedhordings.city_name = $scope.areaObj;
		};

		$scope.resizeTextImg = false;
		$scope.$watch("files.image", function (ctrl) {
			var fileUpload = ctrl;
			var invalidImageFormats = [];
			$scope.imageFileTypes = ["image/jpg","image/jpeg"];
			if (typeof fileUpload[0] != "undefined") {
				$scope.resizeTextImg = false;
				var reader = new FileReader();
				reader.readAsDataURL(fileUpload[0]);
				reader.onload = function (e) {
					var image = new Image();
					image.src = e.target.result;
					image.onload = function () {
						var height = this.height;
						var width = this.width;
						if (width >= 1280 && height >= 960) {
							$scope.resizeTextImg = false;
							// alert("At least you can upload a 1280 px *960 px size.");
							// return false;
							if ($scope.files != null && $scope.files.image.length !== 0) {
								angular.forEach($scope.files.image, function (imageFile) {
									var tempObj = {};
									if (
										$scope.imageFileTypes.indexOf(imageFile.type) === -1
									) {
										//toastr.error("Please select image."); 
										toastr.error("Please select JPG/JPEG image.");
										$scope.files.image = "";
										tempObj.image = imageFile;
										invalidImageFormats.push(tempObj);
									}
								});
							}
						} else {
							$scope.resizeTextImg = true;
							$scope.files.image = "";
							toastr.error(
								"Uploaded image has valid Width 1280px and Height 960px."
							);
						}
					};
				};
			}
		});
		//radio on cahnge stripe per cahnges
		$scope.productAmpService = function (value) {
			$scope.product.billingYes =
				$scope.product.billing == "yes" ? "yes" : "";
			$scope.product.billingNo =
				$scope.product.billing == "no" ? "no" : "";
			//$scope.product.servicingYes = $scope.product.servicing == 'yes'?"yes":"",
			//$scope.product.servicingNo = $scope.product.servicing == 'no'?"no":"",
			if (value == "yes") {
				if ($scope.product.billingYes == "yes") {
					$scope.product.stripe_percent = "15";
				} else {
					$scope.product.stripe_percent = "10";
				}
			} else {
				if ($scope.product.billingYes == "yes") {
					$scope.product.stripe_percent = "10";
				} else {
					$scope.product.stripe_percent = "5";
				}
			}
		};
		$scope.productAmpBill = function (value) {
			$scope.product.servicingYes =
				$scope.product.servicing == "yes" ? "yes" : "";
			$scope.product.servicingNo =
				$scope.product.servicing == "no" ? "no" : "";
			if (value == "yes") {
				if ($scope.product.servicingYes == "yes") {
					$scope.product.stripe_percent = "15";
				} else {
					$scope.product.stripe_percent = "10";
				}
			} else {
				if ($scope.product.servicingYes == "yes") {
					$scope.product.stripe_percent = "10";
				} else {
					$scope.product.stripe_percent = "5";
				}
			}
		};
		//clone radio
		//radio on cahnge stripe per cahnges
		$scope.cloneAmpService = function (value) {
			$scope.editRequestedhordings.billingYes =
				$scope.editRequestedhordings.billing == "yes" ? "yes" : "";
			$scope.editRequestedhordings.billingNo =
				$scope.editRequestedhordings.billing == "no" ? "no" : "";
			if (value == "yes") {
				if ($scope.editRequestedhordings.billingYes == "yes") {
					$scope.editRequestedhordings.stripe_percent = "15";
				} else {
					$scope.editRequestedhordings.stripe_percent = "10";
				}
			} else {
				if ($scope.editRequestedhordings.billingYes == "yes") {
					$scope.editRequestedhordings.stripe_percent = "10";
				} else {
					$scope.editRequestedhordings.stripe_percent = "5";
				}
			}
		};
		$scope.cloneAmpBill = function (value) {
			$scope.editRequestedhordings.servicingYes =
				$scope.editRequestedhordings.servicing == "yes" ? "yes" : "";
			$scope.editRequestedhordings.servicingNo =
				$scope.editRequestedhordings.servicing == "no" ? "no" : "";
			if (value == "yes") {
				if ($scope.editRequestedhordings.servicingYes == "yes") {
					$scope.editRequestedhordings.stripe_percent = "15";
				} else {
					$scope.editRequestedhordings.stripe_percent = "10";
				}
			} else {
				if ($scope.editRequestedhordings.servicingYes == "yes") {
					$scope.editRequestedhordings.stripe_percent = "10";
				} else {
					$scope.editRequestedhordings.stripe_percent = "5";
				}
			}
		};

		//Latitude and Longitude Validation
		$scope.isValidLatLong = {
			lat: true,
			lng: true,
		};

		$scope.validateLat = function (lat) {
			const regEx =
				/^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/;
			if (isNaN(lat)) {
				$scope.isValidLatLong.lat = false;
			} else {
				$scope.isValidLatLong.lat = regEx.test(lat);
			}
		};

		$scope.validateLng = function (lng) {
			const regEx = new RegExp(
				"^(\\+|-)?((\\d((\\.)|\\.\\d{1,6})?)|(0*?\\d\\d((\\.)|\\.\\d{1,6})?)|(0*?1[0-7]\\d((\\.)|\\.\\d{1,6})?)|(0*?180((\\.)|\\.0{1,6})?))$"
			);
			if (isNaN(lng)) {
				$scope.isValidLatLong.lng = false;
			} else {
				$scope.isValidLatLong.lng = regEx.test(lng);
			}
		};
		//End of Latitude and Longitude Validation

		$scope.requestAddProduct = function (product) {
			if (!$scope.checked) {
				//validating the selected upload images
				var invalidImageFormats = [];
				$scope.imageFileTypes = [
					"image/jpg",
					"image/jpeg",
					//"image/png",
					//"image/gif",
					//"image/svg+xml",
				];
				if ($scope.files != null && $scope.files.image.length !== 0) {
					angular.forEach($scope.files.image, function (imageFile) {
						var tempObj = {};
						if (
							$scope.imageFileTypes.indexOf(imageFile.type) === -1
						) {
							//toastr.error("Please select image.");
							toastr.error("Please select JPG/JPEG image.");
							tempObj.image = imageFile;
							invalidImageFormats.push(tempObj);
						}
					});
				}
			}

			if (product.dates.length == 0) {
				toastr.error("Please Select Dates");
				return;
			}
			if ($scope.areaObj == null) {
				toastr.error("No DMA Found");
			} else {
				//save product only if there are no invalid image formats
				if (
					invalidImageFormats == undefined ||
					invalidImageFormats.length == 0
				) {
					debugger;
					for (var item in product.dates) {
						if($scope.areaObj.area_time_zone_type != null){
						const startDate = moment(item.startDate);   
            const offset = startDate.tz($scope.areaObj.area_time_zone_type).format().slice(19,25);
						
						const finalStartDate = moment(product.dates[item].startDate).format("YYYY-MM-DD")
              + `T00:00:00.000${offset}`;
            const finalEndDate = moment(product.dates[item].endDate).format("YYYY-MM-DD") 
              +  `T23:59:59.000${offset}`;
						product.dates[item].startDate = moment.utc(finalStartDate).format();
            product.dates[item].endDate = moment.utc(finalEndDate).format();
						}else{
							product.dates[item].endDate = moment(product.dates[item].endDate).format('YYYY-MM-DD')
							product.dates[item].startDate = moment(product.dates[item].startDate).format('YYYY-MM-DD')
						}
					}
					// product.type = product.type.name;
					// product.area = $scope.areaObj.id;
					var payload = {
						url: config.apiPath + "/save-product-details",
						data: {
							image: $scope.files.image,
							title: $scope.product.title,
							height:
								$scope.product.height +
								" " +
								$scope.productType,
							width:
								$scope.product.weight +
								" " +
								$scope.productType,
							address: $scope.product.address,
							city: $scope.product.city,
							minimumdays: $scope.product.minimumdays,
							length: $scope.product.length,
							direction: $scope.product.direction,
							impressions: $scope.product.impressions,
							zipcode: $scope.product.zipcode,
							state: $scope.product.state,
							audited: $scope.product.audited,
							network: $scope.product.network,
							nationloc: $scope.product.nationloc,
							stripe_percent: $scope.product.stripe_percent,
							daypart: $scope.product.daypart,
							reach: $scope.product.reach,
							genre: $scope.product.genre,
							vendor: $scope.VendorName,
							sellerId: $scope.product.sellerId,
							fix: $scope.product.fix,
							mediahhi: $scope.product.mediahhi,
							firstDay: $scope.product.firstDay,
							lastDay: $scope.product.lastDay,
							weekPeriod: $scope.noOffourweeks,
							rateCard: $scope.product.rateCard,
							installCost: $scope.product.installCost,
							tax_percentage:$scope.product.tax_percentage,
							price: $scope.product.price,
							negotiatedCost: $scope.product.negotiatedCost,
							productioncost: $scope.product.productioncost,
							unitQty: $scope.product.unitQty,
							// billingYes : $scope.product.billingYes,
							// billingNo : $scope.product.billingNo,
							billingYes:
								$scope.product.billing == "yes" ? "yes" : "",
							billingNo:
								$scope.product.billing == "no" ? "no" : "",
							servicingYes:
								$scope.product.servicing == "yes" ? "yes" : "",
							servicingNo:
								$scope.product.servicing == "no" ? "no" : "",
							// servicingYes : $scope.product.servicingYes,
							// servicingNo : $scope.product.servicingNo,
							firstImpression: $scope.product.firstImpression,
							secondImpression: $scope.product.secondImpression,
							thirdImpression: $scope.product.thirdImpression,
							forthImpression: $scope.product.forthImpression,
							firstcpm:
								((($scope.product.rateCard / 28) *
									$scope.totalnumDays) /
									$scope.newFirstImpression) *
								1000,
							cpm:
								((($scope.product.rateCard / 28) *
									$scope.totalnumDays) /
									$scope.newSecond) *
								1000,
							thirdcpm:
								((($scope.product.rateCard / 28) *
									$scope.totalnumDays) /
									$scope.newThree) *
								1000,
							forthcpm:
								((($scope.product.rateCard / 28) *
									$scope.totalnumDays) /
									$scope.newFour) *
								1000,
							cancellation_policy:
								$scope.product.cancellation_policy,
							cancellation_terms:
								$scope.product.cancellation_terms,
							imgdirection: $scope.product.imgdirection,
							notes: $scope.product.notes,
							description: $scope.product.description,
							Comments: $scope.product.Comments,
							lat: $scope.product.lat,
							lng: $scope.product.lng,
							lighting: $scope.product.lighting,
							placement: $scope.product.placement,
							locationDesc: $scope.product.locationDesc,
							// costperpoint: $scope.product.costperpoint,
							title: $scope.product.title,
							fliplength: $scope.product.fliplength,
							// looplength : $scope.product.looplength,
							spotLength: $scope.product.spotLength,
							ageloopLength: $scope.product.ageloopLength,
							locationDesc: $scope.product.locationDesc,
							staticMotion: $scope.product.staticMotion,
							sound: $scope.product.sound,
							file_type: $scope.product.file_type,
							product_newMedia: $scope.product.product_newMedia,
							medium: $scope.product.medium,
							area: $scope.areaObj.id,
							type: $scope.product.type.name,
							//date:$scope.product.dates
							dates: $scope.product.dates,
							//product: JSON.parse(angular.toJson($scope.product))
							default_image_status: $scope.checked,
						},
					};
					Upload.upload(payload).then(
						function (result) {
							if (result.data.status == "1") {
								$scope.product = null;
								toastr.success(result.data.message);
								$state.reload();
							} else if (result.data.status == 0) {
								$scope.requestProductErrors =
									result.data.message;
								toastr.error(result.data.message);
							}
							$scope.hordinglistform.$setPristine();
							$scope.hordinglistform.$setUntouched();
						},
						function (resp) {},
						function (evt) {
							var progressPercentage = parseInt(
								(100.0 * evt.loaded) / evt.total
							);
						}
					);
				}
			}
			// product.DemographicsAge = formdata;
			// product.Strengths = Strengths;
			// for(element in product){
			//   if(element.DemographicsAge){
			//     for(var i = 0; i<element.DemographicsAge.length; i++ ){
			//       if(element.DemographicsAge[i] == $$hashKey){
			//         delete element.DemographicsAge[i]
			//       }
			//     }
			//   }
			// }
		};
		$scope.isChecked = false;
		//upload functionality without product type
		$scope.excelfiles = {};
		$scope.uploadExcelFile = function (excelfiles) {
			var payload = $auth.getPayload();
			var userData = payload.user;
			var userMongoData = payload.userMongo;
			var user = localStorage.getItem("loggedInUser");
			var parsedData = JSON.parse(user);
			var user_type = parsedData.user_type;
			var user_role = parsedData.user_role;
			var data = {
				image: $scope.excelfiles.file,
				email: JSON.parse(localStorage.loggedInUser).email,
				client_name: JSON.parse(localStorage.loggedInUser).firstName,
				clientID: userData.client_id,
				user_id: userMongoData.id,
				created_at: userData.created_at,
				updated_at: userData.updated_at,
				user_type: userMongoData.user_type,
				client_mongo_id: userMongoData.client_mongo_id,
				user_role: userMongoData.user_role,
				seller_name: "",
				subseller_name: "",
			};
			Upload.upload({
				url: config.apiPath + "/save-bulk-product-details",
				data: data,
			}).then(function (result) {
				if (result.data.status == 1) {
					toastr.success(result.data.message);
					addbulkProduct();
				} else {
					toastr.error(result.data.message);
				}
			});
		};
		//
		$scope.addbulkProduct = function () {
			$scope.displayErrorMsg = false;
			$scope.downloadProductType = $scope.download[0];
			$scope.uploadProductType = $scope.download[0];
			$scope.showUploadForm = false;
			$scope.staticContent = false;
			$scope.digitalContent = false;
			$scope.staticDigitalContent = false;
			$scope.mediaContent = false;
			document.getElementById("bulkUpload").classList.toggle("show");
		};
		var getOwnerProductDetails = function (productId) {
			OwnerProductService.getOwnerProductDetails(productId).then(
				function (result) {
					$scope.productDetails = result;
					$scope.runningCampaignDetails = _.filter(
						result.campaigns,
						function (c) {
							return c.status == 6;
						}
					)[0];
					$scope.nonRunningCampaigns = _.filter(
						result.campaigns,
						function (c) {
							return c.status != 6;
						}
					);
				}
			);
		};
		function addnewProduct() {
			document.getElementById("myDropdown").classList.toggle("show");
			// document.getElementById("stripe").innerText = '5%';
		}

		// validatations for the tax percantage
		$scope.product.tax_percentage = 0;
		$scope.percantage = 100;

		$scope.prevQuantity = 0;
		$scope.percantageHandlers = function () {
		  $scope.prevQuantity = $scope.product.tax_percentage;
		}
	
		$scope.percantageChangeHandlers = function() {
		  if ($scope.product.tax_percentage > $scope.percantage || $scope.product.tax_percentage < 0) {
			toastr.warning(`Please enter percantage in between 1 to 100`);        
			$scope.product.tax_percentage = $scope.prevQuantity; 
		  }
		}

		$scope.resetProduct = function () {
			$scope.isValidLatLong.lat = true;
			$scope.isValidLatLong.lng = true;
			$scope.hordinglistform.$setPristine();
			$scope.hordinglistform.$setUntouched();
			document.getElementById("myDropdown").classList.toggle("show");
			//  console.log($scope.ProductTypes);
			$scope.product.title = "";
			$scope.product.type = $scope.ProductTypes[0];
			$scope.areaObj = null;
			$scope.files.image = "";
			$scope.product.stripe_percent = "5";
			$scope.product.dates = "";
			$scope.product.title = "";
			$scope.product.address = "";
			$scope.product.city = "";
			$scope.product.state = "";
			$scope.product.zipcode = "";
			$scope.product.height = "";
			$scope.product.weight = "";
			$scope.product.audited = "No";
			$scope.product.sellerId = "";
			$scope.product.mediahhi = "";
			$scope.product.rateCard = "";
			($scope.product.minimumdays = ""),
				($scope.product.length = ""),
				($scope.product.network = ""),
				($scope.product.nationloc = ""),
				($scope.product.reach = ""),
				($scope.product.genre = ""),
				// $scope.product.costperpoint = '',
				($scope.product.installCost = "");
			$scope.product.negotiatedCost = "";
			$scope.product.productioncost = "";
			$scope.product.unitQty = "";
			$scope.product.taxPercentage ="";
			$scope.product.billingYes = "";
			$scope.product.billingNo = "";
			$scope.product.servicingYes = "";
			$scope.product.servicingNo = "";
			$scope.product.firstImpression = "";
			$scope.product.secondImpression = "";
			$scope.product.thirdImpression = "";
			$scope.product.forthImpression = "";
			$scope.product.imgdirection = "";
			$scope.product.cancellation_policy = "";
			$scope.product.cancellation_terms = "";
			$scope.product.notes = "";
			$scope.product.description = "";
			$scope.product.fix = "";
			$scope.product.direction = "";
			$scope.product.lat = "";
			$scope.product.lng = "";
			$scope.product.lighting = "";
			$scope.product.placement = "";
			$scope.product.Comments = "";
			$scope.product.fliplength = "";
			$scope.product.looplength = "";
			$scope.product.spotLength = "";
			$scope.product.ageloopLength = "";
			$scope.product_newMedia = "";
			$scope.product.medium = "";
			$scope.product.product = "";
			$scope.product.fileType = "";
			$scope.product.locationDesc = "";
			$scope.product.staticMotion = "";
			$scope.product.sound = "";
			$scope.product.type = $scope.ProductTypes[0];
		};

		$scope.viewProductImage = function (image) {
			var imagePath = config.serverUrl + image;
			$mdDialog.show({
				locals: { src: imagePath },
				templateUrl: "views/image-popup-large.html",
				preserveScope: true,
				scope: $scope,
				fullscreen: $scope.customFullscreen,
				clickOutsideToClose: true,
				controller: function ($scope, src) {
					$scope.img_src = src;
				},
				resolve: { 
          imageCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load('./controllers/ImageCtrl.js');
          }],
        }
			});
		};
		$scope.closeDialog = function () {
			$mdDialog.hide();
		};

		function getShortlistedProductsByOwner() {
			OwnerProductService.getShortlistedProductsByOwner().then(function (
				result
			) {
				$scope.shortlistedProducts = result;
			});
		}

		$scope.shortlistProductByOwner = function (productId) {
			OwnerProductService.shortListProductByOwner(productId).then(
				function (result) {
					if (result.status == 1) {
						getShortlistedProductsByOwner();
						_.map($scope.productList, (p) => {
							if (p.id == productId) {
								p.shortlisted = true;
								return p;
							}
						});
						toastr.success(result.message);
					} else {
						toastr.error(result.message);
					}
				}
			);
		};

		$scope.deleteShortlistedByOwner = function (productId) {
			OwnerProductService.deletedShortListedByOwner(productId).then(
				function (result) {
					if (result.status == 1) {
						getShortlistedProductsByOwner();
						_.map($scope.productList, (p) => {
							if (p.id == productId) {
								p.shortlisted = false;
								return p;
							}
						});
						toastr.success(result.message);
					} else {
						toastr.error(result.message);
					}
				}
			);
		};

		$scope.shareShortlistedProductsByOwner = function (recipientObj) {
			OwnerProductService.shareShortlistedProductsByOwner(
				recipientObj
			).then(function (result) {
				if (result.status == 1) {
					toastr.success(result.message);
				} else {
					toastr.error(result.message);
				}
			});
		};

		$scope.getProductUnavailableDatesEdit = function (productId, ev) {
			var productId = $stateParams.id;
			OwnerProductService.getProductUnavailableDates(productId).then(
				function (dateRanges) {
					$scope.unavailalbeDateRanges = dateRanges;
					$(ev.target)
						.parent()
						.parent()
						.find("input")
						.trigger("click");
					productDatesCalculator();
				}
			);
			// if(Type == 'Bulletin'){
			// }else{
			// var productId = $stateParams.id;
			// OwnerProductService.getProductUnavailableDates(productId).then(function (dateRanges) {
			//   $scope.unavailalbeDateRanges = dateRanges;
			//   productDatesDigitalCalculator()
			// $(ev.target).parent().parent().find('input').trigger('click');
			// });
			//}
		};
		$scope.getProductUnavailableDatesCloned = function (ev) {
			$scope.unavailalbeDateRanges = [];
			$(ev.target).parent().parent().find("input").trigger("click");
		};
		$scope.getProductUnavailableDates = function (productId, ev) {
			// if (productId.type == "Bulletin") {
			if ($scope.areaObj) {
				OwnerProductService.getProductUnavailableDates(productId.id).then(
					function (dateRanges) {
						$scope.unavailalbeDateRanges = dateRanges;
						$(ev.target)
							.parent()
							.parent()
							.find("input")
							.trigger("click");
					}
				);
			} else {
        toastr.error("Please select the DMA first before selecting the date range");
			}
			// } else if (productId.type == "Digital" || productId.type == "Transit Digital") {
			//   OwnerProductService.getProductDigitalUnavailableDates(productId.id).then(function (blockedDatesAndSlots) {
			//     $scope.unavailalbeDateRanges = [];
			//     blockedDatesAndSlots.forEach((item) => {
			//       if (item.booked_slots >= productId.slots) {
			//         $scope.unavailalbeDateRanges.push(item);
			//       }
			//     })
			//     $(ev.target).parent().parent().find('input').trigger('click');
			//   })
			// }
			// else {
			//   $scope.unavailalbeDateRanges = [];
			//   $(ev.target).parent().parent().find('input').trigger('click');
			// }
		};
		/*=====================
  | Product Section Ends
  =====================*/

      	  // validatations for the tax percantage
			$scope.editRequestedhordings.tax_percentage = 0;
			$scope.percantage = 100;
	
			$scope.prevQuantity = 0;
			$scope.percantageHandler = function () {
			  $scope.prevQuantity = $scope.editRequestedhordings.tax_percentage;
			}
		
			$scope.percantageChangeHandler = function() {
			  if ($scope.editRequestedhordings.tax_percentage > $scope.percantage || $scope.editRequestedhordings.tax_percentage < 0) {
				toastr.warning(`Please enter percantage in between 1 to 100`);        
				$scope.editRequestedhordings.tax_percentage = $scope.prevQuantity; 
			  }
			}
	

		//create new product with editable cloned product
		$scope.newProductCloned = function (editRequestedhordings) {
			// if(editRequestedhordings.dates.length == 0) {
			//   toastr.error("Please Select Dates");
			//   return;
			// }
			if (!$scope.checked) {
				var invalidImageFormat = [];
				$scope.imageFileType = [
					"image/jpg",
					"image/jpeg",
					//"image/png",
					//"image/gif",
					//"image/svg+xml",
				];
				if (
					$scope.files != null &&
					$scope.files &&
					$scope.files.image &&
					$scope.files.image.length !== 0
				) {
					angular.forEach($scope.files.image, function (imageFile) {
						var tempObj = {};
						if (
							$scope.imageFileType.indexOf(imageFile.type) === -1
						) {
							//toastr.error("Please select image.");
							toastr.error("Please select JPG/JPEG image.");
							tempObj.image = imageFile;
							invalidImageFormat.push(tempObj);
						}
					});
				}
			}

			if ($scope.editRequestedhordings.city_name == null) {
				toastr.error("No DMA Found");
			} else {
				//editRequestedhordings.area = $scope.areaObj.id;
				editRequestedhordings.id = $stateParams.id;
				if (
					invalidImageFormat == undefined ||
					invalidImageFormat.length == 0
				) {
					$scope.ranges.selectedDateRanges.map(function (item) {
						item.startDate = moment(item.startDate).format(
							"YYYY-MM-DD"
						);
						item.endDate = moment(item.endDate).format(
							"YYYY-MM-DD"
						);
					});
					if($scope.editRequestedhordings.dates) {
					$scope.editRequestedhordings.dates.map((item) => {
						const startDate = moment(item.startDate);   
            const offset = startDate.tz($scope.editRequestedhordings.area_time_zone_type).format().slice(19,25);

						const finalStartDate = moment(item.startDate).format("YYYY-MM-DD")
              + `T00:00:00.000${offset}`;
            const finalEndDate = moment(item.endDate).format("YYYY-MM-DD") 
              +  `T23:59:59.000${offset}`;
            item.startDate = moment.utc(finalStartDate).format();
            item.endDate = moment.utc(finalEndDate).format();
					})
				}
					// editRequestedhordings.dates = $scope.ranges.selectedDateRanges;

					// editRequestedhordings.dates = $scope.editRequestedhordings.dates;
					//checkbox
					$scope.editRequestedhordings.billingYes = $scope
						.editRequestedhordings.billing
						? $scope.editRequestedhordings.billing == "yes"
							? "yes"
							: ""
						: $scope.editRequestedhordings.billingYes;
					$scope.editRequestedhordings.billingNo = $scope
						.editRequestedhordings.billing
						? $scope.editRequestedhordings.billing == "no"
							? "no"
							: ""
						: $scope.editRequestedhordings.billingNo;
					$scope.editRequestedhordings.servicingYes = $scope
						.editRequestedhordings.servicing
						? $scope.editRequestedhordings.servicing == "yes"
							? "yes"
							: ""
						: $scope.editRequestedhordings.servicingYes;
					$scope.editRequestedhordings.servicingNo = $scope
						.editRequestedhordings.servicing
						? $scope.editRequestedhordings.servicing == "no"
							? "no"
							: ""
						: $scope.editRequestedhordings.servicingNo;
					if (
						$scope.editRequestedhordings.billingYes == "" &&
						editRequestedhordings.servicingYes == ""
					) {
						$scope.editRequestedhordings.stripe_percent = "5";
					} else if (
						($scope.editRequestedhordings.billingYes == "yes" &&
							editRequestedhordings.servicingNo == "no") ||
						($scope.editRequestedhordings.billingNo == "" &&
							editRequestedhordings.servicingYes == "")
					) {
						$scope.editRequestedhordings.stripe_percent = "10";
					} else if (
						($scope.editRequestedhordings.billingYes == "" &&
							editRequestedhordings.servicingNo == "") ||
						($scope.editRequestedhordings.billingNo == "no" &&
							editRequestedhordings.servicingYes == "yes")
					) {
						$scope.editRequestedhordings.stripe_percent = "10";
					} else if (
						($scope.editRequestedhordings.billingYes == "yes" &&
							editRequestedhordings.servicingNo == "no") ||
						($scope.editRequestedhordings.billingNo == "" &&
							editRequestedhordings.servicingYes == "")
					) {
						$scope.editRequestedhordings.stripe_percent = "10";
					} else if (
						($scope.editRequestedhordings.billingYes == "" &&
							editRequestedhordings.servicingNo == "no") ||
						($scope.editRequestedhordings.billingNo == "no" &&
							editRequestedhordings.servicingYes == "")
					) {
						$scope.editRequestedhordings.stripe_percent = "10";
					} else if (
						$scope.editRequestedhordings.billingYes == "yes" &&
						editRequestedhordings.servicingYes == "yes"
					) {
						$scope.editRequestedhordings.stripe_percent = "15";
					} else if (
						($scope.editRequestedhordings.billingYes == "yes" &&
							editRequestedhordings.servicingNo == "no") ||
						($scope.editRequestedhordings.billingNo == "no" &&
							editRequestedhordings.servicingYes == "yes")
					) {
						$scope.editRequestedhordings.stripe_percent = "10";
					} else {
						$scope.editRequestedhordings.stripe_percent = "5";
					}
					Upload.upload({
						url: config.apiPath + "/clone-product-details",
						data: {
							image: $scope.files.image,
							// cancellation : $scope.adminProductEdit.cancellation,
							// title : $scope.adminProductEdit.title,
							id: $scope.editRequestedhordings.id,
							height: $scope.editRequestedhordings.height,
							width: $scope.editRequestedhordings.width,

							// venue : $scope.editRequestedhordings.venue,
							address: $scope.editRequestedhordings.address,
							city: $scope.editRequestedhordings.city,
							city_name: $scope.editRequestedhordings.city_name,
							state_name: $scope.editRequestedhordings.state_name,
							direction: $scope.editRequestedhordings.direction,
							impressions:
								$scope.editRequestedhordings.impressions,
							zipcode: $scope.editRequestedhordings.zipcode,
							state: $scope.editRequestedhordings.state,
							audited: $scope.editRequestedhordings.audited,
							vendor: $scope.VendorName,
							stripe_percent:
								$scope.editRequestedhordings.stripe_percent,
							minimumdays:
								$scope.editRequestedhordings.minimumdays,
							network: $scope.editRequestedhordings.network,
							nationloc: $scope.editRequestedhordings.nationloc,
							daypart: $scope.editRequestedhordings.daypart,
							reach: $scope.editRequestedhordings.reach,
							genre: $scope.editRequestedhordings.genre,
							// costperpoint: $scope.editRequestedhordings.costperpoint,
							length: $scope.editRequestedhordings.length,
							sellerId: $scope.editRequestedhordings.sellerId,
							fix: $scope.editRequestedhordings.fix,
							mediahhi: $scope.editRequestedhordings.mediahhi,
							firstDay: $scope.editRequestedhordings.firstDay,
							description:
								$scope.editRequestedhordings.description,
							lastDay: $scope.editRequestedhordings.lastDay,
							weekPeriod:
								$scope.noOffourweeks == 0
									? $scope.editRequestedhordings.weekPeriod
									: $scope.noOffourweeks,
							rateCard: $scope.editRequestedhordings.rateCard,
							installCost:
								$scope.editRequestedhordings.installCost,
							// buses : $scope.editRequestedhordings.buses,
							price: $scope.editRequestedhordings.price,
							negotiatedCost:
								$scope.editRequestedhordings.negotiatedCost,
							productioncost:
								$scope.editRequestedhordings.productioncost,
							unitQty: $scope.editRequestedhordings.unitQty,
							tax_percentage:$scope.editRequestedhordings.tax_percentage,
							billingYes: $scope.editRequestedhordings.billing
								? $scope.editRequestedhordings.billing == "yes"
									? "yes"
									: ""
								: $scope.editRequestedhordings.billingYes,
							billingNo: $scope.editRequestedhordings.billing
								? $scope.editRequestedhordings.billing == "no"
									? "no"
									: ""
								: $scope.editRequestedhordings.billingNo,
							servicingYes: $scope.editRequestedhordings.servicing
								? $scope.editRequestedhordings.servicing ==
								  "yes"
									? "yes"
									: ""
								: $scope.editRequestedhordings.servicingYes,
							servicingNo: $scope.editRequestedhordings.servicing
								? $scope.editRequestedhordings.servicing == "no"
									? "no"
									: ""
								: $scope.editRequestedhordings.servicingNo,
							firstImpression:
								$scope.editRequestedhordings.firstImpression,
							secondImpression:
								$scope.editRequestedhordings.secondImpression,
							thirdImpression:
								$scope.editRequestedhordings.thirdImpression,
							forthImpression:
								$scope.editRequestedhordings.forthImpression,
							firstcpm:
								$scope.newFirst == 0
									? $scope.editRequestedhordings.firstcpm
									: ((($scope.editRequestedhordings.rateCard /
											28) *
											$scope.totalnumDays) /
											$scope.newFirst) *
									  1000,
							cpm:
								$scope.newFirsts == 0
									? $scope.editRequestedhordings.cpm
									: ((($scope.editRequestedhordings.rateCard /
											28) *
											$scope.totalnumDays) /
											$scope.newFirsts) *
									  1000,
							thirdcpm:
								$scope.newFirstst == 0
									? $scope.editRequestedhordings.thirdcpm
									: ((($scope.editRequestedhordings.rateCard /
											28) *
											$scope.totalnumDays) /
											$scope.newFirstst) *
									  1000,
							forthcpm:
								$scope.newFirstsf == 0
									? $scope.editRequestedhordings.forthcpm
									: ((($scope.editRequestedhordings.rateCard /
											28) *
											$scope.totalnumDays) /
											$scope.newFirstsf) *
									  1000,
							cancellation_policy:
								$scope.editRequestedhordings
									.cancellation_policy,
							cancellation_terms:
								$scope.editRequestedhordings.cancellation_terms,
							notes: $scope.editRequestedhordings.notes,
							description:
								$scope.editRequestedhordings.description,
							Comments: $scope.editRequestedhordings.Comments,
							lat: $scope.editRequestedhordings.lat,
							lng: $scope.editRequestedhordings.lng,
							lighting: $scope.editRequestedhordings.lighting,
							imgdirection:
								$scope.editRequestedhordings.imgdirection,
							placement: $scope.editRequestedhordings.placement,
							locationDesc:
								$scope.editRequestedhordings.locationDesc,
							title: $scope.editRequestedhordings.title,
							fliplength: $scope.editRequestedhordings.fliplength,
							looplength: $scope.editRequestedhordings.looplength,
							spotLength: $scope.editRequestedhordings.spotLength,
							ageloopLength:
								$scope.editRequestedhordings.ageloopLength,
							product_newMedia:
								$scope.editRequestedhordings.product_newMedia,
							locationDesc:
								$scope.editRequestedhordings.locationDesc,
							staticMotion:
								$scope.editRequestedhordings.staticMotion,
							sound: $scope.editRequestedhordings.sound,
							file_type: $scope.editRequestedhordings.file_type,

							// product_newAge:$scope.editRequestedhordings.product_newAge,
							medium: $scope.editRequestedhordings.medium,
							area: $scope.areaObj.id,
							type: $scope.editRequestedhordings.type.name,
							editNotCpm: false,
							//date:$scope.editRequestedhordings.dates
							dates: $scope.editRequestedhordings.dates,
							// editRequestedhordings: JSON.parse(angular.toJson($scope.editRequestedhordings))
							default_image_status: $scope.checked,
							tax_percentage: $scope.editRequestedhordings.tax_percentage,

						},
					}).then(
						function (result) {
							if (result.data.status == "1") {
								getRequestedProductList();
								toastr.success(result.data.message);
								if (
									$rootScope.currStateName ==
									"admin.cloneproduct-details"
								) {
									$window.location.href =
										"/admin/hoarding-list";
								} else {
									$window.location.href =
										"/owner/demo---landmark-ooh/hoarding-list";
								}

								$scope.removeSelection();
							} else if (result.data.status == 0) {
								$scope.requestProductErrors =
									result.data.message;
								toastr.error(result.data.message);
							}
							$scope.hordinglistform.$setPristine();
							$scope.hordinglistform.$setUntouched();
						},
						function (resp) {},
						function (evt) {
							var progressPercentage = parseInt(
								(100.0 * evt.loaded) / evt.total
							);
						}
					);
				}
			}
		};

			// validatations for the tax percantage
			$scope.editRequestedhordings.tax_percentage = 0;
			$scope.percantage = 100;
	
			$scope.prevQuantity = 0;
			$scope.percantageHandler = function () {
			  $scope.prevQuantity = $scope.editRequestedhordings.tax_percentage;
			}
		
			$scope.percantageChangeHandler = function() {
			  if ($scope.editRequestedhordings.tax_percentage > $scope.percantage || $scope.editRequestedhordings.tax_percentage < 0) {
				toastr.warning(`Please enter percantage in between 1 to 100`);        
				$scope.editRequestedhordings.tax_percentage = $scope.prevQuantity; 
			  }
			}

		$scope.checked = false;
		//updated edited product details
		$scope.updateeditProductdetails = function (editRequestedhordings) {
			// if(editRequestedhordings.dates.length == 0) {
			//   toastr.error("Please Select Dates");
			//   return;
			// }
			if (!$scope.checked) {
				var invalidImageFormat = [];
				$scope.imageFileType = [
					"image/jpg",
					"image/jpeg",
					//"image/png",
					//"image/gif",
					//"image/svg+xml",
				];
				if (
					$scope.files != null &&
					$scope.files &&
					$scope.files.image &&
					$scope.files.image.length !== 0
				) {
					angular.forEach($scope.files.image, function (imageFile) {
						var tempObj = {};
						if (
							$scope.imageFileType.indexOf(imageFile.type) === -1
						) {
							//toastr.error("Please select image.");
							toastr.error("Please select JPG/JPEG image.");
							tempObj.image = imageFile;
							invalidImageFormat.push(tempObj);
						}
					});
				}
			}
			if ($scope.editRequestedhordings.city_name == null) {
				toastr.error("No DMA Found");
			} else {
				//editRequestedhordings.area = $scope.areaObj.id;
				editRequestedhordings.id = $stateParams.id;
				if (
					invalidImageFormat == undefined ||
					invalidImageFormat.length == 0
				) {
					$scope.ranges.selectedDateRanges.map(function (item) {
						item.startDate = moment(item.startDate).format(
							"YYYY-MM-DD"
						);
						item.endDate = moment(item.endDate).format(
							"YYYY-MM-DD"
						);
					});
					// editRequestedhordings.dates = $scope.ranges.selectedDateRanges;

					// editRequestedhordings.dates = $scope.editRequestedhordings.dates;
					//checkbox
					if($scope.editRequestedhordings.dates) {
						$scope.editRequestedhordings.dates.map((item) => {
							const startDate = moment(item.startDate); 
							if($scope.editRequestedhordings.area_time_zone_type != null){  
								const offset = startDate.tz($scope.editRequestedhordings.area_time_zone_type).format().slice(19,25);

								const finalStartDate = moment(item.startDate).format("YYYY-MM-DD")
									+ `T00:00:00.000${offset}`;
								const finalEndDate = moment(item.endDate).format("YYYY-MM-DD") 
									+  `T23:59:59.000${offset}`;
								item.startDate = moment.utc(finalStartDate).format();
								item.endDate = moment.utc(finalEndDate).format();
							}else{
								item.startDate = moment(item.startDate).format("YYYY-MM-DD");
								item.endDate = moment(item.endDate).format("YYYY-MM-DD");
							}
						})
					}
					$scope.editRequestedhordings.billingYes = $scope
						.editRequestedhordings.billing
						? $scope.editRequestedhordings.billing == "yes"
							? "yes"
							: ""
						: $scope.editRequestedhordings.billingYes;
					$scope.editRequestedhordings.billingNo = $scope
						.editRequestedhordings.billing
						? $scope.editRequestedhordings.billing == "no"
							? "no"
							: ""
						: $scope.editRequestedhordings.billingNo;
					$scope.editRequestedhordings.servicingYes = $scope
						.editRequestedhordings.servicing
						? $scope.editRequestedhordings.servicing == "yes"
							? "yes"
							: ""
						: $scope.editRequestedhordings.servicingYes;
					$scope.editRequestedhordings.servicingNo = $scope
						.editRequestedhordings.servicing
						? $scope.editRequestedhordings.servicing == "no"
							? "no"
							: ""
						: $scope.editRequestedhordings.servicingNo;
					if (
						$scope.editRequestedhordings.billingYes == "" &&
						editRequestedhordings.servicingYes == ""
					) {
						$scope.editRequestedhordings.stripe_percent = "5";
					} else if (
						($scope.editRequestedhordings.billingYes == "yes" &&
							editRequestedhordings.servicingNo == "no") ||
						($scope.editRequestedhordings.billingNo == "" &&
							editRequestedhordings.servicingYes == "")
					) {
						$scope.editRequestedhordings.stripe_percent = "10";
					} else if (
						($scope.editRequestedhordings.billingYes == "" &&
							editRequestedhordings.servicingNo == "") ||
						($scope.editRequestedhordings.billingNo == "no" &&
							editRequestedhordings.servicingYes == "yes")
					) {
						$scope.editRequestedhordings.stripe_percent = "10";
					} else if (
						($scope.editRequestedhordings.billingYes == "yes" &&
							editRequestedhordings.servicingNo == "no") ||
						($scope.editRequestedhordings.billingNo == "" &&
							editRequestedhordings.servicingYes == "")
					) {
						$scope.editRequestedhordings.stripe_percent = "10";
					} else if (
						($scope.editRequestedhordings.billingYes == "" &&
							editRequestedhordings.servicingNo == "no") ||
						($scope.editRequestedhordings.billingNo == "no" &&
							editRequestedhordings.servicingYes == "")
					) {
						$scope.editRequestedhordings.stripe_percent = "10";
					} else if (
						$scope.editRequestedhordings.billingYes == "yes" &&
						editRequestedhordings.servicingYes == "yes"
					) {
						$scope.editRequestedhordings.stripe_percent = "15";
					} else if (
						($scope.editRequestedhordings.billingYes == "yes" &&
							editRequestedhordings.servicingNo == "no") ||
						($scope.editRequestedhordings.billingNo == "no" &&
							editRequestedhordings.servicingYes == "yes")
					) {
						$scope.editRequestedhordings.stripe_percent = "10";
					} else {
						$scope.editRequestedhordings.stripe_percent = "5";
					}
        //   debugger;
					Upload.upload({
						url: config.apiPath + "/save-product-details",
						data: {
							image: $scope.files.image,
							// cancellation : $scope.adminProductEdit.cancellation,
							// title : $scope.adminProductEdit.title,
							id: $scope.editRequestedhordings.id,
							height: $scope.editRequestedhordings.height,
							width: $scope.editRequestedhordings.width,

							// venue : $scope.editRequestedhordings.venue,
							address: $scope.editRequestedhordings.address,
							city: $scope.editRequestedhordings.city,
							city_name: $scope.editRequestedhordings.city_name,
							state_name: $scope.editRequestedhordings.state_name,
							direction: $scope.editRequestedhordings.direction,
							impressions:
								$scope.editRequestedhordings.impressions,
							zipcode: $scope.editRequestedhordings.zipcode,
							state: $scope.editRequestedhordings.state,
							audited: $scope.editRequestedhordings.audited,
							vendor: $scope.VendorName,
							stripe_percent:
								$scope.editRequestedhordings.stripe_percent,
							minimumdays:
								$scope.editRequestedhordings.minimumdays,
							network: $scope.editRequestedhordings.network,
							nationloc: $scope.editRequestedhordings.nationloc,
							daypart: $scope.editRequestedhordings.daypart,
							reach: $scope.editRequestedhordings.reach,
							genre: $scope.editRequestedhordings.genre,
							// costperpoint: $scope.editRequestedhordings.costperpoint,
							length: $scope.editRequestedhordings.length,
							sellerId: $scope.editRequestedhordings.sellerId,
							fix: $scope.editRequestedhordings.fix,
							mediahhi: $scope.editRequestedhordings.mediahhi,
							firstDay: $scope.editRequestedhordings.firstDay,
							description:
								$scope.editRequestedhordings.description,
							lastDay: $scope.editRequestedhordings.lastDay,
							weekPeriod:
								$scope.noOffourweeks == 0
									? $scope.editRequestedhordings.weekPeriod
									: $scope.noOffourweeks,
							rateCard: $scope.editRequestedhordings.rateCard,
							installCost:
								$scope.editRequestedhordings.installCost,
							// buses : $scope.editRequestedhordings.buses,
							price: $scope.editRequestedhordings.price,
							negotiatedCost:
								$scope.editRequestedhordings.negotiatedCost,
							productioncost:
								$scope.editRequestedhordings.productioncost,
							unitQty: $scope.editRequestedhordings.unitQty,
							tax_percentage:$scope.editRequestedhordings.tax_percentage,
							billingYes: $scope.editRequestedhordings.billing
								? $scope.editRequestedhordings.billing == "yes"
									? "yes"
									: ""
								: $scope.editRequestedhordings.billingYes,
							billingNo: $scope.editRequestedhordings.billing
								? $scope.editRequestedhordings.billing == "no"
									? "no"
									: ""
								: $scope.editRequestedhordings.billingNo,
							servicingYes: $scope.editRequestedhordings.servicing
								? $scope.editRequestedhordings.servicing ==
								  "yes"
									? "yes"
									: ""
								: $scope.editRequestedhordings.servicingYes,
							servicingNo: $scope.editRequestedhordings.servicing
								? $scope.editRequestedhordings.servicing == "no"
									? "no"
									: ""
								: $scope.editRequestedhordings.servicingNo,
							firstImpression:
								$scope.editRequestedhordings.firstImpression,
							secondImpression:
								$scope.editRequestedhordings.secondImpression,
							thirdImpression:
								$scope.editRequestedhordings.thirdImpression,
							forthImpression:
								$scope.editRequestedhordings.forthImpression,
							firstcpm:
								$scope.newFirst == 0
									? $scope.editRequestedhordings.firstcpm
									: ((($scope.editRequestedhordings.rateCard /
											28) *
											$scope.totalnumDays) /
											$scope.newFirst) *
									  1000,
							cpm:
								$scope.newFirsts == 0
									? $scope.editRequestedhordings.cpm
									: ((($scope.editRequestedhordings.rateCard /
											28) *
											$scope.totalnumDays) /
											$scope.newFirsts) *
									  1000,
							thirdcpm:
								$scope.newFirstst == 0
									? $scope.editRequestedhordings.thirdcpm
									: ((($scope.editRequestedhordings.rateCard /
											28) *
											$scope.totalnumDays) /
											$scope.newFirstst) *
									  1000,
							forthcpm:
								$scope.newFirstsf == 0
									? $scope.editRequestedhordings.forthcpm
									: ((($scope.editRequestedhordings.rateCard /
											28) *
											$scope.totalnumDays) /
											$scope.newFirstsf) *
									  1000,
							cancellation_policy:
								$scope.editRequestedhordings
									.cancellation_policy,
							cancellation_terms:
								$scope.editRequestedhordings.cancellation_terms,
							notes: $scope.editRequestedhordings.notes,
							description:
								$scope.editRequestedhordings.description,
							Comments: $scope.editRequestedhordings.Comments,
							lat: $scope.editRequestedhordings.lat,
							lng: $scope.editRequestedhordings.lng,
							lighting: $scope.editRequestedhordings.lighting,
							imgdirection:
								$scope.editRequestedhordings.imgdirection,
							placement: $scope.editRequestedhordings.placement,
							locationDesc:
								$scope.editRequestedhordings.locationDesc,
							title: $scope.editRequestedhordings.title,
							fliplength: $scope.editRequestedhordings.fliplength,
							looplength: $scope.editRequestedhordings.looplength,
							spotLength: $scope.editRequestedhordings.spotLength,
							ageloopLength:
								$scope.editRequestedhordings.ageloopLength,
							product_newMedia:
								$scope.editRequestedhordings.product_newMedia,
							locationDesc:
								$scope.editRequestedhordings.locationDesc,
							staticMotion:
								$scope.editRequestedhordings.staticMotion,
							sound: $scope.editRequestedhordings.sound,
							file_type: $scope.editRequestedhordings.file_type,

							// product_newAge:$scope.editRequestedhordings.product_newAge,
							medium: $scope.editRequestedhordings.medium,
							area: $scope.areaObj.id,
							type: $scope.editRequestedhordings.type.name,
							editNotCpm: false,
							//date:$scope.editRequestedhordings.dates
							dates: $scope.editRequestedhordings.dates,
							// editRequestedhordings: JSON.parse(angular.toJson($scope.editRequestedhordings))
							default_image_status: $scope.checked,
						},
					}).then(
						function (result) {
							if (result.data.status == "1") {
								getRequestedProductList();
								toastr.success(result.data.message);
								$location.path("/owner/demo---landmark-ooh/hoarding-list");
								$scope.removeSelection();
							} else if (result.data.status == 0) {
								$scope.requestProductErrors =
									result.data.message;
								toastr.error(result.data.message);
							}
							$scope.hordinglistform.$setPristine();
							$scope.hordinglistform.$setUntouched();
						},
						function (resp) {},
						function (evt) {
							var progressPercentage = parseInt(
								(100.0 * evt.loaded) / evt.total
							);
						}
					);
				}
			}
		};

		/*=====================
      | Requested hordings
    ======================*/

		$scope.editRequestedhordings = function (id) {
			this.loading = true;
			$rootScope.http2_loading = true;
			OwnerProductService.getProductDetails(id).then(function (res) {
				$scope.editRequestedhordings = res.product_details;
				$scope.selectedTimezone = $scope.editRequestedhordings.area_time_zone_type;
				if (res.product_details?.default_image_status) {
					$scope.checked = true;
				}
				this.loading = false;
				$rootScope.http2_loading = false;
				$scope.areaObj = $scope.editRequestedhordings.city_name;
				$scope.editRequestedhordings.editNotCpm = false;
				var fromtime =
					$scope.editRequestedhordings.from_date.$date.$numberLong;
				$scope.fromTime = moment(new Date(+fromtime)).format(
					"YYYY-MM-DD"
				);
				var endTime =
					$scope.editRequestedhordings.to_date.$date.$numberLong;
				$scope.endTime = moment(new Date(+endTime)).format(
					"YYYY-MM-DD"
				);
				$scope.location =
					$scope.editRequestedhordings.area_name +
					", " +
					$scope.editRequestedhordings.city_name +
					", " +
					$scope.editRequestedhordings.country_name;
				return res.product_details;
			});
		};

		/*=====================
| Requested hordings Ends
=====================*/

		// filter-code
		$scope.viewSelectedProduct = function (product) {
			$scope.pagination.pageCount = 1;
			$scope.productList = [product];
		};
		$scope.viewSearchText = function (text) {
			if (text == "") {
				$scope.getApprovedProductList();
			}
		};
		$scope.productSearch = function (query) {
			return OwnerProductService.searchOwnerProducts(
				query.toLowerCase()
			).then(function (res) {
				$scope.productList = res;
				$scope.pagination.pageCount = 1;
				return res;
			});
		};
		// Filter-code ends

		/*=====================
  | Campaign Section
  =====================*/
		$scope.ownerCampaign = {};
		$scope.ownerCampaign.from_shortlisted = 1;
		$scope.minStartDate = new Date();
		$scope.minEndDate = moment($scope.minStartDate).add(1, "days").toDate();
		$scope.ownerCampaign.end_date = $scope.minEndDate;
		$scope.defaultStartDate = new Date();

		$scope.updateEndDateValidations = function () {
			$scope.minEndDate = moment($scope.ownerCampaign.start_date)
				.add(1, "days")
				.toDate();
			if (
				$scope.ownerCampaign.end_date <= $scope.ownerCampaign.start_date
			) {
				$scope.ownerCampaign.end_date = $scope.minEndDate;
			}
		};

		$scope.saveOwnerCampaign = function () {
			OwnerCampaignService.saveOwnerCampaign($scope.ownerCampaign).then(
				function (result) {
					if (result.status == 1) {
						$scope.ownerCampaign = {};
						$scope.forms.ownerCampaignForm.$setPristine();
						$scope.forms.ownerCampaignForm.$setUntouched();
						toastr.success(result.message);
						setTimeout(function () {
							window.location.reload();
						}, 500);
					} else if (result.status == 0) {
						if (result.message.constructor == Array) {
							$scope.ownerCampaignErrors = result.message;
						} else {
							toastr.error(result.message);
						}
					} else {
						toastr.error(result.message);
					}
				}
			);
		};

		/*=====================
  | Campaign Section
  =====================*/

		//SORTING FOR INVENTORY LIST
		$scope.sortAsc = function (headingName, type) {
			$scope.upArrowColour = headingName;
			$scope.sortType = "Asc";
			if (type == "string") {
				$scope.newOfferData = $scope.productList.map((e) => {
					return {
						...e,
						name: e.name,
						user_email: e.user_email,
					};
				});
				$scope.productList = [];
				$scope.productList = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					return a[headingName].localeCompare(
						b[headingName],
						undefined,
						{
							numeric: true,
							sensitivity: "base",
						}
					);
				});

				// $scope.productList = $scope.newOfferData;
			}
			$scope.productList = $scope.productList.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? 1 : -1;
				} else {
					return a[headingName] - b[headingName];
				}
			});
			console.log($scope.productList);
		};
		$scope.sortDsc = function (headingName, type) {
			$scope.downArrowColour = headingName;
			$scope.sortType = "Dsc";
			if (type == "string") {
				$scope.newOfferData = $scope.productList.map((e) => {
					return {
						...e,
						name: e.name,
						user_email: e.user_email,
					};
				});
				$scope.productList = [];
				$scope.productList = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					return b[headingName].localeCompare(
						a[headingName],
						undefined,
						{
							numeric: true,
							sensitivity: "base",
						}
					);
				});

				// $scope.RfpData = $scope.newOfferData;
			}
			$scope.productList = $scope.productList.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? -1 : 1;
				} else {
					return b[headingName] - a[headingName];
				}
			});
			console.log($scope.productList);
		};

		//SORTING FOR INVENTORY LIST ENDS

		/*=========================
  | Page based initial loads
  =========================*/

		if ($rootScope.currStateName == "owner.product-details") {
			if (typeof $stateParams.productId !== "undefined") {
				getOwnerProductDetails($stateParams.productId);
			} else {
				toastr.error("Product not found.");
			}
		}

		if ($rootScope.currStateName == "owner.product-camp-details") {
			if (typeof $stateParams.productId !== "undefined") {
				getOwnerProductDetails($stateParams.productId);
			} else {
				toastr.error("Product not found.");
			}
		}

		if ($rootScope.currStateName == "owner.hoarding-list") {
			$scope.getApprovedProductList();
			getShortlistedProductsByOwner();
			$scope.getdetails();
		}

		if ($rootScope.currStateName == "owner.requested-hoardings") {
			getRequestedProductList();
			// $scope.getApprovedProductList()
		}

		if (
			$rootScope.currStateName == "owner.editproduct-details" ||
			$rootScope.currStateName == "owner.cloneproduct-details" ||
			$rootScope.currStateName == "admin.cloneproduct-details"
		) {
			$scope.flag = 1;
			$scope.editRequestedhordings($stateParams.id);
		}

		$scope.changeProductPrice = function (data) {
			product = {};
			product.id = data.id;
			product.default_price = data.default_price;
			OwnerProductService.changeProductPrice(product).then(function (
				result
			) {
				if (result.status == 1) {
					toastr.success(result.message);
				} else {
					toastr.error(result.data.message);
				}
			});
		};

		$scope.product_visibility = function (product_visibility, product_id) {
			visibility = {};
			visibility.product_visibility = product_visibility;
			OwnerProductService.changeProductVisibility(
				product_id,
				visibility
			).then(function (result) {
				if (result.status == 1) {
					toastr.success(result.message);
				} else {
					toastr.error(result.data.message);
				}
			});
		};
		$scope.sendSellerDetails = function (sellerDetails) {
			if (sellerDetails.subseller_id) {
				var detailsObjs = {
					seller_id: sellerDetails.seller_id,
					subseller_id: sellerDetails.subseller_id,
					name: sellerDetails.name,
					type: sellerDetails.type,
					contactName: sellerDetails.contactName,
					email: sellerDetails.email,
					phone: sellerDetails.phone,
					address: sellerDetails.address,
				};
			} else {
				var detailsObjs = {
					name: sellerDetails.name,
					type: sellerDetails.type,
					contactName: sellerDetails.contactName,
					email: sellerDetails.email,
					phone: sellerDetails.phone,
					address: sellerDetails.address,
				};
			}
			OwnerProductService.createSubSeller(detailsObjs).then(function (
				result
			) {
				if (result.status == 1) {
					toastr.success(result.message);
					getgetSubSellerData();
					sellerDetails.name = "";
					sellerDetails.type = "";
					sellerDetails.contactName = "";
					sellerDetails.email = "";
					sellerDetails.phone = "";
					sellerDetails.address = "";
					$("#addSeller").modal("hide");
					$("#editSeller").modal("hide");
				} else {
					toastr.error(result.data.message);
				}
			});
		};
		var getgetSubSellerData = function () {
			OwnerProductService.getAllClients().then(function (result) {
				$scope.subSellerDetails = result;
			});
		};
		getgetSubSellerData();
		$scope.limit = 3;
		$scope.loadMore = function () {
			$scope.limit = $scope.subSellerDetails.length;
		};
		$scope.editSubSeller = function (editedSubSeller) {
			$scope.editedSubSellerDetails = editedSubSeller;
		};
		$scope.conformDeleteSubSeller = function (deletedSubseller) {
			$scope.deletedSubsellerDetails = deletedSubseller;
		};
		$scope.deleteSubSeller = function (subseller_id) {
			OwnerProductService.deletedSubSeller(subseller_id).then(function (
				result
			) {
				if (result.status == 1) {
					toastr.success(result.message);
					$("#deletePopup").modal("hide");
					getgetSubSellerData();
				} else {
					toastr.error(result.data.message);
				}
			});
		};

		/*********Start Subseller on 6thAugust2020**********/

		$scope.setSuperAdminForClient = function () {
			var obj = {
				client_id: $scope.selectedClientId,
				super_admin_email: $scope.superAdminToAssign.email,
			};
			OwnerProductService.assignSuperAdminToClient(obj).then(function (
				result
			) {
				if (result.status == 0) {
					toastr.error(result.message);
				} else {
					$scope.selectedClientId = null;
					$scope.superAdminToAssign = {};
					getAllClients();
					$mdDialog.cancel();
					toastr.success(result.message);
				}
			});
		};

		/**
		 * Get Client
		 */
		var getAllClients = function () {
			OwnerProductService.getAllClients().then(function (result) {
				$scope.allClients = result;
				if ($stateParams.clientID) {
					var client = result.find(
						(item) => item.id == $stateParams.clientID
					);
					if (client) {
						$scope.showUserDetailsPopup(
							client.client_id,
							client.super_admin_m_id
						);
					}
				}
			});
		};
		getAllClients();

		/**
		 * Get Users
		 */

		var getAllUsers = function () {
			OwnerProductService.getAllUsers().then(function (result) {
				$scope.allUsers = result;
			});
		};
		getAllUsers();

		/*
	======== Toggling user activation ========
	*/
		$scope.toggleUserActivation = function (userMId) {
			OwnerProductService.toggleActivationUser(userMId).then(function (
				result
			) {
				if (result.status == 1) {
					getAllUsers();
					toastr.success(result.message);
				} else {
					toastr.error(result.message);
				}
			});
		};
		/*
	======== Toggling user activation ends ========
	*/

		/*
	======== Activating New Super Admin ========
	*/
		$scope.toggleSuperAdminActivation = function (userMId) {
			OwnerProductService.toggleActivationUser(userMId).then(function (
				result
			) {
				if (result.status == 1) {
					$scope.selectedUser.user_details.activated =
						!$scope.selectedUser.user_details.activated;
					getAllUsers();
					toastr.success(result.message);
				} else {
					toastr.error(result.message);
				}
			});
		};
		/*
	======== Activating New Super Admin ends ========
	*/
		/**
		 */
		$scope.viewUserRoles = function (userMId) {
			OwnerProductService.getUserDetailsWithRoles(userMId).then(function (
				result
			) {
				if (result.status == 0) {
					toastr.error(result.message);
				} else {
					$scope.selectedUser = result;
					$scope.selectedRolesForUser = _.pluck(
						result.user_roles,
						"id"
					);
					$mdDialog.show({
						templateUrl: "views/admin/user-details-popup.html",
						fullscreen: $scope.customFullscreen,
						clickOutsideToClose: true,
						preserveScope: true,
						scope: $scope,
					});
				}
			});
		};

		// $scope.conformDeleteSubSeller = function (shortlistId) {
		//   $scope.shortlistId = shortlistId
		// }
		// $scope.deleteSubSellerAccount = function (campaignId) {
		//   var campaignId = {
		//     subseller_id:$scope.shortlistId
		//   }
		//   OwnerProductService.deleteSubSellerr(campaignId).then(function (result) {
		//     if (result.status == 1) {
		//       toastr.success(result.message);

		//     }
		//   });
		// }

		/*********End Subseller on 6thAugust2020**********/

		/*=============================
  | Page based initial loads end
  =============================*/

		/* ----------------------------
              New Hording digital bullitin product Nav bars Start
          -------------------------------*/

		// var digitalSlots = 0;
		// $scope.digitalSlots = [];
		// $scope.weeksDigitalArray = [];
		// $scope.digitalSlotsClosed = false;

		// for (var i = 1; i <= 26; i++) {
		//   $scope.weeksDigitalArray.push({ twoWeeks: 1 })
		// }
		// $scope.blockSlotChange = function () {
		//   $scope.weeksDigitalArray.forEach((item) => { item.selected = false; item.isBlocked = false; $scope.totalDigitalSlotAmount = 0 })
		//   $scope.weeksDigitalArray.forEach(function (item) {
		//     $scope.unavailalbeDateRanges.forEach(function (unAvailable) {
		//       if ((moment(item.startDay).format('DD-MM-YYYY') == moment(unAvailable.booked_from).format('DD-MM-YYYY')) && (moment(item.endDay).format('DD-MM-YYYY') == moment(unAvailable.booked_to).format('DD-MM-YYYY'))) {
		//         item.availableSlots = ($scope.digitalSlots.length - unAvailable.booked_slots)
		//         if (item.availableSlots == 0) {
		//           item.isBlocked = true;
		//         }
		//       } else if ((moment(unAvailable.booked_from).isSameOrAfter(moment(item.startDay).format('YYYY-MM-DD')) && moment(unAvailable.booked_from).isSameOrBefore(moment(item.endDay).format('YYYY-MM-DD'))) || (moment(moment(unAvailable.booked_to).format('YYYY-MM-DD')).isSameOrAfter(moment(item.startDay).format('YYYY-MM-DD')) && moment(moment(unAvailable.booked_to).format('YYYY-MM-DD')).isSameOrBefore(moment(item.endDay).format('YYYY-MM-DD')))) {
		//         item.availableSlots = ($scope.digitalSlots.length - unAvailable.booked_slots)
		//         if (item.availableSlots == 0) {
		//           item.isBlocked = true;
		//         }

		//       }
		//     })
		//   })
		// }
		// function productDatesDigitalCalculator() {
		//   for (var i = 1; i <= digitalSlots; i++) {
		//     $scope.digitalSlots.push(i)
		//   }
		//   var unavailBoundaries = [];
		//   $scope.unavailalbeDateRanges.forEach((dates) => {
		//     unavailBoundaries.push(moment(dates.booked_from))
		//     unavailBoundaries.push(moment(dates.booked_to))
		//   });
		//   // var slotPrices =0;
		//   for (item in $scope.weeksDigitalArray) {
		//     $scope.weeksDigitalArray[item].price = $scope.product.price;
		//   }
		//   var digitalCurrentDay = moment().format('LLLL').split(',')[0];
		//   if (digitalCurrentDay == 'Monday') {
		//     var startDay = moment(new Date()).add(7, 'days').format('LLLL');
		//     var endDay = moment(new Date()).add(7 + 6, 'days').format('LLLL');
		//     $scope.weeksDigitalArray[0].startDay = startDay;
		//     $scope.weeksDigitalArray[0].endDay = endDay;
		//     unavailBoundaries.forEach((date) => {
		//       $scope.weeksDigitalArray[0].isBlocked = date.isSameOrAfter(startDay) && date.isSameOrBefore(endDay);
		//     });
		//   } else {
		//     var tempDay;
		//     for (i = 1; i <= 6; i++) {
		//       tempDay = moment(new Date()).add(i, 'days').format('LLLL').split(',')[0];
		//       if (tempDay == 'Monday') {
		//         var startDay = moment(new Date()).add(i, 'days').format('LLLL');
		//         var endDay = moment(new Date()).add(i + 6, 'days').format('LLLL');
		//         $scope.weeksDigitalArray[0].startDay = startDay;
		//         $scope.weeksDigitalArray[0].endDay = endDay;
		//         var isBlocked = false;
		//         for (var date of unavailBoundaries) {
		//           if (date.isSameOrAfter(startDay) && date.isSameOrBefore(endDay)) {
		//             isBlocked = true;
		//             break;
		//           }
		//         }
		//         $scope.weeksDigitalArray[0].isBlocked = isBlocked;
		//       }

		//     }

		//   }
		//   var tempororyStartDate = $scope.weeksDigitalArray[0].endDay;
		//   $scope.weeksDigitalArray.forEach(function (item, index) {
		//     if (index > 0) {
		//       item.startDay = moment(tempororyStartDate).add(1, 'days').format('LLLL');
		//       item.endDay = moment(tempororyStartDate).add(7, 'days').format('LLLL');
		//       tempororyStartDate = item.endDay;
		//       var isBlocked = false;
		//       for (var date of unavailBoundaries) {
		//         if (date.isSameOrAfter(item.startDay) && date.isSameOrBefore(item.endDay)) {
		//           isBlocked = true;
		//           break;
		//         }
		//       }
		//       $scope.weeksDigitalArray[index].isBlocked = isBlocked;
		//     }

		//   })
		// }

		// };
		// productDatesDigitalCalculator()
		// $scope.totalDigitalSlotAmount = 0;
		// $scope.selectUserDigitalWeeks = function (weeks, index, ev) {
		//   if ($scope.numOfSlots == 0) {
		//     alert("please select no. of slots")
		//     return false;
		//   }
		//   if ($scope.numOfSlots > weeks.availableSlots) {
		//     alert("As you are exceeding the slots. you can't book it");
		//     return false;
		//   }
		//   if ($scope.weeksDigitalArray[index].selected == true) {
		//     $scope.weeksDigitalArray[index].selected = false;
		//     $scope.totalDigitalSlotAmount -= parseInt(parseInt($scope.numOfSlots) * parseInt($scope.weeksDigitalArray[index].price));

		//   } else {
		//     $scope.totalDigitalSlotAmount += parseInt(parseInt($scope.numOfSlots) * parseInt($scope.weeksDigitalArray[index].price));
		//     $scope.weeksDigitalArray[index].selected = true;

		//   }
		// };
		// $scope.digitalSelectUserWeeks = function (weeks, index, ev) {

		//   if ($scope.weeksDigitalArray[index].selected && $scope.weeksDigitalArray[index].selected == true) {
		//     $scope.weeksDigitalArray[index].selected = false;

		//   } else {
		//     $scope.weeksDigitalArray[index].selected = true;
		//   }
		// }
		// $scope.digitalSlotedDatesPopupClosed = function () {
		//   $scope.digitalSlotsClosed = false;
		// }
		// $scope.digitalBlockedSlotesbtn = function (weeksArray) {
		//   $scope.product.dates = [];
		//   weeksArray.filter((week) => week.selected).forEach(function (item) {
		//     var startDate = moment(item.startDay).format('YYYY-MM-DD')
		//     var endDate = moment(item.endDay).format('YYYY-MM-DD')

		//     $scope.product.dates.push({ startDate: startDate, endDate: endDate })
		//     $scope.digitalSlotedDatesPopupClosed();
		//   })

		// }

		/* ----------------------------
  New Hording Digital bullitin product Nav bars Ends
-------------------------------*/

		/*=============================
  | owner slots blocking starts
  =============================*/

		$scope.weeksArray = [];
		for (var i = 1; i <= 26; i++) {
			$scope.weeksArray.push({ twoWeeks: 2 });
		}
		var currentDay = moment().format("LLLL").split(",")[0];
		function productDatesCalculator() {
			var unavailBoundaries = [];
			$scope.unavailalbeDateRanges.forEach((dates) => {
				unavailBoundaries.push(moment(dates.booked_from));
				unavailBoundaries.push(moment(dates.booked_to));
			});
			if (currentDay == "Monday") {
				var startDay = moment().add(7, "days").format("LLLL");
				var endDay = moment()
					.add(7 + 13, "days")
					.format("LLLL");
				$scope.weeksArray[0].startDay = startDay;
				$scope.weeksArray[0].endDay = endDay;
				unavailBoundaries.forEach((date) => {
					$scope.weeksArray[0].isBlocked =
						date.isSameOrAfter(startDay) &&
						date.isSameOrBefore(endDay);
				});
			} else {
				var tempDay;
				for (i = 1; i <= 6; i++) {
					tempDay = moment(new Date())
						.add(i, "days")
						.format("LLLL")
						.split(",")[0];
					if (tempDay == "Monday") {
						var startDay = moment(new Date())
							.add(i + 7, "days")
							.format("LLLL");
						var endDay = moment(new Date())
							.add(i + 7 + 13, "days")
							.format("LLLL");
						$scope.weeksArray[0].startDay = startDay;
						$scope.weeksArray[0].endDay = endDay;
						var isBlocked = false;
						for (var date of unavailBoundaries) {
							if (
								date.isSameOrAfter(startDay) &&
								date.isSameOrBefore(endDay)
							) {
								isBlocked = true;
								break;
							}
						}
						$scope.weeksArray[0].isBlocked = isBlocked;
					}
				}
			}
			var tempororyStartDate = $scope.weeksArray[0].endDay;
			$scope.weeksArray.forEach(function (item, index) {
				if (index > 0) {
					item.startDay = moment(tempororyStartDate)
						.add(1, "days")
						.format("LLLL");
					item.endDay = moment(tempororyStartDate)
						.add(14, "days")
						.format("LLLL");
					tempororyStartDate = item.endDay;
					var isBlocked = false;
					for (var date of unavailBoundaries) {
						if (
							date.isSameOrAfter(item.startDay) &&
							date.isSameOrBefore(item.endDay)
						) {
							isBlocked = true;
							break;
						}
					}
					$scope.weeksArray[index].isBlocked = isBlocked;
				}
			});
		}
		productDatesCalculator();
		$scope.slotsClosed = false;
		$scope.selectUserWeeks = function (weeks, index, ev) {
			if (
				$scope.weeksArray[index].selected &&
				$scope.weeksArray[index].selected == true
			) {
				$scope.weeksArray[index].selected = false;
			} else {
				$scope.weeksArray[index].selected = true;
			}
		};
		$scope.slotedDatesPopupClosed = function (Type) {
			if (Type == "Bulletin") {
				$scope.slotsClosed = false;
			} else {
				$scope.digitalSlotsClosed = false;
			}
		};
		$scope.blockedSlotesbtn = function (weeksArray, Type) {
			$scope.product.dates = [];
			weeksArray
				.filter((week) => week.selected)
				.forEach(function (item) {
					var startDate = moment(item.startDay).format("YYYY-MM-DD");
					var endDate = moment(item.endDay).format("YYYY-MM-DD");

					$scope.product.dates.push({
						startDate: startDate,
						endDate: endDate,
					});
					$scope.slotedDatesPopupClosed(Type);
				});
		};
		/*=============================
 | owner slots blocking ends
 =============================*/
		/****bulk upload anf doenload */
		$scope.downloadProductType = $scope.download[0];
		$scope.uploadProductType = $scope.download[0];
		$scope.staticContent = false;
		$scope.digitalContent = false;
		$scope.staticDigitalContent = false;
		$scope.mediaContent = false;
		$scope.displayErrorMsg = false;
		$scope.displaySuccessMsg=false;
		$scope.productTypeDownload = function (name) {
			if (name == "Static") {
				$scope.staticContent = true;
				$scope.digitalContent = false;
				$scope.staticDigitalContent = false;
				$scope.mediaContent = false;
			} else if (name == "Digital") {
				$scope.digitalContent = true;
				$scope.staticContent = false;
				$scope.staticDigitalContent = false;
				$scope.mediaContent = false;
			} else if (name == "Digital/Static") {
				$scope.staticDigitalContent = true;
				$scope.staticContent = false;
				$scope.digitalContent = false;
				$scope.mediaContent = false;
			} else if (name == "Media") {
				$scope.mediaContent = true;
				$scope.staticContent = false;
				$scope.digitalContent = false;
				$scope.staticDigitalContent = false;
			} else {
				$scope.staticContent = false;
				$scope.digitalContent = false;
				$scope.staticDigitalContent = false;
				$scope.mediaContent = false;
			}
		};
		$scope.showUploadForm = false;
		$scope.displayErrorMsg = false;
		$scope.displaySuccessMsg=false;
		$scope.productTypeUpload = function (type) {
			$scope.displayErrorMsg = false;
			$scope.displaySuccessMsg=false;
			if (
				type == "Static" ||
				type == "Digital" ||
				type == "Digital/Static" ||
				type == "Media"
			) {
				$scope.showUploadForm = true;
			} else {
				$scope.showUploadForm = false;
			}
		};
		$scope.displayErrorMsg = false;
		$scope.displaySuccessMsg=false;
		$scope.errorMsg = [];
		$scope.excelfiles = {};
		$scope.uploadBulkExcelFile = function (excelfiles) {
			Upload.upload({
				url: config.apiPath + "/import",
				data: {
					file: $scope.excelfiles.file,
					type: $scope.uploadProductType.name,
				},
			}).then(
				function (result) {
					if (result.status == 200) {
						if (result.data.status == 1) {
							toastr.success(result.data.message);
							$scope.excelfiles = {};
							addbulkProduct();
						} else {
							toastr.error(result.data.message);
						}
					}
					$scope.downloadProductType = $scope.download[0];
					$scope.uploadProductType = $scope.download[0];
					$scope.showUploadForm = false;
					$scope.staticContent = false;
					$scope.digitalContent = false;
					$scope.staticDigitalContent = false;
					$scope.mediaContent = false;
				},
				function (errorCallback) {
					if (errorCallback.status == 400) {
						$scope.errormsg = errorCallback.data;
						if (errorCallback.data.status == 1) {
							toastr.success(errorCallback.data.message);
							if(errorCallback.data.error_status == 1){
							  $scope.displayErrorMsg=true;
							  $scope.displaySuccessMsg=true;
							  $scope.errorMsg=errorCallback.data.error_message;
							  $scope.staticContent=false;
							  $scope.digitalContent=false;
							  $scope.staticDigitalContent=false;
							  $scope.mediaContent=false;
							  $scope.downloadProductType=$scope.download[0];
							  $scope.successMsg=errorCallback.data.message;
						  }else{
							addbulkProduct()  
						  }
						} else {
							$scope.displayErrorMsg = true;
							$scope.errorMsg = errorCallback.data.message;
							//toastr.error(errorCallback.data.message);
							$scope.staticContent = false;
							$scope.digitalContent = false;
							$scope.staticDigitalContent = false;
							$scope.mediaContent = false;
							$scope.downloadProductType = $scope.download[0];
							// $scope.uploadProductType=$scope.download[0];
							// $scope.showUploadForm= false;
						}
					}
				}
			);
		};
		$scope.clearerrorMsg = function () {
			$scope.displayErrorMsg = false;
			$scope.displaySuccessMsg=false;
		};
		//bulk upload 09-Mar-2022
		$scope.bulk = {};
		$scope.addnewImage = function () {
			document.getElementById("bulkUpload").classList.toggle("show");
			$scope.type = $scope.BulkProductTypes[0];
			$scope.uploadImgContent = false;
		};
		$scope.ImageList = {};
		function getBulkUploadList() {
			OwnerProductService.getBulkUploadList().then(function (result) {
				$scope.ImageList = result.bulk_images;
			});
		}
		getBulkUploadList();
		$scope.BulkProductTypes = [
			{
				name: "Select product type",
			},
			{
				name: "Static",
			},
			{
				name: "Digital",
			},
			{
				name: "Digital/Static",
			},
			{
				name: "Media",
			},
		];
		$scope.type = $scope.BulkProductTypes[0];
		$scope.bulkProductType = function (type) {
			if (
				type == "Static" ||
				type == "Digital" ||
				type == "Digital/Static" ||
				type == "Media"
			) {
				$scope.uploadImgContent = true;
			} else {
				$scope.uploadImgContent = false;
			}
		};
		$scope.$watch("bulk.image", function (ctrl) {
			var fileUpload = ctrl;
			if (typeof fileUpload[0] != "undefined") {
				$scope.resizeTextImg = false;
				var reader = new FileReader();
				reader.readAsDataURL(fileUpload[0]);
				reader.onload = function (e) {
					var image = new Image();
					image.src = e.target.result;
					image.onload = function () {
						var height = this.height;
						var width = this.width;
						if (width >= 1280 && height >= 960) {
							$scope.resizeTextImg = false;
							// alert("At least you can upload a 1280 px *960 px size.");
							// return false;
						} else {
							$scope.resizeTextImg = true;
							$scope.bulk.image = "";
							toastr.error(
								"Uploaded image has valid Width 1280px and Height 960px."
							);
							return true;
						}
					};
				};
			}
		});
		//   if ($scope.image.length !== 0) {
		//     angular.forEach($scope.image, function (imageFile) {
		//       var tempObj = {};
		//       if ($scope.imageFileTypes.indexOf(imageFile.type) === -1) {
		//         toastr.error("Please select image");
		//         tempObj.image = imageFile;
		//         invalidImageFormats.push(tempObj);
		//       }
		//     });
		//   }
		$scope.requestAddImage = function () {
			//validating the selected upload images
			var invalidImageFormats = [];
			$scope.imageFileTypes = [
				"image/jpg",
				"image/jpeg",
				//"image/png",
				//"image/gif",
				//"image/svg+xml",
			];
			if ($scope.bulk.image.length !== 0) {
				angular.forEach($scope.bulk.image, function (imageFile) {
					var tempObj = {};
					if ($scope.imageFileTypes.indexOf(imageFile.type) === -1) {
						//toastr.error("Please select image.");
						toastr.error("Please select JPG/JPEG image.");
						tempObj.image = imageFile;
						invalidImageFormats.push(tempObj);
					}
				});
			}
			//save product only if there are no invalid image formats
			if (invalidImageFormats.length == 0) {
				Upload.upload({
					url: config.apiPath + "/save-bulk-upload-images",
					data: { image: $scope.bulk.image, type: $scope.type.name },
				}).then(
					function (result) {
						getBulkUploadList();
						if (result.data.status == "1") {
							toastr.success(result.data.message);
							document
								.getElementById("bulkUpload")
								.classList.toggle("show");
						}
						$scope.bulk.image = "";
						$scope.type = $scope.ProductTypes[0];
					},
					function (errorCallback) {
						if (errorCallback.status == 400) {
							$scope.errormsg = errorCallback.data;
							if (errorCallback.data.status == 1) {
								toastr.success(errorCallback.data.message);
								addbulkProduct();
							} else {
								$scope.displayErrorMsg = true;
								$scope.errorMsg = errorCallback.data.message;
							}
						}
					}
				);
			}
		};

		$scope.exportCsvHandler = () => {
			$scope.openExportCsvSlushBucket({
				products: $scope.productList,
			});
		};

		//Export to CSV
		$scope.openExportCsvSlushBucket = function (campaignDetails) {
			$mdDialog.show({
				templateUrl: "views/slush-bucket-popup.html",
				fullscreen: $scope.customFullscreen,
				clickOutsideToClose: false,
				preserveScope: true,
				controller: function ($scope) {
					$scope.activeOption = {
						isAvailable: false,
						option: null,
					};

					$scope.changeSelection = function (isAvailable, option) {
						$scope.activeOption.isAvailable = isAvailable;
						$scope.activeOption.option = option;
					};

					$scope.closeMdDialog = function () {
						$mdDialog.hide();
					};

					$scope.changePosition = function (position) {
						switch (position) {
							case "right":
								const opt = $scope.activeOption.option;
								$scope.activeOption.isAvailable = false;
								$scope.personalizeColumns.available_columns.splice(
									$scope.personalizeColumns.available_columns.indexOf(
										opt
									),
									1
								);
								$scope.personalizeColumns.selected_columns.push(
									opt
								);
								break;
							case "left":
								const cho = $scope.activeOption.option;
								$scope.activeOption.isAvailable = true;
								$scope.personalizeColumns.selected_columns.splice(
									$scope.personalizeColumns.selected_columns.indexOf(
										cho
									),
									1
								);
								$scope.personalizeColumns.available_columns.push(
									cho
								);
								break;
							case "up":
								console.log($scope.activeOption);
								console.log(
									$scope.personalizeColumns.selected_columns
								);
								var selectedIndex =
									$scope.personalizeColumns.selected_columns.findIndex(
										(item) =>
											item.field_name ===
											$scope.activeOption.option
												.field_name
									);
								console.log(selectedIndex);
								if (selectedIndex) {
									var tempOption = {
										...$scope.personalizeColumns
											.selected_columns[
											selectedIndex - 1
										],
									};
									$scope.personalizeColumns.selected_columns[
										selectedIndex - 1
									] = {
										...$scope.personalizeColumns
											.selected_columns[selectedIndex],
									};
									$scope.personalizeColumns.selected_columns[
										selectedIndex
									] = {
										...tempOption,
									};
								} else {
									var tempOption = {
										...$scope.personalizeColumns
											.selected_columns[selectedIndex],
									};
									$scope.personalizeColumns.selected_columns.splice(
										selectedIndex,
										1
									);
									$scope.personalizeColumns.selected_columns.push(
										tempOption
									);
								}
								break;
							case "down":
								console.log($scope.activeOption);
								console.log(
									$scope.personalizeColumns.selected_columns
								);
								var selectedIndex =
									$scope.personalizeColumns.selected_columns.findIndex(
										(item) =>
											item.field_name ===
											$scope.activeOption.option
												.field_name
									);
								console.log(selectedIndex);
								if (
									selectedIndex !==
									$scope.personalizeColumns.selected_columns
										.length -
										1
								) {
									var tempOption = {
										...$scope.personalizeColumns
											.selected_columns[
											selectedIndex + 1
										],
									};
									$scope.personalizeColumns.selected_columns[
										selectedIndex + 1
									] = {
										...$scope.personalizeColumns
											.selected_columns[selectedIndex],
									};
									$scope.personalizeColumns.selected_columns[
										selectedIndex
									] = {
										...tempOption,
									};
								} else {
									var tempOption = {
										...$scope.personalizeColumns
											.selected_columns[selectedIndex],
									};
									$scope.personalizeColumns.selected_columns.splice(
										selectedIndex,
										1
									);
									$scope.personalizeColumns.selected_columns.unshift(
										tempOption
									);
								}
								break;
							default:
								console.log("Invalid Move");
						}
					};

					function formatDate(dt) {
						var dt = new Date(dt);
						var year = dt.getFullYear();
						var month = dt.getMonth() + 1;
						var day = dt.getDate();
						return (
							year +
							"-" +
							(month < 0 ? "0" + month : month) +
							"-" +
							(day < 0 ? "0" + day : day)
						);
					}

					function getDate(dt) {
						const dtValue = dt.$date.$numberLong;
						return moment(new Date(+dtValue)).format("YYYY-MM-DD");
					}

					$scope.convertToCSV = function (headers) {
						try {
							var csvData = Object.values(headers).join(",");
							csvData += "\r\n";
							campaignDetails.products.forEach((row) => {
								var rowData = [];
								Object.keys(headers).forEach((col) => {
									var fieldData = "";
									if (
										col == "booked_from" ||
										col == "booked_to"
									) {
										fieldData = getDate(
											row[
												col == "booked_from"
													? "from_date"
													: "to_date"
											]
										); //YYYY-MM-DD
									} else if (col == "price" || col == "cpm") {
										var offerPrice =
											row[
												col == "price"
													? "rateCard"
													: col
											];
										fieldData =
											"$" + Number(offerPrice).toFixed(2);
									} else if (
										col == "impressionsperselectedDates"
									) {
										var impression =
											row["secondImpression"];
										fieldData =
											Number(impression).toFixed(0);
									} else if (col == "sold_status") {
										fieldData = row[col]
											? "Sold out"
											: "Available";
									} else if (col == "offerprice") {
										fieldData =
											"$" +
											(
												parseInt(row["unitQty"]) *
												parseInt(row["rateCard"])
											).toFixed(2);
									} else if (col == "quantity") {
										fieldData = row["unitQty"];
									} else {
										fieldData = row[col];
									}
									rowData.push(fieldData);
								});
								csvData += rowData.join(",");
								csvData += "\r\n";
							});
							return csvData;
						} catch (ex) {
							console.log("exception: " + ex.message);
						}
					};

					$scope.downloadCSVFile = function (fileName, headers) {
						const csvData = this.convertToCSV(headers);
						//console.log(csvData);
						let blob = new Blob(["\ufeff" + csvData], {
							type: "text/csv;charset=utf-8;",
						});
						let dwldLink = document.createElement("a");
						let url = URL.createObjectURL(blob);
						let isSafariBrowser =
							navigator.userAgent.indexOf("Safari") != -1 &&
							navigator.userAgent.indexOf("Chrome") == -1;
						if (isSafariBrowser) {
							//if Safari open in new window to save file with random filename.
							dwldLink.setAttribute("target", "_blank");
						}
						dwldLink.setAttribute("href", url);
						dwldLink.setAttribute("download", fileName + ".csv");
						dwldLink.style.visibility = "hidden";
						document.body.appendChild(dwldLink);
						dwldLink.click();
						document.body.removeChild(dwldLink);
					};
					// get columns
					$rootScope.loading = true;
					CampaignService.getColumnsToExport().then(function (
						result
					) {
						$scope.personalizeColumns = result;
						$rootScope.loading = false;
					});

					//save columns
					$scope.saveColumns = function () {
						const payload = {
							report_type: "report_campaign",
							selected_columns_post: JSON.stringify(
								$scope.personalizeColumns.selected_columns.map(
									(item) => {
										return {
											field_name: item.field_name,
											label: item.label,
										};
									}
								)
							),
						};
						CampaignService.saveColumnsToExport(payload).then(
							function (result) {
								if (result.status == "1") {
									const headers = {};
									$scope.personalizeColumns.selected_columns.forEach(
										(item) => {
											headers[item.field_name] =
												item.label;
										}
									);
									$scope.downloadCSVFile(
										"campaign-products",
										headers
									);
									toastr.success(result.message);
								} else {
									toastr.error(result.message);
								}
								$mdDialog.hide();
							}
						);
					};
				},
			});
		};
		
	//Transfer product
	$scope.sellerList=[];
	 function getSellerList() {
	  OwnerProductService.getSellerList().then(function (result) {
		$scope.sellerList = result.sellers_data;
		// console.log($scope.sellerList)
	  });
	}
	getSellerList();
	
	//Get BU Products By Seller
	$scope.BUProductsList=[];
	 function getBUProductsList() {
	  OwnerProductService.getBUProductsList().then(function (result) {
		$scope.BUProductsList = result;
		// console.log($scope.sellerList)
	  });
	}
	getBUProductsList();
		
	$scope.onAllowTransfer= function (id) {
		var Existingid = $scope.selectedids.indexOf(id)
		$scope.bulkSelect = false
		if(Existingid >-1){
			$scope.selectedids.splice(Existingid, 1)
			if($scope.selectedids.length == 0) {
				$scope.bulkSelect = true
			} 
		} else {
			$scope.selectedids.push(id)
		}
	};
	
	
    $scope.TransferProduct = function () {
		$scope.displayErrorMsg=false;
		$scope.displaySuccessMsg=false;
		document.getElementById("TransferProductDiv").classList.toggle("show");
	}
	
	$scope.TransferProductSeller=function(sellerId,bulkupload_uniqueID){
		if(bulkupload_uniqueID == undefined){
			bulkupload_uniqueID = '';
		}
		Upload.upload({
			url: config.apiPath + '/products-transfer',
			data: {
				seller_id:sellerId,
				bulkupload_uniqueID:bulkupload_uniqueID,
				product_id_arr : $scope.selectedids
			}
		}).then(function (result) {
			console.log(result);
			if (result.data.status == 1) {
				getRequestedProductList();
                toastr.success(result.data.message);
                $state.reload();
			}else{
				toastr.error(result.data.message);
			}
		}),function(errorCallback){
			if (errorCallback.status == 400) {
				toastr.success(errorCallback.data.message);
			}
		}
	};
	}
);
