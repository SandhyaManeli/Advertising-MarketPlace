angular.module("bbManager").controller(
	"AdminCampaignCtrl",
	function (
		$scope,
		$mdDialog,
		$mdSidenav,
		$stateParams,
		$location,
		Upload,
		config,
		$rootScope,
		CampaignService,
		AdminCampaignService,
		AdminContactService,
		ProductService,
		toastr,
		FileSaver,
		Blob,
		$window,
		$state,
		FileSaver,
	) {
		// AdminMetroService,MetroService
		$scope.newDate = new Date();
		$scope.CAMPAIGN_STATUS = [
			"campaign-preparing", //    100
			"campaign-created", //    200
			"quote-requested", //    300
			"quote-given", //    400
			"change-requested", //    500
			"booking-requested", //    600
			"Sold", //    700
			"suspended", //    800
			"stopped", //    900
		];

		/*===================================
      | Popups and Sidenavs
    ===================================*/
		$scope.AddMetroCampaign = function () {
			$mdSidenav("metroAddCmapginSidenav").toggle();
		};
		$scope.toggleAddMetroProductSidenav = function () {
			$mdSidenav("add-metro-product-sidenav").toggle();
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
					: lowest + (pageLinks - 1);
			$scope.pagination.pageArray = _.range(lowest, highest + 1);
		}

		$scope.offerStatus = {
			10: "Requested",
			20: "Accepted",
			30: "Accepted",
			40: "Rejected",
			50: "Rejected",
		};

        /*===================
    | Pagination Ends
    ===================*/

        /*===================================
      | Popups and Sidenavs end
      ===================================*/
        /*
      var isRunning = function(startDt, endDt) {
        var startDate = new Date(startDt);
        var endDate = new Date(endDt) ;
        var today = new Date(new Date().toISOString());
        return (today >= startDate && today <= endDate);
      };
      var isClosed = function(endDt) {
        var endDate = new Date(endDt) ;
        var today = new Date(new Date().toISOString());
        return today > endDate;
      };
      */
	 var tab_status_final = 'insertion_order';
	if($state.current.url == '/paid-payment'){
		tab_status_final = 'payments';
		$scope.tabHandle = function (tab_status = "payments") {
			getAllCampaigns(tab_status);
		};
	}else{
		tab_status_final = 'insertion_order';
		$scope.tabHandle = function (tab_status = "insertion_order") {
			getAllCampaigns(tab_status);
		};
	}
		var getAllCampaigns = function (tab_status = tab_status_final) {
			$rootScope.http2_loading = true;
			try {
				AdminCampaignService.getAllCampaigns(tab_status).then(function (
					result
				) {
					$scope.adminCampaigns = result.admin_campaigns;
					$scope.plannedCampaignsWithPriority = result.user_campaigns;
					$scope.priorityCampaignsArray = [];
					$scope.plannedCampaigns = [];
					angular.forEach(
						$scope.plannedCampaignsWithPriority,
						function (item) {
							if (item.colorcode == "red") {
								$scope.priorityCampaignsArray.push(item);
							} else {
								$scope.plannedCampaigns.push(item);
								// $scope.plannedCampaigns = _.filter($scope.plannedCampaigns, function (c) {
								//   return c.status == 400 || c.status == 500 || c.status == 600;
								// });
							}
						}
					);
					/*
        $scope.priorityCampaignsArray = _.filter($scope.priorityCampaignsArray, function (c) {
          return  c.status == 400 || c.status == 500 || c.status == 600; 
        });
        $scope.adminCampaign = _.filter($scope.adminCampaigns, function (c) {
          return c.status == 100 || c.status == 600; 
        });
        $scope.scheduledCampaigns = _.filter(result.user_campaigns, function (c) {   
        return (c.status == 700 && HelperService.Campaign.isScheduled(c.start_date, c.end_date));
        });
        $scope.scheduledCampaign = _.filter(result.admin_campaigns, function (c) {  
          return (c.status == 700 && HelperService.Campaign.isScheduled(c.start_date, c.end_date));
        });
        $scope.runningCampaigns = _.filter(result.user_campaigns, function (c) {
            return (c.status == 800 && HelperService.Campaign.isRunning(c.start_date, c.end_date));
        });
        $scope.runningCampaign = _.filter(result.admin_campaigns, function (c) {
          return (c.status == 800 && HelperService.Campaign.isRunning(c.start_date, c.end_date));
        });
        $scope.closedCampaigns = _.filter(result.user_campaigns, function (c) {
          return (c.status == 1000 || c.status == 900 || HelperService.Campaign.isClosed(c.end_date));
        });
        $scope.closedCampaign = _.filter(result.admin_campaigns, function (c) {
          return (c.status == 1000 || c.status == 900 || HelperService.Campaign.isClosed(c.end_date));
        });
        */
					// $scope.runningCampaigns = _.where(result.user_campaigns, { status: _.indexOf($scope.CAMPAIGN_STATUS, 'booked') });
					// $scope.closedCampaigns = _.where(result.user_campaigns, { status: _.indexOf($scope.CAMPAIGN_STATUS, 'stopped') });
					switch (tab_status) {
						case "scheduled":
							$scope.scheduledCampaigns = result.user_campaigns;
							break;
						case "running":
							$scope.runningCampaigns = result.user_campaigns;
							break;
						case "closed":
							$scope.closedCampaigns = result.user_campaigns;
							break;
						case "payments":
							$scope.userpayments = result.user_campaigns;
							break;
					}
					$scope.adminCampaigns = result.admin_campaigns;
					$rootScope.http2_loading = false;
				});
			} catch (error) {
				console.log(error);
				$rootScope.http2_loading = false;
			}
		};
		getAllCampaigns();

		

		$scope.addToCampaign = function (products) {
			console.log("Bulk Shortlist");
			console.log(products);
			if ($stateParams.campaignId) {
				var payload = [];
				products.forEach(function (product) {
					var postObj = {
						campaign_id: $stateParams.campaignId,
						booked_slots: 1,
						product: {
							id: product.id,
							booking_dates: [
								{
									startDate: $scope.product.start_date,
									endDate: $scope.product.end_date,
								},
							],
							price: product.rateCard,
						},
					};
					payload.push(postObj);
				});

				AdminCampaignService.allCampaignRequests(payload).then(
					function (result) {
						console.log(result);
						console.log(result);
						if (result && result.status) {
							console.log("success");
							toastr.success(result.message);
							$scope.removeSelection();
							CampaignService.getCampaignWithProducts(
								$stateParams.campaignId
							).then(function (result) {
								$scope.campaignDetails = result;
								_.map($scope.AdminProduct, function (product) {
									if (product.id == adminProduct.id) {
										product.alreadyAdded = true;
									}
									return product;
								});
							});
						} else if (result.status == 0) {
							toastr.error(result.message);
						}
					}
				);
			}
		};

		/*=====================
      | Filtering Campaigns
    =====================*/
		// $scope.simulateQuery = false;
		$scope.isDisabled = false;
		// $scope.querySearch   = querySearch;
		// $scope.selectedItemChange = selectedItemChange;
		// $scope.searchTextChange   = searchTextChange;

		$scope.campaignSearch = function (query) {
			return AdminCampaignService.searchCampaigns(
				query.toLowerCase()
			).then(function (res) {
				return res;
			});
		};

		$scope.viewSelectedCampaign = function (campaign) {
			$location.path(
				"/admin/campaign-proposal-summary/" + campaign.id + "/20"
			); //for ADMIN OOH
		};

		function selectedItemChange(item) {}

		/*=========================
      | Filtering Campaigns Ends
    =========================*/
		// $scope.getProductList = function(){
		//   ProductService.getProductList().then(function(result){
		//     $scope.AdminProduct = result.products;
		//     CampaignService.getCampaignWithProducts($stateParams.campaignId).then(function(results){
		//       _.map($scope.AdminProduct, function (product) {
		//             /*if (product.id == (result.products)) {
		//                 product.alreadyAdded = true;
		//             }*/
		//             //alert("FD1");
		//             if (Object.values(results.products).indexOf(product.id) > -1) {
		//               //alert("FDg");
		//                 product.alreadyAdded = true;
		//            }
		//             return product;
		//         });
		//     });

		//   });
		// }
		$scope.getProductList = function () {
			$scope.searchText = null;
			ProductService.getProductList(
				$scope.pagination.pageNo,
				$scope.pagination.pageSize
			).then(function (result) {
				$scope.AdminProduct = result.products;
				$scope.productList = result.products;
				$scope.pagination.pageCount = result.page_count;
				createPageLinks();
			});
		};
		$scope.getProductList();
		$scope.saveCampaignByAdmin = function (AdminownerCampaign) {
			AdminCampaignService.saveCampaignByAdmin(AdminownerCampaign).then(
				function (result) {
					if (result.status == 1) {
						getAllCampaigns();
						toastr.success(result.message);
						$mdDialog.hide();
					} else if (result.status == 0) {
						$scope.campaignDetailsErrorEessages = result.message;
					}
					$scope.AdminownerCampaign = {};
					myFunction();
				},
				function (result) {
					$scope.campaignDetailsErrorEessages =
						"somthing went wrong please try again after some time!";
				}
			);
		};
		function myFunction() {
			document.getElementById("myDropdown").classList.toggle("show");
		}
		$scope.deleteUserCampaign = function (campaignId) {
			AdminCampaignService.deleteUserCampaign(campaignId).then(
				function (result) {
					if (result.status == 1) {
						getAllCampaigns();
						toastr.success(result.message);
						$mdDialog.hide();
					} else if (result.status == 0) {
						toastr.error(result.message);
					}
				},
				function (result) {
					toastr.error(
						"somthing went wrong please try again after some time!"
					);
				}
			);
		};

		$scope.deleteNonUserCampaign = function (campaignId) {
			AdminCampaignService.deleteNonUserCampaign(campaignId).then(
				function (result) {
					if (result.status == 1) {
						getAllCampaigns();
						toastr.success(result.message);
						$mdDialog.hide();
					} else if (result.status == 0) {
						toastr.error(result.message);
					}
				},
				function (result) {
					toastr.error(
						"somthing went wrong please try again after some time!"
					);
				}
			);
		};
		/*
		 *========= campaign proposal(planned) grid =========
		 */
		// filter-code
		$scope.viewSelectedProduct = function (product) {
			$scope.pagination.pageCount = 1;
			$scope.productList = [product];
		};
		$scope.productSearch = function (query) {
			return ProductService.searchProducts(query.toLowerCase()).then(
				function (res) {
					$scope.productList = res;
					$scope.pagination.pageCount = 1;
					return res;
				}
			);
		};

		$scope.isFiltered = false;
		$scope.applymethod = function (product) {
			ProductService.getProductList(
				$scope.pagination.pageNo,
				$scope.pagination.pageSize,
				product.type,
				product.budgetprice
			).then(function (result) {
				$scope.productList = result.products;
				$scope.pagination.pageCount = result.page_count;
				if ($window.innerWidth >= 420) {
					createPageLinks();
				} else {
					$scope.getRange(0, result.page_count);
				}
				if (product.start_date && product.end_date) {
					$scope.isFiltered = true;
				}
			});
		};

		$scope.clearAdminProductFi = function (product) {
			$scope.product = {};

			$scope.getProductList("All");
			$scope.product.type = $scope.ProductTypes[0];
			$scope.product.formType = $scope.ProductTypesFilter[0];
			$scope.product.audited = "No";

			$scope.isFiltered = false;
		};
		// $scope.applymethod = function (product) {
		//   var data = {};
		//   var pageNo = $scope.pagination.pageNo;
		//   var pageSize = $scope.pagination.pageSize;
		//   var format = product.type;
		//   var budget = product.budgetprice;
		//   var start_date = product.start_date;
		//   var end_date = product.end_date;
		//   if (!format) {
		//       format = '';
		//   }
		//   if (!budget) {
		//       budget = '';
		//   }
		//   if (pageNo || pageSize || format || budget || start_date || end_date) {
		//       data.page_no = pageNo;
		//       data.page_size = pageSize;
		//       data.format = format;
		//       data.budget = budget;
		//       data.start_date = start_date;
		//       data.end_date = end_date;
		//   }
		//   ProductService.getProductList(data).then(function (result) {
		//       $scope.productList = result.products;
		//       $scope.pagination.pageCount = result.page_count;
		//       if ($window.innerWidth >= 420) {
		//           createPageLinks();
		//       } else {
		//           $scope.getRange(0, result.page_count);
		//       }
		//   });
		// }
		var getFormatList = function () {
			this.loading = true;
			AdminCampaignService.getFormatList().then(function (result) {
				$scope.formatList = result;
				this.loading = false;
			});
		};
		getFormatList();

		var userOffers = function () {
			this.loading = true;
			AdminCampaignService.userOffers().then(function (result) {
				$scope.OffersData = result;
				this.loading = false;
			});
		};
		userOffers();

		// Sorting for user ooh insertion order
		$scope.sortAsci = function (headingName, type) {
			$scope.upArrowColour = headingName;
			$scope.sortType = "Asci";
			if (type == "string") {
				$scope.newOfferData = $scope.plannedCampaigns.map((e) => {
					return {
						...e,
						firstName: e.firstName,
						email: e.email,
					};
				});
				$scope.plannedCampaigns = [];
				$scope.plannedCampaigns = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (a[headingName] != undefined) {
						return a[headingName].localeCompare(
							b[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});
				//prority campaign aray
				$scope.newPriorityData = $scope.priorityCampaignsArray.map(
					(e) => {
						return {
							...e,
							firstName: e.firstName,
							email: e.email,
						};
					}
				);
				$scope.priorityCampaignsArray = [];
				$scope.priorityCampaignsArray = $scope.newPriorityData.sort(
					(a, b) => {
						console.log(a[headingName]);
						if (a[headingName] != undefined) {
							return a[headingName].localeCompare(
								b[headingName],
								undefined,
								{
									numeric: true,
									sensitivity: "base",
								}
							);
						}
					}
				);
				// $scope.offersData = $scope.newOfferData;
			}
			$scope.plannedCampaigns = $scope.plannedCampaigns.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? 1 : -1;
				} else if (type == "date") {
					return (
						new Date(b[headingName].date) -
						new Date(a[headingName].date)
					);
				} else {
					return a[headingName] - b[headingName];
				}
			});
			console.log($scope.plannedCampaigns);
			//priority
			$scope.priorityCampaignsArray = $scope.priorityCampaignsArray.sort(
				(a, b) => {
					if (type == "boolean") {
						return a[headingName] ? 1 : -1;
					} else if (type == "date") {
						return (
							new Date(b[headingName].date) -
							new Date(a[headingName].date)
						);
					} else {
						return a[headingName] - b[headingName];
					}
				}
			);
			console.log($scope.priorityCampaignsArray);
		};
		$scope.sortDsci = function (headingName, type) {
			$scope.downArrowColour = headingName;
			$scope.sortType = "Dsci";
			if (type == "string") {
				$scope.newOfferData = $scope.plannedCampaigns.map((e) => {
					return {
						...e,
						firstName: e.firstName,
						email: e.email,
					};
				});
				$scope.plannedCampaigns = [];
				$scope.plannedCampaigns = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (b[headingName] != undefined) {
						return b[headingName].localeCompare(
							a[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});
				//prioority
				$scope.newPriorityData = $scope.priorityCampaignsArray.map(
					(e) => {
						return {
							...e,
							firstName: e.firstName,
							email: e.email,
						};
					}
				);
				$scope.priorityCampaignsArray = [];
				$scope.priorityCampaignsArray = $scope.newPriorityData.sort(
					(a, b) => {
						console.log(a[headingName]);
						if (b[headingName] != undefined) {
							return b[headingName].localeCompare(
								a[headingName],
								undefined,
								{
									numeric: true,
									sensitivity: "base",
								}
							);
						}
					}
				);
				// $scope.offersData = $scope.newOfferData;
			}
			$scope.plannedCampaigns = $scope.plannedCampaigns.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? -1 : 1;
				} else if (type == "date") {
					return (
						new Date(b[headingName].date) -
						new Date(a[headingName].date)
					);
				} else {
					return b[headingName] - a[headingName];
				}
			});
			console.log($scope.plannedCampaigns);
			//priority
			$scope.priorityCampaignsArray = $scope.priorityCampaignsArray.sort(
				(a, b) => {
					if (type == "boolean") {
						return a[headingName] ? -1 : 1;
					} else if (type == "date") {
						return (
							new Date(b[headingName].date) -
							new Date(a[headingName].date)
						);
					} else {
						return b[headingName] - a[headingName];
					}
				}
			);
		};

		// Ends

		// Sorting for admin ooh insertion order
		$scope.sortAscia = function (headingName, type) {
			$scope.upArrowColour = headingName;
			$scope.sortType = "Ascia";
			if (type == "string") {
				$scope.newOfferData = $scope.adminCampaign.map((e) => {
					return {
						...e,
						firstName: e.firstName,
						email: e.email,
					};
				});
				$scope.adminCampaign = [];
				$scope.adminCampaign = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (a[headingName] != undefined) {
						return a[headingName].localeCompare(
							b[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.offersData = $scope.newOfferData;
			}
			$scope.adminCampaign = $scope.adminCampaign.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? 1 : -1;
				} else if (type == "date") {
					return (
						new Date(b[headingName].date) -
						new Date(a[headingName].date)
					);
				} else {
					return a[headingName] - b[headingName];
				}
			});
			console.log($scope.adminCampaign);
		};
		$scope.sortDscia = function (headingName, type) {
			$scope.downArrowColour = headingName;
			$scope.sortType = "Dscia";
			if (type == "string") {
				$scope.newOfferData = $scope.adminCampaign.map((e) => {
					return {
						...e,
						firstName: e.firstName,
						email: e.email,
					};
				});
				$scope.adminCampaign = [];
				$scope.adminCampaign = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (b[headingName] != undefined) {
						return b[headingName].localeCompare(
							a[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.offersData = $scope.newOfferData;
			}
			$scope.adminCampaign = $scope.adminCampaign.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? -1 : 1;
				} else if (type == "date") {
					return (
						new Date(b[headingName].date) -
						new Date(a[headingName].date)
					);
				} else {
					return b[headingName] - a[headingName];
				}
			});
			console.log($scope.adminCampaign);
		};

		// Ends

		// Sorting for user ooh sechudeled
		$scope.sortAscis = function (headingName, type) {
			$scope.upArrowColour = headingName;
			$scope.sortType = "Ascis";
			if (type == "string") {
				$scope.newOfferData = $scope.scheduledCampaigns.map((e) => {
					return {
						...e,
						firstName: e.firstName,
						email: e.email,
					};
				});
				$scope.scheduledCampaigns = [];
				$scope.scheduledCampaigns = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (a[headingName] != undefined) {
						return a[headingName].localeCompare(
							b[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.offersData = $scope.newOfferData;
			}
			$scope.scheduledCampaigns = $scope.scheduledCampaigns.sort(
				(a, b) => {
					if (type == "boolean") {
						return a[headingName] ? 1 : -1;
					} else if (type == "date") {
						return (
							new Date(b[headingName].date) -
							new Date(a[headingName].date)
						);
					} else {
						return a[headingName] - b[headingName];
					}
				}
			);
			console.log($scope.scheduledCampaigns);
		};
		$scope.sortDscis = function (headingName, type) {
			$scope.downArrowColour = headingName;
			$scope.sortType = "Dscis";
			if (type == "string") {
				$scope.newOfferData = $scope.scheduledCampaigns.map((e) => {
					return {
						...e,
						firstName: e.firstName,
						email: e.email,
					};
				});
				$scope.scheduledCampaigns = [];
				$scope.scheduledCampaigns = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (b[headingName] != undefined) {
						return b[headingName].localeCompare(
							a[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.offersData = $scope.newOfferData;
			}
			$scope.scheduledCampaigns = $scope.scheduledCampaigns.sort(
				(a, b) => {
					if (type == "boolean") {
						return a[headingName] ? -1 : 1;
					} else if (type == "date") {
						return (
							new Date(b[headingName].date) -
							new Date(a[headingName].date)
						);
					} else {
						return b[headingName] - a[headingName];
					}
				}
			);
			console.log($scope.scheduledCampaigns);
		};

		// Ends

		// Sorting for user ooh sechudeled
		$scope.sortAscias = function (headingName, type) {
			$scope.upArrowColour = headingName;
			$scope.sortType = "Ascias";
			if (type == "string") {
				$scope.newOfferData = $scope.scheduledCampaign.map((e) => {
					return {
						...e,
						firstName: e.firstName,
						email: e.email,
					};
				});
				$scope.scheduledCampaign = [];
				$scope.scheduledCampaign = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (a[headingName] != undefined) {
						return a[headingName].localeCompare(
							b[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.offersData = $scope.newOfferData;
			}
			$scope.scheduledCampaign = $scope.scheduledCampaign.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? 1 : -1;
				} else if (type == "date") {
					return (
						new Date(b[headingName].date) -
						new Date(a[headingName].date)
					);
				} else {
					return a[headingName] - b[headingName];
				}
			});
			console.log($scope.scheduledCampaign);
		};
		$scope.sortDscias = function (headingName, type) {
			$scope.downArrowColour = headingName;
			$scope.sortType = "Dscias";
			if (type == "string") {
				$scope.newOfferData = $scope.scheduledCampaign.map((e) => {
					return {
						...e,
						firstName: e.firstName,
						email: e.email,
					};
				});
				$scope.scheduledCampaign = [];
				$scope.scheduledCampaign = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (b[headingName] != undefined) {
						return b[headingName].localeCompare(
							a[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.offersData = $scope.newOfferData;
			}
			$scope.scheduledCampaign = $scope.scheduledCampaign.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? -1 : 1;
				} else if (type == "date") {
					return (
						new Date(b[headingName].date) -
						new Date(a[headingName].date)
					);
				} else {
					return b[headingName] - a[headingName];
				}
			});
			console.log($scope.scheduledCampaign);
		};

		// Ends

		// Sorting for user ooh running
		$scope.sortAscisr = function (headingName, type) {
			$scope.upArrowColour = headingName;
			$scope.sortType = "Ascisr";
			if (type == "string") {
				$scope.newOfferData = $scope.runningCampaigns.map((e) => {
					return {
						...e,
						firstName: e.firstName,
						email: e.email,
					};
				});
				$scope.runningCampaigns = [];
				$scope.runningCampaigns = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (a[headingName] != undefined) {
						return a[headingName].localeCompare(
							b[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.offersData = $scope.newOfferData;
			}
			$scope.runningCampaigns = $scope.runningCampaigns.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? 1 : -1;
				} else if (type == "date") {
					return (
						new Date(b[headingName].date) -
						new Date(a[headingName].date)
					);
				} else {
					return a[headingName] - b[headingName];
				}
			});
			console.log($scope.runningCampaigns);
		};
		$scope.sortDscisr = function (headingName, type) {
			$scope.downArrowColour = headingName;
			$scope.sortType = "Dscisr";
			if (type == "string") {
				$scope.newOfferData = $scope.runningCampaigns.map((e) => {
					return {
						...e,
						firstName: e.firstName,
						email: e.email,
					};
				});
				$scope.runningCampaigns = [];
				$scope.runningCampaigns = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (b[headingName] != undefined) {
						return b[headingName].localeCompare(
							a[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.offersData = $scope.newOfferData;
			}
			$scope.runningCampaigns = $scope.runningCampaigns.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? -1 : 1;
				} else if (type == "date") {
					return (
						new Date(b[headingName].date) -
						new Date(a[headingName].date)
					);
				} else {
					return b[headingName] - a[headingName];
				}
			});
			console.log($scope.runningCampaigns);
		};

		// Ends

		// Sorting for admin ooh running
		$scope.sortAsciasr = function (headingName, type) {
			$scope.upArrowColour = headingName;
			$scope.sortType = "Asciasr";
			if (type == "string") {
				$scope.newOfferData = $scope.runningCampaign.map((e) => {
					return {
						...e,
						firstName: e.firstName,
						email: e.email,
					};
				});
				$scope.runningCampaign = [];
				$scope.runningCampaign = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (a[headingName] != undefined) {
						return a[headingName].localeCompare(
							b[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.offersData = $scope.newOfferData;
			}
			$scope.runningCampaign = $scope.runningCampaign.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? 1 : -1;
				} else if (type == "date") {
					return (
						new Date(b[headingName].date) -
						new Date(a[headingName].date)
					);
				} else {
					return a[headingName] - b[headingName];
				}
			});
			console.log($scope.runningCampaign);
		};
		$scope.sortDsciasr = function (headingName, type) {
			$scope.downArrowColour = headingName;
			$scope.sortType = "Dsciasr";
			if (type == "string") {
				$scope.newOfferData = $scope.runningCampaign.map((e) => {
					return {
						...e,
						firstName: e.firstName,
						email: e.email,
					};
				});
				$scope.runningCampaign = [];
				$scope.runningCampaign = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (b[headingName] != undefined) {
						return b[headingName].localeCompare(
							a[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.offersData = $scope.newOfferData;
			}
			$scope.runningCampaign = $scope.runningCampaign.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? -1 : 1;
				} else if (type == "date") {
					return (
						new Date(b[headingName].date) -
						new Date(a[headingName].date)
					);
				} else {
					return b[headingName] - a[headingName];
				}
			});
			console.log($scope.runningCampaign);
		};

		// Ends

		// Sorting for user ooh closed
		$scope.sortAscisrc = function (headingName, type) {
			$scope.upArrowColour = headingName;
			$scope.sortType = "Ascisrc";
			if (type == "string") {
				$scope.newOfferData = $scope.closedCampaigns.map((e) => {
					return {
						...e,
						firstName: e.firstName,
						email: e.email,
					};
				});
				$scope.closedCampaigns = [];
				$scope.closedCampaigns = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (a[headingName] != undefined) {
						return a[headingName].localeCompare(
							b[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.offersData = $scope.newOfferData;
			}
			$scope.closedCampaigns = $scope.closedCampaigns.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? 1 : -1;
				} else if (type == "date") {
					return (
						new Date(b[headingName].date) -
						new Date(a[headingName].date)
					);
				} else {
					return a[headingName] - b[headingName];
				}
			});
			console.log($scope.closedCampaigns);
		};
		$scope.sortDscisrc = function (headingName, type) {
			$scope.downArrowColour = headingName;
			$scope.sortType = "Dscisrc";
			if (type == "string") {
				$scope.newOfferData = $scope.closedCampaigns.map((e) => {
					return {
						...e,
						firstName: e.firstName,
						email: e.email,
					};
				});
				$scope.closedCampaigns = [];
				$scope.closedCampaigns = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (b[headingName] != undefined) {
						return b[headingName].localeCompare(
							a[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.offersData = $scope.newOfferData;
			}
			$scope.closedCampaigns = $scope.closedCampaigns.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? -1 : 1;
				} else if (type == "date") {
					return (
						new Date(b[headingName].date) -
						new Date(a[headingName].date)
					);
				} else {
					return b[headingName] - a[headingName];
				}
			});
			console.log($scope.closedCampaigns);
		};

		// Ends

		// Sorting for user ooh closed
		$scope.sortAsciasrc = function (headingName, type) {
			$scope.upArrowColour = headingName;
			$scope.sortType = "Asciasrc";
			if (type == "string") {
				$scope.newOfferData = $scope.closedCampaign.map((e) => {
					return {
						...e,
						firstName: e.firstName,
						email: e.email,
					};
				});
				$scope.closedCampaign = [];
				$scope.closedCampaign = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (a[headingName] != undefined) {
						return a[headingName].localeCompare(
							b[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.offersData = $scope.newOfferData;
			}
			$scope.closedCampaign = $scope.closedCampaign.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? 1 : -1;
				} else if (type == "date") {
					return (
						new Date(b[headingName].date) -
						new Date(a[headingName].date)
					);
				} else {
					return a[headingName] - b[headingName];
				}
			});
			console.log($scope.closedCampaign);
		};
		$scope.sortDsciasrc = function (headingName, type) {
			$scope.downArrowColour = headingName;
			$scope.sortType = "Dsciasrc";
			if (type == "string") {
				$scope.newOfferData = $scope.closedCampaign.map((e) => {
					return {
						...e,
						firstName: e.firstName,
						email: e.email,
					};
				});
				$scope.closedCampaign = [];
				$scope.closedCampaign = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (b[headingName] != undefined) {
						return b[headingName].localeCompare(
							a[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.offersData = $scope.newOfferData;
			}
			$scope.closedCampaign = $scope.closedCampaign.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? -1 : 1;
				} else if (type == "date") {
					return (
						new Date(b[headingName].date) -
						new Date(a[headingName].date)
					);
				} else {
					return b[headingName] - a[headingName];
				}
			});
			console.log($scope.closedCampaign);
		};

		// Ends

		// Sorting for user ooh Delete Campaign
		$scope.sortAscisrcd = function (headingName, type) {
			$scope.upArrowColour = headingName;
			$scope.sortType = "Ascisrcd";
			if (type == "string") {
				$scope.newOfferData = $scope.RequestData.map((e) => {
					return {
						...e,
						firstName: e.loggedinUser.firstName,
						email: e.loggedinUser.email,
					};
				});
				$scope.RequestData = [];
				$scope.RequestData = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (a[headingName] != undefined) {
						return a[headingName].localeCompare(
							b[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.offersData = $scope.newOfferData;
			}
			$scope.RequestData = $scope.RequestData.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? 1 : -1;
				} else if (type == "date") {
					return (
						new Date(b[headingName].date) -
						new Date(a[headingName].date)
					);
				} else {
					return a[headingName] - b[headingName];
				}
			});
			console.log($scope.RequestData);
		};
		$scope.sortDscisrcd = function (headingName, type) {
			$scope.downArrowColour = headingName;
			$scope.sortType = "Dscisrcd";
			if (type == "string") {
				$scope.newOfferData = $scope.RequestData.map((e) => {
					return {
						...e,
						firstName: e.loggedinUser.firstName,
						email: e.loggedinUser.email,
					};
				});
				$scope.RequestData = [];
				$scope.RequestData = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (b[headingName] != undefined) {
						return b[headingName].localeCompare(
							a[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.offersData = $scope.newOfferData;
			}
			$scope.RequestData = $scope.RequestData.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? -1 : 1;
				} else if (type == "date") {
					return (
						new Date(b[headingName].date) -
						new Date(a[headingName].date)
					);
				} else {
					return b[headingName] - a[headingName];
				}
			});
			console.log($scope.RequestData);
		};

		// Ends

		// Sorting for user ooh Delete Campaign
		$scope.sortAscisrcdp = function (headingName, type) {
			$scope.upArrowColour = headingName;
			$scope.sortType = "Ascisrcdp";
			if (type == "string") {
				$scope.newOfferData = $scope.ProductData.map((e) => {
					return {
						...e,
						firstName: e.loggedinUser.firstName,
						email: e.loggedinUser.email,
					};
				});
				$scope.ProductData = [];
				$scope.ProductData = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (a[headingName] != undefined) {
						return a[headingName].localeCompare(
							b[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.offersData = $scope.newOfferData;
			}
			$scope.ProductData = $scope.ProductData.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? 1 : -1;
				} else if (type == "date") {
					return (
						new Date(b[headingName].date) -
						new Date(a[headingName].date)
					);
				} else {
					return a[headingName] - b[headingName];
				}
			});
			console.log($scope.ProductData);
		};
		$scope.sortDscisrcdp = function (headingName, type) {
			$scope.downArrowColour = headingName;
			$scope.sortType = "Dscisrcdp";
			if (type == "string") {
				$scope.newOfferData = $scope.ProductData.map((e) => {
					return {
						...e,
						firstName: e.loggedinUser.firstName,
						email: e.loggedinUser.email,
					};
				});
				$scope.ProductData = [];
				$scope.ProductData = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (b[headingName] != undefined) {
						return b[headingName].localeCompare(
							a[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.offersData = $scope.newOfferData;
			}
			$scope.ProductData = $scope.ProductData.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? -1 : 1;
				} else if (type == "date") {
					return (
						new Date(b[headingName].date) -
						new Date(a[headingName].date)
					);
				} else {
					return b[headingName] - a[headingName];
				}
			});
			console.log($scope.ProductData);
		};

		// Ends

		$scope.sortAsc = function (headingName, type) {
			$scope.upArrowColour = headingName;
			$scope.sortType = "Asc";
			if (type == "string") {
				$scope.newOfferData = $scope.OffersData.map((e) => {
					return {
						...e,
						firstName: e.loggedinUser.firstName,
						email: e.loggedinUser.email,
					};
				});
				$scope.offersData = [];
				$scope.OffersData = $scope.newOfferData.sort((a, b) => {
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

				// $scope.offersData = $scope.newOfferData;
			}
			$scope.OffersData = $scope.OffersData.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? 1 : -1;
				} else {
					return a[headingName] - b[headingName];
				}
			});
			console.log($scope.OffersData);
		};
		$scope.sortDsc = function (headingName, type) {
			$scope.downArrowColour = headingName;
			$scope.sortType = "Dsc";
			if (type == "string") {
				$scope.newOfferData = $scope.OffersData.map((e) => {
					return {
						...e,
						firstName: e.loggedinUser.firstName,
						email: e.loggedinUser.email,
					};
				});
				$scope.offersData = [];
				$scope.OffersData = $scope.newOfferData.sort((a, b) => {
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

				// $scope.offersData = $scope.newOfferData;
			}
			$scope.OffersData = $scope.OffersData.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? -1 : 1;
				} else {
					return b[headingName] - a[headingName];
				}
			});
			console.log($scope.OffersData);
		};

		// SORTING FOR RFP PAGE

		$scope.sortAscc = function (headingName, type) {
			$scope.upArrowColour = headingName;
			$scope.sortType = "Ascc";
			if (type == "string") {
				$scope.newOfferData = $scope.RfpData.map((e) => {
					return {
						...e,
						name: e.name,
						user_email: e.user_email,
					};
				});
				$scope.RfpData = [];
				$scope.RfpData = $scope.newOfferData.sort((a, b) => {
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

				// $scope.RfpData = $scope.newOfferData;
			}
			$scope.RfpData = $scope.RfpData.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? 1 : -1;
				} else {
					return a[headingName] - b[headingName];
				}
			});
			console.log($scope.RfpData);
		};
		$scope.sortDscc = function (headingName, type) {
			$scope.downArrowColour = headingName;
			$scope.sortType = "Dscc";
			if (type == "string") {
				$scope.newOfferData = $scope.RfpData.map((e) => {
					return {
						...e,
						name: e.name,
						user_email: e.user_email,
					};
				});
				$scope.RfpData = [];
				$scope.RfpData = $scope.newOfferData.sort((a, b) => {
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
			$scope.RfpData = $scope.RfpData.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? -1 : 1;
				} else {
					return b[headingName] - a[headingName];
				}
			});
			console.log($scope.RfpData);
		};

		//SORTING FOR RFP PAGE ENDS

		// SORTING FOR Refered By
		const sortArrayByField = (ary, field, isAscending) => {
			if (isAscending) {
				$scope.upArrowColour = field;
				$scope.sortType = "Asccr";
				ary.sort((a, b) => {
					if (a[field] > b[field]) return 1;
					if (a[field] < b[field]) return -1;
					return 0;
				});
			} else {
				$scope.downArrowColour = field;
				$scope.sortType = "Dsccr";
				ary.sort((a, b) => {
					if (a[field] > b[field]) return -1;
					if (a[field] < b[field]) return 1;
					return 0;
				});
			}
		};

		// SORTING FOR Refered By
		const sortArrayByFieldf = (ary, field, isAscending) => {
			if (isAscending) {
				$scope.upArrowColour = field;
				$scope.sortType = "Asccrf";
				ary.sort((a, b) => {
					if (a[field] > b[field]) return 1;
					if (a[field] < b[field]) return -1;
					return 0;
				});
			} else {
				$scope.downArrowColour = field;
				$scope.sortType = "Dsccrf";
				ary.sort((a, b) => {
					if (a[field] > b[field]) return -1;
					if (a[field] < b[field]) return 1;
					return 0;
				});
			}
		};

		$scope.sortAsccr = function (headingName, type) {
			sortArrayByField($scope.referredByData, headingName, true);
			/*
    $scope.upArrowColour = headingName;
    $scope.sortType ="Asccr";
    if (type=="string") {
      $scope.newOfferData = $scope.referredByData.map(e=>{
      return {
      ...e,
      name: e.name,
      user_email: e.user_email
      }
      })
      $scope.referredByData = [];
      $scope.referredByData = $scope.newOfferData.sort((a,b) =>{
        console.log(a[headingName])
        return  a[headingName].localeCompare(b[headingName], undefined, {
          numeric: true,
          sensitivity: 'base'
        });
      })
      
      // $scope.RfpData = $scope.newOfferData;
    }
    $scope.referredByData = $scope.referredByData.sort((a,b)=>{
      if(type == 'boolean'){
          return a[headingName] ? 1 : -1 
      }
        else {
          return a[headingName] - b[headingName]
        }
    });
    */
		};

		$scope.sortDsccr = function (headingName, type) {
			sortArrayByField($scope.referredByData, headingName, false);
			/*
    $scope.downArrowColour = headingName;
    $scope.sortType ="Dsccr";
    if (type=="string"){
    $scope.newOfferData = $scope.referredByData.map(e=>{
    return {
    ...e,
    name: e.name,
    user_email: e.user_email
    }
    })
    $scope.referredByData = [];
    $scope.referredByData = $scope.newOfferData.sort((a,b) =>{
      console.log(a[headingName])
      return  b[headingName].localeCompare(a[headingName], undefined, {
        numeric: true,
        sensitivity: 'base'
      });
    })
    
    // $scope.RfpData = $scope.newOfferData;
    }
    $scope.referredByData = $scope.referredByData.sort((a,b)=>{

        if(type == 'boolean'){
          return a[headingName] ? -1 : 1 
      }
      else {
          return  b[headingName] - a[headingName] 
      }
    });
    console.log($scope.referredByData);
    */
		};
		//SORTING FOR Refered By ENDS

		// SORTING FOR Find For Me
		$scope.sortAsccrf = function (headingName, type) {
			/*
    $scope.upArrowColour = headingName;
    $scope.sortType ="Asccrf";
    if (type=="string"){
    $scope.newOfferData = $scope.findData.map(e=>{
    return {
    ...e,
    name: e.name,
    user_email: e.user_email
    }
    })
    $scope.findData = [];
    $scope.findData = $scope.newOfferData.sort((a,b) =>{
      console.log(a[headingName])
      if(b[headingName] != undefined){
      return  a[headingName].localeCompare(b[headingName], undefined, {
        numeric: true,
        sensitivity: 'base'
      });
    }
    })
    
    // $scope.RfpData = $scope.newOfferData;
    }
    $scope.findData = $scope.findData.sort((a,b)=>{

        if(type == 'boolean'){
          return a[headingName] ? 1 : -1 
      }
        else {
          return a[headingName] - b[headingName]
        }
    })
    console.log($scope.findData)
    */
			sortArrayByFieldf($scope.findData, headingName, true);
		};

		$scope.sortDsccrf = function (headingName, type) {
			/*
    $scope.downArrowColour = headingName;
    $scope.sortType ="Dsccrf";
    if (type=="string"){
    $scope.newOfferData = $scope.findData.map(e=>{
    return {
    ...e,
    name: e.name,
    user_email: e.user_email
    }
    })
    $scope.findData = [];
    $scope.findData = $scope.newOfferData.sort((a,b) =>{
      console.log(a[headingName])
      if(b[headingName] != undefined){
      return  b[headingName].localeCompare(a[headingName], undefined, {
        numeric: true,
        sensitivity: 'base'
      });
    }
    })
    
    // $scope.RfpData = $scope.newOfferData;
    }
    $scope.findData = $scope.findData.sort((a,b)=>{

          if(type == 'boolean'){
            return a[headingName] ? -1 : 1 
        }
        else {
            return  b[headingName] - a[headingName] 
        }
    })
    console.log($scope.findData)
    */
			sortArrayByFieldf($scope.findData, headingName, false);
		};
		//SORTING FOR Find for me ENDS

		// SORTING FOR Feeds
		$scope.sortAsccrfq = function (headingName, type) {
			$scope.upArrowColour = headingName;
			$scope.sortType = "Asccrfq";
			if (type == "string") {
				$scope.newOfferData = $scope.QueriesData.map((e) => {
					return {
						...e,
						name: e.name,
						user_email: e.user_email,
					};
				});
				$scope.QueriesData = [];
				$scope.QueriesData = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (b[headingName] != undefined) {
						return a[headingName].localeCompare(
							b[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.RfpData = $scope.newOfferData;
			}
			$scope.QueriesData = $scope.QueriesData.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? 1 : -1;
				} else {
					return a[headingName] - b[headingName];
				}
			});
			console.log($scope.QueriesData);
		};
		$scope.sortDsccrfq = function (headingName, type) {
			$scope.downArrowColour = headingName;
			$scope.sortType = "Dsccrfq";
			if (type == "string") {
				$scope.newOfferData = $scope.QueriesData.map((e) => {
					return {
						...e,
						name: e.name,
						user_email: e.user_email,
					};
				});
				$scope.QueriesData = [];
				$scope.QueriesData = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (b[headingName] != undefined) {
						return b[headingName].localeCompare(
							a[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.RfpData = $scope.newOfferData;
			}
			$scope.QueriesData = $scope.QueriesData.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? -1 : 1;
				} else {
					return b[headingName] - a[headingName];
				}
			});
			console.log($scope.QueriesData);
		};

		//SORTING FOR Feeds ENDS

		// SORTING FOR Admin Payments

		$scope.sortAsccrfqa = function (headingName, type) {
			$scope.upArrowColour = headingName;
			$scope.sortType = "Asccrfqa";
			if (type == "string") {
				$scope.newOfferData = $scope.adminpayments.map((e) => {
					return {
						...e,
						name: e.name,
						user_email: e.user_email,
					};
				});
				$scope.adminpayments = [];
				$scope.adminpayments = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (b[headingName] != undefined) {
						return a[headingName].localeCompare(
							b[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.RfpData = $scope.newOfferData;
			}
			$scope.adminpayments = $scope.adminpayments.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? 1 : -1;
				} else {
					return a[headingName] - b[headingName];
				}
			});
			console.log($scope.adminpayments);
		};
		$scope.sortDsccrfqa = function (headingName, type) {
			$scope.downArrowColour = headingName;
			$scope.sortType = "Dsccrfqa";
			if (type == "string") {
				$scope.newOfferData = $scope.adminpayments.map((e) => {
					return {
						...e,
						name: e.name,
						user_email: e.user_email,
					};
				});
				$scope.adminpayments = [];
				$scope.adminpayments = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (b[headingName] != undefined) {
						return b[headingName].localeCompare(
							a[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.RfpData = $scope.newOfferData;
			}
			$scope.adminpayments = $scope.adminpayments.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? -1 : 1;
				} else {
					return b[headingName] - a[headingName];
				}
			});
			console.log($scope.adminpayments);
		};

		//SORTING FOR admin payments ENDS
		//sorting for user payments
		$scope.sortAsccrfqa = function (headingName, type) {
			$scope.upArrowColour = headingName;
			$scope.sortType = "Asccrfqa";
			if (type == "string") {
				$scope.newOfferData = $scope.userpayments.map((e) => {
					return {
						...e,
						name: e.name,
						user_email: e.user_email,
					};
				});
				$scope.userpayments = [];
				$scope.userpayments = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (b[headingName] != undefined) {
						return a[headingName].localeCompare(
							b[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.RfpData = $scope.newOfferData;
			}
			$scope.userpayments = $scope.userpayments.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? 1 : -1;
				} else {
					return a[headingName] - b[headingName];
				}
			});
			console.log($scope.userpayments);
		};
		$scope.sortDsccrfqa = function (headingName, type) {
			$scope.downArrowColour = headingName;
			$scope.sortType = "Dsccrfqa";
			if (type == "string") {
				$scope.newOfferData = $scope.userpayments.map((e) => {
					return {
						...e,
						name: e.name,
						user_email: e.user_email,
					};
				});
				$scope.userpayments = [];
				$scope.userpayments = $scope.newOfferData.sort((a, b) => {
					console.log(a[headingName]);
					if (b[headingName] != undefined) {
						return b[headingName].localeCompare(
							a[headingName],
							undefined,
							{
								numeric: true,
								sensitivity: "base",
							}
						);
					}
				});

				// $scope.RfpData = $scope.newOfferData;
			}
			$scope.userpayments = $scope.userpayments.sort((a, b) => {
				if (type == "boolean") {
					return a[headingName] ? -1 : 1;
				} else {
					return b[headingName] - a[headingName];
				}
			});
		};
		$scope.localStorageProductId = function (productbookingid) {
			console.log(productbookingid);
			localStorage.setItem(
				"productbookingid",
				JSON.stringify(productbookingid)
			);
		};

		var rfpWithoutLogin = function () {
			$scope.bulkSelect = true;
			$scope.selectedids = [];
			$scope.selectedDupIds = [];
			$rootScope.selectAllChk = false;
			$scope.loading = true;
			AdminCampaignService.rfpWithoutLogin().then(function (result) {
				$rootScope.selectAllChk = false;
				$scope.RfpData = result;
				// $scope.RfpData = result.filter(function(product) {
				//   return product.status == "1300";
				// });
				$scope.RfpData = $scope.RfpData.map((e) => {
					return {
						...e,
						valueChecked: false,
					};
				});
				$scope.loading = false;
			});
		};
		rfpWithoutLogin();
		$scope.selectAll = function (event) {
			var selectedids = [];
			$scope.bulkSelect = false;
			if (event.target.checked) {
				$scope.RfpData = $scope.RfpData.map((e) => {
					if (e.status_read != 1) {
						$scope.selectedids.push(e.id);
						return {
							...e,
							valueChecked: true,
						};
					} else {
						return {
							...e,
						};
					}
				});
			} else {
				$scope.bulkSelect = true;
				$scope.RfpData = $scope.RfpData.map((e) => {
					if (e.status_read != 1) {
						$scope.selectedids.splice(0);
						return {
							...e,
							valueChecked: false,
						};
					} else {
						return {
							...e,
						};
					}
				});
			}
		};
		$scope.selectSingle = function (id, status_read) {
			var Existingid = $scope.selectedids.indexOf(id);
			var ExistingDupId = $scope.selectedDupIds.indexOf(id);
			$scope.bulkSelect = false;
			if (ExistingDupId > -1) {
				$scope.selectedDupIds.splice(ExistingDupId, 1);
				if ($scope.selectedDupIds.length == 0) {
					$scope.bulkSelect = true;
				}
			} else {
				if (status_read == 1) {
					$scope.selectedDupIds.push(id);
				}
			}
			if (Existingid > -1) {
				$scope.selectedids.splice(Existingid, 1);
				if ($scope.selectedids.length == 0) {
					$scope.bulkSelect = true;
				}
			} else {
				if (status_read != 1) {
					$scope.selectedids.push(id);
				}
			}
		};
		$scope.bulkUplodData = function () {
			$scope.updateRfpstatus($scope.selectedids);
			// $scope.valueChecked = false
		};
		$scope.updateRfpstatus = function (selectedCamp) {
			var obj = {
				rfp_ids: selectedCamp,
			};
			AdminCampaignService.updateRfpstatus(obj).then(function (result) {
				// if(result.status_read == 1) {
				//   $scope.rfpWithoutLogin();
				// }
				rfpWithoutLogin();
			});
		};
		var findForMe = function () {
			AdminCampaignService.findForMe().then(function (result) {
				if (result) {
					$scope.findData = result.map((user) => {
						return {
							user_query: user.user_query,
							budget_rfp: user.budget_rfp,
							user_name: (
								user.loggedinUser.firstName +
								" " +
								user.loggedinUser.lastName
							)?.toUpperCase(),
							created_at: user.updated_at,
							campaign_name: user.name,
							campaign_cid: user.cid,
							campaign_id: user.campaign_id,
							updated_at: user.updated_at,
							email: user.loggedinUser.email?.toLowerCase(),
						};
					});
					console.log($scope.findData);
				}
			});
		};
		findForMe();

		var getRequestedCampaigns = function () {
			AdminCampaignService.getRequestedCampaigns().then(function (
				result
			) {
				$scope.RequestData = result;
			});
		};
		getRequestedCampaigns();

		var getProductCampaigns = function () {
			AdminCampaignService.getProductCampaigns().then(function (result) {
				$scope.ProductData = result;
			});
		};
		getProductCampaigns();

		var getAdminCampaigns = function () {
			AdminCampaignService.getAdminCampaigns().then(function (result) {
				$scope.referredByData = [];
				if (result && result.admin_campaigns) {
					$scope.referredByData = result.admin_campaigns;
					if (result.owner_campaigns) {
						$scope.referredByData = $scope.referredByData.concat(
							result.owner_campaigns
						);
					}
				}
			});
		};
		getAdminCampaigns();

		$scope.showCampaignDetails = function ($event, campaign) {
			try {
				//$location.path('/admin/' + $rootScope.clientSlug + '/campaign-proposal-summary/' + campaign.id + "/" + campaign.type);
				var path =
					"/admin/campaign-proposal-summary/" +
					(campaign.campaign_id
						? campaign.campaign_id
						: campaign.id) +
					"/30";
				$location.path(path);
			} catch (ex) {
				alert("exception: " + ex.message);
			}
			//$location.path('/admin/campaign-proposal-summary/' + (campaign.campaign_id?campaign.campaign_id:campaign.id)) + '/3';
		};

		$scope.showRfpCampaignDetails = function ($event, campaign) {
			let selectedId = [];
			selectedId.push(campaign.id);
			$scope.updateRfpstatus(selectedId);
			try {
				//$location.path('/admin/' + $rootScope.clientSlug + '/campaign-proposal-summary/' + campaign.id + "/" + campaign.type);
				var path =
					"/admin/campaign-proposal-summary/" +
					(campaign.campaign_id
						? campaign.campaign_id
						: campaign.id) +
					"/40";
				$location.path(path);
			} catch (ex) {
				alert("exception: " + ex.message);
			}
			//$location.path('/admin/campaign-proposal-summary/' + (campaign.campaign_id?campaign.campaign_id:campaign.id)) + '/3';
		};

		// Filter-code ends
		// Share Campagin
		$scope.shareCampaignToEmail = function (ev, shareCampaign, campaignID) {
			$scope.campaignToShare = $scope.campaignDetails;
			var campaignToEmail = {
				campaign_id: campaignID,
				email: shareCampaign.email,
				receiver_name: shareCampaign.receiver_name,
				//campaign_type: $scope.campaignToShare.type
			};
			CampaignService.shareCampaignToEmail(campaignToEmail).then(
				function (result) {
					if (result.status == 1) {
						$mdSidenav("shareCampaignSidenav").close();
						$mdDialog.show(
							$mdDialog
								.alert()
								.parent(
									angular.element(
										document.querySelector("body")
									)
								)
								.clickOutsideToClose(true)
								.title(result.message)
								// .textContent('You can specify some description text in here.')
								.ariaLabel("Alert Dialog Demo")
								.ok("Confirmed!")
								.targetEvent(ev)
						);
					} else {
						toastr.error(result.message);
					}
				}
			);
		};

		$scope.toggleShareCampaignSidenav = function (campaign) {
			$scope.currentAdminShareCampaign = campaign;
			$mdSidenav("shareCampaignSidenav").toggle();
		};

		// Share Campagin-ends
		/*
    //////// Floating campaign section
    */

		$scope.formRows = [{ formId: "1", name: "floatginCampaignForm1" }];
		$scope.addNewFormRow = function () {
			var newItemNo = $scope.formRows.length + 2;
			$scope.formRows.push({
				formId: newItemNo,
				name: "floatingCampaignForm" + newItemNo,
			});
		};

		$scope.generateFloatingCampaignPdf = function () {
			Upload.upload({
				url: config.apiPath + "/floating-campaign-pdf",
				data: { product_arr: $scope.formRows },
				responseType: "arraybuffer",
			}).then(
				function (result) {
					if (result.data) {
						var campaignPdf = new Blob([result.data], {
							type: "application/pdf;charset=utf-8",
						});
						FileSaver.saveAs(campaignPdf, "Campaigns Proposal.pdf");
					} else {
						toastr.error(result.message);
					}
				},
				function (resp) {},
				function (evt) {
					var progressPercentage = parseInt(
						(100.0 * evt.loaded) / evt.total
					);
				}
			);
		};

		/*
    //////// Floating campaign section ends
    */

		/*====================================
      | Metro Campaigns
    ====================================*/
		var getFormatList = function (obj) {
			ProductService.getFormatList(obj).then(function (result) {
				$scope.formatList = result;
			});
		};
		// function getMetroCorridors() {
		//   AdminMetroService.getMetroCorridors().then(function (result) {
		//     $scope.metroCorridorList = result;
		//     $scope.selectedCorridor = $scope.metroCorridorList[0];
		//     $scope.getMetroPackages($scope.selectedCorridor.id);
		//   });
		// }
		$scope.selectPackage = function (pkg) {
			$scope.selectedPackage = pkg;
		};
		$scope.monthoptions = [
			{ value: ".5", label: "15 Days" },
			{ value: "1", label: "1 Month" },
			{ value: "2", label: "2 Months" },
			{ value: "3", label: "3 Months" },
			{ value: "4", label: "4 Months" },
			{ value: "5", label: "5 Months" },
			{ value: "6", label: "6 Months" },
			{ value: "7", label: "7 Months" },
			{ value: "8", label: "8 Months" },
			{ value: "9", label: "9 Months" },
			{ value: "10", label: "10 Months" },
			{ value: "11", label: "11 Months" },
			{ value: "12", label: "12 Months" },
		];

		// $scope.getMetroPackages = function (corridorId) {
		//   AdminMetroService.getMetroPackages(corridorId).then(function (result) {
		//     _.map(result, (res) => {
		//       res.selected_trains = 1;
		//      // res.selected_slots = 1;
		//      res.months = $scope.monthoptions[0];
		//       return res;
		//     });
		//     $scope.metroPackages = result;
		//     $scope.selectedPackage = result[0];
		//     //$scope.selectedPackage.days = "7";
		//     /*$scope.admin_selected_slots = ($scope.selectedPackage.max_slots * $scope.selectedPackage.days);
		//     $scope.admin_price = ($scope.selectedPackage.price * $scope.selectedPackage.days);*/
		//   });
		// }
		// function getMetroCampaigns() {
		//   AdminMetroService.getMetroCampaigns().then((result) => {
		//     $scope.userMetroCampaigns = _.filter(result, (campaign) => {
		//       return campaign.type == 0;
		//     });
		//     $scope.adminMetroCampaigns = _.filter(result, (campaign) => {
		//       return campaign.type == 1;
		//     });
		//   });
		// }
		// function getMetroCampaignDetails(metroCampaignId) {
		//   AdminMetroService.getMetroCampaignDetails(metroCampaignId).then((result) => {
		//     $scope.metroCampaignDetails = result;
		//     if ($scope.metroCampaignDetails.gst_price != "0") {
		//       $scope.GST = ($scope.metroCampaignDetails.act_budget / 100) * 18;
		//       $scope.TOTAL = $scope.metroCampaignDetails.act_budget + parseInt($scope.GST);
		//     } else {
		//       $scope.GST = "0";
		//       $scope.TOTAL = $scope.metroCampaignDetails.act_budget + parseInt($scope.metroCampaignDetails.gst_price);
		//     }
		//   });
		// }
		$scope.addPackageInMetroCampaign = function (slots, price) {
			$scope.selectedPackage.package_id = $scope.selectedPackage.id;
			$scope.selectedPackage.campaign_id = $scope.metroCampaignDetails.id;
			$scope.selectedPackage.months = $scope.selectedPackage.months.value;
			if (slots) {
				$scope.selectedPackage.admin_slots = slots;
			}
			if (price) {
				$scope.selectedPackage.admin_price = price;
			}
			//$scope.selectedPackage.total_price = $scope.selectedPackage.price * ($scope.selectedPackage.selected_trains + $scope.selectedPackage.selected_slots - 1);
			// AdminMetroService.addPackageInMetroCampaign($scope.selectedPackage).then((result) => {
			//   if (result.status == 1) {
			//     $scope.selectedPackage = {};
			//     getMetroCampaignDetails($scope.metroCampaignDetails.id);
			//     toastr.success(result.message);
			//     $scope.toggleAddMetroProductSidenav();
			//   }
			//   else {
			//     toastr.error(result.message);
			//   }
			// });
		};

		/*$scope.updatePackagePrice = function (price,package1) {
    $scope.package_price = {};
    $scope.package_price = package1;
    $scope.package_price.price = price;
    $scope.package_price.edit_id = package1._id;
    AdminMetroService.addPackageInMetroCampaign($scope.package_price).then((result) => {
      if (result.status == 1) {
        $scope.selectedPackage = {};
        getMetroCampaignDetails($scope.metroCampaignDetails.id);
        toastr.success(result.message);
      }
      else {
        toastr.error(result.message);
      }
    });
    }*/

		$scope.updatePackagePrice = function (price, package1) {
			var productObj = {
				id: $scope.metroCampaignDetails.id,
				start_date: package1.start_date,
				price: price,
				edit_id: package1._id,
			};
			$mdDialog.show({
				locals: {
					campaign: $scope.campaignDetails,
					productObj: productObj,
					ctrlScope: $scope,
				},
				templateUrl: "views/admin/edit-metro-proposed-product.html",
				fullscreen: $scope.customFullscreen,
				clickOutsideToClose: true,
				controller: function (
					$scope,
					$mdDialog,
					CampaignService,
					ctrlScope,
					productObj
				) {
					// AdminMetroService
					$scope.product = productObj;
					$scope.AdminProposalFromMinDate = new Date();
					$scope.AdminProposalStartDate = new Date(
						$scope.product.start_date
					);
					$scope.updateProposedProduct = function (product) {
						/* AdminCampaignService.updateProposedProduct(campaign.id, $scope.product).then(function(result){
            if(result.status == 1){
              // update succeeded. update the grid now.
              $mdDialog.hide();
              CampaignService.getCampaignWithProducts(campaign.id).then(function(result){
                ctrlScope.campaignDetails = result;
                ctrlScope.campaignProducts = result.products;
                // setDatesForAdminProposalToSuggest($scope.campaignDetails);
              });
              toastr.success(result.message);
            }
            else{
              toastr.error(result.message);
            }
          });*/
						// AdminMetroService.addPackageInMetroCampaign(product).then((result) => {
						//   if (result.status == 1) {
						//     $scope.selectedPackage = {};
						//     getMetroCampaignDetails(product.id);
						//     toastr.success(result.message);
						//   }
						//   else {
						//     toastr.error(result.message);
						//   }
						//   $scope.closeMdDialog();
						// });
					};
					$scope.closeMdDialog = function () {
						$mdDialog.hide();
					};
				},
			});
		};
		$scope.showConfirmMetroPaymentPopup = function () {
			$mdDialog.show({
				templateUrl: "views/admin/confirm-metro-payment-popup.html",
				fullscreen: $scope.customFullscreen,
				clickOutsideToClose: true,
				preserveScope: true,
				locals: {
					metroCampaignId: $stateParams.metroCampaignId,
					ctrlScope: $scope,
				},
				controller: function (
					$scope,
					$mdDialog,
					CampaignService,
					AdminCampaignService,
					ctrlScope,
					metroCampaignId
				) {
					$scope.paymentTypes = [
						{ name: "Cash" },
						{ name: "Cheque" },
						{ name: "Online" },
						{ name: "Transfer" },
					];
					$scope.updateCampaignPayment = function () {
						$scope.campaignPayment.metro_campaign_id =
							metroCampaignId;
						AdminCampaignService.updateMetroCampaignStatus(
							$scope.campaignPayment
						).then(function (result) {
							if (result.status == 1) {
								toastr.success(result.message);
								getMetroCampaignDetails(metroCampaignId);
								$scope.closeMdDialog();
								$state.reload();
							} else {
								toastr.error(result.message);
							}
						});
					};
					$scope.closeMdDialog = function () {
						$mdDialog.hide();
					};
				},
			});
		};
		$scope.saveUserCampaign = function (AdminownerCampaign) {
			AdminCampaignService.saveUserCampaign(AdminownerCampaign).then(
				function (result) {
					if (result.status == 1) {
						getAllCampaigns();
						toastr.success(result.message);
					} else if (result.status == 0) {
						$rootScope.closeMdDialog();
						if (result.message.constructor == Array) {
							$scope.adminCampaignErrors = result.message;
						} else {
							toastr.error(result.message);
						}
					} else {
						toastr.error(result.message);
					}
					//myFunction();
				}
			);
		};
		//   function myFunction() {
		//     document.getElementById("myDropdown").classList.toggle("show");
		// }
		// var loadOwnerCampaigns = function () {
		//   return new Promise((resolve, reject) => {
		//     AdminCampaignService.getAllCampaigns().then(function (result) {
		//           $scope.ownerCampaigns = result;
		//           $scope.ownerCampaigns = _.filter(result, function (c) {
		//               return c.status < 800;
		//           });
		//           $scope.scheduledCampaigns = _.filter(result, function (c) {
		//               return c.status >= 800;
		//           });
		//           resolve(result);
		//       });
		//   });
		// }
		$scope.launchMetroCampaign = function (campaignId, ev) {
			AdminCampaignService.launchMetroCampaign(campaignId).then(function (
				result
			) {
				if (result.status == 1) {
					$mdDialog.show(
						$mdDialog
							.alert()
							.parent(
								angular.element(document.querySelector("body"))
							)
							.clickOutsideToClose(true)
							.title("Congrats!!")
							.textContent(result.message)
							.ariaLabel("Alert Dialog Demo")
							.ok("Confirmed!")
							.targetEvent(ev)
					);
					getMetroCampaignDetails(campaignId);
				} else {
					toastr.error(result.message);
				}
			});
		};
		// $scope.saveMetroCampaign = function (campaign) {
		//   MetroService.saveMetroCampaign(campaign).then(function (response) {
		//     if (response.status == 1) {
		//       $scope.campaignSavedSuccessfully = true;
		//       $scope.metroCampaign = {};
		//       $scope.metroCampaignForm.$setPristine();
		//       $scope.metroCampaignForm.$setUntouched();
		//       $scope.campaignSavedSuccessfully = false;
		//       toastr.success(response.message);
		//       adminmetroCampaign();
		//       getMetroCampaigns();
		//       //$window.location.href = '/{{clientSlug}}/metro-campaign/' + result.metro_camp_id;
		//     }
		//     else {
		//       $scope.saveUserCampaignErrors = response.message;
		//       toastr.error(response.message);
		//     }
		//   });
		// }
		function adminmetroCampaign() {
			document.getElementById("adminmetroDrop").classList.toggle("show");
		}
		$scope.closeMetroCampaign = function (campaignId) {
			if (
				$window.confirm("Are you sure you want to close this campaign?")
			) {
				AdminMetroService.closeMetroCampaign(campaignId).then(function (
					response
				) {
					if (response.status == 1) {
						$scope.campaignSavedSuccessfully = true;
						$scope.metroCampaign = {};
						toastr.success(response.message);
						getMetroCampaignDetails(campaignId);
					} else {
						//$scope.saveUserCampaignErrors = response.message;
						toastr.error(response.message);
					}
				});
			} else {
				$scope.Message = "You clicked NO.";
			}
		};
		// $scope.deleteProductFromCampaign = function (campaignId, productId) {
		//   if ($window.confirm("Are you sure you want to delete this package?")) {
		//     MetroService.deleteMetroPackageFromCampaign(campaignId, productId).then(function (result) {
		//       if (result.status == 1) {
		//         getMetroCampaignDetails(campaignId);
		//         loadCampaignPayments($stateParams.metroCampaignId);
		//         toastr.success(result.message);
		//       }
		//       else {
		//         toastr.error(result.message);
		//       }
		//     });
		//   } else {
		//     $scope.Message = "You clicked NO.";
		//   }
		// }
		// $scope.deleteMetroCampaign = function (campaignId) {
		//   if ($window.confirm("Are you really want to delete this camapaign?")) {
		//     CampaignService.deleteMetroCampaign(campaignId).then(function (result) {
		//       if (result.status == 1) {
		//         getMetroCampaigns();
		//         toastr.success(result.message);
		//       }
		//       else {
		//         toastr.error(result.message);
		//       }
		//     });
		//   } else {
		//     $scope.Message = "You clicked NO.";
		//   }
		// }

		/*====================================
      | Metro Campaigns end
    ====================================*/

		$scope.downloadOwnerQuote = function (campaignId) {
			AdminCampaignService.downloadQuote(campaignId).then(function (
				result
			) {
				var campaignPdf = new Blob([result], {
					type: "application/pdf;charset=utf-8",
				});
				FileSaver.saveAs(campaignPdf, "campaigns.pdf");
				if (result.status) {
					toastr.error(result.meesage);
				}
			});
		};

		$scope.cancel = function () {
			$mdDialog.hide();
		};
		// query tab
		AdminContactService.userQuery().then(function (response) {
			$scope.QueriesData = response.data;
		});
		// query tab end
		// AdminCampaignService.getAllCampaigns().then(function (response) {
		//   $scope.adminpayments = response.admin_campaigns;
		//   $scope.userpayments= response.user_campaigns;
		//   angular.forEach($scope.userpayments, function (item,key) {
		//     item['pending']=item.total_price - item.total_paid;
		//   })
		// });
		/*=========================
      | Page based initial loads
    =========================*/
		if ($rootScope.currStateName == "admin.campaign-proposal-summary") {
			if ($stateParams.campaignId) {
				var campaignId = $stateParams.campaignId;
				CampaignService.getCampaignWithProducts(campaignId).then(
					function (result) {}
				);
			}
		}
		if ($rootScope.currStateName == "admin.metro-campaigns") {
			getMetroCampaigns();
		}
		if ($rootScope.currStateName == "admin.metro-campaign") {
			if ($stateParams.metroCampaignId) {
				getMetroCampaignDetails($stateParams.metroCampaignId);
			}
			getMetroCorridors();
			getFormatList({ type: "metro" });
		}
		/*=============================
      | Page based initial loads end
    =============================*/

		$scope.loadCampaignPayments = function (campaignId) {
			AdminCampaignService.getCampaignPaymentDetails(campaignId).then(
				function (result) {
					// if (result.all_payments && result.all_payments.length >= 1) {
					$scope.campaignPayments = result.campaign_details;
					$scope.fivePercentAmount =
						(5 / 100) * $scope.campaignPayments?.total_amount;
					//$scope.campaignMetroPayments = result;
					//  }
					//  else {
					//   toastr.error(result.message);
					// }
				}
			);
		};
		// $scope.uncheck = function (checked) {
		//   debugger;
		//   if (!checked) {
		//     $scope.onchecked = false;
		//     $scope.GST = "0";
		//     $scope.TOTAL = $scope.campaignDetails.total_amount + parseInt($scope.GST);
		//   } else {
		//     $scope.onchecked = false;
		//     $scope.GST = ($scope.campaignDetails.total_amount / 100) * 18;
		//     $scope.TOTAL = $scope.campaignDetails.total_amount + $scope.GST;
		//   }
		// };
		$scope.uncheck = function (checked) {
			if (!checked) {
				$scope.onchecked = false;
				$scope.GST = "0";
				$scope.TOTAL =
					$scope.metroCampaignDetails.act_budget +
					parseInt($scope.GST);
			} else {
				$scope.onchecked = false;
				$scope.GST =
					($scope.metroCampaignDetails.act_budget / 100) * 18;
				$scope.TOTAL =
					$scope.metroCampaignDetails.act_budget + $scope.GST;
			}
		};
		$scope.checkoutMetroCampaign = function (ev, metroCampaignId) {
			if ($scope.onchecked === true) {
				$scope.flag = 1;
				$scope.GST =
					($scope.metroCampaignDetails.act_budget / 100) * 18;
			} else if ($scope.onchecked === false) {
				$scope.flag = 0;
				$scope.GST = "0";
			} else {
				$scope.flag = 1;
			}
			AdminCampaignService.checkoutMetroCampaign(
				metroCampaignId,
				$scope.flag,
				$scope.GST
			).then((result) => {
				if (result.status == 1) {
					getMetroCampaignDetails(metroCampaignId);
					// getMetroCampaigns();
					$mdDialog.show(
						$mdDialog
							.alert()
							.parent(
								angular.element(document.querySelector("body"))
							)
							.clickOutsideToClose(true)
							.title(result.message)
							//.textContent('You can specify some description text in here.')
							.ariaLabel("Alert Dialog Demo")
							.ok("Confirmed!")
							.targetEvent(ev)
					);
				} else {
					toastr.error(result.message);
				}
			});
		};
		/**********      Payments  */
		$scope.refundDisplay = false;
		if ($rootScope.currStateName == "admin.campaign-payment-details") {
			AdminCampaignService.getCampaignPaymentDetails(
				$stateParams.campaign_id
			).then(function (result) {
				$scope.campaignDetails = result.campaign_details;
				if ($scope.campaignDetails.refunded_amount != 0) {
					$scope.refundDisplay = true;
				} else {
					$scope.refundDisplay = false;
				}
				// if ($scope.campaignDetails.gst_price != "0") {
				//   $scope.GST = ($scope.campaignDetails.total_amount / 100) * 18;
				//   $scope.TOTALpay = $scope.campaignDetails.total_amount + parseInt($scope.GST) - $scope.campaignDetails.total_paid;
				// } else {
				//   $scope.GST = "0";
				//   $scope.TOTALpay = $scope.campaignDetails.total_amount + parseInt($scope.GST) - $scope.campaignDetails.total_paid;
				// }
			});
			$scope.loadCampaignPayments($stateParams.campaign_id);
		}
		if ($rootScope.currStateName == "admin.metro-campaign-details") {
			AdminCampaignService.getCampaignPaymentDetails(
				$stateParams.metroCampaignId
			).then(function (result) {
				$scope.campaignMetroPayments = result;
			});
		}
		if ($stateParams.metroCampaignId !== undefined) {
			$scope.loadCampaignPayments($stateParams.metroCampaignId);
		}

		$scope.paymentTypes = [
			{ name: "Cash" },
			{ name: "Cheque" },
			{ name: "Online" },
			{ name: "Transfer" },
		];
		$scope.files = {};
		$scope.updateCampaignPayment = function (cid) {
			$scope.campaignPayment.campaign_id = cid;
			Upload.upload({
				url: config.apiPath + "/campaign-payment",
				data: {
					image: $scope.files.image,
					campaign_payment: $scope.campaignPayment,
				},
			}).then(
				function (result) {
					if (result.data.status == "1") {
						toastr.success(result.data.message);
						$scope.campaignPayment = {};
						$scope.files.image = "";
						/*setTimout(() => {
                  $location.path('/owner/' + $rootScope.clientSlug + '/payments');
              }, 2500);*/
						// addPayment();
						$scope.loadCampaignPayments(cid);
						$state.reload();
					} else {
						if (result.data.message.constructor == Array) {
							$scope.updateCampaignPaymentErrors =
								result.data.message;
						} else {
							toastr.error(result.data.message);
						}
					}
				},
				function (resp) {
					toastr.error("somthing went wrong try again later");
				},
				function (evt) {
					var progressPercentage = parseInt(
						(100.0 * evt.loaded) / evt.total
					);
				}
			);
		};
		// function addPayment() {
		//   document.getElementById("myDropdown").classList.toggle("show");
		// }
		$scope.getCampaignList = function () {
			var productId = $stateParams.productId;
			AdminCampaignService.getCampaignsFromProducts(productId).then(
				function (result) {
					if (result) {
						$scope.shortlistedproduct = result;
						//toastr.success(result.message);
					} else {
						toastr.error(result.data.message);
					}
				}
			);
		};
		if ($location.$$path.search("product-shortlist-campagin") !== -1) {
			$scope.getCampaignList();
		}

		$scope.loadCampaignData = function (campaignId) {
			return new Promise(function (resolve, reject) {
				CampaignService.getCampaignWithProducts(campaignId).then(
					function (result) {
						$scope.campaignDetails = result;
						//$scope.campaignProducts = result.products;
						// setDatesForProductsToSuggest($scope.campaignDetails);
						// if(result.status > 7){
						//   loadCampaignPayments(campaignId);
						// }
						resolve(result);
					}
				);
			});
		};
		if ($stateParams.campaignId) {
			var campaignId = $stateParams.campaignId;
			$scope.loadCampaignData(campaignId);
		}
		// Date-Picker
		($scope.getProductUnavailableDates = function (product, ev) {
			// $scope.ownerProductPrice = productPrice
			// AdminCampaignService.getProductUnavailableDates(productId).then(function (dateRanges) {
			// $scope.unavailalbeDateRanges = dateRanges;
			// productDatesCalculator()
			// $(ev.target).parent().parent().find('input').trigger('click');
			// });
			if (product.type == "Bulletin") {
				AdminCampaignService.getProductUnavailableDates(
					product.id
				).then(function (dateRanges) {
					$scope.unavailalbeDateRanges = dateRanges;
					$(ev.target).parents().eq(3).find("input").trigger("click");
					// $(ev.target).parent().parent().find('input').trigger('click');
				});
			} else {
				AdminCampaignService.getProductDigitalUnavailableDates(
					product.id
				).then(function (blockedDatesAndSlots) {
					$scope.unavailalbeDateRanges = [];
					blockedDatesAndSlots.forEach((item) => {
						if (item.booked_slots >= product.slots) {
							$scope.unavailalbeDateRanges.push(item);
						}
					});
					$(ev.target).parents().eq(3).find("input").trigger("click");
					// $(ev.target).parent().parent().find('input').trigger('click');
				});
			}
		}),
			($scope.downloadAdminQuote = function (campaignId) {
				AdminCampaignService.downloadQuote(campaignId).then(function (
					result
				) {
					var campaignPdf = new Blob([result], {
						type: "application/pdf;charset=utf-8",
					});
					FileSaver.saveAs(campaignPdf, "campaigns.pdf");
					if (result.status) {
						toastr.error(result.meesage);
					}
				});
			}),
			
			//RFP Search Criteria PDF
			($scope.downloadRFPsearch = function (campaignId) {
				AdminCampaignService.downloadRFPsearchCriteria(campaignId).then(function (
					result
				) {
					var campaignPdf = new Blob([result], {
						type: "application/pdf;charset=utf-8",
					});
					FileSaver.saveAs(campaignPdf, "campaigns.pdf");
					if (result.status) {
						toastr.error(result.meesage);
					}
				});
			}),
			
			($scope.suggestProductForAdminCampaign = function (adminProduct) {
				if ($stateParams.campaignId) {
					var postObj = {
						campaign_id: $stateParams.campaignId,
						booked_slots: 1,
						product: {
							id: adminProduct.id,
							booking_dates: $scope.ranges.selectedDateRanges,
							price: adminProduct.rateCard,
						},
					};
					AdminCampaignService.proposeProductForCampaign(
						postObj
					).then(function (result) {
						if (result.status == 1) {
							toastr.success(result.message);
							$scope.removeSelection();
							CampaignService.getCampaignWithProducts(
								$stateParams.campaignId
							).then(function (result) {
								// alert("dhajf");
								$scope.campaignDetails = result;
								_.map($scope.AdminProduct, function (product) {
									if (product.id == adminProduct.id) {
										product.alreadyAdded = true;
									}
									return product;
								});
							});
						} else {
							toastr.error(result.message);
						}
					});
				}
			});

		$scope.ranges = {
			selectedDateRanges: [],
		};
		$scope.customOptions = {};
		$scope.removeSelection = function () {
			$scope.customOptions.clearSelection();
		};
		$scope.$on("removeSelection", function () {
			$scope.removeSelection();
		});
		/*================================
     | Multi date range picker options
     ================================*/
		$scope.suggestProductOpts = {
			multipleDateRanges: true,
			opens: "center",
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
				if (moment(dt) < moment()) {
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
		/*====================================
   | Multi date range picker options end
   ====================================*/
		$scope.getProductList();
		// $scope.loadCampaignPayments($stateParams.campaign_id);

		/* ----------------------------------
      New Hording profuct Nav bars starts
    -------------------------------------*/
		$scope.toggleProductDetailSidenav = function () {
			$("#exampleModalcalendar").modal("hide");
			selectWeekValue = 0;
			$scope.yearlyWeeks
				.filter((week) => week.selectedWeek)
				.forEach((week) => {
					week.selectedWeek = false;
				});
			$scope.weeksArray
				.filter((week) => week.selected)
				.forEach((week) => {
					week.selected = false;
				});
		};

		/*---------------------------------
      New Hording profuct Nav bars ends
    -----------------------------------*/
	}
);
