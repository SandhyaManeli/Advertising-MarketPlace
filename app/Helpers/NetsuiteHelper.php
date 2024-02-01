<?php

  namespace app\Helpers;

  /*==================
  ***** IMPORTS *****
  ==================*/
  use Illuminate\Database\DatabaseManager;
  use Illuminate\Support\Facades\Mail;
  use App\Models\Notification;
  use App\Models\Campaign;
  use App\Models\User;
  use App\Models\UserMongo;
  use JWTAuth;
  use Auth;
  use Entrust;
  use PDF;

  class NetsuiteHelper {

    /*=========================================================
    | createNotification
    |
    | Desc: Creates Notifications
    | Args: type(string), from_id(uniqid), to_type(string),
    | to_id(uniqid), to_client(uniqid), link(string), 
    | desc(string), message(string)
    | Returns: true on success, false on failure
    =========================================================*/

	public static function get_crud_netsuite_record($netsuite_data){
		$accountID 				=    env('NS_ACCOUNT_REALM');
		$realm 					=    env('NS_ACCOUNT_REALM');//NOTICE THE UNDERSCORE
		$consumerKey 			=    env('NS_CONSUMER_KEY'); //Consumer Key
		$consumerSecret 		=    env('NS_CONSUMER_SECRET'); //Consumer Secret
		$tokenKey 				=    env('NS_TOKEN_KEY'); //Token ID
		$tokenSecret  			=    env('NS_TOKEN_SECRET'); //Token Secret    
		$timestamp				=    time();
		$nonce					=    uniqid(mt_rand(1, 1000));
		$baseString = $netsuite_data['httpMethod'] . '&' . rawurlencode($netsuite_data['url']) . "&"
			. rawurlencode("oauth_consumer_key=" . rawurlencode($consumerKey)
				. "&oauth_nonce=" . rawurlencode($nonce)
				. "&oauth_signature_method=HMAC-SHA256"
				. "&oauth_timestamp=" . rawurlencode($timestamp)
				. "&oauth_token=" . rawurlencode($tokenKey)
				. "&oauth_version=1.0"
			);
		$key = rawurlencode($consumerSecret) . '&' . rawurlencode($tokenSecret );
		$signature = rawurlencode(base64_encode(hash_hmac('sha256', $baseString, $key, true)));
		$header = array(
			"Authorization: OAuth realm=\"$realm\", 
			oauth_consumer_key=\"$consumerKey\", 
			oauth_token=\"$tokenKey\", 
			oauth_nonce=\"$nonce\", 
			oauth_timestamp=\"$timestamp\", 
			oauth_signature_method=\"HMAC-SHA256\", 
			oauth_version=\"1.0\", oauth_signature=\"$signature\"",
			"Content-Type: application/json"
		);


		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $netsuite_data['url'] ,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $netsuite_data['httpMethod'],
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_POSTFIELDS  => json_encode($netsuite_data['data_string'])
		));

		$response = curl_exec($curl);

		curl_close($curl);
	}
	
  }