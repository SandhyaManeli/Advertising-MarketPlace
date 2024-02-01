<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\Client;
use App\Models\ClientMongo;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Models\Permission;
use App\Models\UserMongo;
use App\Models\ClientType;
use App\Models\Notification;
use App\Helpers\NotificationHelper;
use App\Helpers\NetsuiteHelper;
use App\Helpers\NimbleHelper;
use JWTAuth;
use JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
use Log;
use App\Events\accountSuperAdminEvent;
use PDF;

class CompanyController extends Controller
{
  private $input;
  private $request;
  private $default_user_password = "BbiUser789#";
  /**
  * Create a new controller instance.
  *
  * @return void
  */
  public function __construct(Request $request)
  {
    // Resolve dependencies out of container
    $this->request = $request;
    if ($request->isJson()) {
      $this->input = $request->json()->all();
    } else {
      $this->input = $request->all();
    }
  }

  public function getClientTypes(){
    $company_types = ClientType::where('type', '!=', 'bbi')->select('id', 'type')->get();
    return response()->json($company_types);
  }

  public function registerClient(){
    $messages = [];
    if(!isset($this->input['email'])){
      $this->validate($this->request, 
        [
          'name' => 'required',
          'phone' => 'required',
          // 'type' => 'required'
        ],
        [
          'name.required' => 'Client/Organization name is required',
          'phone.required' => 'Phone is required',
          // 'type.required' => 'Type is required'
        ]
      );
      $company_slug = str_replace(" ", "-", strtolower($this->input['name']));
      // Client is being registered by Admin
       $check_client_id = Client::select('company_slug')->where("company_slug", "=",$company_slug )->first();
      /* if(!empty($check_client_id)){
        return response()->json(['status' => 0, 'message' => "This Company name already exist. Please try with another name or login with your exixting account."]);
      }*/
      $client = new Client(); 
      //$client->company_name = $this->input['name'];
      $client->company_name = $this->input['companyName'];
      // $bbi_type = ClientType::where('type', '=', 'bbi')->first();
      // if($this->input['type'] == $bbi_type->id){
      //   return response()->json(["status" => 0, "message" => $check_client_id,"message1" => $check_client_id1]);
      // }
      $owner_type = ClientType::where('type', '=', 'owner')->first();
      $client->type = $owner_type->id;
      $client->company_slug = $company_slug;
      $client->activated = true;
      $client->save();

      // No email id is given. so don't need to create a super admin
      $client_mongo = new ClientMongo;
      $client_mongo->id = uniqid();
	  
	  $client_mongo->register_from = '';
      if(isset($this->input['register_from']) && $this->input['register_from'] == 'advertising-market-platform'){
        $client_mongo->register_from = 1;
      }
	  
      $client_mongo->client_id = $client->id;
      //$client_mongo->company_name = $this->input['name'];
      $client_mongo->company_name = $this->input['companyName'];
      $client_mongo->company_slug = $client->company_slug;
      $client_mongo->contact_email = isset($this->input['contact_email']) ? $this->input['contact_email'] : "";
      $client_mongo->name = $this->input['name'];
      $client_mongo->phone = $this->input['phone'];
      $client_mongo->client_type = isset($this->input['type']) ? $this->input['type'] : "";
      $client_mongo->contact_name = isset($this->input['contact_name']) ? $this->input['contact_name'] : "";
      $client_mongo->address = isset($this->input['address']) ? $this->input['address'] : "";
      if($client_mongo->save()){
        return response()->json(['status' => 1, 'message' => 'client saved successfully.']);
      }
      else{
        return response()->json(['status' => 0, 'message' => 'failed to save company.']);
      }
      // Client registration by admin ends
    }
    else{
      $this->validate($this->request, 
        [
          'name' => 'required',
          'phone' => 'required',
          // 'type' => 'required',
          'contact_name' => 'required'
        ],
        [
          'name.required' => 'Client/Organization name is required',
          'phone.required' => 'Phone is required',
          // 'type.required' => 'Type is required',
          'contact_name.required' => 'Contact name is required'
        ]
      );
      // When the email is given i.e. user is registering the client
      $company_slug = str_replace(" ", "-", strtolower($this->input['name'])).uniqid();
      $check_client_id = Client::select('company_slug')->where("company_slug", "=", $company_slug)->first();
      if(!empty($check_client_id)){
        return response()->json(['status' => 0, 'message' => "This Company name already exist. Please try with another name or login with your exixting account."]);
      }
      $client_user = User::where("email", "=", $this->input['email'])->first();
      if(isset($client_user) && !empty($client_user)){
        //return response()->json(['status' => 0, 'message' => "The email provided already exists in the database. Please provide another email id, or login"]);
        return response()->json(['status' => 0, 'message' => "An account with this email already exists"]);
      }
      $client = new Client();
      //$client->company_name = $this->input['name'];
      $client->company_name = $this->input['companyName'];
      // $bbi_type = ClientType::where('type', '=', 'bbi')->first();
      // if($this->input['type'] == $bbi_type->id){
      //   return response()->json(["status" => 0, "message" => "Invalid company type"]);
      // }
      $owner_type = ClientType::where('type', '=', 'owner')->first();
      $client->type = $owner_type->id;
      $client->company_slug = $company_slug;
      $client->activated = true;
      $client->save();

      $client_mongo = new ClientMongo;
	  
	  $client_mongo->register_from = '';
      if(isset($this->input['register_from']) && $this->input['register_from'] == 'advertising-market-platform'){
        $client_mongo->register_from = 1;
      }
	  
      $client_mongo->id = uniqid();
      $client_mongo->client_id = $client->id;
	  $full_name = explode(" ", $this->input['name']);
	  $client_mongo->first_name = isset($full_name, $full_name[0]) ? $full_name[0] : "";
	  for($i = 1; $i < count($full_name) - 1; $i++){
		$client_mongo->middle_name .= $full_name[$i] . " ";
	  }
	  $client_mongo->name = isset($full_name) && (count($full_name) > 1) ? $full_name[ count($full_name) - 1 ] : "";
      
      $client_mongo->company_name = $this->input['companyName'];
      $client_mongo->company_slug = $client->company_slug;
      $client_mongo->email = isset($this->input['email']) ? $this->input['email'] : "";
      $client_mongo->name = $this->input['name'];
      $client_mongo->last_name = $this->input['last_name'];
      $client_mongo->phone = $this->input['phone'];
      $client_mongo->client_type = $owner_type->id;
      $client_mongo->contact_name = isset($this->input['contact_name']) ? $this->input['contact_name'] : "";
      $client_mongo->address = isset($this->input['address']) ? $this->input['address'] : "";
      $client_mongo->account_type = isset($this->input['account_type']) ? $this->input['account_type'] : "";
      //$client_mongo->save();
      if($client_mongo->save()){

		//NetSuite on 02-Dec-2022
			/*$u_type = 1;
			if($client_mongo->account_type == 'Individual Account'){
				$a_type = true;
			}else if($client_mongo->account_type == 'Business Account'){
				$a_type = false;
			}
			
			$data_string = array(
				'externalId' => $client_mongo->id,
				'companyName' => $client_mongo->company_name,
				'firstName' => $client_mongo->first_name,
				'middleName' => $client_mongo->middle_name,
				'lastName' => $client_mongo->last_name,
				'email' => $client_mongo->email,
				'phone' => $client_mongo->phone,
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
			  ]);*/
			
		//NetSuite end

      // only if email is provided
      // $client_user = User::where("email", "=", $this->input['email'])->first();
      // $client_type = ClientType::where('id', '=', $this->input['type'])->first();
      if(!isset($client_user) || empty($client_user)){
        // user with the given email id doesn't exist in system
        // create user.
        if(!isset($this->input['contact_name']) || empty($this->input['contact_name'])){
          return response()->json(['status' => 0, 'message' => "Please enter a name for the user who'll manage this account."]);
        }
        $user = new User();
        $user->email = $this->input['email'];
        $user->salt = str_random(7);
        //$user->password = md5($this->default_user_password . $user->salt);
		$user->password = md5($this->input['password'] . $user->salt);
        $user->activated = false;
        $user->client_id = $client->id;
        if($user->save()){
          // Save data to elasticsearch :: Pankaj 29 Nov 2021
          $get_data = User::where('id', '=', $user->id)->first();
          $this->es_etl_users_auth($get_data, "insert");
          // assign the user recently added as the super admin of the client
          $client->super_admin = $user->id;
          if(!$client->save()){
            // could not save user as super admin for this company. Admin needs to do it manually.
            array_push($messages, "Couldn't set up the super admin for the company. Please contact an admin for it.");
          }
          // assign basic user role
          $basic_user_role = Role::where('name', '=', 'basic_user')->first();
          $user->roles()->attach($basic_user_role);
          // create the user in mongo for profile details
          $user_mongo = new UserMongo;
          $user_mongo->id = uniqid();
          //$full_name = explode(" ", $this->input['contact_name']);
          $full_name = explode(" ", $this->input['name']);
          $user_mongo->first_name = isset($full_name, $full_name[0]) ? $full_name[0] : "";
          for($i = 1; $i < count($full_name) - 1; $i++){
            $user_mongo->middle_name .= $full_name[$i] . " ";
          }
          $user_mongo->name = isset($full_name) && (count($full_name) > 1) ? $full_name[ count($full_name) - 1 ] : "";
          $user_mongo->email = $this->input['email'];
          $user_mongo->last_name = $this->input['last_name'];
          $user_mongo->user_id = $user->id;
		  $nimble_client_id = 'AMP'.$user_mongo->user_id;
          $user_mongo->client_id = $client_mongo->client_id;
          $user_mongo->client_mongo_id = $client_mongo->id;
          $user_mongo->phone = isset($this->input['phone']) ? $this->input['phone'] : "";
          $user_mongo->name = isset($this->input['name']) ? $this->input['name'] : "";
          //$user_mongo->company_name = isset($this->input['name']) ? $this->input['name'] : "";
          $user_mongo->company_name = isset($this->input['companyName']) ? $this->input['companyName'] : "";
          $user_mongo->company_type = $owner_type->type;
          $user_mongo->address = isset($this->input['address']) ? $this->input['address'] : "";
          $user_mongo->account_type = isset($this->input['account_type']) ? $this->input['account_type'] : "";
          
          $user_mongo->verification_code = base64_encode(md5($this->input['email'] . uniqid()));
          $user_mongo->verification_code_expiry = new \MongoDB\BSON\UTCDateTime(strtotime("+30 minutes") * 1000); //strtotime returns seconds, we need milliseconds
          $user_mongo->verification_type = UserMongo::$VERIFICATION_TYPES['generate-password'];
		  if($user_mongo->save()){
			 
			//NetSuite on 02-Dec-2022
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
			  ]);*/
			
		//NetSuite end
		
		
		//========================Nimble Start==========================//	
			if(!empty($user_mongo->company_type))
			{
				$nimble_user_type = 'Seller';
			}
			else{
				$nimble_user_type = 'Buyer';
			}
		// Add Company
			$search_company = array('and' => array(array('company name' => array('is' => $user_mongo->company_name)),array('record type' => array('is' => 'company'))));
			$search_company_data = NimbleHelper::searchRecords([
				'httpMethod' => 'POST',
				'search_company' => $search_company 
			]);
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
		
		
            // Save data to elasticsearch :: Pankaj 29 Nov 2021
            $get_data = UserMongo::where('id', '=', $user_mongo->id)->first();
            $this->es_etl_users($get_data, "insert");
            $client_mongo->super_admin_m_id = $user_mongo->id;
            $client_mongo->save();
            $user_email = $this->input['email'];
            /*Mail::send('mail.generate-password', ['verification_code' => $user_mongo->verification_code, 'name' => $this->input['contact_name']], function($message) use ($user_email){
             // $message->to($user_email)->subject('Welcome to Billboards America!');
              $message->to($user_email)->subject('Welcome to Advertising Marketplace!');
            });*/
      
      Mail::send('mail.registration', ['user' => $user_mongo], function($message) use ($user_mongo){
          $message->to($user_mongo->email, $user_mongo->first_name . " " . $user_mongo->last_name)->subject('Welcome to Advertising Marketplace!');
        });
      
            $bbi_sa_id = Client::where('company_slug', '=', 'bbi')->first()->super_admin;
            $bbi_sa = UserMongo::where('user_id', '=', $bbi_sa_id)->first();
           
             event(new accountSuperAdminEvent([
              'type' => Notification::$NOTIFICATION_TYPE['account-super-admin-setup'],
              'from_id' => $user_mongo['id'],
              'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
              'to_id' => null,
              'to_client' => null,
              'desc' => "New owner registered",
              //'message' => ucfirst($client_mongo->company_name) ." Registered as a Owner and Waiting For activation.",
              //'message' => ucfirst($user_mongo->name) ." Registered as a Owner and Waiting For activation.",
              'message' => ucfirst($user_mongo->first_name . " " . $user_mongo->last_name) ." Registered as a Owner and Waiting For activation.",
        'data' => ["client_m_id" => $user_mongo['id']]
            ]));
      $notification_obj = new Notification;
      $notification_obj->id = uniqid();
            $notification_obj->type = "Owner_registartion";
            $notification_obj->from_id =  $user_mongo['id'];
            $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['bbi'];
            $notification_obj->to_id = null;
            $notification_obj->to_client = null;
            $notification_obj->desc = "New owner registered";
            //$notification_obj->message = ucfirst($client_mongo->company_name) ." Registered as a Owner and Waiting For activation.";
            //$notification_obj->message = ucfirst($user_mongo->name) ." Registered as a Owner and Waiting For activation.";
            $notification_obj->message = ucfirst($user_mongo->first_name . " " . $user_mongo->last_name) ." Registered as a Owner and Waiting For activation.";
                    $notification_obj->client_id = $user_mongo['id'];
          $notification_obj->status = 0;
                    $notification_obj->save();

            $mail_tmpl_params = [
              'sender_email' => $user_mongo['email'], 
              'receiver_name' => $bbi_sa->first_name . ' ' . $bbi_sa->last_name,
              'mail_message' => 'Company registered. Please set up a super admin for company ' . ucfirst($client_mongo->company_name) . '.'
            ];
            $mail_data = [
              'email_to' => $bbi_sa->email,
              'recipient_name' => $bbi_sa->first_name
            ];
            Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data){
             // $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Super admin set up for company - Billboards India');
              $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Super admin set up for company - Advertising Marketplace');
            });
            if(!Mail::failures()){
              // Everything went well till here. notify user
              return response()->json(['status' => 1, 'message' => 'Registration successful. Please go to your email to generate password.']);
            }
            else{
              return response()->json(['status' => 0, 'message' => "There was an error sending the welcome email. Please contact admin."]);    
            }
          }
          else{
            return response()->json(['status' => 0, 'message' => "A technical error occured. Please try again later or contact the admin."]);  
          }
        }
        }
        else{
          return response()->json(['status' => 0, 'message' => 'Failed to create account.']);
        }
      }
      else{
        // user exists in system. send verification email to verify 
        // that this company has access to that email account
        
        // make sure no other client has this user as its super admin
        // $client_with_this_user = Client::where('super_admin', '=', $client_user->id)->get();
        // if($client_with_this_user->count() > 0){
        //   return response()->json(['status' => 0, 'message' => "You can not assign the user with given email id as super admin of this company."]);
        // }
        // else{
        //   $user_mongo = UserMongo::where('user_id', '=', $client_user->id)->first();
        //   $user_mongo->verification_code = base64_encode(md5($this->input['email'] . uniqid()));
        //   $user_mongo->verification_code_expiry = new \MongoDB\BSON\UTCDateTime(strtotime("+30 minutes") * 1000); //strtotime returns seconds, we need milliseconds
        //   $user_mongo->verification_type = UserMongo::$VERIFICATION_TYPES['verify-email-company'];
        //   if($user_mongo->save()){
        //     $user_email = $this->input['email'];
        //     Mail::send('mail.verify-email-company', ['verification_code' => $user_mongo->verification_code, 'name' => $this->input['user_name']], function($message) use ($user_email){
        //       $message->to($user_email)->subject('Welcome to Billboards India!');
        //     });
        //     if(!Mail::failures()){
        //       // Everything went well till here. do nothing.
        //     }
        //     else{
        //       return response()->json(['status' => 0, 'message' => "There was an error sending the welcome email. Please contact admin."]);    
        //     }
        //   }
        //   else{
        //     return response()->json(['status' => 0, 'message' => "A technical error occured. Please try again later or contact the admin."]);  
        //   }
        // }
        //return response()->json(["status" => 0, "message" => "the email id you provided already exists in database. Please provide a different email id."]);
        return response()->json(["status" => 0, "message" => "An account with this email already exists."]);
      }
      // Client registration by user ends 
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
  
  public function addCompany(){
    if(isset($this->input['id'])){
      $company = Company::where('id', '=', $this->input['id'])->first();
      if(!isset($company) || empty($company)){
        return response()->json(['status' => 0, 'message' => ['Company not found in database.']]);
      }
      $company->name = isset($this->input['name']) ? $this->input['name'] : $company->name;
      $company->contact_name = isset($this->input['contact_name']) ? $this->input['contact_name'] : (isset($company->contact_name) ? $company->contact_name : "");
      $company->company_type = isset($this->input['company_type']) ? $this->input['company_type'] : (isset($company->company_type) ? $company->company_type : "");
      $company->contact_email = isset($this->input['contact_email']) ? $this->input['contact_email'] : $company->contact_email;
      $company->contact_phone = isset($this->input['contact_phone']) ? $this->input['contact_phone'] : $company->contact_phone;
      $company->address = isset($this->input['address']) ? $this->input['address'] : (isset($company->address) ? $company->address : "");
      if($company->save()){
        return response()->json(['status' => 1, 'message' => 'company details updated successfully.']);
      }
      else{
        return response()->json(['status' => 0, 'message' => 'failed to save company.']);
      }
    }
    else{
      $this->validate($this->request, 
        [
          'name' => 'required',
          'contact_email' => 'required',
          'contact_phone' => 'required'
        ],
        [
          'name.required' => 'Name is required',
          'contact_email.required' => 'Email is required',
          'contact_phone.required' => 'Phone is required'
        ]
      );
      $company_slug = str_replace(" ", "-", strtolower($this->input['name']));
      $existing_company = Company::where('company_slug', '=', $company_slug)->first();
      if(isset($existing_company) && !empty($existing_company)){
        return response()->json(['status' => 0, 'message' => ["The company already exists."]]);
      }
      $company = new Company;
      $company->id = uniqid();
      $company->name = $this->input['name'];
      $company->company_slug = $company_slug;
      $company->contact_name = isset($this->input['contact_name']) ? $this->input['contact_name'] : "";
      $company->company_type = isset($this->input['company_type']) ? $this->input['company_type'] : "";
      $company->contact_email = $this->input['contact_email']; // has to be unique
      $company->contact_phone = $this->input['contact_phone'];
      $company->address = isset($this->input['address']) ? $this->input['address'] : "";
      if($company->save()){
        return response()->json(['status' => 1, 'message' => 'company saved successfully.']);
      }
      else{
        return response()->json(['status' => 0, 'message' => 'failed to save company.']);
      }
    }
  }

  public function getCompanies(){
    $companies = Company::all();
    return response()->json($companies);
  }

  public function addClient(){
    $this->validate($this->request, 
      [
        'name' => 'required',
        'email' => 'required',
        'phone' => 'required'
      ],
      [
        'name.required' => 'Name is required',
        'email.required' => 'Email is required',
        'phone.required' => 'Phone is required'
      ]
    );
    $hoardingCompany = new Company;
    $hoardingCompany->id = uniqid();
    $hoardingCompany->email = $this->input['email'];
    $hoardingCompany->name = $this->input['name'];
    $hoardingCompany->first_name = $this->input['first_name'];
    $hoardingCompany->last_name = $this->input['last_name'];
    $hoardingCompany->phone = $this->input['phone'];
    $hoardingCompany->client_type = isset($this->input['client_type']) ? $this->input['client_type'] : "";
    $hoardingCompany->person_name = isset($this->input['person_name']) ? $this->input['person_name'] : "";
    $hoardingCompany->address = isset($this->input['address']) ? $this->input['address'] : "";
    $hoardingCompany->owner = isset($this->input['owner']) ? $this->input['owner'] : "";
    $hoardingCompany->company_type = Company::$COMPANY_TYPE['hoarding'];
    if($hoardingCompany->save()){
      return response()->json(['status' => 1, 'message' => 'hoarding company saved successfully.']);
    }
    else{
      return response()->json(['status' => 0, 'message' => 'failed to save hoarding company.']);
    }
  }

  // public function getHoardingCompanies(){
  //  $hoardingCompanies = Company::where('company_type', '=', Company::$COMPANY_TYPE['hoarding'])->get();
  //  return response()->json($hoardingCompanies);
  // }
  
  public function pwdGenerationCheck($verification_code){
    $this->validate($this->request, [
      'code' => 'required',
      'newPassword' => 'required|min:6',
      'confirmNewPassword' => 'required|min:6'
    ]);
    $user_mongo = UserMongo::where([
      ['verification_code', '=', $verification_code],
      ['verification_type', '=', UserMongo::$VERIFICATION_TYPES['generate-password']]
    ])->first();
    if(isset($user_mongo) && $user_mongo->verification_code_expiry > new \MongoDB\BSON\UTCDateTime()){
      if($this->input['newPassword'] == $this->input['confirmNewPassword']){
        $user = User::where('id', '=', $user_mongo->user_id)->first();
        $user->password = md5($this->input['newPassword'] . $user->salt);
        if($user->save()){
          // send notification to admins to set up the account
         /* NotificationHelper::createNotification([
            'type' => Notification::$NOTIFICATION_TYPE['account-super-admin-setup'],
            'from_id' => $user_mongo->id,
            'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
            'to_id' => null,
            'to_client' => null,
            'desc' => "New admin setup for a company",
            'message' => "New company registered. Please set up the admin",
            'data' => ["user_mongo_id" => $user_mongo->id]
          ]);*/
           event(new accountSuperAdminEvent([
            'type' => Notification::$NOTIFICATION_TYPE['account-super-admin-setup'],
            'from_id' => $user_mongo->id,
            'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
            'to_id' => null,
            'to_client' => null,
            'desc' => "New admin setup for a company",
            'message' => "New company registered. Please set up the admin",
            'data' => ["user_mongo_id" => $user_mongo->id]
          ]));
          $bbi_sa_id = Client::where('company_slug', '=', 'bbi')->first()->super_admin;
          $bbi_sa = UserMongo::where('user_id', '=', $bbi_sa_id)->first();
          $mail_tmpl_params = [
            'sender_email' => $user_mongo['email'], 
            'receiver_name' => $bbi_sa->first_name . ' ' . $bbi_sa->last_name,
            'mail_message' => $user_mongo['first_name'] . ' ' . $user_mongo['last_name'] . 'needs to be set up as super admin for ' . $user_mongo->company_name . '. Please do the needful.'
          ];
          $mail_data = [
            'email_to' => $bbi_sa->email,
            'recipient_name' => $bbi_sa->first_name
          ];
          Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data){
            $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Super admin set up for an ad space owner');
          });
          return response()->json(['status' => 1, 'message' => "Your password has been created successfully."]);
        }
        else{
          return response()->json(['status' => 0, 'message' => "There was a technical problem while updating your password. Please contact an administrator."]);
        }
      }
      else{
        return response()->json(['status' => 0, 'message' => "The 2 passwords entered do not match."]);
      }
    }
  }

  public function getAllClients(){
    $client_ids = Client::where("company_name", "<>", "bbi")->pluck('id');
  
    $client_data_arr = [];
    foreach($client_ids as $client_id){
      $client = Client::where('id', '=', $client_id)->first();
  
      $client_mongo = ClientMongo::where('client_id', '=', $client_id)->first();
      if(isset($client->super_admin)){
        $user_mongo = UserMongo::where('user_id', '=', $client->super_admin)->first();
      }

      if(isset($client, $client_mongo) && !empty($client) && !empty($client_mongo)){
        $client_data = array_merge($client->toArray(), $client_mongo->toArray());
        $client_data = array_merge($client_data, ['company_type_name' => ClientType::where('id', '=', $client->type)->first()->type]);
        if(isset($user_mongo) && !empty($user_mongo)){
          $client_data = array_merge($client_data, ['company_super_admin' => $user_mongo->id]);
        }
        array_push($client_data_arr, $client_data);
        $client = null;
        $client_mongo = null;
        $user_mongo = null;
      }
      else{
        return response()->json(["status" => 0, "message" => "The data for one or more client is corrupted. Please contact the DBA."]);
      }
    }
    return response()->json($client_data_arr);
  }

  public function resendOwnerInviteEmail(){
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
    $user = User::where([
      ['email', '=', $this->input['super_admin_email']],
      ['client_id', '=', $this->input['client_id']]
    ])->first();
    if(!isset($user) || empty($user)){
      return response()->json(['status' => 0, 'message' => 'User not found.']);
    }
    $user_mongo = UserMongo::where('user_id', '=', $user->id)->first();
    if(!isset($user_mongo) || empty($user_mongo)){
      return response()->json(['status' => 0, 'message' => 'User not found in database.']);
    }
    else{
      $user_mongo->verification_code = base64_encode(md5($this->input['super_admin_email'] . uniqid()));
      $user_mongo->verification_code_expiry = new \MongoDB\BSON\UTCDateTime(strtotime("+3 days") * 1000); //strtotime returns seconds, we need milliseconds
      $user_mongo->verification_type = UserMongo::$VERIFICATION_TYPES['user-invitation'];
      if($user_mongo->save()){
        $user_email = $this->input['super_admin_email'];
        // send the invitation email
        Mail::send('mail.invite-user', ['verification_code' => $user_mongo->verification_code], function($message) use ($user_email){
         // $message->to($user_email)->subject('Invitation from Billboards India!');
          $message->to($user_email)->subject('Invitation from Advertising Marketplace!');
        });
        if(!Mail::failures()){
          return response()->json(['status' => 1, 'message' => 'Invitation email sent successfully.']);
        }
        else{
          return response()->json(['status' => 0, 'message' => 'Failed to send the email. Please try again.']);
        }
      }
      else{
        return response()->json(['status' => 0, 'message' => 'Prerequisites for sending the email not fulfilled.']);
      }
    }
  }
  

}
