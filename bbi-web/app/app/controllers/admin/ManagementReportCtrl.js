angular.module("bbManager").controller('ManagementReportCtrl', function ($scope, ManagementReportService,$window,$location) {
    $scope.reportCount = {};
    $scope.headers = [
      {items:[
        {field: 'first_name', heading: 'First Name'},
        {field: 'last_name', heading: 'Last Name'},
        {field: 'email', heading: 'Email'},
        {field: 'phone', heading: 'Phone'},
        {field: 'company', heading: 'Company'},
        {field: 'verified', heading: 'Activated'},
      ]},
      {items:[
        {field: 'first_name', heading: 'First Name'},
        {field: 'last_name', heading: 'Last Name'},
        {field: 'email', heading: 'Email'},
        {field: 'phone', heading: 'Phone'},
        {field: 'company_name', heading: 'Company'},
        {field: 'verified', heading: 'Activated'},
      ]},
      {items:[
        {field: 'first_name', heading: 'First Name'},
        {field: 'last_name', heading: 'Last Name'},
        {field: 'email', heading: 'Email'},
        {field: 'phone', heading: 'Phone'},
        {field: 'company', heading: 'Company'},
        {field: 'verified', heading: 'Activated'},
      ]}      
    ];

    createChart = function(static, digital, dstatic, media, paymentY, refundY) {
        Highcharts.chart('container', {
            chart: {
              type: 'column'
            },
            title: {
              text: 'Products'
            },
            subtitle: {
              text: ''
            },
            accessibility: {
              announceNewData: {
                enabled: true
              }
            },
            xAxis: {
              type: 'category'
            },
            yAxis: {
              title: {
                text: 'Count'
              }
          
            },
            legend: {
              enabled: false
            },
            credits: {
              enabled: false
            },
            plotOptions: {
              series: {
                borderWidth: 0,
                dataLabels: {
                  enabled: true,
                  format: '{point.y:.f}'
                }
              }
            },
          
            tooltip: {
              headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
              pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.f}</b> of total<br/>'
            },
          
            series: [
              {
                name: "Products",
                colorByPoint: true,
                data: [
                  {
                    name: "Static",
                    y: static
                  },
                  {
                    name: "Digital",
                    y: digital
                  },
                  {
                    name: "Digital/Static",
                    y: dstatic
                  },
                  {
                    name: "Media",
                    y: media
                  }
                ]
              }
            ]
          });
          Highcharts.chart('container1', {
            chart: {
              plotBackgroundColor: null,
              plotBorderWidth: null,
              plotShadow: false,
              type: 'pie'
            },
            title: {
              text: 'Stripe'
            },
            credits: {
              enabled: false
            },
            tooltip: {
              pointFormat: '{series.name}: <b>{point.y:.f}</b>'
            },
            accessibility: {
              point: {
                valueSuffix: '%'
              }
            },
            plotOptions: {
              pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                  enabled: true,
                  format: '<b>{point.name}</b>: {point.y:.f} '
                }
              },
              series: {
                cursor: 'pointer',
                point: {
                    events: {
                        click: function() {
                         return $scope.navigateToDetailPage(2);
                        }
                    }
                }
            }
            },
            series: [{
              name: 'Payments',
              colorByPoint: true,
              data: [{
                name: 'YTD Payments',
                y: paymentY
              }, {
                name: 'YTD Refunds',
                y: refundY
              }]
            }]
        });
    };

    getReportCounts = function() {
        ManagementReportService.getReportCounts().then(function(result) {
            $scope.reportCount = result;
            createChart(result.Static, result.Digital, result["Digital/Static"], result.Media, 5, 2);
            console.log('reports count: '+JSON.stringify(result));
        });
    };
    getReportCounts();

    $scope.navigateToDetailPage = function(index){
      //ManagementReportService.headers = $scope.headers[index%10].items;
      $location.path('/admin/report-details/'+index);
      //to laod page from top 
      document.body.scrollTop = 0;
    }
});