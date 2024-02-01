<?php

if (app()->environment('local')) {
  // When the environment is local
  /*$current_env_server_path = "http://139.59.86.12:8001";
  $client_app_path = "http://139.59.86.12";
  $bbi_email = "noreply@advertisingmarketplace.com";*/
  /*PROD added for testing the env*/
  //$current_env_server_path = "http://52.7.17.56";//working
  //$current_env_server_path = "https://advertisingmarketplace.com";
   $current_env_server_path = "http://52.21.230.182";//working
  //$client_app_path = "http://52.7.17.56:8080";
  //$client_app_path = "http://52.7.17.56:80"; //working
  //$client_app_path = "https://advertisingmarketplace.com";
   //$client_app_path = "http://52.21.230.182:8080"; //working
   $client_app_path = "http://52.21.230.182"; //working
  //$client_app_path = "http://52.7.17.56:8000";
  $bbi_email = "noreply@advertisingmarketplace.com";
}

if (app()->environment('test')) {
  // When the environment is testing
  //$current_env_server_path = "http://104.43.129.226";
  //$current_env_server_path = "http://40.69.164.167";
  
 # $client_app_path = "http://104.43.129.226";
  //$client_app_path = "http://104.43.129.226:8080";
  // $client_app_path = "http://40.69.164.167:8080";
  // $bbi_email = "noreply@billboardsindia.com";
  /*TEST*/
 /* $current_env_server_path = "http://13.67.211.53";
  $client_app_path = "http://13.67.211.53:8080";
  $bbi_email = "noreply@advertisingmarketplace.com";*/
  /*PROD added for testing the env*/
  //$current_env_server_path = "http://52.7.17.56";//working
  //$current_env_server_path = "https://advertisingmarketplace.com";
  //$current_env_server_path = "http://52.21.230.182";//working
    $client_app_path = "http://52.21.230.182"; //working
 // $client_app_path = "http://52.7.17.56:8080";

  //$client_app_path = "http://52.7.17.56:80"; //working
  //$client_app_path = "https://advertisingmarketplace.com";
   $client_app_path = "http://52.21.230.182:8080"; //working
  //$client_app_path = "http://52.7.17.56:8000";
  $bbi_email = "noreply@advertisingmarketplace.com";
}

if (app()->environment('prod')) {
  // When the environment is testing
  /*$current_env_server_path = "http://132.148.147.218";
  $client_app_path = "http://billboardsindia.com";
  $bbi_email = "noreply@billboardsindia.com";*/
  
  //$client_app_path = "https://advertisingmarketplace.com/";
  //$current_env_server_path = "http://52.7.17.56";//working
  //$current_env_server_path = "https://advertisingmarketplace.com";
   $current_env_server_path = "http://52.21.230.182";//working
//  $client_app_path = "http://52.7.17.56:8080";

  //$client_app_path = "http://52.7.17.56:80"; //working
  //$client_app_path = "https://advertisingmarketplace.com";
   //$client_app_path = "http://52.21.230.182:8080"; //working
   $client_app_path = "http://52.21.230.182"; //working
  //$client_app_path = "http://52.7.17.56:8000";
  $bbi_email = "noreply@advertisingmarketplace.com";
  
}
/*'providers' => [
Cartalyst\Stripe\Laravel\StripeServiceProvider::class,
],
'aliases' => [
'Stripe' => Cartalyst\Stripe\Laravel\Facades\Stripe::class,
],*/

return [
  'server_path' => $current_env_server_path,
  'client_app_path' => $client_app_path,
  'bbi_email' => $bbi_email
];
