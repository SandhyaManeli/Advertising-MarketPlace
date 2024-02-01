app.filter('PickFirstLetter', function() {
  return function(input) {
    input = input || '';
    return input.substring(0,1);
  }
});
app.filter('PickFirst2Letters', function(){
  return function(input) {
    input = input || '';
    return input.substring(0,2).toUpperCase();
  }
});
app.filter('mapGender', function () {
  var genderHash = {
    1: 'male',
    2: 'female'
  };
  return function (input) {
    if (!input) {
      return '';
    } else {
      return genderHash[input];
    }
  };
});

app.filter('mapStatus', function () {
  var genderHash = {
    1: 'Bachelor',
    2: 'Nubile',
    3: 'Married'
  };
  return function (input) {
    if (!input) {
      return '';
    } else {
      return genderHash[input];
    }
  };
});

app.filter('address', function () {
  return function (input) {
    return input.street + ', ' + input.city + ', ' + input.state + ', ' + input.zip;
  };
});

app.filter('dateify',function(){
  return function(date, format){
    format = format || 'MM-DD-YYYY';
    if(date){
      return moment(date).local().format(format);
    }
    else{
      return "N/A";
    }
  }
});
app.filter('dateFormat',function(){
  var currentDate = moment(new Date()).local().format('MM-DD-YYYY')
  return function(date, format){
    if(currentDate == moment(date).format('MM-DD-YYYY')){
      //commenting as only time is displaying for the current date selected
     // return moment(date).local().format('LLLL').split(',')[2].split(' ')[2] + " " + moment(date).local().format('LLLL').split(',')[2].split(' ')[3];
     return moment(date).local().format('llll').split(',')[1];
    }else{
      return moment(date).local().format('llll').split(',')[1];
    }
  }
});
app.filter('dateFormatForSelected',function(){
  return function(date){
    //   return moment(date).local().format('LLLL').split(',')[2].split(' ')[2] + " " + moment(date).local().format('LLLL').split(',')[2].split(' ')[3];
    // }else{
      return moment(date).local().format('llll').split(',')[1];
    // }
  }
});
app.filter('jsonConvert',function(){
  return function(date){
    if(Object.prototype.toString.call(date) == "[object Array]"){
      var concatinatedDates = "";
      date.forEach(item => {
        concatinatedDates += moment(item.startDate).format('LLLL').split(',')[1] + " to " + moment(item.endDate).format('LLLL').split(',')[1]
      });
      return concatinatedDates
    }
    else{
      return "N/A";
    }
  }
});
app.filter('dateslots',function(){
  return function(date, format){
    // format = format || 'DD-MM-YYYY HH:mm:ss';
    if(date){
      return date.split(',')[1]
    }
    else{
      return "N/A";
    }
  }
});
app.filter('timeify',function(){
  return function(date, format){
    format = format || 'HH:mm a';
    if(date){
      return moment(date).local().format(format);
    }
    else{
      return "N/A";
    }
  }
});

app.filter('datetimeify',function(){
  return function(date, format){
    format = format || 'DD-MM-YYYY HH:mm:ss';
    if(date){
      return moment(date).local().format(format);
    }
    else{
      return "N/A";
    }
  }
});

app.filter('stringifyProductStatus',function(){
  return function(status) {
    switch (status) {
      case 100 : 
      case 700:
        returnStatus = 'Requested'
        break;
      case 200 : 
        returnStatus = 'Sold'
        break;
      case 300 :
        returnStatus = 'running'
        break;
      default: 
        returnStatus = ''+status;
    }
    return returnStatus;
  }
})

