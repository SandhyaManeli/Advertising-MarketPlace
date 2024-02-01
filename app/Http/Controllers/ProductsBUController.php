<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Mail;
use App\Models\Product;
use App\Models\ProductsBulkUpload;
use App\Models\ProductsBulkImages;
use App\Models\ProductsBulkCsvFiles;
use App\Models\Campaign;
use App\Models\Format;
use App\Models\Area;
use App\Models\Client;
use App\Models\ClientMongo;
use App\Models\ShortListedProduct;
use App\Models\User;
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
use App\Models\ProductExport;
use JWTAuth;
use Auth;
use Entrust;
use PDF;
use App\Jobs\UpdateProductEverywhere;
use Log;
use App\Events\ProductApprovedEvent;
use App\Events\ProductRequestedEvent;
use App\Events\CampaignLaunchRequestedEvent;
use App\Events\ShortlistedProductSoldOutEvent;
use App\Events\BulkUploadEvent;
use DB;
use File;

class ProductsBUController extends Controller {

    public function validateLatitude($lat) {
      return preg_match('/^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/', $lat);
    }

    public function validateLongitude($long) {
      return preg_match('/^(\+|-)?(?:180(?:(?:\.0{1,6})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,6})?))$/', $long);
    }

    public function getAllSellers() {
        $sellers = UserMongo::where('seller_id', '!=', '')->where('company_type', '!=', '')->orderBy('updated_at', 'desc')->get();  
		//echo 'pre';print_r($sellers);exit;
        $sellers_data = [];
        foreach ($sellers as $key => $value) {
            $name = $value->last_name != '' ? $value->first_name." ".$value->last_name : $value->first_name;
            $seller_id = $value->id;
            $seller_type= $value->company_type;
            $sellers_data[] = array("seller_id"=>$seller_id,"name"=>$name,"seller_type"=>$seller_type);
        }
        $sellers_data_list = ["sellers_data" => $sellers_data];
        return response()->json($sellers_data_list);
    }
    
    public function import(Request $request) {
        /*ProductsBulkCsvFiles::where([
            ['id', '!=', 1]
        ])->delete();
        exit();*/

        /*ProductsBulkUpload::where([
            ['id', '!=', 1]
        ])->delete();
        exit();*/

        /*$title_array = array('QA Bulk Product 1001', 'QA Bulk Product 1002', 'QA Bulk Product 1003', 'QA Bulk Product 1004', 'QA Bulk Product 1005');
        $get_data = Product::whereIn('title', $title_array)->get();
        return response()->json(["status" => "2", "message" => $get_data]);*/

        /*$product_json2 = ProductsBulkCsvFiles::get();
        foreach ($product_json2 as $key => $value) {
            echo $value->id."_____".$value->status."<br>";
        }exit();*/

        $input = $request->all();
		//echo '<pre>';print_r($input);exit;
        $image = 'no-image.png';
        
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];

        $um = UserMongo::where('id', '=', $user_mongo['id'])->first();
        $user_type = $um->company_type;
        if ( $user_type == 'bbi' ) {
            if (isset($input['seller_id'])) {
                if ($input['seller_id'] != '') {
                    $seller_check = UserMongo::where('id', '=', $input['seller_id'])->where('company_type', '=', 'owner')->first();
                    if ( count($seller_check) == 1 ) {
                        $client = ClientMongo::where('id', '=', $seller_check->client_mongo_id)->first(); 
                        $user = UserMongo::where('id', '=', $seller_check->id)->first();						
                    } else {
                        $errors[] = "Seller Id Required For Admin account.";
                        return response()->json(["status" => "0", "message" => $errors], 400);
                    }
                } else {
                    $errors[] = "Seller Id Required For Admin account.";
                    return response()->json(["status" => "0", "message" => $errors], 400);
                }
            } else {
                $errors[] = "Seller Id Required For Admin account.";
                return response()->json(["status" => "0", "message" => $errors], 400);
            }    
        } else {
            $client = ClientMongo::where('id', '=', $user_mongo['client_mongo_id'])->first(); 
            $user = UserMongo::where('id', '=', $user_mongo['id'])->first();
        }
        
        $errors = [];
        $errors_pdf = [];
        if (isset($input['type'])) {
            if ($input['type'] != '') {
                if ( !(in_array($input['type'], array('Static', 'Digital', 'Digital/Static', 'Media'))) ) {
                    $errors[] = "Type Is Invalid Value, The value must be from (Static, Digital, Digital/Static, Media) For Any Records From CSV.";
                    $errors_pdf[] = "Type Is Invalid Value, The value must be from (Static, Digital, Digital/Static, Media) For Any Records From CSV.";
                    return response()->json(["status" => "0", "message" => $errors], 400);
                } 
            } else {
                $errors[] = "Type Is Required For CSV.";
                $errors_pdf[] = "Type Is Required For CSV.";
                return response()->json(["status" => "0", "message" => $errors], 400);
            }
        } else {
            $errors[] = "Type Is Required For CSV.";
            $errors_pdf[] = "Type Is Required For CSV.";
            return response()->json(["status" => "0", "message" => $errors], 400);
        }
        $product_type = $input['type'];

        $file_type_check = gettype($request->file('file'));
        if ($file_type_check == 'object') {
            $files_array[] = $request->file('file');
        } else if ($file_type_check == 'array') {
            $files_array = $request->file('file');
        } else {
            $errors[] = "Incorrect File.";
            $errors_pdf[] = "Incorrect File.";
            return response()->json(["status" => "0", "message" => $errors], 400);
        }

        if ( count($files_array) != 1 ) {
            $errors[] = "Only One CSV File Is Required.";
            $errors_pdf[] = "Only One CSV File Is Required.";
                return response()->json(["status" => "0", "message" => $errors], 400);
        }

