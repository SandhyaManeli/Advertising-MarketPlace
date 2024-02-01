app.service('LoadingInterceptor',
  ['$q', '$rootScope', '$log', '$injector', '$timeout', '$location',
    function($q, $rootScope, $log, $injector, $timeout, $location) {
      'use strict';

      return {
        request: function(config) {
          if(!config.skipInterceptor){
            $rootScope.loading = true;
          }
          return config;
        },
        requestError: function(rejection) {
          $rootScope.loading = false;
          $log.error('Request error:', rejection);
          return $q.reject(rejection);
        },
        response: function(response) {
          $rootScope.loading = false;
          return response;
        },
        responseError: function(rejection) {
          $rootScope.loading = false;
          var toastr = $injector.get('toastr');
          var $mdDialog = $injector.get('$mdDialog');
          if(rejection.status == 401){
            if(localStorage.signInOpened && JSON.parse(localStorage.signInOpened)){
              $rootScope.isAuthenticated = false;
              localStorage.clear();
              toastr.error('Your session has expired. Please login again.');
              $timeout(function(){
                $mdDialog.show({
                  templateUrl: 'views/sign-in.html',
                  fullscreen: true
                }).then(function(){
                  localStorage.signInOpened = false;
                });
              }, 700);
              localStorage.signInOpened = true;
            }
          }
          else if(rejection.status == 403){
            toastr.error('You are not authorized to perform this action. Please contact admin.');
          }
          return $q.reject(rejection);
        }
      };
    }
  ]
);