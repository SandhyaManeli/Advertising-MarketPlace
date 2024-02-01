angular.module("bbManager").controller('UserMgmtCtrl', function ($scope, $mdDialog, $http, $rootScope, $auth, $stateParams,$location, toastr, AdminUserService, AdminUserMgmtService, $stateParams) {

  $scope.msg = {};
  $scope.forms = {};

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

  $scope.registrationPopup = function (ev) {
    $mdDialog.show({
      templateUrl: 'views/admin/registration-popup.html',
      fullscreen: $scope.customFullscreen,
      clickOutsideToClose: true,
      preserveScope: true,
      scope: $scope
    })
  };

  /*
  ==== checking if user has owner role ====
  */
  $scope.isUserOwner = _.indexOf(_.pluck($auth.getPayload().user.roles, "name"), "owner") != -1;
  /*
  ==== checking if user has owner role ends ====
  */

  /* 
  ==== Switching between user and agency pop up form ====
  */
  $scope.userChecked = true;

  $scope.showUser = function () {
    $scope.userChecked = true;    
  }
  $scope.showAgency = function () {
    $scope.userChecked = false;
  }
  /* 
  ==== Switching between user and agency pop up form ends ====
  */

  /* 
  ======== Users Grid ========
  */

  // $scope.getUsers = function(){
  //   AdminUserService.getUsers($scope.pagination.pageNo, $scope.pagination.pageSize).then(function (response) {
  //     $scope.userList = response.users;
  //     $scope.pagination.pageCount = response.page_count;
  //   });
  // }
  // $scope.getUsers();

  /* 
  ======== Users Grid ends ========
  */


  /* 
  ======== Agency Grid ========
  */

  $scope.getAgencies = function(){
    AdminUserService.getAgencies().then(function (response) {
      $scope.agencyList = response;
    });
  }
  $scope.getAgencies();

  /* 
  ======== Agency Grid ends ========
  */


  /* 
  ======== Adding New User ========
  */
  $scope.addUser = function () {
    AdminUserService.saveUser($scope.user).then(function(result){
      if(result.status == 1){
        $scope.getUsers();
        $mdDialog.cancel();
        toastr.success('User has been created successfully.');
      }
      else if(result.status == 0) {
          $scope.registerUserErrors = result.message;
        }
          });
        }
  /* 
  ======== Adding New User ends ========
  */

  /* 
  ======== Deleting User ========
  */
  $scope.deleteUser = function (userMongoid) {    
    AdminUserService.deleteUser(userMongoid).then(function(result){
      if(result.status == 1){
        $scope.getUsers();
        $mdDialog.cancel();
        toastr.success(result.message);
      }
      else{
        toastr.error(result.message);
      }
    });
  }
  /* 
  ======== Deleting User ends ========
  */

  /* 
  ======== Toggling user activation ========
  */
  $scope.toggleUserActivation = function(userMId){
    AdminUserService.toggleActivationUser(userMId).then(function(result){
      if(result.status == 1){
        getAllUsers();
        toastr.success(result.message);
      }
      else{
        toastr.error(result.message);
      }
    });
  }
  /* 
  ======== Toggling user activation ends ========
  */

  /* 
  ======== Activating New Super Admin ========
  */
  $scope.toggleSuperAdminActivation = function(userMId){
    AdminUserService.toggleActivationUser(userMId).then(function(result){
      if(result.status == 1){
        $scope.selectedUser.user_details.activated = !$scope.selectedUser.user_details.activated;
        getAllUsers();
        toastr.success(result.message);
      }
      else{
        toastr.error(result.message);
      }
    });
  }
  /* 
  ======== Activating New Super Admin ends ========
  */

  /* 
  ======== Adding New Agency ========
  */
  $scope.addAgency = function () {
    AdminUserService.saveAgency($scope.agency).then(function(result){
      if(result.status == 1){
        AdminUserService.getAgencies().then(function (response) {    
          $scope.gridAgency.data = response;
        });
        $mdDialog.cancel();
        toastr.success(result.message);
      }
      else if(result.status == 0){
        $scope.addAgencyErrors = result.message;
      }
    },function(error){
      toastr.error("somthing went wrong try agin later!");
    });
  }

  /* 
  ======== Adding New Agency ends========
  */
  /**
   * Get Client
   */
  var getUrl = function(){
    console.log('hello', $location.$$url.includes("a=u"))
    $scope.selectedIndex = 0;
    if($location.$$url.includes("a=u")){
      $scope.selectedIndex = 1;
    }
  }
  getUrl();
  var getAllClients = function(){
    AdminUserMgmtService.getAllClients().then(function(result){
      $scope.allClients = result.map( e => {
        return {
          ...e,
          email : e.contact_email != '' && e.contact_email != undefined?e.contact_email:e.email != '' && e.email != undefined?e.email: ''
        }
      });
      if ($stateParams.clientID) {
        var client = result.find((item) => item.id == $stateParams.clientID)
        if (client) {
          $scope.showUserDetailsPopup(client.client_id,client.super_admin_m_id)
        }
      }
    });
  }
  getAllClients();

  $scope.showUserDetailsPopup = function(clientId, userMId){
    $scope.selectedClientId = clientId;
    AdminUserMgmtService.getUserDetailsWithRoles(userMId).then(function(result){
      if(result.status == 0){
        toastr.error(result.message);
      }
      else{
        $scope.selectedUser = result;
        $scope.selectedRolesForUser = _.pluck(result.user_roles, 'id');
        $mdDialog.show({
          templateUrl: 'views/admin/user-details-popup.html',
          fullscreen: $scope.customFullscreen,
          clickOutsideToClose: true,
          preserveScope: true,
          scope: $scope
        });
      }
    });
  }

  $scope.superAdminToAssign = {};
  $scope.showAssignSuperAdminPopup = function(clientId){
    $scope.selectedClientId = clientId;
    $mdDialog.show({
      templateUrl: 'views/admin/assign-super-admin-popup.html',
      fullscreen: $scope.customFullscreen,
      clickOutsideToClose: true,
      preserveScope: true,
      scope: $scope
    });
  }


  //SORTING FOR COMPAINES 
$scope.sortAsc =function(headingName, type){
  $scope.upArrowColour = headingName;
  $scope.sortType ="Asc";
  if (type=="string"){
    $scope.newOfferData = $scope.allClients.map(e=>{
    return {
    ...e,
    name: e.name,
    email: e.email
    }
    })
    $scope.allClients = [];
    $scope.allClients = $scope.newOfferData.sort((a,b) =>{
      console.log(a[headingName])
      if(a[headingName] != undefined){
      return  a[headingName].localeCompare(b[headingName], undefined, {
        numeric: true,
        sensitivity: 'base'
      });
      }
    })
    
    // $scope.productList = $scope.newOfferData;
    }
  $scope.allClients = $scope.allClients.sort((a,b)=>{

       if(type == 'boolean'){
         return a[headingName] ? 1 : -1 
     }
      else {
         return a[headingName] - b[headingName]
      }
  })
  console.log($scope.allClients)
}
$scope.sortDsc =function(headingName, type){
  $scope.downArrowColour = headingName;
  $scope.sortType ="Dsc";
  if (type=="string"){
    $scope.newOfferData = $scope.allClients.map(e=>{
    return {
    ...e,
    name: e.name,
    email: e.email
    }
    })
    $scope.allClients = [];
    $scope.allClients = $scope.newOfferData.sort((a,b) =>{
      console.log(a[headingName])
      if(b[headingName] != undefined){
      return  b[headingName].localeCompare(a[headingName], undefined, {
        numeric: true,
        sensitivity: 'base'
      });
    }
    })
    
    // $scope.RfpData = $scope.newOfferData;
    }
 $scope.allClients = $scope.allClients.sort((a,b)=>{

      if(type == 'boolean'){
         return a[headingName] ? -1 : 1 
     }
     else {
         return  b[headingName] - a[headingName] 
     }
 })
 console.log($scope.allClients)
};


//SORTING FOR COMPAINES ENDS


  //SORTING FOR USERS
  $scope.sortAscc =function(headingName, type){
    $scope.upArrowColour = headingName;
    $scope.sortType ="Ascc";
    if (type=="string"){
      $scope.newOfferData = $scope.allUsers.map(e=>{
      return {
      ...e,
      first_name: e.first_name,
      company_type: e.company_type,
      email: e.email,
      company_name: e.company_name
      }
      })
      $scope.allUsers = [];
      $scope.allUsers = $scope.newOfferData.sort((a,b) =>{
        console.log(a[headingName])
        return  a[headingName].localeCompare(b[headingName], undefined, {
          numeric: true,
          sensitivity: 'base'
        });
      })
      
      // $scope.productList = $scope.newOfferData;
      }
    $scope.allUsers = $scope.allUsers.sort((a,b)=>{
  
         if(type == 'boolean'){
           return a[headingName] ? 1 : -1 
       }
        else {
           return a[headingName] - b[headingName]
        }
    })
    console.log($scope.allUsers)
  }
  $scope.sortDscc =function(headingName, type){
    $scope.downArrowColour = headingName;
    $scope.sortType ="Dscc";
    if (type=="string"){
      $scope.newOfferData = $scope.allUsers.map(e=>{
      return {
      ...e,
      first_name: e.first_name,
      company_type: e.company_type,
      email: e.email,
      company_name: e.company_name
      }
      })
      $scope.allUsers = [];
      $scope.allUsers = $scope.newOfferData.sort((a,b) =>{
        console.log(a[headingName])
        return  b[headingName].localeCompare(a[headingName], undefined, {
          numeric: true,
          sensitivity: 'base'
        });
      })
      
      // $scope.RfpData = $scope.newOfferData;
      }
   $scope.allUsers = $scope.allUsers.sort((a,b)=>{
  
        if(type == 'boolean'){
           return a[headingName] ? -1 : 1 
       }
       else {
           return  b[headingName] - a[headingName] 
       }
   })
   console.log($scope.allUsers)
  };
  
  
  //SORTING FOR USERS ENDS


  $scope.setSuperAdminForClient = function(){
    var obj = {
      'client_id': $scope.selectedClientId,
      'super_admin_email': $scope.superAdminToAssign.email
    };
    AdminUserMgmtService.assignSuperAdminToClient(obj).then(function(result){
      if(result.status == 0){
        toastr.error(result.message);
      }
      else{
        $scope.selectedClientId = null;
        $scope.superAdminToAssign = {};
        getAllClients();
        $mdDialog.cancel();
        toastr.success(result.message);
      }
    });
  }

  $scope.resendOwnerInviteEmail = function(){
    var obj = {
      'client_id': $scope.selectedClientId,
      'super_admin_email': $scope.selectedUser.user_details.email
    };
    AdminUserMgmtService.resendOwnerInviteEmail(obj).then(function(result){
      if(result.status == 0){
        toastr.error(result.message);
      }
      else{
        toastr.success(result.message);
      }
    });
  }


  /*===================
  | User Section
  ===================*/
  
  /**
   * Get Users
   */
  var getAllUsers = function(){
    $scope.loading= true;
    AdminUserMgmtService.getAllUsers().then(function(result){
      $scope.allUsers = result;
      $scope.loading= false;
    });
  }
  getAllUsers();

  $scope.showAddUserPopup = function(){
    $mdDialog.show({
      templateUrl: 'add-bbi-user-popup.html',
      fullscreen: $scope.customFullscreen,
      clickOutsideToClose: true,
      preserveScope: true,
      scope: $scope
    });
  }
  
  $scope.newBBIUser = {};
  $scope.sendInviteToBBIUser = function(){
    AdminUserMgmtService.sendInviteToBBIUser($scope.newBBIUser).then(function(result){
      if(result.status == 1){
        toastr.success(result.message);
      }
      else{
        toastr.error(result.message);
      }
    });
  }

  $scope.viewUserRoles = function(userMId){
    AdminUserMgmtService.getUserDetailsWithRoles(userMId).then(function(result){
      if(result.status == 0){
        toastr.error(result.message);
      }
      else{
        $scope.selectedUser = result;
        $scope.selectedRolesForUser = _.pluck(result.user_roles, 'id');
        $mdDialog.show({
          templateUrl: 'views/admin/user-details-popup.html',
          fullscreen: $scope.customFullscreen,
          clickOutsideToClose: true,
          preserveScope: true,
          scope: $scope
        });
      }
    });
  }

  $scope.resendUserInviteEmail = function(){
    $scope.newBBIUser.email = $scope.selectedUser.user_details.email;
    AdminUserMgmtService.sendInviteToBBIUser($scope.newBBIUser).then(function(result){
      if(result.status == 1){
        toastr.success(result.message);
      }
      else{
        toastr.error(result.message);
      }
    });
  }

  /*===================
  | User Section ends
  ===================*/


  /*===================
  | Roles Section
  ===================*/

  $scope.selectedRoles = [];

  /**
   * Get Roles
   */
  var getAllRoles = function(){
    $scope.loading= true;
    AdminUserMgmtService.getAllRoles().then(function(result){
      $scope.allRoles = result;
      $scope.loading= false;
    });
  }
  getAllRoles();

  $scope.selectedRole = 0;
  $scope.toggleRoleSelection = function(roleId){
    if($scope.selectedRole == roleId){      
      $scope.selectedRole = 0;
      $scope.selectedPermissions = [];
    }
    else{
      $scope.selectedRole = roleId;
      getRoleDetails($scope.selectedRole);
    }
  }

  $scope.toggleRoleSelectionForUser = function(roleId){
    if($scope.selectedRolesForUser.indexOf(roleId) > -1){
      var i = $scope.selectedRolesForUser.indexOf(roleId);
      $scope.selectedRolesForUser.splice(i, 1);
    }
    else{
      $scope.selectedRolesForUser.push(roleId);
    }
  }

  var getRoleDetails = function(roleId){
    $scope.selectedPermissions = [];
    AdminUserMgmtService.getRoleDetails(roleId).then(function(result){
      // $scope.roleDetails = result;
      _.each(result.permissions, function(permission){
        $scope.selectedPermissions.push(permission.id);
      });
    });
  }

  $scope.showAddRolePopup = function(){
    $mdDialog.show({
      templateUrl: 'views/admin/add-role-popup.html',
      fullscreen: $scope.customFullscreen,
      clickOutsideToClose: true,
      preserveScope: true,
      scope: $scope
    });
  }

  $scope.updatePermissionsForRole = function(){
    var permObj = {
      role_id : $scope.selectedRole,
      permissions : $scope.selectedPermissions
    };
    AdminUserMgmtService.setRolePermissions(permObj).then(function(result){
      if(result.status == 1){
        toastr.success(result.message);
      }
      else{
        toastr.error(result.message);
      }
    });
  }

  $scope.addRole = function(){
    $scope.addRoleErrors = [];
    AdminUserMgmtService.addRole($scope.role).then(function(result){
      if(result.status == 1){
        $scope.role = {};
        $scope.forms.addRoleForm.$setPristine();
        $scope.forms.addRoleForm.$setUntouched();
        getAllRoles();
        toastr.success(result.message);
      }
      else{
        $scope.addRoleErrors = result.message;
      }
    })
  }

  $scope.updateRolesForUser = function(){
    var obj = {
      user_id: $scope.selectedUser.user_details.id,
      roles: $scope.selectedRolesForUser
    }
    AdminUserMgmtService.setRolesForUser(obj).then(function(result){
      if(result.status == 1){
        toastr.success(result.message);
      }
      else{
        toastr.error(result.message);
      }
    });
  }

  /*===================
  | Roles Section Ends
  ===================*/


  /*==========================
  | Permissions Section
  ==========================*/

  $scope.selectedPermissions = [];

  /**
   * Get Permissions
   */
  var getAllPermissions = function(){
    AdminUserMgmtService.getAllPermissions().then(function(result){
      $scope.allPermissions = result;
    });
  }
  getAllPermissions();

  $scope.togglePermissionSelection = function(permissionId){
    if($scope.selectedPermissions.indexOf(permissionId) == -1){
      $scope.selectedPermissions.push(permissionId);
    }
    else{
      $scope.selectedPermissions.pop(permissionId);
    }
  }

  /*==========================
  | Permissions Section Ends
  ==========================*/

  $scope.cancel = function () {
    $mdDialog.cancel();
  }
});