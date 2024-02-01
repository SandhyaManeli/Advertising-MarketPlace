<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\Product;
use App\Models\Campaign;
use App\Models\Format;
use App\Models\Area;
use App\Models\Client;
use App\Models\ClientMongo;
use App\Models\ClientType;
use App\Models\ShortListedProduct;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\UserMongo;
use App\Helpers\NotificationHelper;
use App\Models\Notification;
use App\Models\ProductBooking;
use App\Models\MetroCorridor;
use App\Models\MetroPackage;
use App\Models\City;
use App\Models\CampaignProduct;
use App\Models\CampaignPayment;
use App\Models\Comments;
use App\Models\CampaignQuoteChange;
use App\Models\BulkUpload;
use App\Models\SubSeller;
use App\Models\SubSellerUser;
use JWTAuth;
use Auth;
use Entrust;
use PDF;
use App\Jobs\UpdateProductEverywhere;
use Log;
use App\Events\ProductApprovedEvent;
use App\Events\ProductRequestedEvent;
use DB;
use JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Events\accountSuperAdminEvent;
use App\Jobs\UpdateUserEverywhere; 

class SubSellerController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $request, $input;

    public function __construct(Request $request) {
        $this->request = $request;
        if ($request->isJson()) {
            $this->input = $request->json()->all();
        } else {
            $this->input = $request->all();
        }
    }
	
	
	public function registerSubSeller(){
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
      $client->company_name = $this->input['name'];
      // $bbi_type = ClientType::where('type', '=', 'bbi')->first();
      // if($this->input['type'] == $bbi_type->id){
      //   return response()->json(["status" => 0, "message" => $check_client_id,"message1" => $check_client_id1]);
      // }
      //$owner_type = ClientType::where('type', '=', 'owner')->first();
      $owner_type = ClientType::where('type', '=', 'sub-seller')->first();
      $client->type = $owner_type->id;
      $client->company_slug = $company_slug;
      $client->activated = true;
      $client->save();

      // No email id is given. so don't need to create a super admin
      $client_mongo = new ClientMongo;
      $client_mongo->id = uniqid();
      $client_mongo->client_id = $client->id;
      $client_mongo->company_name = $this->input['name'];
      $client_mongo->company_slug = $client->company_slug;
      $client_mongo->contact_email = isset($this->input['contact_email']) ? $this->input['contact_email'] : "";
      $client_mongo->name = $this->input['name'];
      $client_mongo->phone = $this->input['phone'];
      $client_mongo->client_type = isset($this->input['type']) ? $this->input['type'] : "";
      $client_mongo->contact_name = isset($this->input['contactName']) ? $this->input['contactName'] : "";
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
          'contactName' => 'required'
        ],
        [
          'name.required' => 'Client/Organization name is required',
          'phone.required' => 'Phone is required',
          // 'type.required' => 'Type is required',
          'contactName.required' => 'Contact name is required'
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
        return response()->json(['status' => 0, 'message' => "The email provided already exists in the database. Please provide another email id, or login"]);
      }
      $client = new Client();
      $client->company_name = $this->input['name'];
      // $bbi_type = ClientType::where('type', '=', 'bbi')->first();
      // if($this->input['type'] == $bbi_type->id){
      //   return response()->json(["status" => 0, "message" => "Invalid company type"]);
      // }
      //$owner_type = ClientType::where('type', '=', 'owner')->first();
      $owner_type = ClientType::where('type', '=', 'sub-seller')->first();
      $client->type = $owner_type->id;
      $client->company_slug = $company_slug;
      $client->activated = true;
      $client->save();

      $client_mongo = new ClientMongo;
      $client_mongo->id = uniqid();
      $client_mongo->client_id = $client->id;
      $client_mongo->company_name = $this->input['name'];
      $client_mongo->company_slug = $client->company_slug;
      $client_mongo->email = isset($this->input['email']) ? $this->input['email'] : "";
      $client_mongo->name = $this->input['name'];
      $client_mongo->phone = $this->input['phone'];
      $client_mongo->client_type = $owner_type->id;
      $client_mongo->contact_name = isset($this->input['contactName']) ? $this->input['contactName'] : "";
      $client_mongo->address = isset($this->input['address']) ? $this->input['address'] : "";
      $client_mongo->save();

      // only if email is provided
      // $client_user = User::where("email", "=", $this->input['email'])->first();
      // $client_type = ClientType::where('id', '=', $this->input['type'])->first();
      if(!isset($client_user) || empty($client_user)){
        // user with the given email id doesn't exist in system
        // create user.
        if(!isset($this->input['contactName']) || empty($this->input['contactName'])){
          return response()->json(['status' => 0, 'message' => "Please enter a name for the user who'll manage this account."]);
        }
        $user = new User();
        $user->email = $this->input['email'];
        $user->salt = str_random(7);
        //$user->password = md5($this->default_user_password . $user->salt);
        $user->activated = false;
        $user->client_id = $client->id;
        if($user->save()){
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
          $full_name = explode(" ", $this->input['contactName']);
          $user_mongo->first_name = isset($full_name, $full_name[0]) ? $full_name[0] : "";
          for($i = 1; $i < count($full_name) - 1; $i++){
            $user_mongo->middle_name .= $full_name[$i] . " ";
          }
          $user_mongo->last_name = isset($full_name) && (count($full_name) > 1) ? $full_name[ count($full_name) - 1 ] : "";
          $user_mongo->email = $this->input['email'];
          $user_mongo->user_id = $user->id;
          $user_mongo->client_id = $client_mongo->client_id;
          $user_mongo->client_mongo_id = $client_mongo->id;
		  $user_mongo->company_name = $client_mongo->company_name;
          $user_mongo->phone = isset($this->input['phone']) ? $this->input['phone'] : "";
          //$user_mongo->company_name = isset($this->input['name']) ? $this->input['name'] : ""; 
          $user_mongo->company_type = $owner_type->type;
          $user_mongo->address = isset($this->input['address']) ? $this->input['address'] : "";
          
          $user_mongo->verification_code = base64_encode(md5($this->input['email'] . uniqid()));
          $user_mongo->verification_code_expiry = new \MongoDB\BSON\UTCDateTime(strtotime("+30 minutes") * 1000); //strtotime returns seconds, we need milliseconds
          $user_mongo->verification_type = UserMongo::$VERIFICATION_TYPES['subseller-generate-pwd'];
          if($user_mongo->save()){
			  
			$pdf = PDF::loadView('pdf.subseller_pdf');
			$mail_data_pdf = [
				'email' => $this->input['email'],
				'pdf_file_name' => "SubSeller". date('d-m-Y') . ".pdf",
				'pdf' => $pdf
			];
			//echo '<pre>';print_r($mail_data_pdf);exit; 
            $client_mongo->super_admin_m_id = $user_mongo->id;
            $client_mongo->save();	 
		
            //$user_email = $this->input['email'];
            /*Mail::send('mail.subseller-generate-pwd', ['verification_code' => $user_mongo->verification_code, 'name' => $this->input['contactName']], function($message) use ($mail_data_pdf){*/
			Mail::send('mail.subseller-generate-pwd', ['verification_code' => $user_mongo->verification_code, 'name' => $this->input['contactName']], function($message) use ($mail_data_pdf){
             // $message->to($user_email)->subject('Welcome to Billboards America!');   
              $message->to($mail_data_pdf['email'])->subject('Welcome to Advertising Marketplace!');
			  $message->attachData($mail_data_pdf['pdf']->output(), $mail_data_pdf['pdf_file_name']);
            });
            $bbi_sa_id = Client::where('company_slug', '=', 'bbi')->first()->super_admin;
            $bbi_sa = UserMongo::where('user_id', '=', $bbi_sa_id)->first();
           
             event(new accountSuperAdminEvent([
              'type' => Notification::$NOTIFICATION_TYPE['account-super-admin-setup'],
              'from_id' => $user_mongo['id'],
              'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
              'to_id' => null,
              'to_client' => null,
              'desc' => "New Sub Seller registered",
              'message' => ucfirst($client_mongo->company_name) ." Registered as a Sub Seller and Waiting For activation.",
			  'data' => ["client_m_id" => $user_mongo['id']]
            ]));
			$notification_obj = new Notification;
			$notification_obj->id = uniqid();
            $notification_obj->type = "SubSeller_registartion";
            $notification_obj->from_id =  $user_mongo['id'];
            $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['bbi'];
            $notification_obj->to_id = null;
            $notification_obj->to_client = null;
            $notification_obj->desc = "New Sub Seller registered";
            $notification_obj->message = ucfirst($client_mongo->company_name) ." Registered as a Sub Seller and Waiting For activation.";
                    $notification_obj->client_id = $user_mongo['id'];
					$notification_obj->status = 0;
                    $notification_obj->save();
					 
			$notif_mail_message = <<<EOF
          A new Sub Seller joined Advertising Marketplace. User details: <br /><br />
          
          Name: {$client_mongo->company_name}<br />
          Email: {$client_mongo->email}<br />
          Phone: {$client_mongo->phone}<br /><br />

          For more details,.
EOF;
 
            $mail_tmpl_params = [
              'sender_email' => $user_mongo['email'], 
              //'receiver_name' => $bbi_sa->first_name . ' ' . $bbi_sa->last_name,
              'receiver_name' => $this->input['contactName'], 
              'receiver_name1' => 'Sandhya', 
			  'mail_message' => 'Thank you for registering, Your SubSeller account has been setup. Please set your password ' . ucfirst($client_mongo->company_name) . '.',
			  'mail_message1' => $notif_mail_message
            ];  
            $mail_data = [
              //'email_to' => $bbi_sa->email,
              'email_to' => $this->input['email'],
              'email_to1' => 'sandhyarani.manelli@peopletech.com',
              'cc_name' => 'Sandhya',
              'recipient_name' => $this->input['contactName']
            ];
            Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data){
              $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('SubSeller Registration - Advertising Marketplace');
            });
			Mail::send('mail.new_user', $mail_tmpl_params, function($message) use ($mail_data){
				$message->to($mail_data['email_to1'], $mail_data['cc_name'])->subject('New SubSeller Registered - Advertising Marketplace');
			  //$message->cc($mail_data['email_to1'], $mail_data['cc_name'])->subject('New SubSeller Registered - Advertising Marketplace'); 
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
        else{
          return response()->json(['status' => 0, 'message' => 'Failed to create account.']);
        }
      }
      else{
        return response()->json(["status" => 0, "message" => "the email id you provided already exists in database. Please provide a different email id."]);
      }
      // Client registration by user ends 
    }
  }
	
	
