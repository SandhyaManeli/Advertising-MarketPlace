angular.module("bbManager").controller("buyerNewDashReportCtrl", [
  "$scope",
  "$location",
  "$auth",
  "NgMap",
  "$mdSidenav",
  "$mdDialog",
  "$timeout",
  "$window",
  "$rootScope",
  "MapService",
  "LocationService",
  "ProductService",
  "CampaignService",
  "FileSaver",
  "Blob",
  "config",
  "$http",
  "toastr",
  "$state",
  "ManagementReportService",
  function (
    $scope,
    $location,
    $auth,
    NgMap,
    $mdSidenav,
    $mdDialog,
    $timeout,
    $window,
    $rootScope,
    MapService,
    LocationService,
    ProductService,
    CampaignService,
    FileSaver,
    Blob,
    config,
    $http,
    toastr,
    $state,
    ManagementReportService
  ) {
    $scope.showFiltersDialog = function (ev) {
      $mdDialog.show({
        templateUrl: "views/filtersDialogModal.html",
        fullscreen: $scope.customFullscreen,
        clickOutsideToClose: true,
        preserveScope: true,
        scope: $scope,
        controller: "buyerNewDashReportCtrl",
        resolve: {},
      });
    };

    // $scope.selectedAdvertisingType = null;
    // $scope.isShownProductGetApi = false; // Set it initially to false
    // $scope.selectAdvertisingType = function (formatId) {
    //   console.log('Selected Advertising Type:', formatId);
    //   // Update the selected advertising type
    //   $scope.selectedAdvertisingType = formatId;
    // };

    $scope.selectedAdvertisingTypes = [];
    $scope.isShownProductGetApi = false; // Set it initially to false

    $scope.toggleAdvertisingType = function (formatId) {
      // Convert formatId to string (or adjust based on the type in your case)
      var formattedId = String(formatId);

      // Ensure that selectedAdvertisingTypes is an array
      if (!Array.isArray($scope.selectedAdvertisingTypes)) {
        $scope.selectedAdvertisingTypes = [];
      }

      var index = $scope.selectedAdvertisingTypes.indexOf(formattedId);
      if (index === -1) {
        $scope.selectedAdvertisingTypes.push(formattedId);
      } else {
        $scope.selectedAdvertisingTypes.splice(index, 1);
      }
      console.log(
        "Selected Advertising Types:",
        $scope.selectedAdvertisingTypes
      );
    };

    $scope.isAdvertisingTypeSelected = function (formatId) {
      // Convert formatId to string (or adjust based on the type in your case)
      var formattedId = String(formatId);
      return $scope.selectedAdvertisingTypes.indexOf(formattedId) !== -1;
    };

    // Initialize it as an empty object
    $scope.adminProductEdit = {};
    $scope.selectSearchedArea = function () {
      if ($scope.areaObj == null) {
        toastr.error("No DMA Found");
      }
      $scope.adminProductEdit.city_name = $scope.areaObj.city_name;
      console.log($scope.areaObj.id);
    };

    $scope.searchableAreas = function (query) {
      return MapService.searchAreas(query.toLowerCase()).then(function (res) {
        return res;
      });
    };

    $scope.pillData = []; // Array to store data for each pill
    $scope.selectedPillData = "";
    $scope.selectedPillId = ""; // Initialize selectedPillId
    $scope.isUpdating = false; // Flag to track whether it's an update operation

    MapService.getSearchFilterProduct()
      .then(function (getDataResponse) {
        $scope.pillData = getDataResponse;
        $scope.selectedPillData = getDataResponse;
      //   console.log("Pill Data:", $scope.pillData);
        $scope.getDataResponse = getDataResponse; // Save the data for use in the template
        $scope.isShownProductGetApi = true;
        // If data exists, you can perform the update API call here using getDataResponse
        if ($scope.pillData.length > 0) {
          $scope.selectedPillId = $scope.pillData[0].id;

          if (getDataResponse.length > 0) {
            $scope.selectedAdvertisingTypes =
              $scope.getDataResponse[0].product_type || [];
            $scope.product_impressions = parseInt(
              getDataResponse[0].product_impressions || " "
            );
            $scope.size = {
              width: parseInt(getDataResponse[0].product_width || " "),
              height: parseInt(getDataResponse[0].product_height || " "),
            };
            $scope.product_cpm = getDataResponse[0].product_cpm || " ";
            $scope.product_search = getDataResponse[0].product_search || " ";
            // $scope.product_dma = getDataResponse[0].product_dma;
            $scope.areaObj = {
              city_name: getDataResponse[0].product_dma || " ",
            }; // Assuming product_dma is the property with DMA city name
            $scope.product_lat = getDataResponse[0].product_lat || " ";
            $scope.product_long = getDataResponse[0].product_long || " ";
            $scope.product_radius = getDataResponse[0].product_radius || " ";
            $scope.product_startDate = new Date(
              getDataResponse[0].product_startDate || " "
            );
            $scope.product_endDate = new Date(
              getDataResponse[0].product_endDate || " "
            );
            $scope.search_criteria_name =
              getDataResponse[0].search_criteria_name || " ";
          }
        }
       // console.log("Data from GET request:", getDataResponse);
      })
      .catch(function (error) {
        console.error("Error fetching data:", error);
      });

    $scope.loadDataForPill = function (pillId) {
      // Use the pillId to fetch data from your API
      MapService.productSearchFilterById(pillId)
        .then(function (getDataResponse) {
          $scope.selectedPillId = pillId; // Set the selected pill ID
          $scope.selectedPillData = getDataResponse;
          // Update form fields with loaded data
          $scope.selectedAdvertisingTypes =
            $scope.selectedPillData[0].product_type;
          $scope.product_impressions = parseInt(
            $scope.selectedPillData[0].product_impressions || ""
          );
          $scope.size = {
            width: parseInt($scope.selectedPillData[0].product_width || ""),
            height: parseInt($scope.selectedPillData[0].product_height || ""),
          };
          $scope.product_cpm = $scope.selectedPillData[0].product_cpm || "";
          $scope.product_search =
            $scope.selectedPillData[0].product_search || "";
          // $scope.product_dma = $scope.selectedPillData[0].product_dma;
          $scope.areaObj = {
            city_name: $scope.selectedPillData[0].product_dma || "",
          }; // Assuming product_dma is the property with DMA city name
          $scope.product_lat = $scope.selectedPillData[0].product_lat || "";
          $scope.product_long = $scope.selectedPillData[0].product_long || "";
          $scope.product_radius =
            $scope.selectedPillData[0].product_radius || "";
          $scope.product_startDate = new Date(
            $scope.selectedPillData[0].product_startDate || ""
          );
          $scope.product_endDate = new Date(
            $scope.selectedPillData[0].product_endDate || ""
          );
          $scope.search_criteria_name =
            $scope.selectedPillData[0].search_criteria_name || "";
          // Set the flag to indicate it's an update operation
          $scope.isUpdating = true;
        })
        .catch(function (error) {
          console.error("Error loading data:", error);
        });
    };

    $scope.saveFilterSet = function () {
      var postData = {
        id:
          $scope.getDataResponse.length > 0
            ? $scope.getDataResponse[0].id
            : undefined,
        product_type: $scope.selectedAdvertisingTypes,
        product_impressions: parseInt($scope.product_impressions) || "",
        product_width: $scope.size.width || "" + " " + $scope.productType || "",
        product_height:
          $scope.size.height || "" + " " + $scope.productType || "",
        product_cpm: $scope.product_cpm || "",
        product_search: $scope.product_search || "",
        product_dma: $scope.areaObj ? $scope.areaObj.city_name : "", // Include DMA city name
        product_lat: $scope.product_lat || "",
        product_long: $scope.product_long || "",
        product_radius: $scope.product_radius || "",
        product_startDate: $scope.product_startDate || "",
        product_endDate: $scope.product_endDate || "",
        search_criteria_name: $scope.search_criteria_name || "",
      };
      if ($scope.isUpdating) {
        // Update API call with the ID
        postData.id = $scope.selectedPillData[0].id;

        MapService.filterSaveProducts(postData)
          .then(function (response) {
            toastr.success(response.message);
            // Reset the form and close the popup
            $scope.resetProduct();
            $scope.closePopup();
            $mdDialog.cancel();
            $state.reload();
            return MapService.getSearchFilterProduct();
          })
          .then(function (getDataResponse) {
            console.log("Data from GET request:", getDataResponse);
            $scope.isShownProductGetApi = true;
          })
          .catch(function (error) {
            toastr.error(error.message || "Error occurred");
          });
      } else {
        // Save API call for new data
        MapService.filterSaveProducts(postData)
          .then(function (response) {
            toastr.success(response.message);
            // Reset the form and close the popup
            $scope.resetProduct();
            $scope.closePopup();
            $mdDialog.cancel();
            $state.reload();
            return MapService.getSearchFilterProduct();
          })
          .then(function (getDataResponse) {
            console.log("Data from GET request:", getDataResponse);
            $scope.isShownProductGetApi = true;
          })
          .catch(function (error) {
            toastr.error(error.message || "Error occurred");
          });
      }
    };

    $scope.resetProduct = function () {
      // document.getElementById("myDropdown").classList.toggle("show");
      // Reset form-related variables here
      $scope.selectedAdvertisingType = null;
      $scope.product_impressions = null;
      $scope.size = { width: null, height: null };
      $scope.product_cpm = null;
      $scope.product_search = null;
      $scope.product_dma = null;
      $scope.product_lat = null;
      $scope.product_long = null;
      $scope.product_radius = null;
      $scope.product_startDate = null;
      $scope.product_endDate = null;
      $scope.search_criteria_name = null;
      // Reset the form itself
      $scope.filterForm.$setPristine();
      $scope.filterForm.$setUntouched();
      $mdDialog.hide();
    };

    $scope.closePopup = function () {
      $mdDialog.hide();
    };

    ProductService.getFormatList().then(function (formats) {
      $scope.formatList = formats;
      $scope.formatGrid = [];
      $scope.selectedFormats = [];
      var x = 3;
      var y = formats.length / x;
      var k = 0;
      for (var i = 0; i < y; i++) {
        var tempArr = [];
        for (var j = 0; j < x; j++) {
          tempArr.push(formats[k]);
          if (formats[k]) {
            $scope.selectedFormats.push(formats[k].id);
            k++;
          }
        }
        $scope.formatGrid.push(tempArr);
      }
    });

    $scope.showFilterMobileDialog = function (ev) {
      $mdDialog.show({
        templateUrl: "views/MobileViewFilters-modal.html",
        parent: angular.element(document.body),
        targetEvent: ev,
        fullscreen: $scope.customFullscreen,
        preserveScope: true,
        scope: $scope,
      });
    };

    $scope.cancel = function () {
      $mdDialog.cancel();
    };

    // Initialize your form data and current step
    $scope.formData = {};
    $scope.currentStep = 1;

    // Define your form stages and headers
    $scope.formStages = [
      { step: 1, header: "Step 1: Personal Information" },
      { step: 2, header: "Step 2: Address Information" },
      { step: 3, header: "Step 3: Confirmation" },
    ];

    // Function to move to the next step
    $scope.nextStep = function () {
      if ($scope.currentStep < $scope.formStages.length) {
        $scope.currentStep++;
      }
    };

    // Function to move to the previous step
    $scope.prevStep = function () {
      if ($scope.currentStep > 1) {
        $scope.currentStep--;
      }
    };

    $scope.allDone = function () {
      $mdDialog.cancel();
      $window.location.reload();
    };

    // Initialize searchFilterData object
    $scope.searchFilterData = {};

    $scope.redirectToLocation = function () {
      if ($scope.getDataResponse && $scope.getDataResponse.length > 0) {
        var id = "search_criteria";
        if (id) {
          $location.path("/location/" + id);
        } else {
          $location.path("/location");
        }
      } else {
        $location.path("/location");
      }
    };

    $scope.selectSearchedCity = function () {
      if (!$scope.areaObject) {
        toastr.error("No DMA Found");
      } else {
        $scope.browseModal = $scope.browseModal || {};
        $scope.browseModal.city_name = $scope.areaObject.city_name;
      }
    };

    $scope.browseForm = function () {
      var selectedDma = $scope.areaObject.city_name;
      var startDate = $scope.browseProd_startDate;
      var endDate = $scope.browseProd_endDate;
      // Format the dates to 'DD-MM-YYYY' format
      var formattedStartDate = formatDate(startDate);
      var formattedEndDate = formatDate(endDate);

      var id = "dma_search::" + selectedDma +  "::" +  formattedStartDate +  "::" + formattedEndDate;
      if (id) {
        $location.path("/location/" + id);
      } else {
        $location.path("/location");
      }
    };

    $scope.search_param = ""; 
    $scope.SearchForm = function() {
      var search_param  = $scope.additionalSearchTerm;
      console.log("searchTerm", search_param);
      var search_param = "search_param::" + search_param;
      if (search_param) {
        $location.path("/location/" + search_param);
      }else {
        $location.path("/location");
      }
    };



    // Function to format date to 'DD-MM-YYYY' format
    function formatDate(date) {
      var dd = date.getDate();
      var mm = date.getMonth() + 1; // January is 0!
      var yyyy = date.getFullYear();

      if (dd < 10) {
        dd = "0" + dd;
      }

      if (mm < 10) {
        mm = "0" + mm;
      }

      return dd + "-" + mm + "-" + yyyy;
    }

    $scope.browseMobileProducts = function() {
      // console.log("BrowseProducts function called");
      // document.getElementById("userbrowseProd").classList.toggle("show");
      var modal = document.getElementById("userbrowseMobileProd");
      modal.classList.toggle("show");
      modal.style.display = (modal.style.display === 'none' || modal.style.display === '') ? 'block' : 'none';
    };

    $scope.searchMobileProducts = function() {
      var modal = document.getElementById("searchMobileModal");
      modal.classList.toggle("show");
      modal.style.display = (modal.style.display === 'none' || modal.style.display === '') ? 'block' : 'none';
    };



    $scope.resetBrowseForm = function () {
      $scope.areaObject = null;
      $scope.browseProd_startDate = null;
      $scope.browseProd_endDate = null;
      // Reset the form itself
      $scope.browseSearchForm.$setPristine();
      $scope.browseSearchForm.$setUntouched();
    };

    $scope.closeAndResetBrowseForm = function () {
      document.getElementById("userbrowseProd").classList.remove("show");
      $scope.resetBrowseForm();
    };

    $scope.closeAndResetSearchModal = function () {
      document.getElementById("searchModal").classList.remove("show");
      $scope.resetBrowseForm();
    };
  },
]);
