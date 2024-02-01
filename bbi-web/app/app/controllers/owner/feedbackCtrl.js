angular.module("bbManager").controller('feedback', function($scope,$mdDialog,ContactService,toastr,Upload) {

      $scope.uploadFiles = function(files, errFiles) {
        $scope.files = files;
        $scope.errFiles = errFiles;
        angular.forEach(files, function(file) {
            file.upload = Upload.upload({
                url: 'https://angular-file-upload-cors-srv.appspot.com/upload',
                data: {file: file}
            });

            file.upload.then(function (response) {
                $timeout(function () {
                    file.result = response.data;
                });
            }, function (response) {
                if (response.status > 0)
                    $scope.errorMsg = response.status + ': ' + response.data;
            }, function (evt) {
                file.progress = Math.min(100, parseInt(100.0 * 
                                         evt.loaded / evt.total));
            });
        });
    }

    // bbisuport page script
    $scope.concernabouts = [
        {"Name" : "Alfreds Futterkiste",
        "Age": 24,
        "Place": "Hyderabad"
    },
        
    ]
    $scope.bbisuportdata = function(query){
        ContactService.sendQuery(query).then(function(result){
            if(result.status == 1){
                toastr.success(result.message)
            }else{
                toastr.error = result.message;
            }
        });
    }
     // bbisuport page script Ends
});