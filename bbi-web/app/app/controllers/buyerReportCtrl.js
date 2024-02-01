angular.module("bbManager").controller("buyerReportCtrl", [
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
    toastr,
    $state,
    ManagementReportService
  ) {
    $scope.reportCount = {};
    $scope.headers = [
      {
        items: [
          { field: "first_name", heading: "First Name" },
          { field: "last_name", heading: "Last Name" },
          { field: "email", heading: "Email" },
          { field: "phone", heading: "Phone" },
          { field: "company", heading: "Company" },
          { field: "verified", heading: "Activated" },
        ],
      },
      {
        items: [
          { field: "first_name", heading: "First Name" },
          { field: "last_name", heading: "Last Name" },
          { field: "email", heading: "Email" },
          { field: "phone", heading: "Phone" },
          { field: "company_name", heading: "Company" },
          { field: "verified", heading: "Activated" },
        ],
      },
      {
        items: [
          { field: "first_name", heading: "First Name" },
          { field: "last_name", heading: "Last Name" },
          { field: "email", heading: "Email" },
          { field: "phone", heading: "Phone" },
          { field: "company", heading: "Company" },
          { field: "verified", heading: "Activated" },
        ],
      },
    ];

    createChart = function (
      insertionOrder,
      scheduled,
      running,
      closed,
    ) {
      Highcharts.chart("container", {
        chart: {
          type: "column",
        },
        title: {
          text: "Campaigns",
        },
        subtitle: {
          text: "",
        },
        accessibility: {
          announceNewData: {
            enabled: true,
          },
        },
        xAxis: {
          type: "category",
        },
        yAxis: {
          title: {
            text: "Count",
          },
        },
        legend: {
          enabled: false,
        },
        credits: {
          enabled: false,
        },
        plotOptions: {
          series: {
            borderWidth: 0,
            dataLabels: {
              enabled: true,
              format: "{point.y:.f}",
            },
          },
        },

        tooltip: {
          headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
          pointFormat:
            '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.f}</b> of total<br/>',
        },

        series: [
          {
            name: "Campaigns",
            colorByPoint: true,
            data: [
              {
                name: "Insertion Order",
                y: insertionOrder,
              },
              {
                name: "Scheduled",
                y: scheduled,
              },
              {
                name: "Running",
                y: running,
              },
              {
                name: "Closed",
                y: closed,
              },
            ],
          },
        ],
      });
    //   Highcharts.chart("container1", {
    //     chart: {
    //       plotBackgroundColor: null,
    //       plotBorderWidth: null,
    //       plotShadow: false,
    //       type: "pie",
    //     },
    //     title: {
    //       text: "Stripe",
    //     },
    //     credits: {
    //       enabled: false,
    //     },
    //     tooltip: {
    //       pointFormat: "{series.name}: <b>{point.y:.f}</b>",
    //     },
    //     accessibility: {
    //       point: {
    //         valueSuffix: "%",
    //       },
    //     },
    //     plotOptions: {
    //       pie: {
    //         allowPointSelect: true,
    //         cursor: "pointer",
    //         dataLabels: {
    //           enabled: true,
    //           format: "<b>{point.name}</b>: {point.y:.f} ",
    //         },
    //       },
    //       series: {
    //         cursor: "pointer",
    //         point: {
    //           events: {
    //             click: function () {
    //               return $scope.navigateToDetailPage(2);
    //             },
    //           },
    //         },
    //       },
    //     },
    //     series: [
    //       {
    //         name: "Payments",
    //         colorByPoint: true,
    //         data: [
    //           {
    //             name: "YTD Payments",
    //             y: paymentY,
    //           },
    //           {
    //             name: "YTD Refunds",
    //             y: refundY,
    //           },
    //         ],
    //       },
    //     ],
    //   });
    };
    getUserProfile = function () {
      ManagementReportService.getUserProfileData().then(function (result) {
        $scope.profile = result;
      });
    };
    getUserProfile();

    getBuyerReportCounts = function () {
      $rootScope.$emit("notoficationupdate");
      ManagementReportService.getBuyerReportCounts().then(function (result) {
        $scope.reportCount = result;
        $scope.totalCount = result.RequestedCampaigns + result.ScheduledCampaigns + result.RunningCampaigns + result.ClosedCampaigns;
        createChart(
          result.RequestedCampaigns,
          result.ScheduledCampaigns,
          result.RunningCampaigns,
          result.ClosedCampaigns,
        );
        // console.log("reports count: " + JSON.stringify(result));
          console.log(result);
      });
    };
    getBuyerReportCounts();

    $scope.addNewProduct = function () {
      document.getElementById("addNewProdDD").classList.toggle("show");
    };

    $scope.gotoCampaigns = function () {
      $location.path("/campaigns");
    };

    $scope.gotoProductDetails = function (prod_id) {
      var url = "/product-camp-details/" + prod_id;
      $location.path(url);
    };

    $scope.navigateToDetailPage = function (index) {
      //ManagementReportService.headers = $scope.headers[index%10].items;
      // $location.path("/report-details/" + index);
      $location.path("/campaigns");
    };
    //map ctr//

    $scope.forms = {};
    $scope.address = {
      // name: 'Hyderabad, Telangana, India',
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

    /*================================
                 | Multi date range picker options
                 ================================*/
    $scope.mapProductOpts = {
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
        "apply.daterangepicker": function (ev, picker) {
          //selectedDateRanges = [];
          // console.log(ev);
        },
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
      $scope.clearSearch('bottom');
      if (dateType == "startDate") {
        //to clear end date field
        $scope.booked_to = null;
      }
      //if (booked_from && booked_to) {
        var newStartFormat;
        var newEndFormat;

        if (booked_from)
          newStartFormat = getISOFormat(booked_from, true);
        else
          newStartFormat = null;

        if (booked_to)
          newEndFormat = getISOFormat(booked_to, false);
        else
          newEndFormat = null;
          
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
        $scope.plottingDone = false;
        MapService.filterProducts(filterObj).then(function (markers) {
          _.each(markersOnMap, function (v, i) {
            v.setMap(null);
            $scope.Clusterer.removeMarker(v);
          });
          markersOnMap = Object.assign([]);
          $scope.filteredMarkers = markers;
          $scope.productmarkerslist = markers;
          $scope.productListData = [];


          /*---Add Bell Icon for Expired Products---*/
          angular.forEach($scope.productmarkerslist, function(item) {
            angular.forEach(item.product_details, function(newItem) {
              try {
                var date = new Date().getTime();
                newItem.isExpired = true;
                if (newItem.to_date && newItem.to_date.$date && newItem.to_date.$date.$numberLong && newItem.to_date.$date.$numberLong >= date) {
                  newItem.isExpired = false;
                } 
                $scope.productListData.push(item);
              } catch (error) {
                console.log(error);
              }
            });
          });
          /*---//Add Bell Icon for Expired Products---*/


          // angular.forEach($scope.productmarkerslist, function (item) {
          //   angular.forEach(item.product_details, function (newItem) {
          //     var date = new Date();
          //     newItem.isExpired = true;
          //     if (moment(newItem.to_date.$date.$numberLong) >= moment(date)) {
          //       newItem.isExpired = false;
          //     }
          //     $scope.productListData.push(item);
          //   });
          // });




          $scope.productmarkerslist = $scope.productListData;
          console.log("ProductList", $scope.productListData.length);

          if ($scope.productmarkerslist.length > 0) {
            $scope.processMarkers();
            var bounds = new google.maps.LatLngBounds();
            _.each(markersOnMap, function (v, i) {
              bounds.extend(v.getPosition());
            });
          } else {
            toastr.error("no marker found for the criteria you selected");
          }
        });
     // } else {
        //return false;
     // }
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
    //     $mdSidenav('productList').toggle();
    // });
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

    /*** Multiple Search  ***/ 
    $scope.page_params = {
      page_no: 1,
      page_size: 5
    };

    $scope.searchTermAry = [
      {
          searchTerm: ''
      }
    ];
    $scope.searchTerms = [];

     /* Debounce */
    function debounce(func, timeout = 700) {
      let timer;
      return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => { func.apply(this, args); }, timeout);
      };
    }
    /* End of Debounce */

    function keyUpHandler(searchTerm) {
      $scope.clearSearch('top');
      if(searchTerm == '' || searchTerm?.length >= 3) {
          $scope.isSchroll = true;
          $scope.page_params.page_no = 1;
          $scope.searchTerms = $scope.searchTermAry.map(item => item.searchTerm).filter(item => item !=='');
          if ($scope.searchTerms && $scope.searchTerms.length>1) {
              $scope.searchTerms = $scope.searchTerms.join('::');
              mapFilteredProducts($scope.searchTerms, false);
          } else if($scope.searchTerms.length == 1) {
            mapFilteredProducts($scope.searchTerms[0], false);
          } else {
            mapFilteredProducts('', false);
          }
      }
    }

    $scope.searchTermChanged = debounce((searchTerm) => keyUpHandler(searchTerm))

    $scope.addNewSearchField = function() {
      $scope.searchTermAry.push({searchTerm: ''});
    }

    $scope.removeLastSearchField = function() {
      $scope.searchTermAry.pop();
      $scope.searchTermChanged("");
    }
    /*** Ending Multiple Search ***/

    $scope.markers = [];
    $scope.isProductsLoading = false;
    $scope.isSchroll = false;

    function convertDateToMMDDYYYY(dates,areaTimeZoneType) {
      const startDate = parseInt(dates.from_date.$date.$numberLong);
      const endDate = parseInt(dates.to_date.$date.$numberLong);
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

    function mapFilteredProducts(search_param = '', isFromScroll = true) {
      $rootScope.isLoading = true;
      $scope.isProductsLoading = true;
      MapService.mapProductsfiltered({page_params: $scope.page_params, search_param}).then(function (resMarkers) {
        $rootScope.isLoading = true;
        if(isFromScroll) {
          $scope.markers = $scope.markers.concat(resMarkers);
        } else {
          $scope.markers = resMarkers;
        }
        var markers = $scope.markers;

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
        console.log('product' ,  $scope.productmarkerslist.length);
        if (!$scope.isSchroll) {
          $mdSidenav("productList").toggle();
        }
        $timeout(function () {
          $("#showRightPush").show();
        });
        // $scope.productListData = [];
        angular.forEach($scope.productmarkerslist, function (item) {
          const areaTimeZoneType = item.area_time_zone_type;
          angular.forEach(item.product_details, function (newItem) {
            const { startDate, endDate } = convertDateToMMDDYYYY(newItem,areaTimeZoneType);
            newItem["startDate"] = startDate;
            newItem["endDate"] = endDate;
            var date = new Date().getTime();
            newItem.isExpired = true;
            if (newItem.to_date.$date.$numberLong >= date) {
              newItem.isExpired = false;
            }
            // $scope.productListData.push(item);
          });
        });
        // $scope.productmarkerslist = Object.assign({}, $scope.productListData);
        $rootScope.isLoading = false;
        $scope.isProductsLoading = false;
      });
    }
    mapFilteredProducts();

    /***  Scroll Handler  ***/
    const productListDiv =jQuery('#prodlist');
    productListDiv.on('scroll', (event) => {
      // console.log('productListDiv.scrollTop()',productListDiv.scrollTop());
      // console.log('productListDiv.outerHeight()', productListDiv.outerHeight());
      // console.log('event.target.scrollHeight', event.target.scrollHeight);
        if ($scope.isLeftSearch) return;

        if (productListDiv.scrollTop() + Math.round(productListDiv.outerHeight()) >= event.target.scrollHeight) {
          if (!$scope.isProductsLoading) {
            $scope.isSchroll = true;
            $scope.page_params.page_no = parseInt($scope.page_params.page_no) + 1;
            if($scope.searchTerms.length) {
              mapFilteredProducts($scope.searchTerms);
            } else {
              mapFilteredProducts();
            }
          } 
        }
    });
    /***  End of Scroll Handler  ***/

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
          imageCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load('./controllers/ImageCtrl.js');
          }],
        }
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

    function selectMarker(marker) {
      $scope.toggleProductDetailSidenav();
      $scope.$parent.alreadyShortlisted = false;
      $scope.mapObj.setCenter(marker.position);
      selectorMarker.setPosition(marker.position);
      selectorMarker.setMap($scope.mapObj);
      $scope.product.id = marker.properties["id"];
      $scope.product.rateCard = marker.properties["rateCard"];
      $scope.product.image = config.serverUrl + marker.properties["image"];
      $scope.product.siteNo = marker.properties["siteNo"];
      $scope.product.city = marker.properties["city"];
      $scope.product.panelSize = marker.properties["panelSize"];
      $scope.product.type = marker.properties["type"];
      $scope.product.weekPeriod = marker.properties["weekPeriod"];
      $scope.product.venue = marker.properties["venue"];
      $scope.product.vendor = marker.properties["vendor"];
      $scope.product.address = marker.properties["address"];
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
      $scope.product.fliplength = marker.properties["fliplength"];
      $scope.product.audited = marker.properties["audited"];
      $scope.product.format = marker.properties["format_name"];
      $scope.product.lighting = marker.properties["lighting"];
      $scope.product.direction = marker.properties["direction"];
      $scope.product.availableDates = marker.properties["availableDates"];
      $scope.product.slots = marker.properties["slots"];
      $scope.product.lat = marker.properties["lat"];
      $scope.product.lng = marker.properties["lng"];
      $scope.product.installCost = marker.properties["installCost"];
      $scope.product.zipcode = marker.properties["zipcode"];
      $scope.product.Comments = marker.properties["Comments"];
      $scope.product.locationDesc = marker.properties["locationDesc"];
      $scope.product.minimumbooking = marker.properties["minimumbooking"];
      $scope.product.strengths = marker.properties["strengths"];
      $scope.product.staticMotion = marker.properties["staticMotion"];
      $scope.product.mediahhi = marker.properties["mediahhi"];
      $scope.product.sound = marker.properties["sound"];
      $scope.product.notes = marker.properties["notes"];
      $scope.product.productioncost = marker.properties["productioncost"];
      $scope.hideSelectedMarkerDetail = false;
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

    /* Get All Markers Product*/
    function getAllMarkers() {
      MapService.mapAllProducts().then(function(res) {
        $scope.allMarkers = res;
        NgMap.getMap().then(function (map) {
          $scope.mapObj = map;
          $scope.processMarkers();
        });
      });
    }
    getAllMarkers();

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
      // function addUniqueMarker(markerData) {
      function addUniqueMarker(markerData) {
        uniqueMarkers.push(markerData.product_details);
        var latLng = new google.maps.LatLng(
          markerData._id.lat,
          markerData._id.lng
        );
        var marker = new google.maps.Marker({
          position: latLng,
          /*icon: {
                            url: config.serverUrl + markerData.product_details[0].symbol,
                            scaledSize: new google.maps.Size(30, 30)
                        },*/
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
          $window.location.href = "/location";
          selectMarker(marker);
          // setTimeout(() => {
          //     angular.element($('#calender-autolaod-div')).trigger('click');
          //     angular.element($('#hideCalenderInput')).hide()
          // },100)
        });
      }
      //var latLngGroups = _.groupBy($scope.filteredMarkers, function (item) {
      //return item.lat + ', ' + item.lng;
      //});
      _.each($scope.allMarkers, function (data) {
        $scope.loading = true;
        if (data.product_details.length == 1) {
          $scope.loading = false;
          addUniqueMarker(data);
        } else if (data.product_details.length > 1) {
          addNewMarkers(data);
          $scope.loading = false;
        }
        $scope.loading = false;
        $scope.plottingDone = true;
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

    $scope.applyFilter = function (booked_from, booked_to) {
      $scope.clearSearch('bottom');
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
      /*---Radius Search---*/
      if (!$scope.isSearchDisabled) {
        filterObj.radiusSearch = $scope.radSearch;
      }
      /*---//Radius Search---*/
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
          $scope.loading = true;
          var bounds = new google.maps.LatLngBounds();
          _.each(markersOnMap, function (v, i) {
            bounds.extend(v.getPosition());
            $scope.loading = false;
          });
        } else {
          toastr.error("no marker found for the criteria you selected");
        }
      });
    };

    // $scope.shortlistSelected = function (productId, selectedDateRanges, producttype, ev) {
    //     var arr = [];
    //     var startAndEndDates = selectedDateRanges.filter((item) => item.selected)
    //     startAndEndDates.forEach((item, index) => {
    //         if(startAndEndDates.length == 1){
    //                 arr.push({ startDate: moment(item.startDay).format('YYYY-MM-DD'), endDate : moment(item.endDay).format('YYYY-MM-DD') })
    //         }else{
    //             if (index == 0) {
    //                 arr.push({ startDate: moment(item.startDay).format('YYYY-MM-DD') })
    //             } else if (index == startAndEndDates.length - 1) {
    //                 arr[0].endDate = moment(item.endDay).format('YYYY-MM-DD');
    //             }
    //         }
    //     })
    //     if(producttype == "Digital Bulletin" || producttype == "Bulletin" ){
    //         var sendObj = {
    //             product_id: productId,
    //             dates: arr,
    //             numOfSlots : $scope.numOfSlots
    //         }
    //     }else{
    //         var sendObj = {
    //             product_id: productId,
    //             dates: arr
    //         }
    //     }

    //     MapService.shortListProduct(sendObj).then(function (response) {
    //         getShortListedProducts();
    //         if(response.status == 0){
    //             toastr.error(response.message);
    //         }else if((response.status == 1)){
    //             toastr.success(response.message);
    //             $scope.toggleProductDetailSidenav();
    //         }

    //         // $mdDialog.show(
    //         //     $mdDialog.alert()
    //         //         .parent(angular.element(document.querySelector('body')))
    //         //         .clickOutsideToClose(true)
    //         //         .title('Cart Product')
    //         //         .textContent(response.message)
    //         //         .ariaLabel('shortlist-success')
    //         //         .ok('Got it!')
    //         //         .targetEvent(ev),
    //         //      $mdSidenav('productDetails').close()
    //         // );

    //         // $mdSidenav('productDetails').close()
    //     });
    // }
    $scope.shortlistSelected = function (productId, selectedDateRanges, ev) {
      /* 
                if($scope.product.fix == 'Fixed'){
                    var edates = selectedDateRanges[0].endDate.split("T")[0]
                    var d = new Date(edates);
                    // var originalEnddate = d.getDate().addDays(28);
                    d.setDate(d.getDate()+28);
                    edates = moment(d).format("YYYY-MM-DD");
                    selectedDateRanges[0].endDate = edates+selectedDateRanges[0].endDate.split("T")[1]
                    console.log(selectedDateRanges)
                }*/
      var sendObj = {
        product_id: productId,
        dates: selectedDateRanges,
        booked_slots: 1,
        newratecard: $scope.newratecard,
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
        //shortListedProductsLength = response.length;
        // $scope.shortListedProducts = response;
        // $scope.shortListedProducts = response.shortlisted_products;
        // $scope.shortListedTotal = response.shortlistedsum;
        //$rootScope.$emit("shortListedProducts", shortListedProductsLength)
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
      // If we finally decide to use selecting products for a campaign
      // if($scope.selectedForNewCampaign.length == 0){
      //   // add all shortlisted products to campaign
      //   // CampaignService.saveCampaign($scope.shortListedProducts).then(function(response){
      //   //   $scope.campaignSavedSuccessfully = true;
      //   // });
      // }
      // else{
      //   // add all shortlisted products for new campaign
      //   // CampaignService.saveCampaign($scope.selectedForNewCampaign).then(function(response){
      //   //   $scope.campaignSavedSuccessfully = true;
      //   // });
      // }
      // campaign.products = $scope.selectedForNewCampaign;
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
          $scope.loading = true;
          _.each($scope.shortListedProducts, function (v, i) {
            $scope.campaign.products.push(v.id);
            $scope.loading = false;
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
      $scope.clearSearchTerm();
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
            } else if(!newItem.isExpired) {
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
      $scope.details = $scope.selected;
      $scope.loading = true;
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
            //mapFilteredProducts();
            $mdSidenav("productDetails").close();
            // $scope.closedRightMenu();
            productlistArray = [];
            $scope.productlist = [];

            /*---RESET PRODUCTS LIST---*/
            MapService.mapProductsfiltered().then(function (markers) {
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
              $scope.productListData = [];
              angular.forEach($scope.productmarkerslist, function (item) {
                angular.forEach(item.product_details, function (newItem) {
                  var date = new Date().getTime();
                  newItem.isExpired = true;
                  if (newItem.to_date.$date.$numberLong >= date) {
                    newItem.isExpired = false;
                  }
                  if (newItem.selected)
                    newItem.selected = false;
                  $scope.productListData.push(item);
                });
              });
              $timeout(function() {
                $scope.productmarkerslist = Object.assign({}, $scope.productListData);
              });
              $rootScope.isLoading = false;
            });
            /*---//RESET PRODUCTS LIST---*/
          } else if (response.status == 0) {
            toastr.error(response.message);
          }
        });
      }
      // if ($scope.details.length <= 0) {
      //   toastr.error("Please Select Products");
      // }
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
          var refToMapMarker = _.find(markersOnMap, (m) => {
            return m.properties.id == marker.id;
          });
          // debugger;
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
            config.serverUrl + refToMapMarker.properties["image"];
          $scope.product.siteNo = refToMapMarker.properties["siteNo"];
          $scope.product.area_name = refToMapMarker.properties["area_name"];
          $scope.product.panelSize = refToMapMarker.properties["panelSize"];
          $scope.product.Comments = refToMapMarker.properties["Comments"];
          $scope.product.isCommentsExist =
            $scope.product.Comments === "" ? false : true;
          $scope.product.height = refToMapMarker.properties["height"];
          $scope.product.width = refToMapMarker.properties["width"];
          $scope.product.cancellation_policy =
            refToMapMarker.properties["cancellation_policy"];
          $scope.product.isCanelExist =
            $scope.product.cancellation_policy === "" ? false : true;
          $scope.product.billing =
            refToMapMarker.properties["billingNo"] == "no" ? "No" : "Yes";
          $scope.product.servicing =
            refToMapMarker.properties["servicingNo"] == "no" ? "No" : "Yes";
          $scope.product.ageloopLength =
            refToMapMarker.properties["ageloopLength"];
          $scope.product.isLoopLengthExist =
            $scope.product.looplength === "" ? false : true;
          $scope.product.spotLength = refToMapMarker.properties["spotLength"];
          $scope.product.isSpotLengthExist =
            $scope.product.spotLength === "" ? false : true;
          $scope.product.unitQty = refToMapMarker.properties["unitQty"];
          $scope.product.mediahhi = refToMapMarker.properties["mediahhi"];
          $scope.product.isMediaHiExist =
            $scope.product.mediahhi === "" ? false : true;
          $scope.product.vendor = refToMapMarker.properties["vendor"];
          $scope.product.strengths = refToMapMarker.properties["strengths"];
          $scope.product.address = refToMapMarker.properties["address"];
          $scope.product.city = refToMapMarker.properties["city"];
          $scope.product.notes = refToMapMarker.properties["notes"];
          $scope.product.isNotesExist =
            $scope.product.notes === "" ? false : true;
          $scope.product.title = refToMapMarker.properties["title"];
          $scope.product.type = refToMapMarker.properties["type"];
          $scope.product.negotiatedCost =
            refToMapMarker.properties["negotiatedCost"];
          $scope.product.secondImpression =
            refToMapMarker.properties["secondImpression"];
          $scope.product.firstImpression =
            refToMapMarker.properties["firstImpression"];
          $scope.product.isFirstImpExist =
            $scope.product.firstImpression === "" ? false : true;
          $scope.product.thirdImpression =
            refToMapMarker.properties["thirdImpression"];
          $scope.product.isThirdImpExist =
            $scope.product.thirdImpression === "" ? false : true;
          $scope.product.forthImpression =
            refToMapMarker.properties["forthImpression"];
          $scope.product.isForthImpExist =
            $scope.product.forthImpression === "" ? false : true;

          //$scope.product.lat = refToMapMarker.properties['lat'];
          //$scope.product.lng = refToMapMarker.properties['lng'];
          $scope.product.lat = marker.lat;
          $scope.product.lng = marker.lng;

          $scope.product.productioncost =
            refToMapMarker.properties["productioncost"];
          $scope.product.installCost = refToMapMarker.properties["installCost"];
          $scope.product.sound = refToMapMarker.properties["sound"];
          $scope.product.staticMotion =
            refToMapMarker.properties["staticMotion"];
          // $scope.product.isStaticMotionExist = $scope.product.staticMotion == $scope.product.type.name != 'Static';
          $scope.product.locationDesc =
            refToMapMarker.properties["locationDesc"];
          $scope.product.isLocationDescExist =
            $scope.product.locationDesc === "" ? false : true;
          $scope.product.zipcode = refToMapMarker.properties["zipcode"];
          $scope.product.cpm = refToMapMarker.properties["cpm"];
          $scope.product.firstcpm = refToMapMarker.properties["firstcpm"];
          $scope.product.isFirstCpmExist =
            $scope.product.firstcpm === "" ? false : true;
          $scope.product.thirdcpm = refToMapMarker.properties["thirdcpm"];
          $scope.product.isThirdCpmExist =
            $scope.product.thirdcpm === "" ? false : true;
          $scope.product.forthcpm = refToMapMarker.properties["forthcpm"];
          $scope.product.isForthCpmExist =
            $scope.product.forthcpm === "" ? false : true;
          $scope.product.sellerId = refToMapMarker.properties["sellerId"];
          $scope.product.isSellerIdExist =
            $scope.product.sellerId === "" ? false : true;
          $scope.product.audited = refToMapMarker.properties["audited"];
          $scope.product.fix = refToMapMarker.properties["fix"];
          $scope.product.description = refToMapMarker.properties["description"];
          // $scope.product.demographicsage = refToMapMarker.properties['demographicsage'];
          // $scope.product.isDescExist = $scope.product.demographicsage === ""? false:true;
          $scope.product.fliplength = refToMapMarker.properties["fliplength"];
          $scope.product.fliplength = refToMapMarker.properties["fliplength"];
          $scope.product.weekPeriod = refToMapMarker.properties["weekPeriod"];
          $scope.product.rateCard = refToMapMarker.properties["rateCard"];
          $scope.product.venue = refToMapMarker.properties["venue"];
          $scope.product.lighting = refToMapMarker.properties["lighting"];
          $scope.product.direction = refToMapMarker.properties["direction"];
          $scope.product.availableDates =
            refToMapMarker.properties["availableDates"];
          $scope.product.slots = refToMapMarker.properties["slots"];
          $scope.product.file_type = refToMapMarker.properties["file_type"];
          $scope.product.isFileTypeExist =
            $scope.product.file_type === "" ? false : true;
          $scope.product.medium = refToMapMarker.properties["medium"];
          $scope.product.isMediumExist =
            $scope.product.medium === "" ? false : true;
          $scope.product.product_newMedia =
            refToMapMarker.properties["product_newMedia"];
          $scope.product.isProductNewAgeExist =
            $scope.product.product_newAge === "" ? false : true;
          $scope.product.placement = refToMapMarker.properties["placement"];
          $scope.product.isPlacementExist =
            $scope.product.placement === "" ? false : true;
          $scope.product.minimumbooking =
            refToMapMarker.properties["minimumbooking"];
          $scope.product.imgdirection =
            refToMapMarker.properties["imgdirection"];
          $scope.product.state = refToMapMarker.properties["state"];
          $scope.product.fix = refToMapMarker.properties["fix"];
          $scope.product.minimumdays = refToMapMarker.properties["minimumdays"];
          $scope.product.length = refToMapMarker.properties["length"];
          $scope.product.network = refToMapMarker.properties["network"];
          $scope.product.nationloc = refToMapMarker.properties["nationloc"];
          $scope.product.daypart = refToMapMarker.properties["daypart"];
          $scope.product.genre = refToMapMarker.properties["genre"];
          $scope.product.reach = refToMapMarker.properties["reach"];
          // $scope.product.costperpoint = refToMapMarker.properties['costperpoint'];
          var fromtime = refToMapMarker.properties.from_date.$date.$numberLong;
          $scope.product.fromTime = moment(new Date(+fromtime)).format(
            "YYYY-MM-DD"
          );
          var endTime = refToMapMarker.properties.to_date.$date.$numberLong;
          $scope.product.endTime = moment(new Date(+endTime)).format(
            "YYYY-MM-DD"
          );
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

    $scope.$watch("ranges.selectedDateRanges", function () {});

    $scope.getProductUnavailableDates = function (product, ev) {
      // if(product.type == "Bulletin"){
      MapService.getProductUnavailableDates(product.id).then(function (
        dateRanges
      ) {
        $scope.unavailalbeDateRanges = dateRanges;
        localStorage.setItem("mindays", $scope.product.minimumdays);
        $(ev.target).parents().eq(3).find("input").trigger("click");
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
      $scope.loading = true;
      $scope.productmarkerslist = $scope.actualDataCopy.filter(function (item) {
        $scope.loading = false;
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
    $scope.leftSearch = function(booked_from, booked_to) {
      $scope.isLeftSearch = true;
      $scope.applyFilter(booked_from, booked_to);
    }
    /*--- End of Left Search Handler ---*/

    /*--- Clear Search ---*/
    $scope.clearSearch = function(clear) {
      if (clear === 'bottom') {
        $scope.searchTermAry = [{searchTerm: ''}];
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
    }
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
      startAndEndDates.forEach((item, index) => {
        paylaunchProduct.dates.push({
          startDate: moment(item.startDay).format("YYYY-MM-DD"),
          endDate: moment(item.endDay).format("YYYY-MM-DD"),
        });
      });
      // if (startAndEndDates.length == 1) {
      //     paylaunchProduct.startDate = moment(startAndEndDates[0].startDay).format('YYYY-MM-DD')
      //     paylaunchProduct.endDate = moment(startAndEndDates[0].endDay).format('YYYY-MM-DD')
      // } else {
      //     startAndEndDates.forEach((item, index) => {
      //         if (index == 0) {
      //             paylaunchProduct.startDate = moment(item.startDay).format('YYYY-MM-DD')
      //         } else if (index == (startAndEndDates.length - 1)) {
      //             paylaunchProduct.endDate = moment(item.endDay).format('YYYY-MM-DD')
      //         }
      //     })
      // }
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
      $scope.totalPriceUserSelected = 0;
      $scope.totalnumDays = 0;
      $scope.newratecard = 0;
      // if ($scope.product.type == "Digital" || $scope.product.type == "Transit Digital") {
      var productPerDay = $scope.product.rateCard / 28;
      // productPerDay = productPerDay.replace(/,/g,"")
      localStorage.removeItem("mindays");
      for (item in $scope.ranges.selectedDateRanges) {
        var startDate = moment(
          $scope.ranges.selectedDateRanges[item].startDate
        ).format("YYYY-MM-DD");
        var endDate = moment(
          $scope.ranges.selectedDateRanges[item].endDate
        ).format("YYYY-MM-DD");
        var totalDays = moment(endDate).diff(startDate, "days") + 1;
        console.log($scope.ranges);
        $scope.totalnumDays = $scope.totalnumDays + totalDays;
        $scope.totalPriceUserSelected = productPerDay * $scope.totalnumDays;
        // var mindays = $scope.product.minimumdays == "1"? 28 :  $scope.product.minimumdays == "0.75"?21: $scope.product.minimumdays == "0.5"?14:7;
        // $scope.newratecard = Math.ceil($scope.totalnumDays / mindays)*$scope.product.rateCard
      }
      if ($scope.product.fix == "Fixed") {
        var tempValue = productPerDay * $scope.product.minimumdays;
        var selectedTimes = Math.ceil(
          $scope.totalnumDays / $scope.product.minimumdays
        );
        $scope.newratecard = selectedTimes * tempValue;
      } else if ($scope.product.fix == "Variable") {
        var tempValue =
          productPerDay *
          (Number($scope.product.minimumdays) > $scope.totalnumDays
            ? $scope.product.minimumdays
            : $scope.totalnumDays);
        // var selectedTimes = Math.ceil($scope.totalnumDays / $scope.product.minimumdays);
        $scope.newratecard = Math.ceil(tempValue);
      }
      //   }else{
      //     var productPerDay = $scope.product.rateCard / 28;
      //     for(item in $scope.ranges.selectedDateRanges){
      //         var startDate = moment($scope.ranges.selectedDateRanges[item].startDate).format('YYYY-MM-DD')
      //         var endDate = moment($scope.ranges.selectedDateRanges[item].endDate).format('YYYY-MM-DD')
      //         var totalDays = moment(endDate).diff(startDate,'days') + 1
      //         $scope.totalnumDays = $scope.totalnumDays + totalDays
      //         $scope.totalPriceUserSelected =  productPerDay * $scope.totalnumDays;
      //     }
      //   }
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
    });
    $scope.toggleProductDetailSidenav = function () {
      $scope.ranges.selectedDateRanges = [];
      $scope.totalPriceUserSelected = 0;
      $scope.totalnumDays = 0;
      $scope.removeSelection();
      $mdSidenav("productDetails").close();
    };

    $scope.notifyMe = function(prodId, msg) {
      const payload = {
        "user_message": msg,
        /*"loggedinUser": JSON.parse(localStorage.loggedInUser).user_id,*/
        "loggedinUser": JSON.parse(localStorage.loggedInUser),
        "product_id": prodId
      };
      console.log(payload);
      ManagementReportService.notifyMe(payload).then(function(result) {
        if (result && result.status) {
          toastr.success(result.message);
          $("#notifyMeModal-" + prodId).modal("hide");
        } else {
          toastr.error(result.message);
        }
      });
    }

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

    /*==================================
      Map ZoomIN/ZoomOut on pinch
    ====================================*/
    $scope.zoom = 3;
    $scope.last_scale = 1;

    function zoomOnMobile(ev) {
      $timeout(() => {
        if ($scope.zoom < 16 && ev.scale > $scope.last_scale) {
          $scope.zoom = $scope.zoom + 1;
        }
        else if($scope.zoom > 2 && ev.scale < $scope.last_scale) {
          $scope.zoom = $scope.zoom - 1;
        }
      })
    }
    
    $scope.zoomOnPinch = debounce((ev) => zoomOnMobile(ev),300);

    $timeout(() => {
      var mapContainer = document.getElementById("ngmap");
      var hammerObj = new Hammer(mapContainer);
  
      hammerObj.get("pinch").set({enable: true});
      
      hammerObj.on("pinch", function (ev) {
        $scope.zoomOnPinch(ev);
      })
    });
    /*==================================
      End of Map ZoomIN/ZoomOut on pinch
    ====================================*/

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
      console.log("zoom in...." + ++$scope.inCounter);
      $scope.isHover = true;
      //$timeout(function() {
      var img, lens, result, cx, cy;
      img = document.getElementById(imgID);
      result = document.getElementById(resultID);
      console.log(result);
      if (result) {
        result.style.display = "block";
        console.log("displayed");
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
  },
]);
