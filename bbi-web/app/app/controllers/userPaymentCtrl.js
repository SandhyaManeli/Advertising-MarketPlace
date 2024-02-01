angular.module("bbManager").controller(
	"userPaymentCtrl",
	function (
		$scope,
		$stateParams,
		config,
		$window,
		toastr,
		CampaignService,
	) {
		console.log("API PATH: ");
		console.log(config.apiPath + "/stripePost");
		
		function convertDateToMMDDYYYY (dates,areaTimeZoneType) {
			const startDate = dates.booked_from;
			const endDate = dates.booked_to;
			if(areaTimeZoneType != null){
				const splitStartDate = new Date(startDate)
					.toLocaleString('en-GB',{ timeZone: areaTimeZoneType}).slice(0,10).split('/');
				[splitStartDate[0], splitStartDate[1]] = [splitStartDate[1], splitStartDate[0]];
				
				const splitEndDate = new Date(endDate)
					.toLocaleString('en-GB', { timeZone: areaTimeZoneType}).slice(0,10).split('/');
				[splitEndDate[0], splitEndDate[1]] = [splitEndDate[1], splitEndDate[0]];
				
				return {
					startDate: splitStartDate.join('-'),
					endDate: splitEndDate.join('-')
				}
			}else{
				return {
					startDate: moment(startDate).format("MM-DD-YYYY"),
					endDate: moment(endDate).format("MM-DD-YYYY")
				}
			}
		}

		$scope.getCampaignDetails = function (campaignId) {
			CampaignService.getCampaignWithProducts(campaignId).then(function (
				result
			) {
				$scope.campaignDetails = result;
				
				$scope.campaignDetails.products.forEach((product) => {
					const {startDate, endDate} = convertDateToMMDDYYYY(product,product.area_time_zone_type);
					product["date"] = {
					  startDate,endDate
					}
				});
				$scope.fivePercentAmount =
					(5 / 100) * $scope.campaignDetails.totalamount;
				$scope.cardProcessing = (2.9 / 100) * $scope.fivePercentAmount;
				$scope.payableAmount =
					Math.ceil($scope.fivePercentAmount) +
					Math.ceil($scope.cardProcessing);
				// campaign offer
				$scope.offerDetails.campaignId = $stateParams.campaignId;
				$scope.getCampaignOffers();
			});
		};

		/*====================================== 
			Getting Offer Details   
		=======================================*/
		$scope.offerDetails = {
      offers: [],
      status: {
        10: "Requested",
        20: "Accepted",
        30: "Accepted",
        40: "Rejected",
        50: "Rejected",
      },
      showOfferButton: true,
      offerPercentage: 0,
      isRequested: false,
      isAccepted: false,
      isRejected: false,
      campaginId: null,
    };

    $scope.getCampaignOffers = function() {
			try {
				CampaignService.getCampaignOffers($scope.offerDetails.campaignId).then(
					(result) => {
						console.log(result);
						$scope.offerDetails.offers = result;
						if (result && result.length) {
							if (result.length == 1)
								if (result[0].status == 40 || result[0].status == 50)
									$scope.offerDetails.showOfferButton = true;
								else $scope.offerDetails.showOfferButton = false;
							else $scope.offerDetails.showOfferButton = false;
						}
						if (result.length >= 2) {
							var discountPrice =
								$scope.campaignDetails.totalamount - result[1].price;
							$scope.offerDetails.offerPercentage =
								(discountPrice / $scope.campaignDetails.totalamount) * 100;
							$scope.offerDetails.isRequested = result[1].status == 10;
							$scope.offerDetails.isAccepted =
								result[1].status == 20 || result[1].status == 30;
							$scope.offerDetails.isRejected =
								result[1].status == 40 || result[1].status == 50;
						} else if (result.length == 1) {
							var discountPrice =
								$scope.campaignDetails.totalamount - result[0].price;
							$scope.offerDetails.offerPercentage =
								(discountPrice / $scope.campaignDetails.totalamount) * 100;
							$scope.offerDetails.isRequested = result[0].status == 10;
							$scope.offerDetails.isAccepted =
								result[0].status == 20 || result[0].status == 30;
							$scope.offerDetails.isRejected =
								result[0].status == 40 || result[0].status == 50;
						}
					}
				);	
			} catch (error) {
				console.log(error);
			}
    }
		
		// Create a Stripe client
		// strip-payment
		var stripe = Stripe("pk_test_n5DnV8z3P2j751J2JqDyeX6r006Om1UNEK");
		// var stripe = Stripe('pk_live_tIHHemTCIt1GGb5TQwGxjXj700M5NTOzpH');
		var elements = stripe.elements();

		var card = elements.create("card", {
			hidePostalCode: true,
			style: {
				base: {
					iconColor: "#666EE8",
					color: "#31325F",
					lineHeight: "40px",
					fontWeight: 400,
					fontFamily:
						'"Open Sans", "Helvetica Neue", "Helvetica", sans-serif',
					fontSize: "15px",

					"::placeholder": {
						color: "#CFD7E0",
					},
				},
			},
		});
		card.mount("#card-element");
		function setOutcome(result) {
			var successElement = document.querySelector(".success");
			var errorElement = document.querySelector(".error");
			successElement.classList.remove("visible");
			errorElement.classList.remove("visible");

			if (result.token) {
				// In this example, we're simply displaying the token
				successElement.querySelector(".token").textContent =
					result.token.id;
				successElement.classList.add("visible");
				var data = {
					total_Amount: $scope.campaignDetails.totalamount,
					five_percent: $scope.fivePercentAmount,
					card_processing: $scope.cardProcessing,
					// payable_amount : Math.ceil($scope.payableAmount),
					payable_amount: Math.ceil(
						$scope.campaignDetails.finalpurchasepayment
					),
					campaignId: $scope.campaignDetails.id,
					result: result,
				};
				CampaignService.userPaymentDetails(data).then(function (
					result
				) {
					$scope.paymentDetails = result;
					if (result.status == 1) {
						// toastr.error(result.meesage);
						sessionStorage.setItem("backUrl", "yes");
						//$window.location.href = '/#campaign-details/' + $scope.campaignDetails.id;
						$window.location.href =
							"/#campaign-details/" +
							$scope.campaignDetails.id +
							"/2";
						toastr.success(
							"Your Payment is Successfuly now you can Request Campaign!!"
						);
					}
				});
				// In a real integration, you'd submit the form with the token to your backend server
				//  var form = document.querySelector('form');
				//  form.querySelector('input[name="token"]').setAttribute('value', result.token.id);
				//  form.submit();
				//  return false;
			} else if (result.error) {
				errorElement.textContent = result.error.message;
				errorElement.classList.add("visible");
			}
		}

		card.on("change", function (event) {
			setOutcome(event);
		});

		document.querySelector("form").addEventListener("submit", function (e) {
			e.preventDefault();
			var options = {
				name:
					document.getElementById("first-name").value +
					" " +
					document.getElementById("last-name").value,
				address_line1: document.getElementById("address-line1").value,
				address_line2: document.getElementById("address-line2").value,
				address_city: document.getElementById("address-city").value,
				address_state: document.getElementById("address-state").value,
				address_zip: document.getElementById("address-zip").value,
				address_country:
					document.getElementById("address-country").value,
				total_Amount: $scope.campaignDetails.totalamount,
				five_percent: $scope.fivePercentAmount,
				card_processing: $scope.cardProcessing,
				// payable_amount : $scope.payableAmount
				payable_amount: Math.ceil(
					$scope.campaignDetails.finalpurchasepayment
				),
			};
			stripe.createToken(card, options).then(setOutcome);
		});

		$scope.payNow = function () {
			var options = {
				name:
					document.getElementById("first-name").value +
					" " +
					document.getElementById("last-name").value,
				address_line1: document.getElementById("address-line1").value,
				address_line2: document.getElementById("address-line2").value,
				address_city: document.getElementById("address-city").value,
				address_state: document.getElementById("address-state").value,
				address_zip: document.getElementById("address-zip").value,
				address_country:
					document.getElementById("address-country").value,
				total_Amount: $scope.campaignDetails.totalamount,
				five_percent: $scope.fivePercentAmount,
				card_processing: $scope.cardProcessing,
				// payable_amount : $scope.payableAmount
				payable_amount: Math.ceil(
					$scope.campaignDetails.finalpurchasepayment
				),
			};
			stripe.createToken(card, options).then(setOutcome);
		};

		// strip payment
		$scope.getCampaignDetails($stateParams.campaignId);
	}
);
