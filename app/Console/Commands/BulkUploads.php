<?php 

namespace App\Console\Commands;

use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductBooking;
use App\Models\ProductsBulkUpload;
use App\Models\ProductsBulkImages;
use App\Models\ProductsBulkCsvFiles;
use App\Models\ClientMongo;
use App\Models\Client;
use App\Models\Area;
use App\Models\Notification;
use App\Events\BulkUploadEvent;
use Log;

class BulkUploads extends Command {
    
  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'BulkUploads:command';
  
  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = "Products Bulk Uploads";

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
        $product_csv_json_pending_path = base_path() . '/html/csv_json_uploads/pending/';
        $product_csv_json_success_path = base_path() . '/html/csv_json_uploads/success/';

        $get_json_files_data = ProductsBulkCsvFiles::where('status', 'pending')->get();
        foreach ($get_json_files_data as $key => $value) {
            $file_product_type = $value->product_type;

            $client = ClientMongo::where('id', '=', $value->client_mongo_id)->first();  
            $file_client_name = isset($client) ? $client->name : ""; 

            $json_file_name = $product_csv_json_pending_path.$value->json_file_name;
            if (file_exists($json_file_name)) {
                $json_data = json_decode(file_get_contents($json_file_name), true); 
                
                foreach ($json_data as $key => $input) {
                    $product_obj = new Product; 
                    $from_owner = ($product_obj->status == Product::$PRODUCT_STATUS['requested']);
                    $product_obj->id = uniqid();
                   
                    $client_count = Client::count();
                    $newClientID = $client_count+1;
                     
                    $product_count = Product::latest()->first();
                    $product_code_explode = explode("_", $product_count->siteNo);
                    $siteNo1 = '_'.str_pad(end($product_code_explode)+1, 6, '0', STR_PAD_LEFT); 

                    $product_type = isset($input['type']) ? $input['type'] : "";
                    $product_type1 = '_'.$product_type;  

                    if($product_type == 'Static') {
                        $product_obj->status = Product::$STATIC_PRODUCT['product-static'];
                    }
                    else if($product_type == 'Digital') {
                        $product_obj->status = Product::$DIGITAL_PRODUCT['product-digital'];
                    }
                    else if($product_type == 'Digital/Static') {
                        $product_obj->status = Product::$STATIC_DIGITAL_PRODUCT['product-digital-static'];
                    }
                    else if($product_type == 'Media') {
                        $product_obj->status = Product::$MEDIA_PRODUCT['product-media'];
                    }
                    else {
                        $product_obj->status = $product_obj->status; 
                    }   

					$user_email = ClientMongo::where('id', '=', $input['client_mongo_id'])->first();
                    $clientId = str_pad($newClientID, 6, '0', STR_PAD_LEFT);
                    $product_obj->client_id = isset($client) ? $client->client_id : "";
                    $clientId_uniqueID = $clientId.$product_obj->client_id;
                    $ASI_id = '000'.$product_obj->client_id;
                    $ASI_id1 = $clientId.$product_obj->client_id;
                    $seller_Id = isset($input['sellerId']) ? $input['sellerId'] : "";
                    $seller_Id1 = '_'.$seller_Id;

					$product_obj->bulkupload_uniqueID = $user_email['email'].'-'.date("Y-m-d h:m:s");
					//$product_obj->bulkupload_uniqueID = $input['client_mongo_id'].'-'.date("Y-m-d h:m:s");
                    $product_obj->siteNo = 'AMP'.$product_obj->status.'_ASI'.$ASI_id.$siteNo1; 
                    $product_obj->lighting = isset($input['lighting']) ? $input['lighting'] : "";
                    $product_obj->cancellation = isset($input['cancellation']) ? $input['cancellation'] : "";
                    $product_obj->type = isset($input['type']) ? $input['type'] : "";
                    $product_obj->minimumbooking = isset($input['bookingDates']) ? $input['bookingDates'] : "";
                    $product_obj->title = isset($input['title']) ? $input['title'] : "";
                    $product_obj->panelSize = (isset($input['width']) && isset($input['height'])) ? $input['height'] . '*' . $input['width'] : "";
                    $product_obj->venue = isset($input['venue']) ? $input['venue'] : "";
                    $product_obj->address = isset($input['address']) ? $input['address'] : "";
                    $product_obj->city = isset($input['city']) ? $input['city'] : "";
                    $product_obj->zipcode = isset($input['zipcode']) ? $input['zipcode'] : "";
                    $product_obj->lat = isset($input['lat']) ? $input['lat'] : "";
                    $product_obj->lng = isset($input['lng']) ? $input['lng'] : "";
                    $product_obj->direction = isset($input['direction']) ? $input['direction'] : "";

                    $product_obj->default_price = isset($input['rateCard']) ? str_replace( ',', '', $input['rateCard']) : str_replace( ',', '', $input['rateCard']);
                    $product_obj->impressions = isset($input['impressions']) ? $input['impressions'] : "";
                    $product_obj->demographicsage = isset($input['DemographicsAge']) ? $input['DemographicsAge'] : "";
                    $product_obj->strengths = isset($input['Strengths']) ? $input['Strengths'] : "";
                     
                    $product_obj->ethnicity = isset($input['ethnicity']) ? $input['ethnicity'] : "";
                    $product_obj->videoUrl = isset($input['videoUrl']) ? $input['videoUrl'] : "";
                    $product_obj->imgdirection = isset($input['imgdirection']) ? $input['imgdirection'] : "";
                    $product_obj->slots = isset($input['flips']) ? $input['flips'] : "";
                    $product_obj->hour = isset($input['loops']) ? $input['loops'] : "";
                    $product_obj->flipsloops = isset($input['flipsloops']) ? $input['flipsloops'] : "";

                    $product_obj->audited = isset($input['audited']) ? $input['audited'] : "";
                    $product_obj->cancellation_policy = isset($input['cancellation_policy']) ? $input['cancellation_policy'] : "";
					$product_obj->cancellation_terms = isset($input['cancellation_terms']) ? $input['cancellation_terms'] : "";
                    $product_obj->firstImpression = isset($input['firstImpression']) ? $input['firstImpression'] : "";
                    $product_obj->secondImpression = isset($input['secondImpression']) ? $input['secondImpression'] : "";
                    $product_obj->thirdImpression = isset($input['thirdImpression']) ? $input['thirdImpression'] : "";
                    $product_obj->forthImpression = isset($input['forthImpression']) ? $input['forthImpression'] : "";
                    $product_obj->state = isset($input['state']) ? $input['state'] : "";
                    $product_obj->height = isset($input['height']) ? $input['height'] : "";
                    $product_obj->width = isset($input['width']) ? $input['width'] : "";
                    $product_obj->vendor = isset($input['vendor']) ? $input['vendor'] : "";
                    $product_obj->sellerId = isset($input['sellerId']) ? $input['sellerId'] : "";
                    $product_obj->mediahhi = isset($input['mediahhi']) ? $input['mediahhi'] : "";
                    $product_obj->firstdayofpurchase = isset($input['firstdayofpurchase']) ? $input['firstdayofpurchase'] : "";
                    $product_obj->lastdayofpurchase = isset($input['lastdayofpurchase']) ? $input['lastdayofpurchase'] : "";
                    $product_obj->weekPeriod = isset($input['weekPeriod']) ? $input['weekPeriod'] : "";

                    $product_obj->rateCard = isset($input['rateCard']) ? str_replace( ',', '', $input['rateCard']) : str_replace( ',', '', $input['rateCard']); 
                    $product_obj->installCost = isset($input['installCost']) ? $input['installCost'] : "";
                    $product_obj->negotiatedCost = isset($input['negotiatedCost']) ? $input['negotiatedCost'] : "";
                    $product_obj->productioncost = isset($input['productioncost']) ? $input['productioncost'] : "";
                    $product_obj->notes = isset($input['notes']) ? $input['notes'] : "";
                    $product_obj->Comments = isset($input['Comments']) ? $input['Comments'] : "";
                    $product_obj->fliplength = isset($input['fliplength']) ? $input['fliplength'] : "";
                    $product_obj->looplength = isset($input['looplength']) ? $input['looplength'] : "";
                    $product_obj->locationDesc = isset($input['locationDesc']) ? $input['locationDesc'] : "";
                    $product_obj->description = isset($input['description']) ? $input['description'] : ""; 
                    $product_obj->sound = isset($input['sound']) ? $input['sound'] : "";
                    $product_obj->staticMotion = isset($input['staticMotion']) ? $input['staticMotion'] : "";
                    $product_obj->file_type = isset($input['file_type']) ? $input['file_type'] : "";
                    $product_obj->product_newAge = isset($input['product_newAge']) ? $input['product_newAge'] : "";
                    $product_obj->medium = isset($input['medium']) ? $input['medium'] : "";
                    $product_obj->firstDay = isset($input['firstDay']) ? $input['firstDay'] : "";
                    $product_obj->lastDay = isset($input['lastDay']) ? $input['lastDay'] : "";
                    $product_obj->cpm = isset($input['cpm']) ? $input['cpm'] : "";
                    $product_obj->firstcpm = isset($input['firstcpm']) ? $input['firstcpm'] : "";
                    $product_obj->thirdcpm = isset($input['thirdcpm']) ? $input['thirdcpm'] : "";
                    $product_obj->forthcpm = isset($input['forthcpm']) ? $input['forthcpm'] : "";
                    $product_obj->ageloopLength = isset($input['ageloopLength']) ? $input['ageloopLength'] : "";
                    $product_obj->product_newMedia = isset($input['product_newMedia']) ? $input['product_newMedia'] : "";
                    $product_obj->placement = isset($input['placement']) ? $input['placement'] : "";
                    $product_obj->spotLength = isset($input['spotLength']) ? $input['spotLength'] : "";
                    $product_obj->unitQty = isset($input['unitQty']) ? $input['unitQty'] : "";
                    $product_obj->billingYes = isset($input['billingYes']) ? $input['billingYes'] : "";
                    $product_obj->billingNo = isset($input['billingNo']) ? $input['billingNo'] : "";
                    $product_obj->servicingYes = isset($input['servicingYes']) ? $input['servicingYes'] : "";
                    $product_obj->servicingNo = isset($input['servicingNo']) ? $input['servicingNo'] : "";
                    $product_obj->fix = isset($input['fix']) ? $input['fix'] : "";
                    $product_obj->minimumdays = isset($input['minimumdays']) ? $input['minimumdays'] : "";
                    $product_obj->network = isset($input['network']) ? $input['network'] : "";
                    $product_obj->nationloc = isset($input['nationloc']) ? $input['nationloc'] : "";
                    $product_obj->daypart = isset($input['daypart']) ? $input['daypart'] : "";
                    $product_obj->genre = isset($input['genre']) ? $input['genre'] : "";
                    $product_obj->costperpoint = isset($input['costperpoint']) ? $input['costperpoint'] : "";
                    $product_obj->length = isset($input['length']) ? $input['length'] : "";
                    $product_obj->reach = isset($input['reach']) ? $input['reach'] : "";
                    $product_obj->daysselected = isset($input['daysselected']) ? $input['daysselected'] : "";
                    $product_obj->stripe_percent = isset($input['stripe_percent']) ? $input['stripe_percent'] : "";

                    if (isset($input['from_date']) && isset($input['to_date'])) {  
                        $product_obj->from_date = iso_to_mongo_date($input['from_date']);
                        $product_obj->to_date = iso_to_mongo_date($input['to_date']);  
                    }

                    $product_obj->buses = isset($input['buses']) ? $input['buses'] : "";
                    $area = Area::where('id', '=', $input['area'])->first();
                    $product_obj->area = isset($input['area']) ? $input['area'] : "";
                    $product_obj->country_name = isset($area) ? $area->country_name : "";
                    $product_obj->state_name = isset($area) ? $area->state_name : "";
                    $product_obj->city_name = isset($area) ? $area->city_name : "";
                    $product_obj->area_name = isset($area) ? $area->name : "";
                    $product_obj->status = Product::$PRODUCT_STATUS['approved'];
                    $product_obj->product_visibility = 1;

                    $product_obj->client_mongo_id = $input['client_mongo_id'];
                    $product_obj->client_name = $input['client_name'];
                    $product_obj->client_id = $input['client_id'];

                    //Pankaj image have to check isFile
                    $product_obj->image = "";
                    $imageArray = [];
                    if (isset($input['image']) && !empty($input['image']) && $input['image'] != '') {
                        $imageArray[] = "/uploads/images/products/" . $input['image'];
                        $product_obj->image = $imageArray;
                    }

                    // if ($request->hasFile('symbol')) {
                    //     if ($request->file('symbol')->move($product_symbol_path, $request->file('symbol')->getClientOriginalName())) {
                    //         $product_obj->symbol = "/uploads/images/symbols/" . $request->file('symbol')->getClientOriginalName();
                    //     }
                    // }
                    $symbolPath = '';
                    if(isset($input['imgdirection'])){
                        if($input['imgdirection'] == 'Left'){
                            $symbolPath = "/uploads/images/symbols/".$product_obj->type."-left.png";
                        }
                        if($input['imgdirection'] == 'Right'){
                            $symbolPath = "/uploads/images/symbols/".$product_obj->type."-right.png";
                        }
                    }
                    $product_obj->symbol = $symbolPath;

                    if ($product_obj->save()) {
                        //echo "Insert<br>";
                        // Insert data to elasticsearch :: Pankaj 24 Feb 2022    
                        // $get_data = Product::where('id', '=', $product_obj->id)->first();
                        // $this->es_etl($get_data, "insert");

                        // $get_data = Product::where('id', '=', $product_obj->id)->first();
                        // echo "<pre>";print_r($get_data);echo "</pre>";

                        if (isset($input['from_date']) && isset($input['to_date'])) { 
                            $booking = new ProductBooking;
                            $booking->product_id = $product_obj->id;
                            $booking->booked_from = iso_to_mongo_date($input['from_date']);
                            $booking->booked_to = iso_to_mongo_date($input['to_date']);
                            if(isset($input['flips'])){
                                $booking->booked_slots = $input['flips'];
                            }
                            $booking->product_owner = $product_obj->client_mongo_id;
                            $booking->product_status = ProductBooking::$PRODUCT_STATUS['scheduled'];
                            $booking->save();
                        }
                    }
                }

                $product_json1 = ProductsBulkCsvFiles::where('id', '=', $value->id)->first();
                $product_json1->status = "success"; 
                $product_json1->save();

                $success_json_file_name = $product_csv_json_success_path.$value->json_file_name;
                rename($json_file_name, $success_json_file_name);

				event(new BulkUploadEvent([
					  'type' => Notification::$NOTIFICATION_TYPE['bulk-upload'],
					  //'from_id' => $user_mongo['id'],
					  'from_id' => null,
					  'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['owner'],
					  'to_id' => $product_obj->client_mongo_id,
					  'to_client' => $product_obj->client_mongo_id,
					  'desc' => "Bulk Products Uploaded",
					  'message' => 'Bulk Upload For Type - '. $product_type . ' Execution Done Successfully. You can check data now.',
					  'data' => "Bulk Products Uploaded"
					]));
					$notification_obj = new Notification;
					$notification_obj->id = uniqid();
					$notification_obj->type = "bulk_upload";
					//$notification_obj->from_id =  $user_mongo['id'];
					$notification_obj->from_id =  null;
					$notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['owner'];
					$notification_obj->to_id = $product_obj->client_mongo_id;
					$notification_obj->to_client = $product_obj->client_mongo_id;
					$notification_obj->desc = "Bulk Products Uploaded";
					$notification_obj->message = 'Bulk Upload For Type - '. $product_type . ' Execution Done Successfully. You can check data now.';
                    $notification_obj->user_id = null;
					$notification_obj->status = 0;
                    $notification_obj->save();

                $mail_tmpl_params = [
                    'receiver_name' => $file_client_name,
                    'mail_message' => 'Bulk Upload For Type - '. $file_product_type . ' Execution Done Successfully <br> You can check data now.'           
                ];
                $mail_data = [
                    'email_to' => 'sandhyarani.manelli@peopletech.com',
                    //'email_to1' => 'pankaj.mulchandani@peopletech.com',
                    //'email_to1' => 'admin@advertisingmarketplace.com', // CC of Email
                    'recipient_name' => $file_client_name, 
                    'recipient_name1' => 'Richard'
                ];
                Mail::send('mail.bulk_upload_success', $mail_tmpl_params, function($message) use ($mail_data) {
                    $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Bulk Upload CSV File Execution Done Successfully.');
                    //$message->cc($mail_data['email_to1'], $mail_data['recipient_name1'])->subject('Bulk Upload CSV File Execution Done Successfully.');
                });
            } else {
                $mail_tmpl_params = [
                    'receiver_name' => $file_client_name,
                    'mail_message' => 'Bulk Upload For Type - '. $file_product_type . ' Having Any Issue, Something Went Wrong. <br> Please Re-Check the CSV File & Re-Upload Again.'
                ];
                $mail_data = [
                    'email_to' => 'sandhyarani.manelli@peopletech.com',
                    //'email_to1' => 'pankaj.mulchandani@peopletech.com',
                    //'email_to1' => 'admin@advertisingmarketplace.com', // CC of Email
                    'receiver_name' => $file_client_name,
                    'recipient_name1' => 'Richard'
                ];
                Mail::send('mail.bulk_upload_failure', $mail_tmpl_params, function($message) use ($mail_data) {
                    $message->to($mail_data['email_to'], $mail_data['receiver_name'])->subject('Bulk Upload CSV File Execution Failed');
                    //$message->cc($mail_data['email_to1'], $mail_data['recipient_name1'])->subject('Bulk Upload CSV File Execution Failed');
                });
            }
        }
    }
    catch(Exception $ex){
        Log::error($ex);
    }
  }

    public function update_keypair($arr, $key, $siteNo, $title)
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