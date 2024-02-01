<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\Marker;
use App\Models\ClientType;
use App\Models\Client;
use App\Models\ClientMongo;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\UserMongo;
use App\Models\Campaign;
use App\Models\ShortListedProduct;
use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductBooking;

use App\Helpers\NotificationHelper;
use App\Helpers\NetsuiteHelper;
use App\Helpers\NimbleHelper;
use JWTAuth;
use JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Jobs\UpdateUserEverywhere;
use Log;
use App\Events\accountSuperAdminEvent;
use App\Events\CampaignTransferEvent;


// ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
// error_reporting(E_ALL);

class UserController extends Controller
{
  private $input;
  private $request;
  private $default_password = "BbiUser789#";
  /**
  * Create a new controller instance.
  *
  * @return void
  */
  public function __construct(Request $request)
  {
    // Resolve dependencies out of container
	//echo 'in const';exit;
    $this->request = $request;
    if ($request->isJson()) {
      $this->input = $request->json()->all();
    } else {
      $this->input = $request->all();
    }

    // $this->middleware('jwt.auth', ['only' => [
    //   'addUser',
    //   'getAllUsers',
    //   'getUserProfile',
    //   'activateUser'
    // ]]);
    // $this->middleware('role:admin|owner', ['only' => [
    //   'addUser',
    //   'getAllUsers'
    // ]]);
    // $this->middleware('role:owner', ['only' => [
    //   'activateUser'
    // ]]);
  }


  /*
  * Registration by an Admin. Authentication/Authorization is required.
  * Only Admin or Owner can register new user with this method.
  */
  // public function addUser(){
  //   $this->validate($this->request, 
  //     [
  //       'email' => 'required|unique:users|email'
  //       // 'first_name' => 'required',
  //       // 'last_name' => 'required'
  //     ],
  //     [
  //       'email.required' => 'Email is required',
  //       'email.unique' => 'The email entered is already used with an account',
  //       'email.email' => 'Invalid email'
  //     ]
  //   );
  //   $user = new User();
  //   if(isset($this->input['username'])){
  //     $user->username = $this->input['username'];
  //   }
  //   $user->email = $this->input['email'];
  //   $user->salt = str_random(7);
  //   $user->password = md5($this->default_password . $user->salt);
  //   if($user->save()){
  //     $user_mongo = new UserMongo;
  //     $user_mongo->id = uniqid();
  //     $user_mongo->user_id = $user->id;
  //     $user_mongo->first_name = isset($this->input['first_name']) ? $this->input['first_name'] : "";
  //     $user_mongo->last_name = isset($this->input['last_name']) ? $this->input['last_name'] : "";
  //     $user_mongo->phone = isset($this->input['phone']) ? $this->input['phone'] : "";
  //     $user_mongo->company_name = isset($this->input['company_name']) ? $this->input['company_name'] : "";
  //     $user_mongo->company_type = isset($this->input['company_type']) ? $this->input['company_type'] : "";
  //     if($user_mongo->save()){
  //       return response()->json(['status' => 1, 'message' => 'user saved successfully.']);
  //     }
  //     else{
  //       return response()->json(['status' => 0, 'message' => 'user saved partially.']);
  //     }
  //   }
  //   else{
  //     return response()->json(['status' => 1, 'message' => 'failed to save user.']);
  //   }
  // }

