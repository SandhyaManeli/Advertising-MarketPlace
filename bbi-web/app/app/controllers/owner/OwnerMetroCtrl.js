angular.module("bbManager").controller('OwnerMetroCtrl',
  ['$scope', '$mdSidenav', '$mdDialog', '$rootScope', '$stateParams', '$timeout', '$window', 'CampaignService', 'ProductService', 'FileSaver', 'Blob', 'config', 'toastr',
    function ($scope, $mdSidenav, $mdDialog, $rootScope, $stateParams, $timeout, $window, CampaignService, ProductService, FileSaver, Blob, config, toastr) {
      // 'MetroService',MetroService,
      $scope.metroCampaign = {};
      $scope.newDate = new Date();
      /*==============================
      | Popup and Sidenav controls
      ==============================*/
      $scope.showPackagePopup = function (ev) {
        if (typeof $scope.selectedCorridor != "undefined") {
          $mdDialog.show({
            templateUrl: 'views/metro-packages-popup.html',
            fullscreen: $scope.customFullscreen,
            clickOutsideToClose: true,
            preserveScope: true,
            scope: $scope
          });
        }
        else {
          alert("Please select a corridor first.");
        }
      }
      $scope.closePackagePopup = function () {
        $mdDialog.hide();
      }
      $scope.closeCongSaveCampaignSidenav = function () {
        $mdSidenav('saveCampaignSidenavCongo').close();
      }
      $scope.closeSaveCampaignSidenav = function () {
        $mdSidenav('saveCampaignSidenavForm').close();
      }
      $scope.toggleAddMetroProductSidenav = function(){
        $mdSidenav('add-metro-product-sidenav').toggle();
      }
      $scope.toggleShareMetroCampaignSidenav = function () {
        $mdSidenav('shareMetroCampaignSidenav').toggle();
      };
      /*================================
      | Popup and Sidenav controls end
      ================================*/

      /*===============================
      | Campaign Management
      ===============================*/
      var getFormatList = function (obj) {
        ProductService.getFormatList(obj).then(function (result) {
          $scope.formatList = result;
        });
      }
      // function getMetroCorridors() {
      //   MetroService.getMetroCorridors().then(function (result) {
      //     $scope.metroCorridors = result;
      //     $scope.selectedCorridor = $scope.metroCorridors[0];
      //     $scope.getMetroPackages($scope.selectedCorridor.id);
      //   });
      // }
      // $scope.getMetroPackages = function (corridorId) {
      //   MetroService.getMetroPackages(corridorId).then(function (result) {
      //     _.map(result, (res) => {
      //       res.selected_trains = 1;
      //       res.selected_slots = 1;
      //       return res;
      //     });
      //     $scope.metroPackages = result;
      //   });
      // }
      // function loadShortlistedPackages() {
      //   MetroService.getShortlistPackages().then((result) => {
      //     $scope.shortlistedPackages = result;
      //   });
      // }
      loadShortlistedPackages();
      $scope.select_package_var = [];
      $scope.select_package = function (pkg) {
        var alreadySelected = _.filter($scope.shortlistedPackages, function (package) {
          return package.id == pkg.id;
        });
        if (alreadySelected.constructor === Array && alreadySelected.length > 0) {
          toastr.error("This package is already added.");
        }
        else {
          if (typeof pkg.start_date === 'undefined') {
            toastr.error("Start date for the package is required.");
          }
          else {
            MetroService.shortlistPackage(pkg).then((result) => {
              if (result.status == 1) {
                loadShortlistedPackages();
                toastr.success(result.message);
              }
              else {
                toastr.error(result.message);
              }
            });
          }
        }
      }
      $scope.shortlistMetroPackage = function (pkg) {
        var alreadySelected = _.filter($scope.shortlistedPackages, function (package) {
          return package.id == pkg.id;
        });
        if (alreadySelected.constructor === Array && alreadySelected.length > 0) {
          toastr.error("This package is already added.");
        }
        else {
          if (typeof pkg.start_date === 'undefined') {
            toastr.error("Start date for the package is required.");
          }
          else if (typeof pkg.selected_trains === 'undefined') {
            toastr.error('Please select at least 1 train.');
          }
          else {
            MetroService.shortlistPackage(pkg).then((result) => {
              if (result.status == 1) {
                loadShortlistedPackages();
                toastr.success(result.message);
              }
              else {
                toastr.error(result.message);
              }
            });
          }
        }
      }
      $scope.getEstBudgetForSelectedPackages = function () {
        var estBudget = 0;
        _.each($scope.shortlistedPackages, (package) => {
          estBudget += package.price * (package.selected_trains + package.selected_slots - 1);
        });
        return estBudget;
      }
      $scope.isAlreadySelected = function (pkgId) {
        var pkg = _.find($scope.shortlistedPackages, (slPkg) => {
          return slPkg.package_id == pkgId;
        });
        return pkg !== undefined;
      }
      // $scope.deleteShortlistedMetroPackage = function (pkgId) {
      //   MetroService.deleteShortlistedMetroPackage(pkgId).then((result) => {
      //     if (result.status == 1) {
      //       loadShortlistedPackages();
      //       toastr.success(result.message);
      //     }
      //     else {
      //       toastr.error(result.message);
      //     }
      //   });
      // }
      $scope.toggleSaveCampaignSidenavMetro = function () {
        $mdSidenav('saveCampaignSidenavCongo').close();
        $mdSidenav('saveCampaignSidenavForm').toggle();
      }
      $scope.shareMetroCampaignToEmail = function (ev, shareCampaign) {
        $scope.campaignToShare = $scope.metroCampDetails;
        var campaignToEmail = {
          campaign_id: $scope.campaignToShare.id,
          email: shareCampaign.email,
          receiver_name: shareCampaign.receiver_name,
          campaign_type: $scope.campaignToShare.type
        };
        CampaignService.shareMetroCampaignToEmail(campaignToEmail).then(function (result) {
          if (result.status == 1) {
            $mdSidenav('shareCampaignSidenav').close();
            $mdDialog.show(
              $mdDialog.alert()
                .parent(angular.element(document.querySelector('body')))
                .clickOutsideToClose(true)
                .title(result.message)
                // .textContent('You can specify some description text in here.')
                .ariaLabel('Alert Dialog Demo')
                .ok('Confirmed!')
                .targetEvent(ev)
            );
          }
          else {
            toastr.error(result.message);
          }
        });
      }
      $scope.addPackageInMetroCampaign = function () {
        $scope.selectedPackage.package_id = $scope.selectedPackage.id;
        $scope.selectedPackage.campaign_id = $stateParams.metroCampaignId;
        $scope.selectedPackage.total_price = $scope.selectedPackage.price * ($scope.selectedPackage.selected_trains + $scope.selectedPackage.selected_slots - 1);
        MetroService.addPackageInMetroCampaign($scope.selectedPackage).then((result) => {
          if (result.status == 1) {
            $scope.selectedPackage = {};
            getMetroCampDetails($stateParams.metroCampaignId);
            toastr.success(result.message);
          }
          else {
            toastr.error(result.message);
          }
        });
      }
      // $scope.saveMetroCampaign = function (campaign) {
      //   if ($scope.shortlistedPackages.length > 0) {
      //     campaign.packages = [];
      //     _.each($scope.shortlistedPackages, function (v, i) {
      //       campaign.packages.push(v.package_id);
      //     });
      //     MetroService.saveMetroCampaign(campaign).then(function (response) {
      //       if (response.status == 1) {
      //         $mdSidenav('saveCampaignSidenavForm').close();
      //         $mdSidenav('saveCampaignSidenavCongo').open();
      //         $timeout(function () {
      //           $mdSidenav('saveCampaignSidenavCongo').close();
      //           campaign = {};
      //           $scope.forms.viewAndSaveCampaignForm.$setPristine();
      //           $scope.forms.viewAndSaveCampaignForm.$setUntouched();
      //           $scope.campaignSavedSuccessfully = false;
      //         }, 3000);
      //         getMetroCampaigns();
      //         loadShortlistedPackages();
      //       }
      //       else {
      //         $scope.saveUserCampaignErrors = response.message;
      //       }
      //     });
      //   }
      //   else {
      //     toastr.error("Please shortlist some packages first.");
      //   }
      // }
      // function getMetroCampaigns() {
      //   MetroService.getMetroCampaigns().then((result) => {
      //     $scope.metroCampaigns = result;
      //   });
      // }
      // $scope.checkoutMetroCampaign = function (ev, metroCampaignId) {
      //   MetroService.checkoutMetroCampaign(metroCampaignId).then((result) => {
      //     if (result.status == 1) {
      //       getMetroCampDetails(metroCampaignId);
      //       getMetroCampaigns();
      //       $mdDialog.show(
      //         $mdDialog.alert()
      //           .parent(angular.element(document.querySelector('body')))
      //           .clickOutsideToClose(true)
      //           .title(result.message)
      //           // .textContent('You can specify some description text in here.')
      //           .ariaLabel('Alert Dialog Demo')
      //           .ok('Got it!')
      //           .targetEvent(ev)
      //       );
      //     }
      //     else {
      //       toastr.error(result.message);
      //     }
      //   });
      // }
      // $scope.deleteProductFromCampaign = function (campaignId, productId) {
      //   if ($window.confirm("Are you sure you want to delete this package?")) {
      //     MetroService.deleteMetroPackageFromCampaign(campaignId, productId).then(function (result) {
      //       if (result.status == 1) {
      //         getMetroCampDetails($stateParams.metroCampaignId);
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
      // function getMetroCampDetails(mCampId) {
      //   MetroService.getMetroCampDetails(mCampId).then((result) => {
      //     $scope.metroCampDetails = result;
      //   });
      // }
      /*===============================
      | Campaign Management ends
      ===============================*/

      /*================================
      | Page based initial loads
      ================================*/
      if ($rootScope.currStateName == 'index.metro') {
        getMetroCorridors();
        getMetroCampaigns();
      }
      if ($rootScope.currStateName == 'index.metro-campaign') {
        if (typeof $stateParams.metroCampaignId !== undefined) {
          getMetroCampDetails($stateParams.metroCampaignId);
        }
        getMetroCorridors();
        getFormatList({ type: "metro" });
      }
      /*================================
      | Page based initial loads end
      ================================*/
    }
  ]
);