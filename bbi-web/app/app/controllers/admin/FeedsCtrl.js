angular.module("bbManager").controller(
  "AdminFeedsCtrl",
  function (
    $scope,
    $mdDialog,
    $mdSidenav,
    $location,
    $rootScope,
    $stateParams,
    AdminCampaignService,
    HelperService
  ) {
    /*
  ======== Campaign requests =======
  */
    $scope.requestList = {};
    function getAllFeeds() {
      return new Promise((resolve, reject) => {
        AdminCampaignService.getAllCampaignRequests().then(function (result) {
          $scope.requestList.metroCampaignFeeds = result.metro_campaign_feeds;
          $scope.requestList.campaignSuggestionRequests =
            result.requested_campaign_suggestions;
          $scope.requestList.otherCampaignFeeds = result.other_campaign_feeds;

          if (
            $scope.requestList &&
            $scope.requestList.otherCampaignFeeds &&
            $scope.requestList.otherCampaignFeeds.length
          ) {
            $scope.requestList.otherCampaignFeeds.forEach((item) => {
              if (
                item.colorcode != "red" &&
                HelperService.Campaign.isWithinStatingWeek(item.start_date)
              ) {
                item.colorcode = "red";
              }
            });
            $scope.requestList.otherCampaignFeedsRed = [];
    $scope.requestList.otherCampaignFeedsblack = [];
            $scope.requestList.otherCampaignFeedsRed = $scope.requestList.otherCampaignFeeds.filter(function(item) {
                return item.colorcode == "red";
               });
               $scope.requestList.otherCampaignFeedsblack = $scope.requestList.otherCampaignFeeds.filter(function(item) {
                return item.colorcode != "red";
               });
            // HelperService.Campaign.sortDescendingColor(
            //   $scope.requestList.otherCampaignFeeds
            // );
          }
          resolve(result.requested_campaign_suggestions);
        });
      });
    }
    /*
  ======== Campaign requests ends =======
  */

    /*==============================
  | Feeds related methods
  ==============================*/

    function convertDateToMMDDYYYY (campaignData,areaTimeZoneType) {
      const startDate = campaignData.start_date;
      const endDate = campaignData.end_date;
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
    }

    $scope.showCampaignDetailsPopup = function (ev, campaignData) {
      $scope.selectedRequestDetails = campaignData;
      if ($scope.selectedRequestDetails.area_time_zone_type) {
        const {startDate,endDate} = convertDateToMMDDYYYY($scope.selectedRequestDetails,$scope.selectedRequestDetails.area_time_zone_type);
        $scope.selectedRequestDetails["startDate"] = startDate;
        $scope.selectedRequestDetails["endDate"] = endDate;
      }
      $mdDialog.show({
        templateUrl: "views/admin/campaign-details-popup.html",
        fullscreen: $scope.customFullscreen,
        clickOutsideToClose: true,
        preserveScope: true,
        scope: $scope,
      });
    };

    $scope.showCampaignSuggestionRequestPopup = function (ev, campaignData) {
      AdminCampaignService.getSuggestionRequestDetails(
        campaignData.campaign_id
      ).then(function (result) {
        $scope.selectedRequestDetails = result;
        $mdDialog.show({
          templateUrl: "views/admin/campaign-suggestion-request-popup.html",
          fullscreen: $scope.customFullscreen,
          clickOutsideToClose: true,
          preserveScope: true,
          scope: $scope,
        });
      });
    };

    /*
  ======== Campaign Suggestions(planned) ========
  */
    $scope.createCampaignToSuggest = function (emptyCampaign) {
      $mdDialog.show({
        locals: {
          emptyCampaign: emptyCampaign,
          campaignPartial: $scope.selectedRequestDetails,
        },
        templateUrl: "views/admin/add-campaign.html",
        clickOutsideToClose: true,
        fullscreen: $scope.customFullscreen,
        controller: function (
          $scope,
          $mdDialog,
          AdminCampaignService,
          emptyCampaign,
          campaignPartial,
          toastr
        ) {
          $scope.campaignFromSuggestionRequest = {};
          emptyCampaign = _.extend(emptyCampaign, campaignPartial);
          $scope.campaignFromSuggestionRequest = emptyCampaign;
          $scope.campaignFromSuggestionRequest.id = emptyCampaign.campaign_id;
          $scope.campaignFromSuggestionRequest.start_date = moment()
            .add(5, "days")
            .toDate();
          $scope.campaignFromSuggestionRequest.end_date = moment(
            $scope.campaignFromSuggestionRequest.start_date
          )
            .add(1, "days")
            .toDate();
          $scope.dateLimits = {
            minStartDate: moment().add(5, "days").toDate(),
            minEndDate: moment($scope.campaignFromSuggestionRequest.start_date)
              .add(1, "days")
              .toDate(),
          };
          $scope.updateEndDateValidations = function () {
            $scope.dateLimits.minEndDate = moment(
              $scope.campaignFromSuggestionRequest.start_date
            )
              .add(1, "days")
              .toDate();
            if (
              $scope.campaignFromSuggestionRequest.end_date <=
              $scope.campaignFromSuggestionRequest.start_date
            ) {
              $scope.campaignFromSuggestionRequest.end_date =
                $scope.dateLimits.minEndDate;
            }
          };
          $scope.saveCampaign = function () {
            AdminCampaignService.saveUserCampaign(
              $scope.campaignFromSuggestionRequest
            ).then(function (result) {
              if (result.status == 1) {
                getAllFeeds();
                toastr.success(result.message);
                $mdDialog.hide();
              } else {
                toastr.error(result.message);
              }
            });
          };
          $scope.close = function () {
            $mdDialog.hide();
          };
        },
      });
    };
    /*
  ======== Campaign Suggestions(planned) ends ========
  */

    /*
  ======= Campaign Proposals =======
  */
    $scope.viewAndLaunchCampaign = function (campaignId) {
      localStorage.campaignForSuggestion = JSON.stringify(
        $scope.selectedRequestDetails
      );
      $location.path("/admin/campaign-proposal-summary/" + campaignId);
    };
    /*
  ======= Campaign Proposals Ends =======
  */

    /*
  ======= View and Launch Campaign =======
  */
    $scope.prepareQuoteForCampaign = function (campaignId) {
      AdminCampaignService.updateTodoStatus(campaignId).then(function (result) {
        if (result.status == '1') {
          localStorage.campaignForSuggestion = JSON.stringify(
            $scope.selectedRequestDetails
          );
          $location.path("/admin/campaign-proposal-summary/" + campaignId + "/20");
        } else {
          toastr.error(result.message);
        }
      });
    };
    /*
  ======= View and Launch Campaign ands =======
  */

    /*==============================
  | Feeds related methods ends
  ==============================*/

    $scope.loadMore = function () {
      $scope.limit = $scope.items.length;
    };
    /*//// popup ////////*/
    $scope.closeInputPanel = function (ev) {
      $mdSidenav("ClientRequest").toggle();
    };

    /* close modal */
    $scope.close = function () {
      $mdDialog.hide();
    };

    //SORTING FOR INVENTORY LIST
    $scope.sortAsc = function (headingName, type) {
      $scope.upArrowColour = headingName;
      $scope.sortType = "Asc";
      if (type == "string") {
        $scope.newOfferData = $scope.requestList.otherCampaignFeedsblack.map((e) => {
          return {
            ...e,
            name: e.name,
            user_email: e.user_email,
          };
        });
        $scope.requestList.otherCampaignFeedsblack = [];
        $scope.requestList.otherCampaignFeedsblack = $scope.newOfferData.sort(
          (a, b) => {
            console.log(a[headingName]);
            return a[headingName].localeCompare(b[headingName], undefined, {
              numeric: true,
              sensitivity: "base",
            });
          }
        );
//sort red data
$scope.newOfferDatared = $scope.requestList.otherCampaignFeedsRed.map((e) => {
  return {
    ...e,
    name: e.name,
    user_email: e.user_email,
  };
});
$scope.requestList.otherCampaignFeedsRed = [];
$scope.requestList.otherCampaignFeedsRed = $scope.newOfferDatared.sort(
  (a, b) => {
    console.log(a[headingName]);
    return a[headingName].localeCompare(b[headingName], undefined, {
      numeric: true,
      sensitivity: "base",
    });
  }
);
        // $scope.productList = $scope.newOfferData;
      }
      $scope.requestList.otherCampaignFeedsblack =
        $scope.requestList.otherCampaignFeedsblack.sort((a, b) => {
          if (type == "boolean") {
            return a[headingName] ? 1 : -1;
          } else {
            return a[headingName] - b[headingName];
          }
        });
        $scope.requestList.otherCampaignFeedsRed =
        $scope.requestList.otherCampaignFeedsRed.sort((a, b) => {
          if (type == "boolean") {
            return a[headingName] ? 1 : -1;
          } else {
            return a[headingName] - b[headingName];
          }
        });
      console.log($scope.requestList.otherCampaignFeedsblack);
    };
    $scope.sortDsc = function (headingName, type) {
      $scope.downArrowColour = headingName;
      $scope.sortType = "Dsc";
      if (type == "string") {
        $scope.newOfferData = $scope.requestList.otherCampaignFeedsblack.map((e) => {
          return {
            ...e,
            name: e.name,
            user_email: e.user_email,
          };
        });
        $scope.newOfferDatared = $scope.requestList.otherCampaignFeedsRed.map((e) => {
          return {
            ...e,
            name: e.name,
            user_email: e.user_email,
          };
        });
        $scope.requestList.otherCampaignFeedsblack = [];
        $scope.requestList.otherCampaignFeedsblack = $scope.newOfferData.sort(
          (a, b) => {
            console.log(a[headingName]);
            return b[headingName].localeCompare(a[headingName], undefined, {
              numeric: true,
              sensitivity: "base",
            });
          }
        );
        $scope.requestList.otherCampaignFeedsRed = [];
        $scope.requestList.otherCampaignFeedsRed = $scope.newOfferDatared.sort(
          (a, b) => {
            console.log(a[headingName]);
            return b[headingName].localeCompare(a[headingName], undefined, {
              numeric: true,
              sensitivity: "base",
            });
          }
        );
        // $scope.RfpData = $scope.newOfferData;
      }
      $scope.requestList.otherCampaignFeedsblack =
        $scope.requestList.otherCampaignFeedsblack.sort((a, b) => {
          if (type == "boolean") {
            return a[headingName] ? -1 : 1;
          } else {
            return b[headingName] - a[headingName];
          }
        });
        $scope.requestList.otherCampaignFeedsRed =
        $scope.requestList.otherCampaignFeedsRed.sort((a, b) => {
          if (type == "boolean") {
            return a[headingName] ? -1 : 1;
          } else {
            return b[headingName] - a[headingName];
          }
        });
      console.log($scope.requestList.otherCampaignFeedsblack);
    };
   
    //SORTING FOR INVENTORY LIST ENDS
    if ($rootScope.currStateName == "admin.home") {
      if ($stateParams.campSuggReqId) {
        getAllFeeds().then((suggRequests) => {
          var suggReq = _.filter(suggRequests, function (sr) {
            return sr.id == $stateParams.campSuggReqId;
          });
          typeof suggReq != "undefined" &&
            $scope.showCampaignSuggestionRequestPopup(null, suggReq[0]);
        });
      } else {
        getAllFeeds();
      }
    }

    $scope.getStatus = function (statusCode) {
      return AdminCampaignService.campaignStatus[statusCode];
    };
  }
);
