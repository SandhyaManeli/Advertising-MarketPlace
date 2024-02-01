 angular.module("bbManager").controller("GmapCtrl", [
  "$scope",
  "$location",
  "$auth",
  "NgMap",
  "$mdSidenav",
  "$mdDialog",
  "$timeout",
  "$window",
  "$rootScope",
  "$routeParams",
  "MapService",
  "LocationService",
  "ProductService",
  "CampaignService",
  "FileSaver",
  "Blob",
  "config",
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
    $routeParams,
    MapService,
    LocationService,
    ProductService,
    CampaignService,
    FileSaver,
    Blob,
    config,
    toastr,
    $state,
    ManagementReportService
  ) {
    $scope.forms = {};
    $scope.address = {
      //  name: 'Hyderabad, Telangana, India',
      name: "United States of America",
      place: "",
      components: {
        placeId: "",
        streetNumber: "",
        street: "",
        city: "",
        state: "",
        countryCode: "",
        country: "",
        postCode: "",
        district: "",
        location: {
          lat: 40.25405,
          lng: -100.726083,
        },
      },
    };
    $scope.format = "yyyy/MM/dd";
    $scope.date = new Date();
    $scope.formats = ["dd-MMMM-yyyy", "yyyy/MM/dd", "dd.MM.yyyy", "shortDate"];
    $scope.format = $scope.formats[0];
    $scope.altInputFormats = ["M!/d!/yyyy"];
    $scope.ranges = {
      selectedDateRanges: [],
    };

    var campaignId = $location.$$url.split("/")[2];
    /*================================
    | Multi date range picker options
    ================================*/
    $scope.mapProductOpts = {
      multipleDateRanges: true,
      opens: "center",
      locale: {
        applyClass: "btn-green",
        applyLabel: "Apply",
        fromLabel: "From",
        format: "DD-MMM-YY",
        toLabel: "To",
        cancelLabel: "X",
        customRangeLabel: "Custom range",
      },
      // isInvalidDate: function (dt) {
      //     for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
      //         if (moment(dt) >= moment($scope.unavailalbeDateRanges[i].booked_from) && moment(dt) <= moment($scope.unavailalbeDateRanges[i].booked_to)) {
      //             return true;
      //         }
      //     }
      //     if (moment(dt) < moment()) {
      //         return true;
      //     }
      // },
      isCustomDate: function (dt) {
        for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
          if (
            moment(dt) >= moment($scope.unavailalbeDateRanges[i].booked_from) &&
            moment(dt) <= moment($scope.unavailalbeDateRanges[i].booked_to)
          ) {
            if (
              moment(dt).isSame(
                moment($scope.unavailalbeDateRanges[i].booked_from),
                "day"
              )
            ) {
              return ["available", "left-radius"];
            } else if (
              moment(dt).isSame(
                moment($scope.unavailalbeDateRanges[i].booked_to),
                "day"
              )
            ) {
              return ["available", "right-radius"];
            } else {
              return "available";
            }
          }
        }
        if (moment(dt) < moment()) {
          return "gray-blocked";
        }
      },
      eventHandlers: {
        "apply.daterangepicker": function (ev, picker) {},
      },
    };
    /*====================================
    | Multi date range picker options end
    ====================================*/
    $scope.IsDisabled = true;
    $scope.EnableDisable = function () {
      $scope.IsDisabled = $scope.campaign.name.length == 0;
    };

    //Lazy Loading
    $scope.limit = 6;
    $scope.loadMore = function (last, inview) {
      if (last && inview) {
        $scope.limit += 6;
      }
    };

    function getISOFormat(dt, isStartDate) {
      var year = dt.getFullYear();
      var month = dt.getMonth() + 1;
      var day = dt.getDate();
      month = month < 10 ? "0" + month : month;
      day = day < 10 ? "0" + day : day;
      var result = year + "-" + month + "-" + day;
      return result;
    }
    // FIlter Dates
    $scope.FilterDates = function (booked_from, booked_to, dateType) {
      $scope.clearSearch("right");
      if (dateType == "startDate") {
        $scope.booked_to = null; //to clear end date field
      }
      //if (booked_from && booked_to) {

      var newStartFormat;
      var newEndFormat;

      if (booked_from) newStartFormat = getISOFormat(booked_from, true);
      else newStartFormat = null;

      if (booked_to) newEndFormat = getISOFormat(booked_to, false);
      else newEndFormat = null;

      productList = [];
      locArr = [];
      uniqueMarkers = [];
      concentricMarkers = {};
      var filterObj = {
        area: $scope.selectedAreas,
        product_type: $scope.selectedFormats,
        booked_from: newStartFormat,
        booked_to: newEndFormat,
        productId: $scope.search?.siteNo,
        impression: $scope.secondImpressionsLeft,
        cpm: $scope.cpmLeft,
      };
      if (!$scope.isSearchDisabled) {
        filterObj.radiusSearch = $scope.radSearch;
      }
      $scope.plottingDone = false;
      MapService.filterProducts(filterObj).then(function (markers) {
        _.each(markersOnMap, function (v, i) {
          v.setMap(null);
          $scope.Clusterer.removeMarker(v);
        });
        markersOnMap = Object.assign([]);
        $scope.filteredMarkers = markers;
        $scope.productmarkerslist = markers;
        $scope.productListData = markers;

        /*---Add Bell Icon for Expired Products---*/
        angular.forEach($scope.productmarkerslist, function (item) {
          const areaTimeZoneType = item.area_time_zone_type;
          angular.forEach(item.product_details, function (newItem) {
            try {
              var date = new Date().getTime();
              newItem.isExpired = true;
              if (
                newItem.to_date &&
                newItem.to_date.$date &&
                newItem.to_date.$date.$numberLong &&
                newItem.to_date.$date.$numberLong >= date
              ) {
                newItem.isExpired = false;
              }
              const { startDate, endDate } = convertDateToMMDDYYYY(
                newItem,
                areaTimeZoneType
              );
              newItem["startDate"] = startDate;
              newItem["endDate"] = endDate;
              newItem["areaTimeZoneType"] = areaTimeZoneType;
            } catch (error) {
              console.log(error);
            }
          });
        });
        /*---//Add Bell Icon for Expired Products---*/
        $scope.productmarkerslist = $scope.productListData;
        $scope.processMarkers();
        if ($scope.productmarkerslist.length > 0) {
          var bounds = new google.maps.LatLngBounds();
          _.each(markersOnMap, function (v, i) {
            bounds.extend(v.getPosition());
          });
        } else {
          toastr.error("no marker found for the criteria you selected");
        }
      });
      /* } else {
        return false;
      }*/
    };
    // FIlter Dates Ends
    $scope.hidelocations = false;
    var setDefaultArea = function () {
      $scope.selectedArea = JSON.parse(localStorage.areaFromHome);
      var area = $scope.selectedArea;
      $scope.mapObj.setCenter({
        lat: Number(area.lat),
        lng: Number(area.lng),
      });
      var bounds = new google.maps.LatLngBounds();
      bounds.extend({
        lat: Number(area.lat),
        lng: Number(area.lng),
      });
      $scope.mapObj.fitBounds(bounds);
      localStorage.removeItem("areaFromHome");
    };

    $scope.today = new Date();

    $scope.mapObj;
    var markersOnMap = [];
    $scope.selectedProduct = null;
    $scope.selectedForNewCampaign = [];
    $scope.newCampaign = {};
    $scope.serverUrl = config.serverUrl;
    var trafficOn = false;
    $scope.siteNoSearch = "";
    $scope.showTrafficLegend = false;
    $scope.isMapInitialized = false;
    $scope.plottingDone = false;

    $scope.$watch(
      function () {
        return $mdSidenav("productDetails").isOpen();
      },
      function (newValue, oldValue) {
        if (newValue == false) {
          $scope.selectedProduct = null;
          selectorMarker.setMap(null);
          $scope.$parent.existingCampaignSidenavVisible = false;
        }
      }
    );

    // $scope.$watch(
    //     function () {
    //         return $mdSidenav('digitalProductDetails').isOpen();
    //     },
    //     function (newValue, oldValue) {
    //         if (newValue == false) {
    //             $scope.selectedProduct = null;
    //             selectorMarker.setMap(null);
    //             $scope.$parent.existingCampaignSidenavVisible = false;
    //         }
    //     }
    // );

    $scope.$watch(
      function () {
        //return $mdSidenav('suggestMe').isOpen();
      },
      function (newValue, oldValue) {
        if (newValue == false) {
          $scope.suggestMeRequestSent = false;
        }
      }
    );

    var trafficLayer = new google.maps.TrafficLayer();
    var selectorMarker = new google.maps.Marker({
      icon: {
        url: "assets/images/maps/Ellipse 75.png",
        scaledSize: new google.maps.Size(30, 30),
        // origin: new google.maps.Point(0, 0), // origin
        // anchor: new google.maps.Point(20, 30) // anchor
      },
    });

    $scope.product = {};

    function correctLatLng(marker) {
      try {
        if (!marker.lat || marker.lat.toString().search(/[a-zA-Z]/i) > -1)
          marker.lat = 12.971599;
        if (!marker.lng || marker.lng.toString().search(/[a-zA-Z]/i) > -1)
          marker.lng = 77.594566;

        if (
          !marker._id.lat ||
          marker._id.lat.toString().search(/[a-zA-Z]/i) > -1
        )
          marker._id.lat = 12.971599;
        if (
          !marker._id.lng ||
          marker._id.lng.toString().search(/[a-zA-Z]/i) > -1
        )
          marker._id.lng = 77.594566;

        marker.lat = parseFloat(marker.lat);
        marker.lng = parseFloat(marker.lng);
        marker._id.lat = parseFloat(marker._id.lat);
        marker._id.lng = parseFloat(marker._id.lng);

        marker.product_details.forEach((p) => {
          if (!p.lat || p.lat.toString().search(/[a-zA-Z]/i) > -1)
            p.lat = 12.971599;
          if (!p.lng || p.lng.toString().search(/[a-zA-Z]/i) > -1)
            p.lng = 77.594566;

          p.lat = parseFloat(p.lat);
          p.lng = parseFloat(p.lng);
        });
      } catch (ex) {
        console.log("correctLatLng: Exception: " + ex.message);
      }
    }
    $("#showRightPush").hide();

    $scope.page_params = {
      page_no: 1,
      page_size: 5,
    };

    $scope.markers = [];

    $scope.searchTermAry = [
      {
        searchTerm: "",
      },
    ];
    $scope.searchTerms = [];

    /* Debounce */

    function debounce(func, timeout = 700) {
      let timer;
      return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => {
          func.apply(this, args);
        }, timeout);
      };
    }
    /* End of Debounce */

    function keyUpHandler(searchTerm) {
      $scope.clearSearch("left");
      if (searchTerm == "" || searchTerm.length >= 3) {
        $scope.page_params.page_no = 1;
        $scope.searchTerms = $scope.searchTermAry
          .map((item) => {
            console.log(item.searchTerm);
            return item.searchTerm;
          })
          .filter((item) => item !== "");
        if ($scope.searchTerms && $scope.searchTerms.length > 1) {
          $scope.searchTerms = $scope.searchTerms.join("::");
          mapProductsfiltered($scope.searchTerms, false);
        } else if ($scope.searchTerms.length == 1) {
          mapProductsfiltered($scope.searchTerms[0], false);
        } else {
          mapProductsfiltered("", false);
        }
      }
    }

    $scope.searchTermChanged = debounce((searchTerm) => {
      keyUpHandler(searchTerm);
      mapProductsfiltered(searchTerm);
    }, 700);

    $scope.addNewSearchField = function () {
      $scope.searchTermAry.push({ searchTerm: "" });
    };

    $scope.removeLastSearchField = function () {
      $scope.searchTermAry.pop();
      $scope.searchTermChanged("");
    };

    function convertDateToMMDDYYYY(dates, areaTimeZoneType) {
      if (!areaTimeZoneType) {
        areaTimeZoneType = Intl.DateTimeFormat().resolvedOptions().timeZone;
      }
      const startDate = parseInt(dates.from_date.$date.$numberLong);
      const endDate = parseInt(dates.to_date.$date.$numberLong);
      const splitStartDate = new Date(startDate)
        .toLocaleString("en-GB", { timeZone: areaTimeZoneType })
        .slice(0, 10)
        .split("/");
      [splitStartDate[0], splitStartDate[1]] = [
        splitStartDate[1],
        splitStartDate[0],
      ];

      const splitEndDate = new Date(endDate)
        .toLocaleString("en-GB", { timeZone: areaTimeZoneType })
        .slice(0, 10)
        .split("/");
      [splitEndDate[0], splitEndDate[1]] = [splitEndDate[1], splitEndDate[0]];

      return {
        startDate: splitStartDate.join("-"),
        endDate: splitEndDate.join("-"),
      };
    }

    function convertDateToYYYYMMDD(dates, areaTimeZoneType) {
      const startDate = parseInt(dates.from_date.$date.$numberLong);
      const endDate = parseInt(dates.to_date.$date.$numberLong);
      const splitStartDate = new Date(startDate)
        .toLocaleString("en-GB", { timeZone: areaTimeZoneType })
        .slice(0, 10)
        .split("/");

      const splitEndDate = new Date(endDate)
        .toLocaleString("en-GB", { timeZone: areaTimeZoneType })
        .slice(0, 10)
        .split("/");
      return {
        startDate: splitStartDate.reverse().join("-"),
        endDate: splitEndDate.reverse().join("-"),
      };
    }

    function mapProductsfiltered(search_param = "", isFromScroll = true) {
      $rootScope.isLoading = true;
      $scope.isProductsLoading = true;
      MapService.mapProductsfiltered({
        page_params: $scope.page_params,
        search_param,
      }).then(function (resMarkers) {
        angular.forEach(resMarkers, function (item) {
          const areaTimeZoneType = item.area_time_zone_type;
          angular.forEach(item.product_details, function (newItem) {
            try {
              var date = new Date().getTime();
              newItem.isExpired = true;
              if (
                newItem.to_date &&
                newItem.to_date.$date &&
                newItem.to_date.$date.$numberLong &&
                newItem.to_date.$date.$numberLong >= date
              ) {
                newItem.isExpired = false;
              }
            } catch (error) {
              console.log(error);
            }
            const { startDate, endDate } = convertDateToMMDDYYYY(
              newItem,
              areaTimeZoneType
            );
            newItem["startDate"] = startDate;
            newItem["endDate"] = endDate;
            newItem["areaTimeZoneType"] = areaTimeZoneType;
          });
        });
        if (isFromScroll) {
          $scope.markers = $scope.markers.concat(resMarkers);
        } else {
          $scope.markers = resMarkers;
        }
        var markers = $scope.markers;
        $rootScope.isLoading = true;
        markers.forEach((m) => correctLatLng(m));
        $scope.filteredMarkers = markers;
        NgMap.getMap().then(function (map) {
          $scope.mapObj = map;
          $scope.processMarkers();
          if (localStorage.areaFromHome) {
            setDefaultArea();
          }
          $scope.mapObj.addListener("zoom_changed", function () {
            $scope.selectedProduct = null;
            selectorMarker.setMap(null);
          });
        });
        $scope.actualDataCopy = markers;
        $scope.productmarkerslist = markers;
        $scope.productListData = markers;
        console.log("productmarkerslist", $scope.productmarkerslist.length);
        if (!$mdSidenav("productList").isOpen())
          $mdSidenav("productList").toggle();
        $timeout(function () {
          $("#showRightPush").show();
        });
        /*
        angular.forEach($scope.productmarkerslist, function(item) {
            angular.forEach(item.product_details, function(newItem) {
              try {
                var date = new Date().getTime();
                newItem.isExpired = true;
                if (newItem.to_date && newItem.to_date.$date && newItem.to_date.$date.$numberLong && newItem.to_date.$date.$numberLong >= date) {
                  newItem.isExpired = false;
                } 
                // $scope.productListData.push(item);
              } catch (error) {
                console.log(error);
              }
            });
        });
        */
        $rootScope.isLoading = true;
        $scope.isProductsLoading = false;
      });
    }

    $scope.isProductsLoading = false;
    var campaignIdUrlmap = $location.$$url.split("/")[2];
    var exists = false;
    var existsText = false;
    $scope.search_param_scroll = campaignIdUrlmap;
    if (campaignIdUrlmap) {
      var wordToCheck = "dma_search";
      var lowerCaseInput = campaignIdUrlmap.toLowerCase();
      var lowerCaseWord = wordToCheck.toLowerCase();
      exists = lowerCaseInput.includes(lowerCaseWord);

      var wordToCheckText = "search_param";
      var lowerCaseInputText = campaignIdUrlmap.toLowerCase();
      var lowerCaseWordText = wordToCheckText.toLowerCase();
      existsText = lowerCaseInputText.includes(lowerCaseWordText);
      $scope.exists_search_param = existsText;
    }
    if (campaignIdUrlmap !== "search_criteria" && !exists && !existsText) {
      mapProductsfiltered();
    }


    const productListDiv = jQuery("#prodlist");
    productListDiv.on("scroll", (event) => {
      if ($scope.isLeftSearch) return;
      if (
        productListDiv.scrollTop() + Math.round(productListDiv.outerHeight()) >=
        event.target.scrollHeight
      ) {
        if (!$scope.isProductsLoading) {
          $scope.isSchroll = true;
          $scope.page_params.page_no = parseInt($scope.page_params.page_no) + 1;
          // console.log("dileep_durga", $scope.selectedCoordinates);
          if($scope.exists_search_param){
            var split_search_param = $scope.search_param_scroll.split("::");
           // console.log("dileep_durga_split_search_param", split_search_param[1]);
            $scope.searchTerms = split_search_param[1];
          }
         // console.log("dileep_durga_param", $scope.searchTerms);
          if ($scope.selectedCoordinates.length == 0) {
            if ($scope.searchTerms.length) {
              mapProductsfiltered($scope.searchTerms);
            } else {
              mapProductsfiltered();
            }
          }
        }
      }
    });

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
    $scope.countries = [];
    $scope.states = [];
    $scope.cities = [];
    $scope.areas = [];
    LocationService.getCountries().then(function (countries) {
      $scope.countries = countries;
    });
    $scope.Sectors = [];
    // MapService.getIndustrySectors().then(function(Sectors){
    //   $scope.Sectors = Sectors;
    // });
    $scope.DurationSectors = [];

    // clender
    $scope.opened = {
      start: false,
      end: false,
    };

    $scope.today = new Date();
    $scope.filter = false;
    $scope.format = false;
    $scope.shortlist = false;
    $scope.savedcampaign = false;

    $scope.Recommended = false;
    $scope.Popular = false;
    $scope.footerhide = true;

    $scope.locationpageonly = true;

    $scope.dashboardData = false;

    $scope.filters = function () {
      $scope.filter = !$scope.filter;
      $scope.format = false;
      $scope.shortlist = false;
      $scope.savedcampaign = false;
    };
    $scope.formats = function () {
      $scope.filter = false;
      $scope.format = !$scope.format;
      $scope.shortlist = false;
      $scope.savedcampaign = false;
    };

    $scope.shortlistDiv = function () {
      $scope.filter = false;
      $scope.format = false;
      $scope.shortlist = !$scope.shortlist;
      $scope.savedcampaign = false;
    };

    $scope.savedcampaignDiv = function () {
      $scope.filter = false;
      $scope.format = false;
      $scope.shortlist = false;
      $scope.savedcampaign = !$scope.savedcampaign;
    };
    $scope.RecommendedDiv = function () {
      $scope.Recommended = !$scope.Recommended;
      $scope.Popular = false;
    };
    $scope.PopularDiv = function () {
      $scope.Recommended = false;
      $scope.Popular = !$scope.Popular;
    };
    $scope.pointermap = function () {
      $scope.ispointer = !$scope.ispointer;
    };
    // $scope.showProductImagePopup = function (ev, img_src) {
    //     $mdDialog.show({
    //         locals: { src: img_src },
    //         templateUrl: 'views/image-popup-large.html',
    //         fullscreen: $scope.customFullscreen,
    //         clickOutsideToClose: true,
    //         controller: function ($scope, src) {
    //             $scope.img_src = src;
    //             $scope.closeMdDialog = function () {
    //                 $mdDialog.hide();
    //             }
    //         }
    //     });
    // };
    // need to check
    $scope.showProductImagePopup = function (ev, proddetails) {
      $scope.specProductDetail = proddetails;
      $mdDialog.show({
        locals: {
          specProductDetail: proddetails,
        },
        templateUrl: "views/image-popup-large.html",
        fullscreen: $scope.customFullscreen,
        clickOutsideToClose: true,
        controller: function ($scope, specProductDetail) {
          $scope.specDetails = specProductDetail;
          // $scope.productDetails = proddetails
          //$scope.img_src_1 = src.f_image;
          $scope.closeMdDialog = function () {
            $mdDialog.hide();
            localStorage.removeItem("mindays");
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
    $scope.closeMdDialog = function () {
      $mdDialog.hide();
    };
    $scope.showProductDate = function (ev, product) {
      $mdDialog.show({
        templateUrl: "views/map-calendar-popup.html",
        fullscreen: $scope.customFullscreen,
        clickOutsideToClose: true,
        preserveScope: true,
        scope: $scope,
        controller: function ($scope) {
          $scope.closeMdDialog = function () {
            $mdDialog.hide();
          };
          setTimeout(() => {
            angular.element($("#calender-autolaod-div")).trigger("click");
          }, 100);
        },
      });
    };
    // need to check
    $scope.selectedCountry = {};
    $scope.selectedStates = {};
    $scope.selectedcitys = {};
    $scope.selectedareas = {};

    $scope.searchTerm;
    $scope.clearSearchTerm = function () {
      $scope.searchTerm = "";
    };
    $scope.setCountry = function () {
      LocationService.getStates($scope.selectedCountry).then(function (states) {
        $scope.states = states;
      });
    };
    $scope.setStates = function () {
      LocationService.getCities($scope.selectedStates).then(function (cities) {
        $scope.cities = cities;
      });
    };
    $scope.setCities = function () {
      LocationService.getAreas($scope.selectedcitys).then(function (areas) {
        $scope.areas = areas;
      });
    };

    //Confirm Dialog 1
    $scope.showConfirmation = function (ev) {
      $mdDialog.show(
        $mdDialog
          .alert()
          .parent(angular.element(document.querySelector("body")))
          .clickOutsideToClose(true)
          .title("Your Campaign is successfully Saved!!!!")
          //.textContent('You can specify some description text in here.')
          .ariaLabel("Alert Dialog Demo")
          .ok("Confirmed!")
          .targetEvent(ev)
      );
    };
    //export all

    // $scope.exportAllCampaigns = function () {
    //     CampaignService.exportCampaignsPdf().then(function (result) {
    //         var campaignPdf = new Blob([result], {type: 'application/pdf;charset=utf-8'});
    //         FileSaver.saveAs(campaignPdf, 'campaigns.pdf');
    //         if (result.status) {
    //             toastr.error(result.meesage);
    //         }
    //     });
    // };
    ////////////////////////////////////////////////////////////////////////
    // tablet filters filtersMap

    $scope.toggleViewAllFilter = function () {
      $mdSidenav("filtersMobile").toggle();
    };
    $scope.mapFilter = function () {
      $mdSidenav("filtersMap").toggle();
    };
    $scope.shortListed = function () {
      $mdSidenav("shortlistedList").toggle();
    };
    $scope.savedCampagin = function () {
      $mdSidenav("savedCamapgin").toggle();
    };
    ////////////////////////////////////////////////////////////////////
    //Suggest Me Dialog 1
    $scope.suggestionRequest = {};
    $scope.suggestMeRequestSent = false;
    // date picker validation
    // $scope.checkErr = function (startDate, endDate) {
    //   $scope.errMessage = '';
    //   var curDate = new Date();

    //   if (new Date(startDate) > new Date(endDate)) {
    //     $scope.errMessage = 'End Date should be greater than start date';
    //     return false;
    //   }
    //   if (new Date(startDate) < curDate) {
    //     $scope.errMessage = 'Start date should not be before today.';
    //     return false;
    //   }
    // };
    $scope.newDate = new Date();
    // $scope.start_date = new Date();
    $scope.endDate = $scope.start_date + 1;

    //    if(start_date > end_date){
    //     $scope.errMessage = 'end date should not be before start day.';
    //     return false;
    //  }
    /* Get All Markers Product*/
    function getAllMarkers() {
      MapService.mapAllProducts().then(function (res) {
        $scope.filteredMarkers1 = res;
        NgMap.getMap().then(function (map) {
          $scope.mapObj = map;
          $scope.processMarkers();
        });
      });
    }
    getAllMarkers();

    function selectMarker(marker) {
      $scope.isProdLoading = true;
      MapService.mapSingleProduct(marker.properties.id).then(function (res) {
        $scope.mapProdDetails = res;
        // console.log($scope.mapProdDetails);
        // $scope.$parent.alreadyShortlisted = false;
        // $scope.mapObj.setCenter(marker.position);
        // selectorMarker.setPosition(marker.position);
        // selectorMarker.setMap($scope.mapObj);
        // $scope.product.id = $scope.mapProdDetails.id;
        // $scope.product.rateCard = $scope.mapProdDetails.rateCard;
        // $scope.product.image = config.serverUrl + $scope.mapProdDetails.image[0];
        // $scope.product.siteNo = $scope.mapProdDetails.siteNo;
        // $scope.product.city = $scope.mapProdDetails.city;
        // $scope.product.panelSize = $scope.mapProdDetails.panelSize;
        // $scope.product.type = $scope.mapProdDetails.type;
        // $scope.product.weekPeriod = $scope.mapProdDetails.weekPeriod;
        // $scope.product.venue = $scope.mapProdDetails.venue;
        // $scope.product.vendor = $scope.mapProdDetails.vendor;
        // $scope.product.address = $scope.mapProdDetails.address;
        // $scope.product.area_name = $scope.mapProdDetails.area_name;
        // $scope.product.secondImpression = $scope.mapProdDetails.secondImpression;
        // $scope.product.firstImpression = $scope.mapProdDetails.firstImpression;
        // $scope.product.thirdImpression = $scope.mapProdDetails.thirdImpression;
        // $scope.product.forthImpression = $scope.mapProdDetails.forthImpression;
        // $scope.product.cpm = $scope.mapProdDetails.cpm;
        // $scope.product.firstcpm = $scope.mapProdDetails.firstcpm;
        // $scope.product.thirdcpm = $scope.mapProdDetails.thirdcpm;
        // $scope.product.forthcpm = $scope.mapProdDetails.forthcpm;
        // $scope.product.sellerId = $scope.mapProdDetails.sellerId;
        // $scope.product.negotiatedCost = $scope.mapProdDetails.negotiatedCost;
        // $scope.product.title = $scope.mapProdDetails.title;
        // $scope.product.looplength = $scope.mapProdDetails.looplength;
        // $scope.product.fliplength = $scope.mapProdDetails.fliplength;
        // $scope.product.audited = $scope.mapProdDetails.audited;
        // $scope.product.format = marker.properties["format_name"];
        // $scope.product.lighting = $scope.mapProdDetails.lighting;
        // $scope.product.direction = $scope.mapProdDetails.direction;
        // $scope.product.availableDates = $scope.mapProdDetails.from_date;
        // $scope.product.slots = $scope.mapProdDetails.slots;
        // $scope.product.lat = $scope.mapProdDetails.lat;
        // $scope.product.lng = $scope.mapProdDetails.lng;
        // $scope.product.installCost = $scope.mapProdDetails.installCost;
        // $scope.product.zipcode = $scope.mapProdDetails.zipcode;
        // $scope.product.Comments = $scope.mapProdDetails.Comments;
        // $scope.product.locationDesc = $scope.mapProdDetails.locationDesc;
        // $scope.product.minimumbooking = $scope.mapProdDetails.minimumbooking;
        // $scope.product.strengths = $scope.mapProdDetails.strengths;
        // $scope.product.staticMotion = $scope.mapProdDetails.staticMotion;
        // $scope.product.mediahhi = $scope.mapProdDetails.mediahhi;
        // $scope.product.sound = $scope.mapProdDetails.sound;
        // $scope.product.notes = $scope.mapProdDetails.notes;
        // $scope.product.productioncost = $scope.mapProdDetails.productioncost;
        // $scope.hideSelectedMarkerDetail = false;
        // $scope.product.endTime = new Date(parseInt($scope.mapProdDetails.to_date.$date.$numberLong));
        // $scope.product.originalFromTime = new Date(parseInt($scope.mapProdDetails.from_date.$date.$numberLong));

        $scope.product.id = $scope.mapProdDetails.id;
        $scope.product.image =
          config.serverUrl + $scope.mapProdDetails.image[0];
        $scope.product.title = $scope.mapProdDetails.title;
        $scope.product.siteNo = $scope.mapProdDetails.siteNo;
        $scope.product.type = $scope.mapProdDetails.type;
        $scope.product.address = $scope.mapProdDetails.address;
        $scope.product.city = $scope.mapProdDetails.city;
        $scope.product.state = $scope.mapProdDetails.state;
        $scope.product.country_name = $scope.mapProdDetails.country_name;
        $scope.product.zipcode = $scope.mapProdDetails.zipcode;
        $scope.product.height = $scope.mapProdDetails.height;
        $scope.product.length = $scope.mapProdDetails.length;
        $scope.product.width = $scope.mapProdDetails.width;
        $scope.product.panelSize = $scope.mapProdDetails.panelSize;
        $scope.product.minimumdays = $scope.mapProdDetails.minimumdays;
        $scope.product.audited = $scope.mapProdDetails.audited;
        $scope.product.sellerId = $scope.mapProdDetails.sellerId;
        $scope.product.unitQty = $scope.mapProdDetails.unitQty;
        $scope.product.mediahhi = $scope.mapProdDetails.mediahhi;
        $scope.product.rateCard = $scope.mapProdDetails.rateCard;
        $scope.product.installCost = $scope.mapProdDetails.installCost;
        $scope.product.weekPeriod = $scope.mapProdDetails.weekPeriod;
        $scope.product.productioncost = $scope.mapProdDetails.productioncost;
        $scope.product.billing =
          $scope.mapProdDetails["billingYes"] == "yes" ? "Yes" : "No";
        $scope.product.servicing =
          $scope.mapProdDetails["servicingNo"] == "no" ? "No" : "Yes";
        $scope.product.network = $scope.mapProdDetails.network;
        $scope.product.nationloc = $scope.mapProdDetails.nationloc;
        $scope.product.fix = $scope.mapProdDetails.fix;
        $scope.product.genre = $scope.mapProdDetails.genre;
        $scope.product.daypart = $scope.mapProdDetails.daypart;
        $scope.product.firstImpression = $scope.mapProdDetails.firstImpression;
        $scope.product.firstcpm = $scope.mapProdDetails.firstcpm;
        $scope.product.secondImpression =
          $scope.mapProdDetails.secondImpression;
        $scope.product.cpm = $scope.mapProdDetails.cpm;
        $scope.product.thirdImpression = $scope.mapProdDetails.thirdImpression;
        $scope.product.thirdcpm = $scope.mapProdDetails.thirdcpm;
        $scope.product.forthImpression = $scope.mapProdDetails.forthImpression;
        $scope.product.forthcpm = $scope.mapProdDetails.forthcpm;
        $scope.product.imgdirection = $scope.mapProdDetails.imgdirection;
        $scope.product.cancellation_policy =
          $scope.mapProdDetails.cancellation_policy;
        $scope.product.cancellation_terms =
          $scope.mapProdDetails.cancellation_terms;
        $scope.product.notes = $scope.mapProdDetails.notes;
        $scope.product.description = $scope.mapProdDetails.description;
        $scope.product.direction = $scope.mapProdDetails.direction;
        $scope.product.lat = $scope.mapProdDetails.lat;
        $scope.product.lng = $scope.mapProdDetails.lng;
        $scope.product.placement = $scope.mapProdDetails.placement;
        $scope.product.spotLength = $scope.mapProdDetails.spotLength;
        $scope.product.fliplength = $scope.mapProdDetails.fliplength;
        $scope.product.ageloopLength = $scope.mapProdDetails.ageloopLength;
        $scope.product.staticMotion = $scope.mapProdDetails.staticMotion;
        $scope.product.sound = $scope.mapProdDetails.sound;
        $scope.product.medium = $scope.mapProdDetails.medium;
        $scope.product.product_newMedia =
          $scope.mapProdDetails.product_newMedia;
        $scope.product.file_type = $scope.mapProdDetails.file_type;
        $scope.product.locationDesc = $scope.mapProdDetails.locationDesc;
        $scope.product.lighting = $scope.mapProdDetails.lighting;
        $scope.product.Comments = $scope.mapProdDetails.Comments;
        $scope.isProdLoading = false;
      });
      $scope.toggleProductDetailSidenav();
      // $scope.getProductUnavailableDatesautoload(marker.properties['id']);
      // if (marker.properties['type'] == "Bulletin") {
      //     $mdSidenav('productDetails').open();
      // } else if (marker.properties['type'] == "Digital Bulletin" || marker.properties['type'] == "Transit") {
      //     $mdSidenav('digitalProductDetails').open();
      // }
      $mdSidenav("productDetails").open();
      $scope.selectedProduct = marker;
    }

    function selectSpideredMarker(marker) {
      $scope.toggleProductDetailSidenav();
      $scope.$parent.alreadyShortlisted = false;
      $scope.mapObj.setCenter(marker.position);
      selectorMarker.setMap(null);
      $scope.product.id = marker.properties["id"];
      $scope.product.rateCard = marker.properties["rateCard"];
      $scope.product.image = config.serverUrl + marker.properties["image"];
      $scope.product.siteNo = marker.properties["siteNo"];
      $scope.product.city = marker.properties["city"];
      $scope.product.panelSize = marker.properties["panelSize"];
      $scope.product.type = marker.properties["type"];
      $scope.product.vendor = marker.properties["vendor"];
      $scope.product.address = marker.properties["address"];
      $scope.product.weekPeriod = marker.properties["weekPeriod"];
      $scope.product.venue = marker.properties["venue"];
      $scope.product.area_name = marker.properties["area_name"];
      $scope.product.secondImpression = marker.properties["secondImpression"];
      $scope.product.firstImpression = marker.properties["firstImpression"];
      $scope.product.thirdImpression = marker.properties["thirdImpression"];
      $scope.product.forthImpression = marker.properties["forthImpression"];
      $scope.product.cpm = marker.properties["cpm"];
      $scope.product.firstcpm = marker.properties["firsstcpm"];
      $scope.product.thirdcpm = marker.properties["thirdcpm"];
      $scope.product.forthcpm = marker.properties["forthcpm"];
      $scope.product.sellerId = marker.properties["sellerId"];
      $scope.product.negotiatedCost = marker.properties["negotiatedCost"];
      $scope.product.title = marker.properties["title"];
      $scope.product.looplength = marker.properties["looplength"];
      $scope.product.audited = marker.properties["audited"];
      $scope.product.format = marker.properties["format_name"];
      $scope.product.notes = marker.properties["notes"];
      $scope.product.Comments = marker.properties["Comments"];
      $scope.product.direction = marker.properties["direction"];
      $scope.product.description = marker.properties["description"];
      $scope.product.lighting = marker.properties["lighting"];
      $scope.product.availableDates = marker.properties["availableDates"];
      $scope.product.slots = marker.properties["slots"];
      $scope.product.mediahhi = marker.properties["mediahhi"];
      $scope.product.staticMotion = marker.properties["staticMotion"];
      $scope.product.sound = marker.properties["sound"];
      $scope.product.lat = marker.properties["lat"];
      $scope.product.lng = marker.properties["lng"];
      $scope.product.fliplength = marker.properties["fliplength"];
      $scope.product.installCost = marker.properties["installCost"];
      $scope.product.zipcode = marker.properties["zipcode"];
      $scope.product.locationDesc = marker.properties["locationDesc"];
      $scope.product.minimumbooking = marker.properties["minimumbooking"];
      $scope.product.strengths = marker.properties["strengths"];
      $scope.product.productioncost = marker.properties["productioncost"];
      $scope.hideSelectedMarkerDetail = false;
      // if (marker.properties['type'] == "Bulletin") {
      //     $scope.productPerDay = $scope.product.price / 28;
      // } else if (marker.properties['type'] == "Digital" || marker.properties['type'] == "Transit Bulletin") {
      // $scope.productPerDay = $scope.product.price / 28;
      // }
      $mdSidenav("productDetails").open();
      // $scope.getProductUnavailableDatesautoload(marker.properties['id']);
      $scope.selectedProduct = marker;
    }

    /* function selectSpideredMarker(marker) {
             $scope.$parent.alreadyShortlisted = false;
             $scope.mapObj.setCenter(marker.position);
             selectorMarker.setMap(null);
             
             $mdSidenav('productDetails').toggle();
             $scope.getProductUnavailableDatesautoload(marker.properties['id']);
             $scope.selectedProduct = marker;
             }*/

    google.maps.event.addListener(selectorMarker, "click", function (e) {
      $scope.selectedProduct = null;
      selectorMarker.setMap(null);
    });

    /* google.maps.event.addListener(selectorMarker, 'click', function (e) {
             selectorMarker.setMap(null);
             });*/

    var productList = [];
    var locArr = [];
    var uniqueMarkers = [];
    var concentricMarkers = {};
    var uniqueMarkerArr = [];
    $scope.processMarkers = function () {
      markersOnMap = Object.assign([]);
      uniqueMarkerArr = Object.assign([]);
      /*_.each($scope.filteredMarkers, function (v, i) {
                 var product = {position: {lat: v.lat, lng: v.lng}, data: v};
                 productList.push(product);
                 if (locArr[JSON.stringify(product.position)]) {
                 locArr[JSON.stringify(product.position)]++;
                 } else {
                 locArr[JSON.stringify(product.position)] = 1;
                 }
                 });*/

      var mc = {
        gridSize: 50,
        maxZoom: 13,
        imagePath: "assets/images/maps/m",
      };
      $scope.Clusterer = new MarkerClusterer(
        $scope.mapObj,
        uniqueMarkerArr,
        mc
      );
      var circleMarker = new google.maps.Marker({
        icon: {
          url: "assets/images/maps/Ellipse 55.png",
          scaledSize: new google.maps.Size(55, 55),
          origin: new google.maps.Point(0, 0), // origin
          anchor: new google.maps.Point(27.8, 29.5), // anchor
        },
      });
      var oms = new OverlappingMarkerSpiderfier($scope.mapObj, {
        markersWontMove: true,
        markersWontHide: true,
        basicFormatEvents: true,
        circleSpiralSwitchover: Infinity,
        legWeight: 0,
        circleFootSeparation: 32,
        nearbyDistance: 1,
        keepSpiderfied: true,
      });

      oms.addListener("format", function (marker, status) {
        var markerIcon;
        var label = marker.getLabel();
        var scaledCoord = 32 + 10 * (marker.groupSize - 1);
        var circleMarkerIcon = {
          url: "assets/images/maps/Ellipse 55.png",
          scaledSize: new google.maps.Size(scaledCoord, scaledCoord),
          origin: new google.maps.Point(0, 0), // origin
          anchor: new google.maps.Point(scaledCoord / 2, scaledCoord / 2),
        };
        if (status == OverlappingMarkerSpiderfier.markerStatus.SPIDERFIED) {
          // when markers are scattered
          label.color = "rgba(255, 255, 255, 0)";
          marker.setLabel(label);
          circleMarker.setPosition(marker.getPosition());
          circleMarker.setIcon(circleMarkerIcon);
          circleMarker.setMap($scope.mapObj);
          markerIcon = {
            url: config.serverUrl + marker.properties["symbol"], //'assets/images/maps/spidered-marker.png',
            scaledSize: new google.maps.Size(30, 30),
            origin: new google.maps.Point(0, 0), // origin
            anchor: new google.maps.Point(15, 15), // anchor
          };
        } else {
          // when markers are grouped as one
          markerIcon = {
            url: "assets/images/maps/pin.png",
            scaledSize: new google.maps.Size(20, 20),
            origin: new google.maps.Point(0, 0), // origin
            anchor: new google.maps.Point(10, 10), // anchor
          };
          label.color = "rgba(255, 255, 255, 1)";
          marker.setLabel(label);
          // spiderCircle.setMap(null);
          circleMarker.setMap(null);
        }
        marker.setIcon(markerIcon);
      });

      function addNewMarkers(markerData) {
        for (var i = 0; i < markerData.product_details.length; i++) {
          var label = {};
          //label.text = " ";
          label.color = "rgba(255, 255, 255, 1)";
          //if (i == 0) {
          label.text = markerData.product_details.length.toString();
          //}
          var icon = {
            url: "assets/images/maps/unspidered-cluster.png",
            scaledSize: new google.maps.Size(20, 20),
            origin: new google.maps.Point(0, 0), // origin
            anchor: new google.maps.Point(10, 10), // anchor
          };
          var marker = new google.maps.Marker({
            position: {
              lat: parseFloat(markerData._id.lat),
              lng: parseFloat(markerData._id.lng),
            },
            icon: icon,
            label: label,
            title:
              "Location:" +
              markerData.product_details[i].address +
              "\nNo. of views: " +
              markerData.product_details[i].secondImpression +
              "\nTitle: " +
              markerData.product_details[i].title +
              "\nSite No: " +
              markerData.product_details[i].siteNo,
          });
          marker.properties = markerData.product_details[i];
          marker.groupSize = markerData.product_details.length;
          google.maps.event.addListener(marker, "spider_click", function (e) {
            $scope.toggleProductDetailSidenav();
            selectSpideredMarker(this);
          });
          markersOnMap.push(marker);
          oms.addMarker(marker); // adds the marker to the spiderfier _and_ the map
          $scope.Clusterer.addMarker(marker);
        }
      }
      function addUniqueMarker(markerData) {
        uniqueMarkers.push(markerData.product_details);
        var latLng = new google.maps.LatLng(
          markerData._id.lat,
          markerData._id.lng
        );
        var marker = new google.maps.Marker({
          position: latLng,
          title:
            "Location:" +
            markerData.product_details[0].address +
            "\nNo. of views: " +
            markerData.product_details[0].secondImpression,
        });
        marker.properties = markerData.product_details[0];
        uniqueMarkerArr.push(marker);
        markersOnMap.push(marker);
        $scope.Clusterer.addMarker(marker);

        google.maps.event.addListener(marker, "click", function (e) {
          selectMarker(marker);
        });
      }
      $scope.loading = true;
      _.each($scope.filteredMarkers1, function (data) {
        if (data.product_details.length == 1) {
          addUniqueMarker(data);
        } else if (data.product_details.length > 1) {
          addNewMarkers(data);
        }
        $scope.plottingDone = true;
        $scope.loading = false;
      });
      // instantiate oms when click occurs on marker-group
    };

    $scope.$parent.$watch("trafficOn", function (oldValue, newValue) {
      var mapVal = null;
      if (!newValue) {
        mapVal = $scope.mapObj;
      }
      trafficLayer.setMap(mapVal);
    });

    var campaignData = {}; // Assuming an empty object initially, you may adjust this based on your data structure

    var campaignIdUrl = $location.$$url.split("/")[2];
    // Call the getSearchFilterProduct API using MapService
    if (campaignIdUrl) {
      if (exists == false && existsText == false) {
        if (campaignIdUrl == "search_criteria") {
          $scope.loading = true;
          $rootScope.isLoading = true;
          MapService.getSearchFilterProduct().then(function (campaignDataResponse) {
            handleCampaignData(campaignDataResponse);
            $scope.getDataResponse = campaignDataResponse;
            // console.log("durga",  $scope.getDataResponse);
            $scope.searchCriteriaName = campaignDataResponse[0].search_criteria_name;
          }).finally(function () {
            $rootScope.isLoading = false;
            $scope.loading = false;
          });
        } else {
          $location.path("/location/" + campaignIdUrl);
        }
      } else if (exists == true && existsText == false) {
        var resultArrays = campaignIdUrl.split("::");
        if (resultArrays.length !== 4) {
          $location.path("/report");
          toastr.error("Invalid Url search criteria");
        } else {
          $rootScope.isLoading = true;
          handleExistsCase(resultArrays);
        }
      } else if (exists == false && existsText == true) {
        var resultArrays = campaignIdUrl.split("::");
        if (resultArrays.length !== 2) {
          $location.path("/report");
          toastr.error("Invalid Url search criteria");
        } else {
          $rootScope.isLoading = true;
          handleExistsTextCase(resultArrays);
        }
      } else {
        $location.path("/report");
        toastr.error("Invalid Url search criteria");
      }
    }

    function handleCampaignData(campaignDataResponse) {
      if (!$mdSidenav("productList").isOpen()) {
        $mdSidenav("productList").toggle();
        $timeout(function () {
          $("#showRightPush").show();
        });
      }
      campaignData = campaignDataResponse;
      $scope.cpmLeft = parseFloat(campaignData[0].product_cpm);
      $scope.secondImpressionsLeft = Number(
        campaignData[0].product_impressions
      );
      $scope.booked_from = campaignData[0].product_startDate;
      $scope.booked_to = campaignData[0].product_endDate;
      $scope.radSearch.latitude = Number(campaignData[0].product_lat);
      $scope.radSearch.longitude = Number(campaignData[0].product_long);
      $scope.radSearch.radius = Number(campaignData[0].product_radius);
      $scope.selectedFormats =  [{ name: campaignData[0].product_type }];
      var filterObj = {
        area: $scope.selectedArea,
        product_type: $scope.selectedFormats.map(format => format.name),
        productId: $scope.search?.siteNo,
        impression: $scope.secondImpressionsLeft,
        cpm: $scope.cpmLeft,
        booked_from: $scope.booked_from,
        booked_to: $scope.booked_to,
        product_dma: campaignData[0]?.product_dma,
        product_height: campaignData[0].product_height.toString(),
        product_width: campaignData[0].product_width.toString(),
      };
      if (!$scope.isSearchDisabled) {
        filterObj.radiusSearch = $scope.radSearch;
        filterObj.latitude = $scope.radSearch.latitude;
        filterObj.longitude = $scope.radSearch.longitude;
      }
      handleFilterProducts(filterObj);
    }

    function handleExistsCase(resultArrays) {
      $timeout(function () {
        $mdSidenav("productList").toggle();
      });
      $scope.booked_from = resultArrays[2].split("-").reverse().join("-");
      $scope.booked_to = resultArrays[3].split("-").reverse().join("-");
      $scope.product_dma = resultArrays[1].replace(/%20/g, " ").trim();

      var filterObj = {
        booked_from: $scope.booked_from,
        booked_to: $scope.booked_to,
        product_dma: $scope.product_dma,
        product_type: ["Digital", "Digital/Static", "Static", "Media"],
      };
      handleFilterProducts(filterObj);
    }

    function handleExistsTextCase(resultArrays) {
      $timeout(function () {
        $mdSidenav("productList").toggle();
      });
      $scope.searchTerm = resultArrays[1];
      console.log("searchTerm", $scope.searchTerm);
      $scope.searchTermAry[0].searchTerm = $scope.searchTerm;
      mapProductsfiltered($scope.searchTerm, true);
    }

    function handleFilterProducts(filterObj) {
      $scope.plottingDone = false;
      $scope.loading = true;
      MapService.filterProducts(filterObj).then(function (markers) {
        _.each(markersOnMap, function (v, i) {
          v.setMap(null);
          $scope.Clusterer.removeMarker(v);
        });
        markersOnMap = Object.assign([]);
        $scope.filteredMarkers = markers;
        $scope.productmarkerslist = markers;
        if (markers.length > 0) {
          var bounds = new google.maps.LatLngBounds();
          _.each(markersOnMap, function (v, i) {
            bounds.extend(v.getPosition());
          });
          angular.forEach($scope.productmarkerslist, function (item) {
            const areaTimeZoneType = item.area_time_zone_type;
            angular.forEach(item.product_details, function (newItem) {
              try {
                var date = new Date().getTime();
                newItem.isExpired = true;
                if (
                  newItem.to_date &&
                  newItem.to_date.$date &&
                  newItem.to_date.$date.$numberLong &&
                  newItem.to_date.$date.$numberLong >= date
                ) {
                  newItem.isExpired = false;
                }
                const { startDate, endDate } = convertDateToMMDDYYYY(
                  newItem,
                  areaTimeZoneType
                );
                newItem["startDate"] = startDate;
                newItem["endDate"] = endDate;
                newItem["areaTimeZoneType"] = areaTimeZoneType;
              } catch (error) {
                console.log(error);
              }
            });
          });
        } else {
          toastr.error("no marker found for the criteria you selected");
        }
      });
    }

    $scope.applyFilter = function (booked_from, booked_to) {
      $scope.clearSearch("right");
      productList = [];
      locArr = [];
      uniqueMarkers = [];
      concentricMarkers = {};
      var filterObj = {
        area: $scope.selectedArea,
        product_type: $scope.selectedFormats,
        productId: $scope.search?.siteNo,
        impression: $scope.secondImpressionsLeft,
        cpm: $scope.cpmLeft,
        booked_from,
        booked_to,
      };
      if ($scope.currentShape) {
        $scope.currentShape.setMap(null);
        $scope.currentShape = null;
      }
      if (!$scope.isSearchDisabled) {
        filterObj.radiusSearch = $scope.radSearch;
      }
      $scope.plottingDone = false;
      MapService.filterProducts(filterObj).then(function (markers) {
        _.each(markersOnMap, function (v, i) {
          v.setMap(null);
          $scope.Clusterer.removeMarker(v);
        });
        markersOnMap = Object.assign([]);
        $scope.filteredMarkers = markers;
        $scope.productmarkerslist = markers;
        $scope.processMarkers();
        if (markers.length > 0) {
          var bounds = new google.maps.LatLngBounds();
          _.each(markersOnMap, function (v, i) {
            bounds.extend(v.getPosition());
          });
          angular.forEach($scope.productmarkerslist, function (item) {
            const areaTimeZoneType = item.area_time_zone_type;
            angular.forEach(item.product_details, function (newItem) {
              try {
                var date = new Date().getTime();
                newItem.isExpired = true;
                if (
                  newItem.to_date &&
                  newItem.to_date.$date &&
                  newItem.to_date.$date.$numberLong &&
                  newItem.to_date.$date.$numberLong >= date
                ) {
                  newItem.isExpired = false;
                }
                const { startDate, endDate } = convertDateToMMDDYYYY(
                  newItem,
                  areaTimeZoneType
                );
                newItem["startDate"] = startDate;
                newItem["endDate"] = endDate;
                newItem["areaTimeZoneType"] = areaTimeZoneType;
              } catch (error) {
                console.log(error);
              }
            });
          });
        } else {
          toastr.error("no marker found for the criteria you selected");
        }
      });
    };
    $scope.shortlistSelected = function (
      productId,
      selectedDateRanges,
      ev,
      quantity,
      areaTimeZoneType
    ) {
      selectedDateRanges.forEach(function (item) {
        const startDate = moment(item.startDate);
        const finalStartDate = moment(item.startDate).format("YYYY-MM-DD");
        const finalEndDate = moment(item.endDate).format("YYYY-MM-DD");
        if (areaTimeZoneType != null) {
          const offset = startDate.tz(areaTimeZoneType).format().slice(19, 25);
          const finalStartDate =
            moment(item.startDate).format("YYYY-MM-DD") +
            `T00:00:00.000${offset}`;
          const finalEndDate =
            moment(item.endDate).format("YYYY-MM-DD") +
            `T23:59:59.000${offset}`;
          item.startDate = moment.utc(finalStartDate).format();
          item.endDate = moment.utc(finalEndDate).format();
        } else {
          item.startDate = finalStartDate;
          item.endDate = finalEndDate;
        }
      });
      var sendObj = {
        product_id: productId,
        dates: selectedDateRanges,
        booked_slots: 1,
        newratecard: $scope.newratecard,
        quantity: quantity,
      };
      MapService.shortListProduct(sendObj).then(function (response) {
        if (response.status == 1) {
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
          setTimeout(() => {
            $mdDialog.hide();
          }, 2000);
          $scope.removeSelection();
          getShortListedProducts();
          $mdSidenav("productDetails").close();
          $scope.mapObj.setZoom(3);
          MapService.mapProductsfiltered().then(function (markers) {
            $scope.filteredMarkers = markers;
            NgMap.getMap().then(function (map) {
              $scope.mapObj = map;
              $scope.processMarkers();
              if (localStorage.areaFromHome) {
                setDefaultArea();
              }
              $scope.mapObj.addListener("zoom_changed", function () {
                $scope.selectedProduct = null;
                selectorMarker.setMap(null);
              });
            });
            $scope.actualDataCopy = markers;
            $scope.productmarkerslist = markers;
            // $mdSidenav('productList').toggle();
          });
        } else if (response.status == 0) {
          toastr.error(response.message);
        }
      });
    };

    function getShortListedProducts() {
      MapService.getshortListProduct(
        JSON.parse(localStorage.loggedInUser).id
      ).then(function (response) {
        shortListedProductsLength = response.shortlisted_products.length;
        $scope.shortListedProducts = response;
        $scope.shortListedProducts = response.shortlisted_products;
        $scope.shortListedTotal = response.shortlistedsum;
        $rootScope.$emit("shortListedProducts", shortListedProductsLength);
      });
    }
    getShortListedProducts();

    $scope.deleteShortlisted = function (ev, productId) {
      MapService.deleteShortlistedProduct(
        JSON.parse(localStorage.loggedInUser).id,
        productId
      ).then(function (response) {
        $mdDialog.show(
          $mdDialog
            .alert()
            .parent(angular.element(document.querySelector("body")))
            .clickOutsideToClose(true)
            .title("Cart Product")
            .textContent(response.message)
            .ariaLabel("delete-shortlisted")
            .ok("Confirmed!")
            .targetEvent(ev)
        );
        setTimeout(() => {
          $mdDialog.hide();
        }, 2000);
        getShortListedProducts();
      });
    };

    $scope.resetFilters = function () {
      productList = [];
      locArr = [];
      uniqueMarkers = [];
      concentricMarkers = {};
      $scope.selectedAreas = null;
      $scope.selectedcitys = null;
      $scope.selectedStates = null;
      $scope.selectedArea = null;
      $scope.circleRadius = null;
      $scope.plottingDone = false;
      _.each(markersOnMap, function (v, i) {
        v.setMap(null);
        $scope.Clusterer.removeMarker(v);
        delete v;
      });
      markersOnMap = [];
      MapService.markers().then(function (markers) {
        $scope.filteredMarkers = markers;
        $scope.processMarkers();
        var bounds = new google.maps.LatLngBounds();
        _.each(markersOnMap, function (v, i) {
          bounds.extend(v.getPosition());
        });
        $scope.mapObj.fitBounds(bounds);
      });
    };

    $scope.campaign = {};
    var startDate = new Date();
    var productFromDate = new Date($scope.campaign.start_date);
    var productToDate = new Date($scope.campaign.end_date);
    $scope.fromMinDate = new Date(
      startDate.getFullYear(),
      startDate.getMonth(),
      startDate.getDate() + 6
    );
    $scope.toMinDate = new Date(
      startDate.getFullYear(),
      startDate.getMonth(),
      startDate.getDate()
    );
    $scope.saveCampaign = function (product_id, selectedDateRanges) {
      if (product_id) {
        $scope.campaign.products = [];
        var sendObj = {
          product_id: product_id,
        };

        if (selectedDateRanges.length > 0) {
          sendObj.dates = selectedDateRanges;
        } else {
          toastr.error("Please select dates.");
          return false;
        }
        $scope.campaign.products.push(sendObj);
        $form = $scope.forms.mySaveCampaignForm;
      } else {
        if ($scope.shortListedProducts.length > 0) {
          $scope.loading = true;
          $scope.campaign.products = [];
          _.each($scope.shortListedProducts, function (v, i) {
            $scope.campaign.products.push(v.id);
          });
          $form = $scope.forms.viewAndSaveCampaignForm;
          $scope.loading = false;
        } else {
          toastr.error("Please shortlist some products first.");
        }
      }
      if ($scope.campaign.products) {
        CampaignService.saveUserCampaign($scope.campaign).then(function (
          response
        ) {
          if (response.status == 1) {
            //$scope.campaignSavedSuccessfully = true;
            $timeout(function () {
              $scope.campaign = {};
              $form.$setPristine();
              $form.$setUntouched();
              toastr.success(response.message);
              //$scope.campaignSavedSuccessfully = false;
            }, 3000);
            $scope.loadActiveUserCampaigns();
            getShortListedProducts();
          } else {
            $scope.saveUserCampaignErrors = response.message;
          }
        });
      }
    };

    $scope.emptyCampaign = {};
    $scope.createEmptyCampaign = function () {
      $scope.campaign.products = [];
      CampaignService.saveCampaign($scope.emptyCampaign).then(function (
        response
      ) {
        $scope.emptyCampaignSaved = true;
        $scope.emptyCampaign = {};
        $timeout(function () {
          $mdSidenav("createEmptyCampaignSidenav").close();
          $scope.emptyCampaignSaved = false;
        }, 3000);
        $scope.loadActiveUserCampaigns();
        getShortListedProducts();
      });
    };
    // Added this fn to clear the selected Results
    $scope.clearFields = function () {
      $scope.searchId = "";
      $scope.searchText = "";
      window.location.reload(true);
      //$route.reload();
    };

    // Select all

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
    $scope.checkAll = function () {
      if ($scope.selectAll) {
        $scope.loading = true;
        angular.forEach($scope.productmarkerslist, function (item) {
          angular.forEach(item.product_details, function (newItem) {
            idx = $scope.selected.indexOf(newItem);
            if (idx >= 0) {
              return true;
            } else if (!newItem.isExpired) {
              $scope.selected.push(newItem);
            }
          });
          $scope.loading = false;
        });
      } else {
        $scope.selected = [];
      }
    };
    $scope.details = [];
    var productlistArray = [];
    $scope.productlist = [];
    $scope.bulkShortlist = function (start, end, ev) {
      $scope.loading = true;
      $scope.details = $scope.selected;
      if ($scope.details.length <= 0) {
        toastr.error("Please Select Products");
      } else {
        angular.forEach($scope.details, function (productIds) {
          if (productIds.fix == "Fixed") {
            var oneDay = 24 * 60 * 60 * 1000;
            console.log(productIds);
            var fromtime = new Date(+productIds.from_date.$date.$numberLong);
            var totime = new Date(+productIds.to_date.$date.$numberLong);

            var dateDiff = Math.abs(fromtime - totime) / oneDay;
            var minimumdays = productIds.minimumdays;
            var newratecard =
              Math.ceil(dateDiff / minimumdays + 1) * productIds.rateCard;
            var productPerDay = productIds.rateCard / 28;
            var tempValue = productPerDay * productIds.minimumdays;
            var selectedTimes = Math.ceil(dateDiff / productIds.minimumdays);
            $scope.newratecard = selectedTimes * tempValue;
            productlistArray.push({
              id: productIds.id,
              newratecard: $scope.newratecard,
            });
          } else {
            productlistArray.push({ id: productIds.id, newratecard: 0 });
          }
          $scope.loading = false;
        });
        $scope.productlist = productlistArray;
        var sendObj = {
          product_id: $scope.productlist,
          dates: [
            {
              startDate: start,
              endDate: end,
            },
          ],
          booked_slots: 1,
        };
        MapService.shortListProduct(sendObj).then(function (response) {
          if (response.status == 1) {
            $scope.booked_from = null;
            $scope.booked_to = null;
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
            setTimeout(() => {
              $mdDialog.hide();
            }, 2000);
            $scope.removeSelection();
            getShortListedProducts();
            mapProductsfiltered();
            $mdSidenav("productDetails").close();
            // $scope.closedRightMenu();
            productlistArray = [];
            $scope.productlist = [];
            // MapService.mapProducts().then(function (markers) {
            //     $scope.filteredMarkers = markers;
            //     NgMap.getMap().then(function (map) {
            //         $scope.mapObj = map;
            //         $scope.processMarkers();
            //         if (localStorage.areaFromHome) {
            //             setDefaultArea();
            //         }
            //         $scope.mapObj.addListener('zoom_changed', function () {
            //             $scope.selectedProduct = null;
            //             selectorMarker.setMap(null);
            //         });
            //     });
            //     $scope.actualDataCopy = markers;
            //     $scope.productmarkerslist = markers;
            // });
          } else if (response.status == 0) {
            toastr.error(response.message);
          }
        });
      }
    };
    // Sellect All ends

    // $scope.closImag = function() {
    // var pre = document.getElementById("preview");
    // pre.style.backgroundImage = `url("")`;

    // console.log("heeloo")
    // }

    $scope.selectFromTabIdSearch = function (marker) {
      try {
        $scope.toggleProductDetailSidenav();
        if (marker.id) {
          /*
          var refToMapMarker = _.find(markersOnMap, (m) => {
            return m.properties.id == marker.id;
          });
          $scope.currentImage = "";
          $scope.currentImage =
            config.serverUrl + refToMapMarker.properties["image"];
          $scope.$parent.alreadyShortlisted = false;
          $scope.mapObj.setCenter(refToMapMarker.position);
          var bounds = new google.maps.LatLngBounds();
          bounds.extend(refToMapMarker.position);
          $scope.mapObj.fitBounds(bounds);

          $scope.product.id = refToMapMarker.properties["id"];
          $scope.product.image =
            config.serverUrl + marker["image"];
          $scope.product.area_name = refToMapMarker.properties["area_name"];
          

          $scope.product.title = refToMapMarker.properties["title"];
          $scope.product.siteNo = refToMapMarker.properties["siteNo"];
          $scope.product.type = marker["type"];
          $scope.product.address = refToMapMarker.properties["address"];
          $scope.product.city = marker["city"];
          $scope.product.state = refToMapMarker.properties["state"];

          $scope.product.zipcode = refToMapMarker.properties["zipcode"];
          $scope.product.height = refToMapMarker.properties["height"];
          $scope.product.length = refToMapMarker.properties["length"];
          $scope.product.width = refToMapMarker.properties["width"];
          $scope.product.panelSize = marker["panelSize"];
          $scope.product.minimumdays = marker["minimumdays"];
          $scope.product.audited = refToMapMarker.properties["audited"];
          $scope.product.sellerId = refToMapMarker.properties["sellerId"];
          $scope.product.isSellerIdExist =
            $scope.product.sellerId === "" ? false : true;
          $scope.product.unitQty = refToMapMarker.properties["unitQty"];
          $scope.product.mediahhi = refToMapMarker.properties["mediahhi"];
          $scope.product.isMediaHiExist =
            $scope.product.mediahhi === "" ? false : true;
          $scope.product.rateCard = marker['rateCard'];
          $scope.product.installCost = refToMapMarker.properties["installCost"];
          $scope.product.weekPeriod = refToMapMarker.properties["weekPeriod"];
          $scope.product.productioncost =
            refToMapMarker.properties["productioncost"];
          $scope.product.billing =
            refToMapMarker.properties["billingNo"] == "no" ? "No" : "Yes";
          $scope.product.servicing =
            refToMapMarker.properties["servicingNo"] == "no" ? "No" : "Yes";
          $scope.product.network = refToMapMarker.properties["network"];
          $scope.product.nationloc = refToMapMarker.properties["nationloc"];
          // $scope.product.fix = refToMapMarker.properties["fix"];
          $scope.product.fix = marker["fix"];
          $scope.product.genre = refToMapMarker.properties["genre"];
          $scope.product.daypart = refToMapMarker.properties["daypart"];
          $scope.product.firstImpression =
            refToMapMarker.properties["firstImpression"];
          $scope.product.isFirstImpExist =
            $scope.product.firstImpression === "" ? false : true;
          $scope.product.firstcpm = refToMapMarker.properties["firstcpm"];
          $scope.product.isFirstCpmExist =
            $scope.product.firstcpm === "" ? false : true;
          $scope.product.secondImpression =
            refToMapMarker.properties["secondImpression"];
          $scope.product.cpm = refToMapMarker.properties["cpm"];
          $scope.product.thirdImpression =
            refToMapMarker.properties["thirdImpression"];
          $scope.product.isThirdImpExist =
            $scope.product.thirdImpression === "" ? false : true;
          $scope.product.thirdcpm = refToMapMarker.properties["thirdcpm"];
          $scope.product.isThirdCpmExist =
            $scope.product.thirdcpm === "" ? false : true;
          $scope.product.forthImpression =
            refToMapMarker.properties["forthImpression"];
          $scope.product.isForthImpExist =
            $scope.product.forthImpression === "" ? false : true;
          $scope.product.forthcpm = refToMapMarker.properties["forthcpm"];
          $scope.product.isForthCpmExist =
            $scope.product.forthcpm === "" ? false : true;
          $scope.product.imgdirection =
            refToMapMarker.properties["imgdirection"];
          $scope.product.cancellation_policy =
            refToMapMarker.properties["cancellation_policy"];
          $scope.product.isCanelExist =
            $scope.product.cancellation_policy === "" ? false : true;
          $scope.product.notes = refToMapMarker.properties["notes"];
          $scope.product.isNotesExist =
            $scope.product.notes === "" ? false : true;
          $scope.product.description = refToMapMarker.properties["description"];
          $scope.product.direction = refToMapMarker.properties["direction"];
          $scope.product.lat = marker.lat;
          $scope.product.lng = marker.lng;
          $scope.product.placement = refToMapMarker.properties["placement"];
          $scope.product.isPlacementExist =
            $scope.product.placement === "" ? false : true;
          $scope.product.spotLength = refToMapMarker.properties["spotLength"];
          $scope.product.isSpotLengthExist =
            $scope.product.spotLength === "" ? false : true;
          $scope.product.fliplength = refToMapMarker.properties["fliplength"];
          $scope.product.ageloopLength =
            refToMapMarker.properties["ageloopLength"];
          $scope.product.isLoopLengthExist =
            $scope.product.looplength === "" ? false : true;
          $scope.product.staticMotion =
            refToMapMarker.properties["staticMotion"];
          // $scope.product.isStaticMotionExist = $scope.product.staticMotion == $scope.product.type.name != 'Static';
          $scope.product.sound = refToMapMarker.properties["sound"];
          $scope.product.medium = refToMapMarker.properties["medium"];
          $scope.product.isMediumExist =
            $scope.product.medium === "" ? false : true;
          $scope.product.product_newMedia =
            refToMapMarker.properties["product_newMedia"];
          $scope.product.file_type = refToMapMarker.properties["file_type"];
          $scope.product.isFileTypeExist =
            $scope.product.file_type === "" ? false : true;
          $scope.product.locationDesc =
            refToMapMarker.properties["locationDesc"];
          $scope.product.isLocationDescExist =
            $scope.product.locationDesc === "" ? false : true;
          $scope.product.lighting = refToMapMarker.properties["lighting"];      
          $scope.product.Comments = refToMapMarker.properties["Comments"];
          $scope.product.isCommentsExist =
            $scope.product.Comments === "" ? false : true;
          
         
          $scope.product.negotiatedCost =
            refToMapMarker.properties["negotiatedCost"];
          $scope.product.vendor = refToMapMarker.properties["vendor"];
          $scope.product.strengths = refToMapMarker.properties["strengths"];
          // $scope.product.demographicsage = refToMapMarker.properties['demographicsage'];
          // $scope.product.isDescExist = $scope.product.demographicsage === ""? false:true;
          // $scope.product.rateCard = refToMapMarker.properties["rateCard"];
          $scope.product.venue = refToMapMarker.properties["venue"];
          $scope.product.availableDates =
            refToMapMarker.properties["availableDates"];
          $scope.product.slots = refToMapMarker.properties["slots"];
         
        
  
          $scope.product.isProductNewAgeExist =
            $scope.product.product_newAge === "" ? false : true;
         
          $scope.product.minimumbooking =
            refToMapMarker.properties["minimumbooking"];
         
          // $scope.product.minimumdays = refToMapMarker.properties["minimumdays"];
          
          $scope.product.reach = refToMapMarker.properties["reach"];
          // $scope.product.costperpoint = refToMapMarker.properties['costperpoint'];

         */
          var refToMapMarker = _.find(markersOnMap, (m) => {
            return m.properties.id == marker.id;
          });

          $scope.mapObj.setCenter(refToMapMarker.position);
          var bounds = new google.maps.LatLngBounds();
          bounds.extend(refToMapMarker.position);
          $scope.mapObj.fitBounds(bounds);

          $scope.product.id = marker["id"];
          $scope.product.image = config.serverUrl + marker["image"];
          $scope.product.title = marker["title"];
          $scope.product.siteNo = marker["siteNo"];
          $scope.product.type = marker["type"];
          $scope.product.address = marker["address"];
          $scope.product.city = marker["city"];
          $scope.product.state = marker["state"];
          $scope.product.zipcode = marker["zipcode"];
          $scope.product.height = marker["height"];
          $scope.product.length = marker["length"];
          $scope.product.width = marker["width"];
          $scope.product.panelSize = marker["panelSize"];
          $scope.product.minimumdays = marker["minimumdays"];
          $scope.product.audited = marker["audited"];
          $scope.product.sellerId = marker["sellerId"];
          $scope.product.isSellerIdExist =
            $scope.product.sellerId === "" ? false : true;
          $scope.product.unitQty = marker["unitQty"];
          $scope.product.mediahhi = marker["mediahhi"];
          $scope.product.isMediaHiExist =
            $scope.product.mediahhi === "" ? false : true;
          $scope.product.rateCard = marker["rateCard"];
          $scope.product.installCost = marker["installCost"];
          $scope.product.weekPeriod = marker["weekPeriod"];
          $scope.product.productioncost = marker["productioncost"];
          $scope.product.billing = marker["billingNo"] == "no" ? "No" : "Yes";
          $scope.product.servicing =
            marker["servicingNo"] == "no" ? "No" : "Yes";
          $scope.product.network = marker["network"];
          $scope.product.nationloc = marker["nationloc"];
          $scope.product.fix = marker["fix"];
          $scope.product.genre = marker["genre"];
          $scope.product.daypart = marker["daypart"];
          $scope.product.firstImpression = marker["firstImpression"];
          $scope.product.isFirstImpExist =
            $scope.product.firstImpression === "" ? false : true;
          $scope.product.firstcpm = marker["firstcpm"];
          $scope.product.isFirstCpmExist =
            $scope.product.firstcpm === "" ? false : true;
          $scope.product.secondImpression = marker["secondImpression"];
          $scope.product.cpm = marker["cpm"];
          $scope.product.thirdImpression = marker["thirdImpression"];
          $scope.product.isThirdImpExist =
            $scope.product.thirdImpression === "" ? false : true;
          $scope.product.thirdcpm = marker["thirdcpm"];
          $scope.product.isThirdCpmExist =
            $scope.product.thirdcpm === "" ? false : true;
          $scope.product.forthImpression = marker["forthImpression"];
          $scope.product.isForthImpExist =
            $scope.product.forthImpression === "" ? false : true;
          $scope.product.forthcpm = marker["forthcpm"];
          $scope.product.isForthCpmExist =
            $scope.product.forthcpm === "" ? false : true;
          $scope.product.imgdirection = marker["imgdirection"];
          $scope.product.cancellation_policy = marker["cancellation_policy"];
          $scope.product.cancellation_terms = marker["cancellation_terms"];
          $scope.product.isCanelExist =
            $scope.product.cancellation_policy === "" ? false : true;
          $scope.product.notes = marker["notes"];
          $scope.product.isNotesExist =
            $scope.product.notes === "" ? false : true;
          $scope.product.description = marker["description"];
          $scope.product.direction = marker["direction"];
          $scope.product.lat = marker.lat;
          $scope.product.lng = marker.lng;
          $scope.product.placement = marker["placement"];
          $scope.product.isPlacementExist =
            $scope.product.placement === "" ? false : true;
          $scope.product.spotLength = marker["spotLength"];
          $scope.product.isSpotLengthExist =
            $scope.product.spotLength === "" ? false : true;
          $scope.product.fliplength = marker["fliplength"];
          $scope.product.ageloopLength = marker["ageloopLength"];
          $scope.product.isLoopLengthExist =
            $scope.product.looplength === "" ? false : true;
          $scope.product.staticMotion = marker["staticMotion"];
          $scope.product.sound = marker["sound"];
          $scope.product.medium = marker["medium"];
          $scope.product.isMediumExist =
            $scope.product.medium === "" ? false : true;
          $scope.product.product_newMedia = marker["product_newMedia"];
          $scope.product.file_type = marker["file_type"];
          $scope.product.isFileTypeExist =
            $scope.product.file_type === "" ? false : true;
          $scope.product.locationDesc = marker["locationDesc"];
          $scope.product.isLocationDescExist =
            $scope.product.locationDesc === "" ? false : true;
          $scope.product.lighting = marker["lighting"];
          $scope.product.Comments = marker["Comments"];
          $scope.product.isCommentsExist =
            $scope.product.Comments === "" ? false : true;

          /*
          var fromtime = marker.from_date.$date.$numberLong;

          $scope.product.originalFromTime = moment(new Date(+fromtime)).format(
            "YYYY-MM-DD"
          );
          
          $scope.product.fromTime = moment(new Date(+fromtime)).format(
            "YYYY-MM-DD"
          );

          const today = moment(new Date()).format(
            "YYYY-MM-DD"
          );

          if ($scope.product.fromTime < today) {
            $scope.product.fromTime = today;
          }

          var endTime = marker.to_date.$date.$numberLong;
          $scope.product.endTime = moment(new Date(+endTime)).format(
            "YYYY-MM-DD"
          );
          */

          $scope.product.originalFromTime = marker.startDate;
          $scope.product.originalEndTime = marker.endDate;
          $scope.product.areaTimeZoneType = marker.areaTimeZoneType;

          if ($scope.product.areaTimeZoneType != null) {
            const { startDate, endDate } = convertDateToYYYYMMDD(
              marker,
              marker.areaTimeZoneType
            );
            $scope.product.fromTime = startDate;
            const today = moment()
              .tz(marker.areaTimeZoneType)
              .format("YYYY-MM-DD");
            if ($scope.product.fromTime < today) {
              $scope.product.fromTime = today;
            }
            $scope.product.endTime = endDate;
          } else {
            var fromtime = marker.from_date.$date.$numberLong;

            $scope.product.originalFromTime = moment(
              new Date(+fromtime)
            ).format("YYYY-MM-DD");

            $scope.product.fromTime = moment(new Date(+fromtime)).format(
              "YYYY-MM-DD"
            );

            const today = moment(new Date()).format("YYYY-MM-DD");

            if ($scope.product.fromTime < today) {
              $scope.product.fromTime = today;
            }

            var endTime = marker.to_date.$date.$numberLong;
            $scope.product.endTime = moment(new Date(+endTime)).format(
              "YYYY-MM-DD"
            );
          }

          $scope.hideSelectedMarkerDetail = false;
          $mdSidenav("productDetails").open();
          $scope.selectedProduct = refToMapMarker;
        } else {
          toastr.error("No product found with that tab id", "error");
        }
      } catch (ex) {
        console.log("SelectFromTabSearch: Exception: " + ex.message);
      }
    };

    $scope.selectFromImpressionSearch = function (marker) {
      $scope.toggleProductDetailSidenav();
      if (marker.id) {
        var refToMapMarker = _.find(markersOnMap, (m) => {
          return m.properties.id == marker.id;
        });
        $scope.$parent.alreadyShortlisted = false;
        $scope.mapObj.setCenter(refToMapMarker.position);
        var bounds = new google.maps.LatLngBounds();
        bounds.extend(refToMapMarker.position);
        $scope.mapObj.fitBounds(bounds);
        $scope.product.id = refToMapMarker.properties["id"];
        $scope.product.image =
          config.serverUrl + refToMapMarker.properties["image"];
        $scope.product.siteNo = refToMapMarker.properties["siteNo"];
        $scope.product.area_name = refToMapMarker.properties["area_name"];
        $scope.product.panelSize = refToMapMarker.properties["panelSize"];
        $scope.product.vendor = refToMapMarker.properties["vendor"];
        $scope.product.notes = refToMapMarker.properties["notes"];
        $scope.product.strengths = refToMapMarker.properties["strengths"];
        $scope.product.address = refToMapMarker.properties["address"];
        $scope.product.city = refToMapMarker.properties["city"];
        $scope.product.zipcode = refToMapMarker.properties["zipcode"];
        $scope.product.title = refToMapMarker.properties["title"];
        $scope.product.mediahhi = refToMapMarker.properties["mediahhi"];
        $scope.product.secondImpression =
          refToMapMarker.properties["secondImpression"];
        $scope.product.firstImpression =
          refToMapMarker.properties["firstImpression"];
        $scope.product.thirdImpression =
          refToMapMarker.properties["thirdImpression"];
        $scope.product.forthImpression =
          refToMapMarker.properties["forthImpression"];
        $scope.product.productioncost =
          refToMapMarker.properties["productioncost"];
        $scope.product.negotiatedCost =
          refToMapMarker.properties["negotiatedCost"];
        $scope.product.fliplength = refToMapMarker.properties["fliplength"];
        $scope.product.looplength = refToMapMarker.properties["looplength"];
        $scope.product.installCost = refToMapMarker.properties["installCost"];
        $scope.product.staticMotion = refToMapMarker.properties["staticMotion"];
        $scope.product.sound = refToMapMarker.properties["sound"];
        $scope.product.lat = refToMapMarker.properties["lat"];
        $scope.product.lng = refToMapMarker.properties["lng"];
        $scope.product.locationDesc = refToMapMarker.properties["locationDesc"];
        $scope.product.cpm = refToMapMarker.properties["cpm"];
        $scope.product.firstcpm = refToMapMarker.properties["firsstcpm"];
        $scope.product.thirdcpm = refToMapMarker.properties["thirdcpm"];
        $scope.product.forthcpm = refToMapMarker.properties["forthcpm"];
        $scope.product.sellerId = refToMapMarker.properties["sellerId"];
        $scope.product.type = refToMapMarker.properties["type"];
        $scope.product.weekPeriod = refToMapMarker.properties["weekPeriod"];
        $scope.product.rateCard = refToMapMarker.properties["rateCard"];
        $scope.product.venue = refToMapMarker.properties["venue"];
        $scope.product.lighting = refToMapMarker.properties["lighting"];
        $scope.product.direction = refToMapMarker.properties["direction"];
        $scope.product.Comments = refToMapMarker.properties["Comments"];
        $scope.product.audited = refToMapMarker.properties["audited"];
        $scope.product.availableDates =
          refToMapMarker.properties["availableDates"];
        $scope.product.slots = refToMapMarker.properties["slots"];
        $scope.product.negotiatedCost =
          refToMapMarker.properties["negotiatedCost"];
        $scope.product.minimumbooking =
          refToMapMarker.properties["minimumbooking"];
        $scope.hideSelectedMarkerDetail = false;
        $mdSidenav("productDetails").open();
        $scope.selectedProduct = refToMapMarker;
      } else {
        toastr.error("No product found with that tab id", "error");
      }
    };
    $scope.selectFromCPMSearch = function (marker) {
      $scope.toggleProductDetailSidenav();
      if (marker.id) {
        var refToMapMarker = _.find(markersOnMap, (m) => {
          return m.properties.id == marker.id;
        });
        $scope.$parent.alreadyShortlisted = false;
        $scope.mapObj.setCenter(refToMapMarker.position);
        var bounds = new google.maps.LatLngBounds();
        bounds.extend(refToMapMarker.position);
        $scope.mapObj.fitBounds(bounds);
        $scope.product.id = refToMapMarker.properties["id"];
        $scope.product.image =
          config.serverUrl + refToMapMarker.properties["image"];
        $scope.product.siteNo = refToMapMarker.properties["siteNo"];
        $scope.product.area_name = refToMapMarker.properties["area_name"];
        $scope.product.panelSize = refToMapMarker.properties["panelSize"];
        $scope.product.zipcode = refToMapMarker.properties["zipcode"];
        $scope.product.vendor = refToMapMarker.properties["vendor"];
        $scope.product.notes = refToMapMarker.properties["notes"];
        $scope.product.mediahhi = refToMapMarker.properties["mediahhi"];
        $scope.product.lat = refToMapMarker.properties["lat"];
        $scope.product.lng = refToMapMarker.properties["lng"];
        $scope.product.negotiatedCost =
          refToMapMarker.properties["negotiatedCost"];
        $scope.product.fliplength = refToMapMarker.properties["fliplength"];
        $scope.product.looplength = refToMapMarker.properties["looplength"];
        $scope.product.staticMotion = refToMapMarker.properties["staticMotion"];
        roduct.sound = refToMapMarker.properties["sound"];
        $scope.product.title = refToMapMarker.properties["title"];
        $scope.product.strengths = refToMapMarker.properties["strengths"];
        $scope.product.Comments = refToMapMarker.properties["Comments"];
        $scope.product.address = refToMapMarker.properties["address"];
        $scope.product.city = refToMapMarker.properties["city"];
        $scope.product.audited = refToMapMarker.properties["audited"];
        $scope.product.productioncost =
          refToMapMarker.properties["productioncost"];
        $scope.product.secondImpression =
          refToMapMarker.properties["secondImpression"];
        $scope.product.firstImpression =
          refToMapMarker.properties["firstImpression"];
        $scope.product.thirdImpression =
          refToMapMarker.properties["thirdImpression"];
        $scope.product.forthImpression =
          refToMapMarker.properties["forthImpression"];
        $scope.product.installCost = refToMapMarker.properties["installCost"];
        $scope.product.locationDesc = refToMapMarker.properties["locationDesc"];
        $scope.product.cpm = refToMapMarker.properties["cpm"];
        $scope.product.firstcpm = refToMapMarker.properties["firsstcpm"];
        $scope.product.thirdcpm = refToMapMarker.properties["thirdcpm"];
        $scope.product.forthcpm = refToMapMarker.properties["forthcpm"];
        $scope.product.sellerId = refToMapMarker.properties["sellerId"];
        $scope.product.type = refToMapMarker.properties["type"];
        $scope.product.weekPeriod = refToMapMarker.properties["weekPeriod"];
        $scope.product.rateCard = refToMapMarker.properties["rateCard"];
        $scope.product.venue = refToMapMarker.properties["venue"];
        $scope.product.lighting = refToMapMarker.properties["lighting"];
        $scope.product.direction = refToMapMarker.properties["direction"];
        $scope.product.description = refToMapMarker.properties["description"];
        $scope.product.availableDates =
          refToMapMarker.properties["availableDates"];
        $scope.product.slots = refToMapMarker.properties["slots"];
        $scope.product.minimumbooking =
          refToMapMarker.properties["minimumbooking"];
        $scope.hideSelectedMarkerDetail = false;
        $mdSidenav("productDetails").open();
        $scope.selectedProduct = refToMapMarker;
      } else {
        toastr.error("No product found with that tab id", "error");
      }
    };
    $scope.ListMapView = true;
    $scope.openRightMenu = function () {
      $scope.ListMapView = true;
      $mdSidenav("productList").toggle();
      $("#showRightPush").removeClass("right-cls2");
      $("#showRightPush").addClass("left-cls2");
    };
    $scope.closedRightMenu = function () {
      $scope.ListMapView = false;
      $mdSidenav("productList").toggle();
      $("#showRightPush").removeClass("left-cls2");
      $("#showRightPush").addClass("right-cls2");
    };
    $scope.switchViews = function () {
      if ($("#showRightPush").hasClass("left-cls2")) {
        $scope.ListMapView = false;
        $("#showRightPush").removeClass("left-cls2");
        $("#showRightPush").addClass("right-cls2");
      } else {
        $scope.ListMapView = true;
        $("#showRightPush").removeClass("right-cls2");
        $("#showRightPush").addClass("left-cls2");
      }
      if ($("#productListSideNavDiv").hasClass("active_one")) {
        $("#productListSideNavDiv").removeClass("active_one");
        $("#productListSideNavDiv").addClass("active_open");
      } else {
        $("#productListSideNavDiv").removeClass("active_open");
        $("#productListSideNavDiv").addClass("active_one");
      }

      //$scope.isMobileView = !$scope.isMobileView;
    };

    //  $scope.openLeftMenu = function() {
    //     $mdSidenav('productList').toggle();
    //  };
    $scope.activeUserCampaigns = [];
    $scope.loadActiveUserCampaigns = function () {
      CampaignService.getActiveUserCampaigns().then(function (result) {
        $scope.activeUserCampaigns = result.filter(function (item) {
          if (item.status == 100) {
            return true;
          }
        });
      });
    };
    $scope.loadActiveUserCampaigns();

    $scope.deleteUserCampaign = function (campaignId) {
      CampaignService.deleteCampaign(campaignId).then(function (result) {
        if (result.status == 1) {
          $scope.loadActiveUserCampaigns();
          toastr.success(result.message);
        } else {
          toastr.error(result.message);
        }
      });
    };
    // $scope.searchForProduct = function (value){
    //     console.log(value,'Valueeee')
    // }
    $scope.toggleFormatSelection = function (formatId, booked_from, booked_to) {
      if (_.contains($scope.selectedFormats, formatId)) {
        $scope.selectedFormats = _.reject($scope.selectedFormats, function (v) {
          return v == formatId;
        });
      } else {
        $scope.selectedFormats.push(formatId);
      }
      $scope.applyFilter(booked_from, booked_to);
    };

    $scope.isFormatSelected = function (formatId) {
      return _.contains($scope.selectedFormats, formatId);
    };
    // $scope.isDateSelected = function (dates) {
    //     return _.contains($scope.filterDates, dates);
    // }

    $scope.toggleTrafficLegends = function () {
      $scope.showTrafficLegend = !$scope.showTrafficLegend;
    };

    rangeCircle = new google.maps.Circle({
      strokeColor: "#ea3b37",
      strokeOpacity: 1.0,
      strokeWeight: 1.5,
      // fillColor: "#0000ff",
      fillOpacity: 0.0,
    });

    $scope.range = {};
    $scope.range.circleRadius = 0;
    $scope.updateCircle = function () {
      rangeCircle.setMap(null);
      rangeCircle.setRadius(Math.sqrt(($scope.circleRadius * 1000) / Math.PI));
      rangeCircle.setCenter({
        lat: Number($scope.selectedArea.lat),
        lng: Number($scope.selectedArea.lng),
      });
      rangeCircle.setMap($scope.mapObj);
      $scope.mapObj.fitBounds(rangeCircle.getBounds());
    };
    // Drawing a circle ends

    $scope.viewCampaignDetails = function (campaignId) {
      CampaignService.getCampaignWithProducts(campaignId).then(function (
        campaignDetails
      ) {
        $scope.campaignDetails = campaignDetails;
        $scope.$parent.alreadyShortlisted = true;
        // $scope.toggleCampaignDetailSidenav();
      });
    };

    var updateCampaignDetailSidenav = function (campaignId) {
      CampaignService.getCampaignWithProducts(campaignId).then(function (
        campaignDetails
      ) {
        $scope.campaignDetails = campaignDetails;
      });
    };

    $scope.customOptions = {};

    $scope.removeSelection = function () {
      $scope.customOptions.clearSelection();
    };

    $scope.$on("removeSelection", function () {
      $scope.removeSelection();
    });
    $scope.addProductToExistingCampaign = function (
      existingCampaignId,
      productId
    ) {
      var productToCampaign = {
        product_id: productId,
        campaign_id: existingCampaignId,
        dates: [],
      };
      $scope.ranges.selectedDateRanges.forEach((item, index) => {
        productToCampaign.dates.push({
          startDate: moment(item.startDate).format("YYYY-MM-DD"),
          endDate: moment(item.endDate).format("YYYY-MM-DD"),
        });
      });
      CampaignService.addProductToExistingCampaign(productToCampaign).then(
        function (result) {
          if (result.status == 1) {
            //$scope.existingCampaign.id = null;
            toastr.success(result.message);
            // $mdSidenav('productDetails').close();
            $scope.toggleExistingCampaignSidenav();
            $scope.toggleProductDetailSidenav();
            $window.location.href =
              "/campaign-details/" + existingCampaignId + "/1";
          } else if (result.status == 0) {
            toastr.error(result.message);
          }
        }
      );
      // $scope.toggleProductDetailSidenav();
    };

    $scope.shareShortlistedProducts = function (shareShortlisted) {
      var sendObj = {
        email: shareShortlisted.email,
        receiver_name: shareShortlisted.name,
      };
      CampaignService.shareShortListedProducts(sendObj).then(function (result) {
        if (result.status == 1) {
          toastr.success(result.message);
          $mdSidenav("shortlistSharingSidenav").close();
        } else {
          toastr.error(result.message);
        }
      });
    };

    $scope.shareCampaign = function (ev, shareCampaign) {
      var campaignToEmail = {
        campaign_id: $scope.campaignToShare.id,
        email: shareCampaign.email,
        receiver_name: shareCampaign.receiver_name,
        campaign_type: $scope.campaignToShare.type,
      };
      CampaignService.shareCampaignToEmail(campaignToEmail).then(function (
        result
      ) {
        if (result.status == 1) {
          $mdSidenav("shareCampaign").close();
          $mdDialog.show(
            $mdDialog
              .alert()
              .parent(angular.element(document.querySelector("body")))
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
      });
    };

    $scope.viewProduct = function (product) {
      $scope.product.image = config.serverUrl + product.image;
      $scope.product.siteNo = product.siteNo;
      $scope.product.panelSize = product.panelSize;
      $scope.product.type = product.type;
      $scope.product.vendor = product.vendor;
      $scope.product.address = product.address;
      $scope.product.area_name = product.area_name;
      $scope.product.address = product.address;
      $scope.product.rateCard = product.rateCard;
      $scope.product.weekPeriod = product.weekPeriod;
      $scope.product.secondImpression = product.secondImpression;
      $scope.product.firstImpression = product.firstImpression;
      $scope.product.thirdImpression = product.thirdImpression;
      $scope.product.forthImpression = product.forthImpression;
      $scope.product.cpm = product.cpm;
      $scope.product.firstcpm = product.firsstcpm;
      $scope.product.thirdcpm = product.thirdcpm;
      $scope.product.forthcpm = product.forthcpm;
      $scope.product.sellerId = product.sellerId;
      $scope.product.mediahhi = product.mediahhi;
      $scope.product.installCost = product.installCost;
      $scope.product.direction = product.direction;
      $scope.product.lighting = product.lighting;
      $scope.product.zipcode = product.zipcode;
      $scope.product.lat = product.lat;
      $scope.product.lng = product.lng;
      $scope.product.title = product.title;
      $scope.product.notes = product.notes;
      $scope.product.Comments = product.Comments;
      $scope.product.negotiatedCost = product.negotiatedCost;
      $scope.product.audited = product.audited;
      $scope.product.locationDesc = product.locationDesc;
      $scope.product.staticMotion = product.staticMotion;
      $scope.product.sound = product.sound;
      $scope.product.looplength = product.looplength;
      $scope.product.fliplength = product.fliplength;
      $scope.product.availableDates = product.availableDates;
      $scope.product.productioncost = product.productioncost;
      $scope.hideSelectedMarkerDetail = false;
      // if (product.type == "Bulletin") {
      //     $mdSidenav('productDetails').toggle();
      // } else if (product.type == "Digital Bulletin" || product.type == "Transit") {
      //     $mdSidenav('digitalProductDetails').toggle();
      // }
      $mdSidenav("productDetails").toggle();
      $scope.product.id = product.id;
    };

    $scope.deleteProductFromCampaign = function (productId, campaignId) {
      CampaignService.deleteProductFromUserCampaign(campaignId, productId).then(
        function (result) {
          if (result.status == 1) {
            toastr.success(result.message);
            updateCampaignDetailSidenav(campaignId);
          } else {
            toastr.error(result.message);
          }
        }
      );
    };

    $scope.autoCompleteArea = function (query) {
      return LocationService.getAreasWithAutocomplete(query);
    };

    $scope.searchByTabId = function (query) {
      return MapService.searchBySiteNo(query);
    };
    $scope.searchBySecondImpression = function (query) {
      return MapService.searchBySecondImpression(query);
    };
    $scope.searchByCpm = function (query) {
      return MapService.searchByCpm(query);
    };
    $scope.selectedAreaChanged = function (area) {
      $scope.selectedArea = area;
      if (area) {
        $scope.mapObj.setCenter({
          lat: Number(area.lat),
          lng: Number(area.lng),
        });
        var bounds = new google.maps.LatLngBounds();
        bounds.extend({
          lat: Number(area.lat),
          lng: Number(area.lng),
        });
        $scope.mapObj.fitBounds(bounds);
      }
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
          updateCampaignDetailSidenav(campaignId);
        } else {
          toastr.error(result.message);
        }
        $scope.loadActiveUserCampaigns();
      });
    };

    if ($rootScope.currStateName == "index.campaign-details") {
      $scope.viewCampaignDetails(localStorage.activeUserCampaignId);
    }

    // sets the height of the div containing the map.
    function setMapContainerHeight() {
      var windowHeight =
        window.innerHeight ||
        document.documentElement.clientHeight ||
        document.body.clientHeight; //getting windows height
      if (windowHeight < 600) {
        document.querySelector("#map-container").style["height"] =
          windowHeight - 100 + "px";
        // $('#map-container').css('height', height-100+'px');
      } else {
        document.querySelector("#map-container").style["height"] =
          windowHeight - 64 + "px"; //and setting height of map container
      }
      $scope.mapContainerHeightSet = true;
    }
    setMapContainerHeight();

    $scope.elipsis = "";
    var productLoader = function () {
      if (!$scope.filteredMarkers) {
        setTimeout(productLoader, 500);
      }
      if ($scope.elipsis == "...") {
        $scope.elipsis = "";
      }
      $scope.elipsis += ".";
    };
    productLoader();

    $scope.getProductDigitalUnavailableDates = function (
      productId,
      productSlots
    ) {
      digitalSlots = productSlots;
      $scope.digitalSlots = [];
      MapService.getProductDigitalUnavailableDates(productId).then(function (
        blockedDatesAndSlots
      ) {
        $scope.unavailalbeDateRanges = blockedDatesAndSlots;
        productDatesDigitalCalculator();
      });
    };

    $scope.getProductUnavailableDates = function (product, ev) {
      // if(product.type == "Bulletin"){
      MapService.getProductUnavailableDates(product.id).then(function (
        dateRanges
      ) {
        $scope.unavailalbeDateRanges = dateRanges;
        localStorage.setItem("mindays", $scope.product.minimumdays);
        localStorage.setItem("fxed", $scope.product.fix);
        localStorage.setItem(
          "selectedDateRanges",
          JSON.stringify($scope.ranges.selectedDateRanges)
        );
        if (
          $scope.ranges &&
          $scope.ranges.selectedDateRanges &&
          $scope.ranges.selectedDateRanges.length
        ) {
          $scope.ranges.selectedDateRanges.every((selectedDateRange) => {
            const daysCount =
              parseInt(
                (new Date(selectedDateRange.endDate) -
                  new Date(selectedDateRange.startDate)) /
                  (1000 * 60 * 60 * 24),
                10
              ) + 1;
            if (daysCount < $scope.product.minimumdays) {
              // $('.warn-text').text(`GMAP_CTRL Please select minimum ${$scope.product.minimumdays} days.
              //   Even though you are selecting ${daysCount} days we are charging for ${$scope.product.minimumdays} days`);
              return false;
            }
          });
        }
        $(ev.target).parents().eq(3).find("input").trigger("click"); // Open Calendar
      });
      // }else{
      //     MapService.getProductDigitalUnavailableDates(product.id).then(function (blockedDatesAndSlots) {
      //         $scope.unavailalbeDateRanges = [];
      //         blockedDatesAndSlots.forEach((item)=>{
      //             if(item.booked_slots >= $scope.product.slots){
      //                 $scope.unavailalbeDateRanges.push(item);
      //             }
      //         })
      //         $(ev.target).parents().eq(3).find('input').trigger('click') ;
      //     })
      // }
    };

    $scope.getProductUnavailableDatesautoload = function (productId) {
      MapService.getProductUnavailableDates(productId).then(function (
        dateRanges
      ) {
        $scope.unavailalbeDateRanges = dateRanges;
        localStorage.setItem(
          "selectedDateRanges",
          JSON.stringify($scope.ranges.selectedDateRanges)
        );
        $("#calender-autolaod-div")
          .parents()
          .eq(3)
          .find("input")
          .trigger("click");
      });
    };

    // Product-Controller
    ProductService.getFormatList().then(function (formats) {
      // $scope.formatList = formats;
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
    $scope.FormatData = function (selectedZone) {
      $scope.productmarkerslist = $scope.actualDataCopy.filter(function (item) {
        return item.product_details[0].format_name === selectedZone;
      });
    };
    $scope.resetData = function () {
      $scope.productmarkerslist = $scope.actualDataCopy;
      $scope.siteNo = "";
      $scope.area_name = "";
    };
    $scope.getproddata = function (proddetails) {
      $scope.productListDetails = proddetails;
      // if (proddetails.type == "Bulletin") {
      //     $mdSidenav('productDetails').toggle();
      // } else if (proddetails.type == "Digital Bulletin" || proddetails.type == "Transit") {
      //     $mdSidenav('digitalProductDetails').toggle();
      // }

      $mdSidenav("productDetails").toggle();
    };
    $scope.formats = function () {
      $scope.filter = false;
      $scope.format = !$scope.format;
      $scope.shortlist = false;
      $scope.savedcampaign = false;
    };

    /*---Radius Search---*/
    $scope.radSearch = {
      latitude: "",
      longitude: "",
      radius: "",
    };
    $scope.isSearchDisabled = true;
    $scope.radSearchHanddler = function (booked_from, booked_to) {
      $scope.applyFilter(booked_from, booked_to, $scope.radSearch);
    };
    $scope.changeHandler = function () {
      console.log("inside");
      $scope.isSearchDisabled = false;
      for (let x in $scope.radSearch) {
        if (!$scope.radSearch[x]) {
          $scope.isSearchDisabled = true;
          break;
        }
      }
    };
    /*//---Radius Search---*/

    $scope.isLeftSearch = false;
    /*--- Left Search Handler ---*/
    $scope.leftSearch = function (booked_from, booked_to) {
      $scope.isLeftSearch = true;
      $scope.applyFilter(booked_from, booked_to);
    };
    /*--- End of Left Search Handler ---*/

    /*--- Clear Search ---*/
    $scope.clearSearch = function (clear) {
      if (clear === "right") {
        $scope.searchTermAry = [{ searchTerm: "" }];
      } else {
        if ($scope.search?.siteNo) {
          $scope.search.siteNo = null;
        }
        $scope.secondImpressionsLeft = null;
        $scope.cpmLeft = null;
        $scope.booked_from = null;
        $scope.booked_to = null;
        $scope.radSearch.latitude = null;
        $scope.radSearch.longitude = null;
        $scope.radSearch.radius = null;
        $scope.isLeftSearch = false;
      }
    };
    /*--- End of Clear Search ---*/

    /*================================
        | Multi date range picker options
        ================================*/
    // $scope.mapProductOpts = {
    //     multipleDateRanges: true,
    //     opens: 'center',
    //     locale: {
    //         applyClass: 'btn-green',
    //         applyLabel: "Book Now",
    //         fromLabel: "From",
    //         format: "DD-MMM-YY",
    //         toLabel: "To",
    //         cancelLabel: 'Cancel',
    //         customRangeLabel: 'Custom range'
    //     },
    //     isInvalidDate: function (dt) {
    //         for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
    //             if (moment(dt) >= moment($scope.unavailalbeDateRanges[i].booked_from) && moment(dt) <= moment($scope.unavailalbeDateRanges[i].booked_to)) {
    //                 return true;
    //             }
    //         }
    //         if (moment(dt) < moment()) {
    //             return true;
    //         }
    //     },
    //     isCustomDate: function (dt) {
    //         for (var i = 0; i < $scope.unavailalbeDateRanges.length; i++) {
    //             if (moment(dt) >= moment($scope.unavailalbeDateRanges[i].booked_from) && moment(dt) <= moment($scope.unavailalbeDateRanges[i].booked_to)) {
    //                 if (moment(dt).isSame(moment($scope.unavailalbeDateRanges[i].booked_from), 'day')) {
    //                     return ['red-blocked', 'left-radius'];
    //                 } else if (moment(dt).isSame(moment($scope.unavailalbeDateRanges[i].booked_to), 'day')) {
    //                     return ['red-blocked', 'right-radius'];
    //                 } else {
    //                     return 'red-blocked';
    //                 }
    //             }
    //         }
    //         if (moment(dt) < moment()) {
    //             return 'gray-blocked';
    //         }
    //     },
    //     eventHandlers: {
    //         'apply.daterangepicker': function (ev, picker) {
    //             //selectedDateRanges = [];
    //         }
    //     }
    // };
    /*====================================
            | Multi date range picker options end
            ====================================*/
    $scope.IsDisabled = true;
    $scope.EnableDisable = function () {
      $scope.IsDisabled = $scope.campaign.name.length == 0;
    };
    $scope.FilterProductlist = function (booked_from, booked_to) {
      MapService.filterProducts(booked_from, booked_to).then(function (result) {
        productList = [];
        locArr = [];
        uniqueMarkers = [];
        concentricMarkers = {};
        var filterObj = {
          area: $scope.selectedAreas,
          product_type: $scope.selectedFormats,
          booked_from,
          booked_to,
        };
        $scope.plottingDone = false;
        MapService.filterProducts(filterObj).then(function (markers) {
          _.each(markersOnMap, function (v, i) {
            v.setMap(null);
            $scope.Clusterer.removeMarker(v);
          });
          markersOnMap = Object.assign([]);
          $scope.productmarkerslist = markers;
          $scope.filteredMarkers = markers;
          $scope.processMarkers();
          if (markers.length > 0) {
            var bounds = new google.maps.LatLngBounds();
            _.each(markersOnMap, function (v, i) {
              bounds.extend(v.getPosition());
            });
          } else {
            toastr.error("no marker found for the criteria you selected");
          }
        });
      });
    };
    if ($auth.getPayload().userMongo.user_type == "owner") {
      $scope.shortlistedOwnerHide = true;
    }
    //    $scope.checkAuthNav = function(){
    //         if($auth.getPayload().userMongo.user_type == 'basic'){
    //             $location.path('/shortlisted-products')
    //         }
    //         else if($auth.getPayload().userMongo.user_type == 'owner'){
    //             $location.path('/owner/'+$rootScope.clientSlug+'/shortlisted-products')
    //         }
    //     }
    //    $scope.getProductUnavailableDates = function(productId, ev){
    //      MapService.getProductUnavailableDates(productId).then(function(dateRanges){
    //        $scope.unavailalbeDateRanges = dateRanges;
    //        $(ev.target).parents().eq(3).find('input').trigger('click');
    //      });
    //    }
    // SHORT-LIST ENDs
    // Save-camp
    $scope.toggleExistingCampaignSidenav = function () {
      $scope.showSaveCampaignPopup = !$scope.showSaveCampaignPopup;
    };
    // Save-camp-end
    // SAVE-CAMPPP
    $scope.saveCampaign = function (product_id, selectedDateRanges) {
      if (product_id) {
        $scope.campaign.products = [];
        var sendObj = {
          product_id: product_id,
        };

        if (selectedDateRanges.length > 0) {
          sendObj.dates = selectedDateRanges;
        } else {
          toastr.error("Please select dates.");
          return false;
        }
        $scope.campaign.products.push(sendObj);
        $form = $scope.forms.mySaveCampaignForm;
      } else {
        if ($scope.shortListedProducts.length > 0) {
          $scope.campaign.products = [];
          _.each($scope.shortListedProducts, function (v, i) {
            $scope.campaign.products.push(v.id);
          });
          $form = $scope.forms.viewAndSaveCampaignForm;
        } else {
          toastr.error("Please shortlist some products first.");
        }
      }
      if ($scope.campaign.products) {
        CampaignService.saveUserCampaign($scope.campaign).then(function (
          response
        ) {
          if (response.status == 1) {
            //$scope.campaignSavedSuccessfully = true;
            $timeout(function () {
              $scope.campaign = {};
              $form.$setPristine();
              $form.$setUntouched();
              toastr.success(response.message);
              //$scope.campaignSavedSuccessfully = false;
            }, 3000);
            $scope.loadActiveUserCampaigns();
            getShortListedProducts();
          } else {
            $scope.saveUserCampaignErrors = response.message;
          }
        });
      }
    };
    $scope.saveCampaignName = function (
      campaignName,
      productId,
      selectedDateRanges
    ) {
      if ($scope.totalnumDays < $scope.product.minimumbooking.split(" ")[0]) {
        toastr.error(
          "minimum you have to select " + $scope.product.minimumbooking
        );
        return false;
      }
      selectedDateRanges.forEach(function (item) {
        item.startDate = moment(item.startDate).format("YYYY-MM-DD");
        item.endDate = moment(item.endDate).format("YYYY-MM-DD");
      });

      var paylaunchProduct = {
        productId: productId,
        name: campaignName,
        dates: selectedDateRanges,
        booking_slots: 1,
      };
      $scope.removeSelection();
      CampaignService.payAndLaunch(paylaunchProduct).then(function (res) {
        if (res.status == 1) {
          $scope.campaign.name = null;
          $scope.toggleExistingCampaignSidenav();
          $scope.toggleProductDetailSidenav();
          $window.location.href = "/campaign-details/" + res.campaign_id + "/1";
          toastr.success(res.message);
        } else if (res.status == 0) {
          toastr.error(res.message);
        }
      });
    };
    $scope.numOfSlots = 0;
    $scope.saveDigitalCampaignName = function (
      campaignName,
      productId,
      selectedDateRanges
    ) {
      if ($scope.numOfSlots > 0) {
        var paylaunchProduct = {
          productId: productId,
          name: campaignName,
          rateCard: $scope.totalDigitalSlotAmount,
          booking_slots: $scope.numOfSlots,
          dates: [],
        };
      } else {
        var paylaunchProduct = {
          productId: productId,
          name: campaignName,
          rateCard: $scope.totalSlotAmount,
        };
      }
      var startAndEndDates = selectedDateRanges.filter((item) => item.selected);
      $scope.loading = true;
      startAndEndDates.forEach((item, index) => {
        paylaunchProduct.dates.push({
          startDate: moment(item.startDay).format("YYYY-MM-DD"),
          endDate: moment(item.endDay).format("YYYY-MM-DD"),
        });
        $scope.loading = false;
      });
      CampaignService.payAndLaunch(paylaunchProduct).then(function (res) {
        if (res.status == 1) {
          $scope.campaign.name = null;
          $scope.toggleExistingCampaignSidenav();
          $scope.toggleProductDetailSidenav();
          toastr.success(res.message);
        } else if (res.status == 0) {
          toastr.error(res.message);
        }
      });
    };
    // Product-Controller Code - Ends
    $scope.$watch("ranges.selectedDateRanges", function () {
      console.log("inside $watch.....");
      //$scope.totalPriceUserSelected = 0;
      $scope.totalnumDays = 0;
      $scope.newratecard = 0;
      var productPerDay = $scope.product.rateCard / 28;
      console.log("productPerDay: => " + productPerDay);
      localStorage.removeItem("mindays");
      for (item in $scope.ranges.selectedDateRanges) {
        console.log("index: => " + item);
        var startDate = moment(
          $scope.ranges.selectedDateRanges[item].startDate
        ).format("YYYY-MM-DD");
        var endDate = moment(
          $scope.ranges.selectedDateRanges[item].endDate
        ).format("YYYY-MM-DD");
        console.log("startDate: => " + startDate);
        console.log("endDate: => " + endDate);

        var slotDays = moment(endDate).diff(startDate, "days") + 1;
        console.log("Slot Days: => " + slotDays);
        $scope.totalnumDays += slotDays;
        console.log("Total Days: => " + $scope.totalnumDays);
        //$scope.totalPriceUserSelected = productPerDay * $scope.totalnumDays;

        if ($scope.product.fix == "Variable") {
          var tempValue =
            productPerDay *
            (Number($scope.product.minimumdays) > slotDays
              ? $scope.product.minimumdays
              : slotDays);
          console.log("tempValue: " + tempValue);
          $scope.newratecard += tempValue;
          //$scope.newratecard += Math.ceil(tempValue);
        } else {
          //for fixed products
          var pricePerSlot = productPerDay * $scope.product.minimumdays;
          console.log("pricePerSlot: " + pricePerSlot);
          var slotsCount = Math.ceil(slotDays / $scope.product.minimumdays);
          console.log("slotsCount: => " + slotsCount);
          $scope.newratecard += slotsCount * pricePerSlot;
          console.log("newratecard: => " + $scope.newratecard);
        }
      }
      if ($scope.product.minimumbooking) {
        if (
          $scope.product.minimumbooking != "No" &&
          $scope.totalnumDays < $scope.product.minimumbooking.split(" ")[0] &&
          $scope.ranges.selectedDateRanges.length > 0
        ) {
          $scope.ranges.selectedDateRanges = [];
          toastr.error(
            "please select minimum " + $scope.product.minimumbooking
          );
          return false;
        }
      }
      // Get quantity
      if (
        $scope.ranges &&
        $scope.ranges.selectedDateRanges &&
        $scope.ranges.selectedDateRanges.length
      ) {
        if ($scope.product && $scope.product.id) {
          var startDate = moment(
            $scope.ranges.selectedDateRanges[0].startDate
          ).format("YYYY-MM-DD");
          var endDate = moment(
            $scope.ranges.selectedDateRanges[
              $scope.ranges.selectedDateRanges.length - 1
            ].endDate
          ).format("YYYY-MM-DD");
          if (startDate && endDate) {
            $scope.quantity = 0;
            $scope.selectedQuantity = 0;
            var isLoading = false;
            if (!isLoading) {
              isLoading = true;
              MapService.getQuantity(
                $scope.product.id,
                startDate,
                endDate
              ).then(function (result) {
                console.log("Available quantity: " + result);
                isLoading = false;
                $scope.quantity = parseInt(result);
              });
            }
          }
        }
      }
    });

    $scope.selectedQuantity = 0;

    $scope.decreaseQuantity = function () {
      $scope.selectedQuantity -= 1;
    };

    $scope.increaseQuantity = function () {
      $scope.selectedQuantity += 1;
    };

    $scope.prevQuantity = 0;
    $scope.quantityHandler = function () {
      $scope.prevQuantity = $scope.selectedQuantity;
    };

    $scope.quantityChangeHandler = function () {
      if (
        $scope.selectedQuantity > $scope.quantity ||
        $scope.selectedQuantity < 0
      ) {
        toastr.warning(
          `Please enter quantity less or equal to available quantity (${$scope.quantity})`
        );
        $scope.selectedQuantity = $scope.prevQuantity;
      }
    };

    $scope.quantityHandler1 = function () {
      $scope.quantityHandler();
    };

    $scope.quantityChangeHandler1 = function () {
      $scope.quantityChangeHandler();
    };

    $scope.toggleProductDetailSidenav = function (calledFrom) {
      if (calledFrom == "close") {
        $scope.mapObj.setZoom(3);
      }
      $(".warn-text").text("");
      $scope.ranges.selectedDateRanges = [];
      localStorage.setItem("clearSelection", "true");
      //$scope.totalPriceUserSelected = 0;
      $scope.totalnumDays = 0;
      $scope.removeSelection();
      $mdSidenav("productDetails").close();
    };

    $scope.notifyMeProduct = null;
    $scope.notifyMeMessage = "";
    $scope.openNotifyMePopup = function (product) {
      $scope.notifyMeMessage = "";
      $scope.notifyMeProduct = product;
      $("#notifyMeModal-test").show();
    };

    $scope.hideNotifyMeModal = function () {
      $("#notifyMeModal-test").hide();
    };

    $scope.notifyMe = function (prodId, msg) {
      const payload = {
        user_message: msg,
        /*"loggedinUser": JSON.parse(localStorage.loggedInUser).user_id,*/
        loggedinUser: JSON.parse(localStorage.loggedInUser),
        product_id: prodId,
      };
      console.log(payload);
      ManagementReportService.notifyMe(payload).then(function (result) {
        if (result && result.status) {
          toastr.success(result.message);
          $scope.hideNotifyMeModal();
        } else {
          toastr.error(result.message);
        }
      });
    };

    $scope.getImageUrl = function (url) {
      if (
        url &&
        (url.includes(".png") ||
          url.includes(".PNG") ||
          url.includes(".jpg") ||
          url.includes(".jpeg") ||
          url.includes(".JPEG") ||
          url.includes(".JPG") ||
          url.includes(".svg") ||
          url.includes(".SVG"))
      ) {
        return url;
      }
      return "assets/images/no-image.jpg";
    };

    /*
            $scope.zoomIn = function(event, imge) {
                var pre = document.getElementById("preview");
                pre.style.visibility = "visible";
                if ($('#zoom1').is(':hover')) {
                      var img = document.getElementById("zoom1");
                    //   pre.style.backgroundImage = `url(${ $scope.currentImage})`;
                      pre.style.backgroundImage = `url(${imge})`;
                      
                      pre.style.backgroundSize = "2000px 1500px"
                  }
                
                var posX = event.offsetX;
                var posY = event.offsetY;
                // pre.style.width = '150%';
                // pre.style.height ='150%';
                pre.style.backgroundPosition=(-posX*2.5)+"px "+(-posY*5.5)+"px";
              
            }
              
            $scope.zoomOut = function () {
            var pre = document.getElementById("preview");
            pre.style.visibility = "hidden";
            }
            */

    /*==================================
      Map ZoomIN/ZoomOut on pinch
    ====================================*/

    $scope.zoom = 3;
    $scope.last_scale = 1;

    function zoomOnMobile(ev) {
      $timeout(() => {
        if ($scope.zoom < 16 && ev.scale > $scope.last_scale) {
          $scope.zoom = $scope.zoom + 1;
        } else if ($scope.zoom > 2 && ev.scale < $scope.last_scale) {
          $scope.zoom = $scope.zoom - 1;
        }
      });
    }

    $scope.zoomOnPinch = debounce((ev) => zoomOnMobile(ev), 300);

    $timeout(() => {
      var mapContainer = document.getElementById("ngmap");
      var hammerObj = new Hammer(mapContainer);

      hammerObj.get("pinch").set({ enable: true });

      hammerObj.on("pinch", function (ev) {
        $scope.zoomOnPinch(ev);
      });
    });

    /*==================================
      End of Map ZoomIN/ZoomOut on pinch
    ====================================*/

    //Mobile ZoomIn && ZoomOut

    var container = document.getElementById("imgContainer");
    var img = document.getElementById("myimage");

    var hammer = new Hammer(container);
    hammer.get("pinch").set({
      enable: true,
    });
    hammer.on("pinch", function (ev) {
      img.style.transition = "all 1s 0.5ms";
      img.style.transform = `scale(${ev.scale},${ev.scale})`;
    });

    $scope.isHover = true;
    $scope.zoomIn = function (event, imge) {
      imageZoom("myimage", "myresult");
    };
    $scope.counter = 0;
    $scope.zoomOut = function () {
      console.log("zoom out...." + ++$scope.counter);
      result = document.getElementById("myresult");
      result.style.display = "none";
      $scope.isHover = false;
    };
    $scope.inCounter = 0;
    function imageZoom(imgID, resultID) {
      $scope.isHover = true;
      //$timeout(function() {
      var img, lens, result, cx, cy;
      img = document.getElementById(imgID);
      result = document.getElementById(resultID);
      if (result) {
        result.style.display = "block";
      }
      lens = document.getElementById("img-zoom-lens");
      /* Create lens: */
      if (!lens) {
        lens = document.createElement("DIV");
        lens.setAttribute("id", "img-zoom-lens");
      }
      /* Insert lens: */
      img.parentElement.insertBefore(lens, img);
      /* Calculate the ratio between result DIV and lens: */
      cx = result.offsetWidth / lens.offsetWidth;
      cy = result.offsetHeight / lens.offsetHeight;
      /* Set background properties for the result DIV */
      result.style.backgroundImage = "url('" + img.src + "')";
      result.style.backgroundSize =
        img.width * cx + "px " + img.height * cy + "px";
      /* Execute a function when someone moves the cursor over the image, or the lens: */
      lens.addEventListener("mousemove", moveLens);
      img.addEventListener("mousemove", moveLens);
      /* And also for touch screens: */
      lens.addEventListener("touchmove", moveLens);
      img.addEventListener("touchmove", moveLens);
      function moveLens(e) {
        var pos, x, y;
        /* Prevent any other actions that may occur when moving over the image */
        e.preventDefault();
        /* Get the cursor's x and y positions: */
        pos = getCursorPos(e);
        /* Calculate the position of the lens: */
        x = pos.x - lens.offsetWidth / 2;
        y = pos.y - lens.offsetHeight / 2;
        /* Prevent the lens from being positioned outside the image: */
        if (x > img.width - lens.offsetWidth) {
          x = img.width - lens.offsetWidth;
        }
        if (x < 0) {
          x = 0;
        }
        if (y > img.height - lens.offsetHeight) {
          y = img.height - lens.offsetHeight;
        }
        if (y < 0) {
          y = 0;
        }
        /* Set the position of the lens: */
        lens.style.left = x + "px";
        lens.style.top = y + "px";
        /* Display what the lens "sees": */
        result.style.backgroundPosition = "-" + x * cx + "px -" + y * cy + "px";
      }
      function getCursorPos(e) {
        var a,
          x = 0,
          y = 0;
        e = e || window.event;
        /* Get the x and y positions of the image: */
        a = img.getBoundingClientRect();
        /* Calculate the cursor's x and y coordinates, relative to the image: */
        x = e.pageX - a.left;
        y = e.pageY - a.top;
        /* Consider any page scrolling: */
        x = x - window.pageXOffset;
        y = y - window.pageYOffset;
        return { x: x, y: y };
      }
      //  });
    }
    // controller ends

    NgMap.getMap().then(function (map) {
      $scope.map = map;
      $scope.isDrawingManagerVisible = true;
      // Drawing manager configuration
      $scope.drawingControlOptions = {
        drawingModes: [
          google.maps.drawing.OverlayType.MARKER,
          google.maps.drawing.OverlayType.CIRCLE,
          google.maps.drawing.OverlayType.POLYGON,
          google.maps.drawing.OverlayType.POLYLINE,
          google.maps.drawing.OverlayType.RECTANGLE,
        ],
        position: google.maps.ControlPosition.TOP_CENTER,
        drawingControl: true,
      };
      // Selected coordinates variable
      $scope.selectedCoordinates = [];

      // Handle overlay complete event
      $scope.onOverlayComplete = function (e) {
        // Get the selected coordinates from the map
        var selectedShape = e.overlay;

        if ($scope.currentShape) {
          $scope.currentShape.setMap(null);
        }
        if (
          selectedShape instanceof google.maps.Circle ||
          selectedShape instanceof google.maps.Rectangle
        ) {
          var selectedShape_trial = "circle";
          var bounds = selectedShape.getBounds();
          // Extract coordinates from bounds
          var ne = bounds.getNorthEast();
          var sw = bounds.getSouthWest();

          // Format coordinates
          var rectangleCoordinates = {
            northeast: { lat: ne.lat(), lng: ne.lng() },
            southwest: { lat: sw.lat(), lng: sw.lng() },
          };
          // Update the selected coordinates in the view
          $scope.selectedCoordinates = rectangleCoordinates;
          // console.log("dileepcr", $scope.selectedCoordinates);
          // Additional circle-specific logic if needed
        } else if (
          selectedShape instanceof google.maps.Polyline ||
          selectedShape instanceof google.maps.Polygon
        ) {
          var selectedShape_trial = "polyline";
          var path = selectedShape.getPath();

          // Extract coordinates from the path
          var coordinates = path.getArray().map(function (point) {
            return { lat: point.lat(), lng: point.lng() };
          });

          // Update the selected coordinates in the view
          $scope.selectedCoordinates = coordinates;
          // console.log("dileeppp", $scope.selectedCoordinates);
          // Additional polyline-specific logic if needed
        } else if (selectedShape instanceof google.maps.Marker) {
          var selectedShape_trial = "marker";
          var markerPosition = selectedShape.getPosition();

          // Extract coordinates from the position
          var markerCoordinates = [
            {
              lat: markerPosition.lat(),
              lng: markerPosition.lng(),
            },
          ];

          // Update the selected coordinates in the view
          $scope.selectedCoordinates = markerCoordinates;
          console.log("Durga", $scope.selectedCoordinates);
          // Additional polyline-specific logic if needed
        } else {
          toastr.error("Selected shape is of an unknown type");
          return;
        }
        $scope.currentShape = selectedShape;

        if ($scope.selectedCoordinates.length === 0) {
          toastr.error("Invalid request.");
        } else {
          console.log("selectedShape", selectedShape_trial);
          payload = {
            shape: selectedShape_trial,
            coordinates: $scope.selectedCoordinates,
          };
        }
        $scope.plottingDone = false;
        MapService.filterCoordinatorsProducts(payload).then(function (markers) {
          $("#show_prodList").click();
          _.each(markersOnMap, function (v, i) {
            v.setMap(null);
            $scope.Clusterer.removeMarker(v);
          });
          markersOnMap = Object.assign([]);
          $scope.filteredMarkers = markers;
          $scope.productmarkerslist = markers;
          $scope.processMarkers();
          if (markers.length > 0) {
            var bounds = new google.maps.LatLngBounds();
            _.each(markersOnMap, function (v, i) {
              bounds.extend(v.getPosition());
            });
            angular.forEach($scope.productmarkerslist, function (item) {
              const areaTimeZoneType = item.area_time_zone_type;
              angular.forEach(item.product_details, function (newItem) {
                try {
                  var date = new Date().getTime();
                  newItem.isExpired = true;
                  if (
                    newItem.to_date &&
                    newItem.to_date.$date &&
                    newItem.to_date.$date.$numberLong &&
                    newItem.to_date.$date.$numberLong >= date
                  ) {
                    newItem.isExpired = false;
                  }
                  const { startDate, endDate } = convertDateToMMDDYYYY(
                    newItem,
                    areaTimeZoneType
                  );
                  newItem["startDate"] = startDate;
                  newItem["endDate"] = endDate;
                  newItem["areaTimeZoneType"] = areaTimeZoneType;
                } catch (error) {
                  console.log(error);
                }
              });
            });
          } else {
            toastr.error("no marker found for the criteria you selected");
          }
        });
        // .catch(function (error) {
        //   // Handle error response
        //   console.error("API error:", error);
        // });
      };
    });

    $scope.hideShow = false; // Assuming you have this variable for showing/hiding drawn shapes
    $scope.currentShape = null; // Variable to keep track of the current drawn shape

    $scope.toggleDrawnShapes = function () {
      $scope.hideShow = !$scope.hideShow;

      // Toggle the visibility of the current drawn shape
      if ($scope.currentShape) {
        $scope.currentShape.setMap($scope.hideShow ? $scope.map : null);
      }
    };
    $scope.resetDrawnShapes = function () {
      // Clear the current shape on the map
      if ($scope.currentShape) {
        $scope.currentShape.setMap(null);
        $scope.currentShape = null;
      }
      // Clear the selected coordinates
      $scope.selectedCoordinates = [];
      determineApiCall();
    };

    $scope.hasDrawnShapes = function () {
      // Check if there is a drawn shape
      return $scope.currentShape !== null;
    };

    function determineApiCall() {
      var path = $location.path();
      // Check if the path contains "/location/search_criteria"
      if (path.includes("/location/search_criteria")) {
        $window.location.reload();
      } else if (path === "/location") {
        $window.location.reload();
      }
    }

    // $scope.nearMe = function () {
    //   clearDrawnShape();
    //   if (navigator.geolocation) {
    //     navigator.geolocation.getCurrentPosition(
    //       function (position) {
    //         $scope.$apply(function () {
    //           $scope.mysrclat = position.coords.latitude;
    //           $scope.mysrclong = position.coords.longitude;
    //           $scope.filterNearbyProducts($scope.mysrclat, $scope.mysrclong);

    //           var userLatLng = new google.maps.LatLng($scope.mysrclat, $scope.mysrclong);
    //            // Creating a custom marker for the user's location
    //           var userMarker = new google.maps.Marker({
    //             position: userLatLng,
    //             icon: 'assets/images/maps/map_location_person1.png', 
    //             map: $scope.mapObj,
    //           });
    //           $scope.mapObj.setCenter(userLatLng);
    //           $scope.mapObj.setZoom(15); 
    //         });
    //       },
    //       function (error) {
    //         switch (error.code) {
    //           case error.PERMISSION_DENIED:
    //             toastr.error("User denied the request for Geolocation.");
    //             break;
    //           case error.POSITION_UNAVAILABLE:
    //             toastr.error("Location information is unavailable.");
    //             break;
    //           case error.TIMEOUT:
    //             toastr.error("The request to get user location timed out.");
    //             break;
    //           case error.UNKNOWN_ERROR:
    //             toastr.error("An unknown error occurred.");
    //             break;
    //         }
    //       }
    //     );
    //   } else {
    //     toastr.error("Geolocation is not supported by this browser.");
    //   }
    // };
    $scope.nearMe = function () {
      clearDrawnShape();
      // Use static coordinates
      $scope.mysrclat = 17.4482643;
      $scope.mysrclong = 78.3725738;
      // Call the filterNearbyProducts function
      $scope.filterNearbyProducts($scope.mysrclat, $scope.mysrclong);
      var userLatLng = new google.maps.LatLng($scope.mysrclat, $scope.mysrclong);
        // Creating a custom marker for the user's location
      var userMarker = new google.maps.Marker({
          position: userLatLng,
          icon: 'assets/images/maps/map_location_person1.png', 
          map: $scope.mapObj,
      });
      $scope.mapObj.setCenter(userLatLng);
      $scope.mapObj.setZoom(16);
    };
    function clearDrawnShape() {
      $scope.hideShow = false;
      if ($scope.currentShape) {
        $scope.currentShape.setMap(null);
        $scope.currentShape = null;
      }
    }

    $scope.filterNearbyProducts = function (lat, lng) {
      var payload = {
        shape: "marker",
        coordinates: [
          {
            lat: lat,
            lng: lng,
          },
        ],
      };
      $scope.selectedCoordinates = payload;
      $rootScope.isLoading = true;
      MapService.filterCoordinatorsProducts(payload).then(function (markers) {
        $("#show_prodList").click();
        // Clear existing markers on the map
        _.each(markersOnMap, function (v, i) {
          v.setMap(null);
          $scope.Clusterer.removeMarker(v);
        });
        markersOnMap = Object.assign([]);

        // Update the markers with the new data
        _.each(markers, function (marker) {
          var newMarker = new google.maps.Marker({
            position: new google.maps.LatLng(marker.lat, marker.lng),
            map: $scope.map,
          });
          // Add marker to the markersOnMap array
          markersOnMap.push(newMarker);
        });

        $scope.filteredMarkers = markers;
        $scope.productmarkerslist = markers;
        $scope.processMarkers();

        if (markers.length > 0) {
          var bounds = new google.maps.LatLngBounds();
          _.each(markersOnMap, function (v, i) {
            bounds.extend(v.getPosition());
          });

          angular.forEach($scope.productmarkerslist, function (item) {
            const areaTimeZoneType = item.area_time_zone_type;
            angular.forEach(item.product_details, function (newItem) {
              try {
                var date = new Date().getTime();
                newItem.isExpired = true;
                if (
                  newItem.to_date &&
                  newItem.to_date.$date &&
                  newItem.to_date.$date.$numberLong &&
                  newItem.to_date.$date.$numberLong >= date
                ) {
                  newItem.isExpired = false;
                }
                const { startDate, endDate } = convertDateToMMDDYYYY(
                  newItem,
                  areaTimeZoneType
                );
                newItem["startDate"] = startDate;
                newItem["endDate"] = endDate;
                newItem["areaTimeZoneType"] = areaTimeZoneType;
              } catch (error) {
                toastr.error(error);
              }
            });
          });
        } else {
          toastr.error("No marker found for the criteria you selected");
        }
      }).finally(function () {
        $rootScope.isLoading = false;
      });
    };

    $scope.redirectToReport = function() {
      $location.path('/report');
    }
    
  },
]);
