angular.module("bbManager").service('AdminUserMgmtService', 
  ['$http', '$q', 'config', 
    function($http, $q, config){
      return {
        getAllRoles: function(){
          var dfd = $q.defer();
          $http.get(config.apiPath + '/system-roles').success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        getAllPermissions: function(){
          var dfd = $q.defer();
          $http.get(config.apiPath + '/system-permissions').success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        getAllUsers: function(){
          var dfd = $q.defer();
          $http.get(config.apiPath + '/users').success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        getRoleDetails: function(roleId){
          var dfd = $q.defer();
          $http.get(config.apiPath + '/role-details/' + roleId).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        getRolePermissions: function(roleId){
          var dfd = $q.defer();
          $http.get(config.apiPath + '/role-permissions/' + roleId).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        getAllClients: function(){
          var dfd = $q.defer();
          $http.get(config.apiPath + '/all-clients').success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        addRole: function(role){
          var dfd = $q.defer();
          $http.post(config.apiPath + '/role', role).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        getUserDetailsWithRoles: function(userMId){
          var dfd = $q.defer();
          $http.get(config.apiPath + '/user-details-with-roles/' + userMId).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        assignSuperAdminToClient: function(obj){
          var dfd = $q.defer();
          $http.post(config.apiPath + '/set-su-for-client', obj).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        setRolePermissions: function(obj){
          var dfd = $q.defer();
          $http.post(config.apiPath + '/set-permissions-for-role', obj).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        setRolesForUser: function(obj){
          var dfd = $q.defer();
          $http.post(config.apiPath + '/set-roles-for-user', obj).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        resendOwnerInviteEmail: function(obj){
          var dfd = $q.defer();
          $http.post(config.apiPath + '/resend-owner-invite', obj).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        },
        sendInviteToBBIUser: function(obj){
          var dfd = $q.defer();
          $http.post(config.apiPath + '/invite-bbi-user', obj).success(dfd.resolve).error(dfd.reject);
          return dfd.promise;
        }
        
      }
    }
  ]
);