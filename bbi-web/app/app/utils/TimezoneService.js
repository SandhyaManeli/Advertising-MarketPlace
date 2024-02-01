angular.module("bbManager").factory('TimezoneService', [
  function() {
    return {
      convertDateToMMDDYYYY: function (dates,areaTimeZoneType) {
        if(!areaTimeZoneType) {
          areaTimeZoneType = Intl.DateTimeFormat().resolvedOptions().timeZone;
        }
        const startDate = dates.booked_from;
        const endDate = dates.booked_to;
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
      },
      convertDateToYYYYMMDD: function(dates,areaTimeZoneType) {
        if(!areaTimeZoneType) {
          areaTimeZoneType = Intl.DateTimeFormat().resolvedOptions().timeZone;
        }
        const startDate = dates.booked_from;
        const endDate = dates.booked_to;
        const splitStartDate = new Date(startDate)
          .toLocaleString('en-GB',{ timeZone: areaTimeZoneType}).slice(0,10).split('/');
      
        const splitEndDate = new Date(endDate)
          .toLocaleString('en-GB', { timeZone: areaTimeZoneType}).slice(0,10).split('/');
        return {
          startDate: splitStartDate.reverse().join('-'),
          endDate: splitEndDate.reverse().join('-')
        }
      },
      convertSingleDateToMMDDYYYY: function (campaignDate,areaTimeZoneType) {
        try {
          if(!areaTimeZoneType) {
            areaTimeZoneType = Intl.DateTimeFormat().resolvedOptions().timeZone;
          }
          const date = campaignDate;
          const splitStartDate = new Date(date)
            .toLocaleString('en-GB',{ timeZone: areaTimeZoneType}).slice(0,10).split('/');
          [splitStartDate[0], splitStartDate[1]] = [splitStartDate[1], splitStartDate[0]];
          return splitStartDate.join('-');
        } catch (error) {
          console.log(error);
        }
      }
    }
  }
]);
