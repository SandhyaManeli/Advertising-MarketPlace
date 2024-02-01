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

  class NimbleHelper {

    /*=========================================================
    | createNotification
    |
    | Desc: Creates Notifications
    | Args: type(string), from_id(uniqid), to_type(string),
    | to_id(uniqid), to_client(uniqid), link(string), 
    | desc(string), message(string)
    | Returns: true on success, false on failure
    =========================================================*/
	
	const NMBL_ADD_CONTACT_STATUS = 0;
	const NMBL_ADD_COMPANY_STATUS = 0;
	const NMBL_UPDATE_USER_STATUS = 0;
	const
	    OAUTH_ACCESS_TOKEN_URL = "https://api.nimble.com/oauth/token?",
	    OAUTH_AUTHORIZE_URL = "https://api.nimble.com/oauth/authorize?",
	    OAUTH_REQUEST_URL = "https://api.nimble.com/api/v1/"
	  ;
	const ACCESS_TOKEN = 'bUSk7Mpl4i5JBD5rOFyS99btHdNdw1';
	  
	
	//Getting Nimble All Contacts
	public static function getContactList(){
		$headers = array('Accept' => 'application/json','Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8');
		$params = array(
			'tags' => 1,
			'record_type' => 'all',
			'keyword' => 'testampdevseller@gmail.com', //to search particular value
			'access_token' => self::ACCESS_TOKEN
	    );
		$curl_handler = curl_init();
	    $url = sprintf('%scontacts/list?%s', self::OAUTH_REQUEST_URL, http_build_query($params, '', '&'));
	    curl_setopt($curl_handler, CURLOPT_URL, $url);
	    curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($curl_handler, CURLOPT_HTTPHEADER, $headers);
	    $output = curl_exec($curl_handler);
	    curl_close($curl_handler);
		$json = json_decode($output, true);
	    return $json;
	} 
	
	//Add Contact
	public static function addContact($data_string){ 
	  	$params = array('access_token' => self::ACCESS_TOKEN);
		if(self::NMBL_ADD_CONTACT_STATUS == 1){
			if(!is_array($data_string)){
				throw new Exception('Query Must be in array format');
			}
			$curl_handler = curl_init();
			$url = sprintf('%scontact/?%s', self::OAUTH_REQUEST_URL,http_build_query($params, '', '&'));
			curl_setopt($curl_handler, CURLOPT_URL, $url);
			curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl_handler, CURLOPT_POST, 1);
			curl_setopt($curl_handler, CURLOPT_POSTFIELDS,$data_string['data_string']);
			curl_setopt($curl_handler, CURLOPT_HTTPHEADER,array('Content-Type:application/json'));
			$output = curl_exec($curl_handler);
			curl_close($curl_handler); 
			$json = json_decode($output, true);
			return $json;		  
		}else{
			return '0';
		}
	}
	
	//Add Company
	public static function addCompany($company_data){
	  	$params = array('access_token' => self::ACCESS_TOKEN);
		if(self::NMBL_ADD_COMPANY_STATUS == 1){
			if(!is_array($company_data)){
				throw new Exception('Query Must be in array format');
			}
			$curl_handler = curl_init();
			$url = sprintf('%scontact/?%s', self::OAUTH_REQUEST_URL,http_build_query($params, '', '&'));
			curl_setopt($curl_handler, CURLOPT_URL, $url);
			curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl_handler, CURLOPT_POST, 1);
			curl_setopt($curl_handler, CURLOPT_POSTFIELDS,$company_data['company_data']);
			curl_setopt($curl_handler, CURLOPT_HTTPHEADER,array('Content-Type:application/json'));
			$output = curl_exec($curl_handler);
			curl_close($curl_handler);
			$json = json_decode($output, true);
			return $json;		  
		}else{
			return 'we cannot proceed';
		}
	}
	 
	//Search Records
	public static function searchRecords($search_company){
	  	$params = array('access_token' => self::ACCESS_TOKEN);
	  	if(!is_array($search_company)){
			throw new Exception('Search Query Must be in array format');
		}
	  	$curl_handler = curl_init();
	    $url = sprintf('%scontacts/?%s&query=%s', self::OAUTH_REQUEST_URL, http_build_query($params, '', '&'),rawurlencode(json_encode($search_company['search_company'])));
		curl_setopt($curl_handler, CURLOPT_URL, $url);
	    curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($curl_handler, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
	    $output = curl_exec($curl_handler);
	    curl_close($curl_handler);
	    $json = json_decode($output, true);
	    return $json;
	}
	
	//Update User Details
	public static function updateUserDetails($data_string){
		$user_id = $data_string['user_id'];
		if(self::NMBL_UPDATE_USER_STATUS == 1){
			if(!is_array($data_string)){
				throw new Exception('Query Must be in array format');
			}
			$curl_handler = curl_init();
			$url = 'https://api.nimble.com/api/v1/contact/'.$user_id.'?replace=1&access_token='.self::ACCESS_TOKEN;	
			curl_setopt($curl_handler, CURLOPT_URL, $url);
			curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl_handler, CURLOPT_POST, 1);
			curl_setopt($curl_handler, CURLOPT_CUSTOMREQUEST, $data_string['httpMethod']);
			curl_setopt($curl_handler, CURLOPT_POSTFIELDS,$data_string['data_string']);
			curl_setopt($curl_handler, CURLOPT_HTTPHEADER,array('Content-Type:application/json'));
			$output = curl_exec($curl_handler);
			curl_close($curl_handler);
			$json = json_decode($output, true);
			return $json;		  
		}else{
			return 'we cannot proceed';
		}
	}
	
}