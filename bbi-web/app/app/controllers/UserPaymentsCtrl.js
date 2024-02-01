angular
	.module("bbManager")
	.controller(
		"UserPaymentCtrl",
		function (
			$scope,
			CampaignService,
			$rootScope,
			$stateParams,
			$mdSidenav,
			toastr,
			$mdDialog,
			FileSaver
		) {
			/*===================
      	| Lazy Loading
    	===================*/
			$scope.lazy = {};
			$scope.lazy.pageNo = 0;
			$scope.lazy.pageSize = 15;
			$scope.lazy.pageCount = 0;

			const paymentsTable = $("#payments-table");
			paymentsTable.on("scroll", (event) => {
				if ($rootScope.currStateName == "index.user-payments") {
					$scope.isBottom = paymentsTable.scrollTop() + Math.round(paymentsTable.outerHeight()) >=
						(event.target.scrollHeight - 1);
					if (
						$scope.isBottom &&
						!$rootScope.http2_loading &&
						$scope.lazy.pageNo <= $scope.lazy.pageCount
					) {
						$scope.getUserPayment("payments");
					}  else if ($scope.isBottom && $scope.lazy.pageNo > $scope.lazy.pageCount && !$rootScope.http2_loading) {
						toastr.info("You are at the end of page");
					}
				}
			});
			/*======================
				| End of Lazy Loading
			=======================*/
			$scope.userPayments = [];

			$scope.getUserPayment = function (tab_status = 'payments') {
				$rootScope.http2_loading = true;
				$scope.lazy.pageNo += 1;
				CampaignService.getActiveUserCampaigns(
					tab_status,
					$scope.lazy.pageNo,
					$scope.lazy.pageSize
				).then(function (result) {
					$scope.lazy.pageCount = parseInt(result[0]?.user_campaigns_total_count / $scope.lazy.pageSize);
					$scope.userPayments = [...$scope.userPayments,...result];
					angular.forEach($scope.userPayments, function (item, key) {
						item["pendingPayment"] = item.act_budget - item.paid;
					});
					// angular.forEach($scope.userPayments, function (item, key) {
					// 	item["updated_at"] = item.updated_at.toDateString();
					// });
					console.log($scope.userPayments);
					$rootScope.http2_loading = false;
				});
			};
			$scope.convertToLocalTZ = function (date) {
				var stillUtc = moment.utc(date).toDate();
				var local = moment(stillUtc)
					.local()
					.format("YYYY-MM-DD HH:mm:ss");
				return local;
			};
			$scope.getCampaignDetails = function (campaignId) {
				CampaignService.getPaymentForUserCampaigns(campaignId).then(
					function (result) {
						$scope.UserPaymentDetails = result;
						if (result.status == 0) {
							$scope.message = result.message;
						}
						$scope.TOTALpay =
							$scope.UserPaymentDetails.campaign_details
								.total_amount -
							parseInt($scope.UserPaymentDetails.total_paid);
					}
				);
			};

			//share Camp
			$scope.toggleShareCampaignSidenav = function (campaign) {
				$scope.currentOwnerShareCampaign = campaign;
				$mdSidenav("shareCampaignSidenav").toggle();
			};
			$scope.shareCampaignToEmail = function (
				ev,
				shareCampaign,
				campaignID
			) {
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
							document
								.getElementById("myDropdown")
								.classList.toggle("show");
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
			//share Camp ends

			//  Sorting user payments
			$scope.sortAsc = function (headingName, type) {
				$scope.upArrowColour = headingName;
				$scope.sortType = "Asc";
				if (type == "string") {
					$scope.newOfferData = $scope.userPayments.map((e) => {
						return {
							...e,
							first_name: e.first_name,
							company_type: e.company_type,
							email: e.email,
							company_name: e.company_name,
						};
					});
					$scope.userPayments = [];
					$scope.userPayments = $scope.newOfferData.sort((a, b) => {
						console.log(a[headingName]);
						if (a[headingName] != null) {
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

					// $scope.productList = $scope.newOfferData;
				}
				$scope.userPayments = $scope.userPayments.sort((a, b) => {
					if (type == "boolean") {
						return a[headingName] ? 1 : -1;
					} else if (type == "date") {
						return (
							new Date(a[headingName].date) -
							new Date(b[headingName].date)
						);
					} else {
						return a[headingName] - b[headingName];
					}
				});
				console.log($scope.userPayments);
			};

			$scope.sortDsc = function (headingName, type) {
				$scope.downArrowColour = headingName;
				$scope.sortType = "Dsc";
				if (type == "string") {
					$scope.newOfferData = $scope.userPayments.map((e) => {
						return {
							...e,
							first_name: e.first_name,
							company_type: e.company_type,
							email: e.email,
							company_name: e.company_name,
						};
					});
					$scope.userPayments = [];
					$scope.userPayments = $scope.newOfferData.sort((a, b) => {
						console.log(a[headingName]);
						if (b[headingName] != null) {
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
				$scope.userPayments = $scope.userPayments.sort((a, b) => {
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
				console.log($scope.userPayments);
			};
			//  Sorting

			$scope.downloadUserQuote = function (campaignId) {
				CampaignService.downloadQuote(campaignId).then(function (
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
			$scope.downloadOwnerReciepts = function (campaignId) {
				CampaignService.downloadOwnerReciepts(campaignId).then(
					function (result) {
						var campaignPdf = new Blob([result], {
							type: "application/pdf;charset=utf-8",
						});
						FileSaver.saveAs(campaignPdf, "campaigns.pdf");
						if (result.status) {
							toastr.error(result.meesage);
						}
					}
				);
			};
			if ($rootScope.currStateName == "index.user-payments") {
				$scope.getUserPayment();
			}
			if ($rootScope.currStateName == "index.update-user-payments") {
				$scope.getCampaignDetails($stateParams.id);
				// if ($scope.campaignDetails.gst_price != "0") {
				//   $scope.GST = ($scope.campaignDetails.total_amount / 100) * 18;
				//   $scope.TOTALpay = $scope.campaignDetails.total_amount + parseInt($scope.GST) - $scope.campaignDetails.total_paid;
				// } else {
				//   $scope.GST = "0";
				//   $scope.TOTALpay = $scope.campaignDetails.total_amount + parseInt($scope.GST) - $scope.campaignDetails.total_paid;
				// }
			}
		}
	);
