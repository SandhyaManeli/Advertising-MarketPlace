<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Database\DatabaseManager;
use App\Models\Marker;
use App\Models\CustomerQuery;
use App\Models\User;
use App\Models\UserMongo;
use App\Models\Client;
use App\Models\ClientMongo;
use App\Helpers\NotificationHelper;
use App\Models\Notification;

use Auth;
use Entrust;
use JWTAuth;
use JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Jobs\UpdateUserEverywhere;
use Log;
use App\Events\accountSuperAdminEvent;


class CustomerSupportController extends Controller
{
  private $request, $input;
  /**
    * Create a new controller instance.
    *
    * @return void
    */
  public function __construct(Request $request)
  {
    $this->request = $request;
    if ($request->isJson()) {
      $this->input = $request->json()->all();
    } else {
      $this->input = $request->all();
    }
    
		// Resolve dependencies out of container
		// $this->middleware('jwt.auth', ['only' => [
		// 	'loginRequired'
    // ]]);
    // $this->middleware('role:owner', ['only' => [
    //   'isOwner'
    // ]]);
    // $this->middleware('role:admin', ['only' => [
    //   'isAdmin'
    // ]]);
    // $this->middleware('role:admin|owner', ['only' => [
    //   'isAdmin',
    //   'isOwner',
    //   'isAdminOrOwner'
    // ]]);
    // $this->middleware('permission:create-user', ['only' => [
    // ]]);
    // $this->middleware('ability:admin|owner, , false', ['only' => [
    //   'isAdminOrOwner'
    // ]]);
	}

  public function requestCallback(){
    $this->validate($this->request, [
      // 'type' => 'request-callback',
      'phoneNo' => 'required'
    ]);
    $customer_query_obj = new CustomerQuery;
    $customer_query_obj->type = 'request-callback';
    $customer_query_obj->phoneNo = isset($this->input['phoneNo']) ? $this->input['phoneNo'] : "";
    $customer_query_obj->id = uniqid();
    $customer_query_obj->softdelete = false;
    if($customer_query_obj->save()){
      return response()->json(["status" => "1", "message" => "We have received your call back request. Our representative will reach out to you."]);
    }
    else{
      return response()->json(["status" => "0", "message" => "There was an error while creating the call back request. Please try again later."]);
    }
  }

  public function createSubscription(){
    $this->validate($this->request, [
      // 'type' => 'newsletter-subscription',
      'email' => 'required'
    ]);
    $customer_query_obj = new CustomerQuery;
    $customer_query_obj->type = 'newsletter-subscription';
    $customer_query_obj->email = isset($this->input['email']) ? $this->input['email'] : "";
    $customer_query_obj->id = uniqid();
    $customer_query_obj->softdelete = false;
    if($customer_query_obj->save()){
     // return response()->json(["status" => "1", "message" => "You have successfully subscribed to Billboards India newsletter."]);
      return response()->json(["status" => "1", "message" => "You have successfully subscribed to Advertising Marketplace newsletter."]);
    }
    else{
      return response()->json(["status" => "0", "message" => "Sorry. We could not add you to the subscriber list. Please try again later."]);
    }
  }
  
public function userQuery(Request $request){
	 // dd($this->input);
    $this->validate($this->request, [
      // 'type' => 'user-query',
     // 'email' => 'required',
     // 'message' => 'required'
    ]);
	
    $customer_query_obj = new CustomerQuery;
    $customer_query_obj->type = 'user-query';
    $customer_query_obj->email = isset($this->input['email']) ? $this->input['email'] : "";
    $customer_query_obj->message = isset($this->input['message']) ? $this->input['message'] : "";
	 $customer_query_obj->contactno = isset($this->input['contactno']) ? $this->input['contactno'] : "";
	  $customer_query_obj->subject = isset($this->input['subject']) ? $this->input['subject'] : "";
    $customer_query_obj->name = isset($this->input['name']) ? $this->input['name'] : "";
    $customer_query_obj->companyName = isset($this->input['companyName']) ? $this->input['companyName'] : "";
    $customer_query_obj->id = uniqid();
    $customer_query_obj->softdelete = false;
    if($customer_query_obj->save()){
		  
		event(new accountSuperAdminEvent([
              'type' => Notification::$NOTIFICATION_TYPE['account-super-admin-setup'],
              'from_id' => $customer_query_obj->id,
              'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
              'to_id' => null,
              'to_client' => null,
              'desc' => "Feedback",
              'message' => $customer_query_obj->name ." Sent Feedback.",
			  'data' => ["cutomer_id" => $customer_query_obj->id]
            ]));
			$notification_obj = new Notification;
			$notification_obj->id = uniqid();
            $notification_obj->type = "user-query";
            $notification_obj->from_id =  $customer_query_obj->id;
            $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['bbi'];
            $notification_obj->to_id = null;
            $notification_obj->to_client = null;
            $notification_obj->desc = "Feedback";
            $notification_obj->message = $customer_query_obj->name ." Sent Feedback.";
			$notification_obj->cutomer_id = $customer_query_obj->id;
			$notification_obj->status = 0;
			$notification_obj->save();

		
      return response()->json(["status" => "1", "message" => "We have received your query. We'll get back to you soon."]);
    }
    else{
      return response()->json(["status" => "0", "message" => "There was an error while sending the query. Please try again later."]);
    }
  }
  
  public function customer_query($type){
    $user = CustomerQuery::where('type', '=', $type)->where('softdelete', '=', false)->orderBy('created_at', 'desc')->get();
    if(!empty($user)){
      return response()->json(['status' => 1, 'message' => "Data Found",'data'=>$user]);
    }
    else{
      return response()->json(['status' => 0, 'message' => "Data not Found."]);
    }
  }
  
  public function update_customer_data($updateID){
    $cust_query = CustomerQuery::where('id', '=', $updateID)->first();
    if(isset($this->input['softdelete'])) {
      $cust_query->softdelete = true;
    }
    if(isset($this->input['call_feedback'])){
      $cust_query->call_feedback = $this->input['call_feedback'];
    }
    if(isset($this->input['viewed']) ){
      $cust_query->viewed = $this->input['viewed'];      
    }
    if($cust_query->save()) {
      return response()->json(["status" => "1", "message" => "Data updated successfully.", "data"=>$cust_query]);
    }
    else{
      return response()->json(["status" => "0", "message" => "There was a technical error while data the payment. Please try again later."]);
    }
  }

}

 