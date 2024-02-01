<?php

  namespace app\Helpers;

  /*==================
  ***** IMPORTS *****
  ==================*/
  use Illuminate\Database\DatabaseManager;
  use Illuminate\Support\Facades\Mail;
  use App\Models\QuickBooksTokens;
  use JWTAuth;
  use Auth;
  use Entrust;
  use PDF;
  use Illuminate\Support\Facades\Http;
	use App\Models\User;
use App\Helpers\QuickbooksCustomerHelper;

  class QuickbooksCustomerHelper {

    /*=========================================================
    | createNotification
    |
    | Desc: Creates Notifications
    | Args: type(string), from_id(uniqid), to_type(string),
    | to_id(uniqid), to_client(uniqid), link(string), 
    | desc(string), message(string)
    | Returns: true on success, false on failure
    =========================================================*/
	
	//Getting Stored customer 
	public static function getMainMethod($invoice_data_arr)
	{
		$result_qb = 0;
		$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
		$user = User::where('id', '=', $user_mongo['user_id'])->first();
		$search_customer_data = QuickbooksCustomerHelper::getCustomer($user_mongo);
		if(isset($search_customer_data['QueryResponse']['Customer'])){
			$invoice_data = '{
			  '.$invoice_data_arr.', 
			  "CustomerRef": {
				"value": "'.$search_customer_data['QueryResponse']['Customer'][0]['Id'].'"
			  }, 
			  "BillEmail": {
				  "Address": "'.$user_mongo['email'].'"
			  },
				"SalesTermRef": {
				  "value": "1"
				}
			}';
			$store_invoice_data = QuickbooksCustomerHelper::storeInvoice($invoice_data);
			$result_qb = 1;
		}else{
			$customer_data = '{
				"BillAddr": {
					"Line1": "'.$user_mongo['address'].'",
					"City": "'.$user_mongo['city'].'",
					"Country": "USA",
					"CountrySubDivisionCode": "'.$user_mongo['street'].'",
					"PostalCode": "'.$user_mongo['zipcode'].'"
				},
				"Notes": "'.$user_mongo['company_name'].'",
				"DisplayName": "'.$user_mongo['first_name'].' '.$user_mongo['last_name'].'",
				"PrimaryPhone": {
					"FreeFormNumber": "'.$user_mongo['phone'].'"
				},
				"PrimaryEmailAddr": {
					"Address": "'.$user_mongo['email'].'"
				}
			}'; 
			$store_customer_data = QuickbooksCustomerHelper::storeCustomer($customer_data);
			if($store_customer_data){
				$search_customer_data = QuickbooksCustomerHelper::getCustomer($user_mongo);
				$invoice_data = '{
				  '.$invoice_data_arr.', 
				  "CustomerRef": {
					"value": "'.$search_customer_data['QueryResponse']['Customer'][0]['Id'].'"
				  }
				}';
				$store_invoice_data = QuickbooksCustomerHelper::storeInvoice($invoice_data);
				$result_qb = 1;
			}
		}
		return $result_qb;
		/*$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
		$user = User::where('id', '=', $user_mongo['user_id'])->first();
		 $customer_data = '{
			"BillAddr": {
				"Line1": "'.$user_mongo['address'].'",
				"City": "",
				"Country": "USA",
				"CountrySubDivisionCode": "CA",
				"PostalCode": ""
			},
			"Notes": "",
			"DisplayName": "'.$user_mongo['first_name'].' '.$user_mongo['last_name'].'",
			"PrimaryPhone": {
				"FreeFormNumber": "'.$user_mongo['phone'].'"
			},
			"PrimaryEmailAddr": {
				"Address": "'.$user_mongo['email'].'"
			}
		}'; 
		$search_company_data = QuickbooksCustomerHelper::getCustomer($customer_data);
		echo'<pre>';print_r($search_company_data);exit; */
	}
	
	//Getting Stored customer 
	public static function getCustomer($customerData)
	{
		$accessToken = QuickbooksCustomerHelper::getValidAccessToken();
		try {
			$response = QuickbooksCustomerHelper::getCustomerData($customerData, $accessToken['access_token']);
			return $response;
		} catch (\Exception $e) {
			return $e;
		}
	}	 
	
	public static function getCustomerData($data, $accessToken){
		$email_id = urlencode($data['email']);
		$curl = curl_init();
		//https://sandbox-quickbooks.api.intuit.com/v3/company/4620816365318998730/customer/64?minorversion=65
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://sandbox-quickbooks.api.intuit.com/v3/company/4620816365318998730/query?query=SELECT%20*%20FROM%20Customer%20WHERE%20PrimaryEmailAddr%20%3D%20%27{$email_id}%27",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => array(
			'User-Agent: Intuit-qbov3-postman-collection1',
			'Accept: application/json',
			'Authorization: Bearer '.$accessToken
		  ),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		return json_decode($response, true);
	}
	
	// Store customer 
	public static function storeCustomer($customerData)
	{
		$accessToken = QuickbooksCustomerHelper::getValidAccessToken();
		try {
			$response = QuickbooksCustomerHelper::createCustomer($customerData, $accessToken['access_token']);
			return $response;
		} catch (\Exception $e) {
			return $e;
		}
	}
	
	private static function createCustomer($data, $accessToken)
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://sandbox-quickbooks.api.intuit.com/v3/company/4620816365318998730/customer?minorversion=1',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => $data,
		  CURLOPT_HTTPHEADER => array(
				'User-Agent: Intuit-qbov3-postman-collection1',
				'Accept: application/json',
				'Content-Type: application/json',
				'Authorization: Bearer '.$accessToken
			  ),
		)); 

		$response = curl_exec($curl);

		curl_close($curl);
		$json = json_decode($response, true);
		return $json;
	}
	
	//Getting Stored customer 
	public static function getProduct($productData)
	{
		$accessToken = QuickbooksCustomerHelper::getValidAccessToken();
		try {
			$response = QuickbooksCustomerHelper::getProductData($productData, $accessToken['access_token']);
			return $response;
		} catch (\Exception $e) {
			return $e;
		}
	}	 
	
	public static function getProductData($data, $accessToken){
		$siteNo = urlencode($data);
		$curl = curl_init();
		//https://sandbox-quickbooks.api.intuit.com/v3/company/4620816365318998730/customer/64?minorversion=65
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://sandbox-quickbooks.api.intuit.com/v3/company/4620816365318998730/query?query=SELECT%20*%20FROM%20Item%20WHERE%20Name%20%3D%20%27{$siteNo}%27",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => array(
			'User-Agent: Intuit-qbov3-postman-collection1',
			'Accept: application/json',
			'Authorization: Bearer '.$accessToken
		  ),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		return json_decode($response, true);
	}	
	
	// Store Invoice 
	public static function storeProduct($productData)
	{
		$accessToken = QuickbooksCustomerHelper::getValidAccessToken();
		try {
			$response = QuickbooksCustomerHelper::createProduct($productData, $accessToken['access_token']);
			return $response;
		} catch (\Exception $e) {
			return $e;
		}
	}
	
	private static function createProduct($data, $accessToken)
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://sandbox-quickbooks.api.intuit.com/v3/company/4620816365318998730/item?minorversion=1',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => $data,
		  CURLOPT_HTTPHEADER => array(
				'User-Agent: Intuit-qbov3-postman-collection1',
				'Accept: application/json',
				'Content-Type: application/json',
				'Authorization: Bearer '.$accessToken
			  ),
		)); 

		$response = curl_exec($curl);

		curl_close($curl);
		$json = json_decode($response, true);
		return $json;
	}

	// Store Invoice 
	public static function storeInvoice($invoiceData)
	{
		$accessToken = QuickbooksCustomerHelper::getValidAccessToken();
		try {
			$response = QuickbooksCustomerHelper::createInvoice($invoiceData, $accessToken['access_token']);
			return $response;
		} catch (\Exception $e) {
			return $e;
		}
	}
	
	private static function createInvoice($data, $accessToken)
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://sandbox-quickbooks.api.intuit.com/v3/company/4620816365318998730/invoice?minorversion=1',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => $data,
		  CURLOPT_HTTPHEADER => array(
				'User-Agent: Intuit-qbov3-postman-collection1',
				'Accept: application/json',
				'Content-Type: application/json',
				'Authorization: Bearer '.$accessToken
			  ),
		)); 

		$response = curl_exec($curl);

		curl_close($curl);
		$json = json_decode($response, true);
		return $json;
	}
	
	public static function getValidAccessToken()
    {
		
        $clientId = env('QUICKBOOKS_CLIENT_ID');
        $clientSecret = env('QUICKBOOKS_CLIENT_SECRET');
        $redirectUri = env('QUICKBOOKS_REDIRECT_URI');
		/*$url = 'https://sandbox-quickbooks.api.intuit.com/v3/oauth2/auth';*/
		
		$quickbookstokens = QuickBooksTokens::where('id', '=', 1)->first();
		
		$accessToken = array(
			'access_token' => $quickbookstokens['access_token'],
			'refresh_token' => $quickbookstokens['refresh_token'],
			'expires_at' => $quickbookstokens['expires_at']
		);
		
		$exired_result = QuickbooksCustomerHelper::isTokenExpired($quickbookstokens['expires_at']);
		if($exired_result == 1){
            $newAccessToken = QuickbooksCustomerHelper::refreshAccessToken($quickbookstokens['refresh_token']);
			$quickbookstokens->access_token = $newAccessToken['access_token'];
			$quickbookstokens->refresh_token = $newAccessToken['refresh_token'];
            $quickbookstokens->expires_at = time()+3600;
			$quickbookstokens->save();                   
			return $newAccessToken;
        }
		return $accessToken;
    }

    public static function isTokenExpired($accessToken)
    {
		if($accessToken < time()){
			$result = '1';
		}else{
			$result = '2';
		}
        return $result;
    }

    public static function refreshAccessToken($refreshToken)
    {
        $clientId = env('QUICKBOOKS_CLIENT_ID');
        $clientSecret = env('QUICKBOOKS_CLIENT_SECRET');
        $redirectUri = env('QUICKBOOKS_REDIRECT_URI');

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => 'grant_type=refresh_token&redirect_uri=https%3A%2F%2Fdeveloper.intuit.com%2Fv2%2FOAuth2Playground%2FRedirectUrl&refresh_token='.$refreshToken.'&response_type=code&client_id='.$clientId.'&client_secret='.$clientSecret,
		  CURLOPT_HTTPHEADER => array(
			'Content-Type: application/x-www-form-urlencoded'
		  ),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		return json_decode($response, true);
    }
	
}