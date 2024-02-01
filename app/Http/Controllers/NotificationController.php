<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Notification;
use App\Models\Campaign;
use JWTAuth;
use Auth;
use Entrust;
use Log;

class NotificationController extends Controller {

    const MAX = 50;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        // Resolve dependencies out of container
        // $this->middleware('jwt.auth', ['only' => [
        //   'getAllNotifications',
        // 	'getAllAdminNotifications'
        // ]]);
        // $this->middleware('role:admin|owner', ['only' => [
        //   'getAllAdminNotifications'
        // ]]);
    }

    public function getAllNotifications($last_notif_timestamp) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        while (1) {
            $notifications = Notification::where([
                        ['to_id', '=', $user_mongo['id']],
                        ['to_type', '=', Notification::$NOTIFICATION_CLIENT_TYPE['user']],
                        ['status', '=', Notification::$NOTIFICATION_STATUS['unread']],
                        ['updated_at', '>', new \MongoDB\BSON\UTCDateTime($last_notif_timestamp + 1000)]
                            // Added 1000 milliseconds, because date precision is lost when coming from client side.
                            // instead of "2018-03-22T11:07:56.333Z" for example, we get "2018-03-22T11:07:56"
                    ])->orderBy('updated_at', 'desc')->limit(self::MAX);
            if (!$notifications->count()) {
                sleep(10);
            } else {
                break;
            }
        }
        return response()->json($notifications->get());
    }

    public function getAllAdminNotifications($last_notif_timestamp) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        while (1) {
            $admin_notifications = Notification::where([
                        ['to_type', '=', Notification::$NOTIFICATION_CLIENT_TYPE['bbi']],
                        ['updated_at', '>', new \MongoDB\BSON\UTCDateTime($last_notif_timestamp + 1000)],
                        ['status', '=', Notification::$NOTIFICATION_STATUS['unread']]
                            // Added 1000 milliseconds, because date precision is lost when coming from client side.
                            // instead of "2018-03-22T11:07:56.333Z" for example, we get "2018-03-22T11:07:56"
                    ])->orderBy('updated_at', 'desc')->limit(self::MAX);
            if (!$admin_notifications->count()) {
                sleep(10);
            } else {
                break;
            }
        }
        return response()->json($admin_notifications->get());
    }

    public function changeNotificationStatusToRead($notification_id) {
        $notification = Notification::where('id', '=', $notification_id)->first();
        $notification->status = Notification::$NOTIFICATION_STATUS['read'];
        if ($notification->save()) {
            return response()->json(["status" => "1", "message" => "Notification read."]);
        } else {
            return response()->json(["status" => "0", "message" => "Technical error updating the status of notification."]);
        }
    }

    public function getAllOwnerNotifications($last_notif_timestamp) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        while (1) {
            $owner_notifications = Notification::where([
                        ['to_type', '=', Notification::$NOTIFICATION_CLIENT_TYPE['owner']],
                        ['to_client', '=', $user_mongo['client_mongo_id']],
                        ['updated_at', '>', new \MongoDB\BSON\UTCDateTime($last_notif_timestamp + 1000)],
                        ['status', '=', Notification::$NOTIFICATION_STATUS['unread']]
                            // Added 1000 milliseconds, because date precision is lost when coming from client side.
                            // instead of "2018-03-22T11:07:56.333Z" for example, we get "2018-03-22T11:07:56"
                    ])->orderBy('updated_at', 'desc')->limit(self::MAX);
            if (!$owner_notifications->count()) {
                sleep(10);
            } else {
                break;
            }
        }
        return response()->json($owner_notifications->get());
    }

    public function getNotifications() {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo['user_id'])->first();
		$notification_list='';
		$saved_campaigns_count = 0;
        if ($user_mongo['user_type'] == 'bbi') {
            $notification_list = Notification::where('to_type', '=', Notification::$NOTIFICATION_CLIENT_TYPE['bbi'])->orderBy('created_at', 'desc')->get();
			$notification_count = Notification::where('to_type', '=', Notification::$NOTIFICATION_CLIENT_TYPE['bbi'])->where('status','=', 0)->get();
        }
        if ($user_mongo['user_type'] == 'basic') {
            $notification_list = Notification::where('to_type', '=', Notification::$NOTIFICATION_CLIENT_TYPE['user'])
                            ->where('to_id', '=', $user_mongo['id'])
                            ->orderBy('created_at', 'desc')->get();
			$notification_count = Notification::where('to_type', '=', Notification::$NOTIFICATION_CLIENT_TYPE['user'])
									->where('to_id', '=', $user_mongo['id'])
									->where('status','=', 0)->get();	
			$saved_campaigns_count = Campaign::where('status', '=', Campaign::$CAMPAIGN_STATUS['campaign-preparing'])->orWhere('status', '=', Campaign::$CAMPAIGN_STATUS['rfp-campaign'])->where('created_by', '=', $user_mongo['id'])->count();							
        }
        if ($user_mongo['user_type'] == 'owner') {
            $notification_list = Notification::where('to_type', '=', Notification::$NOTIFICATION_CLIENT_TYPE['owner'])
                            ->where('to_id', '=', $user_mongo['client_mongo_id'])
                            ->orderBy('created_at', 'desc')->get();
							
			$notification_count = Notification::where('to_type', '=', Notification::$NOTIFICATION_CLIENT_TYPE['owner'])
									->where('to_id', '=', $user_mongo['client_mongo_id'])
									->where('status','=',0)->get();	
        }
        return response()->json(["status" => "1", "notifications" => $notification_list,"notification_count"=>count($notification_count),"saved_campaigns_count"=>$saved_campaigns_count]);
    }
	
	public function updateNotificationstatus($notificationId){
		  $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo['user_id'])->first();
		$notification_Array = Notification::where('id', '=',$notificationId)->first();
		//dd($notification_Array['type']);
		if(!empty($notification_Array)){
		//if($notification_Array['type'] != 'campaign' ){
			$notification_Array->status=1;
			$notification_Array->save();
		//}
		/*else{
		 if ($user_mongo['user_type'] == 'bbi') {
           		   
        }
        if ($user_mongo['user_type'] == 'basic') {
           				
        }
        if ($user_mongo['user_type'] == 'owner') {
            
        }
		}*/
        return response()->json(["status" => "1", "message" => "Notification status Chnaged Sucessfully"]);
		}
	}
	
	public function updateNotificationsStatus(Request $request)
	{
		$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo['user_id'])->first();
		
		if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
		$notification_id = $input['notification_ids'];
		foreach($notification_id as $notification_id)
		{
			$notification_Array = Notification::where('id', '=',$notification_id)->first();
			if(!empty($notification_Array)){
				$notification_Array->status=1;
				$notification_Array->save();
			}
		}
		return response()->json(["status" => "1", "message" => "Notification status Changed Successfully"]);
	}

}
