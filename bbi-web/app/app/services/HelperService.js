angular.module("bbManager").factory('HelperService', [function(){
  return {
    Campaign: {
        isRunning: function(startDt, endDt) {
            var startDate = new Date(startDt);
            var endDate = new Date(endDt) ;
            var today = new Date(new Date().toISOString());
            return (today >= startDate && today <= endDate); 
        },
        isScheduled: function(startDt, endDt) {
          var startDate = new Date(startDt);
          var endDate = new Date(endDt) ;
          var today = new Date(new Date().toISOString());
          return (today < startDate && today < endDate); 
      },
        isClosed: function(endDt) {
            var endDate = new Date(endDt) ;
            var today = new Date(new Date().toISOString());
            return today > endDate;
        },
        isWithinStatingWeek: function(startDate) {
            var stDate = new Date(startDate) ;
            var today = new Date(new Date().toISOString());
            var weekStart = new Date(new Date().toISOString());
            weekStart = weekStart.setDate(weekStart.getDate() - 7);
            if (stDate < today && stDate > weekStart) {
              return true;
            }
            return false;
        },
        sortDescending: function(ary, date) {
          if (ary && ary.length) {
            var abc = ary.sort(function(x,y) {
              var xDate = new Date(x['start_date']);
              var yDate = new Date(y['start_date'])
              if (xDate > yDate) {
                return -1;
              } else if (xDate < yDate) {
                return 1;
              } else {
                return 0;
              }
            });
          }
        },
        sortDescendingColor: function(ary) {
            var abc = ary.sort(function(x,y) {
              if (x['colorcode'] == 'red') {
                return -1;
              } else {
                return 0;
              }
            });
          }
        }
    }
  //}
}]);
