angular.module("bbManager").controller(
  "OwnerContractsCtrl",
  function ($scope, toastr, AdminUserService, $window,Upload,FileSaver) {
    $scope.addnewDoc = function () {
    
      document.getElementById("Contracts").classList.toggle("show");
      $scope.title='';
      $scope.documnt={};
     };
     //get contracts
     $scope.contracts = [];      
     $scope.getContracts = function () {
       AdminUserService.getContracts().then((result) => {
         if (result) {
           $scope.contracts = result.contracts_data;
         } else toastr.error(result.data.message);
       });
     };
     $scope.getContracts();
     //Get invoice
     $scope.invoice = [];
     $scope.getInvoices = function () {
      AdminUserService.getInvoices().then((result) => {
        if (result) {
          $scope.invoice = result.invoices_data;
        } else toastr.error(result.message);
      });
    };
    $scope.getInvoices();
    //Save Contracts
    $scope.documnt={};
    $scope.requestAddDoc=function(documnt){
     Upload.upload({
       url: config.apiPath + '/save-contracts',
       data: { 
         file:$scope.documnt.file,
         title:$scope.title,
       }
     }).then(function (result) {
       if (result.status == 200){
         if (result.data.status == 1) {
          $scope.contracts = result.contracts_data;
          $scope.getContracts();
          document.getElementById("Contracts").classList.toggle("show");
    toastr.success(result.data.message);
         }
         else {
           toastr.error(result.data.message);
         }
         $scope.contractsform.$setPristine();
         $scope.contractsform.$setUntouched();
       }
     },function(errorCallback){
       if (errorCallback.status == 400) {
           $scope.errormsg = errorCallback.data;
           if (errorCallback.data.status == 1) {
            toastr.error(errorCallback.data.message);
            $scope.getContracts()
          }
          else {
            document.getElementById("Contracts").classList.toggle("show");
            toastr.error(errorCallback.data.message);
          }
       }
   });
    }
  //Save Invoice
  
  $scope.requestAddDocInvoice=function(documnt){
    Upload.upload({
      url: config.apiPath + '/save-invoices',
      data: { 
        file:$scope.documnt.file,
        title:$scope.title,
      }
    }).then(function (result) {
      if (result.status == 200){
        if (result.data.status == 1) {
         $scope.invoice = result.invoices_data;
         $scope.getInvoices();
         document.getElementById("Contracts").classList.toggle("show");
   toastr.success(result.data.message);
        }
        else {
          toastr.error(result.data.message);
        }
        $scope.contractsform.$setPristine();
        $scope.contractsform.$setUntouched();
      }
    },function(errorCallback){
      if (errorCallback.status == 400) {
          $scope.errormsg = errorCallback.data;
          if (errorCallback.data.status == 1) {
            toastr.error(errorCallback.data.message);
            $scope.getInvoices()
          }
          else {
            document.getElementById("Contracts").classList.toggle("show");
            toastr.error(errorCallback.data.message);
          }
      }
  });
   }
   $scope.$watch('documnt.file', function() {
    if($scope.documnt.file.length==0){
      $scope.documnt.file=null;
    }
});
//Download Contract
$scope.downloadPdfContract=function(id,filename){
  var data={
    'id':id
  }
 AdminUserService.downloadContract(data).then(function (result) {
  var downloadPdf = new Blob([result], {
    type: "application/pdf;charset=utf-8",
  });
  FileSaver.saveAs(downloadPdf, filename);
  if (result.status) {
    toastr.error(result.meesage);
  }
});
}
 //Download Invoice
 $scope.downloadPdfInvoice=function(id){
  var data={
    'id':id
  }
 AdminUserService.downloadInvoice(data).then(function (result) {
  var downloadPdf = new Blob([result], {
    type: "application/pdf;charset=utf-8",
  });
  FileSaver.saveAs(downloadPdf, filename);
  if (result.status) {
    toastr.error(result.meesage);
  }
});
}

     //Delete Contract
     $scope.deleteContractItem=function(id){
      $scope.delteContractId=id;
    }
    $scope.deleteContract = function () {
      var data={
        'id':$scope.delteContractId
      }
     AdminUserService.deleteContracts(data).then((result) => {
       if (result) {
        $scope.getContracts();
       } else toastr.error(result.message);
     });
   };


    //Delete Invoice
    $scope.deleteInvoiceItem=function(id){
     $scope.delteInvoiceId=id;
   }
    $scope.deleteInvoice = function () {
     var data={
       'id':$scope.delteInvoiceId
     }
    AdminUserService.deleteInvoice(data).then((result) => {
      if (result) {
       $scope.getInvoices();
       toastr.success(result.message);
      } else toastr.error(result.message);
    });
  };

  //reset
  $scope.resetnewDoc = function () {
    $scope.contractsform.$setPristine();
    $scope.contractsform.$setUntouched();
    
    document.getElementById("Contracts").classList.toggle("show");
   }

    $scope.goBack = function() {
      $window.history.back();
    }
  }
);