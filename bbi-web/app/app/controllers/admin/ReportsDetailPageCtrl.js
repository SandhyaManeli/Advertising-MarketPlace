angular.module("bbManager").controller('ReportsDetailPageCtrl', function ($scope, $stateParams, Blob, $location, $http, $timeout, FileSaver, ManagementReportService, config,toastr,$window) {
    $scope.tableHeader = [];
    $scope.tableRows = [];
    $scope.startDate = null;
    $scope.endDate = null;
    $scope.searchtxt = '';
    $scope.dataErrorMsg = '';
    $scope.isApiCalled = false;
    $scope.isSearching = false;

    //for adding dynamic class
    $scope.types = {
        "10": "user",
        "11": "product",
        "12": "campaign"
    }
    $scope.selectedType = $scope.types[$stateParams.type];

    // Search Text
    $scope.hasFilter = false;
    $scope.searchText = function () {
        $scope.isSearching = true;
        var payload = {};
        if ($scope.startDate) {
            payload.from_date = $scope.startDate;
            $scope.hasFilter = true;
        }
        if ($scope.endDate) {
            payload.to_date = $scope.endDate;
            $scope.hasFilter = true;
        }
        if ($scope.searchtxt) {
            // var srchText = $scope.searchtxt.toLowerCase();
            // if ($stateParams.type == '12' && ManagementReportService.campaignStatusCode[srchText])
            //     payload.searchparam = ManagementReportService.campaignStatusCode[srchText];
            // else
            payload.searchparam = $scope.searchtxt;
            $scope.hasFilter = true;
        }
        else if($scope.searchtxt=='' && $scope.startDate==null && $scope.endDate==null){
            $scope.hasFilter = false;
        };
        switch ($stateParams.type) {
            case '10':
                ManagementReportService.searchUserDetails(payload).then(function(result) {
                    console.log(result);
                    if (result) {
                        if (result.message) {
                            $scope.dataErrorMsg = result.message;
                        } else {
                            $scope.dataErrorMsg = '';
                            $scope.tableRows = result;
                        }
                    }
                    $scope.isApiCalled = true;
                    $scope.isSearching = false;
                });
                // payload.email = btoa(JSON.parse(localStorage.loggedInUser).email);
                // $http.post('http://23.100.84.3/api/users-details-filters-search', payload).then(result => {
                //$http.post('http://52.21.230.182:5000/es/search_users_filter', payload).then(result => {
                // $http.post(config.apiPathES + '/search_users_filter', payload).then(result => {

                //     result = result.data;
                //     result = result.data;

                //     if (result) {
                //         if (result.message) {
                //             if (result.message)
                //                 $scope.dataErrorMsg = result.message;
                //         } else {
                //             $scope.tableRows = result;
                //             if ($scope.tableRows && $scope.tableRows.length)
                //                 $scope.dataErrorMsg = '';
                //             else
                //                 $scope.dataErrorMsg = 'There is no any matching records';
                //         }
                //     } else {
                //         result = [];
                //         $scope.dataErrorMsg = 'There is no any matching records';
                //     }
                // });
                break;
            case '11':
                ManagementReportService.searchProductDetails(payload).then(function(result) {
                    console.log(result);
                    if (result) {
                        if (result.message) {
                            $scope.dataErrorMsg = result.message;
                        } else {
                            $scope.dataErrorMsg = '';
                            $scope.tableRows = result;
                        }
                    }
                    $scope.isSearching = false;
                    $scope.isApiCalled = true;
                });
                // payload.email = btoa(JSON.parse(localStorage.loggedInUser).email);
                // // $http.post('http://23.100.84.3/api/products-details-filters-search', payload).then(result => {
                // $http.post(config.apiPathES + '/search_product_filter', payload).then(result => {
                //     result = result.data;
                //     result = result.data;
                //     if (result) {
                //         if (result.message) {
                //             $scope.dataErrorMsg = result.message;
                //         } else {
                //             $scope.tableRows = result;
                //             if ($scope.tableRows && $scope.tableRows.length)
                //                 $scope.dataErrorMsg = '';
                //             else
                //                 $scope.dataErrorMsg = 'There is no any matching records';
                //         }
                //     } else {
                //         result = [];
                //         $scope.dataErrorMsg = 'There is no any matching records';
                //     }
                // });
                break;
            case '12':
                ManagementReportService.searchCampaignDetails(payload).then(function(result) {
                    console.log(result);
                    if (result) {
                        if (result.message) {
                            $scope.dataErrorMsg = result.message;
                        } else {
                            $scope.dataErrorMsg = '';
                            $scope.tableRows = result;
                        }
                    }
                    $scope.isSearching = false;
                    $scope.isApiCalled = true;
                });
                // payload.email = btoa(JSON.parse(localStorage.loggedInUser).email);
                // // $http.post('http://23.100.84.3/api/campaigns-details-filters-search', payload).then(result => {
                // $http.post(config.apiPathES + '/search_campaigns_filter', payload).then(result => {

                //     result = result.data;
                //     result = result.data;
                //     if (result) {
                //         if (result.message) {
                //             $scope.dataErrorMsg = result.message;
                //         } else {
                //             $scope.tableRows = result;
                //             if ($scope.tableRows && $scope.tableRows.length)
                //                 $scope.dataErrorMsg = '';
                //             else
                //                 $scope.dataErrorMsg = 'There is no any matching records';
                //         }
                //     } else {
                //         result = [];
                //         $scope.dataErrorMsg = 'There is no any matching records';
                //     }
                // });
                break;
        }
    }

    switch ($stateParams.type) {
        case '10': //users
            $scope.tableHeader = ManagementReportService.headers[0].items;
            if($window.innerWidth <=768){
                                    toastr.success('Kindly turn phone sideways to use AMP site');
                                }
            $scope.searchText();
            break;
        case '11': //products
            $scope.tableHeader = ManagementReportService.headers[1].items;
            if($window.innerWidth <=768){
                                    toastr.success('Kindly turn phone sideways to use AMP site');
                                }
            $scope.searchText();
            break;
        case '12': //campaigns
            $scope.tableHeader = ManagementReportService.headers[2].items;
            if($window.innerWidth <=768){
                                    toastr.success('Kindly turn phone sideways to use AMP site');
                                }
            $scope.searchText();
            break;
        default:
            $location.path('/admin/report');
    }

    $scope.onChangeStartEndDate = function () {
        $scope.searchText();
    }
    $scope.datepicker=function(){
        $('html').css('overflow', 'hidden');
    }
    $scope.getCampaignStatusString = function (statusCode) {
        return ManagementReportService.campaignStatus[statusCode];
    }

    $scope.getPageTitle = function () {
        return ManagementReportService.pageTitle[$stateParams.type];
    }

    // SORTING FOR Refered By
    const sortArrayByField = (ary, field, isAscending) => {
        if (isAscending) {
            $scope.upArrowColour = field;
            $scope.sortType = 'Asc';
            ary.sort((a, b) => {
                if (a[field] > b[field])
                    return 1;
                if (a[field] < b[field])
                    return -1;
                return 0;
            });
        } else {
            $scope.downArrowColour = field;
            $scope.sortType = 'Dsc';
            ary.sort((a, b) => {
                if (a[field] > b[field])
                    return -1;
                if (a[field] < b[field])
                    return 1;
                return 0;
            });
        }
    };

    $scope.sortAsc = function (headingName, type) {
        if (headingName != "created_at") {
            $scope.upArrowColour = headingName;
            $scope.sortType = "Asc";
            if (type == "string") {
                $scope.newOfferData = $scope.tableRows.map(e => {
                    return {
                        ...e,
                        first_name: e.first_name,
                        company_type: e.company_type,
                        email: e.email,
                        company_name: e.company_name
                    }
                })
                $scope.tableRows = [];
                $scope.tableRows = $scope.newOfferData.sort((a, b) => {
                    if (a[headingName] != null) {
                        return a[headingName].localeCompare(b[headingName], undefined, {
                            numeric: true,
                            sensitivity: 'base'
                        });
                    }
                })

                // $scope.productList = $scope.newOfferData;
            }
            $scope.tableRows = $scope.tableRows.sort((a, b) => {

                if (type == 'boolean') {
                    return a[headingName] ? 1 : -1
                }
                else if (type == 'date') {
                    return new Date(a[headingName].date) - new Date(b[headingName].date)

                }
                else {
                    return a[headingName] - b[headingName]
                }
            })
        } else {
            sortArrayByField($scope.tableRows, headingName, true);
        }
    }

    $scope.sortDsc = function (headingName, type) {
        if (headingName != "created_at") {
            $scope.downArrowColour = headingName;
            $scope.sortType = "Dsc";
            if (type == "string") {
                $scope.newOfferData = $scope.tableRows.map(e => {
                    return {
                        ...e,
                        first_name: e.first_name,
                        company_type: e.company_type,
                        email: e.email,
                        company_name: e.company_name
                    }
                })
                $scope.tableRows = [];
                $scope.tableRows = $scope.newOfferData.sort((a, b) => {
                    if (b[headingName] != null) {
                        return b[headingName].localeCompare(a[headingName], undefined, {
                            numeric: true,
                            sensitivity: 'base'
                        });
                    }
                })

                // $scope.RfpData = $scope.newOfferData;
            }
            $scope.tableRows = $scope.tableRows.sort((a, b) => {
                if (type == 'boolean') {
                    return a[headingName] ? -1 : 1
                    return new Date(b[headingName].date) - new Date(a[headingName].date)

                }
                else {
                    return b[headingName] - a[headingName]
                }
            })
        } else {
            sortArrayByField($scope.tableRows, headingName, false);
        }
    };
    //Lazy Loading
    $scope.limit = 20;
    $scope.loadMore = function (last, inview) {
        if (last && inview) {
            $scope.limit += 20
        }
    };

    //Download PDF
    $scope.downloadPdf = function () {
        var payload = {};
        if ($scope.startDate && $scope.endDate) {
            payload.from_date = $scope.startDate;
            payload.to_date = $scope.endDate;
        }
        if ($scope.searchtxt) {
            payload.searchparam = $scope.searchtxt;
        };
        switch ($stateParams.type) {
            case '10':
                ManagementReportService.downloadFilteredUsers(payload).then(function (result) {
                    var usersPdf = new Blob([result], { type: 'application/pdf' });
                    FileSaver.saveAs(usersPdf, 'users.pdf');
                });
                break;
            case '11':
                ManagementReportService.downloadFilteredProudcts(payload).then(function (result) {
                    var productsPdf = new Blob([result], { type: 'application/pdf' });
                    FileSaver.saveAs(productsPdf, 'products.pdf');
                });
                break;
            case '12':
                ManagementReportService.downloadFilteredCampaigns(payload).then(function (result) {
                    var campaignsPdf = new Blob([result], { type: 'application/pdf' });
                    FileSaver.saveAs(campaignsPdf, 'campaigns.pdf');
                });
                break;
        };
    }

    //Download CSV
    $scope.DownloadCSVHeader = [];
    $scope.downloadCSV = function () {
        var payload = {};
        if ($scope.startDate && $scope.endDate) {
            payload.from_date = $scope.startDate;
            payload.to_date = $scope.endDate;
        }
        if ($scope.searchtxt) {
            payload.searchparam = $scope.searchtxt;
        };
        switch ($stateParams.type) {
            case '10':
                /*
                ManagementReportService.downloadUsersCSVFile(payload).then(function(result) {
                    var usersPdf = new Blob([result], {type: 'application/csv'});
                    FileSaver.saveAs(usersPdf, 'users.csv');
                });
                */
                $scope.downloadFile("users");
                break;
            case '11':
                /*
                ManagementReportService.downloadProductsCSVFile(payload).then(function(result) {
                    var productsPdf = new Blob([result], {type: 'application/csv'});
                    FileSaver.saveAs(productsPdf, 'products.csv');
                });
                */
                $scope.downloadFile("products");
                break;
            case '12':
                /*
                ManagementReportService.downloadCampaignsCSVFile(payload).then(function(result) {
                    var campaignsPdf = new Blob([result], {type: 'application/csv'});
                    FileSaver.saveAs(campaignsPdf, 'campaigns.csv');
                });
                */
                $scope.downloadFile("campaigns");
                break;
        };
    }
    //Clear All Filters date and search both
    $scope.clearFilters = function () {
        $scope.hasFilter = false;
        $scope.startDate = null;
        $scope.endDate = null;
        $scope.searchtxt = '';
        $scope.searchText();
    }

    $scope.convertToCSV = function () {
        var csvData = $scope.tableHeader.map(header => header.heading).join(',');
        csvData += "\r\n";
        $scope.tableRows.forEach(row => {
            var rowData = [];
            $scope.tableHeader.forEach(col => {
                var fieldData = "";
                if (col.field == "verified") {
                    fieldData = row[col.field] ? "Yes" : "No";
                }
                else if (col.field == "created_at") {
                    //fieldData = formatDate(row[col.field].date); //YYYY-MM-DD
                    fieldData = formatDate(row[col.field]); //YYYY-MM-DD
                } else if (col.field == "rateCard") {
                    //fieldData = "$" + row[col.field].toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    fieldData = "$" + Number(row[col.field]).toFixed(2);
                } else if (col.field == "status") {
                    fieldData = $scope.getCampaignStatusString(row[col.field]);
                } else {
                    fieldData = row[col.field];
                }
                fieldData = fieldData.toString().replace(',', 'c_o_m_m_a')
                rowData.push(fieldData);
            });
            csvData += rowData.join(',');
            csvData += "\r\n";
        });

        return csvData;
    }
    function formatDate(dt) {
        var dt = new Date(dt);
        var year = dt.getFullYear();
        var month = dt.getMonth() + 1;
        var day = dt.getDate();
        var dtString =  year + "-" + (month < 0 ? "0" + month : month) + "-" + (day < 0 ? "0" + day : day);
        return dtString;
    }
    $scope.downloadFile = function (fileName) {
        const csvData = this.convertToCSV();
        let blob = new Blob(['\ufeff' + csvData], { type: 'text/csv;charset=utf-8;' });
        let dwldLink = document.createElement("a");
        let url = URL.createObjectURL(blob);
        let isSafariBrowser = navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1;
        if (isSafariBrowser) {  //if Safari open in new window to save file with random filename.
            dwldLink.setAttribute("target", "_blank");
        }
        dwldLink.setAttribute("href", url);
        dwldLink.setAttribute("download", fileName + ".csv");
        dwldLink.style.visibility = "hidden";
        document.body.appendChild(dwldLink);
        dwldLink.click();
        document.body.removeChild(dwldLink);
    }
});