public function addSubSeller(Request $request){ 
	$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo']; 
	//$user_mongo = JWTAuth::parseToken()->getPayload()['SubSeller']; 
	//echo '<pre>'; print_r($request); exit;
	if ($request->isJson()) {
	$input = $request->json()->all();
	} else {
		$input = $request->all();
	}
	
	if (isset($this->input['editRequestedhordings']) && !empty($this->input['editRequestedhordings'])) {
            //$input['subseller_id'] = $this->input['editRequestedhordings'];
            $input = $this->input['editRequestedhordings'];
        }else{
			//$input['subseller_id']=$this->input;  
			$input = $this->input;   
			//$input=$this->input;
		}
	
	//echo '<pre>input'; print_r($input); exit;
	if (isset($input['client'])) {
	$client = ClientMongo::where('id', '=', $input['client'])->first(); 
	}else{
		$client = ClientMongo::where('id', '=', $user_mongo['client_mongo_id'])->first(); 
	}

	if (isset($input['subseller_id'])) {
		//echo "in edit";exit;
		$sub_seller = SubSeller::where('id', '=', $input['subseller_id'])->first();
		
		 $sub_seller->name = isset($input['name']) ? $input['name'] : $sub_seller->name;;
		 $sub_seller->email = isset($input['email']) ? $input['email'] : $sub_seller->email;
		 $sub_seller->phone = isset($input['phone']) ? $input['phone'] : $sub_seller->phone;
		 $sub_seller->designation = isset($input['designation']) ? $input['designation'] : $sub_seller->designation;
		 
		 if ($sub_seller->save()) {
				   $success = true;
               
				if($success == true){
                return response()->json(["status" => "1", "message" => "Sub Seller Updated successfully."]);
			}
            } else {
                return response()->json(["status" => "0", "message" => "Failed to Update Sub Seller."]);
            }
		
		
	}
	
	else {
		try { 
			//echo "in create";exit;   
	$repeated_user = SubSeller::where('email', '=', $this->input['email'])->get();  
    if(!empty($repeated_user) && count($repeated_user) > 0){
      return response()->json(['status' => 0, 'message' => 'The email id given already exists in database.']);
    }
	
	$user = new SubSellerUser();
	
    if(isset($this->input['name'])){
      $user->subseller_username = $this->input['name'];
    }
	
    $user->subseller_email = $this->input['email'];
    $user->salt = str_random(7);
	
    $user->subseller_password = md5('subselleruser' . $user->salt);
    //$user->client_id = isset($this->input['client_id']) ? $this->input['client_id'] : NULL;
    $user->activated = false;
	//echo "<pre>";print_r($user);exit;
    if($user->save()){
		//return response()->json(['status' => 1, 'message' => 'User Created.']);
		//echo "<pre>";print_r($user);exit;
      // assign basic user role
      //$role = Role::where('name', '=', 'basic_user')->first();
     // $user->roles()->attach($role);
      // create the user in mongo for profile details
     // $client_mongo = ClientMongo::where('client_id', '=', $user->client_id)->first();
      /*$sub_seller = new SubSeller;
      $sub_seller->id = uniqid();
      $sub_seller->email = $this->input['email'];
      $sub_seller->user_id = $user->id;
      if(isset($this->input['client_id'])){
        $sub_seller->client_id = $client_mongo->client_id;
        $sub_seller->client_mongo_id = $client_mongo->id; 
      }*/
	   
	
    $sub_seller = new SubSeller(); 
	$sub_seller->id = uniqid();
	$sub_seller->subseller_id = $sub_seller->id;
	//$subseller = SubSeller::where('client_mongo_id', '=', $subsellerinformation['seller_id'])->first();	
    $sub_seller->seller_id = $client->id;
	$sub_seller->seller_name = $client->name;
	//$sub_seller->subseller_name = isset($input['subseller_name']) ? $input['subseller_name'] : "";
	$sub_seller->name = isset($input['name']) ? $input['name'] : "";  
	$sub_seller->email = isset($input['email']) ? $input['email'] : "";
	$sub_seller->phone = isset($input['phone']) ? $input['phone'] : "";
	$sub_seller->designation = isset($input['designation']) ? $input['designation'] : ""; 
	
	//echo '<pre>'; print_r($sub_seller); exit; 
	
			$sub_seller->verification_code = base64_encode(md5($this->input['email'] . uniqid()));
            $sub_seller->verification_code_expiry = new \MongoDB\BSON\UTCDateTime(strtotime("+3 days") * 1000); //strtotime returns seconds, we need milliseconds
			$sub_seller->verification_type = SubSeller::$VERIFICATION_TYPES['subseller-generate-pwd'];
    
	//echo '<pre>'; print_r($subseller); exit;	 
            if($sub_seller->save()){
				$pdf = PDF::loadView('pdf.subseller_pdf');
				//echo '<pre>'; print_r($pdf); exit;
              //$user_email = $this->input['email']; 
				$mail_data = [
					'email' => $this->input['email'],
					//'pdf_file_name' => ".pdf",
					'pdf_file_name' => "SubSeller". date('d-m-Y') . ".pdf",
					'pdf' => $pdf
				];	
				//echo '<pre>'; print_r($mail_data); exit;				
              // send the invitation email
			  
              Mail::send('mail.subseller-generate-pwd', ['verification_code' => $sub_seller->verification_code, 'name' => $sub_seller->name], function($message) use ($mail_data){
                $message->to($mail_data['email'])->subject('You have been invited to Advertising Marketplace'); 
				$message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
              });
			  //echo '<pre>'; print_r(Mail); exit;
              if(!Mail::failures()){
                return response()->json(['status' => 1, 'message' => "A password link has been sent to your email successfully."]);
              }
              else{
                return response()->json(['status' => 0, 'message' => "There was an error sending the password reset link to your email. Please contact admin."]);    
              }
            }
            else{
              return response()->json(['status' => 0, 'message' => "A technical error occured. Please try again later or contact the admin."]);
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
	
	public function sendInviteToSubSeller(){
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
		$pdf = PDF::loadView('pdf.subseller_pdf');
			$mail_data_pdf = [
				'user_email' => $user_email,
				'pdf_file_name' => "SubSeller". date('d-m-Y') . ".pdf",
				'pdf' => $pdf
			];
		
        Mail::send('mail.invite-user', ['verification_code' => $user_mongo->verification_code], function($message) use ($mail_data_pdf){
          //$message->to($user_email)->subject('Invitation from Billboards India!');
          //$message->to($user_email)->subject('Invitation from Advertising Marketplace!');
		  $message->to($mail_data_pdf['user_email'])->subject('Welcome to Advertising Marketplace!');
		  $message->attachData($mail_data_pdf['pdf']->output(), $mail_data_pdf['pdf_file_name']);
        });
        if(!Mail::failures()){
          return response()->json(['status' => 1, 'message' => "Sub Seller invitation sent."]); 
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
				$pdf = PDF::loadView('pdf.subseller_pdf');
					$mail_data_pdf = [
						'user_email' => $user_email,
						'pdf_file_name' => "SubSeller". date('d-m-Y') . ".pdf",
						'pdf' => $pdf
					];
				
				Mail::send('mail.invite-user', ['verification_code' => $user_mongo->verification_code], function($message) use ($mail_data_pdf){
				  //$message->to($user_email)->subject('Invitation from Billboards India!');
				  //$message->to($user_email)->subject('Invitation from Advertising Marketplace!'); 
				  $message->to($mail_data_pdf['user_email'])->subject('Welcome to Advertising Marketplace!');
				  $message->attachData($mail_data_pdf['pdf']->output(), $mail_data_pdf['pdf_file_name']);
				});
              if(!Mail::failures()){
                return response()->json(['status' => 1, 'message' => "Sub Seller invitation sent."]);
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
	
	
	public function subsellerGeneratePassword(){
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
    $sub_seller = SubSeller::where('verification_code', '=', $this->input['code'])->first();
    if(isset($sub_seller) && $sub_seller->verification_code_expiry > new \MongoDB\BSON\UTCDateTime()){
      if($this->input['newPassword'] == $this->input['confirmNewPassword']){
        $user = SubSellerUser::where('id', '=', $sub_seller->subseller_id)->first();
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
      return response()->json(['status' => 0, 'message' => "The password reset link has expired. Please try again."]);
    }
  }
	 

	public function deleteSubSeller($subseller_id) {

        $success = SubSeller::where('id', '=', $subseller_id)->delete();
        if ($success) {
            return response()->json(['status' => 1, 'message' => 'Sub Seller was  deleted successfully.']);
        } else {
            return response()->json(['status' => 0, 'message' => 'There was a trouble deleting the Sub Seller. Please try again.']);
        }
    }	
	public function getSubSellerDetails() {
		
		$subsellerInformation = SubSeller::orderBy('name', 'asc')->get();
		return response()->json($subsellerInformation);
    }
} 