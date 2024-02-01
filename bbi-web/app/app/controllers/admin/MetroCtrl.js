angular.module("bbManager").controller('AdminMetroCtrl', function($scope, $mdDialog, $rootScope,Upload, ProductService, AdminLocationService, AdminMetroService, toastr,MetroService,$window){

  /*=============================
  | Global variables
  =============================*/
  $scope.forms = [];
  $scope.files = [];
  /*=============================
  | Global variables end
  =============================*/

  /*==============================
  | Popup and Sidenav controls
  ==============================*/
  $scope.closeDialog = function() {
    $mdDialog.hide();
  }

  $scope.showFormatForm = function (ev) {
    $scope.formatPopupType = "metro";
    $mdDialog.show({
      templateUrl: 'views/admin/add-format-popup.html',
      fullscreen: $scope.customFullscreen,
      clickOutsideToClose: true,
      preserveScope: true,
      scope: $scope
    });
  };
  $scope.editFormat = function(format){
    $scope.format = format;
    $mdDialog.show({
      templateUrl: 'views/admin/add-format-popup.html',
      fullscreen: $scope.customFullscreen,
      clickOutsideToClose: true,
      preserveScope: true,
      scope: $scope
    });
  }


  /*$scope.addFormat = function(format){
    $scope.format = format;
    $mdDialog.show({
      templateUrl: 'views/admin/add-format-popup.html',
      fullscreen: $scope.customFullscreen,
      preserveScope: true,
      scope: $scope
    });
  }*/
  $scope.selectPackage = function (ev) {
    $mdDialog.show({
      templateUrl: 'views/selectpack.html',
      fullscreen: $scope.customFullscreen,
      clickOutsideToClose: true
    })
  };
  /*================================
  | Popup and Sidenav controls end
  ================================*/

  /*===============================
  | Formats section
  ===============================*/
  var getFormatList = function(obj){
    ProductService.getFormatList(obj).then(function(result){
      $scope.formatList = result;
      $scope.package.format_id = result[0].id;
    });
  }
  

  $scope.addFormat = function(format){
    Upload.upload({
      url: config.apiPath + '/format',
      data: {image: $scope.files.image, format: format}
    }).then(function (result) {
      if(result.data.status == 1){
        $scope.format = {};
        toastr.success(result.data.message);
        getFormatList({type: "metro"});
        $mdDialog.cancel();
      }
      else if(result.data.status == 0){
        $scope.addFormatErrors = result.data.message;
      }
    }, function (resp) {
      toastr.error("somthing went wrong please try again later");
    }, function (evt) {
      var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
    });
  } 
  /*===============================
  | Formats section ends
  ===============================*/

  /*==============================
  | Corridors
  ==============================*/
  // $scope.selectedCorridorCity = {};
  $scope.citySearch = function(query){
    return AdminLocationService.searchCities(query.toLowerCase()).then(function(res){
      return res;
    });
  }

   $scope.citySearchforEdit = function(query){
    return AdminLocationService.searchCities(query.toLowerCase()).then(function(res){
      return res[0];
    });
  }

  function getMetroCorridors(){
    AdminMetroService.getMetroCorridors().then(function(result){
      $scope.metroCorridorList = result;
      $scope.package.corridor_id = $scope.metroCorridorList[0].id;

    });
  }
  getMetroCorridors();
  $scope.saveCorridor = function(corridor){
    if(corridor.id){
       if($scope.selectedCorridorCity){
          corridor.city_id = $scope.selectedCorridorCity.id;
       }
    }else{
      corridor.city_id = $scope.selectedCorridorCity.id;
    }
    
    AdminMetroService.saveCorridor(corridor).then(function(result){
      if(result.status == 1){
        getMetroCorridors();  
        toastr.success(result.message);
        $scope.corridor = null;
        $scope.addCorridorform.$setPristine()
        $scope.addCorridorform.$setUntouched() 
        $scope.selectedCorridorCity = null;
        $scope.editCorridor = null;
        $scope.exitpayment = null;
      }
      else{
        if(result.message.constructor === Array){
          $scope.addCorridorErrors = result.message;
        }
        else{
          toastr.error(result.message);
        }
      }
    });
  }

  $scope.editcorridors = function (corridor) {
    $scope.corridor = {};
    $scope.corridor.id = corridor.id;
    $scope.corridor.name = corridor.name;  
    $scope.corridor.city_id = corridor.city_id;
    $scope.corridor.city_name = corridor.city_name;  
    $scope.corridor.from = corridor.from;
    $scope.corridor.to =   corridor.to;
  }

  $scope.deleteCorridors = function (c_id) {
    if ($window.confirm("Are you really want to delete this corridor?")) {
      AdminMetroService.deleteCorridor(c_id).then(function (result) {
        if (result.status == 1) {
          getMetroCorridors();
          toastr.success(result.message);
        }
        else {
          toastr.error(result.message);
        }
      });
    }
  }
  /*==============================
  | Corridors end
  ==============================*/

  /*==============================
  | Packages
  ==============================*/
  function getMetroPackages(){
    AdminMetroService.getMetroPackages().then(function(result){
      $scope.metroPackageList = result;
    });
  }
  $scope.savePackage = function(package){
    AdminMetroService.savePackage(package).then(function(result){
      if(result.status == 1){
        getMetroPackages();
        toastr.success(result.message);
      }
      else{
        if(result.message.constructor === Array){
          $scope.addPackageErrors = result.message;
        }
        else{
          toastr.error(result.message);
        }
      }
    });
  }
   

   $scope.package = {};
   /*$scope.package.months = 1;
   $scope.package.days = 0;
   $scope.package.price = 1;
   $scope.package.max_trains =1;
   $scope.package.max_slots =0;*/
   //$scope.package.format_id = $scope.formatList[0].id;
   // $scope.package.corridor_id = $scope.metroCorridorList[0].id;


  $scope.editPackage = function (package) {
    $scope.package = {};
    $scope.package.id = package.id;
    $scope.package.name = package.name;
    $scope.package.format_id = package.format_id;
    $scope.package.corridor_id = package.corridor_id ;
   // $scope.package.months = package.months;
    //$scope.package.days = package.days;
    $scope.package.price = package.price;
    $scope.package.max_trains = package.max_trains;
    $scope.package.max_slots = package.max_slots;
  }

  

   $scope.deletePackage = function (p_id) {
    if ($window.confirm("Are you really want to delete this package?")) {
      AdminMetroService.deletePackage(p_id).then(function (result) {
        if (result.status == 1) {
          getMetroPackages();
          toastr.success(result.message);
        }
        else {
          toastr.error(result.message);
        }
      });
    }
  }

  
  /*==============================
  | Packages end
  ==============================*/

  /*================================
  | Page based initial loads
  ================================*/
  if($rootScope.currStateName == 'admin.metro-formats'){
    getFormatList({type: "metro"});
  }
  if($rootScope.currStateName == 'admin.metro-packages'){
    getFormatList({type: "metro"});
    getMetroCorridors();
    getMetroPackages();
  }
  if($rootScope.currStateName == 'admin.metro-corridors'){
    getMetroCorridors();
  }
  /*================================
  | Page based initial loads end
  ================================*/
  
});