app.filter('stringifyCampaignStatus', function(){
  return function(status){
    switch (status) {
      case 100:
        returnStatus = "In progress";
        break;
        case 101:
        returnStatus = "Delete Product Request";
        break;
      case 200:
        returnStatus = "Created";
        break;
      case 300:
        returnStatus = "Quote Requested";
        break;
      case 400:
        returnStatus = "Quote Given";
        break;
      case 500:
        returnStatus = "Change Requested";
        break;
      case 600:
        returnStatus = "Campagin Requested";
        break;
      case 700:
        returnStatus = 'Sold';
        break;
      case 800:
        returnStatus = "Scheduled";
        break;
      case 1000:
        returnStatus = "Closed";
        break;
      case 1101:
        returnStatus = "Created";
        break;
      case 1121:
        returnStatus = "Awaiting Payment Confirmation";
        break;
      case 1131:
        returnStatus = "Payment Confirmed";
        break;
      case 1141:
        returnStatus = "Running";
        break;
      case 1151:
        returnStatus = "Closed";
        break;
        case 1200:
        returnStatus = "Deleted";
        break;
      case 1300:
        returnStatus = "RFP Campaign";
      default:
        return status = "Unknown";
    }
    return returnStatus;
  }
});

app.filter('boolToYesNo', function(){
  return function(n){
    if(n){
      return "Yes";
    }
    else{
      return "No";
    }
  }
});

app.filter('MetroSlIcon', function(){
  return function(input) {
    input = input || '';
    var corName = input.split(' - ')[0];
    var pkgName = input.split(' - ')[1];
    var part2 = pkgName.split(' ').length > 1 ? pkgName.split(' ')[0].substring(0, 1) + pkgName.split(' ')[1].substring(0, 1) : pkgName.substring(0,1);
    return corName.match(/\b(\w)/g).join('') + "-" + part2;
  }
});

app.filter('MetroNamePrice', function(){
  return function(obj){
    return obj.name + " - " + obj.price;
  }
});
app.filter('metroCorridorsFromTo', function(){
  return function(obj){
    return obj.name + " (" + obj.from + " - " + obj.to + ")";
  }
});
app.filter('customSplitString', function() {
  return function(input) {
    var arr = input.split(',');
    return arr;
  };
});
app.filter('findEmployee', function(){
  return function (records, secondImpressions) {
      if(!records){
          return;
      }
      if(!secondImpressions){
          secondImpressions = 0;
      }
   
      var output = [];
      angular.forEach(records, function(record){
        if(secondImpressions !== 'undefined'){
          if(record.secondImpression >= secondImpressions ){
            output.push(record);
        }
        }
         
          
      });   
      return output;
    };
});
app.filter('findEmployeeSecondImp', function(){
  return function (records, secondImpressionsLeft) {
      if(!records){
          return;
      }
    
      if(!secondImpressionsLeft){
        secondImpressionsLeft = 0;
    }
      var output = [];
      angular.forEach(records, function(record){
        if(secondImpressionsLeft !== undefined){
          if(record.secondImpression >= secondImpressionsLeft){
            output.push(record);

          }
        }
       
      });   
      return output;
    };
});

app.filter('findEmployees', function(){
  return function (records, cpmm) {
      if(!records){
          return;
      }
      if(cpmm != "0" && !cpmm){
        cpmm = 10000000000000000000000000000000000000000000000;
      }
      var output = [];
      // var outputValues;
      angular.forEach(records, function(record){
          if(record.cpm <= cpmm && record.cpm >= 0){
              output.push(record);
          }
      });   
      // if(cpmm >0) {
      //   outputValues = output;
      //   output.push(records.forEach( e => {
      //     outputValues.filter(x => x.cpm !=e.cpm )
      //   }))
      // }
      return output;
    };
});

app.filter('findEmployeeCpmImp', function(){
  return function (records,cpmmLeft) {
      if(!records){
          return;
        
      }
     if(cpmmLeft != "0" && !cpmmLeft){
       cpmmLeft = 10000000000000000000000000000000000000;
     }
     
      var output = [];
      // var outputLessValues;
      angular.forEach(records, function(record){
        
        if(cpmmLeft !== undefined){
          if(record.cpm <= cpmmLeft && record.cpm >= 0){
            output.push(record);

          }
        }
      });  
    //   if(cpmmLeft >0) {
    //   outputLessValues = output;
    //   output.push(records.forEach( e => {
    //     outputLessValues.filter(x => x.cpm !=e.cpm )
    //   }))
    // }
      return output;
    };
});
