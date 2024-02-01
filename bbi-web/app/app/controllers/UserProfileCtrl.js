angular.module("bbManager").controller('UserProfileCtrl', function($scope, $stateParams, $window, UserService, Upload, config) {
  $scope.profileDetails={
      "mongo_id": "",
      "first_name": "",
      "last_name":"",
      "company_name":"",
      "name":"",
      "email":"",
      "phone":"",
  };
  $scope.additionalData={
    "mongo_id": "",
    "first_name": "",
    "last_name":"",
    "company_name":"",
    "address":"",
    "street":"",
    "city":"",
    "zipcode":"",
    "website":""
  };
  $scope.disableSave=false;
  $scope.getLoggedInUserProfile = function(){
    $scope.loading= true;
    UserService.getProfile().then(function(result){
      $scope.userProfile = result; 
      $scope.loading= false;
      $scope.additionalData={
        "mongo_id": $scope.userProfile.user.mongo_id,
        "first_name": $scope.userProfile.user.first_name,
        "last_name":$scope.userProfile.user.last_name,
        "company_name":$scope.userProfile.user.company_name,
        "address":"",
        "street":"",
        "city":"",
        "zipcode":"",
        "website":""
      };
      if($scope.userProfile.user.address=== null || $scope.userProfile.user.address===undefined || $scope.userProfile.user.street=== null || $scope.userProfile.user.street===undefined){
        $scope.disableSave=false;
      } 
      else{
        $scope.disableSave=true;
      }  
      if($scope.disableSave){
        $scope.additionalData.address = $scope.userProfile.user.address;
        $scope.additionalData.street = $scope.userProfile.user.street;
        $scope.additionalData.city = $scope.userProfile.user.city;
        $scope.additionalData.zipcode = $scope.userProfile.user.zipcode;
        $scope.additionalData.website = $scope.userProfile.user.website;
        $scope.additionalData.mongo_id =  $scope.userProfile.user.mongo_id;
        $scope.additionalData.first_name =  $scope.userProfile.user.first_name;
        $scope.additionalData.last_name =  $scope.userProfile.user.last_name;
        $scope.additionalData.company_name =  $scope.userProfile.user.company_name;
        $scope.additionalData.name =  $scope.userProfile.user.name;
        $scope.additionalData.email =  $scope.userProfile.user.email;
        $scope.additionalData.phone =  $scope.userProfile.user.phone;
      }
      else{
        $scope.additionalData.address = "";
        $scope.additionalData.street = "";
        $scope.additionalData.city = "";
        $scope.additionalData.zipcode = "";
        $scope.additionalData.website = "";
      }
    });
   
  }
  $scope.getLoggedInUserProfile();

  $scope.hoardingitems=[
    {
      cname:'Flipkart',
      phone:'(+91) 9878564523',
      email:'Lorem.ipsum.com',
      address:'K67/68, Sonal Heavy Indl Est,Lane Extn,Banjara Hills',
    },
    {
    
      cname:'LG',
      phone:'(+91) 9878564523',
      email:'Lorem.ipsum.com',
      address:'K67/68, Sonal Heavy Indl Est,Lane Extn,Banjara Hills',
    },
    {
      cname:'Iphone',
      phone:'(+91) 9878564523',
      email:'Lorem.ipsum.com',
      address:'K67/68, Sonal Heavy Indl Est,Lane Extn,Banjara Hills',
    }
  ];

  $scope.limit= 5;
  $scope.loadMore = function() {
    $scope.limit = $scope.items.length
  };

  // $scope.uploadProfilePic = function () {
  //   Upload.upload({
  //     url: config.apiPath + '/update-profile-pic',
  //     data: { profile_pic: $scope.files.image}
  //   }).then(function (result) {
  //     if(result.data.status == "1"){
  //       $window.location.reload();   
  //     }
  //   }, function (resp) {
  //   }, function (evt) {
  //     var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
  //   });
  // };
$scope.editProfile=function(profileDetails){
  $scope.profileDetails={
    "mongo_id":  $scope.userProfile.user.mongo_id,
    "first_name": $scope.userProfile.user.first_name,
    "last_name":$scope.userProfile.user.last_name,
    "company_name": $scope.userProfile.user.company_name,
    "name": $scope.userProfile.user.name,
    "email":$scope.userProfile.user.email,
    "phone":$scope.userProfile.user.phone,
}
}
  $scope.updateProfile = function(profileDetails){
    $scope.profileDetails={
      "mongo_id":  profileDetails.mongo_id,
      "first_name": profileDetails.first_name,
      "last_name":profileDetails.last_name,
      "company_name": profileDetails.company_name,
      "name": profileDetails.name,
      "email":profileDetails.email,
    "phone":profileDetails.phone,
  }
  $scope.loading= true;
    UserService.updateProfileData(profileDetails).then(function(result){
      $scope.userProfile.user.mongo_id = profileDetails.mongo_id;
      $scope.userProfile.user.company_name = profileDetails.company_name;
      $scope.userProfile.user.name = profileDetails.name;
      $scope.userProfile.user.last_name = profileDetails.last_name;
      $scope.userProfile.user.first_name = profileDetails.first_name;
      $scope.userProfile.user.email = profileDetails.email;
      $scope.userProfile.user.phone = profileDetails.phone;
      $scope.loading= false;
    })
    $scope.getLoggedInUserProfile();
    $('#editProfile').modal('hide');
  }
  $scope.additionProfileData = function(additionalData){
    $scope.additionalData={
      "address":additionalData.address,
      "street":additionalData.street,
      "city":additionalData.city,
      "zipcode":additionalData.zipcode,
      "website":additionalData.website,
      "mongo_id":   $scope.userProfile.user.mongo_id,
      "first_name":  $scope.userProfile.user.first_name,
      "last_name": $scope.userProfile.user.last_name,
      "company_name":  $scope.userProfile.user.company_name,
      "name":  $scope.userProfile.user.name,
      "email": $scope.userProfile.user.email,
    "phone": $scope.userProfile.user.phone,
  }
    UserService.updateProfileData(additionalData).then(function(result){
      $scope.additionalData.address = additionalData.address;
      $scope.additionalData.street = additionalData.street;
      $scope.additionalData.city = additionalData.city;
      $scope.additionalData.zipcode = additionalData.zipcode;
      $scope.additionalData.website = additionalData.website;
      $scope.additionalData.mongo_id =  $scope.userProfile.user.mongo_id;
      $scope.additionalData.first_name =  $scope.userProfile.user.first_name;
      $scope.additionalData.last_name =  $scope.userProfile.user.last_name;
      $scope.additionalData.company_name =  $scope.userProfile.user.company_name;
      $scope.additionalData.name =  $scope.userProfile.user.name;
      $scope.additionalData.email =  $scope.userProfile.user.email;
      $scope.additionalData.phone =  $scope.userProfile.user.phone;
    })
    $scope.getLoggedInUserProfile();
    console.log('additionalData', additionalData)
  }
$scope.editAdditionalData=function(userProfile){
  $scope.additionalData={
    "address": $scope.userProfile.user.address,
    "street": $scope.userProfile.user.street,
    "city": $scope.userProfile.user.city,
    "zipcode": $scope.userProfile.user.zipcode,
    "website": $scope.userProfile.user.website,
    "mongo_id":   $scope.userProfile.user.mongo_id,
    "first_name":  $scope.userProfile.user.first_name,
    "last_name": $scope.userProfile.user.last_name,
    "company_name":  $scope.userProfile.user.company_name,
    "name":  $scope.userProfile.user.name,
    "email": $scope.userProfile.user.email,
  "phone": $scope.userProfile.user.phone,
}
$scope.disableSave=false;
}
});
