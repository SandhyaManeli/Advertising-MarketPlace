angular.module("bbManager").controller('OwnerBulkUploadCtrl', function ($scope, $mdDialog, $mdSidenav, $stateParams, $rootScope, $window, MapService, OwnerProductService,$auth, ProductService, OwnerLocationService, OwnerCampaignService, Upload, config, toastr, $state, $location) 
{
    $scope.ImageList = {};
    function getBulkUploadList() {
        OwnerProductService.getBulkUploadList().then(function(result) {
            $scope.ImageList = result.bulk_images;
        });
    };
    getBulkUploadList();
    $scope.ProductTypes = [
      {
        name: "Select product type",
      },
        {
          name: "Static",
        },
        {
          name: "Digital",
        },
        {
          name: "Digital/Static",
        },
        {
          name: "Media",
        },
      ];
      $scope.type = $scope.ProductTypes[0];
      $scope.$watch("bulk.image", function (ctrl) {
        var fileUpload = ctrl;
        if (typeof fileUpload[0] != "undefined") {
          $scope.resizeTextImg = false;
          var reader = new FileReader();
          reader.readAsDataURL(fileUpload[0]);
          reader.onload = function (e) {
            var image = new Image();
            image.src = e.target.result;
            image.onload = function () {
              var height = this.height;
              var width = this.width;
              if (width >= 1280 && height >= 960) {
                $scope.resizeTextImg = false;
                // alert("At least you can upload a 1280 px *960 px size.");
                // return false;
              } else {
                $scope.resizeTextImg = true;
                $scope.bulk.image = "";
                toastr.error(
                  "Uploaded image has valid Width 1280px and Height 960px."
                );
                return true;
              }
            };
          };
        }
      });
    //   if ($scope.image.length !== 0) {
    //     angular.forEach($scope.image, function (imageFile) {
    //       var tempObj = {};
    //       if ($scope.imageFileTypes.indexOf(imageFile.type) === -1) {
    //         toastr.error("Please select image");
    //         tempObj.image = imageFile;
    //         invalidImageFormats.push(tempObj);
    //       }
    //     });
    //   }
      $scope.requestAddProduct = function () {
        //validating the selected upload images
        var invalidImageFormats = [];
        $scope.imageFileTypes=["image/jpg", "image/jpeg", "image/png", "image/gif", "image/svg+xml"];
        if($scope.bulk.image.length !== 0){
          angular.forEach($scope.bulk.image , function(imageFile){
            var tempObj = {};
            if($scope.imageFileTypes.indexOf(imageFile.type) === -1){
              toastr.error("Please select image.");
              tempObj.image = imageFile;
              invalidImageFormats.push(tempObj);
            }
          });
        }
          //save product only if there are no invalid image formats
          if(invalidImageFormats.length==0){
    //         var payload = $auth.getPayload();
    // var userData = payload.user;
    // var userMongoData = payload.userMongo;
    // var user = localStorage.getItem("loggedInUser");
    // var parsedData = JSON.parse(user);
            Upload.upload({
              url: config.apiPath + '/save-bulk-upload-images',
              data: { image: $scope.bulk.image,
                type:$scope.type.name,
              }
            }).then(function (result) {
                getBulkUploadList();
              if (result.data.status == "1") {
                toastr.success(result.data.message);
                document.getElementById("bulkUpload").classList.toggle("show");
                
              }
              $scope.bulk.image='';
              $scope.type=$scope.ProductTypes[0];
            },function(errorCallback){
              if (errorCallback.status == 400) {
                  $scope.errormsg = errorCallback.data;
                  if (errorCallback.data.status == 1) {
                    toastr.success(errorCallback.data.message);
                    addbulkProduct()
                  }
                  else {
                    $scope.displayErrorMsg=true;
                    $scope.errorMsg=errorCallback.data.message;
                  }
              }
          });
          }
      };
  });