  /*
  * Registration by a user himself. No authentication/authorization
  */
  public function register(Request $request){
	   if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
    $this->validate($this->request, 
      [
        'email' => 'required|unique:users|email',
        'name' => 'required',
        'password' => 'required|min:6'
      ],
      [
        'email.required' => 'Email is required',
        'email.unique' => 'The email entered is already used with an account',
        'email.email' => 'Invalid email',
        'name.required' => 'Name is required',
        'password.required' => 'Password is required',
        'password.min' => 'Password length should be at least 6 characters'
      ]
    );
    // other regex candidates for password matching 
    // (?=.*[A-Z]+)(?=.*[a-z]+)(?=.*[0-9]+)(?=.*[!@#\$%\^&*\(\)_~\.]+)
    // .*[A-Z]+[a-z]+[0-9]+[!@#\$%\^&*\(\)_~\.]+.*
	$emailvalueUp    = strtoupper($this->input['email']);
	$emailvalueLower = strtolower($this->input['email']);

	$campaign = Campaign::orWhere('user_email', '=', $emailvalueUp)->orWhere('user_email', '=', $emailvalueLower)->first();
	if(empty($campaign)){
		$repeated_user = UserMongo::where('email', '=', $this->input['email'])->get();
    if(!empty($repeated_user) && count($repeated_user) > 0){
      return response()->json(['status' => 0, 'message' => 'The email id given already exists in database.']);
    }

    $user = new User();
    if(isset($this->input['username'])){
      $user->username = $this->input['username'];
    }
    $user->email = $this->input['email'];
    $user->salt = str_random(7);
    $user->password = md5($this->input['password'] . $user->salt);
    $user->client_id = isset($this->input['client_id']) ? $this->input['client_id'] : NULL;
    $user->activated = false;
    if($user->save()){
      // Save data to elasticsearch :: Pankaj 20 Oct 2021
      $get_data = User::where('id', '=', $user->id)->first();
      $this->es_etl_users_auth($get_data, "insert");
      // assign basic user role
      $role = Role::where('name', '=', 'basic_user')->first();
      $user->roles()->attach($role);
      // create the user in mongo for profile details
		

      $client_mongo = ClientMongo::where('client_id', '=', $user->client_id)->first();
      $user_mongo = new UserMongo;
      $user_mongo->id = uniqid();
      $user_mongo->email = $this->input['email'];
      $user_mongo->user_id = $user->id;
      $nimble_client_id = 'AMP'.$user_mongo->user_id;
	  //echo '<pre>';print_r($nimble_client_id);exit;
      if(isset($this->input['client_id'])){
        $user_mongo->client_id = $client_mongo->client_id;
        $user_mongo->client_mongo_id = $client_mongo->id;
      }
	  
	  $user_mongo->register_from = '';
      if(isset($this->input['register_from']) && $this->input['register_from'] == 'advertising-market-platform'){
        $user_mongo->register_from = 1;
      }
	  
      $full_name = explode(" ", $this->input['name']);
      $user_mongo->first_name = isset($full_name, $full_name[0]) ? $full_name[0] : "";
      for($i = 1; $i < count($full_name) - 1; $i++){
        $user_mongo->middle_name .= $full_name[$i] . " ";
      }
      $user_mongo->last_name = isset($full_name) && (count($full_name) > 1) ? $full_name[ count($full_name) - 1 ] : "";
      $user_mongo->name = isset($this->input['name']) ? $this->input['name'] : "";
      $user_mongo->phone = isset($this->input['phone']) ? $this->input['phone'] : "";
      $user_mongo->last_name = isset($this->input['last_name']) ? $this->input['last_name'] : "";
      $user_mongo->company_name = isset($this->input['company_name']) ? $this->input['company_name'] : "";
      $user_mongo->company_type = isset($this->input['company_type']) ? $this->input['company_type'] : "";
      $user_mongo->address = isset($this->input['address']) ? $this->input['address'] : "";
      $user_mongo->account_type = isset($this->input['account_type']) ? $this->input['account_type'] : "";
      $user_mongo->verification_code = base64_encode(md5($this->input['email'] . uniqid()));
      $user_mongo->verification_code_expiry = new \MongoDB\BSON\UTCDateTime(strtotime("+30 minutes") * 1000); //strtotime returns seconds, we need milliseconds
      $user_mongo->verified = false;		  
      if($user_mongo->save()){ 
		//NetSuite
		/*$u_type = 1;
		if($user_mongo->account_type == 'Individual Account'){
			$a_type = true;
		}else if($user_mongo->account_type == 'Business Account'){
			$a_type = false;
		}
		
		$data_string = array(
			'externalId' => $user_mongo->id,
			'companyName' => $user_mongo->company_name,
			'firstName' => $user_mongo->first_name,
			'middleName' => $user_mongo->middle_name,
			'lastName' => $user_mongo->last_name,
			'email' => $user_mongo->email,
			'phone' => $user_mongo->phone,
			'subsidiary' => 2,
			'salesRep' => -5,
			'custentity2' => $u_type,
			'isPerson' => $a_type
		);
		
		$url =    env('NS_CUSTOMER_URL');
		
		NetsuiteHelper::get_crud_netsuite_record([
            'httpMethod' => 'POST',
            'url' => $url,
            'data_string' => $data_string
          ]);
		*/
		//NetSuite
		
		
		
		//========================Nimble Start==========================//
		if(!empty($user_mongo->company_type))
		{
			$nimble_user_type = 'Seller';
		}
		else{
			$nimble_user_type = 'Buyer';
		}
		//Add Company
			$search_company = array('and' => array(array('company name' => array('is' => $user_mongo->company_name)),array('record type' => array('is' => 'company'))));
			$search_company_data = NimbleHelper::searchRecords([
				'httpMethod' => 'POST',
				'search_company' => $search_company 
			]);
			$company_id_contact = 0;
			if($search_company_data['meta']['total'] == 0){
				$company_data = json_encode(array('fields' => array('company name' => array(array('value' => $user_mongo->company_name,'modifier' => ''))),'record_type' => 'company','tags' => ''));
				$response_company = NimbleHelper::addCompany([
					'httpMethod' => 'POST',
					'company_data' => $company_data
				  ]);
				  if($response_company != 0){
					$company_id_contact = $response_company['id'];
				  }
			}else{
				$company_id_contact = $search_company_data['resources'][0]['id'];
			}
		//Add Company End
		//Add Contact
			if($company_id_contact > 0){
				$data_string = json_encode(array('fields' => array('first name' => array(array('value' => $user_mongo->first_name,'modifier' => '')),'last name' => array(array('value' => $user_mongo->last_name,'modifier' => '')),'email' => array(array('value' => $user_mongo->email,'modifier' => '')), 'phone' => array(array('modifier' => 'work', 'value' => $user_mongo->phone)), 'parent company' => array(array('modifier' => '', 'value' => $user_mongo->company_name, 'extra_value'=> $company_id_contact))),'record_type' => 'person','tags' => array($user_mongo->account_type,$nimble_user_type,$nimble_client_id)));

				$nimble_data = NimbleHelper::addContact([
					'httpMethod' => 'POST',
					'data_string' => $data_string
				]);
				if($nimble_data['id'] != ''){
					$get_data_nimble = UserMongo::where('id', '=', $user_mongo->id)->first();
					$get_data_nimble->nimble_user_id = $nimble_data['id'];
					$get_data_nimble->save();
				}
			}
		//Add Contact End
		//========================Nimble End==========================//

		// Save data to elasticsearch :: Pankaj 20 Oct 2021
        $get_data = UserMongo::where('id', '=', $user_mongo->id)->first();
        $this->es_etl_users($get_data, "insert");
        $name = $user_mongo->first_name . " " . $user_mongo->last_name;
        $verification_code = base64_encode(md5($this->input['email'] . uniqid()));
        Mail::send('mail.registration', ['user' => $user_mongo], function($message) use ($user_mongo){
          $message->to($user_mongo->email, $user_mongo->first_name . " " . $user_mongo->last_name)->subject('Welcome!');
        });
		 
		event(new accountSuperAdminEvent([
              'type' => Notification::$NOTIFICATION_TYPE['account-super-admin-setup'],
              'from_id' => $user_mongo->id,
              'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
              'to_id' => null,
              'to_client' => null,
              'desc' => "New User registered",
             //'message' => ucfirst($name) ." Registered as a User and Waiting For activation.",
              'message' => ucfirst($user_mongo->first_name . " " . $user_mongo->last_name) ." Registered as a User and Waiting For activation.",
			  'data' => ["user_id" => $user_mongo->id]
            ]));
			$notification_obj = new Notification;
			$notification_obj->id = uniqid();
            $notification_obj->type = "User_registartion";
            $notification_obj->from_id =  $user_mongo->id;
            $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['bbi'];
            $notification_obj->to_id = null;
            $notification_obj->to_client = null;
            $notification_obj->desc = "New User registered";
            //$notification_obj->message = ucfirst($name) ." Registered as a User and Waiting For activation.";
            $notification_obj->message = ucfirst($user_mongo->first_name . " " . $user_mongo->last_name) ." Registered as a User and Waiting For activation.";
                    $notification_obj->user_id = $user_mongo->id;
					$notification_obj->status = 0;
                    $notification_obj->save();


        $notif_mail_message = <<<EOF
          A new user joined Advertising Marketplace. User details: <br /><br />
          
          Name: {$user_mongo->first_name} {$user_mongo->last_name}<br />
          Email: {$user_mongo->email}<br />
          Company name: {$user_mongo->company_name}<br />
          Phone: {$user_mongo->phone}<br /><br />

          For more details, Please visit the 'user management' section on admin dashboard.
EOF;
        $mail_tmpl_params = [
          'sender_email' => 'reach@billboardsindia.com', 
          'receiver_name' => 'Sravani', 
          'mail_message' => $notif_mail_message
        ];
        $mail_data = [
          //'email_to' => 'chanikya@billboardsindia.com',
          'email_to' => 'sravani.yelesam@peopletech.com',
          'recipient_name' => 'Sravani'
        ];
        Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data){
          //$message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('A new user joined. - Billboards India');
          $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('A new user joined. - Advertising Marketplace');
        });
        return response()->json(['status' => 1, 'message' => 'Account created successfully.']);
      }
     else{
        return response()->json(['status' => 0, 'message' => 'Only some details were saved. Please contact admin.']);
      }
    }
    else{
      return response()->json(['status' => 1, 'message' => 'Failed to create account.']);
    }
		
	}

	else{
		try {
    // make sure that a user with the same email id doesn't exist in mongo either.
    $repeated_user = UserMongo::where('email', '=', $this->input['email'])->get();
    if(!empty($repeated_user) && count($repeated_user) > 0){
      return response()->json(['status' => 0, 'message' => 'The email id given already exists in database.']);
    }

    $user = new User();
    if(isset($this->input['username'])){
      $user->username = $this->input['username'];
    }
    $user->email = $this->input['email'];
    $user->salt = str_random(7);
    $user->password = md5($this->input['password'] . $user->salt);
    $user->client_id = isset($this->input['client_id']) ? $this->input['client_id'] : NULL;
    $user->activated = false;
    if($user->save()){
      // Save data to elasticsearch :: Pankaj 20 Oct 2021
      $get_data = User::where('id', '=', $user->id)->first();
      $this->es_etl_users_auth($get_data, "insert");
      // assign basic user role
      $role = Role::where('name', '=', 'basic_user')->first();
      $user->roles()->attach($role);
      // create the user in mongo for profile details
		

      $client_mongo = ClientMongo::where('client_id', '=', $user->client_id)->first();
      $user_mongo = new UserMongo;
      $user_mongo->id = uniqid();
      $user_mongo->email = $this->input['email'];
      $user_mongo->user_id = $user->id;
	  $nimble_client_id = 'AMP'.$user_mongo->user_id;
      if(isset($this->input['client_id'])){
        $user_mongo->client_id = $client_mongo->client_id;
        $user_mongo->client_mongo_id = $client_mongo->id;
      }
	  $user_mongo->register_from = '';
      if(isset($this->input['register_from']) && $this->input['register_from'] == 'advertising-market-platform'){
        $user_mongo->register_from = 1;
      }
      $full_name = explode(" ", $this->input['name']);
      $user_mongo->first_name = isset($full_name, $full_name[0]) ? $full_name[0] : "";
      for($i = 1; $i < count($full_name) - 1; $i++){
        $user_mongo->middle_name .= $full_name[$i] . " ";
      }
      $user_mongo->last_name = isset($full_name) && (count($full_name) > 1) ? $full_name[ count($full_name) - 1 ] : "";
      $user_mongo->last_name = isset($this->input['last_name']) ? $this->input['last_name'] : "";
      $user_mongo->name = isset($this->input['name']) ? $this->input['name'] : "";
      $user_mongo->phone = isset($this->input['phone']) ? $this->input['phone'] : "";
      $user_mongo->company_name = isset($this->input['company_name']) ? $this->input['company_name'] : "";
      $user_mongo->company_type = isset($this->input['company_type']) ? $this->input['company_type'] : "";
      $user_mongo->address = isset($this->input['address']) ? $this->input['address'] : "";
      $user_mongo->account_type = isset($this->input['account_type']) ? $this->input['account_type'] : "";
      
      $user_mongo->verification_code = base64_encode(md5($this->input['email'] . uniqid()));
      $user_mongo->verification_code_expiry = new \MongoDB\BSON\UTCDateTime(strtotime("+30 minutes") * 1000); //strtotime returns seconds, we need milliseconds
      $user_mongo->verified = false;
	  
	  $campaign = Campaign::orWhere('user_email', '=', $emailvalueUp)->orWhere('user_email', '=', $emailvalueLower)->first();
	  //$campaign[0]->id;
	  $campaign->id;
	  //$campaign_id = $campaign[0]->id;
	  $campaign_id = $campaign->id;
	  //echo '<pre>';print_r($campaign_id);exit; 
	  //echo '<pre>';print_r($campaign);exit; 
		if(!empty($campaign)){
		if (isset($campaign_id)) {
			
				//echo '<pre>in if'; print_r($input); exit;  
				$campaign_edit = Campaign::where('id', '=', $campaign_id)->first();
				$campaign_edit->user_id = $user->id;
				$campaign_edit->created_by = $user_mongo->id;
				$campaign_edit->save();
				//$campaign_edit->name = isset($input['name']) ? $input['name'] : $campaign_edit->name; 
			}
		}
		/*else{
			return response()->json(['status' => 0, 'message' => 'No']);
		}*/ 
	 
     if($user_mongo->save()){
		  
		  
		/*  //NetSuite
		$u_type = 1;
		if($user_mongo->account_type == 'Individual Account'){
			$a_type = true;
		}else if($user_mongo->account_type == 'Business Account'){
			$a_type = false;
		}
		
		$data_string = array(
			'externalId' => $user_mongo->id,
			'companyName' => $user_mongo->company_name,
			'firstName' => $user_mongo->first_name,
			'middleName' => $user_mongo->middle_name,
			'lastName' => $user_mongo->last_name,
			'email' => $user_mongo->email,
			'phone' => $user_mongo->phone,
			'subsidiary' => 2,
			'salesRep' => -5,
			'custentity2' => $u_type,
			'isPerson' => $a_type
		);
		
		$url =    env('NS_CUSTOMER_URL');
		
		NetsuiteHelper::get_crud_netsuite_record([
            'httpMethod' => 'POST',
            'url' => $url,
            'data_string' => $data_string
          ]);
		
		//NetSuite */
		
		
		
		//========================Nimble Start==========================//
		if(!empty($user_mongo->company_type))
		{
			$nimble_user_type = 'Seller';
		}
		else{
			$nimble_user_type = 'Buyer';
		}
		//Add Company
			$search_company = array('and' => array(array('company name' => array('is' => $user_mongo->company_name)),array('record type' => array('is' => 'company'))));
			$search_company_data = NimbleHelper::searchRecords([
				'httpMethod' => 'POST',
				'search_company' => $search_company 
			]);
			$company_id_contact = 0;
			if($search_company_data['meta']['total'] == 0){
				$company_data = json_encode(array('fields' => array('company name' => array(array('value' => $user_mongo->company_name,'modifier' => ''))),'record_type' => 'company','tags' => ''));
				$response_company = NimbleHelper::addCompany([
					'httpMethod' => 'POST',
					'company_data' => $company_data
				  ]);
				  if($response_company != 0){
						$company_id_contact = $response_company['id'];
				  }
			}else{
				$company_id_contact = $search_company_data['resources'][0]['id'];
			}
		//Add Company End
		//Add Contact
			if($company_id_contact > 0){
				$data_string = json_encode(array('fields' => array('first name' => array(array('value' => $user_mongo->first_name,'modifier' => '')),'last name' => array(array('value' => $user_mongo->last_name,'modifier' => '')),'email' => array(array('value' => $user_mongo->email,'modifier' => '')), 'phone' => array(array('modifier' => 'work', 'value' => $user_mongo->phone)), 'parent company' => array(array('modifier' => '', 'value' => $user_mongo->company_name, 'extra_value'=> $company_id_contact))),'record_type' => 'person','tags' => array($user_mongo->account_type,$nimble_user_type,$nimble_client_id)));

				$nimble_data = NimbleHelper::addContact([
					'httpMethod' => 'POST',
					'data_string' => $data_string
				]);
				if($nimble_data['id'] != ''){
					$get_data_nimble = UserMongo::where('id', '=', $user_mongo->id)->first();
					$get_data_nimble->nimble_user_id = $nimble_data['id'];
					$get_data_nimble->save();
				}
			}
		//Add Contact End
		//========================Nimble End==========================//
		
		// Save data to elasticsearch :: Pankaj 20 Oct 2021
        $get_data = UserMongo::where('id', '=', $user_mongo->id)->first();
        $this->es_etl_users($get_data, "insert");
        $name = $user_mongo->first_name . " " . $user_mongo->last_name;
        $verification_code = base64_encode(md5($this->input['email'] . uniqid()));
        Mail::send('mail.registration', ['user' => $user_mongo], function($message) use ($user_mongo){
          $message->to($user_mongo->email, $user_mongo->first_name . " " . $user_mongo->last_name)->subject('Welcome!');
        });
		
		event(new accountSuperAdminEvent([
              'type' => Notification::$NOTIFICATION_TYPE['account-super-admin-setup'],
              'from_id' => $user_mongo->id,
              'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
              'to_id' => null,
              'to_client' => null,
              'desc' => "New User registered",
              //'message' => ucfirst($name) ." Registered as a User and Waiting For activation.",
              'message' => ucfirst($user_mongo->first_name . " " . $user_mongo->last_name) ." Registered as a User and Waiting For activation.",
			  'data' => ["user_id" => $user_mongo->id]
            ]));
			$notification_obj = new Notification;
			$notification_obj->id = uniqid();
            $notification_obj->type = "User_registartion";
            $notification_obj->from_id =  $user_mongo->id;
            $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['bbi'];
            $notification_obj->to_id = null;
            $notification_obj->to_client = null;
            $notification_obj->desc = "New User registered";
            //$notification_obj->message = ucfirst($name) ." Registered as a User and Waiting For activation.";
            $notification_obj->message = ucfirst($user_mongo->first_name . " " . $user_mongo->last_name) ." Registered as a User and Waiting For activation.";
                    $notification_obj->user_id = $user_mongo->id;
					$notification_obj->status = 0;
                    $notification_obj->save();


        $notif_mail_message = <<<EOF
          A new user joined Advertising Marketplace. User details: <br /><br />
          
          Name: {$user_mongo->first_name} {$user_mongo->last_name}<br />
          Email: {$user_mongo->email}<br />
          Company name: {$user_mongo->company_name}<br />
          Phone: {$user_mongo->phone}<br /><br />

          For more details, Please visit the 'user management' section on admin dashboard.
EOF;
        $mail_tmpl_params = [
          'sender_email' => 'reach@billboardsindia.com', 
          'receiver_name' => 'Sravani', 
          'mail_message' => $notif_mail_message
        ];
        $mail_data = [
          //'email_to' => 'chanikya@billboardsindia.com',
          'email_to' => 'sravani.yelesam@peopletech.com',
          'recipient_name' => 'Sravani'
        ];
        Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data){
          //$message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('A new user joined. - Billboards India');
          $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('A new user joined. - Advertising Marketplace');
        });
        return response()->json(['status' => 1, 'message' => 'Account created successfully.']);
     }
    else{
       return response()->json(['status' => 0, 'message' => 'Only some details were saved. Please contact admin.']);
      }
    }
    else{
      return response()->json(['status' => 1, 'message' => 'Failed to create account.']);
    }
	
	} catch (Exception $ex) {
		Log::error($ex);
		print_r($ex);
						}
	}
  }

  public function es_etl_users_auth($get_data, $opr){
      $url_insert = env('ES_SERVER_URL_INSERT');
      $url_delete = env('ES_SERVER_URL_DELETE');

      $index = env('ES_USERS_AUTH');   
      $id = $get_data->id;
          
      if ( $opr == "delete" ) {
          $data_string = array(
              "index" => $index,
              "data" => array (
                  $get_data
              )
          );
          $data = json_encode($data_string);
          $ch = curl_init( $url_delete );
          curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
          curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
          curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "DELETE");
          curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
          $result = curl_exec($ch);
          curl_close($ch);
      } else {
          if ( $opr == "update" ) {
            $data_string = array(
                "index" => $index,
                "data" => array (
                    array (
                        "id" => $id
                    )
                )
            );
            $data = json_encode($data_string);
            $ch = curl_init( $url_delete );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            $result = curl_exec($ch);
            curl_close($ch);
          }

          $data_string = array(
              "index" => $index,
              "data" => array (
                  array (
                      "id" => $get_data->id,
                      "email" => $get_data->email
                  )
              )
          );
          $data = json_encode($data_string);
          $ch = curl_init( $url_insert );
          curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
          curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
          curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
          $result = curl_exec($ch);
          curl_close($ch);
      }
  }

  public function es_etl_users($get_data, $opr){
      $url_insert = env('ES_SERVER_URL_INSERT');
      $url_delete = env('ES_SERVER_URL_DELETE');

      $index = env('ES_USERS');       
      $id = $get_data->id;
          
      if ( $opr == "delete" ) {
          $data_string = array(
              "index" => $index,
              "data" => array (
                  $get_data
              )
          );
          $data = json_encode($data_string);
          $ch = curl_init( $url_delete );
          curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
          curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
          curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "DELETE");
          curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
          $result = curl_exec($ch);
          curl_close($ch);
      } else {
          if ( $opr == "update" ) {
            $data_string = array(
                "index" => $index,
                "data" => array (
                    array (
                        "id" => $id
                    )
                )
            );
            $data = json_encode($data_string);            
            $ch = curl_init( $url_delete );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            $result = curl_exec($ch);
            curl_close($ch);
          }

          $updated_at = $get_data->updated_at;
          $d_updated_at = date("Y-m-d", strtotime($updated_at));
          $t_updated_at = date("H:i:s", strtotime($updated_at));
          $new_updated_at = $d_updated_at."T".$t_updated_at.".000Z";

          $created_at = $get_data->created_at;
          $d_created_at = date("Y-m-d", strtotime($created_at));
          $t_created_at = date("H:i:s", strtotime($created_at));
          $new_created_at = $d_created_at."T".$t_created_at.".000Z";

          if ( is_null($get_data->verification_code_expiry) ) {
                $t_verification_code_expiry = null;
            } else {
                $verification_code_expiry = $get_data->verification_code_expiry;
                $d_verification_code_expiry = date("Y-m-d", strtotime($verification_code_expiry));
                $t_verification_code_expiry = date("H:i:s", strtotime($verification_code_expiry));
                $new_verification_code_expiry = $d_verification_code_expiry."T".$t_verification_code_expiry.".000Z";
            }
          
          $data_string = array(
              "index" => $index,
              "data" => array (
                  array (
                      "id" => $get_data->id,
                      "client_mongo_id" => $get_data->client_mongo_id,
                      "first_name" => $get_data->first_name,
                      "last_name" => $get_data->last_name,
                      "email" => $get_data->email,
                      "user_id" => $get_data->user_id,
                      "client_id" => $get_data->client_id,
                      "company_name" => $get_data->company_name,
                      "phone" => $get_data->phone,
                      "loggedInUser" => $get_data->loggedInUser,
                      "company_type" => $get_data->company_type,
                      "address" => $get_data->address,
                      "verified" => $get_data->verified,
                      "verification_code" => $get_data->verification_code,
                      "verification_type" => $get_data->verification_type,
                      "seller" => $get_data->seller,
                      "middle_name" => $get_data->middle_name,
                      "verification_code_expiry" => $new_verification_code_expiry,
                      "updated_at" => $new_updated_at,
                      "created_at" => $new_created_at
                    )
              )
          );
          $data = json_encode($data_string);
          $ch = curl_init( $url_insert );
          curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
          curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
          curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
          $result = curl_exec($ch);
          curl_close($ch);
      }
  }

	public function login()
  {
    $this->validate($this->request, 
      [
        'email' => 'required',
        'password' => 'required'
      ],
      [
        'email.required' => 'Email is required',
        'password.required' => 'Password is required'
      ]
    );
    
    $user = User::where('email', $this->input['email'])->first();
    if(!empty($user) && md5($this->input['password'].$user->salt) == strtolower($user->password)){
      if(!$user->activated){
        return response()->json(['status' => '0', "message" => "Our admins will review your account and activate it soon."]);  
      }
      $user_mongo = UserMongo::where('user_id', '=', $user->id)->first();
      // $user_internal is to be used to inside the application. if we access the
      // client/client_type properties on $user, these properties get populated and are sent 
      // to the client in token claims. to limit what we send to the client we're using
      // $user_internal. from which we can cherry pick all the necessary data required.
      $user_internal = User::where('id', '=', $user->id)->first();
      
      if(isset($user_internal->client) && !empty($user_internal->client)){
        $user_type = $user_internal->client->client_type->type;
        $client_slug = $user_internal->client->company_slug;
      }
      else{
        $user_type = "basic";
        $client_slug = "";
      }
      $user_mongo->user_type = $user_type;
      $user_mongo->client_slug = $client_slug;
      // $permissions = [];
      // foreach($user->roles as $role){
      //   array_push($permissions, $role->permissions);
      // }
      $token = JWTAuth::customClaims([
        'user' => $user, 
        'userMongo' => $user_mongo,
      ])->fromUser($user);
      return response()->json(compact('token'));
    }
    else{
      return response()->json(['status' => '0', "message" => "Email or password is wrong."]);
    }
  }

  public function logout()
  {
    $token = JWTAuth::getToken();
    if(JWTAuth::invalidate($token)){
      return response()->json(['status' => '1', 'message' => "Successfullly logged out."]);
    }
    else{
      return response()->json(['status' => '0', 'message' => "An error occured whlie logging you out."]);
    }
  }

  public function getAuthUser(){
    $user = JWTAuth::toUser($this->request->token);
    return response()->json(['result' => $user]);
  }

  // public function getAllUsers(){
  //   $page_no = $this->request->input('page_no');
  //   $page_size = $this->request->input('page_size');
  //   $usersToReturn = [];
	// 	if(isset($page_no, $page_size) && !empty($page_no) && !empty($page_size)){
	// 		$offset = ($page_no - 1) * $page_size;
	// 		$users = User::whereDoesntHave('roles', function($query) {
  //       $query->whereIn('name', ['owner', 'agency']);
  //     })->skip($offset)->take((int)$page_size)->get();
  //     $page_count = ceil(count($users) / $page_size);
  //   }
  //   else{
  //     $users = User::whereDoesntHave('roles', function($query) {
  //       $query->whereIn('name', ['owner', 'agency']);
  //     })->get();
  //     $page_count = 1;
  //   }
  //   foreach($users as $user){
  //     $user_mongo = UserMongo::where('user_id', $user->id)->first();
  //     if(isset($user_mongo) && !empty($user_mongo)){
  //       array_push($usersToReturn, array_merge($user->toArray(), $user_mongo->toArray()));
  //     }
  //     else{
  //       return response()->json(['status' => 0, 'message' => "User data corrupted. Please contact the web master."]);
  //     }
  //   }
  //   $return_data = [
  //     "users" => $usersToReturn,
  //     "page_count" => $page_count
  //   ];
  //   return response()->json($return_data);
  // }

  public function verifyEmail($verification_code){
    $user = UserMongo::where('verification_code', '=', $verification_code)->first();
    if(isset($user) && $user->verification_code_expiry > new \MongoDB\BSON\UTCDateTime()){
      $user->verified = true;
      if($user->save()){
        return response()->json(['status' => 1, 'message' => "Your email has been successfully verified."]);
      }
      else{
        return response()->json(['status' => 0, 'message' => "Your email could not be verified. Please try again."]);  
      }
    }
    else{
      return response()->json(['status' => 0, 'message' => "Email mismatch or verification code expired. Please re-register."]);
    }
  }

  public function getUserProfile(){
    $user = JWTAuth::parseToken()->getPayload()['user'];
    $user_mongo_session = JWTAuth::parseToken()->getPayload()['userMongo'];

    $user_mongo = UserMongo::where('id', '=', $user_mongo_session['id'])->first();
    $user_details = [
      'id' => $user['id'],
	  'id' => 'AMP'.$user['id'],
      'mongo_id' => $user_mongo['id'],
      'email' => $user['email'],
      'name' => $user_mongo['first_name'] . " " . $user_mongo['last_name'],
      //'name' => $user_mongo['name'],
      'first_name' => $user_mongo['first_name'],
      'last_name' => $user_mongo['last_name'],
      'phone' => $user_mongo['phone'],
      'company_name' => $user_mongo['company_name'],
      'address' => $user_mongo['address'],
      'street' => $user_mongo['street'],
      'city' => $user_mongo['city'],
      'zipcode' => $user_mongo['zipcode'],
      'website' => $user_mongo['website'],
      'profile_pic' => $user_mongo['profile_pic']
    ];
	 if ($user_mongo_session['user_type'] == 'basic') {
		  $campaigns_by_user = Campaign::where('created_by', '=', $user_mongo_session['id'])->get();
        } 
		  if ($user_mongo_session['user_type'] == 'bbi') {
			  $campaigns_by_user = Campaign::get();
        } 
		 if ($user_mongo_session['user_type'] == 'owner') {
			 //$campaigns_by_user = Campaign::where('created_by', '=', $user_mongo_session['id'])->get();
			 
        $campaign_product_ids = ProductBooking::where('product_owner', '=', $user_mongo_session['client_mongo_id'])->pluck('campaign_id');
        $campaigns_by_user = Campaign::whereIn('id', $campaign_product_ids)
                ->where('type', '<>', Campaign::$CAMPAIGN_USER_TYPE['owner'])
                ->orderBy('updated_at', 'desc')
                ->get();
        } 
    
    $running_campaigns_count = $campaigns_by_user->where('status', '=', Campaign::$CAMPAIGN_STATUS['running'])->count();
    $closed_campaigns_count = $campaigns_by_user->where('status', '=', Campaign::$CAMPAIGN_STATUS['stopped'])->count();
    $scheduled_campaigns_count = $campaigns_by_user->where('status', '=', Campaign::$CAMPAIGN_STATUS['scheduled'])->count();
    $requested_campaigns_count = $campaigns_by_user->where('status', '=', Campaign::$CAMPAIGN_STATUS['booking-requested'])->count();
    // get products in campaigns
    $products_in_campaigns = 0;
    foreach($campaigns_by_user as $campaign){
      $products_in_campaigns += count($campaign->products);
    }
    // get products in campaigns ends
    
    // get products shortlisted by user
    $shortlisted_products = ShortListedProduct::where('user_mongo_id', '=', $user_mongo['id'])->count();
    // get products shortlisted by user ends
	$product_count ='';
	  if ($user_mongo_session['user_type'] == 'owner') {
            $product_count = Product::where('client_mongo_id', '=', $user_mongo['client_mongo_id'])->count();
        } 
		
		
    return response()->json([
      'user' => $user_details,
      'running_campaigns_count' => $running_campaigns_count,
      'closed_campaigns_count' => $closed_campaigns_count,
      'scheduled_campaigns_count' => $scheduled_campaigns_count,
      'requested_campaigns_count' => $requested_campaigns_count,
      'product_count' => $product_count,
	  'usertype'=>$user_mongo_session['user_type']
    ]);
  }

  public function sendResetPasswordLink(){
    $this->validate($this->request, 
      [
        'email' => 'required'
      ],
      [
        'email.required' => 'Email is required'
      ]
    );
    $user_email = $this->input['email'];
    $user = User::where('email', '=', $user_email)->first();
    if(isset($user, $user->id)){
		//dd('dasfffff<pre>'.$user);die();
      $user_mongo = UserMongo::where('user_id', '=', $user->id)->first();
	    //$user_mongo->status = UserMongo::$REQUEST_STATUS['requested-count'];
      $old_verification_code = $user_mongo->verification_code;
      //$old_verification_code_expiry = $user_mongo->verification_code_expiry;
	  $user_mongo->status = UserMongo::$REQUEST__PASSWORD_STATUS['password-link-status'];
      
      

      if(isset($user_mongo->verification_code_expiry)){
        $old_verification_code_expiry = $user_mongo->verification_code_expiry;
        $expiry_time = array_values(get_object_vars($old_verification_code_expiry));

        $current_time_obj = new \MongoDB\BSON\UTCDateTime();
        $current_time = array_values(get_object_vars($current_time_obj));

        $timediff = ($current_time[0] - $expiry_time[0]);

        if(isset($user_mongo->password_reset_request_count)){
          $password_reset_request_count = $user_mongo->password_reset_request_count;
        }else{
          $password_reset_request_count=1;
        }

        if($timediff <= 43200000){
          if($password_reset_request_count >= 3){
            return response()->json(['status' => 0, 'message' => 'Your password request limit per day has been exceeded. Please contact administrator to reset your password.']);
          }else{  
            if(isset($user_mongo->password_reset_request_count)){
              $old_password_reset_request_count = $user_mongo->password_reset_request_count + 1;
            }else{
              $old_password_reset_request_count = 1;
            }            
            $old_verification_code_expiry = new \MongoDB\BSON\UTCDateTime(strtotime("+30 minutes") * 1000); //strtotime returns seconds, we need milliseconds         
          }
        }else{
          $old_verification_code_expiry = new \MongoDB\BSON\UTCDateTime(strtotime("+30 minutes") * 1000); //strtotime returns seconds, we need milliseconds
          $old_password_reset_request_count = 1;
          //return response()->json(['status' => 0, 'message' => 'Your Link has been expired. Please Reset your password again.']);
        }
      }else{
        $old_verification_code_expiry = new \MongoDB\BSON\UTCDateTime(strtotime("+30 minutes") * 1000); //strtotime returns seconds, we need milliseconds
        $old_password_reset_request_count = 1;
      }

      $user_mongo->password_reset_request_count = $old_password_reset_request_count;
      //$user_mongo->password_reset_request_count = UserMongo::$REQUEST_STATUS['requested-count'];
      $user_mongo->verification_code = base64_encode(md5($user_email . uniqid()));
      //$user_mongo->verification_code_expiry = new \MongoDB\BSON\UTCDateTime(strtotime("+30 minutes") * 1000); //strtotime returns seconds, we need milliseconds
      $user_mongo->verification_code_expiry = $old_verification_code_expiry;

	  //dd('dasfffff<pre>'.$user_mongo);die();
      if($user_mongo->save()){
        $user_email = $user_email;
		//dd('verification_code<pre>'.$user_mongo->verification_code);
		//dd('verification_code_expiry<pre>'.$user_mongo->verification_code_expiry);die();
        /*Mail::send('mail.reset-password', ['verification_code' => $user_mongo->verification_code, 'name' => $user_mongo->first_name], function($message) use ($user_email){
          $message->to($user_email)->subject('Password reset request from Billboards India!');
        });*/
		try{
	/*Mail::send('mail.reset-password', ['verification_code' => $user_mongo->verification_code, 'name' => $user_mongo->first_name], function($message) use ($user_email){
          $message->to($user_email)->subject('Password reset request from Billboards India!');
        });*/
	        //Mail::send('mail.reset-password', ['verification_code' => $user_mongo->verification_code, 'name' => $user_mongo->first_name], function($message) use ($user_email){
	        Mail::send('mail.reset-password', ['verification_code' => $user_mongo->verification_code, 'name' => $user_mongo->first_name . " " . $user_mongo->last_name], function($message) use ($user_email){
          $message->to($user_email)->subject('Password reset request from Advertising Marketplace!');
        });
			}catch (\Exception $e) {
				return $e->getMessage();
			}
		
        if(!Mail::failures()){
          return response()->json(['status' => 1, 'message' => "A password reset link has been sent to your email successfully."]);
        }
        else{
			//dd('dasfffff<pre>');die();
          return response()->json(['status' => 0, 'message' => "There was an error sending the password reset link to your email. Please contact admin."]);    
        }
      }
      else{
		  dd('erfdsfsdf<pre>');die();
        return response()->json(['status' => 0, 'message' => "A technical error occured. Please try again later or contact the admin."]);  
      }
    }
    else{
      return response()->json(['status' => 0, 'message' => "Email not found in our databases."]);
    }
  }

  public function resetPassword(Request $request){
	  
	  if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
	  
    $this->validate($this->request, 
      [
        'code' => 'required',
        'newPassword' => 'required|min:6',
        'confirmNewPassword' => 'required|min:6'
      ],
      [
        'code.required' => 'Verification code is required',
        'newPassword.required' => 'New Password is required',
        'newPassword.min' => 'New Password should at least be 6 characters long',
        'confirmNewPassword' => 'Confirm password field is required',
        'confirmNewPassword.min' => 'Confirm password should be same as new password'
      ]
    );
    $user_mongo = UserMongo::where('verification_code', '=', $this->input['code'])->first();
    $user_mongo1 = UserMongo::where('verification_code', '=', $this->input['code'])->where('status', '=', 20)->first();
	//echo "<pre>";print_r($user_mongo1);exit();
    if(isset($user_mongo) && $user_mongo->verification_code_expiry > new \MongoDB\BSON\UTCDateTime()){
      if($this->input['newPassword'] == $this->input['confirmNewPassword']){
        $user = User::where('id', '=', $user_mongo->user_id)->first();
        $user->password = md5($this->input['newPassword'] . $user->salt); 
		$user_mongo->status = UserMongo::$REQUEST__PASSWORD_STATUS['password-link-success']; 
		//$user_mongo->save(); 
    // echo $user_mongo->status;echo "<br>";echo $user_mongo->verification_code;echo "<br>";
    // echo "<pre>";print_r($this->input);echo "</pre>";exit();
		//if($user_mongo->status == 20 && $user_mongo->verification_code == $this->input['code'])
		if(!empty($user_mongo1))
            {
				return response()->json(['status' => 0, 'message' => "You have already reset password for this link, Try again..."]); 
			}
        if($user->save()){
			$user_mongo->status = UserMongo::$REQUEST__PASSWORD_STATUS['password-link-success']; 
			$user_mongo->save(); 
          return response()->json(['status' => 1, 'message' => "Your password has been updated successfully."]);
        }
        else{
          return response()->json(['status' => 0, 'message' => "There was a technical problem while updating your password. Please contact an administrator."]);
        }
	  
      }
      else{
        return response()->json(['status' => 0, 'message' => "The 2 passwords entered do not match."]); 
      }
    }
    else{
      return response()->json(['status' => 0, 'message' => "The password reset link has expired. Please try again."]);
    }
  }

  public function changePassword(){
    $this->validate($this->request, 
      [
        'password' => 'required',
        'newPassword' => 'required|min:6',
        'confirmNewPassword' => 'required|min:6'
      ],
      [
        'password.required' => 'Old password is required',
        'newPassword.required' => 'New Password is required',
        'newPassword.min' => 'New Password should at least be 6 characters long',
        'confirmNewPassword.required' => 'Confirm password field is required',
        'confirmNewPassword.min' => 'Confirm password should be same as new password'
      ]
    );
    $user_id = JWTAuth::parseToken()->getPayload()['user']['id'];
    if(isset($user_id)){
      $user = User::where('id', '=', $user_id)->first();
      if(isset($user) && md5($this->input['password'] . $user->salt) == $user->password){
        if($this->input['newPassword'] == $this->input['confirmNewPassword']){
          $user->password = md5($this->input['newPassword'] . $user->salt);
          if($user->save()){
            return response()->json(['status' => 1, 'message' => "Your password has been updated successfully."]);
          }
          else{
            return response()->json(['status' => 0, 'message' => "There was a technical problem while updating your password. Please contact an administrator."]);
          }
        }
        else{
          return response()->json(['status' => 0, 'message' => "The 2 passwords entered do not match."]); 
        }
      }
      else{
        return response()->json(['status' => 0, 'message' => "Current password given is wrong. Please try again."]);  
      }
    }
    else{
      return response()->json(['status' => 0, 'message' => "User details do not match. Please contact admin."]);
    }
  }
  
  public function generateRandomPassword(){
    $this->validate($this->request, 
      [
        'password' => 'required'
      ],
      [
        'password.required' => 'Password is required'
      ]
    );
    $user_id = JWTAuth::parseToken()->getPayload()['user']['id'];
    if(isset($user_id)){
		$password =$this->input['password'];
		$user_salt = str_random(7);
		$user_password = md5( $password . $user_salt);
		return response()->json(['status' => 1, 'message' => "Your Password is -  " . $user_password.", Salt is - ". $user_salt]);
	}
    else{
      return response()->json(['status' => 0, 'message' => "Error in generating Password, Please contact admin."]);
    }
  }

  public function toggleActivationForUser($user_mongo_id){
    $user_mongo = UserMongo::where('id', '=', $user_mongo_id)->first();
    if(!isset($user_mongo) && empty($user_mongo)){
      return response()->json(['status' => 0, 'message' => "user not found"]);
    }
    $user = User::where('id', '=', $user_mongo->user_id)->first();
    $user->activated = !$user->activated;
    if($user->save()){
      $user_state = $user->activated ? "activated" : "deactivated";
      return response()->json(['status' => 1, 'message' => "User " . $user_state . " successfully."]);
    }
    else{
      return response()->json(['status' => 0, 'message' => "An error occured while activating the user. Please try again."]);
    }
  }

  public function getSystemRoles(){
    $roles = Role::where('name', '<>', 'super_admin')->get();
    return response()->json($roles);
  }

  public function getSystemPermissions(){
    $super_admin_permissions = Role::where('name', '=', 'super_admin')->first()
      ->permissions()->orderBy('name')->get();
    return response()->json($super_admin_permissions);
  }

  public function getAllUsers(){
    $client = Client::where('company_name', '=', 'BBI')->first();
    $user_ids = User::where('id', '<>', $client->super_admin)->orderBy('created_at','desc')->pluck('id');
	
    $all_client_types = ClientType::select('id', 'type')->get();
    $all_client_types_arr = [];
    foreach($all_client_types as $client_type){
      $all_client_types_arr[$client_type->id] = $client_type->type;
    }
    $user_data_arr = [];
    foreach($user_ids as $user_id){
      $user = User::where('id', '=', $user_id)->first();
      $user_profile = UserMongo::where('user_id', '=', $user_id)->first();
      if(!empty($user_profile) && empty($user_profile->company_type)){
        $user_profile->company_type = "User";
      }
	if(!empty($user) && !empty($user_profile)){
      $user_data = array_merge($user->toArray(), $user_profile->toArray());
	 
      array_push($user_data_arr, $user_data);
	 }
    }
    return response()->json($user_data_arr);
  }

  public function getUserDetailsWithRoles($user_mongo_id){
    $user_mongo = UserMongo::where('id', '=', $user_mongo_id)->first();
    if(!isset($user_mongo) || empty($user_mongo)){
      return response()->json(['status' => 0, 'message' => "User not found in database."]);
    }
    $user = User::where('id', '=', $user_mongo->user_id)->first();
    $user_details = [
      'id' => $user['id'],
      'id' => 'AMP'.$user['id'],
      'mongo_id' => $user_mongo['id'],
      'email' => $user['email'],
      'name' => $user_mongo['first_name'] . " " . $user_mongo['last_name'],
      'phone' => $user_mongo['phone'],
      'profile_pic' => $user_mongo['profile_pic'],
      'activated' => $user->activated
    ];
    $user_roles = $user->roles;
    $user_data = [
      'user_details' => $user_details,
      'user_roles' => $user_roles
    ];
    return response()->json($user_data);
  }

  public function getRoleDetails($role_id){
    $role = Role::where('id', '=', $role_id)->first();
    $users = $role->users;
    $permissions = $role->permissions;
    $role_details = [
      "role" => $role,
      "users" => $users,
      "permissions" => $permissions
    ];
    return response()->json($role_details);
  }

  public function addRole(){
    $this->validate($this->request, 
      [
        'display_name' => [
          'required',
          'unique:roles', 
          'regex:/^[A-Za-z]+[\sA-Za-z0-9]*$/'
        ],
        'description' => 'required'
      ],
      [
        'display_name.required' => 'Display name is required',
        'display_name.unique' => 'The role name has to be unique',
        'display_name.regex' => 'Display name can only contain alphanumeric and space',
        'description.required' => 'Description is required'
      ]
    );
    $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
    $user = User::where('id', '=', $user_mongo['user_id'])->first();
    $role = new Role();
    $role->client_id = $user->client_id;
    $role->display_name = $this->input['display_name'];
    $role->name = strtolower(str_replace(" ", "_", $role->display_name));
    $role->description = $this->input['description'];
    if($role->save()){
      return response()->json(['status' => 1, 'message' => 'Role has been added successfully.']);
    }
    else{
      array_push($errors, "There was a trouble adding role. Please try again or contact webmaster");
      return response()->json(['status' => 0, 'message' => $errors]);
    }
  }

  public function setSuperAdminForClient(){
    $this->validate($this->request,
      [
        'client_id' => 'required',
        'super_admin_email' => 'required|email'
      ],
      [
        'client_id.required' => "Client id is not provided.",
        "super_admin_email.required" => "email id is required",
        "super_admin_email.email" => "email provided is not valid"
      ]
    );
    // the client shouldn't have a super admin already.
    $client_mongo = ClientMongo::where('id', '=', $this->input['client_id'])->first();
    if(!isset($client_mongo) || empty($client_mongo)){
      return response()->json(['status' => 0, 'message' => 'Client not found. Please check the client id again.']);
    }
    else{
      $client = Client::where('id', '=', $client_mongo->client_id)->first();
      if(isset($client->super_admin) && !empty($client->super_admin)){
        return response()->json(['status' => 0, 'message' => 'Client already have a super admin set up. One client can only have one super admin.']);
      }
      else{
        // if user exists
        $user = User::where('email', '=', $this->input['super_admin_email'])->first();
        if(isset($user) && !empty($user)){
          $user_already_su_to = Client::where('super_admin', '=', $user->id)->get();
          if(isset($user_already_su_to) && count($user_already_su_to) > 0){
            return response()->json(['status' => 0, 'message' => 'The email is already registered as a super admin for another company.']);
          }
          else{
            // assign this user to client
            $client->super_admin = $user->id;
            if($client->save()){
              $user_mongo_existing = UserMongo::where('user_id', '=', $user->id)->first();
              $client_mongo->super_admin_m_id = $user_mongo_existing->id;
              $client_mongo->save();
              return response()->json(['status' => 1, 'message' => 'Super admin assign successfully.']);
            }
            else{
              return response()->json(['status' => 0, 'message' => 'There was an error setting up the super admin for this client. Please try again later']);
            }
          }
        }
        else{
          // $user is not set. means he's not on the system. 
          // create a user and send the email to generate password.
          $new_user = new User();
          $new_user->client_id = $client->id;
          $new_user->email = $this->input['super_admin_email'];
          $new_user->salt = str_random(7);
          $new_user->activated = false;
          if($new_user->save()){
            // assign basic user role
            $role = Role::where('name', '=', 'basic_user')->first();
            $new_user->roles()->attach($role);
            // create entry in mongo
            $user_mongo = new UserMongo;
            $user_mongo->id = uniqid();
            $user_mongo->email = $this->input['super_admin_email'];
            $user_mongo->user_id = $new_user->id;
            $user_mongo->client_id = $client_mongo->client_id;
            $user_mongo->client_mongo_id = $client_mongo->id;
            $user_mongo->company_name = $client_mongo->company_name;
            $user_mongo->company_type = $new_user->client->client_type->type;
            $user_mongo->verification_code = base64_encode(md5($this->input['super_admin_email'] . uniqid()));
            $user_mongo->verification_code_expiry = new \MongoDB\BSON\UTCDateTime(strtotime("+3 days") * 1000); //strtotime returns seconds, we need milliseconds
            $user_mongo->verification_type = UserMongo::$VERIFICATION_TYPES['user-invitation'];
            if($user_mongo->save()){
              $user_email = $this->input['super_admin_email'];
              $bbi_sa_id = Client::where('company_slug', '=', 'bbi')->first()->super_admin;
              $bbi_sa = UserMongo::where('user_id', '=', $bbi_sa_id)->first();
              /*NotificationHelper::createNotification([
                'type' => Notification::$NOTIFICATION_TYPE['account-super-admin-setup'],
                'from_id' => $user_mongo['id'],
                'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
                'to_id' => null,
                'to_client' => null,
                'desc' => "New owner registered",
                'message' => "Please set up the super admin for the client",
                'data' => ["client_m_id" => $client_mongo->id]
              ]);*/
                event(new accountSuperAdminEvent([
                'type' => Notification::$NOTIFICATION_TYPE['account-super-admin-setup'],
                'from_id' => $user_mongo['id'],
                'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
                'to_id' => null,
                'to_client' => null,
                'desc' => "New owner registered",
                'message' => "Please set up the super admin for the client",
                'data' => ["client_m_id" => $client_mongo->id]
              ]));
              $mail_tmpl_params = [
                'sender_email' => $user_mongo['email'], 
                'receiver_name' => $bbi_sa->first_name . ' ' . $bbi_sa->last_name,
                'mail_message' => 'Company registered. Please set up a super admin for company ' . $client_mongo->company_name . '.'
              ];
              $mail_data = [
                'email_to' => $bbi_sa->email,
                'recipient_name' => $bbi_sa->first_name
              ];
              Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data){
                //$message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Super admin set up for company - Billboards India');
                $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Super admin set up for company - Advertising Marketplace');
              });
              // send the invitation email
              Mail::send('mail.invite-user', ['verification_code' => $user_mongo->verification_code], function($message) use ($user_email){
                //$message->to($user_email)->subject('Invitation from Billboards India!');
                $message->to($user_email)->subject('Invitation from Advertising Marketplace!');
              });
              if(!Mail::failures()){
                $client_mongo->super_admin_m_id = $user_mongo->id;
                $client_mongo->save();
                $client->super_admin = $new_user->id;
                if($client->save()){
                  return response()->json(['status' => 1, 'message' => "Super admin set for the company."]);
                }
                else{
                  return response()->json(['status' => 0, 'message' => "Error occured while assigning user."]);
                }
              }
              else{
                return response()->json(['status' => 0, 'message' => "Set up successful. But failed to send the invitation email."]);    
              }
            }
            else{
              return response()->json(['status' => 0, 'message' => "Error creating user profile. Please contact the webmaster."]);
            }
          }
          else{
            return response()->json(['status' => 0, 'message' => 'There was an error creating the user.']);
          }
        }
      }
    }
  }

  public function completeRegistration(){
    $this->validate($this->request, 
      [
        'code' => 'required',
        'name' => 'required',
        'password' => 'required|min:6',
        'confirm_password' => 'required'
      ],
      [
        'code.required' => 'Invalid request. You are not authorized to update the details from here.',
        'email.required' => 'Email is required',
        'email.unique' => 'The email entered is already used with an account',
        'email.email' => 'Invalid email',
        'name.required' => 'Name is required',
        'password.required' => 'Password is required',
        'password.min' => 'Password length should be at least 6 characters',
        'confirm_password' => 'Confirm Password field is required'
      ]
    );
    $user_mongo = UserMongo::where('verification_code', '=', $this->input['code'])->first();
    if(isset($user_mongo)){
      if($user_mongo->verification_code_expiry > new \MongoDB\BSON\UTCDateTime()){
        if($this->input['password'] != $this->input['confirm_password']){
          return response()->json(['status' => 0, 'message' => 'Password and confirm password fields do not match.']);
        }
        else{
          $user = User::where('id', '=', $user_mongo->user_id)->first();
          $user->password = md5($this->input['password'] . $user->salt);;
          if($user->save()){
            // set up mongo profile
            $full_name = explode(" ", $this->input['name']);
            $user_mongo->first_name = isset($full_name, $full_name[0]) ? $full_name[0] : "";
            for($i = 1; $i < count($full_name) - 1; $i++){
              $user_mongo->middle_name .= $full_name[$i] . " ";
            }
            $user_mongo->last_name = isset($full_name) && (count($full_name) > 1) ? $full_name[ count($full_name) - 1 ] : "";
            $user_mongo->phone = isset($this->input['phone']) ? $this->input['phone'] : "";
            $user_mongo->address = isset($this->input['address']) ? $this->input['address'] : "";
            if($user_mongo->save()){
              return response()->json(['status' => 1, 'message' => "Your account registration is done successfully."]);
            }
          }
          else{
            //return response()->json(['status' => 0, 'message' => 'There was an error setting up the password. Please contact a Billboards India admin.']);
            return response()->json(['status' => 0, 'message' => 'There was an error setting up the password. Please contact a Advertising Marketplace admin.']);
          }
        }
      }
      else{
        //return response()->json(['status' => 0, 'message' => 'Verification link expired. Please contact a Billboards India admin to receive a new link.']);
        return response()->json(['status' => 0, 'message' => 'Verification link expired. Please contact a Advertising Marketplace admin to receive a new link.']);
      }
    }
    else{
      return response()->json(['status' => 0, 'message' => 'User not found.']);
    }
  }

  public function deleteUser($user_mongo_id){
    $user_id = UserMongo::where('id', '=', $user_mongo_id)->first()->user_id;
    $user_campaigns = Campaign::where('user_mongo_id', '=', $user_mongo_id);
    if($user_campaigns->count() > 0){
      if($user_campaigns::where('status', '>=', Campaign::$CAMPAIGN_STATUS['running'])->count()){
        return response()->json(['status' => 0, 'message' => "This user can not be deleted. As deletion will result in lost of campaign data associated with this user."]);  
      }
      else{
        $user_campaigns->delete();
      }
    }
    $user = User::where('id', '=', $user_id);
    if($user->delete()){
      return response()->json(['status' => 1, 'message' => "User deleted successfully."]);    
    }
    else{
      return response()->json(['status' => 0, 'message' => "An error occured while deleting the user. Please try again."]);
    }
  }

  public function changeUserAvatar(){
    if ($this->request->hasFile('profile_pic')){      
      $user_id = JWTAuth::parseToken()->getPayload()['user']['id'];
      $user_mongo = UserMongo::where('user_id', '=', $user_id)->first();
      if(isset($user_mongo) && !empty($user_mongo)){
        $user_avatar_path = base_path() . '/html/uploads/images/users';
        $user_mongo->profile_pic = "";
        if($this->request->file('profile_pic')->move($user_avatar_path, $this->request->file('profile_pic')->getClientOriginalName())){
          $user_mongo->profile_pic = "/uploads/images/users/" . $this->request->file('profile_pic')->getClientOriginalName();
          if($user_mongo->save()){
            try{
              dispatch(new UpdateUserEverywhere($user_mongo));
              //Log::info("job completed: UpdateUserEverywhere with data" . serialize($user_mongo));
            }
            catch(Exception $ex){
              // log exception
              Log::error("UpdateUserEverywhere task threw exception:" . $ex);
              Queue::push(new UpdateUserEverywhere($user_mongo));
            }
            return response()->json(["status" => "1", "message" => "User avatar saved successfully."]);
          }
          else{
            return response()->json(["status" => "0", "message" => "Failed to update user image."]);			
          }  
        }
        else{
          return response()->json(["status" => "0", "message" => "Could not save the image on server."]);			
        }
      }
      else{
        return response()->json(["status" => "0", "message" => "User not found. Please re-sign in and try again."]);			
      }
    }
    else{
      return response()->json(["status" => "0", "message" => "Image not found."]);			
    }
  }

  public function setPermissionsForRole(){
    $this->validate($this->request,
      [
        'role_id' => 'required'
      ],
      [
        'role_id.required' => "Role id is not provided.",
      ]
    );
    $role = Role::where('id', '=', $this->input['role_id'])->first();
    if(!isset($role) || empty($role)){
      return response()->json(['status' => 0, 'message' => "Role not found in database."]);
    }
    else{
      $role->permissions()->detach();
      if(isset($this->input['permissions']) && !empty($this->input['permissions'] && is_array($this->input['permissions']))){
        $role->permissions()->attach($this->input['permissions']);
        return response()->json(['status' => 1, 'message' => "The permissions have been updated for the role."]);
      }
      else{
        return response()->json(['status' => 1, 'message' => 'All permissions removed from role.']);
      }
    }
  }

  public function setRolesForUser(){
    $this->validate($this->request,
      [
        'user_id' => 'required'
      ],
      [
        'user_id.required' => "User id is not provided.",
      ]
    );
    $user = User::where('id', '=', $this->input['user_id'])->first();
    if(!isset($user) || empty($user)){
      return response()->json(['status' => 0, 'message' => "User not found in database."]);
    }
    else{
      $user->roles()->detach();
      if(isset($this->input['roles']) && !empty($this->input['roles'] && is_array($this->input['roles']))){
        $user->roles()->attach($this->input['roles']);
        return response()->json(['status' => 1, 'message' => "The roles have been updated for the user."]);
      }
      else{
        return response()->json(['status' => 1, 'message' => 'All roles removed from user.']);
      }
    }
  }

  public function sendInviteToBBIUser(){
    $this->validate($this->request,
      [
        'email' => 'required|email'
      ],
      [
        'email.required' => "Email is required",
        'email.email' => "Not a valid email"
      ]
    );
    $already_exists = User::where('email', '=', $this->input['email'])->first();
    if(isset($already_exists) && !empty($already_exists)){
      // user already exists.
      $user_mongo = UserMongo::where('user_id', '=', $already_exists->id)->first();
      $user_mongo->verification_code = base64_encode(md5($this->input['email'] . uniqid()));
      $user_mongo->verification_code_expiry = new \MongoDB\BSON\UTCDateTime(strtotime("+3 days") * 1000); //strtotime returns seconds, we need milliseconds
      $user_mongo->verification_type = UserMongo::$VERIFICATION_TYPES['user-invitation'];
      if($user_mongo->save()){
        $user_email = $already_exists->email;
        Mail::send('mail.invite-user', ['verification_code' => $user_mongo->verification_code], function($message) use ($user_email){
          //$message->to($user_email)->subject('Invitation from Billboards India!');
          $message->to($user_email)->subject('Invitation from Advertising Marketplace!');
        });
        if(!Mail::failures()){
          return response()->json(['status' => 1, 'message' => "AMP user invitation sent."]); 
        }
        else{
          return response()->json(['status' => 0, 'message' => "Failed to send the invitation email."]);    
        }
      }
      else{
        return response()->json(['status' => 0, 'message' => "Error updating verification token. Please contact the admin."]);
      }
    }
    else{
      $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
      // the client shouldn't have a super admin already.
      $client_mongo = ClientMongo::where('id', '=', $user_mongo['client_mongo_id'])->first();
      // check if logged in user is super admin for bbi.
      if(!isset($client_mongo->super_admin_m_id) || $client_mongo->super_admin_m_id != $user_mongo['id']){
        return response()->json(['status' => 0, 'message' => 'You\'re not authorized to send this invite. Please contact the admin']);
      }
      else{
        $client = Client::where('id', '=', $client_mongo->client_id)->first();
        // if user exists
        $user = User::where('email', '=', $this->input['email'])->first();
        if(isset($user) && !empty($user)){
          // can't connect an already existing user to a client. would mess up his own campaigns etc.
          return response()->json([['status' => 0, 'message' => 'The email given is already in the database. Unable to send invite.']]);
        }
        else{
          // $user is not set. means he's not on the system. 
          // create a user and send the email to generate password.
          $new_user = new User();
          $new_user->client_id = $client->id;
          $new_user->email = $this->input['email'];
          $new_user->salt = str_random(7);
          $new_user->activated = false;
          if($new_user->save()){
            // assign basic user role
            $role = Role::where('name', '=', 'basic_user')->first();
            $new_user->roles()->attach($role);
            // create entry in mongo
            $user_mongo = new UserMongo;
            $user_mongo->id = uniqid();
            $user_mongo->email = $this->input['email'];
            $user_mongo->user_id = $new_user->id;
            $user_mongo->client_id = $client_mongo->client_id;
            $user_mongo->client_mongo_id = $client_mongo->id;
            $user_mongo->company_name = $client_mongo->company_name;
            $user_mongo->company_type = $new_user->client->client_type->type;
            $user_mongo->verification_code = base64_encode(md5($this->input['email'] . uniqid()));
            $user_mongo->verification_code_expiry = new \MongoDB\BSON\UTCDateTime(strtotime("+3 days") * 1000); //strtotime returns seconds, we need milliseconds
            $user_mongo->verification_type = UserMongo::$VERIFICATION_TYPES['user-invitation'];
            if($user_mongo->save()){
              $user_email = $this->input['email'];
              // send the invitation email
              Mail::send('mail.invite-user', ['verification_code' => $user_mongo->verification_code], function($message) use ($user_email){
                //$message->to($user_email)->subject('Invitation from Billboards India!');
                $message->to($user_email)->subject('Invitation from Advertising Marketplace!');
              });
              if(!Mail::failures()){
                return response()->json(['status' => 1, 'message' => "AMP user invitation sent."]);
              }
              else{
                return response()->json(['status' => 0, 'message' => "Failed to send the invitation email."]);    
              }
            }
            else{
              return response()->json(['status' => 0, 'message' => "Error creating user profile. Please contact the admin."]);
            }
          }
          else{
            return response()->json(['status' => 0, 'message' => 'There was an error creating the user.']);
          }
        }
      }
    }
  }
  
  
  public function getUserCount(){
	  //$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
      // the client shouldn't have a super admin already.
      //$client_mongo = ClientMongo::where('id', '=', $user_mongo['client_mongo_id'])->first(); 
	  $clients_count = ClientMongo::count();
	  $users_count = UserMongo::count();
	  //echo '<pre>'; print_r($users_count); exit;
	  $usersCount = [
            'owners' => $clients_count,
            'buyers' => $users_count,
            'total' => $clients_count + $users_count
        ];
		
	  return response()->json($usersCount);
  }
  
  public function getResetPasswordLink($verification_code){
      /*
        1 Min  = 60000 Milliseconds
        30 Min = 30 X 60000 = 1800000 Milliseconds
        12 Hours = 720 Min = 720 X 60000 = 43200000 Milliseconds    
      */
      $current_time_obj = new \MongoDB\BSON\UTCDateTime();
      $current_time = array_values(get_object_vars($current_time_obj));

      $check_verification_code = UserMongo::where('verification_code', '=', $verification_code)->first();
      $expiry_time_obj = $check_verification_code->verification_code_expiry;
      $password_reset_request_count = $check_verification_code->password_reset_request_count;
      $expiry_time = array_values(get_object_vars($expiry_time_obj));
      
      $timediff = ($current_time[0] - $expiry_time[0]);
      
      if($timediff <= 43200000){
        if($password_reset_request_count >= 3){
          return response()->json(['status' => 0, 'message' => 'Your password request limit per day has been exceeded. Please contact administrator to reset your password.']);
        }else{
            if($timediff >= 1800000){
              return response()->json(['status' => 0, 'message' => 'Your Link has been expired. Please Reset your password again.']);
            }else{
              return response()->json(['status' => 1, 'message' => 'Please show Reset Password form.']);
            }

        }
      }else{
        return response()->json(['status' => 0, 'message' => 'Your Link has been expired. Please Reset your password again.']);
      }
  }
  
  public function updateUserProfile(Request $request){
        $response_company = NimbleHelper::getContactList();
		//echo '<pre>';print_r($response_company);exit;
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
          
        if (isset($input['mongo_id'])) {
            //echo '<pre>in if'; print_r($input); exit;
            $profile_edit = UserMongo::where('id', '=', $input['mongo_id'])->first();
			//$profile_edit_id = $profile_edit['nimble_user_id'];
			//echo '<pre>';print_r($profile_edit_id);exit;
            $profile_edit->first_name = isset($input['first_name']) ? $input['first_name'] : $profile_edit->first_name;
            $profile_edit->name = isset($input['name']) ? $input['name'] : $profile_edit->name;
            $profile_edit->last_name = isset($input['last_name']) ? $input['last_name'] : $profile_edit->last_name;
            $profile_edit->company_name = isset($input['company_name']) ? $input['company_name'] : $profile_edit->company_name;
            $profile_edit->address = isset($input['address']) ? $input['address'] : $profile_edit->address;
            $profile_edit->street = isset($input['street']) ? $input['street'] : $profile_edit->street;
            $profile_edit->city = isset($input['city']) ? $input['city'] : $profile_edit->city;
            $profile_edit->zipcode = isset($input['zipcode']) ? $input['zipcode'] : $profile_edit->zipcode;
            $profile_edit->website = isset($input['website']) ? $input['website'] : $profile_edit->website;
            if ($profile_edit->save()) {
			
			//========================Nimble Update User Details Start============================//	
			$search_company = array('and' => array(array('company name' => array('is' => $profile_edit->company_name)),array('record type' => array('is' => 'company'))));
			$search_company_data = NimbleHelper::searchRecords([
				'search_company' => $search_company 
			]);
			if($search_company_data['meta']['total'] == 0){
				$company_data = json_encode(array('fields' => array('company name' => array(array('value' => $profile_edit->company_name,'modifier' => ''))),'record_type' => 'company','tags' => ''));
				$response_company = NimbleHelper::addCompany([
					'httpMethod' => 'POST',
					'company_data' => $company_data
				  ]);
				  if($response_company != 0){
						$company_id_contact = $response_company['id']; 
				  }
			}else{
				$company_id_contact = $search_company_data['resources'][0]['id'];
			}
			if($company_id_contact > 0){
			$data_string = json_encode(array('fields' => array('first name' => array(array('value' => $profile_edit->first_name,'modifier' => '')),
												'last name' => array(array('value' => $profile_edit->last_name,'modifier' => '')),
												'address' => array(array('value' => stripslashes(trim('"{\"street\":\"'.$profile_edit->street.'\", \"city\":\"'.$profile_edit->city.'\", \"zip\":\"'.$profile_edit->zipcode.'\"}"','"\\')),'modifier'=>'work')),
												'parent company' => array(array('modifier' => '', 'value' => $profile_edit->company_name, 'extra_value'=> $company_id_contact)))));
			$profile_edit_id = $profile_edit['nimble_user_id'];  
				$nimble_data = NimbleHelper::updateUserDetails([
					'httpMethod' => 'PUT',
					'data_string' => $data_string,
					'user_id' => $profile_edit_id
				]);
			}
			//========================Nimble Update User Details End=============================//
			
              // Update data to elasticsearch :: Pankaj 12 Oct 2021
              $get_data = UserMongo::where('id', '=', $profile_edit->id)->first();
              $this->es_etl_users($get_data, "update");   

			//NetSuite Update On 05-Dec-2022
			$data_string = array('externalId' => $profile_edit->id,
								'companyName' => isset($input['company_name']) ? $input['company_name'] : $profile_edit->companyName,
								'firstName' => isset($input['first_name']) ? $input['first_name'] : $profile_edit->firstName,
								'lastName' => isset($input['last_name']) ? $input['last_name'] : $profile_edit->lastName,
								'Address1_AddressName' => isset($input['address']) ? $input['address'] : $profile_edit->Address1_AddressName,
								'Address1_line1' => isset($input['street']) ? $input['street'] : $profile_edit->Address1_line1,
								'Address1_city' => isset($input['city']) ? $input['city'] : $profile_edit->Address1_city,
								'website' => isset($input['website']) ? $input['website'] : $profile_edit->website,
								);
			//echo '<pre>';print_r($data_string);exit;
			$url =    env('NS_CUSTOMER_URL').'eid:'.$profile_edit->id;
			//$url =    env("NS_CUSTOMER_URL")."/eid:".$profile_edit->id;
			//echo '<pre>';print_r($url);exit;
			NetsuiteHelper::get_crud_netsuite_record([
				'httpMethod' => 'PATCH',
				'url' => $url,
				'data_string' => $data_string
			  ]);
			//NetSuite
			
			
			  
              return response()->json(["status" => "1", "message" => "Updated successfully"]);
            } else {
                return response()->json(["status" => "0", "message" => "Failed to Update"]);
            }
        
        }
    }
  
  public function userSingleRecord($user_id){

	if(isset($user_id) && $user_id != ''){
		$user_mongo = UserMongo::where('id', '=', $user_id)->first();
    		$user = User::where('id', '=', $user_mongo->user_id)->first();
			
    		if(isset($user) && $user->activated == 1){
      			return response()->json(['status' => 1, 'message' => "User record found."]);
    		}
    		else{
      			return response()->json(['status' => 0, 'message' => "User record not found." ]);
    		}
	}
    	else{
      		return response()->json(['status' => 0, 'message' => "user id is null."]);
    	}
  }
  
	public function get_netsuite_customer($httpMethod,$url,$data_string){
		$accountID 				=    env('NS_ACCOUNT_REALM');
		$realm 					=    env('NS_ACCOUNT_REALM');//NOTICE THE UNDERSCORE
		$consumerKey 			=    env('NS_CONSUMER_KEY'); //Consumer Key
		$consumerSecret 		=    env('NS_CONSUMER_SECRET'); //Consumer Secret
		$tokenKey 				=    env('NS_TOKEN_KEY'); //Token ID
		$tokenSecret  			=    env('NS_TOKEN_SECRET'); //Token Secret    
		$timestamp				=    time();
		$nonce					=    uniqid(mt_rand(1, 1000));
		$baseString = $httpMethod . '&' . rawurlencode($url) . "&"
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
			oauth_signature_methods=\"HMAC-SHA256\", 
			oauth_version=\"1.0\", oauth_signature=\"$signature\"",
			"Content-Type: application/jsons"
		);

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url ,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $httpMethod,
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_POSTFIELDS  => json_encode($data_string)
		));

		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		
		if($httpcode == '204'){
			return true;
		}else{
			return false;
		}
	}
	
  //Buyers get API for transfer campaign
  public function searchBuyers($search_term){
    $word = strtolower($search_term);
		$buyers = UserMongo::Where('company_type', "")
			->Where('email', '!=', "richard.advertisingmarketplace@gmail.com")
			->orWhere('first_name', 'like', "%$word%")
			->orWhere('last_name', 'like', "%$word%")
			->orWhere('email', 'like', "%$word%")
			->orWhere('phone', 'like', "%$word%")
			->get();
		return response()->json($buyers);
  }
  
  //update API for transfer campaign from Admin unique Email to any Buyer
  
  public function transferCampaignToBuyer(Request $request){
        
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        if (isset($input['campaign_id'])) {
			$transfer_campaign = Campaign::where('id', '=', $input['campaign_id'])->first();
			if(isset($transfer_campaign)){
				if(isset($input['transfer_status'])){
					if($input['transfer_status'] == 1){
						$user_mongo_buyer_admin = userMongo::where('email', '=', $input['transfer_email'])->first();
						$user_id_int = $user_mongo_buyer_admin['user_id'];
						$created_by_int = $user_mongo_buyer_admin['id'];
						$notf_user_type = Notification::$NOTIFICATION_CLIENT_TYPE['user'];
						$notf_message = 'Saved campaign transfer request from admin - Campaign ID : '.$transfer_campaign->cid;
					}else if($input['transfer_status'] == 2){
						$user_mongo_buyer_admin = Campaign::where('id', '=', $input['campaign_id'])->first();
						$user_id_int = $user_mongo_buyer_admin['user_id'];
						$created_by_int = $user_mongo_buyer_admin['created_by'];
						$notf_user_type = Notification::$NOTIFICATION_CLIENT_TYPE['user'];
						$notf_message = 'Saved campaign transfer request has been approved by buyer - Campaign ID : '.$transfer_campaign->cid;
					}else if($input['transfer_status'] == 3){
						$user_mongo_buyer_admin = userMongo::where('email', '=', 'richard.advertisingmarketplace@gmail.com')->first();
						$user_id_int = $user_mongo_buyer_admin['user_id'];
						$created_by_int = $user_mongo_buyer_admin['id'];
						$notf_user_type = Notification::$NOTIFICATION_CLIENT_TYPE['user'];
						$notf_message = 'Saved campaign transfer request has been rejected by buyer - Campaign ID : '.$transfer_campaign->cid;
					}else{
						return response()->json(["status" => "0", "message" => "Invalid transfer status."]);
					}
				}else{
					return response()->json(["status" => "0", "message" => "Invalid transfer status."]);
				}
				if(isset($user_mongo_buyer_admin) && $user_id_int != '' && $created_by_int != ''){
					$transfer_campaign->created_by = $created_by_int;
					$transfer_campaign->user_id = $user_id_int;
					$transfer_campaign->transfer_status = $input['transfer_status'];
					$transfer_campaign->transfer_status_comments = $input['comments'];
					if ($transfer_campaign->save()) {
						event(new CampaignTransferEvent([
						  'type' => Notification::$NOTIFICATION_TYPE['transfer-campaign'],
						  'from_id' => null,
						  'to_type' => $notf_user_type,
						  'to_id' => $created_by_int,
						  'campaign_id' => $input['campaign_id'],
						  'c_id' => $transfer_campaign->cid,
						  'c_name' => $transfer_campaign->name,
						  'to_client' => $created_by_int,
						  'desc' => $notf_message.' - Comments : '.$input['comments'],
						  'message' => $notf_message.' - Comments : '.$input['comments'],
						  'data' => $notf_message.' - Comments : '.$input['comments']
						]));
						$notification_obj = new Notification;
						$notification_obj->id = uniqid();
						$notification_obj->type = "transfer_campaign";
						$notification_obj->from_id =  null;
						$notification_obj->to_type = $notf_user_type;
						$notification_obj->to_id = $created_by_int;
						$notification_obj->campaign_id = $input['campaign_id'];
						$notification_obj->c_id = $transfer_campaign->cid;
						$notification_obj->c_name = $transfer_campaign->name;
						$notification_obj->to_client = $created_by_int;
						$notification_obj->desc = $notf_message.' - Comments : '.$input['comments'];
						$notification_obj->message = $notf_message.' - Comments : '.$input['comments'];
						$notification_obj->status = 0;
						$notification_obj->save();	
						
						return response()->json(["status" => "1", "message" => "Campaign Transferred Successfully"]);
					} else {
						return response()->json(["status" => "0", "message" => "Failed to Transfer"]);
					}
				} else {
					return response()->json(["status" => "0", "message" => "Failed to Transfer"]);
				}
			} else {
				return response()->json(["status" => "0", "message" => "Failed to Transfer"]);
			}
        }else{
			return response()->json(["status" => "0", "message" => "Failed to Transfer"]);
		}
    }

}
