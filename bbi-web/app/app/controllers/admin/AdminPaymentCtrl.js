angular.module("bbManager").controller('AdminPaymentCtrl', function($scope, $mdDialog,$stateParams, $location,Upload,config  , $rootScope, CampaignService, AdminCampaignService) {
   
    $scope.loadCampaignPayments = function(campaignId) {
        AdminCampaignService.getCampaignPaymentDetails(campaignId).then(function (result) {
            $scope.campaignPayments = result.campaign_details;
            $scope.fivePercentAmount = ( 5 / 100) * $scope.campaignPayments.total_amount;
            $scope.cardProcessing = (2.9/100) * $scope.fivePercentAmount;
        });
      }
  
    // Create a Stripe client
  // strip-payment
  var stripe = Stripe('pk_test_n5DnV8z3P2j751J2JqDyeX6r006Om1UNEK');
  var elements = stripe.elements();
  
  var card = elements.create('card', {
    hidePostalCode: true,
    style: {
      base: {
        iconColor: '#666EE8',
        color: '#31325F',
        lineHeight: '40px',
        fontWeight: 300,
        fontFamily: 'Helvetica Neue',
        fontSize: '15px',
  
        '::placeholder': {
          color: '#CFD7E0',
        },
      },
    }
  });
    card.mount('#card-element');
  function setOutcome(result) {
   //console.log('result',result)
   var successElement = document.querySelector('.success');
   var errorElement = document.querySelector('.error');
   successElement.classList.remove('visible');
   errorElement.classList.remove('visible');
 
   if (result.token) {
     // In this example, we're simply displaying the token
     successElement.querySelector('.token').textContent = result.token.id;
     successElement.classList.add('visible');
 
     // In a real integration, you'd submit the form with the token to your backend server
     var form = document.querySelector('form');
     form.querySelector('input[name="token"]').setAttribute('value', result.token.id);
     // form.submit();
   } else if (result.error) {
     errorElement.textContent = result.error.message;
     errorElement.classList.add('visible');
   }
 }
 
 card.on('change', function(event) {
   setOutcome(event);
 });

 document.querySelector('form').addEventListener('submit', function(e) {
  e.preventDefault();
  var options = {
    name: document.getElementById('first-name').value + " " + document.getElementById('last-name').value,
    address_line1: document.getElementById('address-line1').value,
    address_line2: document.getElementById('address-line2').value,
    address_city: document.getElementById('address-city').value,
    address_state: document.getElementById('address-state').value,
    address_zip: document.getElementById('address-zip').value,
    address_country: document.getElementById('address-country').value,
    total_Amount:  $scope.campaignPayments,
    five_percent: $scope.fivePercentAmount
  };
  stripe.createToken(card, options).then(setOutcome);
});
    // strip payment
    $scope.loadCampaignPayments($stateParams.campaign_id); 
});