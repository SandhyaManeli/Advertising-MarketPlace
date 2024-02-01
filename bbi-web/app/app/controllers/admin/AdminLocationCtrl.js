angular.module("bbManager").controller('AdminLocationCtrl', function ($scope, $http, AdminLocationService, toastr) {

  $scope.countryList = [];
  $scope.stateListForCountry = [];
  $scope.cityListForState = [];

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
  function createPageLinks(){
    var mid = Math.ceil(pageLinks/2);
    if($scope.pagination.pageCount < $scope.pagination.pageSize){
      lowest = 1;
    }
    else if($scope.pagination.pageNo >= ($scope.pagination.pageCount - mid) && $scope.pagination.pageNo <= $scope.pagination.pageCount){
      lowest = $scope.pagination.pageCount - pageLinks;
    }
    else if($scope.pagination.pageNo > 0 && $scope.pagination.pageNo <= pageLinks/2){
      lowest = 1;
    }
    else{
      lowest = $scope.pagination.pageNo - mid + 1;
    }
    highest = $scope.pagination.pageCount < $scope.pagination.pageSize ? $scope.pagination.pageCount : lowest + pageLinks;
    $scope.pagination.pageArray = _.range(lowest, highest + 1);
  }

  /*===================
  | Pagination Ends
  ===================*/

  /*===============
  | Countries
  ===============*/
  
  var getAllCountries = function(){
    AdminLocationService.getCountries().then(function (data) {
      $scope.countryList = data;
    });
  }
  getAllCountries();

  $scope.saveCountry = function () {
    AdminLocationService.saveCountry($scope.country).then(function (data) {
      if (data.status == 1) {
        getAllCountries();
        $scope.country = null;
        if($scope.countryForm){
          $scope.countryForm.$setPristine()
          $scope.countryForm.$setUntouched()  
        }
        toastr.success('Country added to database successfully!');
      }
      else if(data.status == 0){
        $scope.saveCountryErrors = data.message;
      }
    });
  }

  $scope.editCountry = function (country) {
    $scope.country = country;
  }

  $scope.deleteCountry = function (country) {
    AdminLocationService.deleteCountry(country.id).then(function (result) {
      if (result.status == 1) {
        getAllCountries();
        toastr.success(result.message);
      }
      else {
        toastr.error(result.message);
      }
    });
  }

  /*================
  | Countries Ends
  ================*/

  /*==============
  | States
  ==============*/

  var getAllStates = function(){
    AdminLocationService.getAllStates().then(function (data) {
      $scope.stateList = data;
    });
  }
  getAllStates();

  $scope.getStateList = function (countryId) {
    AdminLocationService.getStates(countryId).then(function (data) {
      $scope.stateListForCountry = data;
    });
  }

  $scope.saveState = function () {
    AdminLocationService.saveState($scope.state).then(function (data) {
      if (data.status == 1) {
        toastr.success('State added to database successfully!');
        getAllStates();
        $scope.state = null;
        if($scope.stateform){
          $scope.stateform.$setPristine()
          $scope.stateform.$setUntouched()
        }
      }
      else if(data.status == 0) {
        $scope.stateErrors ={
          errorMsg : data.message
        } 
      }
    });
  }

  $scope.editState = function (state) {
    $scope.state = {};
    $scope.state.id = state.id;
    $scope.state.country_id = null;
    $scope.state.name = state.name;
  }

  $scope.deleteState = function (state) {
    AdminLocationService.deleteState(state.id).then(function (result) {
      if (result.status == 1) {
        toastr.success(result.message);
        getAllStates();
      }
      else {
        toastr.error(result.message);
      }
    });
  }

  /*==============
  | States Ends
  ==============*/

  /*==============
  | Cities
  ==============*/

  var getAllCities = function(){
    AdminLocationService.getAllCities().then(function (data) {
      $scope.cityList = data;
    });
  }
  getAllCities();

  $scope.getCityList = function (stateId) {
    AdminLocationService.getCities(stateId).then(function (data) {
      $scope.cityListForState = data;
    });
  }

  $scope.saveCity = function () {
    AdminLocationService.saveCity($scope.city).then(function (data) {
      if (data.status == 1) {
        $scope.cityErrors = null;
        toastr.success('City added to database successfully!');
        getAllCities();
        $scope.city = null;
        if ($scope.cityform) {
          $scope.cityform.$setPristine()
          $scope.cityform.$setUntouched()
        }
      }   
      else if(data.status == 0){
        $scope.cityErrors = data.message;
      }
    });
  }

  $scope.editCity = function (city) {
    $scope.city = {};
    $scope.city.id = city.id;
    $scope.city.country_id = null;
    $scope.city.state_id = null;
    $scope.city.name = city.name;
  }

  $scope.deleteCity = function (city) {
    AdminLocationService.deleteCity(city.id).then(function (result) {
      if (result.status == 1) {
        getAllCities();
        toastr.success(result.message);
      }
      else {
        toastr.error(result.message);
      }
    });
  }

  /*==============
  | Cities Ends
  ==============*/

  /*==============
  | Areas
  ==============*/

  // using a scope function because we call it from pagination logic in html
  $scope.getAllAreas = function(){
    AdminLocationService.getAllAreas($scope.pagination.pageNo, $scope.pagination.pageSize).then(function (data) {
      $scope.areas = data.areas;
      $scope.pagination.pageCount = data.page_count;
      createPageLinks();
    });
  }
  $scope.getAllAreas();
  
  $scope.saveArea = function () {
    AdminLocationService.saveArea($scope.area).then(function (data) {
      if (data.status == 1) {
        toastr.success('Area added to database successfully!');
        $scope.areaErrors = null;
        $scope.getAllAreas();
        $scope.area = null;
        if ($scope.areaform) {
          $scope.areaform.$setPristine()
          $scope.areaform.$setUntouched()
        }

      }
      else if(data.status == 0){
        $scope.areaErrors = data.message;
      }
    });
  }

  $scope.editArea = function (area) {
    $scope.area = {};
    $scope.area.id = area.id;
    $scope.area.country_id = null;
    $scope.area.state_id = null;
    $scope.area.city_id = null;
    $scope.area.name = area.name;
    $scope.area.pincode = area.pincode;
    $scope.area.lat = area.lat;
    $scope.area.lng = area.lng;
  }

  $scope.deleteArea = function (area) {
    AdminLocationService.deleteArea(area.id).then(function (result) {
      if (result.status == 1) {
        toastr.success(result.message);
        $scope.getAllAreas();
      }
      else {
        toastr.error(result.message);
      }
    });
  }

  $scope.resetAreaForm = function () {
    $scope.area = {};
  }

  /*
  * Filtering Areas
  */
  // $scope.simulateQuery = false;
  $scope.isDisabled    = false;
  // $scope.querySearch   = querySearch;
  // $scope.selectedItemChange = selectedItemChange;
  // $scope.searchTextChange   = searchTextChange;

  $scope.areaChanged = function(query) {
    if(!query) {
      $scope.getAllAreas();
    }
  };

  $scope.areaSearch = function(query) {
    var loggedInUser = JSON.parse(localStorage.getItem('loggedInUser'));
    var payload = {
      search_term: query.toLowerCase(),
      email: btoa(loggedInUser.email)
    }
    return AdminLocationService.searchAreas(payload).then(function(res){
      $scope.pagination.pageCount = 1;
      $scope.areas = res.data;
      return res.data;
    });
  };

  $scope.viewSelectedArea = function(area) {
    if(area) {
      $scope.pagination.pageCount = 1;
      $scope.areas = [area];
    } else {
      $scope.getAllAreas();
    }
  };

  function selectedItemChange(item) {
  }
  /*
  * Filtering Areas ends
  */

  /*==============
  | Areas Ends
  ==============*/
});