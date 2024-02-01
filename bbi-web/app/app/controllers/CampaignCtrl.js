angular
  .module("bbManager")
  .controller(
    "CampaignCtrl",
    function (
      $scope,
      $mdDialog,
      $mdSidenav,
      $stateParams,
      $window,
      $location,
      $rootScope,
      $timeout,
      CampaignService,
      config,
      toastr,
      FileSaver,
      $state,
      MapService,
      TimezoneService
    ) {
      $scope.config = config;

      $scope.CAMPAIGN_STATUS = [
        "campaign-preparing", //    0
        "campaign-created", //    1
        "quote-requested", //    2
        "quote-given", //    3
        "change-requested", //    4
        "launch-requested", //    5
        "running", //    6
        "suspended", //    7
        "stopped", //    8
      ];

      $scope.showPaymentdailog = function () {
        $mdDialog.show({
          templateUrl: "views/updatepaymentDailog.html",
          fullscreen: $scope.customFullscreen,
          clickOutsideToClose: true,
        });
      };

      $scope.grossViewPage = function () {
        $mdDialog.show({
          templateUrl: "views/gross-view.html",
          fullscreen: $scope.customFullscreen,
          clickOutsideToClose: true,
          preserveScope: true,
          scope: $scope,
          controller: function ($scope) {
            $scope.closeDialog = function () {
              if ($scope.timeout) {
                clearTimeout($scope.timeout);
                $scope.timeout = null;
              }
              $mdDialog.hide();
            };
          },
        });
      };

      $scope.gotoPaymentPage = function () {
        $scope.loading = true;
        var soldPrdoucts = $scope.campaignDetails.products.filter((prod) => {
          if (prod.sold_status) return true;
          return false;
        });
        var expiredProducts = $scope.campaignDetails.products.filter((prod) => {
          if (prod.is_product_expired == true) return true;
          return false;
        });
        $scope.loading = false;
        if (soldPrdoucts && soldPrdoucts.length) {
          $scope.loading = true;
          try {
            CampaignService.sendUnavailableMail({
              id: $stateParams.campaignId,
            }).then((result) => {
              if (result && result.message) {
                var html = "<p>Below Products are sold:</p>";
                html += "<ul>";
                soldPrdoucts.forEach((prod) => {
                  html += '<li style="font-color:red;">';
                  html +=
                    "<strong>" + prod.siteNo + " " + prod.title + "</strong>";
                  html += "</li>";
                });
                html += "</ul>";
                html += `<p><strong>Note</strong>: <span style="font-color:red;">${result.message}</span></p>`;
                var confirm = $mdDialog
                  .alert()
                  .parent(angular.element(document.querySelector("body")))
                  .clickOutsideToClose(true)
                  .title("Sold Product Warning!")
                  .ariaLabel("Sold Product Warning!")
                  .htmlContent(html)
                  .ok("OK");
                $mdDialog.show(confirm);
              }
            });
          } catch (error) {
            console.log(error);
          }
        } else if (expiredProducts && expiredProducts.length) {
          $scope.loading = true;
          var html = "<p>Below Products are expired:</p>";
          html += "<ul>";
          expiredProducts.forEach((prod) => {
            html += '<li style="font-color:red;">';
            html += "<strong>" + prod.siteNo + " " + prod.title + "</strong>";
            html += "</li>";
          });

					var alert = $mdDialog
						.alert()
						.parent(angular.element(document.querySelector("body")))
						.clickOutsideToClose(true)
						.title("Expired Product Warning!")
						.ariaLabel("Expired Product Warning!")
						.htmlContent(html)
						.ok("close");
					$mdDialog.show(alert);
					// var confirm = $mdDialog
					//   .confirm()
					//   .parent(angular.element(document.querySelector("body")))
					//   .clickOutsideToClose(true)
					//   .title("Expired Product Warning!")
					//   .ariaLabel("Expired Product Warning!")
					//   .htmlContent(html)
					//   .ok("Proceed for payment")
					//   .cancel("Cancel");
					// $mdDialog.show(confirm).then(function () {
					//   var url = "/user-payment/" + $scope.campaignDetails.id;
					//   $location.path(url);
					//   $scope.loading = false;
					// });
				} 
				
				/*Payment Method Online or Offline Start*/
				else if ($scope.campaignDetails && $scope.campaignDetails.id) {
					var htmlContent = `<md-dialog aria-label="Payment Method">
							<form>
								<md-toolbar style="background-color: #44596D;">
									<div class="md-toolbar-tools">
										<h2>Payment Method</h2>
										<span flex></span>
										<md-button class="md-icon-button" ng-click="cancel()">
											<i class="fa fa-times" aria-hidden="true"></i>
										</md-button>
									</div>
								</md-toolbar><br>
								<md-radio-group style="padding-left:30px;" ng-model="selectedMethod">
									<md-radio-button value="Online">Online</md-radio-button>
									<md-radio-button value="Offline">Offline</md-radio-button>
								</md-radio-group>
							</form>
							<md-dialog-actions>
								<md-button ng-click="cancel()">Cancel</md-button>
								<md-button ng-click="proceed()" ng-disabled="!selectedMethod">Proceed</md-button>
							</md-dialog-actions>
						</md-dialog>`;

					$mdDialog.show({
						parent: angular.element(document.body),
						clickOutsideToClose: false,
						template: htmlContent,
						controller: function($scope, $mdDialog, campaignDetails) {
							$scope.selectedMethod = 'Online';
							$scope.cancel = function() {
								$mdDialog.cancel();
							};
							$scope.proceed = function() {
								if($scope.selectedMethod) {
									var method = $scope.selectedMethod.toLowerCase();
									var url = "/user-payment/" + campaignDetails.id + "/" + method;
									$location.path(url);
									$scope.loading = false;
									$mdDialog.hide();
								}
								 else {
									console.log("Please select a payment method.");
								}
							};
						},
						locals: {
							campaignDetails: $scope.campaignDetails
						}
					});
				} 
				else {
					console.log("Campaign details not available or incomplete.");
				}

				/*Payment Method Online or Offline End*/
				
				//else {
					/*------ Comment this code for groos view percantage popup as per the client requirment 10-May-2023----*/
					/*$mdDialog.show({
						templateUrl: "views/gross-view.html",
						clickOutsideToClose: false,
						preserveScope: true,
						fullscreen: $scope.customFullscreen,
						scope: $scope,
						controller: function ($scope) {
							$scope.closeDialog = function () {
								if ($scope.timeout) {
									clearTimeout($scope.timeout);
									$scope.timeout = null;
								}
								$mdDialog.hide();
							};
						},
					});*/
					
					//var url = "/user-payment/" + $scope.campaignDetails.id;
					//$location.path(url);
					//$scope.loading = false;
				//}
			};

      $scope.grossTypes = [
        {
          name: "Yes",
        },
        {
          name: "No",
        },
      ];

      $scope.grossPercentageChoiceChanged = function () {
        if ($scope.gross.type.name === $scope.grossTypes[0].name) {
          $rootScope.http2_loading = true;
          CampaignService.getGrossPercantage($stateParams.campaignId).then(
            (result) => {
              $rootScope.http2_loading = false;
              if (result.status == 1) {
                $scope.grossPercantage =
                  result.campign_gross_data.gross_fee_percentage;
              } else {
                toastr.error(result.message);
              }
            }
          );
        }
      };

      $scope.gotoPaymentsPage = function () {
        gross_status = "";
        gross_percantage = 0;
        if ($scope.gross.type.name == "Yes") {
          gross_status = 1;
          gross_percantage = $scope.grossPercantage;
        } else if ($scope.gross.type.name == "No") {
          gross_status = 0;
          gross_percantage = 0;
        }
        const payload = {
          id: $stateParams.campaignId,
          gross_fee_percentage: gross_percantage,
          gross_fee_percentage_status: gross_status,
        };

        CampaignService.updateGrossPercantage(payload).then((result) => {
          if (result.status == 1) {
            toastr.success(result.message);
            $mdDialog.hide();
            $scope.getCampaignDetails($stateParams.campaignId);
            // var url =
            // 	"/user-payment/" + $scope.campaignDetails.id;
            // $location.path(url);
          } else {
            toastr.error(result.message);
          }
        });
      };

      $scope.gotoLocationPage = function (campaignID) {
        $location.path("/location/" + campaignID);
      };

      // validatations for the gross fee percantage
      $scope.grossPercantage = 0;
      $scope.percantage = 100;

      $scope.decreaseQuantity = function () {
        $scope.grossPercantage -= 1;
      };

      $scope.increaseQuantity = function () {
        $scope.grossPercantage += 1;
      };

      $scope.prevQuantity = 0;
      $scope.percantageHandler = function () {
        $scope.prevQuantity = $scope.grossPercantage;
      };

      $scope.percantageChangeHandler = function () {
        if (
          $scope.grossPercantage > $scope.percantage ||
          $scope.grossPercantage < 0
        ) {
          toastr.warning(`Please enter percantage in between 1 to 100`);
          $scope.grossPercantage = $scope.prevQuantity;
        }
      };

      /*=============================
    | Chat Popup  
    =============================*/
      $scope.sendMessageOnEnter = function (ev) {
        if (ev.originalEvent.key == "Enter") {
          $scope.sendMessage();
        }
      };

      $scope.sendMessage = function () {
        const payload = {
          campaign_id: $stateParams.campaignId,
          product_id: $scope.campaignDetails.products.find(
            (item) => $scope.selectedProductId == item.siteNo
          ).product_id,
          message: $scope.message,
          user_type: "buyer",
        };
        CampaignService.getCreateMessages(payload).then((result) => {
          if (result.status) {
            $scope.message = "";
            CampaignService.getChatMessages($stateParams.campaignId).then(
              (result) => {
                $scope.messageData = result.messages_data;
                $scope.el = document.getElementById("dialogContent_0");
                console.log($scope.el);
                if ($scope.el) {
                  $scope.el.scrollTop = $scope.el.scrollHeight;
                }
              }
            );
          }
        });
      };

      $scope.getChatMessages = function () {
        try {
          CampaignService.getChatMessages($stateParams.campaignId).then(
            (result) => {
              if (result.messages_data.length != $scope.messageData.length) {
                $scope.messageData = result.messages_data;

                $timeout(() => {
                  $scope.el = document.getElementById("dialogContent_0");
                  console.log($scope.el);
                  if ($scope.el) {
                    $scope.el.scrollTop = $scope.el.scrollHeight;
                  }
                });
              }
              if ($scope.timeout) {
                clearTimeout($scope.timeout);
                $scope.timeout = null;
              }
              $scope.timeout = setTimeout($scope.getChatMessages, 15000);
            }
          );
        } catch (error) {
          console.log(error);
        }
      };

      $scope.openChat = function () {
        const uniqueProd = new Set(
          $scope.campaignDetails.products.map((prod) => prod.siteNo)
        );
        $scope.uniqueProducts = Array.from(uniqueProd);
        $scope.currentUser = "buyer";
        $scope.selectedProductId = "";
        $scope.selectedUser = "";
        $scope.message = "";
        $scope.messageData = [];
        $mdDialog.show({
          templateUrl: "views/chat-popup.html",
          clickOutsideToClose: false,
          preserveScope: true,
          fullscreen: $scope.customFullscreen,
          scope: $scope,
          controller: function ($scope) {
            $scope.closeDialog = function () {
              if ($scope.timeout) {
                clearTimeout($scope.timeout);
                $scope.timeout = null;
              }
              $mdDialog.hide();
            };
          },
        });
        $scope.getChatMessages();
      };
      /*=============================
    | End of Chat Popup  
  =============================*/
      $scope.cancel = function () {
        $mdDialog.cancel();
      };

      ////data for image uploading

      $scope.data = {};
      $scope.uploadFile = function (input) {
        $scope.loading = true;
        $scope.data.fileName = input.files[0].name;
        if (input.files && input.files[0]) {
          var reader = new FileReader();
          reader.onload = function (e) {
            //Sets the Old Image to new New Image
            $("#photo-id").attr("src", e.target.result);
            //Create a canvas and draw image on Client Side to get the byte[] equivalent
            var canvas = document.createElement("canvas");
            var imageElement = document.createElement("img");
            imageElement.setAttribute("src", e.target.result);
            canvas.width = imageElement.width;
            canvas.height = imageElement.height;
            var context = canvas.getContext("2d");
            context.drawImage(imageElement, 0, 0);
            var base64Image = canvas.toDataURL("image/jpeg");
            //Removes the Data Type Prefix
            //And set the view model to the new value
            $scope.data.uploadedPhoto = e.target.result.replace(
              /data:image\/jpeg;base64,/g,
              ""
            );
          };
          //Renders Image on Page
          reader.readAsDataURL(input.files[0]);
          $scope.loading = false;
        }
      };
      //product image
      $scope.ProductImageView = function (ev, img_src) {
        $mdDialog.show({
          locals: {
            src: img_src,
          },
          templateUrl: "views/image-popup-large.html",
          fullscreen: $scope.customFullscreen,
          clickOutsideToClose: true,
          controller: function ($scope, src) {
            $scope.img_src = src;
          },
          resolve: {
            imageCtrl: [
              "$ocLazyLoad",
              function ($ocLazyLoad) {
                return $ocLazyLoad.load("./controllers/ImageCtrl.js");
              },
            ],
          },
        });
      };

      $scope.campaignDetails = {};

      // $scope.limit = 3;

      // $scope.loadMore = function () {
      //   $scope.limit = $scope.items.length
      // }

      /*===================
      | Lazy Loading
    ===================*/
      $scope.lazy = {};
      $scope.lazy.pageNo = 0;
      $scope.lazy.pageSize = 15;
      $scope.lazy.pageCount = 0;

      $(document).ready(() => {
        const campaignTable = $("#campaign-table");
        campaignTable.on("scroll", (event) => {
          if ($rootScope.currStateName == "index.user-saved-campaigns") {
            $scope.isBottom =
              campaignTable.scrollTop() +
                Math.round(campaignTable.outerHeight()) >=
              event.target.scrollHeight - 1;
            if (
              $scope.isBottom &&
              !$rootScope.http2_loading &&
              $scope.lazy.pageNo <= $scope.lazy.pageCount
            ) {
              $scope.getUserCampaigns("saved_campaign");
            } else if (
              $scope.isBottom &&
              $scope.lazy.pageNo > $scope.lazy.pageCount &&
              !$rootScope.http2_loading
            ) {
              toastr.info("You are at the end of page");
            }
          }
        });
      });

      /*======================
      | End of Lazy Loading
    =======================*/

      /*==================================================
      | Get Campaigns List For Campaign Management Page  
    ===================================================*/
      $scope.tabHandle = function (tab) {
        $scope.getUserCampaigns(tab);
      };

      $scope.selectedids = [];
      $scope.userSavedCampaigns = [];
      $scope.getUserCampaigns = function (tab_status = "insertion_order") {
        $scope.lazy.pageNo += 1;
        $rootScope.http2_loading = true;
        if ($location.$$url.split("/")[1] != "user-saved-campaigns") {
          $scope.lazy.pageNo = null;
          $scope.lazy.pageSize = null;
        }
        CampaignService.getActiveUserCampaigns(
          tab_status,
          $scope.lazy.pageNo,
          $scope.lazy.pageSize
        ).then(function (result) {
          if (result && result.length) {
            $scope.lazy.pageCount = parseInt(
              result[0]?.user_campaigns_total_count / $scope.lazy.pageSize
            );
            if (tab_status != "saved_campaign") {
              angular.forEach(result, function (camapaign) {
                var date = [];
                date = camapaign.updated_at.split(" ");
                camapaign.updated_at = date[0];
                camapaign.updated_at = moment(camapaign.updated_at).format(
                  "MM-DD-YYYY"
                );
              });
            }
            switch (tab_status) {
              case "saved_campaign":
                $scope.userSavedCampaigns = [
                  ...$scope.userSavedCampaigns,
                  ...result,
                ];
                break;
              case "insertion_order":
                $scope.plannedCampaigns = result;
                break;
              case "scheduled":
                $scope.SheduledCampaigns = result;
                break;
              case "running":
                $scope.runningCampaigns = result;
                break;
              case "closed":
                $scope.closedCampaigns = result;
                break;
              case "cancelled":
                $scope.cancelledCampaigns = result;
                break;
            }
          }
          $rootScope.http2_loading = false;
        });
      };

      /*==========================================================
      | End of Get Campaigns List For Campaign Management Page  
    ===========================================================*/

      var productlistArray = [];
      $scope.productlist = [];
      $scope.disableDeleteCampaignRequest = false;
      $scope.disableDeleteProductRequest = false;
      $scope.getCampaignDetails = function (campaignId) {
        CampaignService.getCampaignWithProducts(campaignId).then(function (
          result
        ) {
          if (result.status) {
            $scope.hidestayconsistent = false;
            if ($location.$$url.split("/")[3] == "2") {
              $scope.hidestayconsistent = true;
            }
            $scope.campaignDetails = result;
            // console.log('durga',result );
            if ($scope.campaignDetails.products != null) {
              $scope.campaignDetails.products.forEach((product) => {
                const { startDate, endDate } =
                  TimezoneService.convertDateToMMDDYYYY(
                    product,
                    product.area_time_zone_type
                  );
                product["date"] = {
                  startDate,
                  endDate,
                };
                product = {
                  billingYes: "Yes",
                  billingNo: "",
                  servicingNo: "",
                  servicingYes: "Yes",
                };
              });
              $scope.loading = true;
              if ($scope.campaignDetails.status == 600) {
                $scope.status = $scope.campaignDetails.status;
                $scope.campaignDetails.products.forEach((s) => {
                  if (s.deleteProductStatus && s.deleteProductStatus == 101) {
                    $scope.status = s.deleteProductStatus;
                  }
                });
                $scope.loading = false;
              }
              $scope.total_grand =
                $scope.campaignDetails.totalamount +
                $scope.campaignDetails.newprocessingfeeamtSum;
            } else if ($scope.campaignDetails.products == null) {
              $scope.RFPsearchArray = result.rfp_search_criteria_preview;
              currentDateData = [];
              dates_dma_diff = [];
              $scope.RFPsearchArray.dma_dates.forEach((product, index) => {
                dates_dma = product.split("::");
                currentDateData.push({ dates_dma });
                var startDate = moment(currentDateData[index].dates_dma[0]);
                var endDate = moment(currentDateData[index].dates_dma[1]);
                dates_dma_diff.push(
                  moment(endDate).diff(startDate, "days") + 1
                );
              });
              $scope.currentDateDataString = currentDateData;
              $scope.dates_dma_diff_data = dates_dma_diff;
            }
            $scope.loading = false;
          } else {
            toastr.error(result.message);
          }
          $scope.disableDeleteCampaignRequest =
            $scope.campaignDetails.cancelledCampaignStatus &&
            $scope.campaignDetails.cancelledCampaignStatus == 99;
          if ($scope.campaignDetails && $scope.campaignDetails.products) {
            $scope.campaignDetails.products =
              $scope.campaignDetails.products.map((e) => {
                if (e.deleteProductStatus && e.deleteProductStatus == 101) {
                  $scope.disableDeleteProductRequest =
                    e.deleteProductStatus && e.deleteProductStatus == 101;
                }

                return {
                  ...e,
                  chkbox: false,
                };
              });
          }
          //  var utcDate = new Date($scope.campaignDetails.created_at);
          // utcDate.setHours(utcDate.getHours()-8);
          // var usDate = new Date (utcDate);
          // $scope.campaignDetails = {
          //   ...$scope.campaignDetails,
          //   created_at:usDate
          // }
          angular.forEach(
            $scope.campaignDetails.products,
            function (productIds) {
              productlistArray.push(productIds.product_id);
            }
          );

          $scope.productIDlist = productlistArray;
          if (sessionStorage.getItem("backUrl") == "yes") {
            $scope.confirmCampaignBooking($scope.campaignDetails.id);
            $scope.notifyUsershortlistedProduct($scope.productIDlist);
            sessionStorage.setItem("backUrl", "no");
          }

          if ($scope.campaignDetails.products_in_campaign != null) {
            $scope.campaignDetails.products_in_campaign.forEach(
              (product_data) => {
                product_data.mapImageUrl = generateMapImageUrl(
                  product_data.lat,
                  product_data.lng
                );
              }
            );
          }
          // $scope.fivePercentAmount = ( 5 / 100) * $scope.campaignDetails.totalamount;
          // $scope.cardProcessing = (2.9/100) * $scope.fivePercentAmount;
          // if(typeof result.act_budget === 'number' && result.act_budget % 1 == 0){
          //   $scope.campaignDetails.gst = 0;
          //   $scope.campaignDetails.subTotal = result.act_budget ;
          //   $scope.campaignDetails.grandTotal = $scope.campaignDetails.subTotal;
          //   $scope.GST = ($scope.campaignDetails.act_budget / 100) * 18;
          //   $scope.TOTAL = $scope.campaignDetails.act_budget + $scope.GST;
          //   $scope.PendingPay = $scope.campaignDetails.totalamount - $scope.campaignDetails.total_paid;
          // }
        });
      };
      $scope.prodDetails = function (productID, isFromRouteCheck) {
        CampaignService.getProductDetails(productID).then(function (result) {
          $scope.productdetails = result.product_details;
          if (!isFromRouteCheck)
            $window.location.href =
              "/product-camp-details/" + $scope.productdetails.id;
        });
      };
      // if($stateParams.campaignId){
      //   $scope.getCampaignDetails($stateParams.campaignId);
      // }
      // $scope.gstuncheck = function(checked) {
      //   if (!checked) {
      //     $scope.GST = "0";
      //     $scope.onchecked = false;
      //     // $scope.checked = true;
      //     $scope.TOTAL = $scope.campaignDetails.act_budget + parseInt($scope.GST);
      //   } else {
      //     $scope.GST = ($scope.campaignDetails.act_budget / 100) * 18;
      //     $scope.TOTAL = $scope.campaignDetails.act_budget + $scope.GST;
      //     $scope.onchecked = true;
      //     // $scope.checked = false;
      //   }
      // };
      // $scope.downloadAdminQuote = function (campaignId) {
      //   CampaignService.downloadQuote(campaignId).then(function (result) {
      //     var campaignPdf = new Blob([result], {type: 'application/pdf;charset=utf-8'});
      //     FileSaver.saveAs(campaignPdf, 'campaigns.pdf');
      //     if (result.status) {
      //         toastr.error(result.meesage);
      //     }
      // });
      //   var downloadToEmail = {
      //     campaign_id: campaignId,
      //     email: JSON.parse(localStorage.loggedInUser).email,
      //   };
      //   CampaignService.shareandDownloadCampaignToEmail(downloadToEmail).then(function (result) {
      //     if (result.status == 1) {
      //       $mdSidenav('shareCampaignSidenav').close();
      //       $mdDialog.show(
      //         $mdDialog.alert()
      //           .parent(angular.element(document.querySelector('body')))
      //           .clickOutsideToClose(true)
      //           .title(result.message)
      //           .ariaLabel('Alert Dialog Demo')
      //           .ok('Got it!')
      //           .targetEvent(ev)
      //       );
      //       UsershareCampaign();
      //     }
      //     else {
      //       toastr.error(result.message);
      //     }
      //   });
      //               },
      $scope.downloadAdminQuote = function (campaignId) {
        CampaignService.downloadQuote(campaignId).then(function (result) {
          var campaignPdf = new Blob([result], {
            type: "application/pdf;charset=utf-8",
          });
          FileSaver.saveAs(campaignPdf, "campaigns.pdf");
          if (result.status) {
            toastr.error(result.meesage);
          }
        });
        var downloadToEmail = {
          campaign_id: campaignId,
          email: JSON.parse(localStorage.loggedInUser).email,
        };
        CampaignService.shareandDownloadCampaignToEmail(downloadToEmail).then(
          function (result) {
            if (result.status == 1) {
              $mdSidenav("shareCampaignSidenav").close();
              $mdDialog.show(
                $mdDialog
                  .alert()
                  .parent(angular.element(document.querySelector("body")))
                  .clickOutsideToClose(true)
                  .title(result.message)
                  //.textContent('You can specify some description text in here.')
                  .ariaLabel("Alert Dialog Demo")
                  .ok("Confirmed!")
                  .targetEvent(ev)
              );
              UsershareCampaign();
            } else {
              toastr.error(result.message);
            }
          }
        );
      };

      //Download PDF

      $scope.downloadPDF = function (campaignId) {
        CampaignService.downloadPDF(campaignId).then(function (result) {
          var campaignPdf = new Blob([result], {
            type: "application/pdf;charset=utf-8",
          });
          FileSaver.saveAs(campaignPdf, "campaigns.pdf");
          if (result.status) {
            toastr.error(result.meesage);
          }
        });
        // var downloadToEmail = {
        //   campaign_id: campaignId,
        //   email: JSON.parse(localStorage.loggedInUser).email,
        // };
        // CampaignService.shareandDownloadCampaignToEmail(downloadToEmail).then(
        //   function (result) {
        //     if (result.status == 1) {
        //       $mdSidenav("shareCampaignSidenav").close();
        //       $mdDialog.show(
        //         $mdDialog
        //           .alert()
        //           .parent(angular.element(document.querySelector("body")))
        //           .clickOutsideToClose(true)
        //           .title(result.message)
        //           //.textContent('You can specify some description text in here.')
        //           .ariaLabel("Alert Dialog Demo")
        //           .ok("Confirmed!")
        //       );
        //       UsershareCampaign();
        //     } else {
        //       toastr.error(result.message);
        //     }
        //   }
        // );
      };

      //RFP Search Criteria PDF
      ($scope.downloadRFPsearch = function (campaignId) {
        CampaignService.downloadRFPsearchCriteria(campaignId).then(function (
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
        //Export to CSV
        ($scope.openExportCsvSlushBucket = function (campaignDetails) {
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
                      $scope.personalizeColumns.available_columns.indexOf(opt),
                      1
                    );
                    $scope.personalizeColumns.selected_columns.push(opt);
                    break;
                  case "left":
                    const cho = $scope.activeOption.option;
                    $scope.activeOption.isAvailable = true;
                    $scope.personalizeColumns.selected_columns.splice(
                      $scope.personalizeColumns.selected_columns.indexOf(cho),
                      1
                    );
                    $scope.personalizeColumns.available_columns.push(cho);
                    break;
                  case "up":
                    console.log($scope.activeOption);
                    console.log($scope.personalizeColumns.selected_columns);
                    var selectedIndex =
                      $scope.personalizeColumns.selected_columns.findIndex(
                        (item) =>
                          item.field_name ===
                          $scope.activeOption.option.field_name
                      );
                    console.log(selectedIndex);
                    if (selectedIndex) {
                      var tempOption = {
                        ...$scope.personalizeColumns.selected_columns[
                          selectedIndex - 1
                        ],
                      };
                      $scope.personalizeColumns.selected_columns[
                        selectedIndex - 1
                      ] = {
                        ...$scope.personalizeColumns.selected_columns[
                          selectedIndex
                        ],
                      };
                      $scope.personalizeColumns.selected_columns[
                        selectedIndex
                      ] = {
                        ...tempOption,
                      };
                    } else {
                      var tempOption = {
                        ...$scope.personalizeColumns.selected_columns[
                          selectedIndex
                        ],
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
                    console.log($scope.personalizeColumns.selected_columns);
                    var selectedIndex =
                      $scope.personalizeColumns.selected_columns.findIndex(
                        (item) =>
                          item.field_name ===
                          $scope.activeOption.option.field_name
                      );
                    console.log(selectedIndex);
                    if (
                      selectedIndex !==
                      $scope.personalizeColumns.selected_columns.length - 1
                    ) {
                      var tempOption = {
                        ...$scope.personalizeColumns.selected_columns[
                          selectedIndex + 1
                        ],
                      };
                      $scope.personalizeColumns.selected_columns[
                        selectedIndex + 1
                      ] = {
                        ...$scope.personalizeColumns.selected_columns[
                          selectedIndex
                        ],
                      };
                      $scope.personalizeColumns.selected_columns[
                        selectedIndex
                      ] = {
                        ...tempOption,
                      };
                    } else {
                      var tempOption = {
                        ...$scope.personalizeColumns.selected_columns[
                          selectedIndex
                        ],
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

              function formatDate(dt, timezone) {
                // var dt = new Date(dt);
                // var year = dt.getFullYear();
                // var month = dt.getMonth() + 1;
                // var day = dt.getDate();
                // return (
                //   year +
                //   "-" +
                //   (month < 0 ? "0" + month : month) +
                //   "-" +
                //   (day < 0 ? "0" + day : day)
                // );
                return TimezoneService.convertSingleDateToMMDDYYYY(
                  dt,
                  timezone
                );
              }
              function formateDateForNum(dt, timezone) {
                const dtValue = dt.$date.$numberLong;
                // return moment(new Date(+dtValue)).format("YYYY-MM-DD");
                return TimezoneService.convertSingleDateToMMDDYYYY(
                  dtValue,
                  timezone
                );
              }
              $scope.convertToCSV = function (headers) {
                try {
                  var csvData = Object.values(headers).join(",");
                  csvData += "\r\n";
                  campaignDetails.products.forEach((row) => {
                    var rowData = [];
                    Object.keys(headers).forEach((col) => {
                      var fieldData = "";
                      if (col.indexOf("::") > -1) {
                        const fieldName = col.split("::");
                        fieldData = row[fieldName[0]]
                          ? row[fieldName[0]]
                          : row[fieldName[1]];
                      } else if (col == "booked_from" || col == "booked_to") {
                        fieldData = formatDate(
                          row[col],
                          row["area_time_zone_type"]
                        ); //YYYY-MM-DD
                      } else if (col == "from_date" || col == "to_date") {
                        fieldData = formateDateForNum(
                          row[col],
                          row["area_time_zone_type"]
                        );
                      } else if (
                        col == "price" ||
                        col == "offerprice" ||
                        col == "cpm"
                      ) {
                        var offerPrice = row[col];
                        fieldData = "$" + Number(offerPrice).toFixed(2);
                      } else if (col == "impressionsperselectedDates") {
                        var impression = row[col];
                        fieldData = Number(impression).toFixed(0);
                      } else if (col == "sold_status") {
                        fieldData = row[col] ? "Sold out" : "Available";
                      } else if (col == "total") {
                        fieldData =
                          "$" + (row["quantity"] * row["price"]).toFixed(2);
                      } else if (col == "state_name_dma") {
                        fieldData = row["state_name"];
                      } else if (col == "ageloopLength") {
                        if (
                          row.type == "Digital" ||
                          row.type == "Digital/Static"
                        ) {
                          fieldData = row["ageloopLength"];
                        } else {
                          fieldData = "";
                        }
                      } else if (col == "break_ageloopLength") {
                        if (row.type == "Media") {
                          fieldData = row["ageloopLength"];
                        } else {
                          fieldData = "";
                        }
                      } else {
                        fieldData = row[col];
                      }
                      fieldData = fieldData?.toString().replace(/,/g, "__");
                      fieldData = fieldData?.toString().replace(/:/g, "__");
                      fieldData = fieldData?.toString().replace(/;/g, "__");
                      fieldData = fieldData?.toString().replace(/\r\n/g, "__");
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
              CampaignService.getColumnsToExport().then(function (result) {
                $scope.personalizeColumns = result;
                $rootScope.loading = false;
              });

              //save columns
              $scope.saveColumns = function () {
                const payload = {
                  report_type: "report_campaign",
                  selected_columns_post: JSON.stringify(
                    $scope.personalizeColumns.selected_columns.map((item) => {
                      return {
                        field_name: item.field_name,
                        label: item.label,
                      };
                    })
                  ),
                };
                CampaignService.saveColumnsToExport(payload).then(function (
                  result
                ) {
                  if (result.status == "1") {
                    const headers = {};
                    $scope.personalizeColumns.selected_columns.forEach(
                      (item) => {
                        if (item.label === "DMA") {
                          headers[`${item.field_name}_dma`] = item.label;
                        } else if (item.label === "Break/Loop Length") {
                          headers[`break_${item.field_name}`] = item.label;
                        } else {
                          headers[item.field_name] = item.label;
                        }
                      }
                    );
                    $scope.downloadCSVFile("campaign-products", headers);
                    toastr.success(result.message);
                  } else {
                    toastr.error(result.message);
                  }
                  $mdDialog.hide();
                });
              };
            },
          });
        });

      $scope.viewProductImage = function (image) {
        var imagePath = config.serverUrl + image;
        $mdDialog.show({
          locals: {
            src: imagePath,
          },
          templateUrl: "views/image-popup-large.html",
          fullscreen: $scope.customFullscreen,
          clickOutsideToClose: true,
          controller: function ($scope, $mdDialog, src) {
            $scope.img_src = src;
            $scope.close = function () {
              $mdDialog.hide();
            };
          },
          resolve: {
            imageCtrl: [
              "$ocLazyLoad",
              function ($ocLazyLoad) {
                return $ocLazyLoad.load("./controllers/ImageCtrl.js");
              },
            ],
          },
        });
      };

      //  Sorting user saved campaign
      $scope.sortAsc = function (headingName, type) {
        $scope.upArrowColour = headingName;
        $scope.sortType = "Asc";
        if (type == "string") {
          $scope.newOfferData = $scope.userSavedCampaigns.map((e) => {
            return {
              ...e,
              first_name: e.first_name,
              company_type: e.company_type,
              email: e.email,
              company_name: e.company_name,
            };
          });
          $scope.userSavedCampaigns = [];
          $scope.userSavedCampaigns = $scope.newOfferData.sort((a, b) => {
            console.log(a[headingName]);
            if (a[headingName] != null) {
              return a[headingName].localeCompare(b[headingName], undefined, {
                numeric: true,
                sensitivity: "base",
              });
            }
          });

          // $scope.productList = $scope.newOfferData;
        }
        $scope.userSavedCampaigns = $scope.userSavedCampaigns.sort((a, b) => {
          if (type == "boolean") {
            return a[headingName] ? 1 : -1;
          } else if (type == "date") {
            return (
              new Date(a[headingName].date) - new Date(b[headingName].date)
            );
          } else {
            return a[headingName] - b[headingName];
          }
        });
        console.log($scope.userSavedCampaigns);
      };

      $scope.sortDsc = function (headingName, type) {
        $scope.downArrowColour = headingName;
        $scope.sortType = "Dsc";
        if (type == "string") {
          $scope.newOfferData = $scope.userSavedCampaigns.map((e) => {
            return {
              ...e,
              first_name: e.first_name,
              company_type: e.company_type,
              email: e.email,
              company_name: e.company_name,
            };
          });
          $scope.userSavedCampaigns = [];
          $scope.userSavedCampaigns = $scope.newOfferData.sort((a, b) => {
            console.log(a[headingName]);
            if (b[headingName] != null) {
              return b[headingName].localeCompare(a[headingName], undefined, {
                numeric: true,
                sensitivity: "base",
              });
            }
          });

          // $scope.RfpData = $scope.newOfferData;
        }
        $scope.userSavedCampaigns = $scope.userSavedCampaigns.sort((a, b) => {
          if (type == "boolean") {
            return a[headingName] ? -1 : 1;
          } else if (type == "date") {
            return (
              new Date(b[headingName].date) - new Date(a[headingName].date)
            );
          } else {
            return b[headingName] - a[headingName];
          }
        });
        console.log($scope.userSavedCampaigns);
      };
      //  Sorting

      //  Sorting planned campaign
      $scope.sortAscp = function (headingName, type) {
        $scope.upArrowColour = headingName;
        $scope.sortType = "Ascp";
        if (type == "string") {
          $scope.newOfferData = $scope.plannedCampaigns.map((e) => {
            return {
              ...e,
              first_name: e.first_name,
              company_type: e.company_type,
              email: e.email,
              company_name: e.company_name,
            };
          });
          $scope.plannedCampaigns = [];
          $scope.plannedCampaigns = $scope.newOfferData.sort((a, b) => {
            console.log(a[headingName]);
            if (a[headingName] != null) {
              return a[headingName].localeCompare(b[headingName], undefined, {
                numeric: true,
                sensitivity: "base",
              });
            }
          });

          // $scope.productList = $scope.newOfferData;
        }
        $scope.plannedCampaigns = $scope.plannedCampaigns.sort((a, b) => {
          if (type == "boolean") {
            return a[headingName] ? 1 : -1;
          } else if (type == "date") {
            return (
              new Date(a[headingName].date) - new Date(b[headingName].date)
            );
          } else {
            return a[headingName] - b[headingName];
          }
        });
        console.log($scope.plannedCampaigns);
      };

      $scope.sortDscp = function (headingName, type) {
        $scope.downArrowColour = headingName;
        $scope.sortType = "Dscp";
        if (type == "string") {
          $scope.newOfferData = $scope.plannedCampaigns.map((e) => {
            return {
              ...e,
              first_name: e.first_name,
              company_type: e.company_type,
              email: e.email,
              company_name: e.company_name,
            };
          });
          $scope.plannedCampaigns = [];
          $scope.plannedCampaigns = $scope.newOfferData.sort((a, b) => {
            console.log(a[headingName]);
            if (b[headingName] != null) {
              return b[headingName].localeCompare(a[headingName], undefined, {
                numeric: true,
                sensitivity: "base",
              });
            }
          });

          // $scope.RfpData = $scope.newOfferData;
        }
        $scope.plannedCampaigns = $scope.plannedCampaigns.sort((a, b) => {
          if (type == "boolean") {
            return a[headingName] ? -1 : 1;
          } else if (type == "date") {
            return (
              new Date(b[headingName].date) - new Date(a[headingName].date)
            );
          } else {
            return b[headingName] - a[headingName];
          }
        });
        console.log($scope.plannedCampaigns);
      };
      //  Sorting ends

      //  Sorting Secheduled campaign
      $scope.sortAscs = function (headingName, type) {
        $scope.upArrowColour = headingName;
        $scope.sortType = "Ascs";
        if (type == "string") {
          $scope.newOfferData = $scope.SheduledCampaigns.map((e) => {
            return {
              ...e,
              first_name: e.first_name,
              company_type: e.company_type,
              email: e.email,
              company_name: e.company_name,
            };
          });
          $scope.SheduledCampaigns = [];
          $scope.SheduledCampaigns = $scope.newOfferData.sort((a, b) => {
            console.log(a[headingName]);
            if (a[headingName] != null) {
              return a[headingName].localeCompare(b[headingName], undefined, {
                numeric: true,
                sensitivity: "base",
              });
            }
          });
        }
        $scope.SheduledCampaigns = $scope.SheduledCampaigns.sort((a, b) => {
          if (type == "boolean") {
            return a[headingName] ? 1 : -1;
          } else if (type == "date") {
            return (
              new Date(a[headingName].date) - new Date(b[headingName].date)
            );
          } else {
            return a[headingName] - b[headingName];
          }
        });
        console.log($scope.SheduledCampaigns);
      };

      $scope.sortDscs = function (headingName, type) {
        $scope.downArrowColour = headingName;
        $scope.sortType = "Dscs";
        if (type == "string") {
          $scope.newOfferData = $scope.SheduledCampaigns.map((e) => {
            return {
              ...e,
              first_name: e.first_name,
              company_type: e.company_type,
              email: e.email,
              company_name: e.company_name,
            };
          });
          $scope.SheduledCampaigns = [];
          $scope.SheduledCampaigns = $scope.newOfferData.sort((a, b) => {
            console.log(a[headingName]);
            if (b[headingName] != null) {
              return b[headingName].localeCompare(a[headingName], undefined, {
                numeric: true,
                sensitivity: "base",
              });
            }
          });
        }
        $scope.SheduledCampaigns = $scope.SheduledCampaigns.sort((a, b) => {
          if (type == "boolean") {
            return a[headingName] ? -1 : 1;
          } else if (type == "date") {
            return (
              new Date(b[headingName].date) - new Date(a[headingName].date)
            );
          } else {
            return b[headingName] - a[headingName];
          }
        });
        console.log($scope.SheduledCampaigns);
      };
      //  Sorting ends

      //  Sorting Running campaign
      $scope.sortAscr = function (headingName, type) {
        $scope.upArrowColour = headingName;
        $scope.sortType = "Ascr";
        if (type == "string") {
          $scope.newOfferData = $scope.runningCampaigns.map((e) => {
            return {
              ...e,
              first_name: e.first_name,
              company_type: e.company_type,
              email: e.email,
              company_name: e.company_name,
            };
          });
          $scope.runningCampaigns = [];
          $scope.runningCampaigns = $scope.newOfferData.sort((a, b) => {
            console.log(a[headingName]);
            if (a[headingName] != null) {
              return a[headingName].localeCompare(b[headingName], undefined, {
                numeric: true,
                sensitivity: "base",
              });
            }
          });
        }
        $scope.runningCampaigns = $scope.runningCampaigns.sort((a, b) => {
          if (type == "boolean") {
            return a[headingName] ? 1 : -1;
          } else if (type == "date") {
            return (
              new Date(a[headingName].date) - new Date(b[headingName].date)
            );
          } else {
            return a[headingName] - b[headingName];
          }
        });
        console.log($scope.runningCampaigns);
      };

      $scope.sortDscr = function (headingName, type) {
        $scope.downArrowColour = headingName;
        $scope.sortType = "Dscr";
        if (type == "string") {
          $scope.newOfferData = $scope.runningCampaigns.map((e) => {
            return {
              ...e,
              first_name: e.first_name,
              company_type: e.company_type,
              email: e.email,
              company_name: e.company_name,
            };
          });
          $scope.runningCampaigns = [];
          $scope.runningCampaigns = $scope.newOfferData.sort((a, b) => {
            console.log(a[headingName]);
            if (b[headingName] != null) {
              return b[headingName].localeCompare(a[headingName], undefined, {
                numeric: true,
                sensitivity: "base",
              });
            }
          });
        }
        $scope.runningCampaigns = $scope.runningCampaigns.sort((a, b) => {
          if (type == "boolean") {
            return a[headingName] ? -1 : 1;
          } else if (type == "date") {
            return (
              new Date(b[headingName].date) - new Date(a[headingName].date)
            );
          } else {
            return b[headingName] - a[headingName];
          }
        });
        console.log($scope.runningCampaigns);
      };
      //  Sorting ends

      //  Sorting Closed campaign
      $scope.sortAscrc = function (headingName, type) {
        $scope.upArrowColour = headingName;
        $scope.sortType = "Asccf";
        if (type == "string") {
          $scope.newOfferData = $scope.closedCampaigns.map((e) => {
            return {
              ...e,
              first_name: e.first_name,
              company_type: e.company_type,
              email: e.email,
              company_name: e.company_name,
            };
          });
          $scope.closedCampaigns = [];
          $scope.closedCampaigns = $scope.newOfferData.sort((a, b) => {
            console.log(a[headingName]);
            if (a[headingName] != null) {
              return a[headingName].localeCompare(b[headingName], undefined, {
                numeric: true,
                sensitivity: "base",
              });
            }
          });
        }
        $scope.closedCampaigns = $scope.closedCampaigns.sort((a, b) => {
          if (type == "boolean") {
            return a[headingName] ? 1 : -1;
          } else if (type == "date") {
            return (
              new Date(a[headingName].date) - new Date(b[headingName].date)
            );
          } else {
            return a[headingName] - b[headingName];
          }
        });
        console.log($scope.closedCampaigns);
      };

      $scope.sortDscrc = function (headingName, type) {
        $scope.downArrowColour = headingName;
        $scope.sortType = "Dsccf";
        if (type == "string") {
          $scope.newOfferData = $scope.closedCampaigns.map((e) => {
            return {
              ...e,
              first_name: e.first_name,
              company_type: e.company_type,
              email: e.email,
              company_name: e.company_name,
            };
          });
          $scope.closedCampaigns = [];
          $scope.closedCampaigns = $scope.newOfferData.sort((a, b) => {
            console.log(a[headingName]);
            if (b[headingName] != null) {
              return b[headingName].localeCompare(a[headingName], undefined, {
                numeric: true,
                sensitivity: "base",
              });
            }
          });
        }
        $scope.closedCampaigns = $scope.closedCampaigns.sort((a, b) => {
          if (type == "boolean") {
            return a[headingName] ? -1 : 1;
          } else if (type == "date") {
            return (
              new Date(b[headingName].date) - new Date(a[headingName].date)
            );
          } else {
            return b[headingName] - a[headingName];
          }
        });
        console.log($scope.closedCampaigns);
      };
      //  Sorting ends

      //  Sorting Cancelled campaign
      $scope.sortAscrcc = function (headingName, type) {
        $scope.upArrowColour = headingName;
        $scope.sortType = "Ascc";
        if (type == "string") {
          $scope.newOfferData = $scope.cancelledCampaigns.map((e) => {
            return {
              ...e,
              first_name: e.first_name,
              company_type: e.company_type,
              email: e.email,
              company_name: e.company_name,
            };
          });
          $scope.cancelledCampaigns = [];
          $scope.cancelledCampaigns = $scope.newOfferData.sort((a, b) => {
            console.log(a[headingName]);
            if (a[headingName] != null) {
              return a[headingName].localeCompare(b[headingName], undefined, {
                numeric: true,
                sensitivity: "base",
              });
            }
          });
        }
        $scope.cancelledCampaigns = $scope.cancelledCampaigns.sort((a, b) => {
          if (type == "boolean") {
            return a[headingName] ? 1 : -1;
          } else if (type == "date") {
            return (
              new Date(a[headingName].date) - new Date(b[headingName].date)
            );
          } else {
            return a[headingName] - b[headingName];
          }
        });
        console.log($scope.cancelledCampaigns);
      };

      $scope.sortDscrcc = function (headingName, type) {
        $scope.downArrowColour = headingName;
        $scope.sortType = "Dscc";
        if (type == "string") {
          $scope.newOfferData = $scope.cancelledCampaigns.map((e) => {
            return {
              ...e,
              first_name: e.first_name,
              company_type: e.company_type,
              email: e.email,
              company_name: e.company_name,
            };
          });
          $scope.cancelledCampaigns = [];
          $scope.cancelledCampaigns = $scope.newOfferData.sort((a, b) => {
            console.log(a[headingName]);
            if (b[headingName] != null) {
              return b[headingName].localeCompare(a[headingName], undefined, {
                numeric: true,
                sensitivity: "base",
              });
            }
          });
        }
        $scope.cancelledCampaigns = $scope.cancelledCampaigns.sort((a, b) => {
          if (type == "boolean") {
            return a[headingName] ? -1 : 1;
          } else if (type == "date") {
            return (
              new Date(b[headingName].date) - new Date(a[headingName].date)
            );
          } else {
            return b[headingName] - a[headingName];
          }
        });
        console.log($scope.cancelledCampaigns);
      };
      //  Sorting ends

      // Send & get comment
      /*$scope.sendquerry = function (campID,message) {
    var data = {id:campID,message:message}
    CampaignService.sendComment(data).then(function (result) {
        if (result.status == 1) {
            //toastr.success(result.message);
            alert("Success!!!!");
            // $scope.sendquerryErrors = null;
            // $scope.sendquerry = {};
            // $scope.forms.sendquerryForm.$setPristine();
            // $scope.forms.sendquerryForm.$setUntouched();
        } else if (result.status == 0) {
            // $scope.sendquerryErrors = result.message;
            alert("Error!!!")
        }
    }, function (error) {
        toastr.error("somthing went wrong please try agin later");
    });
}
$scope.Getcomment = function (campaignID){  
  var data = {id:campaignID}
  CampaignService.getComment(data).then((result) => {
    console.log(result);
    $scope.comments = result;
});
}*/
      // Send and Get comment Ends

      $scope.confirmCampaignBooking = function (campaignId) {
        CampaignService.confirmCampaignBooking(campaignId).then(function (
          result
        ) {
          console.log(result);
          if (result.status == 1) {
            $mdDialog.show(
              $mdDialog
                .alert()
                .parent(angular.element(document.querySelector("body")))
                .clickOutsideToClose(true)
                //.title(result.message)
                .title("Thank you! We have received your request")
                .textContent("We will be in touch with you shortly")
                .ariaLabel("Alert Dialog Demo")
                .ok("Confirmed!")
              // .targetEvent(ev)
            );
            $scope.getCampaignDetails(campaignId);
          } else if (result.status == 0) {
            toastr.error(result.message);
          }
        });
      };

      $scope.notifyUsershortlistedProduct = function (produtIDs) {
        var ProductIDList = {
          product_ids: produtIDs,
        };
        CampaignService.notifyUsershortlistedProduct(ProductIDList).then(
          function (result) {
            //console.log('result', result)
          }
        );
      };

      $scope.conformDeleteProductFromCampaign = function (shortlistId) {
        $scope.shortlistId = shortlistId;
        $scope.selectCampaignId = false;
        $scope.selectProductId = true;
      };
      $scope.deleteProductFromCampaign = function (
        campaignId,
        price,
        newprice
      ) {
        var pprice =
          $scope.offerDetails.offers.length > 0 &&
          ($scope.offerDetails.offers[0].status == 20 ||
            ($scope.offerDetails.offers.length == 2 &&
              $scope.offerDetails.offers[1].status == 20))
            ? newprice
            : price;
        CampaignService.deleteProductFromUserCampaign(
          campaignId,
          $scope.shortlistId,
          pprice
        ).then(function (result) {
          if (result.status == 1) {
            toastr.success(result.message);
            CampaignService.getCampaignWithProducts(campaignId).then(function (
              result
            ) {
              $scope.campaignDetails = result;
              if ($scope.campaignDetails.status == 100) {
                $scope.status = $scope.campaignDetails.status;
                $scope.campaignDetails.products.forEach((s) => {
                  if (s.deleteProductStatus && s.deleteProductStatus == 101) {
                    $scope.status = s.deleteProductStatus;
                  }
                });
              }
            });
          } else {
            toastr.error(result.message);
          }
        });
      };

      $scope.DeleteCampaign = function () {
        var param = $location.$$url.split("/")[2];
        CampaignService.deleteUserCampaign(param).then(function (result) {
          if (result.status == 1) {
            toastr.success(result.message);
            if ($location.$$url.split("/")[3] == "1") {
              $window.location.href = "/user-saved-campaigns";
            }
            // else {
            //   $window.location.href = "/campaigns";
            // }
          } else {
            toastr.error(result.message);
          }
        });
      };

      /*---MULTIPLE SLOTS---*/
      $scope.getDate = function (dt) {
        const dtValue = dt.$date.$numberLong;
        return moment(new Date(+dtValue)).format("YYYY-MM-DD");
      };

      $scope.dateRangePicker = null;
      $scope.selectedProduct = null;
      /*--for date range calendar--*/
      $scope.ranges = {};
      $scope.ranges.selectedDateRanges = [];
      $scope.selectedDateRanges = [
        {
          startDate: new Date(),
          endDate: new Date(),
        },
      ];
      /*
    $scope.selectedDateRanges  = {
      startDate: new Date(),
      endDate: new Date()
    };*/
      $scope.customOptions = {};
      $scope.selectedCalendarDates = [];
      $scope.startDate = moment();
      $scope.endDate = moment();
      $scope.mapProductOpts = {
        multipleDateRanges: true,
        autoUpdateInput: true,
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
          "apply.daterangepicker": function (ev) {
            if (ev.model && ev.model.length) {
              $scope.selectedDateRanges = [...ev.model];
              $("#modifyProductModal-" + $scope.selectedProduct.id).modal(
                "show"
              );
              $scope.isFromApplyDateRangePicker = true;
              $scope.openProductEdit($scope.selectedProduct);
            } else {
              $("#modifyProductModal-" + $scope.selectedProduct.id).modal(
                "show"
              );
              $scope.isFromApplyDateRangePicker = false;
              $scope.openProductEdit($scope.selectedProduct);
            }
          },
          "show.daterangepicker": function (ev) {
            $scope.dateRangePicker
              .data("daterangepicker")
              .setStartDate(new Date($scope.selectedDateRanges[0].startDate));
            $scope.dateRangePicker
              .data("daterangepicker")
              .setEndDate(new Date($scope.selectedDateRanges[0].endDate));
          },
          "cancel.daterangepicker": function (ev) {
            console.log("cancel", ev);
            $scope.isFromApplyDateRangePicker = false;
            $scope.ranges.selectedDateRanges = [];
          },
          "hide.daterangepicker": function (ev) {
            console.log("hide", ev);
            $scope.isFromApplyDateRangePicker = false;
            $scope.ranges.selectedDateRanges = [];
          },
        },

        // isInvalidDate: function(dt) {
        //   for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
        //     if (
        //       moment(dt) >= moment($scope.unavailalbeDateRanges[i].booked_from) &&
        //       moment(dt) <= moment($scope.unavailalbeDateRanges[i].booked_to)
        //     ) {
        //       return true;
        //     }
        //   }

        //   if (moment(dt) < moment()) {
        //     return true;
        //   }
        // }
      };
      /*--//for date range calendar--*/
      $scope.isFromApplyDateRangePicker = false;
      $scope.availableQuantity = 1;
      $scope.selected = {
        quantity: 0,
        dates: [
          {
            startDate: new Date(),
            endDate: new Date(),
          },
        ],
      };
      $scope.fromDate = new Date();
      $scope.endDate = new Date();

      $scope.openProductEdit = function (product) {
        console.log(product);

        $scope.selectedProduct = product;
        $scope.selected.quantity = parseInt(product.quantity);
        $scope.startDate = $scope.getDate(product.from_date);
        $scope.endDate = $scope.getDate(product.to_date);

        const today = moment(new Date()).format("YYYY-MM-DD");

        if ($scope.startDate < today) {
          $scope.startDate = today;
        }

        console.log($scope.startDate, $scope.endDate);

        if (!$scope.isFromApplyDateRangePicker) {
          // $scope.selectedDateRanges.startDate = product.booked_from;
          // $scope.selectedDateRanges.endDate = product.booked_to;
          $scope.selectedDateRanges[0].startDate = product.booked_from;
          $scope.selectedDateRanges[0].endDate = product.booked_to;

          $scope.selectedDateRanges.forEach((_, i) => {
            if (i) $scope.selectedDateRanges.splice(i, 1);
          });
        }

        MapService.getQuantity(
          product.product_id,
          moment(new Date($scope.selectedDateRanges[0].startDate)).format(
            "YYYY-MM-DD"
          ),
          moment(new Date($scope.selectedDateRanges[0].endDate)).format(
            "YYYY-MM-DD"
          )
        ).then(function (result) {
          $scope.availableQuantity = parseInt(result);
        });

        // MapService.getProductUnavailableDates(product.product_id).then(function (
        //   dateRanges
        // ) {
        //   $(".warn-text").text("");
        //   $scope.unavailalbeDateRanges = dateRanges;
        // });
      };

      $scope.updateProductDatesQuantity = function (product) {
        const payload = {
          campaign_id: product.campaign_id,
          booking_id: product.id,
          product_id: product.product_id,
          quantity: $scope.selected.quantity,
          dates: $scope.selectedDateRanges,
        };
        CampaignService.updateProductDatesQuantity(payload).then((result) => {
          if (result.status == "1") {
            $scope.ranges.selectedDateRanges = [];
            $scope.isFromApplyDateRangePicker = false;
            toastr.success(result.message);
            $("#modifyProductModal-" + product.id).modal("hide");
            $scope.getCampaignDetails(product.campaign_id);
          } else {
            toastr.error(result.message);
            $scope.isFromApplyDateRangePicker = false;
          }
        });
      };

      $scope.cancelProductDatesQuantity = function () {
        $scope.ranges.selectedDateRanges = [];
        $scope.isFromApplyDateRangePicker = false;
      };

      $scope.openCalendar = function (product) {
        $("#modifyProductModal-" + product.id).modal("hide");
        $scope.dateRangePicker = $("#booking_dates_picker-" + product.id);
        $scope.dateRangePicker.trigger("click");
      };

      $scope.cancelProductFromCampaign = function (productId, campaignId) {
        CampaignService.cancelProductFromUserCampaign(
          campaignId,
          productId
        ).then(function (result) {
          if (result.status == 1) {
            CampaignService.getCampaignWithProducts(campaignId).then(function (
              result
            ) {
              $scope.campaignDetails = result;
            });
            toastr.success(result.message);
          } else {
            toastr.error(result.message);
          }
        });
      };
      $scope.changeQuoteRequest = function (campaignId, remark, type) {
        $scope.changeRequest = {};
        $scope.changeRequest.for_campaign_id = campaignId;
        $scope.changeRequest.remark = remark;
        $scope.changeRequest.type = type;
        CampaignService.requestChangeInQuote($scope.changeRequest).then(
          function (result) {
            if (result.status == 1) {
              $scope.getCampaignDetails(campaignId);
              //$mdDialog.hide();
              toastr.success(result.message);
            } else {
              toastr.error(result.message);
            }
          }
        );
      };

      $scope.suggestionRequest = CampaignService.suggestedData;
      $scope.goToNextSuggestData = function (e) {
        if (
          $scope.suggestionRequest &&
          Object.keys($scope.suggestionRequest).length > 2
        ) {
          CampaignService.suggestedData = Object.assign(
            $scope.suggestionRequest,
            CampaignService.suggestedData
          );
          $location.path("/suggest/marketing-objectives");
        } else {
          e.preventDefault();
        }
      };
      $scope.goToAddAdvert = function (e) {
        if (
          $scope.suggestionRequest &&
          Object.keys($scope.suggestionRequest).length >= 4
        ) {
          CampaignService.suggestedData = Object.assign(
            $scope.suggestionRequest,
            CampaignService.suggestedData
          );
          $location.path("/suggest/advertising-objectives");
        } else {
          e.preventDefault();
        }
      };
      $scope.goToOtherInfo = function (e) {
        if (
          $scope.suggestionRequest &&
          Object.keys($scope.suggestionRequest).length > 8
        ) {
          CampaignService.suggestedData = Object.assign(
            $scope.suggestionRequest,
            CampaignService.suggestedData
          );
          $location.path("/suggest/other-info");
        } else {
          e.preventDefault();
        }
      };

      $scope.sendSuggestionRequest = function (ev) {
        if (
          $scope.suggestionRequest &&
          Object.keys($scope.suggestionRequest).length >= 13
        ) {
          CampaignService.suggestedData = Object.assign(
            $scope.suggestionRequest,
            CampaignService.suggestedData
          );
          CampaignService.sendSuggestionRequest(
            CampaignService.suggestedData
          ).then(function (result) {
            if (result.status == 1) {
              CampaignService.suggestedData = null;
              $scope.suggestMeRequestSent = true;
              $mdDialog
                .show(
                  $mdDialog
                    .alert()
                    .parent(angular.element(document.querySelector("body")))
                    .clickOutsideToClose(true)
                    .title("We will get back to you!!!!")
                    .textContent(result.message)
                    .ariaLabel("Alert Dialog Demo")
                    .ok("Confirmed!")
                    .targetEvent(ev)
                )
                .finally(function () {
                  $location.path("/home");
                });
            }
            if (result.status == 0) {
              $scope.suggestCampaignErrors = result.message;
            }
          });
        }
      };

      $scope.resetSuggestionForm = function () {
        $scope.suggestionRequest = null;
        CampaignService.suggestedData = null;
      };

      $scope.DeleteCampaignId = function (campaignId) {
        $scope.currentCampaignId = campaignId;
        $scope.selectCampaignId = true;
        $scope.selectProductId = false;
      };
      $scope.camptureSavedCampaignId = function (campaignId) {
        $scope.campaignIdToBeDeleted = campaignId;
      };
      $scope.deleteSavedCampaign = function () {
        if ($scope.campaignIdToBeDeleted) {
          CampaignService.deleteCampaign($scope.campaignIdToBeDeleted).then(
            function (result) {
              if (result.status == 1) {
                toastr.success(result.message);
                $scope.getUserCampaigns("saved_campaign");
              } else {
                toastr.error(result.message);
              }
            }
          );
        }
      };
      $scope.toggleShareCampaignSidenav = function (activeUserCampaign) {
        $scope.currentShareCampaign = activeUserCampaign;
        $mdSidenav("shareCampaignSidenav").toggle();
      };

      $scope.requestProposalForCampaign = function (campaignId, ev) {
        CampaignService.requestCampaignProposal(campaignId).then(function (
          result
        ) {
          if (result.status == 1) {
            $mdDialog.show(
              $mdDialog
                .alert()
                .parent(angular.element(document.querySelector("body")))
                .clickOutsideToClose(true)
                .title("We will get back to you!!!!")
                .textContent(result.message)
                .ariaLabel("Alert Dialog Demo")
                .ok("Confirmed!")
                .targetEvent(ev)
            );
            $scope.getCampaignDetails(campaignId);
          } else {
            toastr.error(result.message);
          }
        });
      };

      $scope.shareCampaignToEmail = function (
        ev,
        shareCampaign,
        campaignID,
        campaign_type
      ) {
        $scope.campaignToShare = $scope.campaignDetails;
        var campaignToEmail = {
          // campaign_id: $scope.campaignToShare.id,
          // email: shareCampaign.email,
          // receiver_name: shareCampaign.receiver_name,
          // campaign_type: $scope.campaignToShare.type
          campaign_id: campaignID,
          email: shareCampaign.email,
          receiver_name: shareCampaign.receiver_name,
          campaign_type: campaign_type,
        };
        CampaignService.shareCampaignToEmail(campaignToEmail).then(function (
          result
        ) {
          if (result.status == 1) {
            $mdSidenav("shareCampaignSidenav").close();
            $mdDialog.show(
              $mdDialog
                .alert()
                .parent(angular.element(document.querySelector("body")))
                .clickOutsideToClose(true)
                .title(result.message)
                //.textContent('You can specify some description text in here.')
                .ariaLabel("Alert Dialog Demo")
                .ok("Confirmed!")
                .targetEvent(ev)
            );
            UsershareCampaign();
          } else {
            toastr.error(result.message);
          }
        });
      };

      $scope.loggedinUser =
        JSON.parse(localStorage.loggedInUser).firstName +
        " " +
        JSON.parse(localStorage.loggedInUser).lastName;
      $scope.makeOfferCampaignTo = function (campaignID) {
        $scope.campaignToShare = $scope.campaignDetails;
        $scope.offerProductIDs = {};
        $scope.campaignDetails.products.forEach((product) => {
          $scope.offerProductIDs[product.id] = 0;
        });
        var campaignOfferParams = {
          campaign_id: campaignID,
          loggedinUser: JSON.parse(localStorage.loggedInUser),
          price: $scope.offerCampaigns.price,
          comments: $scope.offerCampaigns.comments,
          AdminOfferAcceptReject: $scope.offerProductIDs,
        };
        if (
          $scope.offerCampaigns.price <
          $scope.campaignDetails.totalamount -
            $scope.campaignDetails.gross_fee_price
        ) {
          CampaignService.makeOfferCampaignTo(campaignOfferParams).then(
            function (result) {
              if (result.status == 1) {
                $mdSidenav("shareCampaignSidenav").toggle();
                $mdDialog.show(
                  $mdDialog
                    .alert()
                    .parent(angular.element(document.querySelector("body")))
                    .clickOutsideToClose(true)
                    .title(result.message)
                    //.textContent('You can specify some description text in here.')
                    .ariaLabel("Alert Dialog Demo")
                    .ok("Confirmed!")
                  // .targetEvent(ev)
                );
                UserofferCampaign();
                getCampaignOffers();
              } else {
                toastr.error(result.message);
              }
            }
          );
        } else {
          alert(
            "Offered Price should be less the total price $" +
              (
                $scope.campaignDetails.totalamount -
                $scope.campaignDetails.gross_fee_price
              ).toFixed(2)
          );
        }
      };

      $scope.loggedinUser =
        JSON.parse(localStorage.loggedInUser).firstName +
        " " +
        JSON.parse(localStorage.loggedInUser).lastName;
      $scope.deleteCampaignRequest = function (campaignID) {
        $scope.campaignToShare = $scope.campaignDetails;
        var campaigndeleteParams = {
          campaign_id: campaignID,
          loggedinUser: JSON.parse(localStorage.loggedInUser),
          user_query: this.user_query,
          price: this.price,
        };
        CampaignService.deleteCampaignRequest(campaigndeleteParams).then(
          function (result) {
            if (result.status == 1) {
              $mdSidenav("shareCampaignSidenav").toggle();
              $mdDialog.show(
                $mdDialog
                  .alert()
                  .parent(angular.element(document.querySelector("body")))
                  .clickOutsideToClose(true)
                  .title(result.message)
                  //.textContent('You can specify some description text in here.')
                  .ariaLabel("Alert Dialog Demo")
                  .ok("Confirmed!")
                // .targetEvent(ev)
              );
              DeleteCampaign();
              $scope.getCampaignDetails(campaignID);
            } else {
              toastr.error(result.message);
            }
          }
        );
      };

      $scope.EditCampaignRequest = function (campaignID, elId) {
        $scope.campaignToShare = $scope.activeUserCampaign;
        var campaigndeletePara = {
          campaign_id: campaignID,
          name: this.currentName.name,
        };
        $scope.EditCampaign(elId);
        CampaignService.EditCampaignRequest(campaigndeletePara).then(function (
          result
        ) {
          if (result.status == 1) {
            // $mdSidenav('shareCampaignSidenav').toggle();
            $mdDialog.show(
              $mdDialog
                .alert()
                .parent(angular.element(document.querySelector("body")))
                .clickOutsideToClose(true)
                .title(result.message)
                //.textContent('You can specify some description text in here.')
                .ariaLabel("Alert Dialog Demo")
                .ok("Confirmed!")
              // .targetEvent(ev)
            );
            $scope.getUserCampaigns();
          } else {
            toastr.error(result.message);
          }
        });
      };

      // $scope.EditCampaigRequest = function (campaignID) {
      //   $scope.campaignToShare = $scope.activeUserCampaign;
      //   var campaigndeletePara = {
      //     campaign_id: campaignID,
      //     name: this.currentNamee.name
      //   };
      //   CampaignService.EditCampaigRequest(campaigndeletePara).then(function (result) {
      //     if (result.status == 1) {
      //       // $mdSidenav('shareCampaignSidenav').toggle();
      //       $mdDialog.show(
      //         $mdDialog.alert()
      //         .parent(angular.element(document.querySelector('body')))
      //         .clickOutsideToClose(true)
      //         .title(result.message)
      //         //.textContent('You can specify some description text in here.')
      //         .ariaLabel('Alert Dialog Demo')
      //         .ok('Confirmed!')
      //         // .targetEvent(ev)
      //       );
      //       EditCampaig();
      //       $scope.getUserCampaigns(campaignID)
      //     } else {
      //       toastr.error(result.message);
      //     }
      //   });
      // }

      // clone Campaign request
      $scope.CloneCampaignRequest = function (elId, campaignName, camapaign) {
        var payload = {
          id: camapaign.id,
          name: campaignName,
        };
        $scope.CloneCampaign(elId);
        CampaignService.CloneCampaignRequest(payload).then(function (result) {
          if (result.status == 1) {
            toastr.success(result.message);
            if ($rootScope.currStateName == "index.user-saved-campaigns") {
              $location.path(`/campaign-details/${result.campign_id}/1`);
            } else {
              $location.path("/user-saved-campaigns");
            }
          } else {
            toastr.error(result.message);
          }
        });
      };

      $scope.ProductIdsCampaign = function (campaignID) {
        if ($scope.deletedProductId.length == 0) {
          return;
        }
        $scope.campaignToShare = $scope.campaignDetails;
        var campaigndelParam = {
          campaign_id: campaignID,
          product_id: $scope.deletedProductId,
        };
        CampaignService.ProductIdsCampaign(campaigndelParam).then(function (
          result
        ) {
          $scope.purchase = result;
          $scope.finaldelpurchaseamt = 0;
          $scope.finaldelpurchaseamt = $scope.purchase.finaldelpurchaseamt;
        });
      };

      $scope.selectSingle = function (id) {
        $scope.selectedProductsToDelete = [];
        var Existingid = $scope.selectedids.indexOf(id);

        if (Existingid > -1) {
          $scope.selectedids.splice(Existingid, 1);
        } else {
          $scope.selectedids.push(id);
        }

        //  $scope.AdminNotificationService(selectedids)
      };

      var productGrpId = "";
      $scope.deletedProductId = [];
      $scope.productDetails = [];
      $scope.deletechProduct = function (event, id, productbookingid) {
        productGrpId = id + "-" + productbookingid;
        if (event) {
          $scope.deletedProductId.push(id);
          //delete product from campaign
          $scope.productDetails.push(productGrpId);
        } else {
          $scope.deletedProductId = $scope.deletedProductId.filter(
            (orgId) => orgId != id
          );
          $scope.productDetails = $scope.productDetails.filter(
            (orgId) => orgId != productGrpId
          );
        }
      };

      $scope.loggedinUser =
        JSON.parse(localStorage.loggedInUser).firstName +
        " " +
        JSON.parse(localStorage.loggedInUser).lastName;
      $scope.deleteProductRequest = function (campaignID) {
        if ($scope.deletedProductId.length == 0) {
          return;
        }
        $scope.campaignToShare = $scope.campaignDetails;
        var campaigndeleteParam = {
          campaign_id: campaignID,
          loggedinUser: JSON.parse(localStorage.loggedInUser),
          comments: this.comments,
          price: this.finaldelpurchaseamt,
          product_id: $scope.productDetails,
        };
        CampaignService.deleteProductRequest(campaigndeleteParam).then(
          function (result) {
            if (result.status == 1) {
              $mdSidenav("shareCampaignSidenav").toggle();
              $mdDialog.show(
                $mdDialog
                  .alert()
                  .parent(angular.element(document.querySelector("body")))
                  .clickOutsideToClose(true)
                  .title(result.message)
                  //.textContent('You can specify some description text in here.')
                  .ariaLabel("Alert Dialog Demo")
                  .ok("Confirmed!")
                // .targetEvent(ev)
              );
              DeleteProduct();
              $scope.deletedProductId = [];
              $scope.productDetails = [];
              $scope.getCampaignDetails(campaignID);
              $scope.campaignDetails.products =
                $scope.campaignDetails.products.map((obj) => {
                  return {
                    ...obj,
                    chkbox: false,
                  };
                });
              $scope.selectedids = [];
            } else {
              toastr.error(result.message);
            }
          }
        );
      };

      // $scope.makeOfferCampaignToEmail = function (ev, shareCampaign, campaignID, campaign_type) {
      //   $scope.campaignToShare = $scope.campaignDetails;
      //   var campaignToEmail = {
      //     // campaign_id: $scope.campaignToShare.id,
      //     // email: shareCampaign.email,
      //     // receiver_name: shareCampaign.receiver_name,
      //     // campaign_type: $scope.campaignToShare.type
      //     campaign_id: campaignID,
      //     email: shareCampaign.email,
      //     receiver_name: shareCampaign.receiver_name,
      //     campaign_type: campaign_type
      //   };
      //   CampaignService.makeOfferCampaignToEmail(campaignToEmail).then(function (result) {
      //     if (result.status == 1) {
      //       $mdSidenav('shareCampaignSidenav').close();
      //       $mdDialog.show(
      //         $mdDialog.alert()
      //         .parent(angular.element(document.querySelector('body')))
      //         .clickOutsideToClose(true)
      //         .title(result.message)
      //         //.textContent('You can specify some description text in here.')
      //         .ariaLabel('Alert Dialog Demo')
      //         .ok('Got it!')
      //         .targetEvent(ev)
      //       );
      //       UserofferCampaign();
      //     } else {
      //       toastr.error(result.message);
      //     }
      //   });
      // }

      function UsershareCampaign() {
        document.getElementById("usershareDrop").classList.toggle("show");
        // angular.element(document.querySelector("#saveCampaign")).addClass("hide");
        // angular.element(document.querySelector("#saveCampaign")).removeClass("show");
      }

      function UserofferCampaign() {
        document.getElementById("userofferDrop").classList.toggle("show");
        // angular.element(document.querySelector("#saveCampaign")).addClass("hide");
        // angular.element(document.querySelector("#saveCampaign")).removeClass("show");
      }
      function DeleteCampaign() {
        document.getElementById("deleteDrop").classList.toggle("show");
      }

      $scope.DeleteProduct = function () {
        document.getElementById("deleteDro").classList.toggle("show");
        document.getElementById("commentid").innerText = "";
      };

      $scope.EditCampaign = function (elId, activeUserCampaign) {
        //alert(elId);
        if (activeUserCampaign) {
          if ($scope.lastId) {
            document.getElementById($scope.lastId).classList.toggle("show");
          }
          document.getElementById(elId).classList.toggle("show");
          $scope.currentName = activeUserCampaign;
          $scope.lastId = elId;
        } else {
          document.getElementById(elId).classList.toggle("show");
          $scope.lastId = null;
        }
      };

      // clone campaign
      $scope.CloneCampaign = function (elId, activeUserCampaign) {
        if (activeUserCampaign) {
          if ($scope.cloneLastId) {
            document
              .getElementById($scope.cloneLastId)
              .classList.toggle("show");
          }
          document.getElementById(elId).classList.toggle("show");
          $scope.currentName = activeUserCampaign;
          $scope.cloneLastId = elId;
        } else {
          document.getElementById(elId).classList.toggle("show");
          $scope.cloneLastId = null;
        }
      };

      $scope.popup = function (data) {
        $scope.currentName = data;
      };

      // $scope.EditCampaig= function() {
      //   document.getElementById("deleteDroppp").classList.toggle("show");
      // }

      // $scope.popu= function(data) {
      //   $scope.currentNamee = data;
      // }
      // function getMetroCampaigns() {
      //   MetroService.getMetroCampaigns().then((result) => {
      //     $scope.metroCampaigns = result;
      //   });
      // }
      $scope.saveUserCampaign = function () {
        CampaignService.saveUserCampaign($scope.ownerCampaign).then(function (
          result
        ) {
          if (result.status == 1) {
            $scope.ownerCampaign = {};
            loadOwnerCampaigns();
            toastr.success(result.message);
            $state.reload();
          } else if (result.status == 0) {
            $rootScope.closeMdDialog();
            if (result.message.constructor == Array) {
              $scope.ownerCampaignErrors = result.message;
            } else {
              toastr.error(result.message);
            }
          } else {
            toastr.error(result.message);
          }
          UsersaveCampaign();
        });
      };

      function UsersaveCampaign() {
        document.getElementById("usersavedDropdown").classList.toggle("show");
      }

      //   function saveCampaign() {
      //     document.getElementById("savedDropdown").classList.toggle("show");
      // }
      //   function myFunction() {
      //     document.getElementById("savedDropdown").classList.toggle("show");
      // }
      // function close() {
      //   angular.element(document.querySelector("#saveCampaign")).addClass("hide");
      //   angular.element(document.querySelector("#saveCampaign")).removeClass("show");
      // }
      var loadOwnerCampaigns = function () {
        return new Promise((resolve, reject) => {
          CampaignService.getCampaignWithProducts().then(function (result) {
            //$scope.ownerCampaigns = result;
            $scope.loading = true;
            $scope.ownerCampaigns = _.filter(result, function (c) {
              $scope.loading = false;
              return c.status < 800;
            });
            $scope.scheduledCampaigns = _.filter(result, function (c) {
              $scope.loading = false;
              return c.status >= 800;
            });
            resolve(result);
          });
        });
      };
      // $scope.saveMetroCampaign = function (metroCampagin) {
      //   MetroService.saveMetroCampaign(metroCampagin).then(function (result) {
      //     $scope.metrocampaign = result;
      //     if (result.status == 1) {
      //       // $scope.forms.MetroCampaign.$setPristine();
      //       // $scope.forms.MetroCampaign.$setUntouched();
      //       toastr.success(result.message);
      //     } else if (result.status == 0) {
      //       $rootScope.closeMdDialog();
      //       if (result.message.constructor == Array) {
      //         $scope.MetroCampaignErrors = result.message;
      //       } else {
      //         toastr.error(result.message);
      //       }
      //     } else {
      //       toastr.error(result.message);
      //     }
      //     $scope.metrocampaign = {};
      //     UsersaveCampaign();
      //     getMetroCampaigns();
      //   });
      // };
      // function metroclose() {
      //   angular.element(document.querySelector("#saveCampaign")).addClass("hide");
      //   angular
      //     .element(document.querySelector("#saveCampaign"))
      //     .removeClass("show");
      // }
      // var loadMetroCampaigns = function () {
      //   return new Promise((resolve, reject) => {
      //     MetroService.getMetroCampaigns().then(function (result) {
      //       // $scope.metrocampaign = _.filter(result, function (c) {
      //       //   return c.status >= 1101 ;
      //       // });
      //       resolve(result);
      //     });
      //   });
      // };

      // function getMetroCampaignDetails() {
      //   MetroService.getMetroCampaigns().then((result) => {
      //     $scope.metrocampaign = result;
      //   });
      // }

      /*=========================
  | Page based initial loads
  =========================*/

      if ($rootScope.currStateName == "index.campaigns") {
        $scope.getUserCampaigns("insertion_order");
        // getMetroCampaigns();
      }

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
      function getCampaignOffers() {
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
      }
      if ($rootScope.currStateName == "index.campaign-details") {
        $scope.getCampaignDetails($stateParams.campaignId);
        $scope.offerDetails.campaignId = $stateParams.campaignId;
        getCampaignOffers();
      }
      if ($rootScope.currStateName == "index.product-camp-details") {
        $scope.prodDetails($stateParams.productId, true);
      }
      if ($rootScope.currStateName == "index.user-saved-campaigns") {
        $scope.getUserCampaigns("saved_campaign");
      }
      // if ($rootScope.currStateName == "index.user-requested-campaigns") {
      //   $scope.getUserCampaigns();
      // }

      $scope.cancelCampaign = function (activeUserCampaignID) {
        //console.log('activeUserCampaignID', activeUserCampaignID)
        // CampaignService.deleteRequestedCampaign(activeUserCampaignID).then(function (result) {
        //   if (result.status == 1) {
        //     getAllCampaigns();
        //     toastr.success(result.message);
        //   }
        //   else if (result.status == 0) {
        //     toastr.error(result.message);
        //   }
        // });
      };

      // $scope.deleteMetroCampaigns = function (campaignId) {
      //   if ($window.confirm("Are you really want to delete this camapaign?")) {
      //     CampaignService.deleteMetroCampaign(campaignId).then(function (result) {
      //       if (result.status == 1) {
      //         getMetroCampaigns();
      //         toastr.success(result.message);
      //       } else {
      //         toastr.error(result.message);
      //       }
      //     });
      //   } else {
      //     $scope.Message = "You clicked NO.";
      //   }
      // };

      // Select All
      $scope.selected = [];
      $scope.exist = function (item) {
        return $scope.selected.indexOf(item) > -1;
      };
      $scope.toggleSelection = function (item) {
        var idx = $scope.selected.indexOf(item);
        if (idx > -1) {
          $scope.selected.splice(idx, 1);
        } else {
          $scope.selected.push(item);
        }
      };
      $scope.campaignDetailscheckAll = function (selectAll) {
        if (selectAll) {
          angular.forEach($scope.campaignDetails.products, function (item) {
            idx = $scope.selected.indexOf(item);
            if (idx >= 0) {
              return true;
            } else {
              $scope.selected.push(item);
            }
          });
        } else {
          $scope.selected = [];
        }
      };

      $scope.campaignDetailsList = [];
      var deletedListArray = [];
      $scope.campaignListProductList = [];
      $scope.bulkDelete = function () {
        $scope.campaignDetailsList = $scope.selected;
        angular.forEach(
          $scope.campaignDetailsList,
          function (campaignproductIds) {
            deletedListArray.push(campaignproductIds.id);
          }
        );
        $scope.campaignListProductList = deletedListArray;
      };
      $scope.deleteCampaignList = function (ev) {
        console.log($scope.campaignDetails.id);
        var sendObj = {
          campaign_id: $scope.campaignDetails.id,
          product_booking_id: $scope.campaignListProductList,
          product_offer_price: [],
        };
        console.log(sendObj);
        MapService.deleteCampaignProduct(sendObj).then(function (response) {
          if (response.status == 1) {
            toastr.success(response.message);
            CampaignService.getCampaignWithProducts(
              $scope.campaignDetails.id
            ).then(function (result) {
              $scope.campaignDetails = result;
              if ($scope.campaignDetails.status == 100) {
                $scope.status = $scope.campaignDetails.status;
                $scope.campaignDetails.products.forEach((s) => {
                  if (s.deleteProductStatus && s.deleteProductStatus == 101) {
                    $scope.status = s.deleteProductStatus;
                  }
                });
              }
            });
          } else {
            toastr.error(result.message);
          }
          $mdDialog.show(
            $mdDialog
              .alert()
              .parent(angular.element(document.querySelector("body")))
              .clickOutsideToClose(true)
              .title("Delete Product")
              .textContent(response.message)
              .ariaLabel("delete-shortlisted")
              .ok("Confirmed!")
              .targetEvent(ev)
          );
          setTimeout(() => {
            $mdDialog.hide();
          }, 2000);
        });
      };

      $scope.payAndLaunch = function (campaignId) {
        CampaignService.requestCampaignBooking(campaignId).then(function (res) {
          if (res.status == 1) {
            toastr.success(res.message);
          } else if (res.status == 0) {
            toastr.error(res.message);
          }
        });
      };
      $scope.downloadUserQuote = function (campaignId) {
        CampaignService.downloadQuote(campaignId).then(function (result) {
          var campaignPdf = new Blob([result], {
            type: "application/pdf;charset=utf-8",
          });
          FileSaver.saveAs(campaignPdf, "campaigns.pdf");
          if (result.status) {
            toastr.error(result.meesage);
          }
        });
      };

      $scope.searchableBuyers = function (query) {
        if (query) {
          return CampaignService.searchBuyers(query.toLowerCase()).then(
            function (res) {
              return res;
            }
          );
        }
      };

      $scope.selectSearchedBuyers = function () {
        if ($scope.emailObj == null) {
          toastr.error("No Email Found");
        }
        $scope.email = $scope.emailObj;
      };

      $scope.sendTransferCampaign = function (campaignID) {
        $scope.campaignToTransfer = $scope.campaignDetails;
        var campaignOfferParams = {
          campaign_id: campaignID,
          transfer_email: $scope.emailObj.email,
          comments: $scope.comments,
          transfer_status: 1,
        };
        CampaignService.transferCampaignTo(campaignOfferParams).then(function (
          result
        ) {
          if (result.status == 1) {
            toastr.success(result.message);
            $window.location.href = "/user-saved-campaigns";
          } else {
            toastr.error(result.message);
          }
        });
      };

      $scope.acceptTransferCampaign = function (campaignID) {
        $scope.campaignToAccpeted = $scope.activeUserCampaign;
        var payload = {
          campaign_id: campaignID,
          comments: $scope.commentsRequested,
          transfer_status: 2,
        };
        CampaignService.transferCampaignTo(payload).then(function (result) {
          if (result.status == 1) {
            toastr.success(result.message);
            $window.location.href = "/user-saved-campaigns";
          } else {
            toastr.error(result.message);
          }
        });
      };

			$scope.rejectTransferCampaign = function (campaignID) {
				var payload = {
					campaign_id: campaignID,
					comments: $scope.commentsRequested,
					transfer_status: 3
				};
				CampaignService.transferCampaignTo(payload).then
					(function (result) {
						if (result.status == 1) {
							toastr.success(result.message);
							$window.location.href = "/user-saved-campaigns";
						} else {
							toastr.error(result.message);
						}
					});
			};

			//Refresh Prodcuts
			
			$scope.refreshProductsRFP = function(campaignId) {
				CampaignService.getAssignedRfpProducts(campaignId)
				  .then(function(result) {
					if (result.status == 0) {
					  toastr.error(result.message);
					} else if (result.status == 1) {
					  $scope.getCampaignDetails(campaignId);
					  toastr.success(result.message);
					}
				  })
				  .catch(function(error) {
					toastr.error(result.message);
				  });
			  };

      //code for the request product campaign for with rfp  and zero product campaign
      var user = localStorage.getItem("loggedInUser");
      var parsedData = JSON.parse(user);
      var user_id = parsedData.user_id;
      var user_email = parsedData.user_email;
      $scope.user = {
        email: user_email,
      };
      $scope.queryTxt = "";
      $scope.budget_Rfp = "";
      $scope.findForMe = function (queryTxt, budget_Rfp, campaignID) {
        CampaignService.findForMe({
          user_query: queryTxt,
          budget_rfp: budget_Rfp,
          campaign_id: campaignID,
          loggedinUser: parsedData,
        }).then((result) => {
          if (result && result.status) {
            toastr.success(result.message);
            console.log("durga", result.message);
            $timeout(function () {
              // $window.location.href = "/user-saved-campaigns";
              $scope.queryTxt = "";
              $scope.budget_Rfp = "";
              $scope.loginForm.$setPristine();
              $scope.loginForm.$setUntouched();
            }, 3000);
          } else {
            toastr.error(result.message);
          }
        });
      };
      $scope.lat = "";
      $scope.lng = "";
      // Generate the map image URL
      function generateMapImageUrl(lat, lng) {
        console.log("dileep_durga", $scope);
        var apiKey = "AIzaSyAl05ze0VsbHB2lnp2VRXbNQHNyRzVWUQQ"; // Replace with your Google Maps API key
        var zoomLevel = 12;
        var imageSize = "380x280";
        var color = "color:red%7Clabel:C%7C" + lat + "," + lng;

        var mapImageUrl = "https://maps.googleapis.com/maps/api/staticmap?";
        mapImageUrl += "center=" + lat + "," + lng;
        mapImageUrl += "&markers=" + color;
        mapImageUrl += "&zoom=" + zoomLevel;
        mapImageUrl += "&size=" + imageSize;
        mapImageUrl += "&key=" + apiKey;
        return mapImageUrl;
      }

      $scope.userProfileApi = function () {
        CampaignService.getProfile().then(function (result) {
          $scope.userProfile = result;
          $scope.loading = false;
        });
      };

      $scope.cdate = new Date();
      /*=============================
  | Page based initial loads end
  =============================*/
      //loadMetroCampaigns();
      //$scope.Getcomment($stateParams.campaignId);
      //getMetroCampaignDetails();
    }
  );
