angular.module("bbManager").controller('MetroCtrl',
  ['$scope', '$mdSidenav', '$mdDialog', '$rootScope', '$stateParams', '$timeout', '$window', 'CampaignService', 'MetroService', 'ProductService', 'FileSaver', 'Blob', 'config', 'toastr', '$state',
    function ($scope, $mdSidenav, $mdDialog, $rootScope, $stateParams, $timeout, $window, CampaignService, MetroService, ProductService, FileSaver, Blob, config, toastr, $state) {
      $scope.metroCampaign = {};
      $scope.newDate = new Date();
      var today = new Date();
      var dd = today.getDate();
      var mm = today.getMonth() + 1; //January is 0!
      var yyyy = today.getFullYear();
      if (dd < 10) {
        dd = '0' + dd
      }
      if (mm < 10) {
        mm = '0' + mm
      }
      today.setDate(today.getDate() + 2);
      $scope.today1 = today;
      // $scope.today1 = yyyy+'-'+mm+'-'+dd;
      $scope.colors = ['#FE0000', '#0D7ECA'];
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
        } else {
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
      $scope.toggleAddMetroProductSidenav = function () {
        $mdSidenav('add-metro-product-sidenav').toggle();
      }
      $scope.toggleShareMetroCampaignSidenav = function () {
        $mdSidenav('shareMetroCampaignSidenav').toggle();
      };
      /*================================
      | Popup and Sidenav controls end
      ================================*/
      // $scope.uncheck = function (checked) {
      //   if (!checked) {
      //     $scope.onchecked = false;
      //     $scope.GST = "0";
      //     $scope.TOTAL = $scope.metroCampDetails.act_budget + parseInt($scope.GST);
      //   } else {
      //     $scope.onchecked = false;
      //     $scope.GST = ($scope.metroCampDetails.act_budget / 100) * 18;
      //     $scope.TOTAL = $scope.metroCampDetails.act_budget + $scope.GST;
      //   }
      // };
      /*===============================
      | Campaign Management
      ===============================*/
      var getFormatList = function (obj) {
        ProductService.getFormatList(obj).then(function (result) {
          $scope.formatList = result;
        });
      }

      function getMetroCorridors() {
        MetroService.getMetroCorridors().then(function (result) {
          $scope.metroCorridors = result;
          $scope.selectedCorridor = $scope.metroCorridors[0];
          $scope.getMetroPackages($scope.selectedCorridor.id);
          if ($state.current.url == 'metro') {
            //$scope.showPackagePopup();
          }

        });
      }
      $scope.monthoptions = [{
        value: '.5',
        label: '15 Days'
      }, {
        value: '1',
        label: '1 Month'
      }, {
        value: '2',
        label: '2 Months'
      }, {
        value: '3',
        label: '3 Months'
      }, {
        value: '4',
        label: '4 Months'
      }, {
        value: '5',
        label: '5 Months'
      }, {
        value: '6',
        label: '6 Months'
      }, {
        value: '7',
        label: '7 Months'
      }, {
        value: '8',
        label: '8 Months'
      }, {
        value: '9',
        label: '9 Months'
      }, {
        value: '10',
        label: '10 Months'
      }, {
        value: '11',
        label: '11 Months'
      }, {
        value: '12',
        label: '12 Months'
      }];
      //$scope.myvar = $scope.myoptions[1]; 
      $scope.getMetroPackages = function (corridorId) {
        MetroService.getMetroPackages(corridorId).then(function (result) {
          _.map(result, (res) => {
            res.selected_trains = 1;
            res.months = $scope.monthoptions[0];
            // res.selected_slots = 1;
            return res;
          });
          $scope.metroPackages = result;
          $scope.selectedPackage = $scope.metroPackages[0];
          //$scope.selectedPackage.months = 1;
        });
      }

      function loadShortlistedPackages() {
        MetroService.getShortlistPackages().then((result) => {
          $scope.shortlistedPackages = result;
        });
      }
      loadShortlistedPackages();
      $scope.select_package_var = [];

      $scope.select_package = function (pkg) {
        var alreadySelected = _.filter($scope.shortlistedPackages, function (package) {
          return package.id == pkg.id;
        });
        if (alreadySelected.constructor === Array && alreadySelected.length > 0) {
          toastr.error("This package is already added.");
        } else {
          if (typeof pkg.start_date === 'undefined') {
            toastr.error("Start date for the package is required.");
          } else {
            MetroService.shortlistPackage(pkg).then((result) => {
              if (result.status == 1) {
                loadShortlistedPackages();
                toastr.success(result.message);
              } else {
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
        } else {
          if (typeof pkg.start_date === 'undefined') {
            toastr.error("Start date for the package is required.");
          } else if (!pkg.months) {
            toastr.error('Please select days.');
          } else if (!pkg.selected_trains) {
            toastr.error('Please select trains.');
          } else {
            MetroService.shortlistPackage(pkg).then((result) => {
              if (result.status == 1) {
                loadShortlistedPackages();
                toastr.success(result.message);
              } else {
                toastr.error(result.message);
              }
            });
          }
        }
      }
      $scope.getEstBudgetForSelectedPackages = function () {
        var estBudget = {};
        estBudget.price = 0;
        //	estBudget.selected_slots = 0;
        estBudget.selected_trains = 0;
        estBudget.months = 0;
        _.each($scope.shortlistedPackages, (package) => {
          estBudget.price += package.price;
          //estBudget.selected_slots += package.selected_slots;
          estBudget.selected_trains += package.selected_trains;
          estBudget.months += package.months;
        });
        return estBudget;
      }
      $scope.isAlreadySelected = function (pkgId) {
        var pkg = _.find($scope.shortlistedPackages, (slPkg) => {
          return slPkg.package_id == pkgId;
        });
        return pkg !== undefined;
      }
      $scope.deleteShortlistedMetroPackage = function (pkgId) {
        MetroService.deleteShortlistedMetroPackage(pkgId).then((result) => {
          if (result.status == 1) {
            loadShortlistedPackages();
            toastr.success(result.message);
          } else {
            toastr.error(result.message);
          }
        });
      }
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
            sharemetroCampaign();
          } else {
            toastr.error(result.message);
          }
          $state.reload();
        });
      }

      function sharemetroCampaign() {
        document.getElementById("sharemetroDrop").classList.toggle("show");
        //      document.getElementById("sharecampmetroDrop").classList.toggle("show");

      }
      $scope.addPackageInMetroCampaign = function () {
        $scope.selectedPackage.package_id = $scope.selectedPackage.id;
        $scope.selectedPackage.campaign_id = $stateParams.metroCampaignId;
        $scope.selectedPackage.months = $scope.selectedPackage.months.value;
        //$scope.selectedPackage.total_price = $scope.selectedPackage.price * ($scope.selectedPackage.selected_trains + $scope.selectedPackage.selected_slots - 1);
        MetroService.addPackageInMetroCampaign($scope.selectedPackage).then((result) => {
          if (result.status == 1) {
            $scope.selectedPackage = {};
            getMetroCampDetails($stateParams.metroCampaignId);
            toastr.success(result.message);
            $scope.toggleAddMetroProductSidenav();
          } else {
            toastr.error(result.message);
          }
        });
      }
      $scope.saveMetroCampaign = function (campaign) {
        if ($scope.shortlistedPackages.length > 0) {
          campaign.packages = [];
          _.each($scope.shortlistedPackages, function (v, i) {
            campaign.packages.push(v.package_id);
          });
          MetroService.saveMetroCampaign(campaign).then(function (response) {
            if (response.status == 1) {
              $timeout(function () {
                $scope.showSaveCampaignPopup = false;
                $scope.campaign = {};
                campaign = {};
                $scope.metroCampaignForm.$setPristine();
                $scope.metroCampaignForm.$setUntouched();
                $scope.campaignSavedSuccessfully = false;

              }, 1000);
              getMetroCampaigns();
              loadShortlistedPackages();
              toastr.success(response.message);
              if(response.user_type=='owner'){
              $window.location.href = '/owner/{{clientSlug}}/metro-campaign-details/' + response.metro_camp_id;
          }
          else if(response.user_type=='basic'){
              $window.location.href = '/metro-campaign/' + response.metro_camp_id;
          }
            } else {
              $scope.saveUserCampaignErrors = response.message;
            }
          });
        } else {
          toastr.error("Please shortlist some packages first.");
        }
      }

      function getMetroCampaigns() {
        MetroService.getMetroCampaigns().then((result) => {
          $scope.metroCampaigns = result;
        });
      }
      $scope.checkoutMetroCampaign = function (ev, metroCampaignId) {
        if ($scope.onchecked === true) {
          $scope.flag = 1;
          $scope.GST = ($scope.metroCampDetails.act_budget / 100) * 18;
        } else if ($scope.onchecked === false) {
          $scope.flag = 0;
          $scope.GST = "0";
        } else {
          $scope.flag = 1;
        }
        MetroService.checkoutMetroCampaign(metroCampaignId, $scope.flag, $scope.GST).then((result) => {
          if (result.status == 1) {
            getMetroCampDetails(metroCampaignId);
            getMetroCampaigns();
            $mdDialog.show(
              $mdDialog.alert()
              .parent(angular.element(document.querySelector('body')))
              .clickOutsideToClose(true)
              .title(result.message)
              //.textContent('You can specify some description text in here.')
              .ariaLabel('Alert Dialog Demo')
              .ok('Confirmed!')
              .targetEvent(ev)
            );
          } else {
            toastr.error(result.message);
          }
        });
      }
      $scope.deleteProductFromCampaign = function (metroCampaignId,package) {

        if ($window.confirm("Are you sure you want to delete this package?")) {
          MetroService.deleteMetroPackageFromCampaign(metroCampaignId,package).then(function (result) {
            if (result.status == 1) {
              getMetroCampDetails($stateParams.metroCampaignId);
              getMetroCampaigns();
              toastr.success(result.message);
            } else {
              toastr.error(result.message);
            }
          });
        } else {
          $scope.Message = "You clicked NO.";
        }

      }

      function getMetroCampDetails(mCampId) {
        MetroService.getMetroCampDetails(mCampId).then((result) => {
          $scope.metroCampDetails = result;
          if ($scope.metroCampDetails.gst_price != "0") {
            $scope.GST = ($scope.metroCampDetails.act_budget / 100) * 18;
            $scope.TOTAL = $scope.metroCampDetails.act_budget + $scope.GST;
          } else {
            $scope.GST = "0";
            $scope.TOTAL = $scope.metroCampDetails.act_budget + parseInt($scope.GST);
          }
        });
      }
      /*===============================
      | Campaign Management ends
      ===============================*/
      $scope.downloadOwnerQuote = function (campaignId) {
        MetroService.downloadQuote(campaignId).then(function (result) {
          var campaignPdf = new Blob([result], {
            type: 'application/pdf;charset=utf-8'
          });
          FileSaver.saveAs(campaignPdf, 'campaigns.pdf');
          if (result.status) {
            toastr.error(result.meesage);
          }
        });
      };
      /*================================
      | Page based initial loads
      ================================*/
      if ($rootScope.currStateName == 'index.metro' || 'owner.metro-campaign-details') {
        getMetroCorridors();
        getMetroCampaigns();
      }
      if ($rootScope.currStateName == 'index.metro-campaign' || 'owner.metro-campaign-details') {
        if (typeof $stateParams.metroCampaignId !== undefined) {
          getMetroCampDetails($stateParams.metroCampaignId);
        }
        getMetroCorridors();
        getFormatList({
          type: "metro"
        });
      }
      /*================================
      | Page based initial loads end
      ================================*/

      $scope.showSaveCampaignPopup = false;
      $scope.toggleSaveCampaignPopup = function () {
        $scope.showSaveCampaignPopup = !$scope.showSaveCampaignPopup;
      }
    }
  ]
);