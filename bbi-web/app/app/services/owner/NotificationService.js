angular.module("bbManager").service('OwnerNotificationService', ['$http', '$q', 'config',
    function ($http, $q, config) {
        return {
            getAllOwnerNotifications: function (last_notif) {
                var dfd = $q.defer();
                $http.get(config.apiPath + '/all-owner-notifications/last-notif/' + last_notif, {skipInterceptor: true}).success(dfd.resolve).error(dfd.reject);
                return dfd.promise;
            },
            updateNotifRead: function (notifId) {
                var dfd = $q.defer();
                $http.get(config.apiPath + '/update-notification-read/' + notifId, {skipInterceptor: true}).success(dfd.resolve).error(dfd.reject);
                return dfd.promise;
            },
            viewOwnerNotification: function () {
                var dfd = $q.defer();
                $http.get(config.apiPath + '/get-notifications', {skipInterceptor: true}).success(dfd.resolve).error(dfd.reject);
                return dfd.promise;
            },
            updatenotificationsstatus: function (campaignOfferParams) {
                var dfd = $q.defer();
                let params = {
                    notification_ids:campaignOfferParams
                }
                $http.post(config.apiPath + '/update-notifications-status', params).success(dfd.resolve).error(dfd.reject);
                return dfd.promise;
            }
        }
    }
]);