        if ($request->hasFile('file')) {
        
            $val = $files_array[0];                
            $ext = $val->getClientOriginalExtension();
            if ( strtolower($ext) != 'csv' ) {
                $errors[] = "File Type Is Invalid! File Type Must Be csv Only.";
                $errors_pdf[] = "File Type Is Invalid! File Type Must Be csv Only.";
                return response()->json(["status" => "0", "message" => $errors], 400);
            }

            $path = $val->getRealPath();
            $open = fopen($path, "r");

            $headersCount = 0;
            $headers=[]; $final_data=[]; $final_data_success = [];
            $rn = 1;$sn = 1;    

            $client_mongo_id = isset($client) ? $client->id : "";
            $client_name = isset($client) ? $client->name : "";
            $client_id = isset($client) ? $client->client_id : "";
			if(isset($client->contact_email)){
				$client_email = isset($client) ? $client->contact_email : ""; 
			}else if(isset($client->email)){
				$client_email = isset($client) ? $client->email : "";
			}else{
				$client_email = "";
			}
            $vendor = $client_name;

            while (($data = fgetcsv($open, 1000, ",")) !== FALSE) {
                $fd = array_filter($data);
                if ( $product_type == "Static" ) {
                    $headersCount = 40;
                } else if ( $product_type == "Digital" ) {
                    $headersCount = 41;                         
                } else if ( $product_type == "Digital/Static" ) {
                    $headersCount = 41;                         
                } else if ( $product_type == "Media" ) {
                    $headersCount = 47;                         
                }
                if ( $headersCount == 0 ) {
                    $errors[] = "Invalid Headers or Method.";
                    $errors_pdf[] = "Invalid Headers or Method.";
                    return response()->json(["status" => "0", "message" => $errors], 400);
                }   
                //var_dump(count($data));
                if (count($data) != $headersCount) {
                    $errors[] = "No Records In Given File";
                    $errors_pdf[] = "No Records In Given File";
                    return response()->json(["status" => "0", "message" => $errors], 400);
                }             
                if ( count($fd) > 0 && !empty($fd) ) {
                    if ( $rn == 1 ) {                    
                        $headers[] = $fd;
                        if (count($headers[0]) != $headersCount) {
                            $errors[] = "Headers are mismatch as per the product type. For $product_type method the headers count must be $headersCount";
                            $errors_pdf[] = "Headers are mismatch as per the product type. For $product_type method the headers count must be $headersCount";
                            return response()->json(["status" => "0", "message" => $errors], 400);
                        }
                    } else {
						
						//  Duplication check //
						
						//echo '<pre>';print_r($product_duplicate);exit;
						$valid_data_status = 0;
                        $area = '';
                        $search_term = '';
                        $title = '';
                        $height = '';
                        $width = '';
                        $height_width_parameter = '';
                        $street = '';
                        $city = '';
                        $state = '';
                        $zip = '';
                        $lat = '';
                        $lng = '';
                        $unitqty = '';
                        $minimumdays = '';
                        $locationDesc = '';
                        $imgdirection = '';
                        $direction = '';
                        $lighting = '';
                        $length = '';
                        $spotlength = '';
                        $fliplength = '';
                        //$looplength = '';
                        $ageloopLength = '';
                        $date1 = ''; $date2 = '';
                        $rateCard = '';
                        $negotiatedCost = '';
                        $symbolPath = "";
                        $impression1 = ''; $impression2 = ''; $impression3 = ''; $impression4 = '';
                        $cpm1 = ''; $cpm2 = ''; $cpm3 = ''; $cpm4 = '';
                        $install_cost = 0;
                        $productioncost = 0;
                        $audited = '';
                        $sellerId = '';
                        $cancellation_policy = '';
                        $cancellation_terms = '';
                        $mediahhi = '';
                        $notes = '';
                        $comments = '';
                        $description = '';
                        $stripe_percent = 5;
                        $fix = '';
                        $billing = ''; $billingYes = ''; $billingNo = '';
                        $servicing = ''; $servicingYes = ''; $servicingNo = '';
                        $network = '';
                        $nationloc = '';
                        $placement = '';
                        $genre = '';
                        $daypart = '';
                        $file_type = '';
                        $staticMotion = '';
                        $sound = '';
                        $medium = '';
                        $product_newMedia = '';
                        $tax_percentage = 0;
                        $amp_id = '-';
						
                        if ( $product_type == "Static" ) {
                            if (isset($fd[0])) {
                                $amp_id = strtolower($fd[0]);
                            }
                            if (isset($fd[1])) {
                                $search_term = strtolower($fd[1]);
                            }
                            if(isset($fd[2])) {
                                $title = $fd[2];
                            }
                            if(isset($fd[3])) {
                                $height = $fd[3];
                            }
                            if(isset($fd[4])) {
                                $width = $fd[4];
                            } 
                            if(isset($fd[5])) {
                                $height_width_parameter = $fd[5];
                            } 
                            if(isset($fd[8])) {
                                $street = $fd[8];
                            } 
                            if(isset($fd[9])) {
                                $city = $fd[9];
                            } 
                            if(isset($fd[10])) {
                                $state = $fd[10];
                            } 
                            if(isset($fd[11])) {
                                $zip = $fd[11];
                            } 
                            if(isset($fd[12])) {
                                $lat = $fd[12];
                            } 
                            if(isset($fd[13])) {
                                $lng = $fd[13];
                            } 
                            if(isset($fd[16])) {
                                $unitqty = $fd[16];
                            } 
                            if(isset($fd[17])) {
                                $minimumdays = $fd[17];
                            } 
                            if(isset($fd[20])) {
                                $locationDesc = $fd[20];
                            } 
                            if(isset($fd[21])) {
                                $imgdirection = $fd[21];
                            } 
                            if(isset($fd[22])) {
                                $direction = $fd[22];
                            } 
                            if(isset($fd[23])) {
                                $lighting = ucfirst($fd[23]);
                            } 
                            if(isset($fd[25])) {
                                $date1 = $fd[25]; 
                            }
                            if(isset($fd[26])) {
                                $date2 = $fd[26]; 
                            }
                            if(isset($fd[27])) {
                                $rateCard = $fd[27];
                            } 
                            if(isset($fd[28])) {
                                $negotiatedCost = $fd[28];
                            } 
                            if ( isset($fd[29]) ) {
                                $impression1 = $fd[29]; 
                            }
                            if ( isset($fd[30]) ) {
                                $impression2 = $fd[30];                         
                            }
                            if ( isset($fd[31]) ) {
                                $impression3 = $fd[31]; 
                            }
                            if ( isset($fd[32]) ) {
                                $impression4 = $fd[32]; 
                            }
                            if(isset($fd[35])) {
                                $install_cost = $fd[35];
                            }
                            if(isset($fd[36])) {
                                $productioncost = $fd[36];
                            }
                            if(isset($fd[14])) {
                                $audited = ucfirst($fd[14]);
                            }                    
                            if(isset($fd[15])) {
                                $sellerId = $fd[15];
                            }                    
                            if(isset($fd[33])) {
                                $cancellation_policy = $fd[33];
                            }
							if(isset($fd[34])) {
                                $cancellation_terms = $fd[34];
                            }
                            if(isset($fd[24])) {
                                $mediahhi = $fd[24];
                            }                    
                            if(isset($fd[37])) {
                                $notes = $fd[37];
                            }                    
                            if(isset($fd[38])) {
                                $comments = $fd[38];
                            }
							if(isset($fd[39])) {
                                $tax_percentage = $fd[39];
                            }                    
                            if(isset($fd[6])) {
                                $description = $fd[6];
                            }                    
                            if(isset($fd[7])) {
                                $fix = ucfirst($fd[7]);
                            }
                            if(isset($fd[18])) {
                                $billing = strtolower($fd[18]);
                            }
                            if(isset($fd[19])) {
                                $servicing = strtolower($fd[19]);
                            }

                            if(preg_match('/^\d+$/',$amp_id)) {
                            } else {
                                $amp_id = $sn;
                                $errors[] = "Record $amp_id - "."AMP assigned Inventory ID Must Be Valid Number";
                                $errors_pdf[$amp_id] = $title;
                            }

                            if ($lighting != '') {
                                if ( !(in_array($lighting, array('Yes', 'No'))) ) {
                                    //$errors[] = "Record $amp_id - "."Light must be Yes or No";
                                    $errors[] = "Record $amp_id - "."Illumination must be Yes or No";
                                    $errors_pdf[$amp_id] = $title;
									$valid_data_status = 1;
                                } 
                            }
							else {
                                //$errors[] = "Record $amp_id - "."Light is required";
                                $errors[] = "Record $amp_id - "."Illumination is required";  
                                $errors_pdf[$amp_id] = $title; 
								$valid_data_status = 1;
                            }
							if ($cancellation_policy == '') {
                                $errors[] = "Record $amp_id - "."Cancellation Policy is Required";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }
							if ($cancellation_terms == '') {
                                $errors[] = "Record $amp_id - "."Payment Terms is Required";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }
							

                        } else if ( $product_type == "Digital" OR $product_type == "Digital/Static" ) {
                            if (isset($fd[0])) {
                                $amp_id = strtolower($fd[0]);
                            }
                            if (isset($fd[1])) {
                                $search_term = strtolower($fd[1]);
                            }
                            if(isset($fd[2])) {
                                $title = $fd[2];
                            }
                            if(isset($fd[3])) {
                                $height = $fd[3];
                            }
                            if(isset($fd[4])) {
                                $width = $fd[4];
                            } 
                            if(isset($fd[5])) {
                                $height_width_parameter = $fd[5];
                            }
                            if(isset($fd[8])) {
                                $street = $fd[8];
                            } 
                            if(isset($fd[9])) {
                                $city = $fd[9];
                            } 
                            if(isset($fd[10])) {
                                $state = $fd[10];
                            } 
                            if(isset($fd[11])) {
                                $zip = $fd[11];
                            } 
                            if(isset($fd[12])) {
                                $lat = $fd[12];
                            } 
                            if(isset($fd[13])) {
                                $lng = $fd[13];
                            } 
                            if(isset($fd[16])) {
                                $unitqty = $fd[16];
                            } 
                            if(isset($fd[17])) {
                                $minimumdays = $fd[17];
                            } 
                            if(isset($fd[20])) {
                                $locationDesc = $fd[20];
                            } 
                            if(isset($fd[21])) {
                                $imgdirection = $fd[21];
                            } 
                            if(isset($fd[22])) {
                                $direction = $fd[22];
                            } 
                            if(isset($fd[26])) {
                                $date1 = $fd[26]; 
                            }
                            if(isset($fd[27])) {
                                $date2 = $fd[27]; 
                            }
                            if(isset($fd[28])) {
                                $rateCard = $fd[28];
                            } 
                            if(isset($fd[29])) {
                                $negotiatedCost = $fd[29];
                            } 
                            if ( isset($fd[30]) ) {
                                $impression1 = $fd[30]; 
                            }
                            if ( isset($fd[31]) ) {
                                $impression2 = $fd[31];                         
                            }
                            if ( isset($fd[32]) ) {
                                $impression3 = $fd[32]; 
                            }
                            if ( isset($fd[33]) ) {
                                $impression4 = $fd[33]; 
                            }
                            if(isset($fd[36])) {
                                $install_cost = $fd[36];
                            }
                            if(isset($fd[37])) {
                                $productioncost = $fd[37];
                            }
                            if(isset($fd[14])) {
                                $audited = ucfirst($fd[14]);
                            }                    
                            if(isset($fd[15])) {
                                $sellerId = $fd[15];
                            }                    
                            if(isset($fd[34])) {
                                $cancellation_policy = $fd[34];
                            }
							if(isset($fd[35])) {
                                $cancellation_terms = $fd[35];
                            }
                            if(isset($fd[25])) {
                                $mediahhi = $fd[25];
                            }                    
                            if(isset($fd[38])) {
                                $notes = $fd[38];
                            }                    
                            if(isset($fd[39])) {
                                $comments = $fd[39];
                            }
							if(isset($fd[40])) {
                                $tax_percentage = $fd[40];
                            }                    
                            if(isset($fd[6])) {
                                $description = $fd[6];
                            }                    
                            if(isset($fd[7])) {
                                $fix = ucfirst($fd[7]);
                            }
                            if(isset($fd[18])) {
                                $billing = strtolower($fd[18]);
                            }
                            if(isset($fd[19])) {
                                $servicing = strtolower($fd[19]);
                            }

                            /*if(isset($fd[24])) {
                                $looplength = $fd[24];
                            } else {
                                $looplength = '';
                            }*/
							if(isset($fd[24])) {
                                $ageloopLength = $fd[24];
                            } else {
                                $ageloopLength = '';
                            }

                            if(preg_match('/^\d+$/',$amp_id)) {
                            } else {
                                $amp_id = $sn;
                                $errors[] = "Record $amp_id - "."AMP assigned Inventory ID Must Be Valid Number";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }
                            
                            if ($product_type == "Digital") {
                                $fliplength = '';
                                if(isset($fd[23])) {
                                    $spotlength = $fd[23];
                                } else {
                                    $spotlength = '';
                                }

                                if(preg_match('/^\d+$/',$spotlength)) {
                                } else {
                                    $errors[] = "Record $amp_id - "."Spot Length Must Be Valid Number";
                                    $errors_pdf[$amp_id] = $title;
									$valid_data_status = 1;
                                }
                            } 

                            if ($product_type == "Digital/Static") {
                                $spotlength = '';
                                if(isset($fd[23])) {
                                    $fliplength = $fd[23];
                                } else {
                                    $fliplength = '';
                                }
                                
                                if(preg_match('/^\d+$/',$fliplength)) {
                                } else {
                                    $errors[] = "Record $amp_id - "."Flip Length Must Be Valid Number";
                                    $errors_pdf[$amp_id] = $title;
									$valid_data_status = 1;
                                }
                            }

                            /*if(preg_match('/^\d+$/',$looplength)) {
                            } else {
                                $errors[] = "Record $amp_id - "."Loop Length Must Be Valid Number";
                            }*/
							if(preg_match('/^\d+$/',$ageloopLength)) {
                            } else {
                                $errors[] = "Record $amp_id - "."Loop Length Must Be Valid Number";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }
							if ($cancellation_policy == '') {
                                $errors[] = "Record $amp_id - "."Cancellation Policy is Required";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }
							if ($cancellation_terms == '') {
                                $errors[] = "Record $amp_id - "."Payment Terms is Required";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }
                                            
                        } else if ( $product_type == "Media" ) {
                            if (isset($fd[0])) {
                                $amp_id = strtolower($fd[0]);
                            }
                            if (isset($fd[1])) {
                                $search_term = strtolower($fd[1]);
                            }
                            if(isset($fd[2])) {
                                $title = $fd[2];
                            }
                            if(isset($fd[3])) {
                                $length = $fd[3];
                            }
                            if(isset($fd[6])) {
                                $street = $fd[6];
                            } 
                            if(isset($fd[7])) {
                                $city = $fd[7];
                            } 
                            if(isset($fd[8])) {
                                $state = $fd[8];
                            } 
                            if(isset($fd[9])) {
                                $zip = $fd[9];
                            } 
                            if(isset($fd[10])) {
                                $lat = $fd[10];
                            } 
                            if(isset($fd[11])) {
                                $lng = $fd[11];
                            } 
                            if(isset($fd[14])) {
                                $unitqty = $fd[14];
                            } 
                            if(isset($fd[15])) {
                                $minimumdays = $fd[15];
                            } 
                            if(isset($fd[18])) {
                                $locationDesc = $fd[18];
                            } 
                            if(isset($fd[19])) {
                                $imgdirection = $fd[19];
                            } 
                            if(isset($fd[33])) {
                                $date1 = $fd[33]; 
                            }
                            if(isset($fd[34])) {
                                $date2 = $fd[34]; 
                            }
                            if(isset($fd[35])) {
                                $rateCard = $fd[35];
                            } 
                            if(isset($fd[36])) {
                                $negotiatedCost = $fd[36];
                            } 
                            if ( isset($fd[37]) ) {
                                $impression1 = $fd[37]; 
                            }
                            if ( isset($fd[38]) ) {
                                $impression2 = $fd[38];                         
                            }
                            if ( isset($fd[39]) ) {
                                $impression3 = $fd[39]; 
                            }
                            if ( isset($fd[40]) ) {
                                $impression4 = $fd[40]; 
                            }
                            if(isset($fd[43])) {
                                $install_cost = $fd[43];
                            }
                            if(isset($fd[44])) {
                                $productioncost = $fd[44];
                            }
                            if(isset($fd[12])) {
                                $audited = ucfirst($fd[12]);
                            }                    
                            if(isset($fd[13])) {
                                $sellerId = $fd[13];
                            }                    
                            if(isset($fd[41])) {
                                $cancellation_policy = $fd[41];
                            }
							if(isset($fd[42])) {
                                $cancellation_terms = $fd[42];
                            }
                            if(isset($fd[32])) {
                                $mediahhi = $fd[32];
                            }                    
                            if(isset($fd[45])) {
                                $notes = $fd[45];
                            } 
							if(isset($fd[46])) {
                                $tax_percentage = $fd[46];
                            }                   
                            if(isset($fd[4])) {
                                $description = $fd[4];
                            }                    
                            if(isset($fd[5])) {
                                $fix = ucfirst($fd[5]);
                            }
                            if(isset($fd[16])) {
                                $billing = strtolower($fd[16]);
                            }
                            if(isset($fd[17])) {
                                $servicing = strtolower($fd[17]);
                            }

                            if(isset($fd[20])) {
                                $network = ucfirst($fd[20]);
                            }
                            if(isset($fd[21])) {
                                $nationloc = ucfirst($fd[21]);
                            }
                            if(isset($fd[22])) {
                                $placement = $fd[22];
                            }
                            if(isset($fd[23])) {
                                $genre = $fd[23];
                            }
                            if(isset($fd[24])) {
                                $daypart = $fd[24];
                            }
                            if(isset($fd[25])) {
                                $file_type = $fd[25];
                            }
                            if(isset($fd[28])) {
                                $staticMotion = ucfirst($fd[28]);
                            }
                            if(isset($fd[29])) {
                                $sound = ucfirst($fd[29]);
                            }
                            if(isset($fd[30])) {
                                $medium = $fd[30];
                            }
                            if(isset($fd[31])) {
                                $product_newMedia = $fd[31];
                            }

                            if(isset($fd[26])) {
                                $spotlength = $fd[26];
                            } else {
                                $spotlength = '';
                            }
                            
                            /*if(isset($fd[27])) {
                                $looplength = $fd[27];
                            } else {
                                $looplength = '';
                            }*/
							if(isset($fd[27])) {
                                $ageloopLength = $fd[27];
                            } else {
                                $ageloopLength = '';
                            }

                            if(preg_match('/^\d+$/',$amp_id)) {
                            } else {
                                $amp_id = $sn;
                                $errors[] = "Record $amp_id - "."AMP assigned Inventory ID Must Be Valid Number";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }

                            if(preg_match('/^\d+$/',$length)) {
                            } else {
                                $errors[] = "Record $amp_id - "."Length Must Be Valid Number";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }

                            if ($network != '') {
                                if ( !(in_array($network, array('Yes', 'No'))) ) {
                                    $errors[] = "Record $amp_id - "."Network must be from (Yes, No)";
                                    $errors_pdf[$amp_id] = $title;
									$valid_data_status = 1;
                                } 
                            } else {
                                $errors[] = "Record $amp_id - "."Network Is Required";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }

                            if ($nationloc != '') {
                                if ( !(in_array($nationloc, array('National', 'Local'))) ) {
                                    $errors[] = "Record $amp_id - "."National/Local must be from (National, Local)";
                                    $errors_pdf[$amp_id] = $title;
									$valid_data_status = 1;
                                } 
                            } else {
                                $errors[] = "Record $amp_id - "."National/Local Is Required";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }

                            if ($placement == '') {
                                $errors[] = "Record $amp_id - "."Placement Is Required";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }

                            if ($genre == '') {
                                $errors[] = "Record $amp_id - "."Genre Is Required";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }

                            if ($daypart == '') {
                                $errors[] = "Record $amp_id - "."Day Part Is Required";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }

                            if ($file_type != '') {
                                if ( !(in_array($file_type, array('MP3', 'MP4', 'Jpeg'))) ) {
                                    $errors[] = "Record $amp_id - "."File type must be from (MP3, MP4, Jpeg)";
                                    $errors_pdf[$amp_id] = $title;
									$valid_data_status = 1;
                                } 
                            } else {
                                $errors[] = "Record $amp_id - "."File type Is Required";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }

                            $fliplength = '';
                            if(preg_match('/^\d+$/',$spotlength)) {
                            } else {
                                $errors[] = "Record $amp_id - "."Spot Length Must Be Valid Number";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }
							if(preg_match('/^\d+$/',$ageloopLength)) {
                            } else {
                                $errors[] = "Record $amp_id - "."Break / Loop Length Must Be Valid Number";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }

                            if ($staticMotion != '') {
                                if ( !(in_array($staticMotion, array('Static', 'Motion'))) ) {
                                    $errors[] = "Record $amp_id - "."Static or Motion must be from (Static, Motion)";
                                    $errors_pdf[$amp_id] = $title;
									$valid_data_status = 1;
                                } 
                            } else {
                                $errors[] = "Record $amp_id - "."Static or Motion Is Required";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }

                            if ($sound != '') {
                                if ( !(in_array($sound, array('Yes', 'No'))) ) {
                                    $errors[] = "Record $amp_id - "."Sound must be from (Yes, No)";
                                    $errors_pdf[$amp_id] = $title;
									$valid_data_status = 1;
                                } 
                            } else {
                                $errors[] = "Record $amp_id - "."Sound Is Required";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }

                            if ($medium == '') {
                                $errors[] = "Record $amp_id - "."Medium Is Required";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }

                            if ($product_newMedia == '') {
                                $errors[] = "Record $amp_id - "."Product Is Required";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }
							if ($cancellation_policy == '') {
                                $errors[] = "Record $amp_id - "."Cancellation Policy is Required";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }
							if ($cancellation_terms == '') {
                                $errors[] = "Record $amp_id - "."Payment Terms is Required";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }
                        }
                        if ($search_term != '') {
                            $areas = Area::where('name', 'like', "%$search_term%")
                                ->orWhere('country_name', 'like', "%$search_term%")
                                ->orWhere('state_name', 'like', "%$search_term%")
                                ->orWhere('city_name', 'like', "%$search_term%")
                                ->orWhere('pincode', 'like', "%$search_term%")
                                ->get();
                            $area = $areas[0]->id;
                            if ($area == '') {
                                $errors[] = "Record $amp_id - "."DMA Must Be Valid";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }
                        } else {
                            $errors[] = "Record $amp_id - "."DMA Is Required";
                            $errors_pdf[$amp_id] = $title;
							$valid_data_status = 1;
                        }

                        if ($title == '') {
                            $errors[] = "Record $amp_id - "."Product Title Is Required";
                            $errors_pdf[$amp_id] = $title;
							$valid_data_status = 1;
                        }

                        if ( $product_type != "Media" ) {
                            if(preg_match('/^\d+$/',$height)) {
                            } else {
                                $errors[] = "Record $amp_id - "."Height Must Be Valid Number";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }

                            if(preg_match('/^\d+$/',$width)) {
                            } else {
                                $errors[] = "Record $amp_id - "."Width Must Be Valid Number";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }

                            if ( !(in_array($height_width_parameter, array('pixels', 'feet', 'Line Inches'))) ) {
                                $errors[] = "Record $amp_id - "."* Height / Width Parameter Is Missing OR Invalid Value, The value must be from (pixels, feet, Line Inches)";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            } 

                            $height .= ' '.$height_width_parameter;
                            $width .= ' '.$height_width_parameter;

                            if ( !(in_array($direction, array('East', 'West', 'North', 'South'))) ) { 
                                $errors[] = "Record $amp_id - "."Facing Is Missing OR Invalid Value, The value must be from (East, West, North, South)";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }

                            if ( $height_width_parameter == 'Line Inches' ) {
                                $height_width_parameter = 'LineInches';
                            }
                        }

                        if ($street == '') {
                            $errors[] = "Record $amp_id - "."Street Is Required";
                            $errors_pdf[$amp_id] = $title;
							$valid_data_status = 1;
                        }

                        if ($city == '') {
                            $errors[] = "Record $amp_id - "."City Is Required";
                            $errors_pdf[$amp_id] = $title;
							$valid_data_status = 1;
                        }

                        if ($state == '') {
                            $errors[] = "State $sn - "."Width Is Required";
                            $errors_pdf[$amp_id] = $title;
							$valid_data_status = 1;
                        }

                        if ($zip == '') {
                            $errors[] = "Record $amp_id - "."Zipcode Is Required";
                            $errors_pdf[$amp_id] = $title;
							$valid_data_status = 1;
                        }
                        
                        if ($lat == '') {
                            $errors[] = "Record $amp_id - "."Latitude Is Required";
                            $errors_pdf[$amp_id] = $title;
							$valid_data_status = 1;
                        } else {
                            if ( !($this->validateLatitude($lat)) ) {
                                $errors[] = "Record $amp_id - "."Invalid Latitude";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            } 
                        }

                        if ($lng == '') {
                            $errors[] = "Record $amp_id - "."Longitude Is Required";
							$errors_pdf[$amp_id] = $title;
							$valid_data_status = 1;
                        } else {
                            if ( !($this->validateLongitude($lng)) ) {
                                $errors[] = "Record $amp_id - "."Invalid Longitude";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            } 
                        }

                        if ($audited != '') {
                            if ( !(in_array($audited, array('Yes', 'No'))) ) {
                                $errors[] = "Record $amp_id - "."Audited must be from (Yes, No)";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            } 
                        }
                        
                        if(preg_match('/^\d+$/',$unitqty)) {
                        } else {
                            $errors[] = "Record $amp_id - "."Unit Qty Must Be Valid Number";
                            $errors_pdf[$amp_id] = $title;
							$valid_data_status = 1;
                        }
                        
                        if(preg_match('/^\d+$/',$minimumdays)) {
                        } else {
                            $errors[] = "Record $amp_id - "."Minimum Days Must Be Valid Number";
                            $errors_pdf[$amp_id] = $title;
							$valid_data_status = 1;
                        }

                        if ($locationDesc == '') {
                            $errors[] = "Record $amp_id - "."Location Description Is Required";
                            $errors_pdf[$amp_id] = $title;
							$valid_data_status = 1;
                        }

                        if ( !(in_array($imgdirection, array('Left Hand Read', 'Right Hand Read', 'Not Applicable'))) ) { 
                            $errors[] = "Record $amp_id - "."Product Direction Is Missing OR Invalid Value, The value must be from (Left Hand Read, Right Hand Read, Not Applicable)";
                            $errors_pdf[$amp_id] = $title;
							$valid_data_status = 1;
                        }

                        $from_date = "0000-00-00";$to_date = "0000-00-00";

                        if ($date1 == '') {
                            $errors[] = "Record $amp_id - "."First day available Is Required";
                            $errors_pdf[$amp_id] = $title;
							$valid_data_status = 1;
                        } else {  
                            $dc1 = substr_count($date1,"-");
							$fsl1 = substr_count($date1,"/");							
                            if ( $dc1 == 2 ) {
                                $dd1 = explode('-', $date1);
                                $d1 = checkdate ( $dd1[1], $dd1[0], $dd1[2] );
                                if ($d1) {
                                    $from_date = date("Y-m-d", strtotime($date1));
                                    //$from_date = iso_to_mongo_date($date1); 
                                } else {
                                    $errors[] = "Record $amp_id - "."First day available Is Not In Valid Format (dd-mm-yyyy)";
                                    $errors_pdf[$amp_id] = $title;
									$valid_data_status = 1;
                                }
                             }else if($fsl1 == 2){
								$dd1 = explode('/', $date1);
                                $d1 = checkdate ( $dd1[1], $dd1[0], $dd1[2] );
                                if ($d1) {
									$date1 = str_replace('/', '-', $date1);
                                    $from_date = date("Y-m-d", strtotime($date1));
                                    //$from_date = iso_to_mongo_date($date1); 
                                } else {
                                    $errors[] = "Record $amp_id - "."First day available Is Not In Valid Format (dd-mm-yyyy)";
                                    $errors_pdf[$amp_id] = $title;
									$valid_data_status = 1;
                                }
							 }else {
                                $errors[] = "Record $amp_id - "."First day available Is Not In Valid Format (dd-mm-yyyy)";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                             } 
                         }

                        if ($date2 == '') {
                            $errors[] = "Record $amp_id - "."Last day available Is Required";
                            $errors_pdf[$amp_id] = $title;
							$valid_data_status = 1;
                        } else {    
                            $dc2 = substr_count($date2,"-");
							$fsl2 = substr_count($date2,"/");							
                            if ( $dc2 == 2 ) {                    
                            $dd2 = explode('-', $date2);
                            $d2 = checkdate ( $dd2[1], $dd2[0], $dd2[2] );
                                if ($d2) {
                                    $to_date = date("Y-m-d", strtotime($date2));
                                    //$to_date = iso_to_mongo_date($date2); 
                                } else {
                                    $errors[] = "Record $amp_id - "."Last day available Is Not In Valid Format (dd-mm-yyyy)";
                                    $errors_pdf[$amp_id] = $title;
									$valid_data_status = 1;
                                }
                            }else if ( $fsl2 == 2 ) {                    
								$dd2 = explode('/', $date2);
								$d2 = checkdate ( $dd2[1], $dd2[0], $dd2[2] );
                                if ($d2) {
									$date2 = str_replace('/', '-', $date2);
                                    $to_date = date("Y-m-d", strtotime($date2));
                                    //$to_date = iso_to_mongo_date($date2); 
                                } else {
                                    $errors[] = "Record $amp_id - "."Last day available Is Not In Valid Format (dd-mm-yyyy)";
                                    $errors_pdf[$amp_id] = $title;
									$valid_data_status = 1;
                                }
                            } else {
                                $errors[] = "Record $amp_id - "."Last day available Is Not In Valid Format (dd-mm-yyyy)";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }
                        }

                        $today_date = date('Y-m-d');$date_diff_count = 0;$weekPeriod = '';

                        if ( $from_date != '0000-00-00' && $to_date != '0000-00-00' ) {
                            if ( $from_date >= $today_date ) {
                            } else {
                                $from_date = "0000-00-00";
                                $errors[] = "Record $amp_id - "."First day available should be greater than Today's Date";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }
							
                            if ( $to_date >= $today_date ) {    
                            } else {
                                $to_date = "0000-00-00";
                                $errors[] = "Record $amp_id - "."Last day available should be greater than Today's Date";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }
                        }

                        if ( $from_date != '0000-00-00' && $to_date != '0000-00-00' ) {
                            $d_diff = date_diff(date_create($from_date),date_create($to_date));
                            $date_diff_count = $d_diff->format("%R%a");
                            if ((int)$date_diff_count > 0) {
                            } else {
                                $errors[] = "Record $amp_id - "."End Date should be greater than Start Date";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }
                        }  

                        if ((int)$date_diff_count > 0) {

                            if(preg_match('/^\d+$/',$rateCard)) {
                            } else {
                                $errors[] = "Record $amp_id - "."4-week Rate Card Net Cost Must Be Valid Number";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }

                            if(preg_match('/^\d+$/',$negotiatedCost)) {
                            } else {
                                $errors[] = "Record $amp_id - "."4-week Negotiated Net Cost / Reserve Price Must Be Valid Number";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }

                            $four_weeks_price_net_cost = $rateCard;

                            $date1_ts = strtotime($date1); 
                            $date2_ts = strtotime($date2); 
                            $diff = $date2_ts - $date1_ts; 
                            $total_selected_days = round($diff / 86400) + 1;

                            $weekPeriod = number_format(($total_selected_days/28),2);
                            
                            if ( $impression1 != '' ) {
                                if(preg_match('/^\d+$/',$impression1)) {
                                    $first_impression_value =  floor($impression1 / 7) * $total_selected_days;
                                    $cpm1 = ((($four_weeks_price_net_cost/28)*$total_selected_days) / $first_impression_value) * 1000;
                                } else {
                                    $errors[] = "Record $amp_id - "."Impression 1 Must Be Valid Number";
                                    $errors_pdf[$amp_id] = $title;
									$valid_data_status = 1;
                                }
                            }

                            if ( $impression2 != '' ) {
                                if(preg_match('/^\d+$/',$impression2)) {
                                    $second_impression_value =  floor($impression2 / 7) * $total_selected_days;
                                    $cpm2 = ((($four_weeks_price_net_cost/28)*$total_selected_days) / $second_impression_value) * 1000;
                                } else {
                                    $errors[] = "Record $amp_id - "."Impression 2 Must Be Valid Number";
                                    $errors_pdf[$amp_id] = $title;
									$valid_data_status = 1;
                                }
                            } else {
                                $errors[] = "Record $amp_id - "."Impression 2 Is Required";
                                $errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
                            }

                            if ( $impression3 != '' ) {
                                if(preg_match('/^\d+$/',$impression3)) {
                                    $third_impression_value =  floor($impression3 / 7) * $total_selected_days;
                                    $cpm3 = ((($four_weeks_price_net_cost/28)*$total_selected_days) / $third_impression_value) * 1000;
                                } else {
                                    $errors[] = "Record $amp_id - "."Impression 3 Must Be Valid Number";
                                    $errors_pdf[$amp_id] = $title;
									$valid_data_status = 1;
                                }
                            } 

                            if ( $impression4 != '' ) {
                                if(preg_match('/^\d+$/',$impression4)) {
                                    $fourth_impression_value =  floor($impression4 / 7) * $total_selected_days;
                                    $cpm4 = ((($four_weeks_price_net_cost/28)*$total_selected_days) / $fourth_impression_value) * 1000;
                                } else {
                                    $errors[] = "Record $amp_id - "."Impression 4 Must Be Valid Number";
                                    $errors_pdf[$amp_id] = $title;
									$valid_data_status = 1;
                                }
                            }
                        }
							if($install_cost != '' || $install_cost == 0){
								if(preg_match('/^\d+$/',$install_cost)) {
									if($install_cost < 0){
										$errors[] = "Record $amp_id - "."Install Cost Must Be Greaterthan or equal to zero";
										$errors_pdf[$amp_id] = $title;
										$valid_data_status = 1;	
									}
								} else {
									$errors[] = "Record $amp_id - "."Install Cost Must Be Valid Number";
									$errors_pdf[$amp_id] = $title;
									$valid_data_status = 1;
								}
							}else{ 
								$errors[] = "Record $amp_id - "."Please enter Installation Cost";
								$errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
							}
						if($tax_percentage !='' || $tax_percentage == 0){
							if(preg_match('/^\d+$/',$tax_percentage)) {
								if($tax_percentage < 0){
									$errors[] = "Record $amp_id - "."Tax Must Be Greaterthan or equal to zero";
									$errors_pdf[$amp_id] = $title;
									$valid_data_status = 1;
								}else if($tax_percentage > 100){
									$errors[] = "Record $amp_id - "."Tax Must Be Lessthan or equal to 100";
									$errors_pdf[$amp_id] = $title;
									$valid_data_status = 1;
								}
							} else {
								$errors[] = "Record $amp_id - "."Tax Must Be Valid Number";
								$errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
							}
						}
						if($productioncost !='' || $productioncost == 0){	
							if(preg_match('/^\d+$/',$productioncost)) {
								if($productioncost < 0){
								$errors[] = "Record $amp_id - "."Production Cost Must Be Greaterthan or equal to zero";
								$errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;	
								}
							} else {
								$errors[] = "Record $amp_id - "."Production Cost Must Be Valid Number";
								$errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
							}
						}else{
							$errors[] = "Record $amp_id - "."Please enter Production Cost";
							$errors_pdf[$amp_id] = $title;
							$valid_data_status = 1;
						}
                        if ( !(in_array($fix, array('Fixed', 'Variable'))) ) {
                            $errors[] = "Record $amp_id - "."Fixed vs Variable Is Missing OR Invalid Value, The value must be from (Fixed, Variable)";
                            $errors_pdf[$amp_id] = $title;
							$valid_data_status = 1;
                        } 

                        if ( !(in_array($billing, array('yes', 'no'))) ) {
                            $errors[] = "Record $amp_id - "."AMP Bills Is Missing OR Invalid Value, The value must be from (yes, no)";
                            $errors_pdf[$amp_id] = $title;
							$valid_data_status = 1;
                        } 

                        if ( $billing == 'yes' ) {
                            $billingYes = 'yes';
                        }

                        if ( $billing == 'no' ) {
                            $billingNo = 'no';
                        }

                        if ( !(in_array($servicing, array('yes', 'no'))) ) {
                            $errors[] = "Record $amp_id - "."AMP Services Is Missing OR Invalid Value, The value must be from (yes, no)";
                            $errors_pdf[$amp_id] = $title;
							$valid_data_status = 1;
                        } 

                        if ( $servicing == 'yes' ) {
                            $servicingYes = 'yes';
                        }

                        if ( $servicing == 'no' ) {
                            $servicingNo = 'no';
                        }
						$data_dup_message = 0;
						$data_dup_dates_message = 0;
						/// Check duplicates in pending json files Start ///
						$product_csv_json_pending_path_duplicates = base_path() . '/html/csv_json_uploads/pending/';
						$product_csv_json_success_path_duplicates = base_path() . '/html/csv_json_uploads/success/';

						$get_json_files_data_duplicates = ProductsBulkCsvFiles::where('status', 'pending')->get();
						foreach ($get_json_files_data_duplicates as $key => $value_dup) {
							if($data_dup_message == 1){
								break;
							}
							$json_file_name_duplicates = $product_csv_json_pending_path_duplicates.$value_dup->json_file_name;
							if (file_exists($json_file_name_duplicates)) {
								$json_data_duplicates = json_decode(file_get_contents($json_file_name_duplicates), true); 
								if ( !(is_null($json_data_duplicates)) ) {
									foreach ($json_data_duplicates as $key => $input_dup) {
										if($input_dup['title'] == $title && $input_dup['client_mongo_id'] == $client_mongo_id && $input_dup['type'] == $product_type && $input_dup['from_date'] == $from_date && $input_dup['to_date'] == $to_date && abs(($input_dup['cpm']-$cpm2)/$cpm2) < 0.00001 && $input_dup['secondImpression'] == $impression2){
											$data_dup_message = 1;
											break;
										}
									}
								}
							}
						}
						if($data_dup_message == 1){
							$errors[] = "Record $amp_id - "."is already exists in processing product list";
							$errors_pdf[$amp_id] = $title;
							$valid_data_status = 1;
						}
						/// Check duplicates in pending json files End ///
						/// Check duplicates in products table Start ///
						$check_duplicates = Product::select('id','from_date','to_date','cpm')->where('title',$title)->where('client_mongo_id',$client_mongo_id)->where('type',$product_type)->where('secondImpression',$impression2)->get()->toArray();
						if(!empty($check_duplicates)){
							foreach ($check_duplicates as $key => $input_dup_dates) {
								if($data_dup_dates_message == 1){
									break;
								}
								$from_date_dup = $input_dup_dates['from_date']->toDateTime()->format('Y-m-d');
								$to_date_dup = $input_dup_dates['to_date']->toDateTime()->format('Y-m-d');
								if($from_date_dup == $from_date && $to_date_dup == $to_date && abs(($input_dup_dates['cpm']-$cpm2)/$cpm2) < 0.00001){
									$data_dup_dates_message = 1;
									break;
								} 
							}
							if($data_dup_dates_message == 1){
								$errors[] = "Record $amp_id - "."is already exists in product table list";
								$errors_pdf[$amp_id] = $title;
								$valid_data_status = 1;
							}
						}
						/// Check duplicates in products table End ///

                        $product_data = array(
                            "type" => $product_type,
                            "sellerId" => $sellerId,
                            "lighting" => $lighting,
                            "fliplength" => $fliplength,
                            "spotLength" => $spotlength,
                            //"looplength" => $looplength,
                            "ageloopLength" => $ageloopLength,
                            "length" => $length,
                            "title" => $title,
                            "width" => $width,
                            "height" => $height,
                            "address" => $street,
                            "city" => $city,
                            "zipcode" => $zip,
                            "lat" => $lat,
                            "lng" => $lng,
                            "direction" => $direction, 
                            "rateCard" => $rateCard,
                            "imgdirection" => $imgdirection,
                            "audited" => $audited,
                            "cancellation_policy" => $cancellation_policy,
                            "cancellation_terms" => $cancellation_terms,
                            "firstImpression" => $impression1,
                            "secondImpression" => $impression2,
                            "thirdImpression" => $impression3,
                            "forthImpression" => $impression4,
                            "state" => $state,
                            "vendor" => $vendor,
                            "mediahhi" => $mediahhi,
                            "weekPeriod" => $weekPeriod,
                            "installCost" => $install_cost,
                            "negotiatedCost" => $negotiatedCost,
                            "productioncost" => $productioncost,
                            "notes" => $notes,
                            "Comments" => $comments,
                            "locationDesc" => $locationDesc,
                            "description" => $description,
                            "firstDay" => '',
                            "lastDay" => '',
                            "cpm" => $cpm2,
                            "firstcpm" => $cpm1,
                            "thirdcpm" => $cpm3,
                            "forthcpm" => $cpm4,
                            "unitQty" => $unitqty,
                            "billingYes" => $billingYes,
                            "billingNo" => $billingNo,
                            "servicingYes" => $servicingYes,
                            "servicingNo" => $servicingNo,
                            "fix" => $fix,
                            "minimumdays" => $minimumdays,
                            "stripe_percent" => $stripe_percent,
                            "from_date" => $from_date,
                            "to_date" => $to_date,
                            "area" => $area, 
                            "image" => $image,
                            "symbol" => $symbolPath,
                            "network" => $network,
                            "nationloc" => $nationloc,
                            "placement" => $placement,
                            "genre" => $genre,
                            "daypart" => $daypart,
                            "file_type" => $file_type,
                            "staticMotion" => $staticMotion,
                            "sound" => $sound,
                            "medium" => $medium,
                            "product_newMedia" => $product_newMedia,
                            "client_mongo_id" => $client_mongo_id,
                            "client_name" => $client_name,
                            "client_id" => $client_id,
                            "tax_percentage" => $tax_percentage
                        );
						if($valid_data_status == 0){
							$final_data[] = $product_data;
							$final_data_success[$amp_id] = $product_data;
						}

                        //echo "<pre>";print_r($product_data);echo "</pre>";exit();
                        $sn++;
                    }
                    $rn++;
                } else {
                    $errors[] = "No Records In Given File";
                    return response()->json(["status" => "0", "message" => $errors], 400);
                }               
            }      
            
            fclose($open);

            $headers = array_filter($headers);
            $final_data = array_filter($final_data);

            $success = count($final_data);
			//echo '<pre>finaldata';print_r($final_data);
			//echo '<pre>';print_r($success[$amp_id]);exit;
            /*if ( count($errors) > 0 ) {
                return response()->json(["status" => "0", "message" => $errors], 400);
            }*/
			//$duplicate_record = Product::where('title', '=', $product_data['title'])->get();
			//echo '<pre>';print_r($duplicate_record);exit;
            if ( $success == 0 ) {
                $errors[] = "No Records In Given File";
                return response()->json(["status" => "0", "message" => $errors], 400);
            }
            
            //echo "<pre>";print_r($headers);echo "</pre>";exit(); 
            //echo "<pre>";print_r($final_data);echo "</pre>";exit();

            $product_type_file_name = $product_type == 'Digital/Static' ? 'Digital-Static' : $product_type;
            $product_csv_json_pending_path = base_path() . '/html/csv_json_uploads/pending/';
            $jsonName = $product_type_file_name."-".date("Y-m-d")."-".\Carbon\Carbon::now()->timestamp .".json";
            $uploadedJsonName = $product_csv_json_pending_path.$jsonName;
            $newJsonString = json_encode($final_data, JSON_PRETTY_PRINT);
            //var_dump($newJsonString);exit();
                
            if ($newJsonString === false) {
                $errors[] = "File Contains Special Characters, Please Fix Remove Special Characters.";
                return response()->json(["status" => "0", "message" => $errors], 400);
            }

            file_put_contents($uploadedJsonName, stripslashes($newJsonString));         
			//$product_duplicate = Product::where('title', '=', $title)->get();
			//echo '<pre>';print_r($product_duplicate);exit;
            //echo '<pre>success';print_r($product_data); exit;                
            //Save the json file name in table
            $product_json_id = uniqid();
            $product_json = new ProductsBulkCsvFiles;
            $product_json->id = $product_json_id;
            $product_json->client_mongo_id = $user_mongo['client_mongo_id']; 
            $product_json->product_type = $product_type; 
            $product_json->status = "pending"; 
            $product_json->json_file_name = $jsonName; 
			
			$bulk_upload_report = ['errors' => $errors,'errors_pdf' => $errors_pdf, 'success' => $final_data_success];
            $pdf = PDF::loadView('pdf.bulk_upload_products_data', $bulk_upload_report);
			//echo '<pre>report';print_r($bulk_upload_report);exit;
            if ($product_json->save()) {
				
				event(new BulkUploadEvent([
					  'type' => Notification::$NOTIFICATION_TYPE['bulk-upload-pending'],
					  'from_id' => $user_mongo['id'],
					  'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['owner'],
					  'to_id' => $client->id,
					  'to_client' => $client->id,
					  'desc' => "Bulk Products Uploaded",
					  'message' => 'Bulk Upload CSV File Initially Submitted For Type '. $product_type . ' and the execution will be done in next 30 minutes. '. $success .' Records has been uploaded successfully and '. count($errors_pdf) .' Records has failed to upload. Please check mail and attachment of PDF accordingly.',
					  'data' => "Bulk Products Uploaded"
					])); 
					$notification_obj = new Notification;
					$notification_obj->id = uniqid();
					$notification_obj->type = "bulk_upload_pending";
					$notification_obj->from_id =  $user_mongo['id'];
					$notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['owner'];
					$notification_obj->to_id = $client->id;
					$notification_obj->to_client = $client->id;
					$notification_obj->desc = "Bulk Products Uploaded";
					$notification_obj->message = 'Bulk Upload CSV File Initially Submitted For Type '. $product_type . ' and the execution will be done in next 30 minutes. '. $success .' Records has been uploaded successfully and '. count($errors_pdf) .' Records has failed to upload. Please check mail and attachment of PDF accordingly.';
                    $notification_obj->user_id = null;
					$notification_obj->status = 0;
                    $notification_obj->save(); 
				
                if ($client_email != '') {
                    $mail_tmpl_params = [
                        'receiver_name' => $client_name,
                        'mail_message' => 'Bulk Upload CSV File Initially Submitted For Type '. $product_type . ' and the execution will be done in next 30 minutes. <br> '. $success .' Records has been uploaded successfully and '. count($errors_pdf) .' Records has failed to upload <br> Please check attachment of PDF accordingly.'
                    ];
                    $mail_data = [
                        'email_to' => $client_email,
                        'recipient_name' => $client_name, // Actual Client Name as $client_name               
                        //'email_to1' => 'admin@advertisingmarketplace.com', // CC of Email
                        'email_to1' => 'sandhyarani.manelli@peopletech.com', // CC of Email
                        'recipient_name1' => 'Richard',
						'pdf_file_name' => "Bulk-Upload.pdf",
						'pdf' => $pdf
                    ];
                    Mail::send('mail.bulk_upload_intial', $mail_tmpl_params, function($message) use ($mail_data) {
                        $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Bulk Upload CSV File Initially Submitted Successfully');
                        $message->cc($mail_data['email_to1'], $mail_data['recipient_name1'])->subject('Bulk Upload CSV File Initially Submitted Successfully');
						$message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
                    });

                    $success_msg[] = "File Uploaded Successfully, $success records of $product_type will be insert as scheduled cron job run in next 30 min, please check accordingly.";
					$error_status = 0;
					if(count($errors) > 0){
						$error_status = 1;
					}

                    return response()->json(["status" => "1", "message" => $success_msg, "error_status" => $error_status, "error_message" => $errors],400);
                } else {
                    $errors1[] = "Something went wrong!";
                    return response()->json(["status" => "0", "message" => $errors1]);
                }

                //sleep(10);

                // Here to entire loop will be move to cron file.
                $product_csv_json_pending_path = base_path() . '/html/csv_json_uploads/pending/';
                $product_csv_json_success_path = base_path() . '/html/csv_json_uploads/success/';

                $get_json_files_data = ProductsBulkCsvFiles::where('id', $product_json_id)->get();
                foreach ($get_json_files_data as $key => $value) {
                    
                    $json_file_name = $product_csv_json_pending_path.$value->json_file_name;
                    if (file_exists($json_file_name)) {
                        $json_data = json_decode(file_get_contents($json_file_name), true); 
                        if ( !(is_null($json_data)) ) {
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

                                $clientId = str_pad($newClientID, 6, '0', STR_PAD_LEFT);
                                $product_obj->client_id = isset($client) ? $client->client_id : "";
                                $clientId_uniqueID = $clientId.$product_obj->client_id;
                                $ASI_id = '000'.$product_obj->client_id;
                                $ASI_id1 = $clientId.$product_obj->client_id;
                                $seller_Id = isset($input['sellerId']) ? $input['sellerId'] : "";
                                $seller_Id1 = '_'.$seller_Id;

								$product_obj->bulkupload_uniqueID = $client_email.'-'.date("Y-m-d h:m:s");
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
                                $product_obj->tax_percentage = isset($input['tax_percentage']) ? $input['tax_percentage'] : "";

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

                                $product_obj->image = "";
                                $imageArray = [];
                                $imageArray[] = "/uploads/images/products/" . $image;
                                $product_obj->image = $imageArray;

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
                                    // Insert data to elasticsearch :: Pankaj 24 Feb 2022    
                                    // $get_data = ProductsBulkUpload::where('id', '=', $product_obj->id)->first();
                                    // $this->es_etl($get_data, "insert");

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
                        }

                        $product_json1 = ProductsBulkCsvFiles::where('id', '=', $value->id)->first();
                        $product_json1->status = "success"; 
                        $product_json1->save();

                        $success_json_file_name = $product_csv_json_success_path.$value->json_file_name;
                        rename($json_file_name, $success_json_file_name);
                    }
                }
				
				event(new BulkUploadEvent([
					  'type' => Notification::$NOTIFICATION_TYPE['bulk-upload'],
					  'from_id' => $user_mongo['id'],
					  'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['owner'],
					  'to_id' => $client->id,
					  'to_client' => $client->id,
					  'desc' => "Bulk Products Uploaded",
					  'message' => 'Bulk Upload For Type - '. $product_type . ' Execution Done Successfully. You can check data now.',
					  'data' => "Bulk Products Uploaded"
					]));
					$notification_obj = new Notification;
					$notification_obj->id = uniqid();
					$notification_obj->type = "bulk_upload";
					$notification_obj->from_id =  $user_mongo['id'];
					$notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['owner'];
					$notification_obj->to_id = $client->id;
					$notification_obj->to_client = $client->id;
					$notification_obj->desc = "Bulk Products Uploaded";
					$notification_obj->message = 'Bulk Upload For Type - '. $product_type . ' Execution Done Successfully. You can check data now.';
                    $notification_obj->user_id = null;
					$notification_obj->status = 0;
                    $notification_obj->save();
				 
                $mail_tmpl_params = [
                    'receiver_name' => $client_name,
                    'mail_message' => 'Bulk Upload For Type - '. $product_type . ' Execution Done Successfully. You can check data now.'           
                ];
                $mail_data = [
                    'email_to' => $client_email,
                    //'email_to' => 'sandhyarani.manelli@peopletech.com',
                    'email_to1' => 'sandhyarani.manelli@peopletech.com',
                    //'email_to1' => 'admin@advertisingmarketplace.com', // CC of Email
                    'recipient_name' => $client_name, 
                    'recipient_name1' => 'Richard'
                ];
                Mail::send('mail.bulk_upload_success', $mail_tmpl_params, function($message) use ($mail_data) {
                    $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Bulk Upload CSV File Execution Done Successfully');
                    $message->cc($mail_data['email_to1'], $mail_data['recipient_name1'])->subject('Bulk Upload CSV File Execution Done Successfully');
                });

                return response()->json(["status" => "1", "message" => $success_msg]);
            }
        } else {
            $errors[] = "CSV File Is Required.";
            return response()->json(["status" => "0", "message" => $errors], 400);
        }
    }

    public function saveBulkUploadImages(Request $request) {
        /*ProductsBulkImages::where([
            ['id', '!=', 1]
        ])->delete();
        exit();*/ 
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $client_mongo_id = $user_mongo['client_mongo_id'];

        $product_img_path = base_path() . '/html/uploads/images/products';
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }

        if (isset($input['type'])) {
            if ($input['type'] != '') {
                if ( !(in_array($input['type'], array('Static', 'Digital', 'Digital/Static', 'Media'))) ) {
                    return response()->json(["status" => "0", "message" => "Type Is Invalid Value, The value must be from (Static, Digital, Digital/Static, Media)"], 400);
                } 
            } else {
                return response()->json(["status" => "0", "message" => "Type Is Required"], 400);
            }
        } else {
            return response()->json(["status" => "0", "message" => "Type Is Required"], 400);
        }

        $product_type = $input['type'];

        $file_type_check = gettype($request->file('image'));
        if ($file_type_check == 'object') {
            $files_array[] = $request->file('image');
        } else if ($file_type_check == 'array') {
            $files_array = $request->file('image');
        } else {
            return response()->json(["status" => "0", "message" => "Incorrect Image File."], 400);
        }

        if ( count($files_array) != 1 ) {
            return response()->json(["status" => "0", "message" => "Only One Image Is Required."], 400);
        }

        if ($request->hasFile('image')) {
            $msg = 'File Uploaded Successfully.';

            $val = $files_array[0];           
            $imageNmae = \Carbon\Carbon::now()->timestamp . $val->getClientOriginalName();
            if ($val->move($product_img_path, $imageNmae)) {
                $product_img = new ProductsBulkImages; 
                $product_img->id = uniqid();
                $product_img->product_type = $product_type; 
                $product_img->client_mongo_id = $client_mongo_id; 
                $product_img->image = $imageNmae; 

                $product_img->save();
            } else {
                $msg = 'Errors';
            }

            return response()->json(["status" => "1", "message" => $msg]);
        } else {
            return response()->json(["status" => "0", "message" => "Image Is Required."], 400);
        }
    }

    public function getBulkUploadImages() {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $client_mongo_id = $user_mongo['client_mongo_id'];
        $bulk_images_data = [];
        $bulk_images = ProductsBulkImages::where('client_mongo_id', '=', $client_mongo_id)->orderBy('updated_at', 'desc')->get();
        foreach ($bulk_images as $key => $value) {
            $value->image_name = $value->image;
            $value->image = '/uploads/images/products/'.$value->image;
            $bulk_images_data[] = $value;
        }
        $bulk_images_list = ["bulk_images" => $bulk_images_data];
        return response()->json($bulk_images_list);
    }
	public function getBUProductsBySeller() {
		$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
		$admin_user = UserMongo::select('id','client_mongo_id')->where('user_id', '=', 1)->first();
		if($user_mongo['client_mongo_id'] == $admin_user['client_mongo_id']){
			//$user_mongo_c_id = UserMongo::where('id', '=', $input['seller_id'])->first();
			//$bulk_products = Product::select('id','bulkupload_uniqueID')->where('client_mongo_id', '=', $user_mongo_c_id['client_mongo_id'])->groupBy('bulkupload_uniqueID')->get();
			$bulk_products = Product::select('id','bulkupload_uniqueID')->where('bulkupload_uniqueID','!=',null)->groupBy('bulkupload_uniqueID')->get();
			return response()->json($bulk_products);
		}
		else{
			$bulk_products = Product::select('id','bulkupload_uniqueID')->where('client_mongo_id', '=', $user_mongo['client_mongo_id'])->where('bulkupload_uniqueID','!=',null)->groupBy('bulkupload_uniqueID')->get();
			return response()->json($bulk_products);
		}
	}
}