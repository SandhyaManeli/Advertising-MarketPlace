angular.module("bbManager").controller('HomeCtrl', function($scope) {
  var chart1 = {};
  chart1.type = "google.charts.Bar";
  chart1.displayed = false;
  chart1.data = {
    "cols": [{
      id: "month",
      label: "Month",
      type: "string"
    }, {
      id: "Available-id",
      label: "Available",
      type: "number"
    }, {
      id: "Booked-id",
      label: "Booked",
      type: "number"
    }, {
      id: "Total-id",
      label: "Total",
      type: "number"
    }],
    "rows": [{
      c: [{
        v: "January"
      }, {
        v: 19,
        f: "19 hoardings"
      }, {
        v: 12,
        f: "12 hoardings"
      }, {
        v: 31,
        f: "31 hoardings"
      }]
    }, {
      c: [{
        v: "February"
      }, {
        v: 13,
        f: "13 hoardings"
      }, {
        v: 19,
        f: "19 hoardings"
      }, {
        v: 32,
        f: "32 hoardings"
      }]
    }, {
      c: [{
          v: "March"
        }, {
          v: 24,
          f: "24 hoardings"
        }, {
          v: 5,
          f: "5 hoardings"
        }, {
          v: 29,
          f: "29 hoardings"
        }

      ]
    }, {
      c: [{
        v: "April"
      }, {
        v: 13,
        f: "13 hoardings"
      }, {
        v: 19,
        f: "19 hoardings"
      }, {
        v: 32,
        f: "32 hoardings"
      }]
    }
    ]
  };

  chart1.options = {
    "title": "Sales per month",
    "isStacked": "true",
    "fill": 20,
    "displayExactValues": true,
    "vAxis": {
      "title": "Sales unit",
      "gridlines": {
        "count": 10
      }
    },
    "hAxis": {
      "title": "Date"
    }
  };
  $scope.myChart = chart1;
}).value('googleChartApiConfig', {
  version: '1.1',
  optionalSettings: {
    packages: ['bar'],
    language: 'en'
  }
});