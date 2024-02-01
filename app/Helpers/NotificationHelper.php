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

  class NotificationHelper {

    /*=========================================================
    | createNotification
    |
    | Desc: Creates Notifications
    | Args: type(string), from_id(uniqid), to_type(string),
    | to_id(uniqid), to_client(uniqid), link(string), 
    | desc(string), message(string)
    | Returns: true on success, false on failure
    =========================================================*/

    // $notification_data = [
    //   'type' => null,
    //   'from_id' => null,
    //   'to_type' => null,
    //   'to_id' => null,
    //   'to_client' => null,
    //   'desc' => null,
    //   'message' => null,
    //   'data' => null
    // ];

    public static function createNotification($notification_data){
      $notification_obj = new Notification;
      $notification_obj->id = uniqid();
      $notification_obj->type = $notification_data['type'];
      $notification_obj->from_id = $notification_data['from_id'];
      $notification_obj->to_type = $notification_data['to_type'];
      $notification_obj->to_id = $notification_data['to_id'];
      $notification_obj->to_client = $notification_data['to_client'];
      $notification_obj->desc =$notification_data['desc'];
      $notification_obj->message = $notification_data['message'];
      $notification_obj->status = Notification::$NOTIFICATION_STATUS['unread'];
      $notification_obj->data = $notification_data['data'];
      if($notification_obj->save()){
        return true;
      }
      else{
        return false;
      }
    }
  }