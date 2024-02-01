<?php 

namespace App\Console\Commands;

use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Console\Command;
use App\Events\ProductExpirationEvent;
use App\Models\Product;
use App\Models\UserMongo;
use App\Models\Notification;
use Log;

class ProductsExpiration extends Command {
    
  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'ProductsExpiration:command';
  
  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = "send the email & notification of products expiration in next 7 days.";

  public function __construct(){
    parent::__construct();
  }
  
  /**
   * Execute the console command.
   *
   * @return void
   */
  public function handle()
  {
    try{
    	$exp_date = date('Y-m-d', strtotime(date('Y-m-d'). ' + 7 days'));
        $to_date = date_create($exp_date);
        $products = Product::where('to_date', '=', $to_date)->get();
        //$products = Product::get();
        
        $products_data = []; $owner_emails = []; $owner_names = [];
        foreach ($products as $key => $product) {
            $users = UserMongo::where('client_mongo_id', $product->client_mongo_id)->get();
            foreach ($users->toArray() as $k => $v) {
                $user_id = $v['client_mongo_id']; $email = $v['email']; 
                $owner_email = $v['email']; 
                $last_name = $v['last_name'] != '' ? " ".$v['last_name'] : "";
                $owner_name = ucfirst($v['first_name']).$last_name; 

                $products_data = $this->update_keypair($products_data, $user_id, $product->siteNo, $product->title);
                $owner_emails = $this->update_keypair2($owner_emails, $user_id, $owner_email);
                $owner_names = $this->update_keypair2($owner_names, $user_id, $owner_name);
            }
        }
        //print_r($owner_emails);exit();
        foreach ($products_data as $user_id => $value) {
            $pd = "";
            foreach ($value as $key1 => $value1) {
                $pd .= $value1.", ";
            }
            $prod_desc = "Your product ".rtrim($pd, ", ")." will be expiring in 7 days.";
            $owner_name = $owner_names[$user_id][0]; $owner_email = $owner_emails[$user_id][0]; 
            if ( $owner_email != '' && $product->siteNo != '' ) {
                event(new ProductExpirationEvent([
                    'type' => Notification::$NOTIFICATION_TYPE['product-expiration'],
                    'from_id' => null,
                    'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['owner'],
                    'to_id' => null,
                    'to_client' => $user_id,
                    'desc' => "Your product is about to expire",
                    'message' => $prod_desc,
                    'data' => ["id" => $user_id]
                ]));

                $notification_obj = new Notification;
                
                $notification_obj->id = uniqid();
                $notification_obj->type = "product-expiration";
                $notification_obj->from_id = null;
                $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['owner'];
                $notification_obj->to_id = $user_id;
                $notification_obj->to_client = $user_id;
                $notification_obj->desc = "Your product is about to expire";
                $notification_obj->message = $prod_desc;
                $notification_obj->status = 0;
                $notification_obj->save();

                $mail_tmpl_params = [
                    'sender_email' => config('app.bbi_email'), 
                    'receiver_name' => $owner_name,
                    'mail_message' => $prod_desc
                ]; 
                $mail_data = [
                    //'email_to' => 'pankajmulchandani80@gmail.com',
                    //'email_to' => 'pankaj.mulchandani@peopletech.com',
                    'email_to' => $owner_email,
                    'recipient_name' => $owner_name,
                    'email_to1' => 'admin@advertisingmarketplace.com',
                    //'email_to1' => 'pankajmulchandani80@gmail.com',
                    'recipient_name1' => 'Richard'
                ];
                Mail::send('mail.product_expiration', $mail_tmpl_params, function($message) use ($mail_data){
                    $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Your product is about to expire - Advertising Marketplace');
                    $message->cc($mail_data['email_to1'], $mail_data['recipient_name1'])->subject('Your product is about to expire - Advertising Marketplace');
                });

                $mail_data1 = [
                    //'email_to' => 'pankajmulchandani80@gmail.com',
                    'email_to' => 'pankaj.mulchandani@peopletech.com',
                    //'email_to' => $owner_email,
                    'recipient_name' => $owner_name,
                    //'email_to1' => 'admin@advertisingmarketplace.com',
                    'email_to1' => 'pankajmulchandani80@gmail.com',
                    'recipient_name1' => 'Richard'
                ];
                Mail::send('mail.product_expiration', $mail_tmpl_params, function($message) use ($mail_data1){
                    $message->to($mail_data1['email_to'], $mail_data1['recipient_name'])->subject('Your product is about to expire - Advertising Marketplace');
                    $message->cc($mail_data1['email_to1'], $mail_data1['recipient_name1'])->subject('Your product is about to expire - Advertising Marketplace');
                });

                // $mail_data2 = [
                //     'email_to' => 'AMPDEVTEAM@peopletech.com',
                //     'recipient_name' => $owner_name,
                //     'email_to1' => 'pankaj.mulchandani@peopletech.com',
                //     'recipient_name1' => 'Richard'
                // ];
                // Mail::send('mail.product_expiration', $mail_tmpl_params, function($message) use ($mail_data2){
                //     $message->to($mail_data2['email_to'], $mail_data2['recipient_name'])->subject('Your product is about to expire - Advertising Marketplace');
                //     $message->cc($mail_data2['email_to1'], $mail_data2['recipient_name1'])->subject('Your product is about to expire - Advertising Marketplace');
                // });
            }
        }
    }
    catch(Exception $ex){
        Log::error($ex);
    }
  }

    public function update_keypair($arr, $key, $val)
    {
       if(empty($arr[$key])) $arr[$key] = array($title." (".$siteNo.")");
       else $arr[$key][] = $title." (".$siteNo.")";
       return $arr;
    }

    public function update_keypair2($arr, $key, $val)
    {
        if ( @array_key_exists($key, $arr) ) {      
            if ( !in_array($val, $arr[$key]) ) {
                if(empty($arr[$key])) $arr[$key] = array($val);
                else $arr[$key][] = $val;
            }      
        } else {
            if(empty($arr[$key])) $arr[$key] = array($val);
            else $arr[$key][] = $val;
        }
        return $arr;
    }
}