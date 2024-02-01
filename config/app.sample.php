<?php

if (app()->environment('local')) {
  // When the environment is local
  $current_env_server_path = "http://localhost:8001";
  $client_app_path = "http://localhost:8000";
  $bbi_email = "noreply@billboardsindia.com";
}

if (app()->environment('test')) {
  // When the environment is testing
  $current_env_server_path = "http://104.236.11.252";
  $client_app_path = "http://staging.billboardsindia.com";
  $bbi_email = "noreply@billboardsindia.com";
}

if (app()->environment('prod')) {
  // When the environment is testing
  $current_env_server_path = "http://132.148.147.218";
  $client_app_path = "http://billboardsindia.com";
  $bbi_email = "noreply@billboardsindia.com";
}

return [
  'server_path' => $current_env_server_path,
  'client_app_path' => $client_app_path,
  'bbi_email' => $bbi_email
];