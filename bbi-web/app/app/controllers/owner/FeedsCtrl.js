angular.module("bbManager").controller('OwnerFeedsCtrl', function ($scope, $mdDialog, $http, $mdSidenav, $location, $rootScope, OwnerCampaignService, ProductService, toastr, HelperService) {
  
    $scope.msg = {};
    $scope.limit = 3;
    $scope.pageNo = 1;

    $scope.productList = [];
    $scope.loadProductList = function(){
      ProductService.getProductForPage($scope.pageNo).then(function(result){
        if(localStorage.campaignForSuggestion){
          var campaignForSuggestion = JSON.parse(localStorage.campaignForSuggestion);
          if(campaignForSuggestion.products && campaignForSuggestion.products.length > 0){
            _.map(result, function(p){
              if(_.find(JSON.parse(localStorage.campaignForSuggestion).products, {id: p.id}) !== undefined){
                p.alreadyAdded = true;
                return p;
              } 
            });  
          } 
        }
        $scope.pageNo += 1;
        $scope.productList = $scope.productList.concat(result);
      });
    }
    // $scope.loadProductList();

    /*
    ======== Campaign requests =======
    */

    var getAllOwnerFeeds = function(){
      $scope.loading= true;
      OwnerCampaignService.getOwnerFeeds().then(function(result){
        $scope.requestList = result;
        console.log('Request List: ');
        console.log($scope.requestList);        
        if (result && result.length) {
          $scope.requestList.forEach(item => {
            if (item.colorcode != 'red' && HelperService.Campaign.isWithinStatingWeek(item.start_date))  {
              item.colorcode = 'red';
            }
          });
          $scope.loading= false;
        }
        $scope.noCampaigns=false;
        if($scope.requestList.length ==0){
          $scope.noCampaigns=true;
        }
        HelperService.Campaign.sortDescendingColor($scope.requestList);
      });
    }
    getAllOwnerFeeds();

    $scope.showCampaignDetails = function($event, campaign){
      $location.path('/owner/' + $rootScope.clientSlug + '/campaign-details/' + campaign.id + "/" + campaign.type);
    }

    /*
    ======== Campaign requests ends =======
    */

    $scope.showCampaignDetailsPopup = function (ev, campaignData) {
      // console.log(campaignData);
      // $scope.selectedRequestDetails = campaignData;
      // $mdDialog.show({
      //   templateUrl: 'views/admin/campaign-details-popup.html',
      //   fullscreen: $scope.customFullscreen,
      //   clickOutsideToClose: true,
      //   preserveScope: true,
      //   scope: $scope
      // })
    };

    $scope.closeCampaignRequestDetails = function(){
      $mdDialog.hide();
    }
  
    /*
    ======== Campaign Suggestions(planned) ========
    */

    // saves the campaign details in a service so it can be accessed on suggest-product page.
    // different from sugggestProductForCampaign (no 's', and a parameter 'suggestedProduct')
    // as it's used for redirecting the user to suggest-product page with campaign details saved,
    // while suggestProductForCampaign actually adds a product in the campaign

    // $scope.alreadyAdded = function(productId){
    //   console.log(count++);
    //   console.log($scope.productList.length);
    //   // return _.find(campaign.products, {id: productId}) !== undefined;
    // }

    $scope.suggestProductsForCampaign = function(){
      localStorage.campaignForSuggestion = JSON.stringify($scope.selectedRequestDetails);
      $location.path('/admin/suggest-products');
    }

    // adds a product in the campaign
    $scope.suggestProductForCampaign = function(suggestedProduct){
      if(!localStorage.campaignForSuggestion){
        toastr.error("No Campaign is seleted. Please select which campaign you're adding this product in to.")
      }
      else{
        var postObj = {
          campaign_id: JSON.parse(localStorage.campaignForSuggestion).id,
          product:{
            id: suggestedProduct.id,
            from_date: suggestedProduct.start_date,
            to_date:  suggestedProduct.end_date,
            price: suggestedProduct.price
          }
        };
        AdminCampaignService.proposeProductForCampaign(postObj).then(function(result){
          if(result.status == 1){
            AdminCampaignService.getCampaignWithProducts(JSON.parse(localStorage.campaignForSuggestion).id).then(function(updatedCampaignData){
              localStorage.campaignForSuggestion = JSON.stringify(updatedCampaignData);
              _.map($scope.productList, function(product){
                if(product.id == suggestedProduct.id){
                  product.alreadyAdded = true;             
                }
                return product;
              });
            });
            toastr.success(result.message);
          }
          else{
            toastr.error(result.message);
          }
        });
      }
    }

    $scope.removeProductFromCampaignSuggestion = function(productId){
      var campaignId = JSON.parse(localStorage.campaignForSuggestion).id;
      AdminCampaignService.deleteProductFromCampaign(campaignId, productId).then(function(result){
        if(result.status == 1){
          AdminCampaignService.getCampaignWithProducts(JSON.parse(localStorage.campaignForSuggestion).id).then(function(updatedCampaignData){
            localStorage.campaignForSuggestion = JSON.stringify(updatedCampaignData);
          });
          _.map($scope.productList, function(product){
            if(product.id == productId){
              product.alreadyAdded = false;             
            }
            return product;
          });          
        }
      });
    }

    // AdminCampaignService.getPlannedCampaigns().then(function(result){
    //   $scope.campaignProposalList = result;
    // });
    /*
    ======== Campaign Suggestions(planned) ends ========
    */

    $scope.loadMore = function () {
      $scope.limit = $scope.items.length
    }

    // shortlist popup

    $scope.shortlistProduct = function() {
        $mdSidenav('shortlistAndSaveOwnerSidenav').toggle();
    };
    // add product

    $scope.addProductOwner = function() {
        $mdSidenav('addProductOwnerSidenav').toggle();
    };
    $scope.shareProducts = function() {
        $mdSidenav('shareProductOwner').toggle();
    };
  });
 