<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Mail;
use Illumninate\support\Collection;
use App\Models\Campaign;
use App\Models\CampaignSuggestionRequest;
use App\Models\ShortListedProduct;
use App\Models\User;
use App\Models\UserMongo;
use App\Models\Client;
use App\Models\Product;
use App\Models\Format;
use App\Models\CampaignQuoteChange;
use App\Models\CampaignPayment;
use App\Models\ProductBooking;
use App\Models\Notification;
use App\Models\ClientMongo;
use App\Models\MetroPackage;
use App\Models\MakeOffer;
use App\Models\FindForMe;
use App\Models\CancelCampaign;
use App\Models\DeleteProduct;
use App\Helpers\NotificationHelper;
use App\Models\CampaignProduct;
use App\Models\CustomerQuery;
use App\Models\BulkUpload;
use App\Models\Contracts;
use App\Models\Invoices;
use App\Models\ClientType;
use App\Models\Offer_admin_seller_comments;
use App\Models\Area;
use App\Models\RFPSearchCriteria;
use App\Models\StripePayments;
use Auth;
use Entrust;
use JWTAuth;
use PDF;
use Log;
use App\Events\ProductDeleteRequestedEvent;
use App\Events\CampaignDeleteRequestedEvent;
use App\Events\OfferRequestedEvent;
use App\Events\OfferRejectedEvent;
use App\Events\OfferAcceptedEvent;
use App\Events\CampaignClosedEvent;
use App\Events\CampaignLaunchEvent;
use App\Events\CampaignLaunchRequestedEvent;
use App\Events\CampaignQuoteProvidedEvent;
use App\Events\CampaignQuoteRequestedEvent;
use App\Events\CampaignQuoteRevisionEvent;
use App\Events\CampaignSuspendedEvent;
use App\Events\metroCampaignClosedEvent;
use App\Events\metroCampaignLaunchEvent;
use App\Events\metroCampaignLockedEvent;
use App\Events\RFPRequestedEvent;
use App\Events\AddProductRequestedEvent;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel; 
use DB;
use DateTime;
use DateTimeZone;
use QuickBooksOnline\API\DataService\DataService;
use App\Helpers\QuickbooksCustomerHelper;

class CampaignController extends Controller {

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

    public function getCampaigns(Request $request) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $page_no = $request->input('page_no');
        $page_size = $request->input('page_size');
        if (isset($page_no, $page_size) && !empty($page_no) && !empty($page_size)) {
            $offset = ($page_no - 1) * $page_size;
            $campaigns = Campaign::where([
                        ['user_mongo_id', '=', $user_mongo['id']],
                        ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['user']]
                    ])->skip($offset)->take((int) $page_size)->orderBy('updated_at', 'desc')->get();
        } else {
            $campaigns = Campaign::where([
                        ['user_mongo_id', '=', $user_mongo['id']],
                        ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['user']]
                    ])->orderBy('updated_at', 'desc')->get();
        }
        return response()->json($campaigns);
    }

    public function getUserCampaigns(Request $request) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $page_no = $request->input('page_no');
        $page_size = $request->input('page_size');
        if (isset($page_no, $page_size) && !empty($page_no) && !empty($page_size)) {
            $offset = ($page_no - 1) * $page_size;
            $campaigns = Campaign::where([
                        ['created_by', '=', $user_mongo['id']],
                        ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['user']]
                    ])->skip($offset)->take((int) $page_size)->orderBy('updated_at', 'desc')->get();
        } else {
            $campaigns = Campaign::where([
                        ['created_by', '=', $user_mongo['id']],
                        ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['user']]
                    ])->orderBy('updated_at', 'desc')->get();
        }
        return response()->json($campaigns);
    }
  
    public function getActiveUserCampaigns(Request $request) {
		 
			
		/*$campaign_products = ProductBooking::where('campaign_id', '=', '654dd5c871028')->get();
		$invoice_data_arr = array();
		foreach ($campaign_products as $key => $campaign_product) {
			$product_data = Product::select("siteNo","title","rateCard","unitQty")->where('id', '=', $campaign_product->product_id)->first();	
			$products_data = QuickbooksCustomerHelper::getProduct($product_data['siteNo']);
			if(empty($products_data['QueryResponse'])){
				$product_arr = '{
				  "TrackQtyOnHand": true, 
				  "Name": "'.$product_data['siteNo'].'", 
				  "QtyOnHand": '.$product_data['unitQty'].', 
				  "Sku": "'.$product_data['siteNo'].'", 
				  "PurchaseCost": '.$product_data['rateCard'].',
				  "IncomeAccountRef": {
					"name": "Sales of Product Income", 
					"value": "79"
				  }, 
				  "AssetAccountRef": {
					"name": "Inventory Asset", 
					"value": "81"
				  }, 
				  "InvStartDate": '.date('Y-m-d').',  
				  "Type": "Inventory", 
				  "ExpenseAccountRef": {
					"name": "Cost of Goods Sold", 
					"value": "80"
				  }
				}';
				$products_data_store = QuickbooksCustomerHelper::storeProduct($product_arr);
				$products_data = QuickbooksCustomerHelper::getProduct($product_data['siteNo']);
			}
			$invoice_data_arr[] = array(
				"DetailType" => "SalesItemLineDetail",
				"Amount" => $campaign_product->quantity*$campaign_product->price,
				"SalesItemLineDetail" => array(
					"ItemRef" => array(
						"name" => $product_data['siteNo'], 
						"value" => $products_data['QueryResponse']['Item'][0]['Id']
					),
					"Qty" => $campaign_product->quantity,
					"ServiceDate" => $campaign_product->booked_from,
					"UnitPrice" => $campaign_product->price,
				),
				"Description" => '"'.$product_data['title'].'"'
			);
		} 
		$invoice_data = '"Line": 
				'.json_encode($invoice_data_arr).'
			  ';	  
		$products_data = QuickbooksCustomerHelper::getMainMethod($invoice_data);
		echo'<pre>invoice_data_arr';print_r($products_data);exit;*/
			
			
			
			
		
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo['user_id'])->first();
        $processed_campaign_suggestion_ids = CampaignSuggestionRequest::where('processed', '=', true)->pluck('campaign_id')->toArray();
        $match_array_multiple = [];
		if ($request->isJson()) {
			$input = $request->json()->all();
		} else {
			$input = $request->all();
		}
		$page_no = $request->input('page_no');
        $page_size = $request->input('page_size');
		$user_campaigns_count = 0;
		/*if (isset($page_no, $page_size) && !empty($page_no) && !empty($page_size)) {
		$offset = ($page_no - 1) * $page_size;*/	
		if (isset($this->input['tab_status'])) {
			if($this->input['tab_status'] == 'saved_campaign'){
				$match_array_multiple = [
						'$or' => [
								  ['status' => Campaign::$CAMPAIGN_STATUS['campaign-preparing']],
								  ['status' => Campaign::$CAMPAIGN_STATUS['rfp-campaign']]
						]
					];
			}else if($this->input['tab_status'] == 'insertion_order'){
				$match_array_multiple = ['status' => Campaign::$CAMPAIGN_STATUS['booking-requested']];
			}else if($this->input['tab_status'] == 'scheduled'){
				$match_array_multiple = ['status' => Campaign::$CAMPAIGN_STATUS['scheduled']];
			}else if($this->input['tab_status'] == 'running'){
				$match_array_multiple = ['status' => Campaign::$CAMPAIGN_STATUS['running']];
			}else if($this->input['tab_status'] == 'closed'){
				$match_array_multiple = ['status' => [
											'$nin' => [
												Campaign::$CAMPAIGN_STATUS['campaign-preparing'],
												Campaign::$CAMPAIGN_STATUS['booking-requested']
											]
										]];
			}else if($this->input['tab_status'] == 'cancelled'){
				$match_array_multiple = ['status' => Campaign::$CAMPAIGN_STATUS['deleted-cancelled']];
			}else if($this->input['tab_status'] == 'payments'){
				$match_array_multiple = ['status' => [
											'$nin' => [
												Campaign::$CAMPAIGN_STATUS['campaign-preparing'],
												Campaign::$CAMPAIGN_STATUS['rfp-campaign']
											]
										]];
			}else{
				return response()->json(['status' => 0, 'message' => "Tab is incorrect."]);
			}
			
			$user_campaigns_count = Campaign::raw(function($collection) use ($processed_campaign_suggestion_ids, $user_mongo,$match_array_multiple) {
					return $collection->find([
								'$and' => [
									['created_by' => $user_mongo['id']],
									[
										'format_type' => [
											'$in' => [null, Format::$FORMAT_TYPE['ooh']]
										]
									],
									[
										'$or' => [
											[
												'$and' => [
													['from_suggestion' => true],
													[
														'id' => [
															'$in' => $processed_campaign_suggestion_ids
														]
													]
												]
											],
											[
												'from_suggestion' => [
													'$in' => [null, false]
												]
											]
										]
									],
									$match_array_multiple
								]
									], [
								'sort' => [
									'updated_at' => -1
								]
					]);
				})->count();
			
			if (isset($page_no, $page_size) && !empty($page_no) && !empty($page_size)) {
				$offset = ($page_no - 1) * $page_size;
				$user_campaigns = Campaign::raw(function($collection) use ($processed_campaign_suggestion_ids, $user_mongo,$match_array_multiple) {
					return $collection->find([
								'$and' => [
									['created_by' => $user_mongo['id']],
									[
										'format_type' => [
											'$in' => [null, Format::$FORMAT_TYPE['ooh']]
										]
									],
									[
										'$or' => [
											[
												'$and' => [
													['from_suggestion' => true],
													[
														'id' => [
															'$in' => $processed_campaign_suggestion_ids
														]
													]
												]
											],
											[
												'from_suggestion' => [
													'$in' => [null, false]
												]
											]
										]
									],
									$match_array_multiple
								]
									], [
								'sort' => [
									'updated_at' => -1
								]
					]);
				})->slice($offset,(int) $page_size);
			}else{
				$user_campaigns = Campaign::raw(function($collection) use ($processed_campaign_suggestion_ids, $user_mongo,$match_array_multiple) {
					return $collection->find([
								'$and' => [
									['created_by' => $user_mongo['id']],
									[
										'format_type' => [
											'$in' => [null, Format::$FORMAT_TYPE['ooh']]
										]
									],
									[
										'$or' => [
											[
												'$and' => [
													['from_suggestion' => true],
													[
														'id' => [
															'$in' => $processed_campaign_suggestion_ids
														]
													]
												]
											],
											[
												'from_suggestion' => [
													'$in' => [null, false]
												]
											]
										]
									],
									$match_array_multiple
								]
									], [
								'sort' => [
									'updated_at' => -1
								]
					]);
				});
			}
		}else{
			if (isset($page_no, $page_size) && !empty($page_no) && !empty($page_size)) {
				$offset = ($page_no - 1) * $page_size;
				$user_campaigns = Campaign::raw(function($collection) use ($processed_campaign_suggestion_ids, $user_mongo) {
					return $collection->find([
								'$and' => [
									['created_by' => $user_mongo['id']],
									[
										'format_type' => [
											'$in' => [null, Format::$FORMAT_TYPE['ooh']]
										]
									],
									[
										'$or' => [
											[
												'$and' => [
													['from_suggestion' => true],
													[
														'id' => [
															'$in' => $processed_campaign_suggestion_ids
														]
													]
												]
											],
											[
												'from_suggestion' => [
													'$in' => [null, false]
												]
											]
										]
									],
								]
									], [
								'sort' => [
									'updated_at' => -1
								]
					]);
				})->slice($offset,(int) $page_size);
			}else{
				$user_campaigns = Campaign::raw(function($collection) use ($processed_campaign_suggestion_ids, $user_mongo) {
					return $collection->find([
								'$and' => [
									['created_by' => $user_mongo['id']],
									[
										'format_type' => [
											'$in' => [null, Format::$FORMAT_TYPE['ooh']]
										]
									],
									[
										'$or' => [
											[
												'$and' => [
													['from_suggestion' => true],
													[
														'id' => [
															'$in' => $processed_campaign_suggestion_ids
														]
													]
												]
											],
											[
												'from_suggestion' => [
													'$in' => [null, false]
												]
											]
										]
									],
								]
									], [
								'sort' => [
									'updated_at' => -1
								]
					]);
				});
			}
		}
        foreach ($user_campaigns as $key => $user_campaign) {
			$user_campaign->user_campaigns_total_count = $user_campaigns_count;
			
            $product_start_date = ProductBooking::where('campaign_id', '=', $user_campaign->id)->select("booked_from","booked_to","quantity","product_id")->orderBy('booked_from', 'asc')->first();
            if (!empty($product_start_date)) {
                $user_campaign->start_date = $product_start_date->booked_from;
                $user_campaign->end_date = $product_start_date->booked_to;
				if(!is_null($product_start_date->product_id)){
					$product_area =Product::where('id', '=', $product_start_date->product_id)->select("area")->first();
					if(!is_null($product_area)){	
						$area_time_zone_value = Area::select("area_time_zone","area_time_zone_type")->where('id', '=', $product_area->area)->first();
						if(!is_null($area_time_zone_value)){
							$area_time_zone_type = $area_time_zone_value['area_time_zone_type'];
							if(!is_null($area_time_zone_type)){
								$start_time = new DateTime( $user_campaign->start_date );
								$end_time = new DateTime( $user_campaign->end_date );
								$laTimezone = new DateTimeZone($area_time_zone_type);
								$start_time->setTimeZone( $laTimezone );
								$end_time->setTimeZone( $laTimezone );
								$user_campaign->start_date = $start_time->format( 'Y-m-d H:i:s' );
								$user_campaign->end_date = $end_time->format( 'Y-m-d H:i:s' );	
							}							
						}
					}
				}
            }
			if (isset($this->input['tab_status'])){
				if($this->input['tab_status'] == 'scheduled'){
					if(date('Y-m-d') > $user_campaign->start_date || date('Y-m-d') > $user_campaign->end_date){
						unset($user_campaigns[$key]);
						continue;
					}
				}if($this->input['tab_status'] == 'running'){
					if(date('Y-m-d') < $user_campaign->start_date && date('Y-m-d') < $user_campaign->end_date){
						unset($user_campaigns[$key]);
						continue;
					}
				}if($this->input['tab_status'] == 'closed'){
					if($user_campaign->status == Campaign::$CAMPAIGN_STATUS['stopped'] || $user_campaign->status == Campaign::$CAMPAIGN_STATUS['suspended'] || date('Y-m-d') > $user_campaign->end_date){

					}else{
						unset($user_campaigns[$key]);
						continue;
					}
				}
			}
            $act_budget = ProductBooking::raw(function($collection) use ($user_campaign) {
                        return $collection->aggregate(
                                        [
                                            [
                                                '$match' =>
                                                [
                                                    "campaign_id" => $user_campaign->id
                                                ]
                                            ],
                                            [
                                                '$group' =>
                                                [
                                                    '_id' => '$campaign_id',
                                                    'total_price' => [
                                                        '$sum' => '$price*$quantity'
                                                    ],
                                                    'count' => [
                                                        '$sum' => 1
                                                    ]
                                                ]
                                            ]
                                        ]
                        );
                    });
            $total_paid = CampaignPayment::where('campaign_id', '=', $user_campaign->id)->sum('amount');
			if (isset($this->input['tab_status'])) {
				if($this->input['tab_status'] == 'payments'){
					if($total_paid == 0){
						unset($user_campaigns[$key]);
						continue;
					}
				}
			}
            if(isset($user->client->client_type->type) && $user->client->client_type->type == "owner"){
                $count = ProductBooking::where('campaign_id', '=', $user_campaign->id)->where('product_owner', '=', $user_mongo['client_mongo_id'])->count();
            }else{
                $count = ProductBooking::where('campaign_id', '=', $user_campaign->id)->count();
            }
            
			$price = 0;
            $camptot = 0;
			$offershortlistedsum = 0;
			$tax_percentage_booking = 0;
			$tax_percentage_amount = 0;
			$tax_percentage_booking_tot = 0;
			$tax_percentage_amount_tot = 0;
            if (count($act_budget) > 0) {
				if($act_budget[0]->total_price == 0){
					$campaign_products = ProductBooking::where('campaign_id', '=', $user_campaign->id)->get();
					
					if (isset($campaign_products) && count($campaign_products) > 0) {
						foreach ($campaign_products as $campaign_product_camp) {
							$tax_percentage_booking_tot = 0;
							$tax_percentage_amount_tot = 0;
							$diff=date_diff(date_create($campaign_product_camp->booked_from),date_create($campaign_product_camp->booked_to));
							$daysCount = $diff->format("%a");
							$daysCountCPM = $daysCount + 1;
							$getproductDetails =Product::where('id', '=', $campaign_product_camp->product_id)->get();
							if (!$getproductDetails->isEmpty()){
								$getproductDetails = $getproductDetails->first();
								$perdayprice = $getproductDetails->default_price/28;
							
								if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
									$price_cp1 = $campaign_product_camp->price*$campaign_product_camp->quantity;
									if($total_paid != 0){
										if(isset($campaign_product_camp->tax_percentage)){
											$tax_percentage_booking_tot = $campaign_product_camp->tax_percentage;
										}else{
											$tax_percentage_booking_tot = 0;
										}
									}else{
										$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
									}
									if($tax_percentage_booking_tot != 0){
										$tax_percentage_amount_tot = ($price_cp1 * ($tax_percentage_booking_tot))/100;
									}
									$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
									$camptot += $price_cp1+$tax_percentage_amount_tot;
								}else{
									$priceperselectedDates = $campaign_product_camp->price;
									if($total_paid != 0){
										if(isset($campaign_product_camp->tax_percentage)){
											$tax_percentage_booking_tot = $campaign_product_camp->tax_percentage;
										}else{
											$tax_percentage_booking_tot = 0;
										}
									}else{
										$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
									}
									if($tax_percentage_booking_tot != 0){
										$tax_percentage_amount_tot = ($priceperselectedDates * ($tax_percentage_booking_tot))/100;
									}
									$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
									$camptot += ($priceperselectedDates*$campaign_product_camp->quantity)+($tax_percentage_amount_tot*$campaign_product_camp->quantity);
								} 
							}
						}
					}
					if (isset($campaign_products) && count($campaign_products) > 0) {
						foreach ($campaign_products as $campaign_product) {
								$tax_percentage_booking = 0;
								$tax_percentage_amount = 0;
								$campaign_product->tax_percentage_amount = 0; 
								$diff=date_diff(date_create($campaign_product->booked_from),date_create($campaign_product->booked_to));
								$daysCount = $diff->format("%a");
								$daysCountCPM = $daysCount + 1;
								$getproductDetails =Product::where('id', '=', $campaign_product->product_id)->get();
							if (!$getproductDetails->isEmpty()){
								$getproductDetails = $getproductDetails->first();
								$perdayprice = $getproductDetails->default_price/28;
								
								$offerDetails = MakeOffer::where([
									['campaign_id', '=', $campaign_product->campaign_id],
									['status', '=', 20],
								])->get();
								if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
									if(isset($offerDetails) && count($offerDetails)==1){
										foreach($offerDetails as $offerDetails){
											$price_cp = $campaign_product->price;
											$offerprice = $offerDetails->price;
											$priceperday = $price_cp;
											$priceperselectedDates = $priceperday;
											$newpricepercentage = ($priceperselectedDates/$camptot) * 100;
											
											$newofferprice = ($offerprice * ($newpricepercentage))/100;
											if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
												$newofferprice = $newofferprice*$campaign_product->quantity;
											}
											$price = $newofferprice;
										}
									}else{
										$priceperselectedDates = $campaign_product->price;
										if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
											$price = $campaign_product->price*$campaign_product->quantity;
										}else{
											$price = $campaign_product->price;
										}
									}
									if($total_paid != 0){
										if(isset($campaign_product->tax_percentage)){
											$tax_percentage_booking = $campaign_product->tax_percentage;
										}else{
											$tax_percentage_booking = 0;
										}
									}else{
										$tax_percentage_booking = $getproductDetails->tax_percentage;
									}
									if($tax_percentage_booking != 0){
										$tax_percentage_amount = ($price * ($tax_percentage_booking))/100;
									}
									$campaign_product->tax_percentage_amount = round($tax_percentage_amount,2);
									$offershortlistedsum+= $price+$campaign_product->tax_percentage_amount;
								}else{
									if(isset($offerDetails) && count($offerDetails)==1){
										foreach($offerDetails as $offerDetails){
											$price_cp = $getproductDetails->default_price;
											$offerprice = $offerDetails->price;
											///variable_offer///
											$priceperday = $campaign_product->price;
											$priceperselectedDates = $priceperday;
											
											
											$newpricepercentage = ($priceperselectedDates/$camptot) * 100;
											
											$newofferprice = ($offerprice * ($newpricepercentage))/100;
											if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
												$newofferprice = $newofferprice*$campaign_product->quantity;
											}
											$price = $newofferprice;
										}
									}else{
										$priceperselectedDates = $campaign_product->price;
										if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
											$price = $campaign_product->price*$campaign_product->quantity;
										}else{
											$price = $campaign_product->price;
										}
									}
									if($total_paid != 0){
										if(isset($campaign_product->tax_percentage)){
											$tax_percentage_booking = $campaign_product->tax_percentage;
										}else{
											$tax_percentage_booking = 0;
										}
									}else{
										$tax_percentage_booking = $getproductDetails->tax_percentage;
									}
									if($tax_percentage_booking != 0){
										$tax_percentage_amount = ($price * ($tax_percentage_booking))/100;
									}
									$campaign_product->tax_percentage_amount = round($tax_percentage_amount,2);
									$offershortlistedsum+= $price+$campaign_product->tax_percentage_amount;
								}
							}
							$user_campaign->act_budget = $offershortlistedsum;
						}
					}
				}else{
					$user_campaign->act_budget = $act_budget[0]->total_price;
				}
            }
			$gross_fee_price = 0;
			if(isset($user_campaign->gross_fee_percentage) && $user_campaign->gross_fee_percentage > 0){
				$gross_fee_price = ($user_campaign->act_budget*$user_campaign->gross_fee_percentage)/100;
				$user_campaign->act_budget = $user_campaign->act_budget+$gross_fee_price;
			}
            $user_campaign->product_count = $count;
            $user_campaign->paid = $total_paid;
        }
        return response()->json($user_campaigns->values());
    }

      public function getCampaignDetails($campaign_id, $is_ret_fun = false) {
        $user_mongo_jwt = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo_jwt['user_id'])->first();
        $shortlisted_products_arr = [];
        $shortlistedsum = 0;
        $shortlistedsumcpm = 0;
        $offershortlistedsum = 0;
        $offershortlistedsumcpm = 0;
        $cpmsum = 0;
        $negotiatedsum = 0;
        $offercpmsum = 0;
        $impressionSum = 0;
        $impressionSum4 = 0;
        $newofferStripepercentamtSum = 0;
        $newprocessingfeeamtSum = 0;
		$tax_percentage_amount = 0;
		$tax_percentage_booking = 0;
		$tax_percentage_amount_sum = 0;
		$offer_applied = 0;
        $current_time_obj = new \MongoDB\BSON\UTCDateTime();
        $current_time = array_values(get_object_vars($current_time_obj)); 
        $string = substr($current_time[0], 0, -3); // removing last 3 digits from current_time
        // $client = $user->client; 
		// commented on 14-Jul-2021   start
        if (!isset($user->client) || empty($user->client)) {
            //echo 'client';//exit;
            $campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        ['created_by', '=', $user_mongo_jwt['id']],
                    ])->first();
			if (empty($campaign)){
				return response()->json(['status' => 0, 'message' => 'Campaign not found']);
			}
                    //echo 'client';print_r($campaign);exit;
        } else if ($user->client->client_type->type == "bbi") {
            //echo 'bbi';exit;
            $campaign = Campaign::where('id', '=', $campaign_id)->first();
        } else if ($user->client->client_type->type == "owner") {
            //echo 'owner';exit;
            $campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        ['created_by', '=', $user_mongo_jwt['id']],
                    ])->first();
        } else {
            //echo 'no campaign';exit;
            return response()->json(['status' => 0, 'message' => 'Campaign not found']);
        }
		// commented on 14-Jul-2021  end 
		/*$campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        ['created_by', '=', $user_mongo_jwt['id']],
                    ])->first();
		
		if (empty($campaign)){
			return response()->json(['status' => 0, 'message' => 'Campaign not found']);
		}*/
		
        $user_mongo = UserMongo::where('id', '=', $campaign->created_by)->first();
        $campaign->first_name = $user_mongo->first_name;
		$campaign->last_name = $user_mongo->last_name;
		$campaign->company_name = $user_mongo->company_name;
		$campaign->email = $user_mongo->email;
		$campaign->phone = $user_mongo->phone;
        $campaign_products = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
        //echo "<pre>aa"; print_r($campaign_products);exit; 
        $products_arr = [];
        $cancellationArray=[];
        $getcampaigntot = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
        //echo "<pre>aa"; print_r($getcampaigntot);exit;
        $camptot = 0;
		$campaign->total_paid = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('amount'); 
		$tax_percentage_booking_tot = 0;
		$tax_percentage_amount_tot = 0;
		$offerDetails_tot = 0;
        if (isset($getcampaigntot) && count($getcampaigntot) > 0) {
            foreach ($getcampaigntot as $getcampaigntot) {
				$tax_percentage_booking_tot = 0;
				$tax_percentage_amount_tot = 0;
                $getcampaigntotproduct =Product::where('id', '=', $getcampaigntot->product_id)->first();
                $bookedfrom[] = strtotime($getcampaigntot->booked_from);
                $bookedto[] = strtotime($getcampaigntot->booked_to);

                $getproductDetails =Product::where('id', '=', $getcampaigntot->product_id)->first();
                $diff=date_diff(date_create($getcampaigntot->booked_from),date_create($getcampaigntot->booked_to));
                //$diff=date_diff(($booked_from_date),($booked_to_date));
                $daysCount = $diff->format("%a");//exit;
                $daysCountCPM = $daysCount + 1;
				$perdayprice = $getproductDetails->default_price/28;
                if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
                    //echo 'fix';exit;
                    $price = $getcampaigntot->price*$getcampaigntot->quantity;
                    //$priceperday = $price;
                    //$priceperselectedDates = $priceperday; 
                    //$camptot += $priceperselectedDates;
					if($campaign->total_paid != 0){
						if(isset($getcampaigntot->tax_percentage)){
							$tax_percentage_booking_tot = $getcampaigntot->tax_percentage;
						}else{
							$tax_percentage_booking_tot = 0;
						}
					}else{
						$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
					}
					if($tax_percentage_booking_tot != 0){
						$tax_percentage_amount_tot = ($price * ($tax_percentage_booking_tot))/100;
					}
					$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
					$camptot += $price+$tax_percentage_amount_tot;
                }else{
                    //echo 'else';exit;
                    //$price = $getcampaigntot->price; 
                    /*$price = $getproductDetails->default_price;
                    $priceperday = $price/28;//exit;
                    $priceperselectedDates = $priceperday * $daysCountCPM;*/
                    //echo '--daysCountCPM---'.$daysCountCPM;
					
					$priceperselectedDates = $getcampaigntot->price;
					
					if($campaign->total_paid != 0){
						if(isset($getcampaigntot->tax_percentage)){
							$tax_percentage_booking_tot = $getcampaigntot->tax_percentage;
						}else{
							$tax_percentage_booking_tot = 0;
						}
					}else{
						$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
					}
					if($tax_percentage_booking_tot != 0){
						$tax_percentage_amount_tot = ($priceperselectedDates * ($tax_percentage_booking_tot))/100;
					}
					$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
					$camptot += ($priceperselectedDates*$getcampaigntot->quantity)+($tax_percentage_amount_tot*$getcampaigntot->quantity);
                    //$camptot += $price;
                }
//echo '--camptot--'.$camptot;
                //echo '---camptot123---'.$camptot += $getcampaigntot->price;
            }
        }
//exit;
		$products_in_campaign = array();
		$campaign_delete_product_requested_price = 0;
        if (isset($campaign_products) && count($campaign_products) > 0) {
             
            $f = $campaign_products;
            //echo "<pre>";print_r($f);exit;
             $campaigntotal = 0;
             foreach($f as $f){
                 $campaigntotal+= $f->price;
             }
			$campaign_purchase_disable = 0;
            foreach ($campaign_products as $campaign_product) {
                $campaign_product->tax_percentage_amount_shortlist = 0;
				$campaign_product->tax_percentage_amount =0;
				$tax_percentage_amount = 0;
				$tax_percentage_booking = 0;
                $product =Product::where('id', '=', $campaign_product->product_id)->first();
				$products_in_campaign[] = $product;
                $area_time_zone_value = Area::select("area_time_zone","area_time_zone_type")->where('id', '=', $product->area)->first();
				$campaign_product->area_time_zone_type = $area_time_zone_value['area_time_zone_type'];
				
                $booked_from[] = $campaign_product->booked_from;
                $booked_to[] = $campaign_product->booked_to;
                if($product->cancelation =='Yes')
                {
                    $booked_from_date = date('m-d-Y',strtotime($campaign_product->booked_from));
                    $bookedtoval = strtotime($campaign_product->booked_to);
                    if($bookedtoval < $string){
                        //$campaign_product->is_product_expired = 'Product Expired';
                        $campaign_product->is_product_expired = true;
                     }else{
                        //$campaign_product->is_product_expired = 'Product Not Expired';
                        $campaign_product->is_product_expired = false;
                     }

                    $current_date = date('m-d-Y');
                    
                    $getproductDetails =Product::where('id', '=', $campaign_product->product_id)->first();
                    $diff=date_diff(date_create($current_date),date_create($booked_from_date));
                    $daysCount = $diff->format("%a");
                    $daysCountCPM = $date_diff->days + 1;
                    
                    $date_diff=0;
                    
                    if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
                        /*$offerDetails = MakeOffer::where([
                            ['campaign_id', '=', $campaign_product->campaign_id],
                            ['status', '=', 40],
                        ])->get();*/
                        //echo "<pre>offerDetails"; print_r($offerDetails);exit;
                        //$price = $getproductDetails->default_price;
                        $price = $campaign_product->price;
                        $priceperday = $price;
                        $priceperselectedDates = $priceperday;
                
                        //$shortlistedsum+= $sup->price; 
                        $shortlistedsum+= $priceperselectedDates;
                        $campaign_product->price = $priceperselectedDates;
                        $cpmsum+= $campaign_product->cpm;
                        $impressions = $campaign_product->secondImpression;
                        $impressionsperday = (float)($impressions);
                        $impressionsperselectedDates = $impressionsperday;
                        
                        if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                            $impressionsperselectedDates = $impressionsperselectedDates;
                        }else{
                            $impressionsperselectedDates = 1;
                        }
    
                        //$impressionSum+= $product_details->secondImpression;
                        $impressionSum+= $impressionsperselectedDates;
                        $campaign_product->secondImpression = round($impressionsperselectedDates, 2);
                        $cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                        $campaign_product->cpm = $cpmcal;
                        $campaign_product->productbookingid = $campaign_product->id;
                    }else{
                        /*$offerDetails = MakeOffer::where([
                            ['campaign_id', '=', $campaign_product->campaign_id],
                            ['status', '=', 40],
                        ])->get();
                        echo "<pre>offerDetails"; print_r($offerDetails);exit;*/
                        $price = $getproductDetails->default_price;
                        $priceperday = $price/28;
						
						$fixeddays = $daysCountCPM/$product->minimumdays;
 
						//$priceperselectedDates = $priceperday;
						if($daysCountCPM <= $product->minimumdays){
							$priceperselectedDates = $priceperday * $fixeddays;
						}
							$priceperselectedDates = $priceperday * $daysCountCPM;
                
                        //$shortlistedsum+= $sup->price; 
                        $shortlistedsum+= $priceperselectedDates;
                        $campaign_product->price = $priceperselectedDates;
                        $cpmsum+= $campaign_product->cpm;
                        $impressions = $campaign_product->secondImpression;
                        $impressionsperday = (float)($impressions/7);
                        $impressionsperselectedDates = $impressionsperday * $daysCountCPM;
                        
                        if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                            $impressionsperselectedDates = $impressionsperselectedDates;
                        }else{
                            $impressionsperselectedDates = 1;
                        }
                        //$impressionSum+= $product_details->secondImpression;
                        $impressionSum+= $impressionsperselectedDates*$campaign_product->quantity;
                        $campaign_product->secondImpression = round($impressionsperselectedDates, 2);
                        $cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                        $campaign_product->cpm = $cpmcal;
                        $campaign_product->productbookingid = $campaign_product->id;
                    }
                    
                    $cancellationfeeArray = array('0-30'=>35,'30-60'=>20,'61-120'=>10,'121-0'=>0);
                    $cancellationfee =0;
                    if($daysCount >0){
                    foreach($cancellationfeeArray as $key=>$val){
                        $daysrange = explode("-",$key);
                         $mindays = $daysrange[0];
                         $maxdays = $daysrange[1];
                        if($mindays <= $daysCount && $daysCount <= $maxdays && $maxdays!=0 ){
                             $cancellationfee = (($campaign_product->price)*($val/100));
                             
                        }else if($mindays <= $daysCount && $maxdays ==0 ){
                             $cancellationfee = (($campaign_product->price)*($val/100));
                        }
                        
                        $cancellationArray = array('cancellation_charge'=>$cancellationfee,'cancel_remaingdays'=>$daysCount);
                       }
                    }
                }else{
                    //$booked_from_date = date('m-d-Y',strtotime($campaign_product->booked_from));
                    //$booked_to_date = date('m-d-Y',strtotime($campaign_product->booked_to));
                    //dd($booked_from);
                    //echo $current_date = date('m-d-Y');
                    ///echo ($booked_from_date);
                    //echo ($booked_to_date);
                    $getproductDetails =Product::where('id', '=', $campaign_product->product_id)->first();
                    $diff=date_diff(date_create($campaign_product->booked_from),date_create($campaign_product->booked_to));
                    //$diff=date_diff(($booked_from_date),($booked_to_date));
                    $daysCount = $diff->format("%a");//exit;
                    $daysCountCPM = $daysCount + 1;
					
					$perdayprice = $getproductDetails->default_price/28;

                    $bookedtoval = strtotime($campaign_product->booked_to);
                    if($bookedtoval < $string){
                        //$campaign_product->is_product_expired = 'Product Expired';
                        $campaign_product->is_product_expired = true;
                     }else{
                        //$campaign_product->is_product_expired = 'Product Not Expired';
                        $campaign_product->is_product_expired = false;
                     }

                    //$daysCountCPM = $daysCount;
                    //echo "<pre>daysCount"; print_r($daysCount);exit;
                    //echo "<pre>getproductDetails"; print_r($getproductDetails);//exit;
                    //echo "<pre>product"; print_r($campaign_product);exit;
                    if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
                        //echo 'dsdsds111';exit;
                        $offerDetails = MakeOffer::where([
                            ['campaign_id', '=', $campaign_product->campaign_id],
                            ['status', '=', 20],
                        ])->get();

                        if(isset($offerDetails) && count($offerDetails)==1){
                            //echo 'offer exists';exit;
                                foreach($offerDetails as $offerDetails){
                                            $offerprice = $offerDetails->price;
                                            $stripe_percent=$getproductDetails->stripe_percent;
                                            
                                            
                                            $prd_arr = array($getproductDetails->id);
                                            
                                            $getProductBookingID = ProductBooking::where([
                                                ['campaign_id', '=', $campaign_product->campaign_id],
                                            ])->whereIn('product_id', $prd_arr)->get();
                                            //echo "<pre>"; print_r($getProductBookingID);exit;
                                            
                                            $deleteProductStatus = DeleteProduct::where([
                                                ['campaign_id', '=', $campaign_product->campaign_id],
                                                //['status', '=', 101],
                                            ])->whereIn('product_id', $prd_arr)->whereIn('productbookingid', array($campaign_product->id))->orderBy('created_at', 'desc')->first();
                                            //])->get();
                                            

                                            if(isset($deleteProductStatus) && $deleteProductStatus!=''){
                                                //foreach($deleteProductStatus as $deleteProductStatus){
                                                    $campaign_product->deleteProductStatus = $deleteProductStatus->status;
													$campaign_product->deleteProductPrice = $deleteProductStatus->price;
													$campaign_delete_product_requested_price += $deleteProductStatus->price;
                                                //}
                                            }else{
                                                $campaign_product->deleteProductStatus = '';
												$campaign_product->deleteProductPrice = 0;
												$campaign_delete_product_requested_price += 0;
                                            }
                                            
                                            //$price = $getproductDetails->default_price;
                                            $price = $campaign_product->price;

                                            //$price = $campaign_product->price;
                                            $priceperday = $price;
                                            $priceperselectedDates = $priceperday;
                                            $newpricepercentage = ($priceperselectedDates/$camptot) * 100;

                                            //$newofferprice = ($offerprice * ($newpricepercentage))/100;
											if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
												$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
												$newofferprice = $newofferprice_wq*$campaign_product->quantity;
											}else{
												$newofferprice = ($offerprice * ($newpricepercentage))/100;
											}
                                            //$offerpriceperday = $newofferprice/28;//exit;
                                            //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                                            $offerpriceperselectedDates = $newofferprice;
                                            $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
                                            $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                                            $campaign_product->stripe_percent = $stripe_percent;

                                            $negotiatedprice = $getproductDetails->negotiatedCost;
                                            $negotiatedpriceperday = $negotiatedprice;
                                            $negotiatedpriceperselectedDates = $negotiatedpriceperday;

                                }
								$offer_applied = 1;
                            }else{
                                 //$offerprice = $getproductDetails->default_price;
                                 //echo 'no offer exists';exit;
                                $prd_arr = array($getproductDetails->id);
                                
                                $getProductBookingID = ProductBooking::where([
                                                ['campaign_id', '=', $campaign_product->campaign_id],
                                            ])->whereIn('product_id', $prd_arr)->get();
                                            //echo "<pre>"; print_r($getProductBookingID);exit;
                                            
                                $deleteProductStatus = DeleteProduct::where([
                                    ['campaign_id', '=', $campaign_product->campaign_id],
									//['status', '=', 101],
								])->whereIn('product_id', $prd_arr)->whereIn('productbookingid', array($campaign_product->id))->orderBy('created_at', 'desc')->first();
                                //])->get();

                                if(isset($deleteProductStatus) && $deleteProductStatus!=''){
                                    //foreach($deleteProductStatus as $deleteProductStatus){
                                        $campaign_product->deleteProductStatus = $deleteProductStatus->status;
										$campaign_product->deleteProductPrice = $deleteProductStatus->price;
										$campaign_delete_product_requested_price += $deleteProductStatus->price;
                                    //}
                                }else{
                                    $campaign_product->deleteProductStatus = '';
									$campaign_product->deleteProductPrice = 0;
									$campaign_delete_product_requested_price += 0;
                                }

                                 $offerprice = $campaign_product->price;
                                 //$offerprice = $getproductDetails->default_price;
                                 $stripe_percent=$getproductDetails->stripe_percent;
                                 //$price = $getproductDetails->default_price;
                                 $price = $campaign_product->price;
                                 //$price = $campaign_product->price;
                                 $priceperday = $price;//exit;
                                 $priceperselectedDates = $priceperday;
                                 $newpricepercentage = ($priceperselectedDates/$camptot) * 100;
								
							if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
                                $newofferprice = $offerprice*$campaign_product->quantity;
							}else{
								$newofferprice = $offerprice ;
							}
                            //$offerpriceperday = $newofferprice/28;//exit;
                            //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                                $offerpriceperselectedDates = $newofferprice;
                                $campaign_product->stripe_percent = $stripe_percent;
                                
                                $negotiatedprice = $getproductDetails->negotiatedCost;
                                $negotiatedpriceperday = $negotiatedprice;
                                $negotiatedpriceperselectedDates = $negotiatedpriceperday;
                            }
                            
							if($campaign->total_paid != 0){
								if(isset($campaign_product->tax_percentage)){
									$tax_percentage_booking = $campaign_product->tax_percentage;
								}else{
									$tax_percentage_booking = 0;
								}
							}else{
								$tax_percentage_booking = $getproductDetails->tax_percentage;
							}
							if($tax_percentage_booking != 0){
								$campaign_product->tax_percentage_amount_shortlist = ($priceperselectedDates * ($tax_percentage_booking))/100;
								$tax_percentage_amount = ($offerpriceperselectedDates * ($tax_percentage_booking))/100;
							}
                            $campaign_product->tax_percentage_amount = round($tax_percentage_amount,2);        
							$tax_percentage_amount_sum += round($tax_percentage_amount,2);
                            $shortlistedsum+= ($priceperselectedDates+$campaign_product->tax_percentage_amount_shortlist)*$campaign_product->quantity;
							$shortlistedsumcpm += $priceperselectedDates*$campaign_product->quantity;
                            $campaign_product->price = $priceperselectedDates;
            
							$newofferStripepercentamt = (($newofferprice+$tax_percentage_amount) * ($stripe_percent))/100;
							$newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                            $negotiatedsum+= $negotiatedpriceperselectedDates;

							$offershortlistedsum+= $offerpriceperselectedDates+$campaign_product->tax_percentage_amount;
							$offershortlistedsumcpm += $offerpriceperselectedDates;
                            $campaign_product->offerprice = $offerpriceperselectedDates;
                            $cpmsum+= $getproductDetails->cpm;
                            $impressions = $getproductDetails->secondImpression;
                            $impressionsperday = (float)($impressions/7);
                            $impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
                            
                            if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                                $impressionsperselectedDates = $impressionsperselectedDates;
                            }else{
                                $impressionsperselectedDates = 1;
                            }
                            //$impressionSum+= $product_details->secondImpression; 
                            $impressionSum+= $impressionsperselectedDates*$campaign_product->quantity;
                            $campaign_product->secondImpression = round($impressionsperselectedDates, 2);
                            //$cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                            //$offercpmcal = ($offershortlistedsum/$impressionSum) * 1000;
                            $cpmcal = ($priceperselectedDates/$impressionsperselectedDates) * 1000;
                            $offercpmcal = ($offerpriceperselectedDates/$impressionsperselectedDates) * 1000;
                            $campaign_product->cpmperselectedDates = $cpmcal;
                            $campaign_product->offercpmperselectedDates = $offercpmcal;
                            $campaign_product->cpm = $cpmcal;
                            $campaign_product->offercpm = $offercpmcal;
                            $campaign_product->impressionsperselectedDates = $impressionsperselectedDates;
                            $campaign_product->priceperselectedDates = $priceperselectedDates;
                            $campaign_product->negotiatedpriceperselectedDates = $negotiatedpriceperselectedDates;
                            $campaign_product->offerpriceperselectedDates = $offerpriceperselectedDates+$campaign_product->tax_percentage_amount;
                            
                            $campaign_product->new_stripe_percent_amount = $newofferStripepercentamt;
                            $campaign_product->newprocessingfeeamt = $newprocessingfeeamt;
            
										$newofferStripepercentamtSum += (($offerpriceperselectedDates+$campaign_product->tax_percentage_amount)* ($stripe_percent))/100;
										$newprocessingfeeamtSum += $newprocessingfeeamt;
							
                            $campaign_product->productbookingid = $campaign_product->id;

                    }else{
                        //echo 'dsdsds';exit; 
                        $offerDetails = MakeOffer::where([
                            ['campaign_id', '=', $campaign_product->campaign_id],
                            ['status', '=', 20],
                        ])->get();
                        
                        $bookedtoval = strtotime($campaign_product->booked_to);
                        if($bookedtoval < $string){
                            //$campaign_product->is_product_expired = 'Product Expired';
                            $campaign_product->is_product_expired = true;
                        }else{
                            //$campaign_product->is_product_expired = 'Product Not Expired';
                            $campaign_product->is_product_expired = false;
                        }

                        /*$deleteProductStatus = DeleteProduct::where([
                            ['campaign_id', '=', $campaign_product->campaign_id],
                            ['status', '=', 101],
                        ])->get();
                        //echo '<pre>'; print_r($deleteProductStatus); exit; 
                        
                        foreach($deleteProductStatus as $deleteProductStatus){
                                                     $del_pro_status = $deleteProductStatus->status;
                                                     //$del_pro_status = $campaign_product->status;
                                                     //echo '<pre>'; print_r($del_pro_status);           
                        }*/
                        //exit;
                        if(isset($offerDetails) && count($offerDetails)==1){
                                                foreach($offerDetails as $offerDetails){
                                                     $offerprice = $offerDetails->price;
                                                     $stripe_percent=$getproductDetails->stripe_percent;
                                                     
                                                $price = $getproductDetails->default_price;
                                                $prd_arr = array($getproductDetails->id);                                                
                                                $deleteProductStatus = DeleteProduct::where([
                                                    ['campaign_id', '=', $campaign_product->campaign_id],
													//['status', '=', 101],
												])->whereIn('product_id', $prd_arr)->whereIn('productbookingid', array($campaign_product->id))->orderBy('created_at', 'desc')->first();
                                               // ])->get(); 

                                                if(isset($deleteProductStatus) && $deleteProductStatus!=''){
                                                    //foreach($deleteProductStatus as $deleteProductStatus){
                                                        $campaign_product->deleteProductStatus = $deleteProductStatus->status; 
														$campaign_product->deleteProductPrice = $deleteProductStatus->price;
														$campaign_delete_product_requested_price += $deleteProductStatus->price;
                                                    //}
                                                }else{
                                                    $campaign_product->deleteProductStatus = '';
													$campaign_product->deleteProductPrice = 0;
													$campaign_delete_product_requested_price += 0;
                                                }   

                                                //$price = $campaign_product->price; 
                                                //$priceperday = $price/28;//exit;
                                                //echo '---camptot--'.$camptot;
                                                //$priceperselectedDates = $priceperday * $daysCountCPM;
												///variable_offer///
												$priceperday = $campaign_product->price;
												$priceperselectedDates = $priceperday;
												
                                                $newpricepercentage = ($priceperselectedDates/$camptot) * 100;
                                                //$newofferprice = ($offerprice * ($newpricepercentage))/100;//exit;
												if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
													$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
													$newofferprice = $newofferprice_wq*$campaign_product->quantity;
												}else{
													$newofferprice = ($offerprice * ($newpricepercentage))/100;
												}
                                                //$offerpriceperday = $newofferprice/28;//exit;
                                                //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                                                $offerpriceperselectedDates = $newofferprice;
                                                $campaign_product->stripe_percent = $stripe_percent;

                                                $negotiatedprice = $getproductDetails->negotiatedCost;
                                                $negotiatedpriceperday = $negotiatedprice/28;
                                                $negotiatedpriceperselectedDates = $negotiatedpriceperday * $daysCountCPM;
                                                   }
												   $offer_applied = 1;
                                                }else{
                                                     //$offerprice = $getproductDetails->default_price;
                                                $offerprice = $campaign_product->price;
                                                $stripe_percent=$getproductDetails->stripe_percent;
                                                $price = $campaign_product->price;
                                                //$price = $campaign_product->price;
                                                $prd_arr = array($getproductDetails->id);
                                                $deleteProductStatus = DeleteProduct::where([
                                                    ['campaign_id', '=', $campaign_product->campaign_id],
													//['status', '=', 101],
												])->whereIn('product_id', $prd_arr)->whereIn('productbookingid', array($campaign_product->id))->orderBy('created_at', 'desc')->first();
                                                //])->get(); 
                                                if(isset($deleteProductStatus) && $deleteProductStatus!=''){
                                                    //foreach($deleteProductStatus as $deleteProductStatus){
                                                        $campaign_product->deleteProductStatus = $deleteProductStatus->status;
														$campaign_product->deleteProductPrice = $deleteProductStatus->price;
														$campaign_delete_product_requested_price += $deleteProductStatus->price;
                                                    //}
                                                }else{
                                                    $campaign_product->deleteProductStatus = '';
													$campaign_product->deleteProductPrice = 0;
													$campaign_delete_product_requested_price += 0;
                                                }   
                                                
                                                $priceperday = $price;//exit;
                                                $priceperselectedDates = $priceperday;
                                                $newpricepercentage = ($priceperselectedDates/$camptot) * 100;
                        
												if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
                                                $newofferprice = $offerprice*$campaign_product->quantity;
												}else{
													$newofferprice = $offerprice;
												}
                                                //$offerpriceperday = $newofferprice/28;//exit;
                                                //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                                                $offerpriceperselectedDates = $newofferprice;
                                                $campaign_product->stripe_percent = $stripe_percent;

                                                $negotiatedprice = $getproductDetails->negotiatedCost;
                                                $negotiatedpriceperday = $negotiatedprice/28;
                                                $negotiatedpriceperselectedDates = $negotiatedpriceperday * $daysCountCPM;
                                                }
                                                
                                                //if($daysCountCPM <= $product->minimumdays){
												//	$priceperselectedDates = $priceperday * $product->minimumdays;
												//}
                                                
												if($campaign->total_paid != 0){
													if(isset($campaign_product->tax_percentage)){
														$tax_percentage_booking = $campaign_product->tax_percentage;
													}else{
														$tax_percentage_booking = 0;
													}
												}else{
													$tax_percentage_booking = $getproductDetails->tax_percentage;
												}
												if($tax_percentage_booking != 0){
													$campaign_product->tax_percentage_amount_shortlist = ($priceperselectedDates * ($tax_percentage_booking))/100;
													$tax_percentage_amount = ($offerpriceperselectedDates * ($tax_percentage_booking))/100;
												}
												$campaign_product->tax_percentage_amount = round($tax_percentage_amount,2);
												$tax_percentage_amount_sum += round($tax_percentage_amount,2);
                                                $shortlistedsum+= ($priceperselectedDates+$campaign_product->tax_percentage_amount_shortlist)*$campaign_product->quantity;
                                                $shortlistedsumcpm += $priceperselectedDates*$campaign_product->quantity;
                                                $negotiatedsum+= $negotiatedpriceperselectedDates;
                                                $newofferStripepercentamt = (($newofferprice+$tax_percentage_amount) * ($stripe_percent))/100;
                                                $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
												
                                                $campaign_product->price = $priceperselectedDates;
                        
												$offershortlistedsum+= $offerpriceperselectedDates+$campaign_product->tax_percentage_amount;
												$offershortlistedsumcpm += $offerpriceperselectedDates;
                                                $campaign_product->offerprice = $offerpriceperselectedDates;
                                                $cpmsum+= $getproductDetails->cpm;
                                                $impressions = $getproductDetails->secondImpression;
                                                $impressionsperday = (float)($impressions/7);
                                                $impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
                                                
                                                if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                                                    $impressionsperselectedDates = $impressionsperselectedDates;
                                                }else{
                                                    $impressionsperselectedDates = 1;
                                                }
                                                //$impressionSum+= $product_details->secondImpression; 
                                                $impressionSum+= $impressionsperselectedDates*$campaign_product->quantity;
                                                $campaign_product->secondImpression = round($impressionsperselectedDates, 2);
                                                //$cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                                                //$offercpmcal = ($offershortlistedsum/$impressionSum) * 1000;
                                                $cpmcal = ($priceperselectedDates/$impressionsperselectedDates) * 1000;
                                                $offercpmcal = ($offerpriceperselectedDates/$impressionsperselectedDates) * 1000;
                                                $campaign_product->cpmperselectedDates = $cpmcal;
                                                $campaign_product->offercpmperselectedDates = $offercpmcal;
                                                $campaign_product->cpm = $cpmcal;
                                                $campaign_product->offercpm = $offercpmcal;
                                                $campaign_product->impressionsperselectedDates = $impressionsperselectedDates;
                                                $campaign_product->priceperselectedDates = $priceperselectedDates;
                                                $campaign_product->negotiatedpriceperselectedDates = $negotiatedpriceperselectedDates;
                                                $campaign_product->offerpriceperselectedDates = $offerpriceperselectedDates+$campaign_product->tax_percentage_amount;
                                                
                                                $campaign_product->new_stripe_percent_amount = $newofferStripepercentamt;
                                                $campaign_product->newprocessingfeeamt = $newprocessingfeeamt;
                        
                                                
												$newofferStripepercentamtSum += (($offerpriceperselectedDates+$campaign_product->tax_percentage_amount)* ($stripe_percent))/100;
												$newprocessingfeeamtSum += $newprocessingfeeamt;
												
                                                $campaign_product->productbookingid = $campaign_product->id;
//exit;
                       
                    }
                }
				$sold_out_status = array('sold_status' => 0);
				if($campaign->status <= 500){
					$available_quantity = $product->unitQty;
					$campaign_sold_out = ProductBooking::select('campaign_id','quantity','id')->where('campaign_id', '!=', $campaign_product->campaign_id)->where('product_id','=',$campaign_product->product_id)->where('booked_from','<=',iso_to_mongo_date($campaign_product->booked_from))->where('booked_to','>=',iso_to_mongo_date($campaign_product->booked_to))->where('product_status','!=',100)->where('product_status','!=',400)->where('product_status','!=',700)->where('quantity', '!=',  '')->groupBy('group_slot_id')->get([DB::raw('MAX(quantity) as quantity')])->toArray();
					if(isset($campaign_sold_out) && !empty($campaign_sold_out)){
						$sum_quantity = 0;
						if(!empty($campaign_sold_out)){
							$campaign_payment_status_flag = 0;
							foreach($campaign_sold_out as $key => $value){
								$delete_product_status = DeleteProduct::where([
																	['campaign_id', '=', $value['campaign_id']],
																	['status', '=', DeleteProduct::$PRODUCT_STATUS['confirm-delete-product-from-campaign']],
																])->whereIn('product_id', array($campaign_product->product_id))->whereIn('productbookingid', array($value['id']))->orderBy('created_at', 'desc')->first();
								if($value['campaign_id'] != ''){
									$campaign_delete = Campaign::select("status")->where('id', '=', $value['campaign_id'])->first();
									if(empty($delete_product_status) && ($campaign_delete->status != 1200)){
										$sum_quantity += $value['quantity'];
									}
								}
								if(isset($value['campaign_id']) && !empty($value['campaign_id'])){
									$campaign_payment_status = CampaignPayment::select('campaign_id')->where('campaign_id', '=', $value['campaign_id'])->get()->toArray();
									if($campaign_payment_status_flag == 0 && isset($campaign_payment_status) && !empty($campaign_payment_status)){
										$campaign_payment_status_flag = 1;
									}
								}
							}
						}
						$available_quantity = $product->unitQty-$sum_quantity;
						if($available_quantity >= 0){
							$available_quantity = $available_quantity;
						}else{
							$available_quantity = 0;
						}
						$campaign_payment_status = array();
						if($available_quantity <  $campaign_product->quantity){
							if($campaign_payment_status_flag == 1){
								$sold_out_status = array('sold_status' => 1);
								$campaign_purchase_disable = 1;
							}
						}
					}
				}
               /*array_push($products_arr, array_merge(Product::where('id', '=', $campaign_product->product_id)->first()->toArray(), DeleteProduct::where([
                            ['campaign_id', '=', $campaign_product->campaign_id],
                        ])->get()->toArray(), $campaign_product->toArray(),$cancellationArray));*/
						
				$offer_comments = Offer_admin_seller_comments::where('booking_id', '=', $campaign_product->productbookingid)->orderBy('created_at', 'ASC')->get();
				if(isset($offer_comments) && count($offer_comments) > 0){
					$first_offer_id = $offer_comments[0]->offer_id;
					foreach($offer_comments as $keys => $values){
						if($values->offer_id == $first_offer_id){
							$values->offer_type = 'First Offer Details';
						}else{
							$values->offer_type = 'Second Offer Details';
						}
						
						if($values->status == 1){
							$values->final_status = 'Send Request';
							$values->Sender = 'Admin';
						}else if($values->status == 2){
							$values->final_status = 'Approved';
							$values->Sender = 'Owner';
						}else if($values->status == 3){
							$values->final_status = 'Rejected';
							$values->Sender = 'Owner';
						}
					}
					$offer_comments_array = array('offer_comments' => $offer_comments->toArray());
				}else{
					$offer_comments_array = array('offer_comments' => array());
				}
                array_push($products_arr, array_merge(Product::where('id', '=', $campaign_product->product_id)->first()->toArray(), $campaign_product->toArray(),$cancellationArray,$sold_out_status,$offer_comments_array));
            }
            $campaign->products = $products_arr;
            $campaign->actbudg = $products_arr;
			$campaign->products_in_campaign = $products_in_campaign;
			$campaign->campaign_purchase_disable_status = $campaign_purchase_disable;

            $quote_change = CampaignQuoteChange::select('remark', 'type')->where('campaign_id', '=', $campaign_id)->get();
            //$quote_change = CampaignQuoteChange::select('remark','type')->where('campaign_id', '=', $campaign_id)->orderBy('created_at', 'desc')->get();
            if (!empty($quote_change)) {
                $campaign->quote_change = $quote_change;
            }

            // get campaign actual budget
            // $data = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
            // dd($data);
            $act_budget = ProductBooking::raw(function($collection) use ($campaign_id) {
                        return $collection->aggregate(
                                        [
                                            [
                                                '$match' =>
                                                [
                                                    "campaign_id" => $campaign_id
                                                ]
                                            ],
                                            [
                                                '$group' =>
                                                [                                                                                             
                                                    '_id' => '$campaign_id',
                                                    'total_price' => [
                                                        '$sum' => '$admin_price'
                                                    ]
                                                ]
                                            ]
                                        ]
                        );
                    });
                    $cancelledCampaignStatus =CancelCampaign::where('campaign_id', '=', $campaign_id)->get();
                    if(isset($cancelledCampaignStatus) && ($cancelledCampaignStatus!='')){
                        foreach($cancelledCampaignStatus as $cancelledCampaignStatus){
                            $campaign->cancelledCampaignStatus = $cancelledCampaignStatus->status;
                        }
                    }else{
                        $campaign->cancelledCampaignStatus = '';
                    }
                    //echo "<pre>cancelledCampaignStatus";print_r($cancelledCampaignStatus);exit;
                $res = array_sum(array_map(function($item) { 
					if(isset($item['quantity']) && $item['quantity'] != '' && $item['quantity'] != 0){
						return $item['price']*$item['quantity']; 
					}else{
						return $item['price']; 
					}     
                }, $campaign->actbudg));
            //echo "<pre>act_budget";print_r($res);exit;
            /*$campaign->act_budget = $act_budget[0]->total_price;
            
            if($campaign->act_budget == '0'){
                //dd(123)
                  $campaign->act_budget  = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('owner_price');
            }
            //dd($campaign->act_budget);
            if($campaign->act_budget == '0'){
                  $campaign->act_budget  = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('price');
            }*/
            /*$gststatus = isset($campaign->gststatus)?$campaign->gststatus:0;
                if($gststatus ==1){
                    $campaign->totalamount = $campaign->act_budget+round(($campaign->act_budget*(0.18)),2);
                }
                else{*/
				$campaign->gross_fee_price = 0;
				if(isset($campaign->gross_fee_percentage) && $campaign->gross_fee_percentage > 0){
					$campaign->gross_fee_price = ($offershortlistedsum*$campaign->gross_fee_percentage)/100;
					$newofferStripepercentamtGross = ($newofferStripepercentamtSum*$campaign->gross_fee_percentage)/100;
					$newofferStripepercentamtSum = $newofferStripepercentamtGross+$newofferStripepercentamtSum;
					$newprocessingfeeamtGross = ($newprocessingfeeamtSum*$campaign->gross_fee_percentage)/100;
					$newprocessingfeeamtSum = $newprocessingfeeamtSum+$newprocessingfeeamtGross;
				}else{
					$campaign->gross_fee_price = 0;
				}
				$campaign->shortlistedsum = $shortlistedsum;
				if($offershortlistedsum!=0){
					$campaign->act_budget = $offershortlistedsum+$campaign->gross_fee_price;
					$campaign->totalamount = $offershortlistedsum+$campaign->gross_fee_price; 
				}else{
					$campaign->act_budget = $res;
                    $campaign->totalamount = $campaign->act_budget;
				}
                //}
                 
                 
                 $campaign->refunded_amount = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('refunded_amount');
                 
                 $campaign->bal_amount_available_with_amp = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('bal_amount_available_with_amp');
                 
                 //$campaign->payments = CampaignPayment::where('campaign_id', '=', $campaign->id)->get();
                 //echo "<pre>"; print_r($campaign->refunded_amount);exit;
                // echo 'ddddddddddd';exit;
                 $impressionSum4 = round($impressionSum, 2); /* As discussed with Richard on July 14) */
                 if($impressionSum4>0){
                    $cpmval = ($shortlistedsumcpm/$impressionSum4) * 1000;
                    $offercpmval = ($offershortlistedsumcpm/$impressionSum4) * 1000;
					$grosscpmval = (($offershortlistedsumcpm+$campaign->gross_fee_price)/$impressionSum4) * 1000;
                 }else{
                     $cpmval = 0;
                     $offercpmval = 0;
					 $grosscpmval = 0;
                 }
			
                 $campaign->negotiatedsum = $negotiatedsum;
                 $campaign->cpmval = $cpmval;

                 $campaign->offershortlistedsum = $offershortlistedsum;
				 $campaign->grossshortlistedsum = $offershortlistedsum+$campaign->gross_fee_price;
                 $campaign->offercpmval = $offercpmval;
				 $campaign->grosscpmval = $grosscpmval;

				 if($offershortlistedsum != 0){
					$campaign->campaign_cpmval = (($offershortlistedsumcpm+$campaign->gross_fee_price)/$impressionSum4) * 1000;
				 }else{
					 $campaign->campaign_cpmval = (($shortlistedsumcpm+$campaign->gross_fee_price)/$impressionSum4) * 1000;
				 }
                 $campaign->impressionSum = $impressionSum4;

                 $campaign->newofferStripepercentamtSum = $newofferStripepercentamtSum;
                 $campaign->newprocessingfeeamtSum = $newprocessingfeeamtSum;
                 if($offershortlistedsum!=0){
                    $campaign->percentagevalue = ($newofferStripepercentamtSum * 100)/($offershortlistedsum+$campaign->gross_fee_price);
                 }else{
                    $campaign->percentagevalue=5;
                 }
                 $campaign->offer_applied_res = $offer_applied;
                 $campaign->tax_percentage_amount_sum = $tax_percentage_amount_sum;
				 $campaign->paid_percentage = $campaign->total_paid*100/($campaign->totalamount-$campaign->gross_fee_price-$tax_percentage_amount_sum);
				 $campaign->rfp_search_criteria_preview = RFPSearchCriteria::where('campaign_id', '=', $campaign->id)->first();
                 $campaign->finalpurchasepayment = $newofferStripepercentamtSum + $newprocessingfeeamtSum;
                 $campaign->campaign_delete_product_requested_price = $campaign_delete_product_requested_price;

        }else{
			$campaign->rfp_search_criteria_preview = RFPSearchCriteria::where('campaign_id', '=', $campaign->id)->first();
			$area_full_details = array();
			$rfp_search_criteria_preview_dma_dates = array();
			if(isset($campaign->rfp_search_criteria_preview['dma_area'])){
				foreach($campaign->rfp_search_criteria_preview['dma_area'] as $key => $value){
					$area_full_details[] = Area::where('id', '=', $value)->first();
					if(!is_null($area_full_details[$key])){
						$area_time_zone_type = $area_full_details[$key]['area_time_zone_type'];
						if(!is_null($area_time_zone_type)){
							$explode_dates  = @explode('::',$campaign->rfp_search_criteria_preview['dma_dates'][$key]);
							$start_time = new DateTime( $explode_dates[0] );
							$end_time = new DateTime( $explode_dates[1] );
							$laTimezone = new DateTimeZone($area_time_zone_type);
							$start_time->setTimeZone( $laTimezone );
							$end_time->setTimeZone( $laTimezone );
							$start_time->format( 'Y-m-d H:i:s' );
							$end_time->format( 'Y-m-d H:i:s' );	
							$rfp_search_criteria_preview_dma_dates[] = $start_time->format( 'Y-m-d' ).'::'.$end_time->format( 'Y-m-d' );
						}							
					}
				}
			}
			$campaign->area_rfp_details = $area_full_details;
			if(!empty($rfp_search_criteria_preview_dma_dates)){
				$campaign->rfp_search_criteria_preview['dma_dates'] = $rfp_search_criteria_preview_dma_dates;
			}
		}
        //}
        if(isset($booked_from) && !empty($booked_from)) {$campaign->startDate =  min($booked_from);}
        if(isset($booked_to) && !empty($booked_to)) {$campaign->endDate = max($booked_to);}
        
        if ( $is_ret_fun ) {
            return $campaign;
        }
        return response()->json($campaign);
    }

    public function addProductToCampaign() {
        if (isset($this->input['shortlisted_products'])) {
            $this->validate($this->request, [
                'campaign_id' => 'required',
                'shortlisted_products' => 'required',
                    ], [
                'campaign_id.required' => 'Campaign Id is required',
                'shortlisted_products.required' => 'Product Id is required',
                    ]
            );
        } else {
            $this->validate($this->request, [
                'campaign_id' => 'required',
                'product_id' => 'required',
                    ], [
                'campaign_id.required' => 'Campaign Id is required',
                'product_id.required' => 'Product Id is required',
                    ]
            );
        }
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user_internal = User::where('id', '=', $user_mongo['user_id'])->first();
        if (isset($user_internal->client)) {
            $user_type = $user_internal->client->client_type->type;
        } else {
            $user_type = "basic";
        }
        if ($user_type == "basic") {
            $campaign = Campaign::where([
                        ['id', '=', $this->input['campaign_id']],
                        ['created_by', '=', $user_mongo['id']]
                    ])->first();
            if (!isset($campaign) || empty($campaign)) {
                return response()->json(["status" => 0, "message" => "campaign referred not found in the database."]);
            } else {
                if ($campaign->from_suggestion) {
                    return response()->json(["status" => "0", "message" => "You can not add a product to a campaign you asked suggestion for. Please create a change-request instead."]);
                } else {


                    if ($campaign->status != Campaign::$CAMPAIGN_STATUS['campaign-preparing'] && $campaign->status != Campaign::$CAMPAIGN_STATUS['campaign-created'] && $campaign->status != Campaign::$CAMPAIGN_STATUS['quote-requested'] && $campaign->status != Campaign::$CAMPAIGN_STATUS['quote-given'] && $campaign->status != Campaign::$CAMPAIGN_STATUS['change-requested'] && $campaign->status != Campaign::$CAMPAIGN_STATUS['booking-requested'] && $campaign->status != Campaign::$CAMPAIGN_STATUS['rfp-campaign']) {
                        return response()->json(["status" => "0", "message" => "You can not add a product to a campaign when any kind of admin approval is pending."]);
                    }
                }
            }
        } else if ($user_type == "bbi") {
            $campaign = Campaign::where([
                        ['id', '=', $this->input['campaign_id']],
                        ['type', '<>', Campaign::$CAMPAIGN_USER_TYPE['owner']],
                    ])->first();
            if (!isset($campaign) || empty($campaign)) {
                return response()->json(["status" => 0, "message" => "campaign referred not found in the database."]);
            } else
            if (!($campaign->status < Campaign::$CAMPAIGN_STATUS['quote-given'] || $campaign->status == Campaign::$CAMPAIGN_STATUS['change-requested'])) {
                return response()->json(["status" => "0", "message" => "You can not add a product to a campaign that's pending from any kind of user approval."]);
            }
        } else if ($user_type == "owner") {
            $campaign = Campaign::where([
                        ['id', '=', $this->input['campaign_id']],
                        ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['owner']],
                        ['client_mongo_id', '=', $user_mongo['client_mongo_id']]
                    ])->first();
            if (!isset($campaign) || empty($campaign)) {
                return response()->json(["status" => 0, "message" => "campaign referred not found in the database."]);
            } else if ($campaign->status >= Campaign::$CAMPAIGN_STATUS['booking-requested']) {
                return response()->json(["status" => "0", "message" => "You can not add a product to this campaign at this stage."]);
            }
        } else {
            return response()->json(["status" => "0", "message" => "Invalid user."]);
        }

        if (isset($this->input['shortlisted_products']) && is_array($this->input['shortlisted_products'])) {
            $products = $this->input['shortlisted_products'];
			$rand = substr(str_shuffle(str_repeat("ABCDEFGHJKLMNPQRSTUVWXYZ", 3)), 0, 3);
        	$group_id ="AMP".date('Ymd').$rand;
            $sucess = 'true';
            foreach ($products as $Key => $val) {
                // print_r( $val);
                // check if product already exists in this campaign
				
                $shortlisted = ShortListedProduct::where('id', '=', $val)->first();
                /// print_r($shortlisted);
                $product = Product::where('id', '=', $shortlisted->product_id)->first();
				
					$locked_products = ProductBooking::where([
								['product_id', '=', $product->id],
								['product_status', '=', ProductBooking::$PRODUCT_STATUS['scheduled']],
							])->get();
					if (count($locked_products) > 0) {
						foreach ($locked_products as $locked_product) {
							if ($locked_product->booked_from <= $campaign->start_date &&
									$locked_product->booked_to >= $campaign->end_date && !empty($campaign->start_date) && !empty($campaign->end_date)) {
								return response()->json(["status" => "0", "message" => "This product is unavailable for the entire duration of campaign."]);
							}
						}
					}
					
				$alreadyCampaignProd = new ProductBooking;
                $alreadyCampaignProd = ProductBooking::where([
                            ['campaign_id', '=', $this->input['campaign_id']],
                            ['product_id', '=', $product->id],
                            ['booked_from', '<=', iso_to_mongo_date($shortlisted->to_date)],
                            ['booked_to', '>=', iso_to_mongo_date($shortlisted->from_date)]
                        ])->first();
                if (isset($alreadyCampaignProd) && !empty($alreadyCampaignProd)) {
                    //return response()->json(["status" => "0", "message" => "This product is already added in this campaign."]);
					$productBooked = ProductBooking::where('product_id', '=',  $product->id)->where('quantity', '!=',  '')->get()->toArray();

					$available_quantity = $product->unitQty;
					if(!empty($productBooked)){
						
						$productBooked_last = ProductBooking::select("quantity","campaign_id","id")->where('product_id', '=',  $product->id)->where('booked_from','<=',iso_to_mongo_date($shortlisted->to_date))->where('booked_to','>=',iso_to_mongo_date($shortlisted->from_date))->where('product_status','!=',100)->where('product_status','!=',400)->where('product_status','!=',700)->where('quantity', '!=',  '')->groupBy('group_slot_id')->get([DB::raw('MAX(quantity) as quantity')])->toArray();
						$sum_quantity = 0;
						if(!empty($productBooked_last)){
							foreach($productBooked_last as $key => $value){
								$delete_product_status = DeleteProduct::where([
																	['campaign_id', '=', $value['campaign_id']],
																	['status', '=', DeleteProduct::$PRODUCT_STATUS['confirm-delete-product-from-campaign']],
																])->whereIn('product_id', array($product->id))->whereIn('productbookingid', array($value['id']))->orderBy('created_at', 'desc')->first();
								if($value['campaign_id'] != ''){
									$campaign_delete = Campaign::select("status")->where('id', '=', $value['campaign_id'])->first();
									if(empty($delete_product_status) && ($campaign_delete->status != 1200)){
										$sum_quantity += $value['quantity'];
									}
								}
							}
						}
						$available_quantity = $product->unitQty-$sum_quantity;
						if($available_quantity >= 0){
							$available_quantity = $available_quantity;
						}else{
							$available_quantity = 0;
						}
					}
					$alreadyCampaignProd_quantity = isset($shortlisted->quantity) ? $alreadyCampaignProd->quantity+$shortlisted->quantity : $alreadyCampaignProd->quantity+1;
					if($alreadyCampaignProd_quantity <= $available_quantity){
						$final_add_quantity = $alreadyCampaignProd_quantity;
					}else{
						$final_add_quantity = $available_quantity;
					}
					$alreadyCampaignProd->quantity = $final_add_quantity;
					$alreadyCampaignProd->group_slot_id = $alreadyCampaignProd->group_slot_id;
					if (!$alreadyCampaignProd->save()) {
						$sucess = 'false';
						break;
					} else {
						$shortlisted->delete();
					}
                }else{
					
					$alreadyCampaignProdSingle = ProductBooking::where([
                            ['campaign_id', '=', $this->input['campaign_id']],
                            ['product_id', '=', $product->id]
                        ])->first();
						
					
					$campaign_product = new ProductBooking;

					$campaign_product->id = uniqid();
					$campaign_product->campaign_id = $campaign->id;
					$campaign_product->product_id = $product->id;
					$campaign_product->product_status = ProductBooking::$PRODUCT_STATUS['proposed'];
					$campaign_product->product_owner = $product->client_mongo_id;
					$campaign_product->booked_from = iso_to_mongo_date($shortlisted->from_date);
					$campaign_product->booked_to = iso_to_mongo_date($shortlisted->to_date);
					//$campaign_product->price = $product->default_price;
					$campaign_product->price = $shortlisted->price;
					
					$campaign_product->quantity = isset($shortlisted->quantity) ? $shortlisted->quantity : "1";
					if(isset($alreadyCampaignProdSingle) && !empty($alreadyCampaignProdSingle)){
						$campaign_product->group_slot_id = $alreadyCampaignProdSingle->group_slot_id;
					}else{
						$campaign_product->group_slot_id = isset($shortlisted->group_slot_id) ? $shortlisted->group_slot_id : "0";
					}
					if (!$campaign_product->save()) {
						$sucess = 'false';
						break;
					} else {
						$shortlisted->delete();
					}
				}
            }
            if ($sucess == 'true') {
                return response()->json(["status" => "1", "message" => "Product added to campaign successfully."]);
            } else {
                return response()->json(["status" => "0", "message" => "There was an error while adding the product to campaign."]);
            }
        } else if (isset($this->input['dates'])) {
            $product_id = $this->input['product_id'];
            $campaign_id = $this->input['campaign_id'];
if(isset($this->input['startDate'])&& isset($this->input['endDate'])){
     $product = Product::where('id', '=', $product_id)->first();
                $new_booking = new ProductBooking;
                $new_booking->id = uniqid();
                $new_booking->campaign_id = $campaign_id;
                $new_booking->product_id = $product_id;
                $new_booking->booked_from = iso_to_mongo_date($this->input['startDate']);
                $new_booking->booked_to = iso_to_mongo_date($this->input['endDate']);
                //$new_booking->price = $product->default_price;
                $new_booking->price = $shortlisted->price;
                $new_booking->product_owner = $product->client_mongo_id;
                $new_booking->product_status = ProductBooking::$PRODUCT_STATUS['proposed'];
				$new_booking->quantity = isset($shortlisted->quantity) ? $shortlisted->quantity : "1";
				$new_booking->group_slot_id = isset($shortlisted->group_slot_id) ? $shortlisted->group_slot_id : "0";
                $new_booking->save();
                $sucess = true;
}else{
            foreach ($this->input['dates'] as $dr) {
                $product = Product::where('id', '=', $product_id)->first();
                $new_booking = new ProductBooking;
                $new_booking->id = uniqid();
                $new_booking->campaign_id = $campaign_id;
                $new_booking->product_id = $product_id;
                $new_booking->booked_from = iso_to_mongo_date($dr['startDate']);
                $new_booking->booked_to = iso_to_mongo_date($dr['endDate']);
                //$new_booking->price = $product->default_price;
                $new_booking->price = $shortlisted->price;
                $new_booking->product_owner = $product->client_mongo_id;
                $new_booking->product_status = ProductBooking::$PRODUCT_STATUS['proposed'];
				$new_booking->quantity = isset($shortlisted->quantity) ? $shortlisted->quantity : "1";
				$new_booking->group_slot_id = isset($shortlisted->group_slot_id) ? $shortlisted->group_slot_id : "0";
                $new_booking->save();
                $sucess = true;
}
}
            if ($sucess == 'true') {
                return response()->json(["status" => "1", "message" => "Product added to campaign successfully."]);
            } else {
                return response()->json(["status" => "0", "message" => "There was an error while adding the product to campaign."]);
            }
        } else {
            // check if product already exists in this campaign
			
			$rand = substr(str_shuffle(str_repeat("ABCDEFGHJKLMNPQRSTUVWXYZ", 3)), 0, 3);
        	$group_id ="AMP".date('Ymd').$rand;
			
            $shortlisted = ShortListedProduct::where('id', '=', $this->input['product_id'])->first();
            $product = Product::where('id', '=', $shortlisted->product_id)->first();
			
            $locked_products = ProductBooking::where([
                        ['product_id', '=', $product->id],
                        ['product_status', '=', ProductBooking::$PRODUCT_STATUS['scheduled']],
                    ])->get();
            if (count($locked_products) > 0) {
                foreach ($locked_products as $locked_product) {
                    if ($locked_product->from_date <= $campaign->start_date &&
                            $locked_product->to_date >= $campaign->end_date && !empty($campaign->start_date) && !empty($campaign->end_date)) {
                        return response()->json(["status" => "0", "message" => "This product is unavailable for the entire duration of campaign."]);
                    }
                }
            }
			
				$alreadyCampaignProd = new ProductBooking;
				$alreadyCampaignProd = ProductBooking::where([
                        ['campaign_id', '=', $this->input['campaign_id']],
                        ['product_id', '=', $product->id],
                        ['booked_from', '<=', iso_to_mongo_date($shortlisted->to_date)],
                        ['booked_to', '>=', iso_to_mongo_date($shortlisted->from_date)]
                    ])->first();
				if (isset($alreadyCampaignProd) && !empty($alreadyCampaignProd)) {
					//return response()->json(["status" => "0", "message" => "This product is already added in this campaign."]);
					$productBooked = ProductBooking::where('product_id', '=',  $product->id)->where('quantity', '!=',  '')->get()->toArray();

					$available_quantity = $product->unitQty;
					if(!empty($productBooked)){
						
						$productBooked_last = ProductBooking::select("quantity","campaign_id","id")->where('product_id', '=',  $product->id)->where('booked_from','<=',iso_to_mongo_date($shortlisted->to_date))->where('booked_to','>=',iso_to_mongo_date($shortlisted->from_date))->where('product_status','!=',100)->where('product_status','!=',400)->where('product_status','!=',700)->where('quantity', '!=',  '')->groupBy('group_slot_id')->get([DB::raw('MAX(quantity) as quantity')])->toArray();
						$sum_quantity = 0;
						if(!empty($productBooked_last)){
							foreach($productBooked_last as $key => $value){
								$delete_product_status = DeleteProduct::where([
																	['campaign_id', '=', $value['campaign_id']],
																	['status', '=', DeleteProduct::$PRODUCT_STATUS['confirm-delete-product-from-campaign']],
																])->whereIn('product_id', array($product->id))->whereIn('productbookingid', array($value['id']))->orderBy('created_at', 'desc')->first();
								if($value['campaign_id'] != ''){
									$campaign_delete = Campaign::select("status")->where('id', '=', $value['campaign_id'])->first();
									if(empty($delete_product_status) && ($campaign_delete->status != 1200)){
										$sum_quantity += $value['quantity'];
									}
								}
							}
						}
						$available_quantity = $product->unitQty-$sum_quantity;
						if($available_quantity >= 0){
							$available_quantity = $available_quantity;
						}else{
							$available_quantity = 0;
						}
					}
					$alreadyCampaignProd_quantity = isset($shortlisted->quantity) ? $alreadyCampaignProd->quantity+$shortlisted->quantity : $alreadyCampaignProd->quantity+1;
					if($alreadyCampaignProd_quantity <= $available_quantity){
						$final_add_quantity = $alreadyCampaignProd_quantity;
					}else{
						$final_add_quantity = $available_quantity;
					}
					$alreadyCampaignProd->quantity = $final_add_quantity;
					$alreadyCampaignProd->group_slot_id = $alreadyCampaignProd->group_slot_id;
					if ($alreadyCampaignProd->save()) {
						$shortlisted->delete();
						return response()->json(["status" => "1", "message" => "Product added to campaign successfully."]);
					} else {
						return response()->json(["status" => "0", "message" => "There was an error while adding the product to campaign."]);
					}
				}else{
					
					$alreadyCampaignProdSingle = ProductBooking::where([
                            ['campaign_id', '=', $this->input['campaign_id']],
                            ['product_id', '=', $product->id]
                        ])->first();
						
					$campaign_product = new ProductBooking;
					$campaign_product->id = uniqid();
					$campaign_product->campaign_id = $campaign->id;
					$campaign_product->product_id = $product->id;
					//$campaign_product->price = $product->default_price;
					$campaign_product->price = $shortlisted->price;
					$campaign_product->product_status = ProductBooking::$PRODUCT_STATUS['proposed'];
					$campaign_product->product_owner = $product->client_mongo_id;
					$campaign_product->quantity = isset($shortlisted->quantity) ? $shortlisted->quantity : "1";
					if(isset($alreadyCampaignProdSingle) && !empty($alreadyCampaignProdSingle)){
						$campaign_product->group_slot_id = $alreadyCampaignProdSingle->group_slot_id;
					}else{
						$campaign_product->group_slot_id = isset($shortlisted->group_slot_id) ? $shortlisted->group_slot_id : "0";
					}
					if ($campaign_product->save()) {
						$shortlisted->delete();
						return response()->json(["status" => "1", "message" => "Product added to campaign successfully."]);
					} else {
						return response()->json(["status" => "0", "message" => "There was an error while adding the product to campaign."]);
					}
				}
        }
    }

    public function proposeProductForCampaign() {
        $this->validate($this->request, [
            'campaign_id' => 'required',
            'product.id' => 'required',
            'product.booking_dates' => 'required',
            'product.price' => 'required'
                ], [
            'campaign_id.required' => 'Campaign id is required',
            'product.id.required' => 'Product id is required',
            'product.booking_dates.required' => 'Product booking date(s) is required',
            'product.price.required' => 'Product price is required'
                ]
        );

        if (!isset($this->input['product']['booking_dates']) || empty($this->input['product']['booking_dates'])) {
            return response()->json(['status' => 0, 'message' => 'booking date(s) are not provided.']);
        }
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        //echo "<pre>"; print_r($user_mongo);exit;
        $campaign_id = $this->input['campaign_id'];
        if ($user_mongo['user_type'] == "bbi") {
            $campaign = Campaign::raw(function($collection) use ($campaign_id) {
                        return $collection->find([
                                    "id" => $campaign_id,
                                    'status' => [
                                        '$in' => [
                                            Campaign::$CAMPAIGN_STATUS['campaign-preparing'],
                                            Campaign::$CAMPAIGN_STATUS['campaign-created'],
                                            Campaign::$CAMPAIGN_STATUS['quote-requested'],
                                            Campaign::$CAMPAIGN_STATUS['change-requested'],
                                            Campaign::$CAMPAIGN_STATUS['quote-given']
                                        ]
                                    ]
                        ]);
                    })->first();
        } else if ($user_mongo['company_type'] == "owner") {
            $campaign = Campaign::raw(function($collection) use ($campaign_id, $user_mongo) {
                        return $collection->find([
                                    "id" => $campaign_id,
                                   // 'status' => [
                                  //    '$lt' => Campaign::$CAMPAIGN_STATUS['booked']
                                   // ],
                                    'client_mongo_id' => $user_mongo['client_mongo_id']
                        ]);
                    })->first();
                    //echo "<pre>"; print_r($campaign);exit;
        } else {
            return response()->json(['status' => 0, 'message' => "Invalid user."]);
        }
        if (!isset($campaign) or empty($campaign)) {
            return response()->json(["status" => "0", "message" => "You can not add products to this campaign at this stage."]);
        }
          $product_occurances = ProductBooking::where([
                        ['product_owner', '=', $user_mongo['client_mongo_id']],
                        ['product_id', '=',  $this->input['product']['id']],
                        ['product_status', '>=',  ProductBooking::$PRODUCT_STATUS['proposed']],
                        ['campaign_id','=',$this->input['campaign_id']]
                    ])->get();
                //dd($product_occurances);  die();
                     $overlapping_dates = [];
                     $productData = Product::where('id', '=', $this->input['product']['id'])->first();
                //if($productData->type=='Bulletin'){ 
            foreach ($product_occurances as $po) {
                foreach ($this->input['product']['booking_dates'] as $dr) {
                        
                        if (strtotime($po->booked_from) <= strtotime($dr['endDate']) && strtotime($po->booked_to) >= strtotime($dr['startDate'])) {
                        array_push($overlapping_dates, $dr);
                    }
                }
                }
                //}
                
            //echo(count($overlapping_dates));exit;
            //echo "<pre>"; print_r($productData);exit;
            if(count($overlapping_dates) >0 && $productData->type=='Bulletin'){
             return response()->json(["status" => "0", "message" => "The dates for selected product is already shortlisted.", "overlapping_dates" => $overlapping_dates]);
            }
            if(count($overlapping_dates) >$productData->slots && $productData->type!='Bulletin'){
             return response()->json(["status" => "0", "message" => "The dates for selected product is already shortlisted.", "overlapping_dates" => $overlapping_dates]);
            }

        // product adding checks
        //======================
        // product should not be in another running, suspended or ready to launch campaign
        $locked_products = ProductBooking::where([
                    ['product_id', '=', $this->input['product']['id']],
                    ['product_status', '<>', ProductBooking::$PRODUCT_STATUS['proposed']],
                ])->get();
        if (count($locked_products) > 0) {
            foreach ($locked_products as $locked_product) {
                foreach ($this->input['product']['booking_dates'] as $date_range) {
                    if ($locked_product->from_date <= $date_range['endDate'] &&
                            $locked_product->to_date >= $date_range['startDate']) {
                        return response()->json(["status" => "0", "message" => "This product is unavailable for the duration of campaign."]);
                    }
                }
            }
        }

       
        // All checks successful. add the product
        $product_obj = Product::where('id', '=', $this->input['product']['id'])->first();
        $success = true;
        /*$product_occurances = ShortListedProduct::where([
                        ['client_mongo_id', '=', $user_mongo['client_mongo_id']],
                        ['product_id', '=', $this->input['product']['id']]
                    ])->get();
            foreach ($product_occurances as $po) {
                foreach ($this->input['product']['booking_dates'] as $dr) {
                    if ($po->from_date >= $dr['endDate'] && $po->to_date <= $dr['startDate']) {
                        array_push($overlapping_dates, $dr);
                    }
                }
            }*/
        if (count($overlapping_dates) == 0) {
        foreach ($this->input['product']['booking_dates'] as $date_range) {
              $sl_product_obj = New ShortListedProduct;
                $sl_product_obj->id = uniqid();
                if ($user_mongo['user_type'] == "basic") {
                    $sl_product_obj->user_mongo_id = $user_mongo['id'];
                } else {
                    $sl_product_obj->client_mongo_id = $user_mongo['client_mongo_id'];
                }
                $sl_product_obj->product_id = $product_obj->id;
                $sl_product_obj->format_type = Format::$FORMAT_TYPE['ooh'];
                $sl_product_obj->from_date = iso_to_mongo_date($date_range['startDate']);
                $sl_product_obj->to_date = iso_to_mongo_date($date_range['endDate']);
                if(isset($this->input['product']['booked_slots'])){
            $sl_product_obj->booked_slots = $this->input['product']['booked_slots'];
            }
                if($sl_product_obj->save()){
            $booking = new ProductBooking;
            $booking->id = uniqid();
            $booking->campaign_id = $this->input['campaign_id'];
            $booking->product_id = $product_obj->id;
            $booking->booked_from = iso_to_mongo_date($date_range['startDate']);
            $booking->booked_to = iso_to_mongo_date($date_range['endDate']);
            
            $diff=date_diff(date_create($date_range['endDate']),date_create($date_range['startDate']));
                        $daysCount = $diff->format("%a");
                        //$price = round(($product->default_price*($daysCount+1))/28);
                        if($product_obj->type=='Bulletin'){
                        //$price = round(($product_obj->default_price*($daysCount+1))/28);
                        $price = round(($product_obj->rateCard*($daysCount+1))/28);
                        }else{
                            //$price = round(($product_obj->default_price*($daysCount+1))/7);
                            //$price = round(($product_obj->rateCard*($daysCount+1))/7);
                            $price = round(($product_obj->rateCard*($daysCount+1))/28);
                        }
                        
            $booking->price = (int) $price;
            if(isset($this->input['product']['booked_slots'])){
            $booking->booked_slots = $this->input['product']['booked_slots'];
            }
            $booking->product_owner = $product_obj->client_mongo_id;
            $booking->product_status = ProductBooking::$PRODUCT_STATUS['proposed'];
            //echo "<pre>"; print_r($booking);exit;
            if (!$booking->save()) {
                $success = false;
            }
        }
        }
    }
     else {
            return response()->json(["status" => "0", "message" => "The dates given are overlapping with another entry in your shortlist", "overlapping_dates" => $overlapping_dates]);
        }
        if ($success) {
            // TO: create notification for owner whose campaign is added
            // create event to set the start date of the campaign.
            return response()->json(["status" => "1", "message" => "Product added to campaign successfully."]);
        } else {
            return response()->json(['status' => 0, 'message' => "The product could not be added to the campaign. Please try again"]);
        }
    }

    public function generatecampaignID() {
        $number = mt_rand(10, 999999); // better than rand()
        $number = 'BB-' . $number;
		
		/*$buyer_id = str_pad(+1, 4, '0', STR_PAD_LEFT);
		$buyer_id1 = 'ABI'.$buyer_id;
		$campaign_id = str_pad(+1, 6, '0', STR_PAD_LEFT);
		$campaign_id1 = '_'.$campaign_id;
		$number = 'AMP_'.$buyer_id1.$campaign_id1;*/
		
        // call the same function if the barcode exists already
        if ($this->campaignIDExists($number)) {
            return $this->generatecampaignID();
        }
        return $number;
    }

    public function campaignIDExists($number) {
        return Campaign::where('cid', '=', $number)->exists();
    }

    public function saveUserCampaign(Request $request) {
		
		 if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
		//echo'<pre>';print_r($request);exit;
        if (isset($this->input['id']) && !empty($this->input['id'])) {
            $this->validate($this->request, [
                'name' => 'required',
                'start_date' => 'required',
                'end_date' => 'required'
                    ], [
                'name.required' => 'Name is required',
                'start_date.required' => 'Start date is required',
                'end_date.required' => 'End date is required'
                    ]
            );
            $start_date_obj = new \DateTime($this->input['start_date']);
            $end_date_obj = new \DateTime($this->input['end_date']);
            $min_end_date_required = $start_date_obj->add(new \DateInterval('P15D'));
            if ($start_date_obj < (new \DateTime('now'))->add(new \DateInterval('P5D'))) {
                return response()->json(['status' => 0, 'message' => ['Campaign start date has to be at least 5 days from today.']]);
            }
            if ($end_date_obj < $min_end_date_required) {
                return response()->json(['status' => 0, 'message' => ['Campaign duration has to be at least 15 days']]);
            }
            $name_slug_string = str_replace(" ", "-", strtolower($this->input['name']));
            $campaign_obj = Campaign::where('id', '=', $this->input['id'])->first();
            if ($name_slug_string == $campaign_obj->slug) {
                return response()->json(['status' => 0, 'message' => "Campaign name must be unique."]);
            }
            $campaign_obj->name = isset($this->input['name']) ? $this->input['name'] : "";
            $campaign_obj->slug = $name_slug_string;
            $campaign_obj->start_date = isset($this->input['start_date']) ? $this->input['start_date'] : "";
            $campaign_obj->end_date = isset($this->input['end_date']) ? $this->input['end_date'] : "";
            $campaign_obj->format_type = Format::$FORMAT_TYPE['ooh'];
            $campaign_obj->type = Campaign::$CAMPAIGN_USER_TYPE['user'];
            if ($campaign_obj->save()) {
                $campaign_suggestion_request = CampaignSuggestionRequest::where('campaign_id', '=', $campaign_obj->id)->first();
                $campaign_suggestion_request->processed = true;
                if (!$campaign_suggestion_request->save()) {
                    Log::error("campaign suggestion request status couldn't be changed. campaign suggestion request id:" . $campaign_suggestion_request->id);
                }
                // Update data to elasticsearch :: Pankaj 19 Oct 2021
                $get_data = Campaign::where('id', '=', $campaign_obj->id)->first();
                $this->es_etl($get_data, "update");
                return response()->json(["status" => "1", "message" => "campaign saved successfully."]);
            } else {
                return response()->json(["status" => "0", "message" => "Failed to save campaign."]);
            }
        } else {
            $this->validate($this->request, [
                'name' => 'required'
                    ], [
                'name.required' => 'Name is required'
                    ]
            );
            //echo "<pre>request";print_r($this->request);exit;
            $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
            if (isset($input['client'])) {
            $client = userMongo::where('id', '=', $input['client'])->first();
			}else{
				$client = userMongo::where('id', '=', $user_mongo['id'])->first();
			}
	
			//echo "<pre>";print_r($client);exit;
            
            if ($user_mongo['user_type'] != 'basic' ) {
                return response()->json(['status' => 0, 'message' => "You can not create a campaign from here. Please switch to your dashboard."]);
            }
            $campaign_obj = new Campaign;
			
			$campaign_obj->user_id = isset($client) ? $client->user_id : "";
            //echo "<pre>";print_r($campaign_obj->user_id);exit;
			
			
			//campaign unique ID duplicate start
			$campaign_count = Campaign::latest()->first();
			$campaign_code_explode = explode("_", $campaign_count->cid);
			$uid_cid = '_'.str_pad(end($campaign_code_explode)+1, 6, '0', STR_PAD_LEFT);	
	
			//campaign unique ID duplicate end
			
			/*$campaign_count = Campaign::count();
			$newSiteNo = $campaign_count+1;
			$siteNo = str_pad($newSiteNo, 6, '0', STR_PAD_LEFT);
			$siteNo1 = '_'.$siteNo;*/
			
			$buyer_id = '000'.$campaign_obj->user_id;
			//echo "<pre>";print_r($buyer_id);exit;
            $campaign_obj->id = uniqid();
            //$campaign_obj->cid = $this->generatecampaignID();
            //$campaign_obj->cid = 'AMP_'.'ABI'.$buyer_id.$siteNo1; 
            $campaign_obj->cid = 'AMP_'.'ABI'.$buyer_id.$uid_cid; 
			//echo "<pre>";print_r($campaign_obj->cid);exit;
            $campaign_obj->name = isset($this->input['name']) ? $this->input['name'] : "";
            $campaign_obj->slug = str_replace(" ", "-", strtolower($this->input['name']));
            $campaign_obj->est_budget = isset($this->input['est_budget']) ? $this->input['est_budget'] : "";
            $campaign_obj->created_by = $user_mongo['id'];
            $campaign_obj->status = Campaign::$CAMPAIGN_STATUS['campaign-preparing'];
            $campaign_obj->type = Campaign::$CAMPAIGN_USER_TYPE['user'];
			$campaign_obj->todolist_read = 0;
            //echo "<pre>campaign_obj";print_r($campaign_obj);exit;
            
            if ($campaign_obj->save()) {
                $success = true;
                // Save data to elasticsearch :: Pankaj 19 Oct 2021
                $get_data = Campaign::where('id', '=', $campaign_obj->id)->first();
                $this->es_etl($get_data, "insert");
                Log::info($campaign_obj->id);
		$rand = substr(str_shuffle(str_repeat("ABCDEFGHJKLMNPQRSTUVWXYZ", 3)), 0, 3);
        	$group_id ="AMP".date('Ymd').$rand;
                if (isset($this->input['shortlisted_products']) && !empty($this->input['shortlisted_products'])) {
                    // move products from shortlisted_products collection to product_bookings collection

                    foreach ($this->input['shortlisted_products'] as $key => $shortlisted_id) {
                        $shortlisted = ShortListedProduct::where('id', '=', $shortlisted_id)->first();
                        //echo "<pre>"; print_r($shortlisted);exit;
                        $product = Product::where('id', '=', $shortlisted->product_id)->first();
                        $new_booking = new ProductBooking;
                        $new_booking->id = uniqid();
                        $new_booking->campaign_id = $campaign_obj->id;
                        $new_booking->product_id = $product->id;
                        $new_booking->booked_from = iso_to_mongo_date($shortlisted->from_date);
                        $new_booking->booked_to = iso_to_mongo_date($shortlisted->to_date);
                        if(isset($shortlisted->booked_slots) && $shortlisted->booked_slots!='' ){
                        $new_booking->booked_slots = $shortlisted->booked_slots;
                        }
                        $new_booking->price = $shortlisted->price;
                        $new_booking->product_owner = $product->client_mongo_id;
                        $new_booking->product_status = ProductBooking::$PRODUCT_STATUS['proposed'];
			$new_booking->quantity = isset($shortlisted->quantity) ? $shortlisted->quantity : "0";
			$new_booking->group_slot_id = isset($shortlisted->group_slot_id) ? $shortlisted->group_slot_id : "0";
                        if (!$new_booking->save()) {
                            $success = false;
                            break;
                        } else {
                            $shortlisted->delete();
                        }
                    }
                    //exit;
                } else if (isset($this->input['products']) && !empty($this->input['products'])) {
                    $products = $this->input['products'];
                    $product_id = $products[0]['product_id'];
                    foreach ($products[0]['dates'] as $dr) {
                        $product = Product::where('id', '=', $product_id)->first();
                        $new_booking = new ProductBooking;
                        $new_booking->id = uniqid();
                        $new_booking->campaign_id = $campaign_obj->id;
                        $new_booking->product_id = $product_id;
                        $new_booking->booked_from = iso_to_mongo_date($dr['startDate']);
                        $new_booking->booked_to = iso_to_mongo_date($dr['endDate']);
                        //$new_booking->price = $product->default_price;
                        $new_booking->price = $product->rateCard;
                        $new_booking->product_owner = $product->client_mongo_id;
                        $new_booking->product_status = ProductBooking::$PRODUCT_STATUS['proposed'];
                        $new_booking->save();
                    }
                }
                if ($success) {
                    return response()->json(["status" => "1", "message" => "campaign saved successfully and products added."]);
                } else {
                    return response()->json(["status" => "0", "message" => "campaign saved successfully but product addition failed."]);
                }
            } else {
                return response()->json(["status" => "0", "message" => "Failed to save campaign."]);
            }
        }
    }

    // Add/Update/Delete data to elasticsearch function :: Pankaj 19 Sept 2021
    public function es_etl($get_data, $opr){
        $url_insert = env('ES_SERVER_URL_INSERT');
        $url_delete = env('ES_SERVER_URL_DELETE');

        $index = env('ES_CAMPAIGNS');   
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

            if ( is_null($get_data->due_date) ) {
                $new_due_date = null;
            } else {
                $due_date = $get_data->due_date;
                $d_due_date = date("Y-m-d", strtotime($due_date));
                $t_due_date = date("H:i:s", strtotime($due_date));
                $new_due_date = $d_due_date."T".$t_due_date.".000Z";
            }

            $data_string = array(
                "index" => $index,
                "data" => array (
                    array (
                        "id" => $get_data->id,
                        "cid" => $get_data->cid,
                        "name" => $get_data->name,
                        "slug" => $get_data->slug,
                        "est_budget" => $get_data->est_budget,
                        "created_by" => $get_data->created_by,
                        "status" => $get_data->status,
                        "type" => $get_data->type,
                        "org_name" => $get_data->org_name,
                        "org_contact_name" => $get_data->org_contact_name,
                        "org_contact_email" => $get_data->org_contact_email,
                        "org_contact_phone" => $get_data->org_contact_phone,
                        "referred_by" => $get_data->referred_by,
                        "client_name" => $get_data->client_name,
                        "prev_status" => $get_data->prev_status,
                        "user_email" => $get_data->user_email,
                        "due_date" => $new_due_date,
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
            //var_dump($result);exit;
            curl_close($ch);
        }
    }

/* Save RFP User Campaign */
public function saveRFPUserCampaign(Request $request) {
		if ($request->isJson()) {
			$input = $request->json()->all();
		} else {
			$input = $request->all();
		}
		
		$this->validate($this->request, [
			'campaign_name' => 'required'
				], [
			'campaign_name.required' => 'Campaign Name is required'
				]
		);

		$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
		if (isset($input['client'])) {
			$client = userMongo::where('id', '=', $input['client'])->first();
		}else{
			$client = userMongo::where('id', '=', $user_mongo['id'])->first();
		}
			
		//$start_date_obj = new \DateTime($this->input['startDate']);
		//$end_date_obj = new \DateTime($this->input['endDate']);
		if ($user_mongo['user_type'] != 'basic' ) {
			return response()->json(['status' => 0, 'message' => "You can not create a campaign from here. Please switch to your dashboard."]);
		}
		$campaign_obj = new Campaign;
		$campaign_obj->user_id = isset($client) ? $client->user_id : "";
        $campaign_count = Campaign::count();
		$newSiteNo = $campaign_count+1;
		$siteNo = str_pad($newSiteNo, 6, '0', STR_PAD_LEFT);
		$siteNo1 = '_'.$siteNo;
		//campaign unique ID duplicate start
		$campaign_count = Campaign::latest()->first();
		$campaign_code_explode = explode("_", $campaign_count->cid);
		$uid_cid = '_'.str_pad(end($campaign_code_explode)+1, 6, '0', STR_PAD_LEFT);	
		//campaign unique ID duplicate end
		$buyer_id = '000'.$campaign_obj->user_id;
		$campaign_obj->id = uniqid();
		$campaign_obj->cid = 'AMP_'.'ABI'.$buyer_id.$uid_cid; 
		$campaign_id = $campaign_obj->id;
		$campaign_obj->name = isset($this->input['campaign_name']) ? $this->input['campaign_name'] : "";
		$campaign_obj->due_date = isset($this->input['due_date']) ? $this->input['due_date'] : "";
		$campaign_obj->slug = str_replace(" ", "-", strtolower($this->input['campaign_name']));
		$campaign_obj->est_budget = isset($this->input['est_budget']) ? $this->input['est_budget'] : "";
		$campaign_obj->created_by = $user_mongo['id'];
		$campaign_obj->status = Campaign::$CAMPAIGN_STATUS['campaign-preparing'];
		$campaign_obj->cmp_type = 1;
		$campaign_obj->type = Campaign::$CAMPAIGN_USER_TYPE['user'];
            
        $filters_array = [];
        array_push($filters_array, ["product_visibility" => ['$ne' => "0"]]);
        if ($user_mongo['user_type'] == 'owner') {
            array_push($filters_array, ["client_mongo_id" => $user_mongo['client_mongo_id']]);
        }

        if (isset($this->input['area']) && !empty($this->input['area'])) {
            $area_filter = $this->input['area'];
            array_push($filters_array, ["area" => ['$eq' => $area_filter]]);
        }
        if (isset($this->input['producttype']) && !empty($this->input['producttype'])) {
            $type_filter = $this->input['producttype'];
            array_push($filters_array, ["type" => ['$eq' => $type_filter]]);
        }

        if (isset($this->input['startDate']) && isset($this->input['endDate'])) {
            if (isset($this->input['startDate']) && !empty($this->input['startDate'])) {
                $from = $this->input['startDate'];
            }
            if (isset($this->input['endDate']) && !empty($this->input['endDate'])) {
                $to = $this->input['endDate'];
            }
            $start = iso_to_mongo_date($from);
            $startdate = date_create($from);
            $enddate = date_create($to);
            $curdate1 = date_create(date("Y-m-d"));
            
            $product_List = ProductBooking::where('booked_from', '<=', $enddate)
            ->where('booked_to', '>=', $startdate)
            ->where('booked_to', '>=', $curdate1)
            ->get(); 
            
            $prod_filter = [];
            if (count($product_List) > 0) {
                foreach ($product_List as $val) {
                    $prod_filter[] = $val->product_id;
                }
                array_push($filters_array, ["id" => ['$in' => $prod_filter]]);
            } else {
                array_push($filters_array, ["id" => ['$nin' => $prod_filter]]);
            }
        }
		$grouped_products = array(); 
		$curdate1 = date_create(date("Y-m-d"));
		$type = $this->input['producttype'];
		foreach($this->input['area'] as $key => $value){
			$date_ranges = @explode('::',$this->input['date_ranges'][$key]);
			$grouped_products_arr = Product::where([
				['to_date', '>=', $curdate1],
				['product_visibility', '=', 1],
			])->where('area', $value)
			->where('from_date', '<=', date_create($date_ranges[1]))
			->where('to_date', '>=', date_create($date_ranges[0]))
			->whereIn('type', $type)->get()->toArray();
			if(isset($grouped_products_arr) && !empty($grouped_products_arr)){
				$grouped_products[] = $grouped_products_arr;
			}
		}	
		$res = $grouped_products;
        $resval = [];
        $resval2 = [];
        $resval3 = [];
        $resval4 = [];
		if(isset($res) && !empty($res)){
			foreach (call_user_func_array("array_merge", $res) as $res) {
				$resval[] = $res;
			}
		}
		$products_not_found = "";
        if ($campaign_obj->save()) {
			$success = true;
			if (isset($resval) && !empty($resval)) {
				$status_avail = 0;
				foreach ($resval as $resval_avail) {
					$product_avail = Product::where('id', '=', $resval_avail['id'])->first();
					$productBooked_avail = ProductBooking::where('product_id', '=',  $product_avail->id)->where('quantity', '!=',  '')->get();
					$available_quantity_avail = $product_avail->unitQty;
					if(isset($productBooked_avail)){
						$productBooked_last_avail = ProductBooking::select("quantity","campaign_id","id")->where('product_id', '=',  $product_avail->id)->where('booked_from','<=',$product_avail->to_date)->where('booked_to','>=',$product_avail->from_date)->where('product_status','!=',100)->where('product_status','!=',400)->where('product_status','!=',700)->where('quantity', '!=',  '')->groupBy('group_slot_id')->get([DB::raw('MAX(quantity) as quantity')]);
						$sum_quantity_avail = 0;
						if(isset($productBooked_last_avail)){
							$productBooked_last_avail = $productBooked_last_avail->toArray();
							foreach($productBooked_last_avail as $key => $value){
									$delete_product_status_avail = DeleteProduct::where([
																		['campaign_id', '=', $value['campaign_id']],
																		['status', '=', DeleteProduct::$PRODUCT_STATUS['confirm-delete-product-from-campaign']],
																	])->whereIn('product_id', array($product_avail->id))->whereIn('productbookingid', array($value['id']))->orderBy('created_at', 'desc')->first();
								if($value['campaign_id'] != ''){
									$campaign_delete_avail = Campaign::select("status")->where('id', '=', $value['campaign_id'])->first();
									if(empty($delete_product_status_avail) && ($campaign_delete_avail->status != 1200)){
										$sum_quantity_avail += $value['quantity'];
									}
								}
							}
						} 
						$available_quantity_avail = $product_avail->unitQty-$sum_quantity_avail;
						if($available_quantity_avail >= 0){
							$available_quantity_avail = $available_quantity_avail;
						}else{
							$available_quantity_avail = 0;
						}
					}
					if($available_quantity_avail > 0 && $status_avail == 0){
						$status_avail = 1;
						break;
					}
				}
				if($status_avail == 1){
					$success = true;
					Log::info($campaign_obj->id);
					// move products from shortlisted_products collection to product_bookings collection
					$rand = substr(str_shuffle(str_repeat("ABCDEFGHJKLMNPQRSTUVWXYZ", 3)), 0, 3);
					$group_id ="AMP".date('Ymd').$rand;
					foreach ($resval as $resval) {
						$product = Product::where('id', '=', $resval['id'])->first();
						$productBooked = ProductBooking::where('product_id', '=',  $product->id)->where('quantity', '!=',  '')->get();
						$available_quantity = $product->unitQty;
						if(isset($productBooked)){
							$productBooked_last = ProductBooking::select("quantity","campaign_id","id")->where('product_id', '=',  $product->id)->where('booked_from','<=',$product->to_date)->where('booked_to','>=',$product->from_date)->where('product_status','!=',100)->where('product_status','!=',400)->where('product_status','!=',700)->where('quantity', '!=',  '')->groupBy('group_slot_id')->get([DB::raw('MAX(quantity) as quantity')]);
							$sum_quantity = 0;
							if(isset($productBooked_last)){
								$productBooked_last = $productBooked_last->toArray();
								foreach($productBooked_last as $key => $value){
									$delete_product_status = DeleteProduct::where([
																		['campaign_id', '=', $value['campaign_id']],
																		['status', '=', DeleteProduct::$PRODUCT_STATUS['confirm-delete-product-from-campaign']],
																	])->whereIn('product_id', array($product->id))->whereIn('productbookingid', array($value['id']))->orderBy('created_at', 'desc')->first();
									if($value['campaign_id'] != ''){
										$campaign_delete = Campaign::select("status")->where('id', '=', $value['campaign_id'])->first();
										if(empty($delete_product_status) && ($campaign_delete->status != 1200)){
											$sum_quantity += $value['quantity'];
										}
									}
								}
							} 
							$available_quantity = $product->unitQty-$sum_quantity;
							if($available_quantity >= 0){
								$available_quantity = $available_quantity;
							}else{
								$available_quantity = 0;
							}
						}
						if($available_quantity > 0){
							$diff=date_diff(date_create($product->to_date->toDateTime()->format("Y-m-d")),date_create($product->from_date->toDateTime()->format("Y-m-d")));
							$daysCount = $diff->format("%a");
							$perdayprice = $resval['default_price']/28;
							if(isset($product->fix) && $product->fix=="Fixed"){
								$price =0;
								if(($daysCount+1) <= $product->minimumdays){
									$price = $perdayprice * $product->minimumdays;
								}else{
									$y  = 1;
									$z = 'aa';
									for ($x = 1; $x <= $y; $x++) {
										if(($product->minimumdays*$x) >= ($daysCount+1) && $z == 'aa'){
											$price = $perdayprice*($product->minimumdays*$x);
											$z = 'bb';
											$y++;
										}if($z == 'aa'){
											$y++;
										}
									}
								}
							}else{
								if(($daysCount+1) <= $product->minimumdays){
									$price = $perdayprice * $product->minimumdays;
								}else{
									$price = $perdayprice * ($daysCount+1);
								}
							}
							
							$new_booking = new ProductBooking;
							$new_booking->id = uniqid();
							$new_booking->campaign_id = $campaign_obj->id;
							$new_booking->product_id = $product->id;
							$new_booking->booked_from = ($product->from_date);
							$new_booking->booked_to = ($product->to_date);
							$new_booking->price = $price;
							$new_booking->product_owner = $product->client_mongo_id;
							$new_booking->product_status = ProductBooking::$PRODUCT_STATUS['proposed'];
							$new_booking->quantity = "1";
							$new_booking->group_slot_id = $group_id.$product->id;
							$new_booking->save();
						}
					}
					$products_not_found = " and products added.";
				} else {
					$rfp_search_criteria = new RFPSearchCriteria;
					$rfp_search_criteria->id = uniqid();
					$rfp_search_criteria->campaign_id = $campaign_obj->id;
					$rfp_search_criteria->demo = isset($this->input['demo']) ? $this->input['demo'] : "";
					$rfp_search_criteria->product_type = isset($this->input['producttype']) ? $this->input['producttype'] : "";
					$rfp_search_criteria->budget = isset($this->input['budget']) ? $this->input['budget'] : "";
					$rfp_search_criteria->dma_area = isset($this->input['area']) ? $this->input['area'] : "";
					$rfp_search_criteria->dma_dates = isset($this->input['date_ranges']) ? $this->input['date_ranges'] : "";
					$rfp_search_criteria->email = isset($this->input['user_email']) ? $this->input['user_email'] : "";
					$rfp_search_criteria->instructions = isset($this->input['instructions']) ? $this->input['instructions'] : "";
					$rfp_search_criteria->due_date = isset($this->input['due_date']) ? $this->input['due_date'] : "";
					$rfp_search_criteria->save();
					$products_not_found = " and products not found.";
					//return response()->json(["status" => "0", "message" => "Failed to save campaign, No products available in the selected criteria"]);
				}
			} else {
				$rfp_search_criteria = new RFPSearchCriteria;
				$rfp_search_criteria->id = uniqid();
				$rfp_search_criteria->campaign_id = $campaign_obj->id;
				$rfp_search_criteria->demo = isset($this->input['demo']) ? $this->input['demo'] : "";
				$rfp_search_criteria->product_type = isset($this->input['producttype']) ? $this->input['producttype'] : "";
				$rfp_search_criteria->budget = isset($this->input['budget']) ? $this->input['budget'] : "";
				$rfp_search_criteria->dma_area = isset($this->input['area']) ? $this->input['area'] : "";
				$rfp_search_criteria->dma_dates = isset($this->input['date_ranges']) ? $this->input['date_ranges'] : "";
				$rfp_search_criteria->email = isset($this->input['user_email']) ? $this->input['user_email'] : "";
				$rfp_search_criteria->instructions = isset($this->input['instructions']) ? $this->input['instructions'] : "";
				$rfp_search_criteria->due_date = isset($this->input['due_date']) ? $this->input['due_date'] : "";
				$rfp_search_criteria->save();
				
				$products_not_found = " and products not found.";
				//return response()->json(["status" => "0", "message" => "Failed to save campaign, No products available in the selected criteria"]);
			}   
		}if ($success) {
			return response()->json(["status" => "1", "message" => "campaign saved successfully".$products_not_found, "campaign_id"=>$campaign_id]);
		} else {
			return response()->json(["status" => "0", "message" => "campaign saved successfully but product addition failed."]);
		}
    }
    /* Save RFP User Campaign */


    public function saveSuggestionRequest() {
        $this->validate($this->request, [
            'org_name' => 'required',
            'start_end_date' => 'required'
                ], [
            'org_name.required' => 'Organization name is required',
            'start_end_date.required' => 'Start End date is required'
                ]
        );
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        if ($user_mongo['user_type'] != 'basic') {
            return response()->json(['status' => 0, 'message' => "You can not create a suggestion request."]);
        }
        $campaign_obj = new Campaign;
        $campaign_obj->id = uniqid();
        $campaign_obj->cid = $this->generatecampaignID();
        $campaign_obj->created_by = $user_mongo['id'];
        $campaign_obj->type = Campaign::$CAMPAIGN_USER_TYPE['user'];
        $campaign_obj->status = Campaign::$CAMPAIGN_STATUS['campaign-preparing'];
        $campaign_obj->from_suggestion = true;
        if ($campaign_obj->save()) {
            $campaign_suggest_request_obj = new CampaignSuggestionRequest;
            $campaign_suggest_request_obj->id = uniqid();
            $campaign_suggest_request_obj->campaign_id = $campaign_obj->id;
            $campaign_suggest_request_obj->org_name = isset($this->input['org_name']) ? $this->input['org_name'] : "";
            $campaign_suggest_request_obj->product = isset($this->input['product']) ? $this->input['product'] : "";
            $campaign_suggest_request_obj->user_mongo_id = isset($user_mongo, $user_mongo['id']) ? $user_mongo['id'] : "";
            $campaign_suggest_request_obj->user_full_name = isset($user_mongo, $user_mongo['first_name'], $user_mongo['last_name']) ? $user_mongo['first_name'] . " " . $user_mongo['last_name'] : "";
            $campaign_suggest_request_obj->user_phone = isset($user_mongo, $user_mongo['phone']) ? $user_mongo['phone'] : "";
            $campaign_suggest_request_obj->user_email = isset($user_mongo, $user_mongo['email']) ? $user_mongo['email'] : "";
            $campaign_suggest_request_obj->user_avatar = isset($user_mongo, $user_mongo['avatar']) ? $user_mongo['avatar'] : "";
            $campaign_suggest_request_obj->product_desc = isset($this->input['product_desc']) ? $this->input['product_desc'] : "";
            if (isset($this->input['market_reach'])) {
                $campaign_suggest_request_obj->market_reach = $this->input['market_reach'];
            }
            if (isset($this->input['adv_objective'])) {
                $campaign_suggest_request_obj->adv_objective = $this->input['adv_objective'];
            }
            if (isset($this->input['medium'])) {
                $campaign_suggest_request_obj->medium = $this->input['medium'];
            }
            if (isset($this->input['target_audience'])) {
                $campaign_suggest_request_obj->target_audience = $this->input['target_audience'];
            }
            if (isset($this->input['gender_group'])) {
                $campaign_suggest_request_obj->gender_group = $this->input['gender_group'];
            }
            if (isset($this->input['age_group'])) {
                $campaign_suggest_request_obj->age_group = $this->input['age_group'];
            }
            $campaign_suggest_request_obj->geo_region = isset($this->input['geo_region']) ? $this->input['geo_region'] : "";
            $campaign_suggest_request_obj->ad_design = isset($this->input['ad_design']) ? $this->input['ad_design'] : "";
            $campaign_suggest_request_obj->duration = isset($this->input['duration']) ? $this->input['duration'] : "";
            $campaign_suggest_request_obj->start_end_date = isset($this->input['start_end_date']) ? $this->input['start_end_date'] : "";
            $campaign_suggest_request_obj->est_budget = isset($this->input['est_budget']) ? $this->input['est_budget'] : "";
            $campaign_suggest_request_obj->processed = false;
            if ($campaign_suggest_request_obj->save()) {
                // new suggestion request created. need to show notification to admin.
                /* NotificationHelper::createNotification([
                  'type' => Notification::$NOTIFICATION_TYPE['campaign-suggestion-requested'],
                  'from_id' => $user_mongo['id'],
                  'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
                  'to_id' => null,
                  'to_client' => null,
                  'desc' => "New campaign suggestion requested",
                  'message' => $user_mongo['first_name'] . " " . $user_mongo['last_name'] . " requested a suggestion for new campaign.",
                  'data' => ["campaign_sugg_req_id" => $campaign_suggest_request_obj->id]
                  ]); */
                $bbi_sa_id = Client::where('company_slug', '=', 'bbi')->first()->super_admin;
                $bbi_sa = UserMongo::where('user_id', '=', $bbi_sa_id)->first();
                $mail_tmpl_params = [
                    'sender_email' => $user_mongo['email'],
                    'receiver_name' => $bbi_sa->first_name . ' ' . $bbi_sa->last_name,
                    'mail_message' => $user_mongo['first_name'] . ' ' . $user_mongo['last_name'] . 'has requested a campaign suggestion'
                ];
                $mail_data = [
                    'email_to' => $bbi_sa->email,
                    'recipient_name' => $bbi_sa->first_name
                ];
                Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data) {
                    //$message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('New Campaign Suggestion Request - Billboards India');
                    $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('New Campaign Suggestion Request - Advertising Marketplace');
                });
                return response()->json(["status" => "1", "message" => "Request sent successfully."]);
            } else {
                $campaign_obj->delete();
                return response()->json(["status" => "0", "message" => "There was an error in sending the request."]);
            }
        } else {
            return response()->json(["status" => "0", "message" => "There was an error in sending the request."]);
        }
    }

    public function campaignSuggestionDetails($campaign_id) {
        $campaign_suggestion_request_details = CampaignSuggestionRequest::where('campaign_id', '=', $campaign_id)->first();
        return response()->json($campaign_suggestion_request_details);
    }

    public function deleteCampaign($campaign_id) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        if ($user_mongo['user_type'] == "bbi") {
            $campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['user']]
                    ])->first();
        } else if ($user_mongo['user_type'] == "basic") {
            $campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['user']],
                        ['created_by', '=', $user_mongo['id']]
                    ])->first();
        } else {
            return response()->json(['status' => 0, 'message' => 'Invalid user.']);
        }
        //if (isset($campaign) && !empty($campaign) && $campaign->status < Campaign::$CAMPAIGN_STATUS['quote-requested']) {
        if (isset($campaign) && !empty($campaign)) {
            if ($campaign->delete()) {
                return response()->json(['status' => 1, 'message' => "Campaign deleted successfully."]);
            } else {
                return response()->json(['status' => 0, 'message' => "Error deleting campaign."]);
            }
        } else {
            return response()->json(['status' => 0, 'message' => "You can not delete this campaign at this stage."]);
        }
    }
    
    public function deleteNonUserCampaign($campaign_id) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        if ($user_mongo['user_type'] == "bbi") {
            $campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['bbi']]
                    ])->first();
            $data = array(
                'id' => $campaign_id,
                'type' => Campaign::$CAMPAIGN_USER_TYPE['bbi']
            );
        } else if ($user_mongo['user_type'] == "owner") {
            $campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['owner']],
                        ['client_mongo_id', '=', $user_mongo['client_mongo_id']]
                    ])->first();
            $data = array(
                'id' => $campaign_id,
                'type' => Campaign::$CAMPAIGN_USER_TYPE['owner'],
                'client_mongo_id' => $user_mongo['client_mongo_id']
            );
        } else {
            return response()->json(['status' => 0, 'message' => 'Invalid user.']);
        }
        if ($campaign->status < Campaign::$CAMPAIGN_STATUS['booking-requested']) {
            if ($campaign->delete()) {
                $campaign_products = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
                foreach ($campaign_products as $campaign_product) {
                    $campaign_product->delete();
                }
                $campaign_payments = CampaignPayment::where('campaign_id', '=', $campaign_id)->get();
                foreach ($campaign_payments as $campaign_payment) {
                    $campaign_payment->delete();
                }
                // Delete data to elasticsearch :: Pankaj 19 Oct 2021
                $this->es_etl($data, "delete");
                return response()->json(['status' => 1, 'message' => "Campaign deleted successfully."]);
            } else {
                return response()->json(['status' => 0, 'message' => "Error deleting campaign."]);
            }
        } else {
            return response()->json(['status' => 0, 'message' => "You can not delete this campaign at this stage."]);
        }
    }

    public function deleteUserCampaign($campaign_id) {
                $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        if ($user_mongo['user_type'] == "bbi") {
            $campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['user']]
                    ])->first();
            $data = array(
                'id' => $campaign_id,
                'type' => Campaign::$CAMPAIGN_USER_TYPE['user']
            );
        } else if ($user_mongo['user_type'] == "basic") {
            $campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['user']],
                        ['created_by', '=', $user_mongo['id']]
                    ])->first();
            $data = array(
                'id' => $campaign_id,
                'type' => Campaign::$CAMPAIGN_USER_TYPE['user'],
                'created_by' => $user_mongo['id']
            );
        } else {
            return response()->json(['status' => 0, 'message' => 'Invalid user.']);
        }
        //if (isset($campaign) && !empty($campaign) && $campaign->status < Campaign::$CAMPAIGN_STATUS['quote-requested']) {
        if (isset($campaign) && !empty($campaign)) {
            if ($campaign->delete()) {
				$campaign_products = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
				foreach ($campaign_products as $booking) {
					$booking->delete();
				}
                return response()->json(['status' => 1, 'message' => "Campaign deleted successfully."]);
            } else {
                // Delete data to elasticsearch :: Pankaj 19 Oct 2021
                $this->es_etl($data, "delete");
                return response()->json(['status' => 0, 'message' => "Error deleting campaign."]);
            }
        } else {
            return response()->json(['status' => 0, 'message' => "You can not delete this campaign at this stage."]);
        }
    }
    
    public function deleteAdminOwnerCampaign($campaign_id)  {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        //echo '<pre>';print_r($user_mongo);exit; 
        if ($user_mongo['user_type'] == "bbi") {
            $campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['bbi']]
                    ])->first();
            $data = array(
                'id' => $campaign_id,
                'type' => Campaign::$CAMPAIGN_USER_TYPE['bbi']
            );
        } else if ($user_mongo['user_type'] == "owner") {
            $campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['owner']],
                        ['client_mongo_id', '=', $user_mongo['client_mongo_id']]
                    ])->first();
            $data = array(
                'id' => $campaign_id,
                'type' => Campaign::$CAMPAIGN_USER_TYPE['owner'],
                'client_mongo_id' => $user_mongo['client_mongo_id']
            );
        } else {
            return response()->json(['status' => 0, 'message' => 'Invalid user.']);
        }
       //if ($campaign->status < Campaign::$CAMPAIGN_STATUS['booking-requested']) {
        if (isset($campaign) && !empty($campaign)) { 
            if ($campaign->delete()) {
                // Delete data to elasticsearch :: Pankaj 19 Oct 2021
                $this->es_etl($data, "delete");
                return response()->json(['status' => 1, 'message' => "Campaign deleted successfully."]);
            } else {
                return response()->json(['status' => 0, 'message' => "Error deleting campaign."]);
            }
        } else {
            return response()->json(['status' => 0, 'message' => "Error deleting campaign."]); 
        }
    }
   
 public function launchCampaign($campaign_id) {

        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo['user_id'])->first();
        $campaign_obj = Campaign::where([
                    ['id', '=', $campaign_id],
                ])->first();
        if (!isset($user->client) || empty($user->client)) {
            return response()->json(['status' => 0, 'message' => 'You are not authorized to do this operation.']);
        }
        $campaign_products = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
        //print_r($campaign_products)
        if (count($campaign_products) == 0) {
            return response()->json(['status' => 0, 'message' => "Please add some products first."]);
        } else {
            $error = 0;
            foreach ($campaign_products as $booking) {
                $error = !empty($booking->booked_from) ? $error : $error + 1;
                $error = !empty($booking->booked_to) ? $error : $error + 1;
            }
            if ($error > 0) {
                return response()->json(["status" => "0", "message" => "One or more products are quoted incompletely. Please check again."]);
            }
        } 
		
		// Buyer snd admin CPM, IMPRESSIONS CALUCULATIONS
		
		$shortlistedsum = 0;
		$shortlistedsumcpm = 0;
		$shortlistedsumcpm = 0;
		$impressionSum = 0;
		$offercpmsum = 0;
		$impressionSum4 = 0;
		$offershortlistedsum = 0;
		$offershortlistedsumcpm = 0;
		$offershortlistedsumcpm = 0;
        $newprocessingfeeamtSum = 0;
		$newprocessingfeeamtSum_seller = 0;
		$tax_percentage_booking = 0;
		$tax_percentage_amount = 0;   
        $tax_percentage_amount_sum = 0;
        $tax_percentage_booking_tot = 0;
        $tax_percentage_amount_tot = 0;
		
		if($user_mongo['user_type'] == 'owner'){
			$campaignproducts = ProductBooking::where([
					['campaign_id', '=', $campaign_id],
					['product_owner', '=', $user_mongo['client_mongo_id']]
				])->get();
		}else{
			$campaignproducts = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
		}
		
		$getcampaigntot = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
		$camptot = 0;
		$offer_applied = 0;
		if (isset($getcampaigntot) && count($getcampaigntot) > 0) {
			foreach ($getcampaigntot as $getcampaigntot) {
				$tax_percentage_booking_tot = 0;
				$tax_percentage_amount_tot = 0;
				$getcampaigntotproduct =Product::where('id', '=', $getcampaigntot->product_id)->first();
				$bookedfrom[] = strtotime($getcampaigntot->booked_from);
				$bookedto[] = strtotime($getcampaigntot->booked_to);

				$getproductDetails =Product::where('id', '=', $getcampaigntot->product_id)->first();
		
				$diff=date_diff(date_create($getcampaigntot->booked_from),date_create($getcampaigntot->booked_to));
				$daysCount = $diff->format("%a");
				$daysCountCPM = $daysCount + 1;
				$perdayprice = $getproductDetails->default_price/28;

				if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
					$price = $getcampaigntot->price*$getcampaigntot->quantity;
					
					if(isset($getcampaigntot->tax_percentage)){
						$tax_percentage_booking_tot = $getcampaigntot->tax_percentage;
					}else{
						$tax_percentage_booking_tot = 0;
					}
					if($tax_percentage_booking_tot != 0){
						$tax_percentage_amount_tot = ($price * ($tax_percentage_booking_tot))/100;
					}
					$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
					$camptot += $price+$getcampaigntot->tax_percentage_amount_tot;
				}else{
					
					$priceperselectedDates = $getcampaigntot->price;
					if(isset($getcampaigntot->tax_percentage)){
						$tax_percentage_booking_tot = $getcampaigntot->tax_percentage;
					}else{
						$tax_percentage_booking_tot = 0;
					}
					if($tax_percentage_booking_tot != 0){
						$tax_percentage_amount_tot = ($priceperselectedDates * ($tax_percentage_booking_tot))/100;
					}
					$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
					$camptot += ($priceperselectedDates*$getcampaigntot->quantity)+($tax_percentage_amount_tot*$getcampaigntot->quantity);
				}
			}
		}
		
		if (isset($campaignproducts) && count($campaignproducts) > 0) {

			foreach ($campaignproducts as $campaignproduct) {
				$campaignproduct->tax_percentage_amount = 0;
				$campaignproduct->tax_percentage_amount_shortlist = 0;
				$tax_percentage_booking = 0;
				$tax_percentage_amount = 0;
				$getproductDetails =Product::where('id', '=', $campaignproduct->product_id)->first();
				$diff=date_diff(date_create($campaignproduct->booked_from),date_create($campaignproduct->booked_to));
				$daysCount = $diff->format("%a");
				$daysCountCPM = $daysCount + 1;
				if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
					$offerDetails = MakeOffer::where([
						['campaign_id', '=', $campaignproduct->campaign_id],
						['status', '=', 20],
					])->get();

					if(isset($offerDetails) && count($offerDetails)==1){
						//echo 'offer exists';exit;
						foreach($offerDetails as $offerDetails){
							$offerprice = $offerDetails->price;
							$stripe_percent=$getproductDetails->stripe_percent;
							$price = $campaignproduct->price;
							$priceperday = $price;
							$priceperselectedDates = $priceperday;
							$newpricepercentage = ($priceperselectedDates/$camptot) * 100;
							//$newofferprice = ($offerprice * ($newpricepercentage))/100;
							if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
								$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
								$newofferprice = $newofferprice_wq*$campaignproduct->quantity;
							}else{
								$newofferprice = ($offerprice * ($newpricepercentage))/100;
							}
							$offerpriceperselectedDates = $newofferprice;
							$newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
							$newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
						}
					}else{
						$price = $campaignproduct->price;
						$stripe_percent=$getproductDetails->stripe_percent;
						 $priceperday = $price;
						 $priceperselectedDates = $priceperday;
						$offerprice = $campaignproduct->price;
						if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
							$newofferprice = $offerprice*$campaignproduct->quantity;
						}else{
							$newofferprice = $offerprice ;
						}
						$offerpriceperselectedDates = $newofferprice;
						$newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
						$newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
					}
					if(isset($campaignproduct->tax_percentage)){
						$tax_percentage_booking = $campaignproduct->tax_percentage;
					}else{
						$tax_percentage_booking = 0;
					}
					if($tax_percentage_booking != 0){
						$campaignproduct->tax_percentage_amount_shortlist = ($priceperselectedDates * ($tax_percentage_booking))/100;
						$tax_percentage_amount = ($offerpriceperselectedDates * ($tax_percentage_booking))/100;
					}
					$campaignproduct->tax_percentage_amount = round($tax_percentage_amount,2); 
					$tax_percentage_amount_sum += round($tax_percentage_amount,2);
					$shortlistedsum+= ($priceperselectedDates+$campaignproduct->tax_percentage_amount_shortlist)*$campaignproduct->quantity;

					$offershortlistedsum+= $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;

					$shortlistedsumcpm += $priceperselectedDates*$campaignproduct->quantity;
					$offershortlistedsumcpm += $offerpriceperselectedDates;
					$impressions = $getproductDetails->secondImpression;
					$impressionsperday = (float)($impressions/7);
					$impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
					$impressionSum+= $impressionsperselectedDates*$campaignproduct->quantity;
                    $newprocessingfeeamtSum += $newprocessingfeeamt;
				}else{
					$offerDetails = MakeOffer::where([
						['campaign_id', '=', $campaignproduct->campaign_id],
						['status', '=', 20],
					])->get();
					
					
					if(isset($offerDetails) && count($offerDetails)==1){
						//echo 'variable -offer'; exit;;
							foreach($offerDetails as $offerDetails){
								$offerprice = $offerDetails->price;
								$stripe_percent=$getproductDetails->stripe_percent;
								$price = $getproductDetails->default_price;
								///variable_offer///
									$priceperday = $campaignproduct->price;
									$priceperselectedDates = $priceperday;
								
								$newpricepercentage = ($priceperselectedDates/$camptot) * 100;
								//$newofferprice = ($offerprice * ($newpricepercentage))/100;//exit;
								if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
									$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
									$newofferprice = $newofferprice_wq*$campaignproduct->quantity;
								}else{
									$newofferprice = ($offerprice * ($newpricepercentage))/100;
								}
								$offerpriceperselectedDates = $newofferprice;
								$newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
								$newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
							}
							
					}else{
						$price = $campaignproduct->price;
						$stripe_percent=$campaignproduct->stripe_percent;
						$priceperday = $price;//exit;
						$priceperselectedDates = $priceperday;
						$offerprice = $campaignproduct->price;
						if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
							$newofferprice = $offerprice*$campaignproduct->quantity;
						}else{
							$newofferprice = $offerprice;
						}
						$offerpriceperselectedDates = $newofferprice;
						$newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
						$newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
					}
					if(isset($campaignproduct->tax_percentage)){
						$tax_percentage_booking = $campaignproduct->tax_percentage;
					}else{
						$tax_percentage_booking = 0;
					}
					if($tax_percentage_booking != 0){
						$campaignproduct->tax_percentage_amount_shortlist = ($priceperselectedDates * ($tax_percentage_booking))/100;
						$tax_percentage_amount = ($offerpriceperselectedDates * ($tax_percentage_booking))/100;
					}
					$campaignproduct->tax_percentage_amount = round($tax_percentage_amount,2);
					$tax_percentage_amount_sum += round($tax_percentage_amount,2);
					$shortlistedsum+= ($priceperselectedDates+$campaignproduct->tax_percentage_amount_shortlist)*$campaignproduct->quantity;

					$offershortlistedsum+= $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;
					$shortlistedsumcpm += $priceperselectedDates*$campaignproduct->quantity;
					$offershortlistedsumcpm += $offerpriceperselectedDates;
					$impressions = $getproductDetails->secondImpression;
					$impressionsperday = (float)($impressions/7);
					$impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
					$impressionSum+= $impressionsperselectedDates*$campaignproduct->quantity;
                    $newprocessingfeeamtSum += $newprocessingfeeamt;
				}
			}
			
		}
		
		
		$impressionSum4 = round($impressionSum, 2);
		if($impressionSum4>0){
            $cpmval = ($shortlistedsumcpm/$impressionSum4) * 1000;
            $offercpmval = ($offershortlistedsumcpm/$impressionSum4) * 1000;
        }else{
            $cpmval = 0;
            $offercpmval = 0;
        }
		 
		if($offershortlistedsum != 0){
			$campaign_cpmval = $offercpmval;
		}else{
			 $campaign_cpmval = $cpmval;
		}
		
		$campaign_impressionSum = $impressionSum4;
		
		$campaign_shortlistedsum = $shortlistedsum;
		
		// Buyer admin end
		
		$products_arr_seller_final = [];
		
		if ($user->client->client_type->type == "bbi") {
            
            if ($campaign_obj->type == Campaign::$CAMPAIGN_USER_TYPE['user'] && $campaign_obj->status == Campaign::$CAMPAIGN_STATUS['booking-requested']) {
            //if ($campaign_obj->type == Campaign::$CAMPAIGN_USER_TYPE['user'] && $campaign_obj->status == 100) {
                $campaign_obj->status = Campaign::$CAMPAIGN_STATUS['scheduled'];
                if ($campaign_obj->save()) {
                    // camapign launch successful. lock the products.
                    $campaign_products = ProductBooking::where('campaign_id', '=', $campaign_obj->id)->get();
                    foreach ($campaign_products as $campaign_product) {
                        $campaign_product->product_status = ProductBooking::$PRODUCT_STATUS['scheduled'];
                        $campaign_product->save();
                    }

                    // send the email to user.
                    $campaign_user_mongo = UserMongo::where('id', '=', $campaign_obj->created_by)->first();
                    
                    // notifications and emails for user start 
                    event(new CampaignLaunchEvent([
                      'type' => Notification::$NOTIFICATION_TYPE['campaign-launched'],
                        'from_id' => null,
                        'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['user'],
                        'to_id' => $campaign_obj->created_by,
                        'to_client' => $campaign_obj->created_by,
                        'c_id' => $campaign_obj->cid,
                        'c_name' => $campaign_obj->name,
                        //'desc' => "Campaign launched",
                        'desc' => "Campaign confirmed",
                        //'message' => "Your Campaign has been launched!",
                        'message' => "Your Campaign has been confirmed!",
                        'data' => ["campaign_id" => $campaign_obj->id]
                    ]));
                    $notification_obj = new Notification;
                    $notification_obj->id = uniqid();
                    $notification_obj->type = "campaign";
                    $notification_obj->from_id = null;
                    $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['user'];
                    $notification_obj->to_id = $campaign_obj->created_by;
                    $notification_obj->to_client = $campaign_obj->created_by;
                    $notification_obj->c_id = $campaign_obj->cid;
                    $notification_obj->c_name = $campaign_obj->name;
                    //$notification_obj->desc = "Campaign launched";
                    $notification_obj->desc = "Campaign confirmed";
                    //$notification_obj->message = "Your Campaign has been launched!";
                    $notification_obj->message = "Your Campaign has been Confirmed!";
                    $notification_obj->campaign_id = $campaign_obj->id;
                    $notification_obj->status = 0;
                    $notification_obj->save();
					
					// notification to Admin when confirm campaign
                    event(new CampaignLaunchEvent([
                      'type' => Notification::$NOTIFICATION_TYPE['campaign-launched'],
                        'from_id' => null,
                        'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
                        'to_id' => null,
                        'to_client' => null,
                        'c_id' => $campaign_obj->cid,
                        'c_name' => $campaign_obj->name,
                        //'desc' => "Campaign launched",
                        'desc' => "Campaign confirmed",
                        //'message' => "Your Campaign has been launched!",
                        'message' => "Your Campaign has been confirmed!",
                        'data' => ["campaign_id" => $campaign_obj->id]
                    ]));
                    $notification_obj = new Notification;
                    $notification_obj->id = uniqid();
                    $notification_obj->type = "campaign";
                    $notification_obj->from_id = null;
                    $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['bbi'];
                    $notification_obj->to_id = null;
                    $notification_obj->to_client = null;
                    $notification_obj->c_id = $campaign_obj->cid;
                    $notification_obj->c_name = $campaign_obj->name;
                    //$notification_obj->desc = "Campaign launched";
                    $notification_obj->desc = "Campaign confirmed";
                    //$notification_obj->message = "Your Campaign has been launched!";
                    //$notification_obj->message = "You have confirmed a Campaign has been Confirmed!";
                    $notification_obj->message = 'You have confirmed a campaign ' . $campaign_obj->name;
                    $notification_obj->campaign_id = $campaign_obj->id;
                    $notification_obj->status = 0;
                    $notification_obj->save();
   
                /*campaign-report data*/ 
                    $campaign = Campaign::where('id', '=', $campaign_id)->first();
                    //echo '<pre>campaign';print_r($campaign);exit; 
                    //$campaign_id=$this->input['campaign_id'];
                    if($campaign->status < 1000){
                    $product_ids_in_campaign = ProductBooking::where('campaign_id', '=', $campaign_id)->pluck('product_id');

                    $products_in_campaign = Product::whereIn('id', $product_ids_in_campaign)->get();

                    $formats = $products_in_campaign->unique('type')->count();
                    $areas = $products_in_campaign->unique('area')->count();
                    $audience_reach = $products_in_campaign->each(function($v, $k) {
                    $v->impressions = intval(str_replace(",", "", $v->impressions));
                    })->sum('impressions');
                    $repeated_audience = $audience_reach * 30 / 100;
					
					$campaign_products = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
                    

                    $products_arr = [];
                    if (isset($campaign_products) && count($campaign_products) > 0) {
						$price_pdf = 0;
						foreach ($campaign_products as $campaign_product) {
							$product =Product::where('id', '=', $campaign_product->product_id)->first();
							if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
								$price_pdf = $campaign_product->price*$campaign_product->quantity;
							}else{
								$price_pdf = $campaign_product->price;
							}
							$tax_percentage_booking = $product->tax_percentage;
							if($tax_percentage_booking != 0){
								$tax_percentage_amount = ($price_pdf * ($tax_percentage_booking))/100;
							}
							$campaign_product->tax_percentage_amount = round($tax_percentage_amount,2); 
							$area_time_zone_value = Area::select("area_time_zone","area_time_zone_type")->where('id', '=', $product->area)->first();
							$campaign_product->area_time_zone_type = $area_time_zone_value['area_time_zone_type'];
							$client_mongo = ClientMongo::where('id', '=', $product->client_mongo_id)->first();
							//array_push($products_arr, array_merge(Product::where('id', '=', $campaign_product->product_id)->first()->toArray(), $campaign_product->toArray()));
							array_push($products_arr, array_merge(Product::where('id', '=', $campaign_product->product_id)->first()->toArray(), $campaign_product->toArray(), $client_mongo->toArray()));
						}           
					}}
                     
                    $total_price = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('owner_price');
                    if($total_price == 0){
                    $total_price = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('price');
                    }
					
					
					$client_details = array();
					$client_details_single = array();
					$temp = array_unique(array_column($products_arr, 'siteNo'));
					$unique_arr = array_intersect_key($products_arr, $temp);
					$product_ids_in_campaign = ProductBooking::where('campaign_id', '=', $campaign_id)->pluck('product_id');
					$products_in_campaign = Product::whereIn('id', $product_ids_in_campaign)->get();
					foreach($unique_arr as $key => $value){
						$j = 0;
						foreach ($products_in_campaign as $product) {
							if($value['siteNo'] == $product['siteNo']){
								if(!is_array($product['vendor'])){
									$client_details_single = ClientMongo::select('company_name','contact_email','phone','address')->where('client_id', '=', $product['client_id'])->first();
									if(isset($client_details_single)){
										$client_details[] = $client_details_single->toArray();
									}else{
										$client_details[] = array();
									}
								}else{
									$client_details[] = array();
								}
							}
						}
					}
					
					$paid_percentage = $campaign->total_paid*100/$total_price;
					$processing_fee_sum = $newprocessingfeeamtSum;
					
                    $campaign_report = [
                    'campaign' => $campaign,
                    'areas_covered' => $areas,
                    'format_types' => $formats,
                    'mediums_covered' => $products_in_campaign->count(),
                    'audience_reach' => $audience_reach,
                    'repeated_audience' => $repeated_audience,
                    'products' => $products_in_campaign,
					'campaign_shortlistedsum'=>$campaign_shortlistedsum,
					'campaign_cpmval'=>$campaign_cpmval,
					'campaign_impressionSum'=>$campaign_impressionSum,
                    'total_price'=>$total_price,
                    'products_arr'=>$products_arr,
					'client_details_billing'=>$client_details,
					'paid_percentage'=>$paid_percentage,
					'processing_fee_sum'=>$processing_fee_sum,
					'user_mongo_report'=>$user_mongo,
					'tax_percentage_amount_sum'=>$tax_percentage_amount_sum,
                    ];
                    
                    //echo '<pre>campaign_report';print_r($campaign_report);exit; 
                /*campaign-report data*/
  
                    //$pdf = PDF::loadView('pdf.launch_campaign_details_pdf',$campaign_report);
					$pdf = PDF::loadView('pdf.IO_pdf',$campaign_report);
                    
                    //echo '<pre>pdf';print_r($pdf);exit; 
                    
                    $mail_tmpl_params = [
                      'sender_email' => $user['email'], 
                      //'receiver_name' => $bbi_sa->first_name . ' ' . $bbi_sa->last_name,
                      'receiver_name' => $campaign_user_mongo->first_name,
                      'mail_message' => "Your campaign '" . $campaign_obj->name . "' has been confirmed. Visit our website to see details."
                    ];
                    $mail_data = [
                      //'email_to' => $bbi_sa->email,
                      'email_to' => $campaign_user_mongo->email,
                      //'email_to1' => 'sandhyarani.manelli@peopletech.com',
                      'email_to1' => 'admin@advertisingmarketplace.com',
                      //'email_to1' => 'shiva.karunakar@peopletech.com',
                      'recipient_name1' => 'Richard',
                      'recipient_name' => $campaign_user_mongo->first_name,
                      //'pdf_file_name' => "Insertion Order-". date('m-d-Y') . ".pdf",
                      //'pdf_file_name' => "Insertion Order CDHP covid vac 10 27 21" . ".pdf",
                      'pdf_file_name' => "IO-". $campaign_obj->cid . "-" . date('m-d-Y') . ".pdf",
                      'pdf' => $pdf
                    ];
                    Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data){
                     // $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Super admin set up for company - Billboards India');
                      $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Campaign Confirmed! - Advertising Marketplace');
                      $message->cc($mail_data['email_to1'], $mail_data['recipient_name1'])->subject('Campaign Confirmed! - Advertising Marketplace');
                      $message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
                    });
                    
                    // notifications and emails for user end


                    // notifications and emails for owner
                    $campaign_product->product_status = ProductBooking::$PRODUCT_STATUS['scheduled'];
                    $campaign_product_owner_ids = ProductBooking::raw(function($collection) use ($campaign_id) {
                                return $collection->distinct('product_owner', ['campaign_id' => $campaign_id]);
                            });
                    $owner_notif_recipients = ClientMongo::whereIn('id', $campaign_product_owner_ids)->get();
                    $owner_sa_ids = [];
					
                    foreach ($owner_notif_recipients as $owner_notif_recipient) {
                        if (isset($owner_notif_recipient->super_admin_m_id) && !empty($owner_notif_recipient->super_admin_m_id)) {
                            array_push($owner_sa_ids, $owner_notif_recipient->id);
                        }
                    }
					
					if(isset($owner_sa_ids) && !empty($owner_sa_ids)){
						$price_pdf_seller = 0;
						foreach($owner_sa_ids as $key => $value){
							$products_arr_seller_act = [];
							$products_arr_seller_final = [];
							$shortlistedsum_seller = 0;
							$shortlistedsumcpm_seller = 0;
							$impressionSum_seller = 0;
							$offercpmsum_seller = 0;
							$impressionSum4_seller = 0;
							$offershortlistedsum_seller = 0;
							$offershortlistedsumcpm_seller = 0;
							$tax_percentage_booking_seller = 0;
							$tax_percentage_amount_seller = 0;
							$tax_percentage_amount_sum_seller = 0;
							$tax_percentage_booking_tot_seller = 0;
							$tax_percentage_amount_tot_seller = 0;
							$products_arr_seller = ProductBooking::where([
								['campaign_id', '=', $campaign_id],
								['product_owner', '=', $value]
							])->get();
							foreach($products_arr_seller as $keys => $values){
								$product_seller =Product::where('id', '=', $values->product_id)->first();
								if(isset($values->quantity) && $values->quantity != '' && $values->quantity != 0){
									$price_pdf_seller = $values->price*$values->quantity;
								}else{
									$price_pdf_seller = $values->price;
								}
								$tax_percentage_booking_seller = $product_seller->tax_percentage;
								if($tax_percentage_booking_seller != 0){
									$tax_percentage_amount_seller = ($price_pdf_seller * ($tax_percentage_booking_seller))/100;
								}
								$values->tax_percentage_amount = round($tax_percentage_amount_seller,2); 
								$area_time_zone_value_seller = Area::select("area_time_zone","area_time_zone_type")->where('id', '=', $product_seller->area)->first();
								$values->area_time_zone_type = $area_time_zone_value_seller['area_time_zone_type'];
								array_push($products_arr_seller_final, array_merge(Product::where('id', '=', $values->product_id)->first()->toArray(), $campaign_product->toArray()));
							}
							
							$owner_email = ClientMongo::where('id', $value)->select("contact_email")->first();
							$final_email = '';
							if(isset($owner_email->contact_email) && $owner_email->contact_email != ''){
								$final_email = $owner_email->contact_email;
							}else{
								$owner_email = ClientMongo::where('id', $value)->select("email")->first();
								$final_email = $owner_email->email;
							}
							
							$campaignproducts_seller = ProductBooking::where([
								['campaign_id', '=', $campaign_id],
								['product_owner', '=', $value]
							])->get();
							
							
							$getcampaigntot_seller = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
							$camptot_seller = 0;
							$offer_applied = 0;
							if (isset($getcampaigntot_seller) && count($getcampaigntot_seller) > 0) {
								foreach ($getcampaigntot_seller as $getcampaigntot_seller) {
									$tax_percentage_booking_tot_seller = 0;
									$tax_percentage_amount_tot_seller = 0;
									$getcampaigntotproduct_seller =Product::where('id', '=', $getcampaigntot_seller->product_id)->first();
									$bookedfrom[] = strtotime($getcampaigntot_seller->booked_from);
									$bookedto[] = strtotime($getcampaigntot_seller->booked_to);

									$getproductDetails_seller =Product::where('id', '=', $getcampaigntot_seller->product_id)->first();
							
									$diff_seller=date_diff(date_create($getcampaigntot_seller->booked_from),date_create($getcampaigntot_seller->booked_to));
									$daysCount_seller = $diff_seller->format("%a");
									$daysCountCPM_seller = $daysCount_seller + 1;
									$perdayprice_seller = $getproductDetails_seller->default_price/28;

									if(isset($getproductDetails_seller->fix) && $getproductDetails_seller->fix=="Fixed"){
										$price_seller = $getcampaigntot_seller->price*$getcampaigntot_seller->quantity;
										if(isset($getcampaigntot_seller->tax_percentage)){
											$tax_percentage_booking_tot_seller = $getcampaigntot_seller->tax_percentage;
										}else{
											$tax_percentage_booking_tot_seller = 0;
										}
										if($tax_percentage_booking_tot_seller != 0){
											$tax_percentage_amount_tot_seller = ($price_seller * ($tax_percentage_booking_tot_seller))/100;
										}
										$tax_percentage_amount_tot_seller = round($tax_percentage_amount_tot_seller,2); 
										$camptot_seller += $price_seller+$tax_percentage_amount_tot_seller;
									}else{
										
										$priceperselectedDates_seller = $getcampaigntot_seller->price;
										if(isset($getcampaigntot_seller->tax_percentage)){
											$tax_percentage_booking_tot_seller = $getcampaigntot_seller->tax_percentage;
										}else{
											$tax_percentage_booking_tot_seller = 0;
										}
										if($tax_percentage_booking_tot_seller != 0){
											$tax_percentage_amount_tot_seller = ($priceperselectedDates_seller * ($tax_percentage_booking_tot_seller))/100;
										}
										$tax_percentage_amount_tot_seller = round($tax_percentage_amount_tot_seller,2); 
										$camptot_seller += ($priceperselectedDates_seller*$getcampaigntot_seller->quantity)+($tax_percentage_amount_tot_seller*$getcampaigntot_seller->quantity);
									}
								}
							}
							
							if (isset($campaignproducts_seller) && count($campaignproducts_seller) > 0) {

								foreach ($campaignproducts_seller as $campaignproduct_seller) {
									$campaignproduct_seller->tax_percentage_amount_shortlist_seller = 0;
									$campaignproduct_seller->tax_percentage_amount = 0;
									$tax_percentage_booking_seller = 0;
									$tax_percentage_amount_seller = 0;
									$getproductDetails_seller =Product::where('id', '=', $campaignproduct_seller->product_id)->first();
									$diff_seller=date_diff(date_create($campaignproduct_seller->booked_from),date_create($campaignproduct_seller->booked_to));
									$daysCount_seller = $diff_seller->format("%a");
									$daysCountCPM_seller = $daysCount_seller + 1;
									if(isset($getproductDetails_seller->fix) && $getproductDetails_seller->fix=="Fixed"){
										$offerDetails_seller = MakeOffer::where([
											['campaign_id', '=', $campaignproduct_seller->campaign_id],
											['status', '=', 20],
										])->get();

										if(isset($offerDetails_seller) && count($offerDetails_seller)==1){
											//echo 'offer exists';exit;
											foreach($offerDetails_seller as $offerDetails_seller){
												$offerprice_seller = $offerDetails_seller->price;
												$stripe_percent_seller=$getproductDetails_seller->stripe_percent;
												
												$price_seller = $campaignproduct_seller->price;
												$priceperday_seller = $price_seller;
												$priceperselectedDates_seller = $priceperday_seller;
												$newpricepercentage_seller = ($priceperselectedDates_seller/$camptot_seller) * 100;
												//$newofferprice_seller = ($offerprice_seller * ($newpricepercentage_seller))/100;
												if(isset($campaignproduct_seller->quantity) && $campaignproduct_seller->quantity != '' && $campaignproduct_seller->quantity != 0){
													$newofferprice_seller_wq = ($offerprice_seller * ($newpricepercentage_seller))/100;
													$newofferprice_seller = $newofferprice_seller_wq*$campaignproduct_seller->quantity;
												}else{
													$newofferprice_seller = ($offerprice_seller * ($newpricepercentage_seller))/100;
												}
												$offerpriceperselectedDates_seller = $newofferprice_seller;
												$newofferStripepercentamt_seller = ($newofferprice_seller * ($stripe_percent_seller))/100;
												$newprocessingfeeamt_seller = ((2.9) * $newofferStripepercentamt_seller)/100;
											}
										}else{
											$price_seller = $campaignproduct_seller->price;
												$stripe_percent_seller=$getproductDetails_seller->stripe_percent;
											 $priceperday_seller = $price_seller;
											 $priceperselectedDates_seller = $priceperday_seller;
											$offerprice_seller = $campaignproduct_seller->price;
											if(isset($campaignproduct_seller->quantity) && $campaignproduct_seller->quantity != '' && $campaignproduct_seller->quantity != 0){
												$newofferprice_seller = $offerprice_seller*$campaignproduct_seller->quantity;
											}else{
												$newofferprice_seller = $offerprice_seller ;
											}
											$offerpriceperselectedDates_seller = $newofferprice_seller;
											$newofferStripepercentamt_seller = ($newofferprice_seller * ($stripe_percent_seller))/100;
											$newprocessingfeeamt_seller = ((2.9) * $newofferStripepercentamt_seller)/100;
										}
										if(isset($campaignproduct_seller->tax_percentage)){
											$tax_percentage_booking_seller = $campaignproduct_seller->tax_percentage;
										}else{
											$tax_percentage_booking_seller = 0;
										}
										if($tax_percentage_booking_seller != 0){
											$campaignproduct_seller->tax_percentage_amount_shortlist_seller = ($priceperselectedDates_seller * ($tax_percentage_booking_seller))/100;
											$tax_percentage_amount_seller = ($offerpriceperselectedDates_seller * ($tax_percentage_booking_seller))/100;
										}
										$campaignproduct_seller->tax_percentage_amount = round($tax_percentage_amount_seller,2);
										$tax_percentage_amount_sum_seller += round($tax_percentage_amount_seller,2);
										$shortlistedsum_seller += ($priceperselectedDates_seller+$campaignproduct_seller->tax_percentage_amount_shortlist_seller)*$campaignproduct_seller->quantity;

										$offershortlistedsum_seller+= $offerpriceperselectedDates_seller+$campaignproduct_seller->tax_percentage_amount;
										$shortlistedsumcpm_seller += $priceperselectedDates_seller*$campaignproduct_seller->quantity;
										$offershortlistedsumcpm_seller+= $offerpriceperselectedDates_seller;
										$impressions_seller = $getproductDetails_seller->secondImpression;
										$impressionsperday_seller = (float)($impressions_seller/7);
										$impressionsperselectedDates_seller = $impressionsperday_seller * $daysCountCPM_seller;//exit;
										$impressionSum_seller += $impressionsperselectedDates_seller*$campaignproduct_seller->quantity;
										$newprocessingfeeamtSum_seller += $newprocessingfeeamt_seller;
									}else{
										$offerDetails_seller = MakeOffer::where([
											['campaign_id', '=', $campaignproduct_seller->campaign_id],
											['status', '=', 20],
										])->get();
										
										
										if(isset($offerDetails_seller) && count($offerDetails_seller)==1){
											//echo 'variable -offer'; exit;;
												foreach($offerDetails_seller as $offerDetails_seller){
													$offerprice_seller = $offerDetails_seller->price;
													$stripe_percent_seller=$getproductDetails_seller->stripe_percent;
													$price_seller = $getproductDetails_seller->default_price;
													///variable_offer///
													$priceperday_seller = $campaignproduct_seller->price;
													$priceperselectedDates_seller = $priceperday_seller;
													
													$newpricepercentage_seller = ($priceperselectedDates_seller/$camptot_seller) * 100;
													//$newofferprice_seller = ($offerprice_seller * ($newpricepercentage_seller))/100;//exit;
													if(isset($campaignproduct_seller->quantity) && $campaignproduct_seller->quantity != '' && $campaignproduct_seller->quantity != 0){
														$newofferprice_seller_wq = ($offerprice_seller * ($newpricepercentage_seller))/100;
														$newofferprice_seller = $newofferprice_seller_wq*$campaignproduct_seller->quantity;
													}else{
														$newofferprice_seller = ($offerprice_seller * ($newpricepercentage_seller))/100;
													}
													$offerpriceperselectedDates_seller = $newofferprice_seller;
													
													$newofferStripepercentamt_seller = ($newofferprice_seller * ($stripe_percent_seller))/100;
													$newprocessingfeeamt_seller = ((2.9) * $newofferStripepercentamt_seller)/100;
												}
												
										}else{
											$price_seller = $campaignproduct_seller->price;
											$stripe_percent_seller=$getproductDetails_seller->stripe_percent;
											$priceperday_seller = $price_seller;//exit;
											$priceperselectedDates_seller = $priceperday_seller;
											$offerprice_seller = $campaignproduct_seller->price;
											if(isset($campaignproduct_seller->quantity) && $campaignproduct_seller->quantity != '' && $campaignproduct_seller->quantity != 0){
												$newofferprice_seller = $offerprice_seller*$campaignproduct_seller->quantity;
											}else{
												$newofferprice_seller = $offerprice_seller;
											}
											$offerpriceperselectedDates_seller = $newofferprice_seller;
											$newofferStripepercentamt_seller = ($newofferprice_seller * ($stripe_percent_seller))/100;
											$newprocessingfeeamt_seller = ((2.9) * $newofferStripepercentamt_seller)/100;
										}
										if(isset($campaignproduct_seller->tax_percentage)){
											$tax_percentage_booking_seller = $campaignproduct_seller->tax_percentage;
										}else{
											$tax_percentage_booking_seller = 0;
										}
										if($tax_percentage_booking_seller != 0){
											$campaignproduct_seller->tax_percentage_amount_shortlist_seller = ($priceperselectedDates_seller * ($tax_percentage_booking_seller))/100;
											$tax_percentage_amount_seller = ($priceperselectedDates_seller * ($tax_percentage_booking_seller))/100;
										}
										$campaignproduct_seller->tax_percentage_amount = round($tax_percentage_amount_seller,2);
										$tax_percentage_amount_sum_seller += round($tax_percentage_amount_seller,2);
										$shortlistedsum_seller += ($priceperselectedDates_seller+$campaignproduct_seller->tax_percentage_amount_shortlist_seller)*$campaignproduct_seller->quantity;

										$offershortlistedsum_seller+= $offerpriceperselectedDates_seller+$campaignproduct_seller->tax_percentage_amount;
										$shortlistedsumcpm_seller += $priceperselectedDates_seller*$campaignproduct_seller->quantity;
										$offershortlistedsumcpm_seller+= $offerpriceperselectedDates_seller;
										$impressions_seller = $getproductDetails_seller->secondImpression;
										$impressionsperday_seller = (float)($impressions_seller/7);
										$impressionsperselectedDates_seller = $impressionsperday_seller * $daysCountCPM_seller;//exit;
										$impressionSum_seller += $impressionsperselectedDates_seller *$campaignproduct_seller->quantity;
										$newprocessingfeeamtSum_seller += $newprocessingfeeamt_seller;
									}
									array_push($products_arr_seller_act, array_merge(Product::where('id', '=', $campaignproduct_seller->product_id)->first()->toArray(), $campaignproduct_seller->toArray()));
								}
								$actbudg_seller = $products_arr_seller_act;
								$res_seller = array_sum(array_map(function($item) { 
									if(isset($item['quantity']) && $item['quantity'] != '' && $item['quantity'] != 0){
										return ($item['price']+$item['tax_percentage_amount'])*$item['quantity']; 
									}else{
										return $item['price']+$item['tax_percentage_amount']; 
									}
								}, $actbudg_seller));
							}
							
							
							$impressionSum4_seller = round($impressionSum_seller, 2);
							if($impressionSum4_seller >0){
								$cpmval_seller = ($shortlistedsumcpm_seller/$impressionSum4_seller) * 1000;
								$offercpmval_seller = ($offershortlistedsumcpm_seller/$impressionSum4_seller) * 1000;
							}else{
								$cpmval_seller = 0;
								$offercpmval_seller = 0;
							}
							 
							if($offershortlistedsum_seller != 0){
								$campaign_cpmval_seller = $offercpmval_seller;
								$total_price_seller = $offershortlistedsum_seller;
							}else{
								 $campaign_cpmval_seller = $cpmval_seller;
								 $total_price_seller = $res_seller;
							}
							
							$campaign_impressionSum_seller = $impressionSum4_seller;
							
							$campaign_shortlistedsum_seller = $shortlistedsum_seller;
							
							$paid_percentage_seller = $campaign->total_paid*100/$total_price_seller;
							$processing_fee_sum_seller = $newprocessingfeeamtSum_seller;
							
							$campaign_report_seller = [
								'campaign' => $campaign,
								'areas_covered' => $areas,
								'format_types' => $formats,
								'mediums_covered' => $products_in_campaign->count(),
								'audience_reach' => $audience_reach,
								'repeated_audience' => $repeated_audience,
								'products' => $products_in_campaign,
								'campaign_shortlistedsum'=>$campaign_shortlistedsum_seller,
								'campaign_cpmval'=>$campaign_cpmval_seller,
								'campaign_impressionSum'=>$campaign_impressionSum_seller,
								'total_price'=>$total_price_seller,
								'products_arr'=>$products_arr_seller_final,
								'client_details_billing'=>$client_details,
								'paid_percentage'=>$paid_percentage_seller,
								'processing_fee_sum'=>$processing_fee_sum_seller,
								'user_mongo_report'=>$user_mongo,
								'tax_percentage_amount_sum'=>$tax_percentage_amount_sum_seller,
							];
							
							
							$pdf = PDF::loadView('pdf.IO_pdf', $campaign_report_seller);
							$mail_tmpl_params = [
							  'sender_email' => config('app.bbi_email'), 
							  'receiver_name' => '',
							  'mail_message' => "Your campaign '" . $campaign_obj->name . "' has been received. Attached below is your Insertion Order with details of the order you placed.<br>
							  Visit your Advertising Marketplace seller account for additional information. If you have any questions please contact me directly at (949).226.1279.<br> <br>
							  Thank you for using Advertising Marketplace."
							];
							$mail_data = [
							  'bcc' => $final_email,
							  'email_to1' => 'admin@advertisingmarketplace.com',
							  'recipient_name1' => 'Richard',
							  'recipient_name' => '',
							  'pdf_file_name' => "IO-". $campaign_obj->cid . "-" . date('m-d-Y') . ".pdf",
							  'pdf' => $pdf
							];
							if($final_email !=''){
								Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data){
								  $message->to($mail_data['bcc'], $mail_data['recipient_name'])->subject('User campaign confirmed! - Advertising Marketplace');
								  $message->cc($mail_data['email_to1'], $mail_data['recipient_name1'])->subject('User Campaign Confirmed! - Advertising Marketplace');
								  $message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
								});
							}
						}
					}
                    
                    //notification Email Owner Start
                    
                        event(new CampaignLaunchEvent([
                      'type' => Notification::$NOTIFICATION_TYPE['campaign-launched'],
                        'from_id' => null,
                        'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['owner'],
                        'to_id' => null,
                        'to_client' => $campaign_product_owner_ids,
                        //'desc' => "Campaign launched",
                        'desc' => "Campaign confirmed",
                        //'message' => "Your Campaign has been launched!",
                        'message' => "A campaign with your products in it has been confirmed.",
                        'data' => ["campaign_id" => $campaign_obj->id]
                    ]));
                     
                    foreach ($campaign_product_owner_ids as $key => $val) {
                    $notification_obj = new Notification;
                        $notification_obj->id = uniqid();
                        $notification_obj->type = "campaign";
                        $notification_obj->from_id = null;
                        $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['owner'];
                        $notification_obj->to_id = $val;
                        $notification_obj->to_client = $val;
                        //$notification_obj->desc = "Campaign launched";
                        $notification_obj->desc = "Campaign confirmed";
                        $notification_obj->message = "A campaign with your products in it has been confirmed!";
                        $notification_obj->campaign_id = $campaign_obj->id;
                        $notification_obj->status = 0;
                        $notification_obj->save();

                    }
                    
                    //notification owner end
					
					//Admin mail start
					
					$pdf = PDF::loadView('pdf.IO_pdf',$campaign_report);
                    
                    //echo '<pre>pdf';print_r($pdf);exit;  
                    
                    $mail_tmpl_params = [
                      'sender_email' => $user['email'], 
                      //'receiver_name' => $bbi_sa->first_name . ' ' . $bbi_sa->last_name,
                      'receiver_name' => '',
                      //'receiver_name' => 'San',
                      'mail_message' => "You have confimed campaign '" . $campaign_obj->name . "'."
                    ];
                    $mail_data = [
                      //'email_to' => $bbi_sa->email,
                      //'email_to' => $user_mongo->email,
                      //'email_to' => 'sandhyarani.manelli@peopletech.com',
                      //'email_to1' => 'admin@advertisingmarketplace.com',
                      'email_to' => 'shiva.karunakar@peopletech.com',
                      //'recipient_name1' => 'Richard',
                      'recipient_name' => '',
                      //'recipient_name' => 'San',
                      //'pdf_file_name' => "Insertion Order-". date('m-d-Y') . ".pdf",
					  //'pdf_file_name' => "Insertion Order CDHP covid vac 10 27 21" . ".pdf",
					  'pdf_file_name' => "IO-". $campaign_obj->cid . "-" . date('m-d-Y') . ".pdf",
                      'pdf' => $pdf
                    ];
                    Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data){
                     // $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Super admin set up for company - Billboards India');
                      $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('User Campaign Confirmed! - Advertising Marketplace');
                      //$message->cc($mail_data['email_to1'], $mail_data['recipient_name1'])->subject('User Campaign Confirmed! - Advertising Marketplace');
                      $message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
                    });
					
					//Admin mail end
  
                    
                    //return response()->json(["status" => "1", "message" => "Campaign launched successfully."]);
                    return response()->json(["status" => "1", "message" => "Campaign confirmed successfully."]);
                } else {
                    return response()->json(["status" => 0, "message" => "There was a technical error while launching your campaign."]);
                }
            } else if ($campaign_obj->type == Campaign::$CAMPAIGN_USER_TYPE['bbi']) {
                $campaign_obj->status = Campaign::$CAMPAIGN_STATUS['scheduled'];
                if ($campaign_obj->save()) {
                    // camapign launch successful. lock the products.
                    $campaign_products = ProductBooking::where('campaign_id', '=', $campaign_obj->id)->get();
                    foreach ($campaign_products as $campaign_product) {
                        $campaign_product->product_status = ProductBooking::$PRODUCT_STATUS['scheduled'];
                        $campaign_product->save();
                    }
                
                    $campaign_user = UserMongo::where('id', '=', $campaign_obj->created_by)->first();
                    
                    // notifications and emails for user start 
                    event(new CampaignLaunchEvent([
                      'type' => Notification::$NOTIFICATION_TYPE['campaign-launched'],
                        'from_id' => null,
                        'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['user'],
                        'to_id' => $campaign_obj->created_by,
                        'to_client' => $campaign_obj->created_by,
                        'c_id' => $campaign_obj->cid,
                        'c_name' => $campaign_obj->name,
                        //'desc' => "Campaign launched",
                        'desc' => "Campaign confirmed",
                        //'message' => "Your Campaign has been launched!", 
                        'message' => "A campaign with your products in it has been confirmed!",
                        'data' => ["campaign_id" => $campaign_obj->id]
                    ]));
                    $notification_obj = new Notification;
                    $notification_obj->id = uniqid();
                    $notification_obj->type = "campaign";
                    $notification_obj->from_id = null;
                    $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['user'];
                    $notification_obj->to_id = $campaign_obj->created_by;
                    $notification_obj->to_client = $campaign_obj->created_by;
                    $notification_obj->c_id = $campaign_obj->cid;
                    $notification_obj->c_name = $campaign_obj->name;
                    //$notification_obj->desc = "Campaign launched";
                    $notification_obj->desc = "Campaign confirmed";
                    //$notification_obj->message = "Your Campaign has been launched!";
                    $notification_obj->message = "A campaign with your products in it has been confirmed!";
                    $notification_obj->campaign_id = $campaign_obj->id;
                    $notification_obj->status = 0;
                    $notification_obj->save();
 
            /*campaign-report data*/
                    $campaign = Campaign::where('id', '=', $campaign_id)->first();
                    //echo '<pre>campaign';print_r($campaign);exit; 
                    //$campaign_id=$this->input['campaign_id'];
                    if($campaign->status < 1000){
                    $product_ids_in_campaign = ProductBooking::where('campaign_id', '=', $campaign_id)->pluck('product_id');

                    $products_in_campaign = Product::whereIn('id', $product_ids_in_campaign)->get();

                    $formats = $products_in_campaign->unique('type')->count();
                    $areas = $products_in_campaign->unique('area')->count();
                    $audience_reach = $products_in_campaign->each(function($v, $k) {
                    $v->impressions = intval(str_replace(",", "", $v->impressions));
                    })->sum('impressions');
                    $repeated_audience = $audience_reach * 30 / 100;
					
					$campaign_products = ProductBooking::where('campaign_id', '=', $campaign_id)->get();

                    $products_arr = [];
                    if (isset($campaign_products) && count($campaign_products) > 0) {
						$price_pdf = 0;
                    foreach ($campaign_products as $campaign_product) {
                    $product =Product::where('id', '=', $campaign_product->product_id)->first();
					if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
						$price_pdf = $campaign_product->price*$campaign_product->quantity;
					}else{
						$price_pdf = $campaign_product->price;
					}
					$tax_percentage_booking = $product->tax_percentage;
					if($tax_percentage_booking != 0){
						$tax_percentage_amount = ($price_pdf * ($tax_percentage_booking))/100;
					}
					$campaign_product->tax_percentage_amount = round($tax_percentage_amount,2);
                    $client_mongo = ClientMongo::where('id', '=', $product->client_mongo_id)->first();
                    //array_push($products_arr, array_merge(Product::where('id', '=', $campaign_product->product_id)->first()->toArray(), $campaign_product->toArray()));
                    array_push($products_arr, array_merge(Product::where('id', '=', $campaign_product->product_id)->first()->toArray(), $campaign_product->toArray(), $client_mongo->toArray()));
                    } 

                    }}

                    $total_price = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('owner_price');
                    if($total_price == 0){
                    $total_price = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('price');
                    }
					
					
					$client_details = array();
					$client_details_single = array();
					$temp = array_unique(array_column($products_arr, 'siteNo'));
					$unique_arr = array_intersect_key($products_arr, $temp);
					$product_ids_in_campaign = ProductBooking::where('campaign_id', '=', $campaign_id)->pluck('product_id');
					$products_in_campaign = Product::whereIn('id', $product_ids_in_campaign)->get();
					foreach($unique_arr as $key => $value){
						$j = 0;
						foreach ($products_in_campaign as $product) {
							if($value['siteNo'] == $product['siteNo']){
								if(!is_array($product['vendor'])){
									$client_details_single = ClientMongo::select('company_name','contact_email','phone','address')->where('client_id', '=', $product['client_id'])->first();
									if(isset($client_details_single)){
										$client_details[] = $client_details_single->toArray();
									}else{
										$client_details[] = array();
									}
								}else{
									$client_details[] = array();
								}
							}
						}
					}
		
                    $campaign_report = [
                    'campaign' => $campaign,
                    'areas_covered' => $areas,
                    'format_types' => $formats,
                    'mediums_covered' => $products_in_campaign->count(),
                    'audience_reach' => $audience_reach,
                    'repeated_audience' => $repeated_audience,
                    'products' => $products_in_campaign,
					'campaign_shortlistedsum'=>$campaign_shortlistedsum,
					'campaign_cpmval'=>$campaign_cpmval,
					'campaign_impressionSum'=>$campaign_impressionSum,
                    'total_price'=>$total_price,
                    'products_arr'=>$products_arr,
					'client_details_billing'=>$client_details,
					'tax_percentage_amount_sum'=>$tax_percentage_amount_sum_seller
                    ];
                    
                    //echo '<pre>campaign_report';print_r($campaign_report);exit; 
                /*campaign-report data*/
                
                    $pdf = PDF::loadView('pdf.IO_pdf',$campaign_report);
                    $mail_tmpl_params = [
                      'sender_email' => config('app.bbi_email'), 
                      //'receiver_name' => $bbi_sa->first_name . ' ' . $bbi_sa->last_name,
                      'receiver_name' => $campaign_user->first_name . ' ' . $campaign_user->last_name,
                      'mail_message' => 'Campaign ' . $campaign_obj->name . 'has been confirmed.'
                    ];
                    $mail_data = [
                      //'email_to' => $bbi_sa->email,
                      'email_to' => $campaign_user->email,
                      //'email_to1' => 'sandhyarani.manelli@peopletech.com',
                      'email_to1' => 'admin@advertisingmarketplace.com',
                      //'email_to1' => 'shiva.karunakar@peopletech.com',
                      'recipient_name1' => 'Richard',
                      'recipient_name' => '',
                      //'pdf_file_name' => "Insertion Order-". date('m-d-Y') . ".pdf",
					  //'pdf_file_name' => "Insertion Order CDHP covid vac 10 27 21" . ".pdf",
					  'pdf_file_name' => "IO-". $campaign_obj->cid . "-" . date('m-d-Y') . ".pdf",
                      'pdf' => $pdf
                    ];
                    Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data){
                      $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Your campaign has been confirmed! - Advertising Marketplace');
                      $message->cc($mail_data['email_to1'], $mail_data['recipient_name1'])->subject('Your campaign has been confirmed! - Advertising Marketplace');
                      $message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
                    });
                    
                    // notifications and emails for user end
                    
                    
                    // notifications and emails for owner
                    $campaign_product_owner_ids = ProductBooking::raw(function($collection) use ($campaign_id) {
                                return $collection->distinct('product_owner', ['campaign_id' => $campaign_id]);
                            });
                    $owner_notif_recipients = ClientMongo::whereIn('id', $campaign_product_owner_ids)->get();
                    $owner_sa_ids = [];
                    foreach ($owner_notif_recipients as $owner_notif_recipient) {
                        if (isset($owner_notif_recipient->super_admin_m_id) && !empty($owner_notif_recipient->super_admin_m_id)) {
                            array_push($owner_sa_ids, $owner_notif_recipient->id);
                        }
                    }
					
					if(isset($owner_sa_ids) && !empty($owner_sa_ids)){
						$price_pdf_seller = 0;
						foreach($owner_sa_ids as $key => $value){
							$products_arr_seller_final = [];
							$shortlistedsum_seller = 0;
							$shortlistedsumcpm_seller = 0;
							$impressionSum_seller = 0;
							$offercpmsum_seller = 0;
							$impressionSum4_seller = 0;
							$offershortlistedsum_seller = 0;
							$offershortlistedsumcpm_seller = 0;
							$tax_percentage_booking_seller = 0;
							$tax_percentage_amount_seller = 0;
							$tax_percentage_amount_sum_seller = 0;
							$tax_percentage_booking_tot_seller = 0;
							$tax_percentage_amount_tot_seller = 0;
							$products_arr_seller = ProductBooking::where([
								['campaign_id', '=', $campaign_id],
								['product_owner', '=', $value]
							])->get();
							foreach($products_arr_seller as $keys => $values){
								$product_seller =Product::where('id', '=', $values->product_id)->first();
								if(isset($values->quantity) && $values->quantity != '' && $values->quantity != 0){
									$price_pdf_seller = $values->price*$values->quantity;
								}else{
									$price_pdf_seller = $values->price;
								}
								$tax_percentage_booking_seller = $product_seller->tax_percentage;
								if($tax_percentage_booking_seller != 0){
									$tax_percentage_amount_seller = ($price_pdf_seller * ($tax_percentage_booking_seller))/100;
								}
								$values->tax_percentage_amount = round($tax_percentage_amount_seller,2);
								$area_time_zone_value_seller = Area::select("area_time_zone","area_time_zone_type")->where('id', '=', $product_seller->area)->first();
								$values->area_time_zone_type = $area_time_zone_value_seller['area_time_zone_type'];
								array_push($products_arr_seller_final, array_merge(Product::where('id', '=', $values->product_id)->first()->toArray(), $campaign_product->toArray()));
							}
							
							$owner_email = ClientMongo::where('id', $value)->select("contact_email")->first();
							$final_email = '';
							if(isset($owner_email->contact_email) && $owner_email->contact_email != ''){
								$final_email = $owner_email->contact_email;
							}else{
								$owner_email = ClientMongo::where('id', $value)->select("email")->first();
								$final_email = $owner_email->email;
							}
							
							$campaignproducts_seller = ProductBooking::where([
								['campaign_id', '=', $campaign_id],
								['product_owner', '=', $value]
							])->get();
							
							
							$getcampaigntot_seller = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
							$camptot_seller = 0;
							$offer_applied = 0;
							if (isset($getcampaigntot_seller) && count($getcampaigntot_seller) > 0) {
								foreach ($getcampaigntot_seller as $getcampaigntot_seller) {
									$tax_percentage_booking_tot_seller = 0;
									$tax_percentage_amount_tot_seller = 0;
									$getcampaigntotproduct_seller =Product::where('id', '=', $getcampaigntot_seller->product_id)->first();
									$bookedfrom[] = strtotime($getcampaigntot_seller->booked_from);
									$bookedto[] = strtotime($getcampaigntot_seller->booked_to);

									$getproductDetails_seller =Product::where('id', '=', $getcampaigntot_seller->product_id)->first();
							
									$diff_seller=date_diff(date_create($getcampaigntot_seller->booked_from),date_create($getcampaigntot_seller->booked_to));
									$daysCount_seller = $diff_seller->format("%a");
									$daysCountCPM_seller = $daysCount_seller + 1;
									$perdayprice_seller = $getproductDetails_seller->default_price/28;

									if(isset($getproductDetails_seller->fix) && $getproductDetails_seller->fix=="Fixed"){
										$price_seller = $getcampaigntot_seller->price*$getcampaigntot_seller->quantity;
										if(isset($getcampaigntot_seller->tax_percentage)){
											$tax_percentage_booking_tot_seller = $getcampaigntot_seller->tax_percentage;
										}else{
											$tax_percentage_booking_tot_seller = 0;
										}
										if($tax_percentage_booking_tot_seller != 0){
											$tax_percentage_amount_tot_seller = ($price_seller * ($tax_percentage_booking_tot_seller))/100;
										}
										$tax_percentage_amount_tot_seller = round($tax_percentage_amount_tot_seller,2); 
										$camptot_seller += $price_seller+$tax_percentage_amount_tot_seller;
									}else{
										
										$priceperselectedDates_seller = $getcampaigntot_seller->price;
										if(isset($getcampaigntot_seller->tax_percentage)){
											$tax_percentage_booking_tot_seller = $getcampaigntot_seller->tax_percentage;
										}else{
											$tax_percentage_booking_tot_seller = 0;
										}
										if($tax_percentage_booking_tot_seller != 0){
											$tax_percentage_amount_tot_seller = ($priceperselectedDates_seller * ($tax_percentage_booking_tot_seller))/100;
										}
										$tax_percentage_amount_tot_seller = round($tax_percentage_amount_tot_seller,2); 
										$camptot_seller += ($priceperselectedDates_seller*$getcampaigntot_seller->quantity)+($tax_percentage_amount_tot_seller*$getcampaigntot_seller->quantity);
									}
								}
							}
							
							if (isset($campaignproducts_seller) && count($campaignproducts_seller) > 0) {

								foreach ($campaignproducts_seller as $campaignproduct_seller) {
									$campaignproduct_seller->tax_percentage_amount_shortlist_seller = 0;
									$campaignproduct_seller->tax_percentage_amount = 0;
									$tax_percentage_booking_seller = 0;
									$tax_percentage_amount_seller = 0;
									$getproductDetails_seller =Product::where('id', '=', $campaignproduct_seller->product_id)->first();
									$diff_seller=date_diff(date_create($campaignproduct_seller->booked_from),date_create($campaignproduct_seller->booked_to));
									$daysCount_seller = $diff_seller->format("%a");
									$daysCountCPM_seller = $daysCount_seller + 1;
									if(isset($getproductDetails_seller->fix) && $getproductDetails_seller->fix=="Fixed"){
										$offerDetails_seller = MakeOffer::where([
											['campaign_id', '=', $campaignproduct_seller->campaign_id],
											['status', '=', 20],
										])->get();

										if(isset($offerDetails_seller) && count($offerDetails_seller)==1){
											//echo 'offer exists';exit; 
											foreach($offerDetails_seller as $offerDetails_seller){
												$offerprice_seller = $offerDetails_seller->price;
												$stripe_percent_seller=$getproductDetails_seller->stripe_percent;
												$price_seller = $campaignproduct_seller->price;
												$priceperday_seller = $price_seller;
												$priceperselectedDates_seller = $priceperday_seller;
												$newpricepercentage_seller = ($priceperselectedDates_seller/$camptot_seller) * 100;
												//$newofferprice_seller = ($offerprice_seller * ($newpricepercentage_seller))/100;
												if(isset($campaignproduct_seller->quantity) && $campaignproduct_seller->quantity != '' && $campaignproduct_seller->quantity != 0){
													$newofferprice_seller_wq = ($offerprice_seller * ($newpricepercentage_seller))/100;
													$newofferprice_seller = $newofferprice_seller_wq*$campaignproduct_seller->quantity;
												}else{
													$newofferprice_seller = ($offerprice_seller * ($newpricepercentage_seller))/100;
												}
												$offerpriceperselectedDates_seller = $newofferprice_seller;
												$newofferStripepercentamt_seller = ($newofferprice_seller * ($stripe_percent_seller))/100;
												$newprocessingfeeamt_seller = ((2.9) * $newofferStripepercentamt_seller)/100;
											}
										}else{
											$price_seller = $campaignproduct_seller->price;
											$stripe_percent_seller=$getproductDetails_seller->stripe_percent;
											 $priceperday_seller = $price_seller;
											 $priceperselectedDates_seller = $priceperday_seller;
											$offerprice_seller = $campaignproduct_seller->price;
											if(isset($campaignproduct_seller->quantity) && $campaignproduct_seller->quantity != '' && $campaignproduct_seller->quantity != 0){
												$newofferprice_seller = $offerprice_seller*$campaignproduct_seller->quantity;
											}else{
												$newofferprice_seller = $offerprice_seller ;
											}
											$offerpriceperselectedDates_seller = $newofferprice_seller;
											$newofferStripepercentamt_seller = ($newofferprice_seller * ($stripe_percent_seller))/100;
											$newprocessingfeeamt_seller = ((2.9) * $newofferStripepercentamt_seller)/100;
										}
										
										if(isset($campaignproduct_seller->tax_percentage)){
											$tax_percentage_booking_seller = $campaignproduct_seller->tax_percentage;
										}else{
											$tax_percentage_booking_seller = 0;
										}
										if($tax_percentage_booking_seller != 0){
											$campaignproduct_seller->tax_percentage_amount_shortlist_seller = ($offerpriceperselectedDates_seller * ($tax_percentage_booking_seller))/100;
											$tax_percentage_amount_seller = ($priceperselectedDates_seller * ($tax_percentage_booking_seller))/100;
										}
										$campaignproduct_seller->tax_percentage_amount = round($tax_percentage_amount_seller,2);
										$tax_percentage_amount_sum_seller += round($tax_percentage_amount_seller,2);
										$shortlistedsum_seller += ($priceperselectedDates_seller+$campaignproduct_seller->tax_percentage_amount_shortlist_seller)*$campaignproduct_seller->quantity;

										$offershortlistedsum_seller+= $offerpriceperselectedDates_seller+$campaignproduct_seller->tax_percentage_amount;
										$shortlistedsumcpm_seller += $priceperselectedDates_seller*$campaignproduct_seller->quantity;
										$offershortlistedsumcpm_seller+= $offerpriceperselectedDates_seller;
										$impressions_seller = $getproductDetails_seller->secondImpression;
										$impressionsperday_seller = (float)($impressions_seller/7);
										$impressionsperselectedDates_seller = $impressionsperday_seller * $daysCountCPM_seller;//exit;
										$impressionSum_seller += $impressionsperselectedDates_seller*$campaignproduct_seller->quantity;
										$newprocessingfeeamtSum_seller += $newprocessingfeeamt_seller;
									}else{
										$offerDetails_seller = MakeOffer::where([
											['campaign_id', '=', $campaignproduct_seller->campaign_id],
											['status', '=', 20],
										])->get();
										
										
										if(isset($offerDetails_seller) && count($offerDetails_seller)==1){
											//echo 'variable -offer'; exit;;
												foreach($offerDetails_seller as $offerDetails_seller){
													$offerprice_seller = $offerDetails_seller->price;
													$stripe_percent_seller=$getproductDetails_seller->stripe_percent;
													$price_seller = $getproductDetails_seller->default_price;
													///variable_offer///
													$priceperday_seller = $campaignproduct_seller->price;
													$priceperselectedDates_seller = $priceperday_seller;
													
													$newpricepercentage_seller = ($priceperselectedDates_seller/$camptot_seller) * 100;
													//$newofferprice_seller = ($offerprice_seller * ($newpricepercentage_seller))/100;//exit;
													if(isset($campaignproduct_seller->quantity) && $campaignproduct_seller->quantity != '' && $campaignproduct_seller->quantity != 0){
														$newofferprice_seller_wq = ($offerprice_seller * ($newpricepercentage_seller))/100;
														$newofferprice_seller = $newofferprice_seller_wq*$campaignproduct_seller->quantity;
													}else{
														$newofferprice_seller = ($offerprice_seller * ($newpricepercentage_seller))/100;
													}
													$offerpriceperselectedDates_seller = $newofferprice_seller;
													$newofferStripepercentamt_seller = ($newofferprice_seller * ($stripe_percent_seller))/100;
													$newprocessingfeeamt_seller = ((2.9) * $newofferStripepercentamt_seller)/100;
												}
												
										}else{
											$price_seller = $campaignproduct_seller->price;
											$stripe_percent_seller=$getproductDetails_seller->stripe_percent;
											$priceperday_seller = $price_seller;//exit;
											$priceperselectedDates_seller = $priceperday_seller;
											$offerprice_seller = $campaignproduct_seller->price;
											if(isset($campaignproduct_seller->quantity) && $campaignproduct_seller->quantity != '' && $campaignproduct_seller->quantity != 0){
												$newofferprice_seller = $offerprice_seller*$campaignproduct_seller->quantity;
											}else{
												$newofferprice_seller = $offerprice_seller;
											}
											$offerpriceperselectedDates_seller = $newofferprice_seller;
											$newofferStripepercentamt_seller = ($newofferprice_seller * ($stripe_percent_seller))/100;
											$newprocessingfeeamt_seller = ((2.9) * $newofferStripepercentamt_seller)/100;
										}
										if(isset($campaignproduct_seller->tax_percentage)){
											$tax_percentage_booking_seller = $campaignproduct_seller->tax_percentage;
										}else{
											$tax_percentage_booking_seller = 0;
										}
										if($tax_percentage_booking_seller != 0){
											$campaignproduct_seller->tax_percentage_amount_shortlist_seller = ($offerpriceperselectedDates_seller * ($tax_percentage_booking_seller))/100;
											$tax_percentage_amount_seller = ($priceperselectedDates_seller * ($tax_percentage_booking_seller))/100;
										}
										$campaignproduct_seller->tax_percentage_amount = round($tax_percentage_amount_seller,2);
										$tax_percentage_amount_sum_seller += round($tax_percentage_amount_seller,2);
										$shortlistedsum_seller += ($priceperselectedDates_seller+$campaignproduct_seller->tax_percentage_amount_shortlist_seller)*$campaignproduct_seller->quantity;

										$offershortlistedsum_seller+= $offerpriceperselectedDates_seller+$campaignproduct_seller->tax_percentage_amount;
										$shortlistedsumcpm_seller += $priceperselectedDates_seller*$campaignproduct_seller->quantity;
										$offershortlistedsumcpm_seller+= $offerpriceperselectedDates_seller;
										$impressions_seller = $getproductDetails_seller->secondImpression;
										$impressionsperday_seller = (float)($impressions_seller/7);
										$impressionsperselectedDates_seller = $impressionsperday_seller * $daysCountCPM_seller;//exit;
										$impressionSum_seller += $impressionsperselectedDates_seller *$campaignproduct_seller->quantity;
										$newprocessingfeeamtSum_seller += $newprocessingfeeamt_seller;
									}
									array_push($products_arr_seller_act, array_merge(Product::where('id', '=', $campaignproduct_seller->product_id)->first()->toArray(), $campaignproduct_seller->toArray()));
								}
								$actbudg_seller = $products_arr_seller_act;
								$res_seller = array_sum(array_map(function($item) { 
									if(isset($item['quantity']) && $item['quantity'] != '' && $item['quantity'] != 0){
										return ($item['price']+$item['tax_percentage_amount'])*$item['quantity']; 
									}else{
										return $item['price']+$item['tax_percentage_amount']; 
									}
								}, $actbudg_seller));
							}
							
							
							$impressionSum4_seller = round($impressionSum_seller, 2);
							if($impressionSum4_seller >0){
								$cpmval_seller = ($shortlistedsumcpm_seller/$impressionSum4_seller) * 1000;
								$offercpmval_seller = ($offershortlistedsumcpm_seller/$impressionSum4_seller) * 1000;
							}else{
								$cpmval_seller = 0;
								$offercpmval_seller = 0;
							}
							 
							if($offershortlistedsum_seller != 0){
								$campaign_cpmval_seller = $offercpmval_seller;
								$total_price_seller = $offershortlistedsum_seller;
							}else{
								 $campaign_cpmval_seller = $cpmval_seller;
								 $total_price_seller = $res_seller;
							}
							
							$campaign_impressionSum_seller = $impressionSum4_seller;
							
							$campaign_shortlistedsum_seller = $shortlistedsum_seller;
							
							$paid_percentage_seller = $campaign->total_paid*100/$total_price_seller;
							$processing_fee_sum_seller = $newprocessingfeeamtSum_seller;
							
							$campaign_report_seller = [
								'campaign' => $campaign,
								'areas_covered' => $areas,
								'format_types' => $formats,
								'mediums_covered' => $products_in_campaign->count(),
								'audience_reach' => $audience_reach,
								'repeated_audience' => $repeated_audience,
								'products' => $products_in_campaign,
								'campaign_shortlistedsum'=>$campaign_shortlistedsum_seller,
								'campaign_cpmval'=>$campaign_cpmval_seller,
								'campaign_impressionSum'=>$campaign_impressionSum_seller,
								'total_price'=>$total_price_seller,
								'products_arr'=>$products_arr_seller_final,
								'client_details_billing'=>$client_details,
								'paid_percentage'=>$paid_percentage_seller,
								'processing_fee_sum'=>$processing_fee_sum_seller,
								'user_mongo_report'=>$user_mongo,
								'tax_percentage_amount_sum'=>$tax_percentage_amount_sum_seller,
							];
							
							$pdf = PDF::loadView('pdf.IO_pdf', $campaign_report_seller);
							
							$mail_tmpl_params = [
							  'sender_email' => config('app.bbi_email'), 
							  'receiver_name' => '',
							  'mail_message' => "Your campaign '" . $campaign_obj->name . "' has been received. Attached below is your Insertion Order with details of the order you placed.<br>
							  Visit your Advertising Marketplace seller account for additional information. If you have any questions please contact me directly at (949).226.1279.<br> <br>
							  Thank you for using Advertising Marketplace."
							];
							$mail_data = [
							  'bcc' => $final_email,
							  'email_to1' => 'admin@advertisingmarketplace.com',
							  'recipient_name1' => 'Richard',
							  'recipient_name' => '',
							  'pdf_file_name' => "IO-". $campaign_obj->cid . "-" . date('m-d-Y') . ".pdf",
							  'pdf' => $pdf
							];
							if($final_email !=''){
								Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data){
								  $message->to($mail_data['bcc'], $mail_data['recipient_name'])->subject('User campaign confirmed! - Advertising Marketplace');
								  $message->cc($mail_data['email_to1'], $mail_data['recipient_name1'])->subject('User Campaign Confirmed! - Advertising Marketplace');
								  $message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
								});
							}
						}
					}
					
					//$owner_sa_emails = UserMongo::whereIn('id', $owner_sa_ids)->pluck('email');

                    //$campaign_user = UserMongo::where('id', '=', $campaign_obj->created_by)->first();
                    
                    //notification Email Owner Start
                    
                        event(new CampaignLaunchEvent([
                      'type' => Notification::$NOTIFICATION_TYPE['campaign-launched'],
                        'from_id' => null,
                        'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['owner'],
                        'to_id' => null,
                        'to_client' => $campaign_product_owner_ids,
                        //'desc' => "Campaign launched",
                        'desc' => "Campaign confirmed",
                        //'message' => "Your Campaign has been launched!",
                        'message' => "A campaign with your products in it has been confirmed.",
                        'data' => ["campaign_id" => $campaign_obj->id]
                    ]));
                    
                    foreach ($campaign_product_owner_ids as $key => $val) {
                    $notification_obj = new Notification;
                        $notification_obj->id = uniqid();
                        $notification_obj->type = "campaign";
                        $notification_obj->from_id = null;
                        $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['owner'];
                        $notification_obj->to_id = $val;
                        $notification_obj->to_client = $val;
                        //$notification_obj->desc = "Campaign launched";
                        $notification_obj->desc = "Campaign confirmed";
                        $notification_obj->message = "A campaign with your products in it has been confirmed!";
                        $notification_obj->campaign_id = $campaign_obj->id;
                        $notification_obj->status = 0;
                        $notification_obj->save();

                    }
                    
                    //notification owner end
					
					//Admin mail start
					
					$pdf = PDF::loadView('pdf.IO_pdf',$campaign_report);
                    
                    //echo '<pre>pdf';print_r($pdf);exit; 
                    
                    $mail_tmpl_params = [
                      'sender_email' => $user['email'], 
                      //'receiver_name' => $bbi_sa->first_name . ' ' . $bbi_sa->last_name,
                      //'receiver_name' => $user_mongo->first_name,
                      'receiver_name' => '',
                      'mail_message' => "You have confimed campaign '" . $campaign_obj->name . "'."
                    ];
                    $mail_data = [
                      //'email_to' => $bbi_sa->email,
                      //'email_to' => $user_mongo->email,
                      //'email_to' => 'sandhyarani.manelli@peopletech.com',
                      //'email_to1' => 'admin@advertisingmarketplace.com',
                      'email_to' => 'shiva.karunakar@peopletech.com',
                      //'recipient_name1' => 'Richard',
                      //'recipient_name' => $user_mongo->first_name,
                      'recipient_name' => '',
                      //'pdf_file_name' => "Insertion Order-". date('m-d-Y') . ".pdf",
					  //'pdf_file_name' => "Insertion Order CDHP covid vac 10 27 21" . ".pdf",
					  'pdf_file_name' => "IO-". $campaign_obj->cid . "-" . date('m-d-Y') . ".pdf",
                      'pdf' => $pdf
                    ];
                    Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data){
                     // $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Super admin set up for company - Billboards India');
                      $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('User Campaign Confirmed! - Advertising Marketplace');
                      //$message->cc($mail_data['email_to1'], $mail_data['recipient_name1'])->subject('User Campaign Confirmed! - Advertising Marketplace');
                      $message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
                    });
					
					//Admin mail end

                    //return response()->json(["status" => "1", "message" => "Campaign launched successfully."]);
                    return response()->json(["status" => "1", "message" => "Campaign confirmed successfully."]);
                } else {
                    return response()->json(["status" => 0, "message" => "There was a technical error while launching your campaign."]);
                }
            } else {
                return response()->json(['status' => 0, 'message' => 'You are not authorized to launch this campaign.']);
            }
        } else {
            // logged in user is owner
            // check if the campaign belongs to owner. if yes, then launch campaign, otherwise, do nothing.
            if ($campaign_obj->type == Campaign::$CAMPAIGN_USER_TYPE['owner'] && $campaign_obj->client_mongo_id == $user_mongo['client_mongo_id']) {
                if (count($unavailable_products) > 0) {
                    return response()->json(['status' => 0, 'message' => 'Some products from this campaign are already being used in other campaigns.', 'product_ids' => $unavailable_products]);
                }
                $campaign_obj->status = Campaign::$CAMPAIGN_STATUS['scheduled'];
                if ($campaign_obj->save()) {
                    $campaign_products = ProductBooking::where('campaign_id', '=', $campaign_obj->id)->get();
                    foreach ($campaign_products as $campaign_product) {
                        $campaign_product->product_status = ProductBooking::$PRODUCT_STATUS['scheduled'];
                        $campaign_product->save();
                    }
                    //return response()->json(["status" => "1", "message" => "Campaign launched successfully."]);
                    return response()->json(["status" => "1", "message" => "Campaign confirmed successfully."]);
                } else {
                    return response()->json(["status" => 0, "message" => "There was a technical error while launching your campaign."]);
                } 
            } else {
                return response()->json(['status' => 0, 'message' => 'You are not authorized to launch this campaign.']);
            }
        }
    } 

    
  
    public function shareCampaign() {
        $this->validate($this->request, [
            'campaign_id' => 'required',
            'email' => 'required',
            'receiver_name' => 'required',
                //'campaign_type' => 'required'
                ], [
            'campaign_id.required' => 'Campaign id is required',
            'email.required' => 'Email is required',
            'receiver_name.required' => 'Receiver name is required',
                //'campaign_type.required' => 'Campaign type is required'
                ]
        );
		
			$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        
        $shortlisted_products_arr = [];
        $shortlistedsum = 0;
		$shortlistedsumcpm = 0;
        $offershortlistedsum = 0;
		$offershortlistedsumcpm = 0;
        $cpmsum = 0;
        $offercpmsum = 0;
        $impressionSum = 0;
        $impressionSum4 = 0;
        $newofferStripepercentamtSum = 0;
        $newprocessingfeeamtSum = 0;
		$tax_percentage_booking = 0;
		$tax_percentage_amount = 0;
        
        try {
            $user = JWTAuth::parseToken()->getPayload()['user'];
            //echo '<pre>user'; print_r($user);exit;
            $campaign = Campaign::where('id', '=', $this->input['campaign_id'])->first();
            $campaign_id=$this->input['campaign_id'];
            //if($campaign->status < 1000){
			if($campaign->status <= 2000){
            $product_ids_in_campaign = ProductBooking::where('campaign_id', '=', $campaign_id)->pluck('product_id');
            $products_in_campaign = Product::whereIn('id', $product_ids_in_campaign)->get();
            $formats = $products_in_campaign->unique('type')->count();
            $areas = $products_in_campaign->unique('area')->count();
            $audience_reach = $products_in_campaign->each(function($v, $k) {
                        $v->impressions = intval(str_replace(",", "", $v->impressions));
                    })->sum('impressions');
            $repeated_audience = $audience_reach * 30 / 100;
                 $campaign_products = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
                 
        $products_arr = [];
        // if (isset($campaign_products) && count($campaign_products) > 0) {
        //     foreach ($campaign_products as $campaign_product) {
        //      $product =Product::where('id', '=', $campaign_product->product_id)->first();
        //         array_push($products_arr, array_merge(Product::where('id', '=', $campaign_product->product_id)->first()->toArray(), $campaign_product->toArray()));
        //     } 
          
        // }

        $getcampaigntot = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
        $campaign->total_paid = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('amount');
        $camptot = 0;
		$offer_applied = 0;
		$tax_percentage_booking_tot = 0;
		$tax_percentage_amount_tot = 0;
        if (isset($getcampaigntot) && count($getcampaigntot) > 0) {
            foreach ($getcampaigntot as $getcampaigntot) {
				$tax_percentage_booking_tot = 0;
				$tax_percentage_amount_tot = 0;
                $getcampaigntotproduct =Product::where('id', '=', $getcampaigntot->product_id)->first();
                $bookedfrom[] = strtotime($getcampaigntot->booked_from);
                $bookedto[] = strtotime($getcampaigntot->booked_to);

                $getproductDetails =Product::where('id', '=', $getcampaigntot->product_id)->first();
                $diff=date_diff(date_create($getcampaigntot->booked_from),date_create($getcampaigntot->booked_to));
                $daysCount = $diff->format("%a");
                $daysCountCPM = $daysCount + 1;
				$perdayprice = $getproductDetails->default_price/28;

                if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
                    $price = $getcampaigntot->price*$getcampaigntot->quantity;
					if($campaign->total_paid != 0){
						if(isset($getcampaigntot->tax_percentage)){
							$tax_percentage_booking_tot = $getcampaigntot->tax_percentage;
						}else{
							$tax_percentage_booking_tot = 0;
						}
					}else{
						$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
					}
					if($tax_percentage_booking_tot != 0){
						$tax_percentage_amount_tot = ($price * ($tax_percentage_booking_tot))/100;
					}
					$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
					$camptot += $price+$tax_percentage_amount_tot;
                }else{
                    /*$price = $getproductDetails->default_price;
                    $priceperday = $price/28;
                    $priceperselectedDates = $priceperday * $daysCountCPM;
                    $camptot += $priceperselectedDates;*/
					
					$priceperselectedDates = $getcampaigntot->price;
					
					if($campaign->total_paid != 0){
						if(isset($getcampaigntot->tax_percentage)){
							$tax_percentage_booking_tot = $getcampaigntot->tax_percentage;
						}else{
							$tax_percentage_booking_tot = 0;
						}
					}else{
						$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
					}
					if($tax_percentage_booking_tot != 0){
						$tax_percentage_amount_tot = ($priceperselectedDates * ($tax_percentage_booking_tot))/100;
					}
					$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
					$camptot += ($priceperselectedDates*$getcampaigntot->quantity)+($tax_percentage_amount_tot*$getcampaigntot->quantity);
                }
            }
        } 

		if($user_mongo['user_type'] == 'owner'){	
				$campaignproducts = ProductBooking::where([
                    ['campaign_id', '=', $campaign_id],
                    ['product_owner', '=', $user_mongo['client_mongo_id']]
                ])->orderBy('booked_from','ASC')->orderBy('booked_to','ASC')->get();
			}else{
				$campaignproducts = ProductBooking::where('campaign_id', '=', $campaign_id)->orderBy('booked_from','ASC')->orderBy('booked_to','ASC')->get();
			}
        
        if (isset($campaignproducts) && count($campaignproducts) > 0) {
			
            
            foreach ($campaignproducts as $campaignproduct) {
				$campaignproduct->tax_percentage_amount_shortlist = 0;
				$campaignproduct->tax_percentage_amount = 0;
				$tax_percentage_booking = 0;
				$tax_percentage_amount = 0;
                //echo "<pre>campaignproduct"; print_r($campaignproducts);exit;
				$getproductDetails =Product::where('id', '=', $campaignproduct->product_id)->first();
                $product = Product::where('id', '=', $campaignproduct->product_id)->first();
                $area_time_zone_value = Area::select("area_time_zone","area_time_zone_type")->where('id', '=', $product->area)->first();
				$campaignproduct->area_time_zone_type = $area_time_zone_value['area_time_zone_type'];
                /*CPM Calculation*/
                $diff=date_diff(date_create($campaignproduct->booked_from),date_create($campaignproduct->booked_to));
                //$diff=date_diff(($booked_from_date),($booked_to_date));
                $daysCount = $diff->format("%a");//exit;
                $daysCountCPM = $daysCount + 1;
				$perdayprice = $getproductDetails->default_price/28;
                //$daysCoun1tCPM = $daysCount;
                //echo "<pre>diff";print_r($diff);exit;
                //echo "<pre>product";print_r($product);exit;
                //echo $daysCoun1tCPM;exit;
                
                //$price = $campaignproduct->price;
                if(isset($product->fix) && $product->fix=="Fixed"){
                    /*$price = $product->default_price;
                    $priceperday = $price;//exit;
                    $priceperselectedDates = $priceperday;
                    $shortlistedsum+= $priceperselectedDates;
                    $campaignproduct->price = $priceperselectedDates;
                    $cpmsum+= $product->cpm;
                    $impressions = $product->secondImpression;
                    $impressionsperday = (int)($impressions);
                    $impressionsperselectedDates = $impressionsperday;//exit;
                    
                    if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                        $impressionsperselectedDates = $impressionsperselectedDates;
                    }else{
                        $impressionsperselectedDates = 1;
                    }
                    $impressionSum+= $impressionsperselectedDates;
                    $campaignproduct->secondImpression = round($impressionsperselectedDates, 2);
                    $cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                    $campaignproduct->cpmperselectedDates = $cpmcal;
                    $campaignproduct->cpm = $cpmcal;
                    $campaignproduct->impressionsperselectedDates = $impressionsperselectedDates;
                    $campaignproduct->priceperselectedDates = $priceperselectedDates;*/

                    $offerDetails = MakeOffer::where([
                        ['campaign_id', '=', $campaignproduct->campaign_id],
                        ['status', '=', 20],
                    ])->get();

                    if(isset($offerDetails) && count($offerDetails)==1){
                        //echo 'offer exists';exit;
                        foreach($offerDetails as $offerDetails){
                             $offerprice = $offerDetails->price;
                             $stripe_percent=$getproductDetails->stripe_percent;
                             
                        //$price = $getproductDetails->default_price;
                        $price = $campaignproduct->price;
                        
                        //$price = $campaign_product->price;
                        $priceperday = $price;
                        $priceperselectedDates = $priceperday;
                        $newpricepercentage = ($priceperselectedDates/$camptot) * 100;
        
                        //$newofferprice = ($offerprice * ($newpricepercentage))/100;
						if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
							$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
							$newofferprice = $newofferprice_wq*$campaignproduct->quantity;
						}else{
							$newofferprice = ($offerprice * ($newpricepercentage))/100;
						}
                        //$offerpriceperday = $newofferprice/28;//exit;
                        //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                        $offerpriceperselectedDates = $newofferprice;
                        $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
                        $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                        $campaignproduct->stripe_percent = $stripe_percent;
                           }
								$offer_applied = 1;
                        }else{
                             //$offerprice = $getproductDetails->default_price;
                             //echo 'no offer exists';exit;
                             $offerprice = $campaignproduct->price;
                             //$offerprice = $getproductDetails->default_price;
                             $stripe_percent=$getproductDetails->stripe_percent;
                             //$price = $getproductDetails->default_price;
                             $price = $campaignproduct->price;
                             //$price = $campaign_product->price;
                             $priceperday = $price;//exit;
                             $priceperselectedDates = $priceperday;
                             $newpricepercentage = ($priceperselectedDates/$camptot) * 100;
            
							if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
                                $newofferprice = $offerprice*$campaignproduct->quantity;
							}else{
								$newofferprice = $offerprice ;
							}
                        //$offerpriceperday = $newofferprice/28;//exit;
                        //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                            $offerpriceperselectedDates = $newofferprice;
                            $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
                            $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                            $campaignproduct->stripe_percent = $stripe_percent;
                        }
                        
                        if($campaign->total_paid != 0){
							if(isset($campaignproduct->tax_percentage)){
								$tax_percentage_booking = $campaignproduct->tax_percentage;
							}else{
								$tax_percentage_booking = 0;
							}
						}else{
							$tax_percentage_booking = $getproductDetails->tax_percentage;
						}
						if($tax_percentage_booking != 0){
							$campaignproduct->tax_percentage_amount_shortlist = ($priceperselectedDates * ($tax_percentage_booking))/100;
							$tax_percentage_amount = ($offerpriceperselectedDates * ($tax_percentage_booking))/100;
						}
						$campaignproduct->tax_percentage_amount = round($tax_percentage_amount,2);
                                            
                        $shortlistedsum+= ($priceperselectedDates+$campaignproduct->tax_percentage_amount_shortlist)*$campaignproduct->quantity;
						$shortlistedsumcpm+= $priceperselectedDates*$campaignproduct->quantity;
                        $campaignproduct->price = $priceperselectedDates;
        
						$offershortlistedsum+= $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;
						$campaignproduct->offerprice = $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;
						
						$offershortlistedsumcpm += $offerpriceperselectedDates;
                        $cpmsum+= $getproductDetails->cpm;
                        $impressions = $getproductDetails->secondImpression;
                        $impressionsperday = (float)($impressions/7);
                        $impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
                        
                        if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                            $impressionsperselectedDates = $impressionsperselectedDates;
                        }else{
                            $impressionsperselectedDates = 1;
                        }
                        //$impressionSum+= $product_details->secondImpression; 
                        $impressionSum+= $impressionsperselectedDates*$campaignproduct->quantity;
                        $campaignproduct->secondImpression = round($impressionsperselectedDates, 2);
                        //$cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                        //$offercpmcal = ($offershortlistedsum/$impressionSum) * 1000;
                        $cpmcal = ($priceperselectedDates/$impressionsperselectedDates) * 1000;
                        $offercpmcal = ($offerpriceperselectedDates/$impressionsperselectedDates) * 1000;
                        $campaignproduct->cpmperselectedDates = $cpmcal;
                        $campaignproduct->offercpmperselectedDates = $offercpmcal;
                        $campaignproduct->cpm = $cpmcal;
                        $campaignproduct->offercpm = $offercpmcal;
                        $campaignproduct->impressionsperselectedDates = $impressionsperselectedDates;
                        $campaignproduct->priceperselectedDates = $priceperselectedDates;
                        $campaignproduct->offerpriceperselectedDates = $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;
        
                        $campaignproduct->new_stripe_percent_amount = $newofferStripepercentamt;
                        $campaignproduct->newprocessingfeeamt = $newprocessingfeeamt;
        
                        $newofferStripepercentamtSum += $newofferStripepercentamt;
                        $newprocessingfeeamtSum += $newprocessingfeeamt;
                        //echo "<pre>";print_r($campaignproduct);exit;

                }else{
                    /*$price = $product->default_price;
                    $priceperday = $price/28;//exit;
                    $priceperselectedDates = $priceperday * $daysCountCPM;
                    $shortlistedsum+= $priceperselectedDates;
                    $campaignproduct->price = $priceperselectedDates;
                    $cpmsum+= $product->cpm;
                    $impressions = $product->secondImpression;
                    $impressionsperday = (int)($impressions/7);
                    $impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
                    
                    if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                        $impressionsperselectedDates = $impressionsperselectedDates;
                    }else{
                        $impressionsperselectedDates = 1;
                    }
                    $impressionSum+= $impressionsperselectedDates;
                    $campaignproduct->secondImpression = round($impressionsperselectedDates, 2);
                    $cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                    $campaignproduct->cpmperselectedDates = $cpmcal;
                    $campaignproduct->cpm = $cpmcal;
                    $campaignproduct->impressionsperselectedDates = $impressionsperselectedDates;
                    $campaignproduct->priceperselectedDates = $priceperselectedDates;*/

                    $offerDetails = MakeOffer::where([
                        ['campaign_id', '=', $campaignproduct->campaign_id],
                        ['status', '=', 20],
                    ])->get();
                    
                    
                    if(isset($offerDetails) && count($offerDetails)==1){
                        //echo 'variable -offer'; exit;;
                            foreach($offerDetails as $offerDetails){
                                    $offerprice = $offerDetails->price;
                                    $stripe_percent=$getproductDetails->stripe_percent;
                                    
                            $price = $getproductDetails->default_price;
                            
                            //$price = $campaign_product->price;
                            //$priceperday = $price/28;//exit;
                            //echo '---camptot--'.$camptot;
                            //$priceperselectedDates = $priceperday * $daysCountCPM;
							
							
							///variable_offer///
								$priceperday = $campaignproduct->price;
								$priceperselectedDates = $priceperday;
							
							
							
							
                            $newpricepercentage = ($priceperselectedDates/$camptot) * 100;

                            //$newofferprice = ($offerprice * ($newpricepercentage))/100;//exit;
							if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
								$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
								$newofferprice = $newofferprice_wq*$campaignproduct->quantity;
							}else{
								$newofferprice = ($offerprice * ($newpricepercentage))/100;
							}
                            //$offerpriceperday = $newofferprice/28;//exit;
                            //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                            $offerpriceperselectedDates = $newofferprice;
                            $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
                            $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                            $campaignproduct->stripe_percent = $stripe_percent;
                                }
								$offer_applied = 1;
                            }else{
                               //echo 'variable -no-offer'; exit;;
                                    //$offerprice = $getproductDetails->default_price;
                            $offerprice = $campaignproduct->price;
                            $stripe_percent=$getproductDetails->stripe_percent;
                            $price = $campaignproduct->price;
                            //$price = $campaign_product->price;
                            $priceperday = $price;//exit;
                            $priceperselectedDates = $priceperday;
                            $newpricepercentage = ($priceperselectedDates/$camptot) * 100;

							if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
								$newofferprice = $offerprice*$campaignproduct->quantity;
							}else{
								$newofferprice = $offerprice;
							}
							
                            //$offerpriceperday = $newofferprice/28;//exit;
                            //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                            $offerpriceperselectedDates = $newofferprice;
                            $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
                            $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                            $campaignproduct->stripe_percent = $stripe_percent;
                            }
                            
                            //if($daysCountCPM <= $product->minimumdays){
                            //    $priceperselectedDates = $priceperday * $product->minimumdays;
                            //}
                            
                            if($campaign->total_paid != 0){
								if(isset($campaignproduct->tax_percentage)){
									$tax_percentage_booking = $campaignproduct->tax_percentage;
								}else{
									$tax_percentage_booking = 0;
								}
							}else{
								$tax_percentage_booking = $getproductDetails->tax_percentage;
							}
							if($tax_percentage_booking != 0){
								$campaignproduct->tax_percentage_amount_shortlist = ($priceperselectedDates * ($tax_percentage_booking))/100;
								$tax_percentage_amount = ($offerpriceperselectedDates * ($tax_percentage_booking))/100;
							}
                            $campaignproduct->tax_percentage_amount = round($tax_percentage_amount,2);
							
							$shortlistedsum+= ($priceperselectedDates+$campaignproduct->tax_percentage_amount_shortlist)*$campaignproduct->quantity;
                            $shortlistedsumcpm += $priceperselectedDates*$campaignproduct->quantity;
                            $campaignproduct->price = $priceperselectedDates;

							$offershortlistedsum+= $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;
                            $campaignproduct->offerprice = $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;
								
							$offershortlistedsumcpm += $offerpriceperselectedDates;
                            $cpmsum+= $getproductDetails->cpm;
                            $impressions = $getproductDetails->secondImpression;
                            $impressionsperday = (float)($impressions/7);
                            $impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
                            
                            if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                                $impressionsperselectedDates = $impressionsperselectedDates;
                            }else{
                                $impressionsperselectedDates = 1;
                            }
                            //$impressionSum+= $product_details->secondImpression; 
                            $impressionSum+= $impressionsperselectedDates*$campaignproduct->quantity;
                            $campaignproduct->secondImpression = round($impressionsperselectedDates, 2);
                            //$cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                            //$offercpmcal = ($offershortlistedsum/$impressionSum) * 1000;
                            $cpmcal = ($priceperselectedDates/$impressionsperselectedDates) * 1000;
                            $offercpmcal = ($offerpriceperselectedDates/$impressionsperselectedDates) * 1000;
                            $campaignproduct->cpmperselectedDates = $cpmcal;
                            $campaignproduct->offercpmperselectedDates = $offercpmcal;
                            $campaignproduct->cpm = $cpmcal;
                            $campaignproduct->offercpm = $offercpmcal;
                            $campaignproduct->impressionsperselectedDates = $impressionsperselectedDates;
                            $campaignproduct->priceperselectedDates = $priceperselectedDates;
                            $campaignproduct->offerpriceperselectedDates = $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;

                            $campaignproduct->new_stripe_percent_amount = $newofferStripepercentamt;
                            $campaignproduct->newprocessingfeeamt = $newprocessingfeeamt;

                            $newofferStripepercentamtSum += $newofferStripepercentamt;
                            $newprocessingfeeamtSum += $newprocessingfeeamt;
                            //echo "<pre>";print_r($campaignproduct);exit;

                }
                array_push($products_arr, array_merge(Product::where('id', '=', $campaignproduct->product_id)->first()->toArray(), $campaignproduct->toArray()));
            }
        }

        $campaign->products = $products_arr;
        $campaign->actbudg = $products_arr;

        $act_budget = ProductBooking::raw(function($collection) use ($campaign_id) {
            return $collection->aggregate(
                            [
                                [
                                    '$match' =>
                                    [
                                        "campaign_id" => $campaign_id
                                    ]
                                ],
                                [
                                    '$group' =>
                                    [
                                        '_id' => '$campaign_id',
                                        'total_price' => [
                                            '$sum' => '$admin_price'
                                        ]
                                    ]
                                ]
                            ]
            );
        });


        $res = array_sum(array_map(function($item) { 
            return $item['price']; 
        }, $campaign->actbudg));
        //echo "<pre>act_budget";print_r($res);exit;
        $campaign->act_budget = $res;

        $campaign->totalamount = $campaign->act_budget;
        $campaign->offer_applied_res = $offer_applied;
                 
        $campaign->refunded_amount = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('refunded_amount');
        
        $campaign->bal_amount_available_with_amp = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('bal_amount_available_with_amp');
        
		$campaign->gross_fee_price = 0;
		if(isset($campaign->gross_fee_percentage) && $campaign->gross_fee_percentage > 0){
			$campaign->gross_fee_price = ($offershortlistedsum*$campaign->gross_fee_percentage)/100;
		}else{
			$campaign->gross_fee_price = 0;
		}
        $impressionSum4 = round($impressionSum, 2); /* As discussed with Richard on July 14) */
         if($impressionSum4>0){
            $cpmval = (($shortlistedsumcpm+$campaign->gross_fee_price)/$impressionSum4) * 1000;
			$offercpmval = (($offershortlistedsumcpm+$campaign->gross_fee_price)/$impressionSum4) * 1000;
         }else{
             $cpmval = 0;
			$offercpmval = 0;
         }
		 
 
         $campaign_shortlistedsum = $shortlistedsum;
		 if($offershortlistedsum != 0){
			$campaign_cpmval = $offercpmval;
		 }else{
			 $campaign_cpmval = $cpmval;
		 }
         $campaign_impressionSum = $impressionSum4;

			$total_price = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('owner_price');
             if($total_price == 0){
             $total_price = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('price');
             }
			 
			 
        
            $res = array_sum(array_map(function($item) { 
				if(isset($item['quantity']) && $item['quantity'] != '' && $item['quantity'] != 0){
					return ($item['price']+$item['tax_percentage_amount'])*$item['quantity']; 
				}else{
					return $item['price']+$item['tax_percentage_amount']; 
				}
            }, $campaign->actbudg));
            $total_price = $res;
			if($offershortlistedsum != 0){
				$total_price = $offershortlistedsum+$campaign->gross_fee_price;
			}else{
				$total_price = $res+$campaign->gross_fee_price;
			}
			
            $campaign_report = [
                'campaign' => $campaign,
                'areas_covered' => $areas,
                'format_types' => $formats,
                'mediums_covered' => $products_in_campaign->count(),
                'audience_reach' => $audience_reach,
                'repeated_audience' => $repeated_audience,
                'products' => $products_in_campaign,
                'total_price'=>$total_price,
                'products_arr'=>$products_arr,
                'campaign_shortlistedsum'=>$campaign_shortlistedsum,
                'campaign_cpmval'=>$campaign_cpmval,
                'campaign_impressionSum'=>$campaign_impressionSum,
            ];
            //echo '<pre>total_price'; print_r($products_arr);exit;
            //echo '<pre>campaign'; print_r($campaign);//exit;
            //echo '<pre>campaign_report'; print_r($campaign_report);exit;
            // return view('pdf.campaign_details_pdf', $campaign_report); exit;
            $pdf = PDF::loadView('pdf.campaign_details_pdf', $campaign_report);
            //echo '<pre>pdf'; print_r($pdf);exit;
            // $pdf->save('uploads/campaign' . uniqid() . '.pdf'); die();
            }
            else{
                 $packages_in_campaign = CampaignProduct::where('campaign_id', '=', $campaign_id)->get();
            foreach ($packages_in_campaign as $pkg) {
                $package_tplt = MetroPackage::where('id', '=', $pkg->package_id)->first();
                $pkg->format = $package_tplt->format;
                $pkg->max_trains = $package_tplt->max_trains;
                $pkg->max_slots = $package_tplt->max_slots;
                $pkg->months = $package_tplt->months;
                $pkg->days = $package_tplt->days;
            }
            $campaign_report = [
                'campaign' => $campaign
                // 'format_types' => $formats,
                //'packages' => $packages_in_campaign
            ];
            //$pdf = PDF::loadView('pdf.metro_campaign_details_pdf', $campaign_report);
            $pdf = PDF::loadView('pdf.RFP_campaign_pdf', $campaign_report);
            }
           /* $product_ids_in_campaign = ProductBooking::where('campaign_id', '=', $this->input['campaign_id'])->pluck('product_id');
            $products_in_campaign = Product::whereIn('id', $product_ids_in_campaign)->get();
            $formats = $products_in_campaign->unique('type')->count();
            $areas = $products_in_campaign->unique('area')->count();
            $audience_reach = $products_in_campaign->each(function($v, $k) {
                        $v->impressions = intval(str_replace(",", "", $v->impressions));
                    })->sum('impressions');
            $repeated_audience = $audience_reach * 30 / 100;
            $campaign_report = [
                'campaign' => $campaign,
                'areas_covered' => $areas,
                'format_types' => $formats,
                'mediums_covered' => $products_in_campaign->count(),
                'audience_reach' => $audience_reach,
                'repeated_audience' => $repeated_audience,
                'products' => $products_in_campaign
            ];
            // return view('pdf.campaign_details_pdf', $campaign_report); exit; 
            $pdf = PDF::loadView('pdf.campaign_details_pdf', $campaign_report);
            // $pdf->save('uploads/campaign' . uniqid() . '.pdf'); die();*/
            $mail_tmpl_params = [
                'sender_email' => $user['email'],
                'receiver_name' => $this->input['receiver_name']
            ];
            $pdf_file_name = str_replace(' ', '-', preg_replace('/\s+/', ' ',$campaign['name']));
            $mail_data = [
                'email_to' => $this->input['email'],
                'recipient_name' => $this->input['receiver_name'],
                'pdf_file_name' => $pdf_file_name. ".pdf",
                'pdf' => $pdf
            ];
            Mail::send('mail.campaign_details', $mail_tmpl_params, function($message) use ($mail_data) {
                $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('A campaign has been shared to you!');
                $message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
            });
            if (!Mail::failures()) {
                return response()->json(['status' => 1, 'message' => "Your campaign has been shared successfully."]);
            } else {
                return response()->json(['status' => 0, 'message' => "There was an error sending the email. Please try again."]);
            }
        } catch (Exception $ex) {
            return response()->json(['status' => 0, 'message' => "There was an error generating the campaign report."]);
        }
    }
    
    public function shareCampaigndownloadQuote() {
        $this->validate($this->request, [
            'campaign_id' => 'required',
            'email' => 'required',
            //'receiver_name' => 'required',
            'receiver_name' => '',
                //'campaign_type' => 'required'
                ], [
            'campaign_id.required' => 'Campaign id is required',
            'email.required' => 'Email is required',
            //'receiver_name.required' => 'Receiver name is required',
                //'campaign_type.required' => 'Campaign type is required'
                ]
        );
        $shortlisted_products_arr = [];
        $shortlistedsum = 0;
		$shortlistedsumcpm = 0;
        $offershortlistedsum = 0;
		$offershortlistedsumcpm = 0;
        $cpmsum = 0;
        $offercpmsum = 0;
        $impressionSum = 0;
        $impressionSum4 = 0;
        $newofferStripepercentamtSum = 0;
        $newprocessingfeeamtSum = 0;
		$tax_percentage_booking = 0;
		$tax_percentage_amount = 0;
		
        try {
            $user = JWTAuth::parseToken()->getPayload()['user'];
            $campaign = Campaign::where('id', '=', $this->input['campaign_id'])->first();
            $campaign_id=$this->input['campaign_id'];
            //if($campaign->status < 1000){
			if($campaign->status <= 2000){
            $product_ids_in_campaign = ProductBooking::where('campaign_id', '=', $campaign_id)->pluck('product_id');
            $products_in_campaign = Product::whereIn('id', $product_ids_in_campaign)->get();
            $formats = $products_in_campaign->unique('type')->count();
            $areas = $products_in_campaign->unique('area')->count();
            $audience_reach = $products_in_campaign->each(function($v, $k) {
                        $v->impressions = intval(str_replace(",", "", $v->impressions));
                    })->sum('impressions');
            $repeated_audience = $audience_reach * 30 / 100;
                 $campaign_products = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
        $products_arr = [];
        /*if (isset($campaign_products) && count($campaign_products) > 0) {
            foreach ($campaign_products as $campaign_product) {
                $product =Product::where('id', '=', $campaign_product->product_id)->first();
                array_push($products_arr, array_merge(Product::where('id', '=', $campaign_product->product_id)->first()->toArray(), $campaign_product->toArray()));
            } 
          
        }*/
        //echo "<pre>"; print_r($products_arr);exit; 

        $getcampaigntot = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
        $campaign->total_paid = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('amount');
        $camptot = 0;
		$offer_applied = 0;
		$tax_percentage_booking_tot = 0;
		$tax_percentage_amount_tot = 0;
       if (isset($getcampaigntot) && count($getcampaigntot) > 0) {
            foreach ($getcampaigntot as $getcampaigntot) {
				$tax_percentage_booking_tot = 0;
				$tax_percentage_amount_tot = 0;
                $getcampaigntotproduct =Product::where('id', '=', $getcampaigntot->product_id)->first();
				
                $bookedfrom[] = strtotime($getcampaigntot->booked_from);
                $bookedto[] = strtotime($getcampaigntot->booked_to);

                $getproductDetails =Product::where('id', '=', $getcampaigntot->product_id)->first();
                $diff=date_diff(date_create($getcampaigntot->booked_from),date_create($getcampaigntot->booked_to));
                //$diff=date_diff(($booked_from_date),($booked_to_date));
                $daysCount = $diff->format("%a");//exit;
                $daysCountCPM = $daysCount + 1;
					$perdayprice = $getproductDetails->default_price/28;

                if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
                    //echo 'fix';exit;
                    $price = $getcampaigntot->price*$getcampaigntot->quantity;
                    //$priceperday = $price;
                    //$priceperselectedDates = $priceperday; 
                    //$camptot += $priceperselectedDates;
					if($campaign->total_paid != 0){
						if(isset($getcampaigntot->tax_percentage)){
							$tax_percentage_booking_tot = $getcampaigntot->tax_percentage;
						}else{
							$tax_percentage_booking_tot = 0;
						}
					}else{
						$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
					}
					if($tax_percentage_booking_tot != 0){
						$tax_percentage_amount_tot = ($price * ($tax_percentage_booking_tot))/100;
					}
					$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
					$camptot += $price+$tax_percentage_amount_tot;
                }else{
                    //echo 'else';exit;
                    //$price = $getcampaigntot->price; 
                    /*$price = $getproductDetails->default_price;
                    $priceperday = $price/28;//exit;
                    $priceperselectedDates = $priceperday * $daysCountCPM;
                    //echo '--daysCountCPM---'.$daysCountCPM;
                    $camptot += $priceperselectedDates;
                    //$camptot += $price;*/
					
					$priceperselectedDates = $getcampaigntot->price;
					
					if($campaign->total_paid != 0){
						if(isset($getcampaigntot->tax_percentage)){
							$tax_percentage_booking_tot = $getcampaigntot->tax_percentage;
						}else{
							$tax_percentage_booking_tot = 0;
						}
					}else{
						$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
					}
					if($tax_percentage_booking_tot != 0){
						$tax_percentage_amount_tot = ($priceperselectedDates * ($tax_percentage_booking_tot))/100;
					}
					$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
                    $camptot += ($priceperselectedDates*$getcampaigntot->quantity)+($tax_percentage_amount_tot*$getcampaigntot->quantity);
                }
                //echo '---camptot123---'.$camptot += $getcampaigntot->price;
            }
        }
//echo '--camptot--'.$camptot; 
//exit;

        $campaignproducts = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
        //echo "<pre>campaignproducts"; print_r(count($campaignproducts));//exit;
        if (isset($campaignproducts) && count($campaignproducts) > 0) {
            
            foreach ($campaignproducts as $campaignproduct) {
				$campaignproduct->tax_percentage_amount_shortlist = 0;
				$campaignproduct->tax_percentage_amount = 0;
				$tax_percentage_booking = 0;
				$tax_percentage_amount = 0;
                $getproductDetails =Product::where('id', '=', $campaignproduct->product_id)->first();
                //echo "<pre>campaignproduct"; print_r(count($campaignproducts));exit;
                $product = Product::where('id', '=', $campaignproduct->product_id)->first();
				
				$area_time_zone_value = Area::select("area_time_zone","area_time_zone_type")->where('id', '=', $product->area)->first(); 
				$campaignproduct->area_time_zone_type = $area_time_zone_value['area_time_zone_type'];
				
                
                /*CPM Calculation*/
                $diff=date_diff(date_create($campaignproduct->booked_from),date_create($campaignproduct->booked_to));
                //$diff=date_diff(($booked_from_date),($booked_to_date));
                $daysCount = $diff->format("%a");//exit;
                $daysCountCPM = $daysCount + 1;
					$perdayprice = $getproductDetails->default_price/28;
                //$daysCoun1tCPM = $daysCount;
                //echo "<pre>diff";print_r($diff);exit;
                //echo "<pre>product";print_r($product);exit;
                //echo $daysCoun1tCPM;exit;
                
                //$price = $campaignproduct->price;
                if(isset($product->fix) && $product->fix=="Fixed"){
                    //echo 'fixed'; exit;
                    /*$price = $campaignproduct->price;
                    $priceperday = $price;
                    $priceperselectedDates = $priceperday;
                    $shortlistedsum+= $priceperselectedDates;
                    $campaignproduct->price = $priceperselectedDates;
                    $cpmsum+= $product->cpm;
                    $impressions = $product->secondImpression;
                    $impressionsperday = (int)($impressions);
                    $impressionsperselectedDates = $impressionsperday;
                    
                    if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                        $impressionsperselectedDates = $impressionsperselectedDates;
                    }else{
                        $impressionsperselectedDates = 1;
                    }
                    $impressionSum+= $impressionsperselectedDates;
                    $campaignproduct->secondImpression = round($impressionsperselectedDates, 2);
                    $cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                    $campaignproduct->cpmperselectedDates = $cpmcal;
                    $campaignproduct->cpm = $cpmcal;
                    $campaignproduct->impressionsperselectedDates = $impressionsperselectedDates;
                    $campaignproduct->priceperselectedDates = $priceperselectedDates;*/

                    $offerDetails = MakeOffer::where([
                        ['campaign_id', '=', $campaignproduct->campaign_id],
                        ['status', '=', 20],
                    ])->get();

                    if(isset($offerDetails) && count($offerDetails)==1){
                        //echo 'offer exists';exit;
                        foreach($offerDetails as $offerDetails){
                             $offerprice = $offerDetails->price;
                             $stripe_percent=$getproductDetails->stripe_percent;
                             
                        //$price = $getproductDetails->default_price;
                        $price = $campaignproduct->price;
                        
                        //$price = $campaign_product->price;
                        $priceperday = $price;
                        $priceperselectedDates = $priceperday;
                        $newpricepercentage = ($priceperselectedDates/$camptot) * 100;
        
                        //$newofferprice = ($offerprice * ($newpricepercentage))/100;
						if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
							$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
							$newofferprice = $newofferprice_wq*$campaignproduct->quantity;
						}else{
							$newofferprice = ($offerprice * ($newpricepercentage))/100;
						}
                        //$offerpriceperday = $newofferprice/28;//exit;
                        //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                        $offerpriceperselectedDates = $newofferprice;
                        $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
                        $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                        $campaignproduct->stripe_percent = $stripe_percent;
                           }
								$offer_applied = 1;
                        }else{
                             //$offerprice = $getproductDetails->default_price;
                             //echo 'no offer exists';exit;
                             $offerprice = $campaignproduct->price;
                             //$offerprice = $getproductDetails->default_price;
                             $stripe_percent=$getproductDetails->stripe_percent;
                             //$price = $getproductDetails->default_price;
                             $price = $campaignproduct->price;
                             //$price = $campaign_product->price;
                             $priceperday = $price;//exit;
                             $priceperselectedDates = $priceperday;
                             $newpricepercentage = ($priceperselectedDates/$camptot) * 100;
            
							if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
                                $newofferprice = $offerprice*$campaignproduct->quantity;
							}else{
								$newofferprice = $offerprice ;
							}
							
                        //$offerpriceperday = $newofferprice/28;//exit;
                        //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                            $offerpriceperselectedDates = $newofferprice;
                            $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
                            $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                            $campaignproduct->stripe_percent = $stripe_percent;
                        }
                        
                        if($campaign->total_paid != 0){
							if(isset($campaignproduct->tax_percentage)){
								$tax_percentage_booking = $campaignproduct->tax_percentage;
							}else{
								$tax_percentage_booking = 0;
							}
						}else{
							$tax_percentage_booking = $getproductDetails->tax_percentage;
						}
						if($tax_percentage_booking != 0){
							$campaignproduct->tax_percentage_amount_shortlist = ($priceperselectedDates * ($tax_percentage_booking))/100;
							$tax_percentage_amount = ($offerpriceperselectedDates * ($tax_percentage_booking))/100;
						}
						$campaignproduct->tax_percentage_amount = round($tax_percentage_amount,2);
                                            
                        $shortlistedsum+= ($priceperselectedDates+$campaignproduct->tax_percentage_amount_shortlist)*$campaignproduct->quantity;
						$shortlistedsumcpm += $priceperselectedDates*$campaignproduct->quantity;
                        $campaignproduct->price = $priceperselectedDates;
        
						$offershortlistedsum+= $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;
						$campaignproduct->offerprice = $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;
						
						$offershortlistedsumcpm+= $offerpriceperselectedDates;
                        $cpmsum+= $getproductDetails->cpm;
                        $impressions = $getproductDetails->secondImpression;
                        $impressionsperday = (float)($impressions/7);
                        $impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
                        
                        if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                            $impressionsperselectedDates = $impressionsperselectedDates;
                        }else{
                            $impressionsperselectedDates = 1;
                        }
                        //$impressionSum+= $product_details->secondImpression; 
                        $impressionSum+= $impressionsperselectedDates*$campaignproduct->quantity;
                        $campaignproduct->secondImpression = round($impressionsperselectedDates, 2);
                        //$cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                        //$offercpmcal = ($offershortlistedsum/$impressionSum) * 1000;
                        $cpmcal = ($priceperselectedDates/$impressionsperselectedDates) * 1000;
                        $offercpmcal = ($offerpriceperselectedDates/$impressionsperselectedDates) * 1000;
                        $campaignproduct->cpmperselectedDates = $cpmcal;
                        $campaignproduct->offercpmperselectedDates = $offercpmcal;
                        $campaignproduct->cpm = $cpmcal;
                        $campaignproduct->offercpm = $offercpmcal;
                        $campaignproduct->impressionsperselectedDates = $impressionsperselectedDates;
                        $campaignproduct->priceperselectedDates = $priceperselectedDates;
                        $campaignproduct->offerpriceperselectedDates = $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;
        
                        $campaignproduct->new_stripe_percent_amount = $newofferStripepercentamt;
                        $campaignproduct->newprocessingfeeamt = $newprocessingfeeamt;
        
                        $newofferStripepercentamtSum += $newofferStripepercentamt;
                        $newprocessingfeeamtSum += $newprocessingfeeamt;
                        //echo "<pre>";print_r($campaignproduct);exit;

                }else{
                    //echo 'variable'; exit;;
                    /*$price = $product->default_price;
                    $priceperday = $price/28;
                    $priceperselectedDates = $priceperday * $daysCountCPM;
                    $shortlistedsum+= $priceperselectedDates;
                    $campaignproduct->price = $priceperselectedDates;
                    $cpmsum+= $product->cpm;
                    $impressions = $product->secondImpression;
                    $impressionsperday = (int)($impressions/7);
                    $impressionsperselectedDates = $impressionsperday * $daysCountCPM;
                    
                    if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                        $impressionsperselectedDates = $impressionsperselectedDates;
                    }else{
                        $impressionsperselectedDates = 1;
                    }
                    $impressionSum+= $impressionsperselectedDates;
                    $campaignproduct->secondImpression = round($impressionsperselectedDates, 2);
                    $cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                    $campaignproduct->cpmperselectedDates = $cpmcal;
                    $campaignproduct->cpm = $cpmcal;
                    $campaignproduct->impressionsperselectedDates = $impressionsperselectedDates;
                    $campaignproduct->priceperselectedDates = $priceperselectedDates;*/

                    $offerDetails = MakeOffer::where([
                        ['campaign_id', '=', $campaignproduct->campaign_id],
                        ['status', '=', 20],
                    ])->get();
                    
                    
                    if(isset($offerDetails) && count($offerDetails)==1){
                        //echo 'variable -offer'; exit;;
                            foreach($offerDetails as $offerDetails){
                                    $offerprice = $offerDetails->price;
                                    $stripe_percent=$getproductDetails->stripe_percent;
                                    
                            $price = $getproductDetails->default_price;
                            
                            //$price = $campaign_product->price;
                            //$priceperday = $price/28;//exit;
                            //echo '---camptot--'.$camptot;
                            //$priceperselectedDates = $priceperday * $daysCountCPM;
							
							
							///variable_offer///
							$priceperday = $campaignproduct->price;
							$priceperselectedDates = $priceperday;
							
							
							
                            $newpricepercentage = ($priceperselectedDates/$camptot) * 100;

                            //$newofferprice = ($offerprice * ($newpricepercentage))/100;//exit;
							if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
								$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
								$newofferprice = $newofferprice_wq*$campaignproduct->quantity;
							}else{
								$newofferprice = ($offerprice * ($newpricepercentage))/100;
							}
                            //$offerpriceperday = $newofferprice/28;//exit;
                            //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                            $offerpriceperselectedDates = $newofferprice;
                            $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
                            $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                            $campaignproduct->stripe_percent = $stripe_percent;
                                }
								$offer_applied = 1;
                            }else{
                               //echo 'variable -no-offer'; exit;;
                                    //$offerprice = $getproductDetails->default_price;
                            $offerprice = $campaignproduct->price;
                            $stripe_percent=$getproductDetails->stripe_percent;
                            $price = $campaignproduct->price;
                            //$price = $campaign_product->price;
                            $priceperday = $price;//exit;
                            $priceperselectedDates = $priceperday;
                            $newpricepercentage = ($priceperselectedDates/$camptot) * 100;

							if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
								$newofferprice = $offerprice*$campaignproduct->quantity;
							}else{
								$newofferprice = $offerprice;
							}
							
                            //$offerpriceperday = $newofferprice/28;//exit;
                            //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                            $offerpriceperselectedDates = $newofferprice;
                            $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
                            $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                            $campaignproduct->stripe_percent = $stripe_percent;
                            }
                            
                            //if($daysCountCPM <= $product->minimumdays){
                            //    $priceperselectedDates = $priceperday * $product->minimumdays;
                            //}
							
							if($campaign->total_paid != 0){
								if(isset($campaignproduct->tax_percentage)){
									$tax_percentage_booking = $campaignproduct->tax_percentage;
								}else{
									$tax_percentage_booking = 0;
								}
							}else{
								$tax_percentage_booking = $getproductDetails->tax_percentage;
							}
							if($tax_percentage_booking != 0){
								$campaignproduct->tax_percentage_amount_shortlist = ($priceperselectedDates * ($tax_percentage_booking))/100;
								$tax_percentage_amount = ($offerpriceperselectedDates * ($tax_percentage_booking))/100;
							}
							$campaignproduct->tax_percentage_amount = round($tax_percentage_amount,2);
                                                
                            $shortlistedsum+= ($priceperselectedDates+$campaignproduct->tax_percentage_amount_shortlist)*$campaignproduct->quantity;
							$shortlistedsumcpm+= $priceperselectedDates*$campaignproduct->quantity;
                            $campaignproduct->price = $priceperselectedDates;
							
							$offershortlistedsum+= $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;
							$campaignproduct->offerprice = $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;

							$offershortlistedsumcpm+= $offerpriceperselectedDates;
                            $cpmsum+= $getproductDetails->cpm;
                            $impressions = $getproductDetails->secondImpression;
                            $impressionsperday = (float)($impressions/7);
                            $impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
                            
                            if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                                $impressionsperselectedDates = $impressionsperselectedDates;
                            }else{
                                $impressionsperselectedDates = 1;
                            }
                            //$impressionSum+= $product_details->secondImpression; 
                            $impressionSum+= $impressionsperselectedDates*$campaignproduct->quantity;
                            $campaignproduct->secondImpression = round($impressionsperselectedDates, 2);
                            //$cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                            //$offercpmcal = ($offershortlistedsum/$impressionSum) * 1000;
                            $cpmcal = ($priceperselectedDates/$impressionsperselectedDates) * 1000;
                            $offercpmcal = ($offerpriceperselectedDates/$impressionsperselectedDates) * 1000;
                            $campaignproduct->cpmperselectedDates = $cpmcal;
                            $campaignproduct->offercpmperselectedDates = $offercpmcal;
                            $campaignproduct->cpm = $cpmcal;
                            $campaignproduct->offercpm = $offercpmcal;
                            $campaignproduct->impressionsperselectedDates = $impressionsperselectedDates;
                            $campaignproduct->priceperselectedDates = $priceperselectedDates;
                            $campaignproduct->offerpriceperselectedDates = $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;

                            $campaignproduct->new_stripe_percent_amount = $newofferStripepercentamt;
                            $campaignproduct->newprocessingfeeamt = $newprocessingfeeamt;

                            $newofferStripepercentamtSum += $newofferStripepercentamt;
                            $newprocessingfeeamtSum += $newprocessingfeeamt;
                            //echo "<pre>";print_r($campaignproduct);exit;
                }
                array_push($products_arr, array_merge(Product::where('id', '=', $campaignproduct->product_id)->first()->toArray(), $campaignproduct->toArray()));
            }

           
           //array_push($products_arr,  array_merge(Product::where('id', '=', $campaign_product->product_id)->first()->toArray()));
        }
        //echo '<pre>'; print_r(($products_arr)); exit;;
        $campaign->products = $products_arr;
        $campaign->actbudg = $products_arr;

        $act_budget = ProductBooking::raw(function($collection) use ($campaign_id) {
            return $collection->aggregate(
                            [
                                [
                                    '$match' =>
                                    [
                                        "campaign_id" => $campaign_id
                                    ]
                                ],
                                [
                                    '$group' =>
                                    [
                                        '_id' => '$campaign_id',
                                        'total_price' => [
                                            '$sum' => '$admin_price'
                                        ]
                                    ]
                                ]
                            ]
            );
        });


        $res = array_sum(array_map(function($item) { 
            return $item['price']; 
        }, $campaign->actbudg));
        //echo "<pre>act_budget";print_r($res);exit;
        $campaign->act_budget = $res;

        $campaign->totalamount = $campaign->act_budget;
        $campaign->offer_applied_res = $offer_applied;

                 
        $campaign->refunded_amount = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('refunded_amount');
        
        $campaign->bal_amount_available_with_amp = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('bal_amount_available_with_amp');
     
 //echo 'impressionSum4'.$impressionSum4;exit;
 
		$campaign->gross_fee_price = 0;
		if(isset($campaign->gross_fee_percentage) && $campaign->gross_fee_percentage > 0){
			$campaign->gross_fee_price = ($offershortlistedsum*$campaign->gross_fee_percentage)/100;
		}else{
			$campaign->gross_fee_price = 0;
		}
		
        $impressionSum4 = round($impressionSum, 2); /* As discussed with Richard on July 14) */
        if($impressionSum4>0){
			$cpmval = (($shortlistedsumcpm+$campaign->gross_fee_price)/$impressionSum4) * 1000;
			$offercpmval = (($offershortlistedsumcpm+$campaign->gross_fee_price)/$impressionSum4) * 1000;
        }else{
			$cpmval = 0;
			$offercpmval = 0;
        }

        $campaign->shortlistedsum = $shortlistedsum;
        $campaign->cpmval = $cpmval;

        $campaign->offershortlistedsum = $offershortlistedsum;
        $campaign->offercpmval = $offercpmval;

        $campaign->impressionSum = $impressionSum4;

        $campaign->newofferStripepercentamtSum = $newofferStripepercentamtSum;
        $campaign->newprocessingfeeamtSum = $newprocessingfeeamtSum;
        $campaign->percentagevalue = ($newofferStripepercentamtSum * 100)/$offershortlistedsum;
        $campaign->finalpurchasepayment = $newofferStripepercentamtSum + $newprocessingfeeamtSum;
        
		
         $campaign_shortlistedsum = $shortlistedsum;
		 if($offershortlistedsum != 0){
			$campaign_cpmval = $offercpmval;
		 }else{
			 $campaign_cpmval = $cpmval;
		 }
         $campaign_impressionSum = $impressionSum4;
        //echo "<pre>campaign";print_r($campaign_impressionSum);exit;
        //echo "<pre>campaign";print_r($campaign_cpmval);exit;
            $total_price = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('owner_price');
             if($total_price == 0){
             $total_price = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('price');
             }
			 
			$res = array_sum(array_map(function($item) { 
				if(isset($item['quantity']) && $item['quantity'] != '' && $item['quantity'] != 0){
					return ($item['price']+$item['tax_percentage_amount'])*$item['quantity']; 
				}else{
					return $item['price']+$item['tax_percentage_amount']; 
				}
            }, $campaign->actbudg));
            $total_price = $res;
			if($offershortlistedsum != 0){
				$total_price = $offershortlistedsum+$campaign->gross_fee_price;
			}else{
				$total_price = $res+$campaign->gross_fee_price;
			}
			
             //echo "<pre>total_price";print_r($total_price);exit;
            $campaign_report = [
                'campaign' => $campaign,
                'areas_covered' => $areas,
                'format_types' => $formats,
                'mediums_covered' => $products_in_campaign->count(),
                'audience_reach' => $audience_reach,
                'repeated_audience' => $repeated_audience,
                'products' => $products_in_campaign,
                'total_price'=>$total_price,
                'products_arr'=>$products_arr,
                'campaign_shortlistedsum'=>$campaign_shortlistedsum,
                'campaign_cpmval'=>$campaign_cpmval,
                'campaign_impressionSum'=>$campaign_impressionSum,
            ];
            // return view('pdf.campaign_details_pdf', $campaign_report); exit;
            $pdf = PDF::loadView('pdf.campaign_details_pdf', $campaign_report);
            // $pdf->save('uploads/campaign' . uniqid() . '.pdf'); die();
            }
            else{
                 $packages_in_campaign = CampaignProduct::where('campaign_id', '=', $campaign_id)->get();
            foreach ($packages_in_campaign as $pkg) {
                $package_tplt = MetroPackage::where('id', '=', $pkg->package_id)->first();
                $pkg->format = $package_tplt->format;
                $pkg->max_trains = $package_tplt->max_trains;
                $pkg->max_slots = $package_tplt->max_slots;
                $pkg->months = $package_tplt->months;
                $pkg->days = $package_tplt->days;
            }
            $campaign_report = [
                'campaign' => $campaign
                // 'format_types' => $formats,
                //'packages' => $packages_in_campaign
            ];
            //$pdf = PDF::loadView('pdf.metro_campaign_details_pdf', $campaign_report);
            $pdf = PDF::loadView('pdf.RFP_campaign_pdf', $campaign_report);
            }
           /* $product_ids_in_campaign = ProductBooking::where('campaign_id', '=', $this->input['campaign_id'])->pluck('product_id');
            $products_in_campaign = Product::whereIn('id', $product_ids_in_campaign)->get();
            $formats = $products_in_campaign->unique('type')->count();
            $areas = $products_in_campaign->unique('area')->count();
            $audience_reach = $products_in_campaign->each(function($v, $k) {
                        $v->impressions = intval(str_replace(",", "", $v->impressions));
                    })->sum('impressions');
            $repeated_audience = $audience_reach * 30 / 100;
            $campaign_report = [
                'campaign' => $campaign,
                'areas_covered' => $areas,
                'format_types' => $formats,
                'mediums_covered' => $products_in_campaign->count(),
                'audience_reach' => $audience_reach,
                'repeated_audience' => $repeated_audience,
                'products' => $products_in_campaign
            ];
            // return view('pdf.campaign_details_pdf', $campaign_report); exit; 
            $pdf = PDF::loadView('pdf.campaign_details_pdf', $campaign_report);
            // $pdf->save('uploads/campaign' . uniqid() . '.pdf'); die();*/
            $mail_tmpl_params = [
                'sender_email' => $user['email'],
               // 'receiver_name' => $this->input['receiver_name']
                'receiver_name' => $campaign['name']
            ];
            $pdf_file_name = str_replace(' ', '-', preg_replace('/\s+/', ' ',$campaign['name']));
            $mail_data = [
                'email_to' => $this->input['email'],
                //'recipient_name' => $this->input['receiver_name'],
                'recipient_name' => $campaign['name'],
                'pdf_file_name' => $pdf_file_name. ".pdf",
                'pdf' => $pdf
            ];
            Mail::send('mail.campaign_details', $mail_tmpl_params, function($message) use ($mail_data) {
                $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('A campaign has been shared to you!');
                $message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
            });
            if (!Mail::failures()) {
                return response()->json(['status' => 1, 'message' => "Your campaign has been shared successfully."]);
            } else {
                return response()->json(['status' => 0, 'message' => "There was an error sending the email. Please try again."]);
            }
        } catch (Exception $ex) {
            return response()->json(['status' => 0, 'message' => "There was an error generating the campaign report."]);
        }
    }

    public function shareMetroCampaign() {
        $this->validate($this->request, [
            'campaign_id' => 'required',
            'email' => 'required',
            'receiver_name' => 'required'
                ], [
            'campaign_id.required' => 'Campaign id is required',
            'email.required' => 'Email is required',
            'receiver_name.required' => 'Receiver name is required'
                ]
        );
        try {
            $user = JWTAuth::parseToken()->getPayload()['user'];
            $campaign = Campaign::where([
                        ['id', '=', $this->input['campaign_id']],
                        ['format_type', '=', Format::$FORMAT_TYPE['metro']]
                    ])->first();
            $packages_in_campaign = CampaignProduct::where('campaign_id', '=', $this->input['campaign_id'])->get();
            foreach ($packages_in_campaign as $pkg) {
                $package_tplt = MetroPackage::where('id', '=', $pkg->package_id)->first();
                $pkg->format = $package_tplt->format;
                $pkg->max_trains = $package_tplt->max_trains;
                $pkg->max_slots = $package_tplt->max_slots;
                $pkg->months = $package_tplt->months;
                $pkg->days = $package_tplt->days;
            }
            $campaign_report = [
                'campaign' => $campaign,
                // 'format_types' => $formats,
                'packages' => $packages_in_campaign
            ];
            $pdf = PDF::loadView('pdf.metro_campaign_details_pdf', $campaign_report);
            // $pdf->save('uploads/campaign' . uniqid() . '.pdf'); die();
            $mail_tmpl_params = [
                'sender_email' => $user['email'],
                'receiver_name' => $this->input['receiver_name']
            ];
            $pdf_file_name = str_replace(' ', '-', preg_replace('/\s+/', ' ',$campaign['name']));
            $mail_data = [
                'email_to' => $this->input['email'],
                'recipient_name' => $this->input['receiver_name'],
                'pdf_file_name' => $pdf_file_name. ".pdf",
                'pdf' => $pdf
            ];
            Mail::send('mail.campaign_details', $mail_tmpl_params, function($message) use ($mail_data) {
                $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('A campaign has been shared to you!');
                $message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
            });
            if (!Mail::failures()) {
                return response()->json(['status' => 1, 'message' => "Your campaign has been shared successfully."]);
            } else {
                return response()->json(['status' => 0, 'message' => "There was an error sending the email. Please try again."]);
            }
        } catch (Exception $ex) {
            return response()->json(['status' => 0, 'message' => "There was an error generating the campaign report."]);
        }
    }
	
	public function deleteMultipleProductFromCampaign(Request $request) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
		
		if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
		$success = 0;
		$price = 0;
		$product_booking_id = $input['product_booking_id'];
		$product_offer_price = $input['product_offer_price'];
		$campaign_id = $input['campaign_id'];
		foreach($product_booking_id as $key => $productids){
			$product_id = $productids;
			if(isset($product_offer_price) && !empty($product_offer_price)){
				$price = $product_offer_price[$key];
			}
			$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
			$user_internal = User::where('id', '=', $user_mongo['user_id'])->first();
			if (isset($user_internal->client)) {
				$user_type = $user_internal->client->client_type->type;
			} else { 
				$user_type = "basic";
			}
			if ($user_type == "basic") {
				$filter_criteria = [
					['id', '=', $campaign_id],
					['created_by', '=', $user_mongo['id']]
				];
				$campaign = Campaign::where($filter_criteria)->first();
				
				if (!isset($campaign) || empty($campaign)) {
					return response()->json(["status" => 0, "message" => "campaign referred not found in the database."]);
				} else {
					if ($campaign->from_suggestion) {
						return response()->json(["status" => "0", "message" => "You can not delete a product from a campaign you asked suggestion for. Please create a change-request instead."]);
					}
				}
				$campaign_product = ProductBooking::where([
							['campaign_id', '=', $campaign_id],
							['id', '=', $product_id]
						])->first();

				$offer_product_price = MakeOffer::where([
					['campaign_id', '=', $campaign_id],
					['status', '=', 20]
				])->orWhere([
					['campaign_id', '=', $campaign_id],
					['status', '=', 10]
				])->first();
							
				if (isset($campaign_product) && !empty($campaign_product)) {
					if(isset($offer_product_price) && !empty($offer_product_price)){
						$offer_product_price->old_price = $offer_product_price->price;
						$offer_product_price->price = $offer_product_price->price - $price;
						$offer_product_price->save();
						$success = $campaign_product->delete();
					}else{
						$success = $campaign_product->delete();
					}
				}
			}else{
				return response()->json(['status' => '0', 'message' => 'You are not authorized to delete this product.']);
			}
		}
        if ($success) {
            return response()->json(['status' => '1', 'message' => 'Product(s) deleted from campaign successfully.']);
        } else {
            return response()->json(['status' => '0', 'message' => 'Error removing products from campaign.']);
        }
    } 

    public function deleteProductFromCampaign($campaign_id, $product_id, $price) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user_internal = User::where('id', '=', $user_mongo['user_id'])->first();
        //echo '<pre>user'; print_r($user_internal); //exit;
        //echo '<pre>user'; print_r($user_mongo); //exit;
        if (isset($user_internal->client)) {
            $user_type = $user_internal->client->client_type->type;
        } else {
            $user_type = "basic";
        }
        if ($user_type == "bbi") {
            // bbi campaign
            $campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        ['type', '=', CAMPAIGN::$CAMPAIGN_USER_TYPE['bbi']]
                    ])->orWhere([
                        ['id', '=', $campaign_id],
                        ['type', '=', CAMPAIGN::$CAMPAIGN_USER_TYPE['user']]
                    ])->first();
            if (!isset($campaign) || empty($campaign)) {
                return response()->json(["status" => 0, "message" => "campaign referred not found in the database."]);
            } else if (isset($campaign->from_suggestion) && $campaign->from_suggestion == true) {
                if ($campaign->status > Campaign::$CAMPAIGN_STATUS['quote-requested']
                        and $campaign->status != Campaign::$CAMPAIGN_STATUS['change-requested']) {
                    return response()->json(["status" => "0", "message" => "You can not remove a product from a campaign that's pending from any kind of user approval."]);
                }
            } else if (!isset($campaign->from_suggestion) || $campaign->from_suggestion == false) {
                if ($campaign->status == Campaign::$CAMPAIGN_STATUS['quote-requested'] && $campaign->status == Campaign::$CAMPAIGN_STATUS['change-requested']) {
                    return response()->json(["status" => "0", "message" => "You can not remove a product from a campaign that's pending from any kind of user approval."]);
                }
            }
            $campaign_product = ProductBooking::where([
                        ['campaign_id', '=', $campaign_id],
                        ['id', '=', $product_id]
                    ])->first();
                    //echo '<pre>bbi'; print_r($campaign_product); exit;
            if (isset($campaign_product) && !empty($campaign_product)) {
                $campaign_product->delete();
                return response()->json(['status' => 1, 'message' => 'Product removed from campaign successfully.']);
            } else {
                return response()->json(['status' => 0, 'message' => 'You are not authorized to delete this product']);
            }
        } else if ($user_type == "owner") {
            // owner campaign
            $filter_criteria = [
                ['id', '=', $campaign_id],
                ['type', '=', CAMPAIGN::$CAMPAIGN_USER_TYPE['owner']],
                ['client_mongo_id', '=', $user_mongo['client_mongo_id']]
            ];
            $campaign = Campaign::where($filter_criteria)->first();
            if (!isset($campaign) || empty($campaign)) {
                return response()->json(["status" => 0, "message" => "You can not delete products from this campaign."]);
            } else if ($campaign->status >= Campaign::$CAMPAIGN_STATUS['scheduled']) {
                return response()->json(["status" => "0", "message" => "You can not delete a product from camapign after it's running or has closed."]);
            }
            $campaign_product = ProductBooking::where([
                        ['campaign_id', '=', $campaign_id],
                        ['id', '=', $product_id]
                       // ['product_owner', '=', $user_mongo['client_mongo_id']]
                    ])->first();
                    //echo '<pre>owner'; print_r($campaign_product); exit;
            if (isset($campaign_product) && !empty($campaign_product)) {
                $campaign_product->delete();
                return response()->json(['status' => 1, 'message' => 'Product removed from campaign successfully.']);
            } else {
                return response()->json(['status' => 0, 'message' => 'You are not authorized to delete this product']);
            }
        } else {
            // user campaign
          // user campaign
            $filter_criteria = [
                ['id', '=', $campaign_id],
                ['created_by', '=', $user_mongo['id']]
            ];
            $campaign = Campaign::where($filter_criteria)->first();
            //echo '<pre>else'; print_r($campaign); //exit;
            if (!isset($campaign) || empty($campaign)) {
                return response()->json(["status" => 0, "message" => "campaign referred not found in the database."]);
            } else {
                if ($campaign->from_suggestion) {
                    return response()->json(["status" => "0", "message" => "You can not delete a product from a campaign you asked suggestion for. Please create a change-request instead."]);
                }
                //if ($campaign->status >= Campaign::$CAMPAIGN_STATUS['quote-requested']) {
                //    return response()->json(["status" => "0", "message" => "You can not remove a product from a campaign when any kind of admin approval is pending."]);
                //}
            }
            $campaign_product = ProductBooking::where([
                        ['campaign_id', '=', $campaign_id],
                        ['id', '=', $product_id]
                    ])->first();
                    //dd($campaign_product);
            // $offer_product_price = MakeOffer::where([
            //     ['campaign_id', '=', $campaign_id],
            //     ['status', '=', 20]
            // ])->first();

            $offer_product_price = MakeOffer::where([
                ['campaign_id', '=', $campaign_id],
                ['status', '=', 20]
            ])->orWhere([
                ['campaign_id', '=', $campaign_id],
                ['status', '=', 10]
            ])->first();
            //$price = 7500;
                        
                    //echo '<pre>else-offer_product_price'.$campaign_id.'---'; print_r($offer_product_price); exit;
            if (isset($campaign_product) && !empty($campaign_product)) {
                if(isset($offer_product_price) && !empty($offer_product_price)){
                    $offer_product_price->old_price = $offer_product_price->price;
                    $offer_product_price->price = $offer_product_price->price - $price;
                    $offer_product_price->save();
                    $campaign_product->delete();
                }else{
                    $campaign_product->delete();
                }
                
            } else {
                return response()->json(['status' => 0, 'message' => 'You are not authorized to delete this product']);
            }
        }
        return response()->json(["status" => "1", "message" => "Product deleted from campaign successfully."]);
    }

    /*
     * Returns the campaign related requests made by users. along with
     * some user details
     */

    public function getAllCampaignRequests() {
        $feeds = [];
        $requested_campaign_suggestions = CampaignSuggestionRequest::where("processed", "=", false)->orderBy('updated_at', 'desc')->get();
        $other_campaign_feeds = Campaign::whereIn('format_type', [null, Format::$FORMAT_TYPE['ooh']])
                        ->where(function($query) {
                            $query->where([
                                ['status', '=', Campaign::$CAMPAIGN_STATUS['quote-requested']],
                                ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['user']]
                            ])
                            ->orWhere([
                                ['status', '=', Campaign::$CAMPAIGN_STATUS['booking-requested']],
                                ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['user']]
                            ])
                            ->orWhere([
                                ['status', '=', Campaign::$CAMPAIGN_STATUS['change-requested']],
                                ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['user']]
                            ]);
                        })
                        ->orderBy('updated_at', 'desc')->get();

        $metro_campaign_feeds = Campaign::where([
                    ['status', '=', Campaign::$CAMPAIGN_STATUS['metro-campaign-checked-out']],
                    ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['user']],
                    ['format_type', '=', Format::$FORMAT_TYPE['metro']]
                ])->orderBy('updated_at', 'desc')->get();
                
        $current_date = date('d-m-Y');
        $colorcode = '';
        $diff= '';
        
        $user_campaigns_arr = [];       
        $j = 0;

        foreach ($other_campaign_feeds as $ocf) {
            $user_mongo = UserMongo::select('first_name', 'last_name', 'email', 'phone')->where('id', '=', $ocf->created_by
            )->first();
            if(!empty($user_mongo)){
            $ocf->contact_name = $user_mongo->first_name . ' ' . $user_mongo->last_name;
                $ocf->email = $user_mongo->email;
                $ocf->phone = $user_mongo->phone;
            }
             
            /*$campaign_products = ProductBooking::where([
                    ['campaign_id', '=', $ocf->id],
                    //['product_owner', '=', $user_mongo->id]
                ])->get();*/
                
            $total_paid = CampaignPayment::where('campaign_id', '=', $ocf->id)->sum('amount');
            $total_price = ProductBooking::where('campaign_id', '=', $ocf->id)->sum('price');
            $ocf->total_paid = $total_paid;
            $ocf->total_price = $total_price;
            //$ocf->total_paid = $total_paid;
            $count = ProductBooking::where('campaign_id', '=', $ocf->id)->count();
            $ocf->product_count = $count;
            $product_start_date = ProductBooking::where('campaign_id', '=', $ocf->id)->select("booked_from","booked_to","product_id")->orderBy('booked_from', 'asc')->first();
            if (!empty($product_start_date)) {
				$product_date_type = Product::where('id', '=', $product_start_date->product_id)->first();
				$area_time_zone_value = Area::select("area_time_zone","area_time_zone_type")->where('id', '=', $product_date_type->area)->first(); 
				$ocf->area_time_zone_type = $area_time_zone_value['area_time_zone_type'];
                $ocf->start_date = $product_start_date->booked_from;
                $ocf->end_date = $product_start_date->booked_to;
                $diff=date_diff(date_create($current_date),date_create($ocf->start_date));
                if($diff->days <=7 ){
                    $ocf->colorcode = 'red';
                }else{
                    $ocf->colorcode = 'black';
                }
            }
                
            $products_in_campaign = [];
            if (isset($campaign_products) && count($campaign_products) > 0) { 

            foreach ($campaign_products as $campaign_product) {
                // adding campaign products to it.
                $product = Product::where('id', '=', $campaign_product->product_id)->first();
				
                
				
                if($campaign_product->booked_from!=null){
                //$booked_from[] = strtotime($campaign_product->booked_from);
                $booked_from = $campaign_product->booked_from;
                }
                if($campaign_product->booked_to!=null){
                    //$booked_to[] = strtotime($campaign_product->booked_to); 
                    $booked_to = $campaign_product->booked_to;
                }
                
                $ocf->contact_name = $user_mongo->first_name . ' ' . $user_mongo->last_name;
                $ocf->email = $user_mongo->email;
                $ocf->phone = $user_mongo->phone;
                $ocf->from_date = $booked_from;
                $ocf->to_date = $booked_to;
            }
        }
            
            // $ocf->contact_name = $user_mongo->first_name . ' ' . $user_mongo->last_name;
            // $ocf->email = $user_mongo->email;
            // $ocf->phone = $user_mongo->phone;
            // $ocf->from_date = $booked_from;
            // $ocf->to_date = $booked_to; 
    //    } 
        $user_details = UserMongo::select('first_name', 'last_name', 'email', 'phone')->where('id', '=', $ocf->created_by)->first();
            array_push($user_campaigns_arr, array_merge($ocf->toArray(), $user_details->toArray()));
            ++$j;
        }

        foreach ($metro_campaign_feeds as $mcf) {
            $user_mongo = UserMongo::select('first_name', 'last_name', 'email', 'phone')->where('id', '=', $mcf->created_by)->first();
            $mcf->contact_name = $user_mongo->first_name . ' ' . $user_mongo->last_name;
            $mcf->email = $user_mongo->email;
            $mcf->phone = $user_mongo->phone;
        }

        $feeds = [
            'metro_campaign_feeds' => $metro_campaign_feeds,
            'requested_campaign_suggestions' => $requested_campaign_suggestions,
            'other_campaign_feeds' => $other_campaign_feeds
        ];
        return response()->json($feeds);
    }

    public function getAllCampaignsForAdmin(Request $request) {
		if ($request->isJson()) {
			$input = $request->json()->all();
		} else {
			$input = $request->all();
		}
		
		if (isset($this->input['tab_status'])){
			if($this->input['tab_status'] == 'insertion_order'){
				$user_campaigns = Campaign::whereIn('format_type', [null, Format::$FORMAT_TYPE['ooh']])
						->where('type', '=', Campaign::$CAMPAIGN_USER_TYPE['user'])
						->where('status', '=', Campaign::$CAMPAIGN_STATUS['booking-requested'])
						->orderBy('updated_at', 'desc')->get();
			}else if($this->input['tab_status'] == 'scheduled'){
				$user_campaigns = Campaign::whereIn('format_type', [null, Format::$FORMAT_TYPE['ooh']])
						->where('type', '=', Campaign::$CAMPAIGN_USER_TYPE['user'])
						->where('status', '=', Campaign::$CAMPAIGN_STATUS['scheduled'])
						->orderBy('updated_at', 'desc')->get();	
			}else if($this->input['tab_status'] == 'running'){
				$user_campaigns = Campaign::whereIn('format_type', [null, Format::$FORMAT_TYPE['ooh']])
						->where('type', '=', Campaign::$CAMPAIGN_USER_TYPE['user'])
						->where('status', '=', Campaign::$CAMPAIGN_STATUS['running'])
						->orderBy('updated_at', 'desc')->get();	
			}else if($this->input['tab_status'] == 'closed'){
				$user_campaigns = Campaign::whereIn('format_type', [null, Format::$FORMAT_TYPE['ooh']])
						->where('type', '=', Campaign::$CAMPAIGN_USER_TYPE['user'])
						->where('status', '!=', Campaign::$CAMPAIGN_STATUS['campaign-preparing'])
						->where('status', '!=', Campaign::$CAMPAIGN_STATUS['booking-requested'])
						->orderBy('updated_at', 'desc')->get();	
			}else if($this->input['tab_status'] == 'cancelled'){
				$user_campaigns = Campaign::whereIn('format_type', [null, Format::$FORMAT_TYPE['ooh']])
						->where('type', '=', Campaign::$CAMPAIGN_USER_TYPE['user'])
						->where('status', '=', Campaign::$CAMPAIGN_STATUS['deleted-cancelled'])
						->orderBy('updated_at', 'desc')->get();
			}else{
				$user_campaigns = Campaign::whereIn('format_type', [null, Format::$FORMAT_TYPE['ooh']])
							->where('type', '=', Campaign::$CAMPAIGN_USER_TYPE['user'])
							->orderBy('updated_at', 'desc')->get();
			}
		}else{
			$user_campaigns = Campaign::whereIn('format_type', [null, Format::$FORMAT_TYPE['ooh']])
							->where('type', '=', Campaign::$CAMPAIGN_USER_TYPE['user'])
							->orderBy('updated_at', 'desc')->get();
		}
        $current_date = date('d-m-Y');
        $colorcode = '';
        $diff= '';
        
        $user_campaigns_arr = [];       
        $j = 0;
        foreach ($user_campaigns as $key => $user_campaign) {
			
            $product_start_date = ProductBooking::where('campaign_id', '=', $user_campaign->id)->select("booked_from","booked_to","product_id")->orderBy('booked_from', 'asc')->first();
            if (!empty($product_start_date)) {
                $user_campaign->start_date = $product_start_date->booked_from;
                $user_campaign->end_date = $product_start_date->booked_to;
				if(!is_null($product_start_date->product_id)){
					$product_area =Product::where('id', '=', $product_start_date->product_id)->select("area")->first();
					if(!is_null($product_area)){	
						$area_time_zone_value = Area::select("area_time_zone","area_time_zone_type")->where('id', '=', $product_area->area)->first();
						if(!is_null($area_time_zone_value)){
							$area_time_zone_type = $area_time_zone_value['area_time_zone_type'];
							if(!is_null($area_time_zone_type)){
								$start_time = new DateTime( $user_campaign->start_date );
								$end_time = new DateTime( $user_campaign->end_date );
								$laTimezone = new DateTimeZone($area_time_zone_type);
								$start_time->setTimeZone( $laTimezone );
								$end_time->setTimeZone( $laTimezone );
								$user_campaign->start_date = $start_time->format( 'Y-m-d H:i:s' );
								$user_campaign->end_date = $end_time->format( 'Y-m-d H:i:s' );	
							}
						}
					}
				}
                $diff=date_diff(date_create($current_date),date_create($user_campaign->start_date));
                if($diff->days <=7 ){
                    $user_campaign->colorcode = 'red';
                }else{
                    $user_campaign->colorcode = 'black';
                }
            }
			
			if (isset($this->input['tab_status'])){
				if($this->input['tab_status'] == 'scheduled'){
					if(date('Y-m-d') > $user_campaign->start_date || date('Y-m-d') > $user_campaign->end_date){
						unset($user_campaigns[$key]);
						continue;
					}
				}if($this->input['tab_status'] == 'running'){
					if(date('Y-m-d') < $user_campaign->start_date && date('Y-m-d') < $user_campaign->end_date){
						unset($user_campaigns[$key]);
						continue;
					}
				}if($this->input['tab_status'] == 'closed'){
					if($user_campaign->status == Campaign::$CAMPAIGN_STATUS['stopped'] || $user_campaign->status == Campaign::$CAMPAIGN_STATUS['suspended'] || date('Y-m-d') > $user_campaign->end_date){

					}else{
						unset($user_campaigns[$key]);
						continue;
					}
				} 
			}
			
			$price = 0;
            $camptot = 0;
			$offershortlistedsum = 0;
			$total_price = 0;
			$tax_percentage_booking = 0;
			$tax_percentage_amount = 0;
			$tax_percentage_booking_tot = 0;
			$tax_percentage_amount_tot = 0;
            $total_paid = CampaignPayment::where('campaign_id', '=', $user_campaign->id)->sum('amount');
            //$total_price = ProductBooking::where('campaign_id', '=', $user_campaign->id)->sum('price');
			
			
			$campaign_products = ProductBooking::where('campaign_id', '=', $user_campaign->id)->get();
					
			if (isset($campaign_products) && count($campaign_products) > 0) {
				foreach ($campaign_products as $key => $campaign_product_camp) {
					$tax_percentage_booking_tot = 0;
					$tax_percentage_amount_tot = 0;
					$diff=date_diff(date_create($campaign_product_camp->booked_from),date_create($campaign_product_camp->booked_to));
					$daysCount = $diff->format("%a");
					$daysCountCPM = $daysCount + 1;
					$getproductDetails =Product::where('id', '=', $campaign_product_camp->product_id)->get();
					if (!$getproductDetails->isEmpty()){
						$getproductDetails = $getproductDetails->first();
						$perdayprice = $getproductDetails->default_price/28;
						
						if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
							$price_cp1 = $campaign_product_camp->price*$campaign_product_camp->quantity;
							if($total_paid != 0){
								if(isset($campaign_product_camp->tax_percentage)){
									$tax_percentage_booking_tot = $campaign_product_camp->tax_percentage;
								}else{
									$tax_percentage_booking_tot = 0;
								}
							}else{
								$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
							}
							if($tax_percentage_booking_tot != 0){
								$tax_percentage_amount_tot = ($price_cp1 * ($tax_percentage_booking_tot))/100;
							}
							$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
							$camptot += $price_cp1+$tax_percentage_amount_tot;
							
						}else{
							$priceperselectedDates = $campaign_product_camp->price;
							if($total_paid != 0){
								if(isset($campaign_product_camp->tax_percentage)){
									$tax_percentage_booking_tot = $campaign_product_camp->tax_percentage;
								}else{
									$tax_percentage_booking_tot = 0;
								}
							}else{
								$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
							}
							if($tax_percentage_booking_tot != 0){
								$tax_percentage_amount_tot = ($priceperselectedDates * ($tax_percentage_booking_tot))/100;
							}
							$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
							$camptot += ($priceperselectedDates*$campaign_product_camp->quantity)+($tax_percentage_amount_tot*$campaign_product_camp->quantity);
						}
					}
				}
			}
			if (isset($campaign_products) && count($campaign_products) > 0) {
				foreach ($campaign_products as $campaign_product) {
					$campaign_product->tax_percentage_amount_shortlist = 0;
					$campaign_product->tax_percentage_amount = 0;
					$tax_percentage_booking = 0;
					$tax_percentage_amount = 0;
					$diff=date_diff(date_create($campaign_product->booked_from),date_create($campaign_product->booked_to));
					$daysCount = $diff->format("%a");
					$daysCountCPM = $daysCount + 1;
					$getproductDetails =Product::where('id', '=', $campaign_product->product_id)->get();
					if (!$getproductDetails->isEmpty()){
						$getproductDetails = $getproductDetails->first();		
						$perdayprice = $getproductDetails->default_price/28;
						
						$offerDetails = MakeOffer::where([
							['campaign_id', '=', $campaign_product->campaign_id],
							['status', '=', 20],
						])->get();
						if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
							if(isset($offerDetails) && count($offerDetails)==1){
								foreach($offerDetails as $offerDetails){
									$price_cp = $campaign_product->price;
									$offerprice = $offerDetails->price;
									$priceperday = $price_cp;
									$priceperselectedDates = $priceperday;
									$newpricepercentage = ($priceperselectedDates/$camptot) * 100;
									
									$newofferprice = ($offerprice * ($newpricepercentage))/100;
									if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
										$newofferprice = $newofferprice*$campaign_product->quantity;
									}
									$price = $newofferprice;
								}
							}else{
								$priceperselectedDates = $campaign_product->price;
								if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
									$price = $campaign_product->price*$campaign_product->quantity;
								}else{
									$price = $campaign_product->price;
								}
							}
							if($total_paid != 0){
								if(isset($campaign_product->tax_percentage)){
									$tax_percentage_booking = $campaign_product->tax_percentage;
								}else{
									$tax_percentage_booking = 0;
								}
							}else{
								$tax_percentage_booking = $getproductDetails->tax_percentage;
							}
							if($tax_percentage_booking != 0){
								$tax_percentage_amount = ($price * ($tax_percentage_booking))/100;
							}
                            $user_campaign->tax_percentage_amount = round($tax_percentage_amount,2);

							$offershortlistedsum+= $price+$user_campaign->tax_percentage_amount;
						}else{
							if(isset($offerDetails) && count($offerDetails)==1){
								foreach($offerDetails as $offerDetails){
									$price_cp = $getproductDetails->default_price;
									$offerprice = $offerDetails->price;
									//$priceperday = $price_cp/28;
									//$priceperselectedDates = $priceperday * $daysCountCPM;
									
									///variable_offer///
									$priceperday = $campaign_product->price;
									$priceperselectedDates = $priceperday;
									
									
									$newpricepercentage = ($priceperselectedDates/$camptot) * 100;
									
									$newofferprice = ($offerprice * ($newpricepercentage))/100;
									if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
										$newofferprice = $newofferprice*$campaign_product->quantity;
									}
									$price = $newofferprice;
								}
							}else{
								$priceperselectedDates = $campaign_product->price;
								if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
									$price = $campaign_product->price*$campaign_product->quantity;
								}else{
									$price = $campaign_product->price;
								}
							}
							if($total_paid != 0){
								if(isset($campaign_product->tax_percentage)){
									$tax_percentage_booking = $campaign_product->tax_percentage;
								}else{
									$tax_percentage_booking = 0;
								}
							}else{
								$tax_percentage_booking = $getproductDetails->tax_percentage;
							}
							if($tax_percentage_booking != 0){
								$tax_percentage_amount = ($price * ($tax_percentage_booking))/100;
							}
                            $user_campaign->tax_percentage_amount = round($tax_percentage_amount,2);
							
							$offershortlistedsum+= $price+$user_campaign->tax_percentage_amount;
						}
					}
					$total_price = $offershortlistedsum;
				}
			}
			$user_campaign->total_paid = $total_paid;
			if (isset($this->input['tab_status'])) {
				if($this->input['tab_status'] == 'payments'){
					if($total_paid == 0){
						unset($user_campaigns[$key]);
						continue;
					}
				}
			}
			
			$gross_fee_price = 0;
			if(isset($user_campaign->gross_fee_percentage) && $user_campaign->gross_fee_percentage > 0){
				$gross_fee_price = ($offershortlistedsum*$user_campaign->gross_fee_percentage)/100;
			}else{
				$gross_fee_price = 0;
			}
            $user_campaign->total_price = $total_price+$gross_fee_price;
            $user_campaign->pending = ($total_price+$gross_fee_price)-$total_paid;
			if($user_campaign->pending < 0){
				$user_campaign->pending = 0;
			}
            $count = ProductBooking::where('campaign_id', '=', $user_campaign->id)->count();
            $user_campaign->product_count = $count;
            
            $user_details = UserMongo::select('first_name', 'last_name', 'email', 'phone')->where('id', '=', $user_campaign->created_by)->first();
            if($user_details){
               $user_detailstoArray = $user_details->toArray();
            }else{
                $user_detailstoArray = [];
            }
            array_push($user_campaigns_arr, array_merge($user_campaign->toArray(), $user_detailstoArray));
            ++$j;
        }
        //echo '<pre>'; print_r($user_campaigns_arr);exit;
        $admin_campaigns = Campaign::whereIn('format_type', [null, Format::$FORMAT_TYPE['ooh']])->where([
                    ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['bbi']],
                ])->orderBy('updated_at', 'desc')->get();
        if (!empty($admin_campaigns)) {
            $i = 0;
            foreach ($admin_campaigns as $campaign) {
				$total_price = 0;
                $total_paid = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('amount');
                //$total_price = ProductBooking::where('campaign_id', '=', $campaign->id)->sum('price');
				
				
				
				$campaign_products = ProductBooking::where('campaign_id', '=', $campaign->id)->get();
						
				if (isset($campaign_products) && count($campaign_products) > 0) {
					foreach ($campaign_products as $campaign_product_camp) {
						$tax_percentage_booking_tot = 0;
						$tax_percentage_amount_tot = 0;
						$diff=date_diff(date_create($campaign_product_camp->booked_from),date_create($campaign_product_camp->booked_to));
						$daysCount = $diff->format("%a");
						$daysCountCPM = $daysCount + 1;
						$getproductDetails =Product::where('id', '=', $campaign_product_camp->product_id)->get();
						if (!$getproductDetails->isEmpty()){	
							$getproductDetails = $getproductDetails->first();
							$perdayprice = $getproductDetails->default_price/28;
							
							if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
								$price_cp1 = $campaign_product_camp->price*$campaign_product_camp->quantity;
								if($total_paid != 0){
									if(isset($campaign_product_camp->tax_percentage)){
										$tax_percentage_booking_tot = $campaign_product_camp->tax_percentage;
									}else{
										$tax_percentage_booking_tot = 0;
									}
								}else{
									$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
								}
								if($tax_percentage_booking_tot != 0){
									$tax_percentage_amount_tot = ($price_cp1 * ($tax_percentage_booking_tot))/100;
								}
								$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
								$camptot += $price_cp1+$tax_percentage_amount_tot;
							}else{
								if($daysCountCPM <= $getproductDetails->minimumdays){
									$priceperselectedDates = round($perdayprice * $getproductDetails->minimumdays);
								}else{
									$priceperselectedDates = round($perdayprice * $daysCountCPM);
								}
								if($total_paid != 0){
									if(isset($campaign_product_camp->tax_percentage)){
										$tax_percentage_booking_tot = $campaign_product_camp->tax_percentage;
									}else{
										$tax_percentage_booking_tot = 0;
									}
								}else{
									$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
								}
								if($tax_percentage_booking_tot != 0){
									$tax_percentage_amount_tot = ($priceperselectedDates * ($tax_percentage_booking_tot))/100;
								}
								$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
								$camptot += ($priceperselectedDates*$campaign_product_camp->quantity)+($tax_percentage_amount_tot*$campaign_product_camp->quantity);
							}
						}
					}
				}
				if (isset($campaign_products) && count($campaign_products) > 0) {
					foreach ($campaign_products as $campaign_product) {
						$user_campaign->tax_percentage_amount = 0;
						$tax_percentage_booking = 0;
						$tax_percentage_amount = 0;
						$diff=date_diff(date_create($campaign_product->booked_from),date_create($campaign_product->booked_to));
						$daysCount = $diff->format("%a");
						$daysCountCPM = $daysCount + 1;
						$getproductDetails =Product::where('id', '=', $campaign_product->product_id)->get();
						if (!$getproductDetails->isEmpty()){	
							$getproductDetails = $getproductDetails->first();
							$perdayprice = $getproductDetails->default_price/28;
							
							$offerDetails = MakeOffer::where([
								['campaign_id', '=', $campaign_product->campaign_id],
								['status', '=', 20],
							])->get();
							if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
								if(isset($offerDetails) && count($offerDetails)==1){
									foreach($offerDetails as $offerDetails){
										$price_cp = $campaign_product->price;
										$offerprice = $offerDetails->price;
										$priceperday = $price_cp;
										$priceperselectedDates = $priceperday;
										$newpricepercentage = ($priceperselectedDates/$camptot) * 100;
										
										$newofferprice = ($offerprice * ($newpricepercentage))/100;
										if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
											$newofferprice = $newofferprice*$campaign_product->quantity;
										}
										$price = $newofferprice;
									}
								}else{
									$priceperselectedDates = $campaign_product->price;
									if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
										$price = $campaign_product->price*$campaign_product->quantity;
									}else{
										$price = $campaign_product->price;
									}
								}
								if($total_paid != 0){
									if(isset($campaign_product->tax_percentage)){
										$tax_percentage_booking = $campaign_product->tax_percentage;
									}else{
										$tax_percentage_booking = 0;
									}
								}else{
									$tax_percentage_booking = $getproductDetails->tax_percentage;
								}
								if($tax_percentage_booking != 0){
									$tax_percentage_amount = ($price * ($tax_percentage_booking))/100;
								}
								$user_campaign->tax_percentage_amount = round($tax_percentage_amount,2);
								
								$offershortlistedsum+= $price+$user_campaign->tax_percentage_amount;
							}else{
								if(isset($offerDetails) && count($offerDetails)==1){
									foreach($offerDetails as $offerDetails){
										$price_cp = $getproductDetails->default_price;
										$offerprice = $offerDetails->price;
										//$priceperday = $price_cp/28;
										//$priceperselectedDates = $priceperday * $daysCountCPM;
											
										///variable_offer///
										$priceperday = $campaign_product->price;
										$priceperselectedDates = $priceperday;
										
										
										$newpricepercentage = ($priceperselectedDates/$camptot) * 100;
										
										$newofferprice = ($offerprice * ($newpricepercentage))/100;
										if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
											$newofferprice = $newofferprice*$campaign_product->quantity;
										}
										$price = $newofferprice;
									}
								}else{
									$priceperselectedDates = $campaign_product->price;
									if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
										$price = $campaign_product->price*$campaign_product->quantity;
									}else{
										$price = $campaign_product->price;
									}
								}
								if($total_paid != 0){
									if(isset($campaign_product->tax_percentage)){
										$tax_percentage_booking = $campaign_product->tax_percentage;
									}else{
										$tax_percentage_booking = 0;
									}
								}else{
									$tax_percentage_booking = $getproductDetails->tax_percentage;
								}
								if($tax_percentage_booking != 0){
									$tax_percentage_amount = ($price * ($tax_percentage_booking))/100;
								}
								$user_campaign->tax_percentage_amount = round($tax_percentage_amount,2);
								
								$offershortlistedsum+= $price+$user_campaign->tax_percentage_amount;
							}
						}
						$total_price = $offershortlistedsum;
					}
				}
				
				$gross_fee_price = 0;
				if(isset($user_campaign->gross_fee_percentage) && $user_campaign->gross_fee_percentage > 0){
					$gross_fee_price = ($offershortlistedsum*$user_campaign->gross_fee_percentage)/100;
				}else{
					$gross_fee_price = 0;
				}
				
				
                $count = ProductBooking::where('campaign_id', '=', $campaign->id)->count();
                $admin_campaigns[$i]->total_paid = $total_paid;
                $admin_campaigns[$i]->total_price = $total_price+$gross_fee_price;
                $admin_campaigns[$i]->product_count = $count;
                $product_start_date = ProductBooking::where('campaign_id', '=', $campaign->id)->select("booked_from","booked_to")->orderBy('booked_from', 'asc')->first();
                if (!empty($product_start_date)) {
                    $admin_campaigns[$i]->start_date = $product_start_date->booked_from;
                    $admin_campaigns[$i]->end_date = $product_start_date->booked_to;
                    $diff=date_diff(date_create($current_date),date_create($admin_campaigns[$i]->start_date));
                    if($diff->days <=7 ){
                        $admin_campaigns[$i]->colorcode = 'red';
                    }else{
                        $admin_campaigns[$i]->colorcode = 'black';
                    }
                }
                ++$i;
            }
        }
        // $campaign_requests_result = [];
        // foreach($campaign_requests as $campaign_request){
        //  $user_mongo_id = $campaign_request->user_mongo_id;
        //  $user_mongo = UserMongo::where('id', '=', $user_mongo_id)->first();
        //  $user = User::where('id', '=', $user_mongo->user_id)->first();
        //  // Figuring out who created it
        //  if($user->hasRole('admin') or $user->hasRole('owner')){
        //      $campaign_request['created_by'] = 'AD';
        //  }
        //  else if($user->hasRole('agency')){
        //      $campaign_request['created_by'] = 'AG';
        //  }
        //  else if($user->hasRole('billboards-owner')){
        //      $campaign_request['created_by'] = 'BO';
        //  }
        //  else{
        //      $campaign_request['created_by'] = 'U';
        //  }
        //  // Giving the campaigns which don't have a name, one.
        //  if(!isset($campaign_request->name)){
        //      $campaign_request['name'] = 'N/A';
        //  }
        //  // If Estimated budget doesn't exist
        //  if(!isset($campaign_request->est_budget) or empty($campaign_request->est_budget)){
        //      $campaign_request['est_budget'] = 'N/A';
        //  }
        //  // If no products are added yet
        //  if(!isset($campaign_request->products) or empty($campaign_request->products)){
        //      $campaign_request['products'] = [];
        //  }
        //  array_push($campaign_requests_result, $campaign_request);
        // }
        $campaigns = [
            'user_campaigns' => array_values($user_campaigns_arr),
            'admin_campaigns' => $admin_campaigns
        ];
        return response()->json($campaigns);
    }

    /*
     * Export all campaigns
     */

    public function exportAllCampaigns() {
        try {
            $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
            $all_user_campaigns = Campaign::where([
                        ['created_by', '=', $user_mongo['id']],
                        ['status', '>=', Campaign::$CAMPAIGN_STATUS['campaign-preparing']]
                    ])->get();
            $all_campaign_report = [];
            foreach ($all_user_campaigns as $campaign) {
                $campaign_products = ProductBooking::where('campaign_id', '=', $campaign->id)->get();
                $products_in_campaign = [];
                if (count($campaign_products) > 0) {
                    $product_ids_in_campaign = [];
                    $formats = 0;
                    $areas = 0;
                    $audience_reach = 0;
                    $repeated_audience = 0;
                    foreach ($campaign_products as $c_product) {
                        array_push($product_ids_in_campaign, $c_product->product_id);
                    }
                    $products_in_campaign = Product::whereIn('id', $product_ids_in_campaign)->get();
                    $formats = $products_in_campaign->unique('type')->count();
                    $areas = $products_in_campaign->unique('area')->count();
                    $audience_reach = $products_in_campaign->each(function($v, $k) {
                                $v->impressions = intval(str_replace(",", "", $v->impressions));
                            })->sum('impressions');
                    $repeated_audience = $audience_reach * 30 / 100;
                }
                $product_count = isset($campaign_products) ? count($products_in_campaign) : 0;
                $campaign_report = [
                    'campaign' => $campaign,
                    'areas_covered' => $areas,
                    'format_types' => $formats,
                    'mediums_covered' => $product_count,
                    'audience_reach' => $audience_reach,
                    'repeated_audience' => $repeated_audience,
                    'products' => $products_in_campaign
                ];
                array_push($all_campaign_report, $campaign_report);
            }
            //  Log::info(print_r($all_campaign_report, true));
            $pdf = PDF::loadView('pdf.export_all_campaigns_pdf', ['all_campaign_report' => $all_campaign_report]);
            // $mail_tmpl_params = [
            //  'sender_email' => $user['email'], 
            //  'receiver_name' => $this->input['receiver_name']
            // ];
            // $mail_data = [
            //  'email_to' => $this->input['email'],
            //  'recipient_name' => $this->input['receiver_name'],
            //  'pdf_file_name' => "Campaign_" . $campaign->user_mongo_id . "_" . date('d-m-Y') . ".pdf",
            //  'pdf' => $pdf
            // ];
            // Mail::send('mail.campaign_details', $mail_tmpl_params, function($message) use ($mail_data){
            //  $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('A campaign has been shared to you!');
            //  $message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
            // });
            if (!empty($pdf)) {
                return $pdf->download("campaigns.pdf");
            } else {
                return response()->json(['status' => 0, 'message' => "There was an error sending the email. Please try again."]);
            }
        } catch (Exception $ex) {
            return response()->json(['status' => 0, 'message' => "There was an error generating the campaign report."]);
        }
    }

    public function editProposedProductForCampaign($campaign_id) {
        $this->validate($this->request, [
            'booking_id' => 'required',
            'price' => 'required'
                ], [
            'booking_id.required' => 'Booking id is required',
            'price.required' => 'Price is required'
                ]
        );
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        if ($user_mongo['user_type'] == "bbi") {
            // can set up dates/prices for all products
            $product_booking = ProductBooking::where([
                        ['campaign_id', '=', $campaign_id],
                        ['id', '=', $this->input['booking_id']],
                    ])->first();
            if (!isset($product_booking) || empty($product_booking)) {
                return response()->json(['status' => 0, 'message' => 'Product not found in this campaign.']);
            }
        } else {
            // can set up dates/prices for his own products only.
            $product_booking = ProductBooking::where([
                        ['campaign_id', '=', $campaign_id],
                        ['id', '=', $this->input['booking_id']],
                        ['product_owner', '=', $user_mongo['client_mongo_id']]
                    ])->first();
            if (!isset($product_booking) || empty($product_booking)) {
                return response()->json(['status' => 0, 'message' => 'You are not authorized to provide quote for this product.']);
            }
        }
        $campaign = Campaign::Where([
                    ['id', '=', $campaign_id],
                    ['status', '=', Campaign::$CAMPAIGN_STATUS['campaign-preparing']]
                ])->orWhere([
                    ['id', '=', $campaign_id],
                    ['status', '=', Campaign::$CAMPAIGN_STATUS['quote-requested']]
                ])->orWhere([
                    ['id', '=', $campaign_id],
                    ['status', '=', Campaign::$CAMPAIGN_STATUS['change-requested']]
                ])->first();
        if (!isset($campaign) || empty($campaign)) {
            return response()->json(['status' => 0, 'message' => "The campaign you referred to, either does not exist or is not in a state where you can quote a product in it."]);
        }
        $product_booking->default_price = (int) $this->input['default_price'];
        if ($product_booking->save()) {
            if ($user_mongo['user_type'] == "owner") {
                $noti_array = [
                    'type' => Notification::$NOTIFICATION_TYPE['campaign-quote-provided'],
                    'from_id' => null,
                    'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
                    'to_id' => null,
                    'to_client' => null,
                    'desc' => "Quote From owner",
                    'message' => "Owner has quoted a product in campaign " . $campaign->name,
                    'data' => ["campaign_id" => $campaign->id]
                ];
                event(new CampaignQuoteProvidedEvent($noti_array, $mail_array));

                $notification_obj = new Notification;
                $notification_obj->id = uniqid();
                $notification_obj->type = "campaign";
                $notification_obj->from_id = $user_mongo['id'];
                $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['bbi'];
                $notification_obj->to_id = null;
                $notification_obj->to_client = null;
                $notification_obj->desc = "Quote From owner";
                $notification_obj->message ="Owner has quoted a product in campaign " . $campaign->name;
                $notification_obj->campaign_id = $campaign->id;
                $notification_obj->status = 0;
                $notification_obj->save();
            }
            return response()->json(['status' => 1, 'message' => 'Product quote saved.']);
        } else {
            return response()->json(['status' => 0, 'message' => 'There was a problem while saving product quote. Please try again.']);
        }
    }

    public function requestCampaignProposal($campaign_id) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        if ($user_mongo['user_type'] != 'basic') {
            return response()->json(['status' => 0, 'message' => "You can not request a quote."]);
        }
        $campaign_obj = Campaign::where([
                    ['created_by', '=', $user_mongo['id']],
                    ['id', '=', $campaign_id]
                ])->first();
        if ($campaign_obj->status >= Campaign::$CAMPAIGN_STATUS['quote-requested']) {
            return response()->json(['status' => 0, 'message' => 'You can not request a quote now.']);
        }
        $campaign_products = ProductBooking::where([
                    ['campaign_id', '=', $campaign_id],
                ])->get();
        if (count($campaign_products) <= 0) {
            return response()->json(["status" => "0", "message" => "Add some products in the campaign first."]);
        }
        $campaign_obj->status = Campaign::$CAMPAIGN_STATUS['quote-requested'];
        if ($campaign_obj->save()) {

            $bbi_sa_id = Client::where('company_slug', '=', 'bbi')->first()->super_admin;
            $bbi_sa = UserMongo::where('user_id', '=', $bbi_sa_id)->first();
            $noti_array = [
                'type' => Notification::$NOTIFICATION_TYPE['campaign-quote-requested'],
                'from_id' => $user_mongo['id'],
                'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
                'to_id' => null,
                'to_client' => null,
                'desc' => "Quote for a campaign requested",
                'message' => $user_mongo['first_name'] . " " . $user_mongo['last_name'] . " has requested quote for campaign .",
                'data' => ["campaign_id" => $campaign_obj->id]
            ];
            $mail_array = [
                'mail_tmpl_params' => ['sender_email' => $user_mongo['email'],
                    'receiver_name' => $bbi_sa->first_name . ' ' . $bbi_sa->last_name,
                    'mail_message' => $user_mongo['first_name'] . ' ' . $user_mongo['last_name'] . 'has requested a quote for a campaign.'],
                'email_to' => $bbi_sa->email,
                'recipient_name' => $bbi_sa->first_name,
                //'subject' => 'A user has requested a quote for their campaign - Billboards India'
                'subject' => 'A user has requested a quote for their campaign - Advertising Marketplace'
            ];
            event(new CampaignQuoteRequestedEvent($noti_array, $mail_array));
            $notification_obj = new Notification;
            $notification_obj->id = uniqid();
            $notification_obj->type = "campaign";
            $notification_obj->from_id = $user_mongo['id'];
            $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['bbi'];
            $notification_obj->to_id = null;
            $notification_obj->to_client = null;
            $notification_obj->desc = "Quote for a campaign requested";
            $notification_obj->message = $user_mongo['first_name'] . " " . $user_mongo['last_name'] . " has requested quote for campaign .";
            $notification_obj->campaign_id = $campaign_obj->id;
            $notification_obj->status = 0;
            $notification_obj->save();
            $campaign_product_owner_ids = ProductBooking::raw(function($collection) use ($campaign_id) {
                        return $collection->distinct('product_owner', ['campaign_id' => $campaign_id]);
                    });
            $owner_notif_recipients = ClientMongo::whereIn('id', $campaign_product_owner_ids)->get();
            $owner_sa_ids = [];
            foreach ($owner_notif_recipients as $owner_notif_recipient) {
                if (isset($owner_notif_recipient->super_admin_m_id) && !empty($owner_notif_recipient->super_admin_m_id)) {
                    array_push($owner_sa_ids, $owner_notif_recipient->super_admin_m_id);
                }
            }
            $owner_sa_emails = UserMongo::whereIn('id', $owner_sa_ids)->pluck('email');
            $noti_array1 = [
                'type' => Notification::$NOTIFICATION_TYPE['campaign-quote-requested'],
                'from_id' => null,
                'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['owner'],
                'to_id' => null,
                'to_client' => $campaign_product_owner_ids,
                'desc' => "Quote for a campaign requested",
                //'message' => "BBI has requested quote for campaign ",
                'message' => "AMP has requested quote for campaign ",
                'data' => ["campaign_id" => $campaign_obj->id]
            ];
            $mail_array1 = [
                'mail_tmpl_params' => ['sender_email' => config('app.bbi_email'),
                    'receiver_name' => "",
                    //'mail_message' => 'You have received a quote request from Billboards India.'],
                    'mail_message' => 'You have received a quote request from Advertising Marketplace.'],
                'bcc' => $owner_sa_emails->toArray(),
                //'subject' => 'A user has requested a quote for their campaign - Billboards India'
                'subject' => 'A user has requested a quote for their campaign - Advertising Marketplace'
            ];
            event(new CampaignQuoteRequestedEvent($noti_array1, $mail_array1));
            foreach ($campaign_product_owner_ids as $key => $val) {
                $notification_obj = new Notification;
                $notification_obj->id = uniqid();
                $notification_obj->type = "campaign";
                $notification_obj->from_id = null;
                $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['owner'];
                $notification_obj->to_id = $val;
                $notification_obj->to_client = $val;
                $notification_obj->desc = "Quote for a campaign requested";
                //$notification_obj->message = "BBI has requested quote for campaign ";
                $notification_obj->message = "AMP has requested quote for campaign ";
                $notification_obj->campaign_id = $campaign_obj->id;
                $notification_obj->status = 0;
                $notification_obj->save();
            }
            return response()->json(["status" => "1", "message" => "Successfully sent a request for quote."]);
        } else {
            return response()->json(["status" => "0", "message" => "There was an error in sending the request."]);
        }
    }

    public function quoteCampaign($campaign_id = '', $flag='', $gst = '') {
        $campaign_obj = Campaign::where([
                    ['id', '=', $campaign_id]
                ])->first();
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo['user_id'])->first();
        // check if all products have a price and start/end date in this campaign.
        $bookings = ProductBooking::where([['campaign_id', '=', $campaign_id]])->get();
        if (isset($bookings) && !empty($bookings)) {
            /* $error = 0;
              foreach ($bookings as $booking) {
              $error = !empty($booking->admin_price) ? $error : $error + 1;
              }
              if ($error > 0) {
              return response()->json(["status" => "0", "message" => "One or more products are quoted incompletely. Please check again."]);
              } */
            foreach ($bookings as $booking) {
                // $error = 0;
                if (!$booking->admin_price) {
                    // $error = 1;
                    $product = Product::select('default_price', 'siteNo')->where('id', $booking->product_id)->first();
                    if ($booking->owner_price) {
                        $price = $booking->owner_price;
                    } else {
                        $price = $product->default_price;
                    }
                    if ($price) {
                        $previous_quote_change = CampaignQuoteChange::where('campaign_id', '=', $campaign_id)->orderBy('iteration', 'desc')->first();
                        $quote_change_obj = new CampaignQuoteChange;
                        if (isset($previous_quote_change) && !empty($previous_quote_change))
                            $quote_change_obj->iteration = $previous_quote_change->iteration + 1;
                        else
                            $quote_change_obj->iteration = 1;
                        $quote_change_obj->campaign_id = $campaign_id;
                        $quote_change_obj->remark = 'BBI has give price Rs. ' . $price . ' for ' . $product->siteNo;
                        $quote_change_obj->type = 'bbi';
                        if ($quote_change_obj->save()) {
                            $campaign_product = ProductBooking::where('campaign_id', '=', $campaign_id)->where('product_id', '=', $booking->product_id)->first();
                            $campaign_product->admin_price = (int) $price;
                            $campaign_product->save();
                        }
                    }
                }
            }
        } else {
            return response()->json(["status" => "0", "message" => "Can not send a quote for a campaign which doesn't have products in it."]);
        }
        $campaign_obj->status = Campaign::$CAMPAIGN_STATUS['quote-given'];
        $campaign_obj->gststatus = $flag;
        $campaign_obj->gst_price = $gst;
        $campaign_obj->save();
        if ($user->client->client_type->type == "bbi") {
            if ($campaign_obj->save()) {

                $campaign_user_mongo = UserMongo::where('id', '=', $campaign_obj->created_by)->first();
                $noti_array = [
                    'type' => Notification::$NOTIFICATION_TYPE['campaign-quote-provided'],
                    'from_id' => null,
                    'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['user'],
                    'to_id' => $campaign_obj->created_by,
                    'to_client' => $campaign_obj->created_by,
                    'desc' => "Quote received",
                    'message' => "You have received a quote for your campaign",
                    'data' => ["campaign_id" => $campaign_obj->id]
                ];
                $mail_array = [
                    'mail_tmpl_params' => [
                        'sender_email' => $user_mongo['email'],
                        'receiver_name' => $campaign_user_mongo->first_name,
                        'mail_message' => "You have received the quote for a campaign that you requested. Please visit the website and login to see the details of campaign and quote provided."
                    ],
                    'email_to' => $campaign_user_mongo->email,
                    'recipient_name' => $campaign_user_mongo->first_name,
                    //'subject' => 'Our quote for your campaign - Billboards India'
                    'subject' => 'Our quote for your campaign - Advertising Marketplace'
                ];

                $notification_obj = new Notification;
                $notification_obj->id = uniqid();
                $notification_obj->type = "campaign";
                $notification_obj->from_id = null;
                $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['user'];
                $notification_obj->to_id = $campaign_obj->created_by;
                $notification_obj->to_client = $campaign_obj->created_by;
                $notification_obj->desc = "Quote received";
                $notification_obj->message = "You have received a quote for your campaign";
                $notification_obj->campaign_id = $campaign_obj->id;
                $notification_obj->status = 0;
                $notification_obj->save();
                event(new CampaignQuoteProvidedEvent($noti_array, $mail_array));
                return response()->json(["status" => "1", "message" => "Quote has been sent to the user."]);
            } else {
                return response()->json(["status" => "0", "message" => "There was an error in udpating campaign status."]);
            }
        } else if ($client_type == "owner") {
            //return response()->json(["status" => "1", "message" => "Your quote has been received by BillBoards India."]);
            return response()->json(["status" => "1", "message" => "Your quote has been received by Advertising Marketplace."]);
        } else {
            return response()->json(['status' => 0, 'message' => "You are not authorized to quote this campaign."]);
        }
    }

    public function requestCampaignBooking($campaign_id) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        if ($user_mongo['user_type'] != 'basic') {
            return response()->json(['status' => 0, 'message' => "You can not request campaign launch."]);
        }
        $campaign_obj = Campaign::where([
                    ['id', '=', $campaign_id],
                    ['created_by', '=', $user_mongo['id']]
                ])->first();
        if ($campaign_obj->status != Campaign::$CAMPAIGN_STATUS['quote-given']) {
            return response()->json(['status' => 0, 'message' => "Please request a quote for the campaign first. Or if you have, please wait for the admin to provide you with one."]);
        }
        

        
        $campaign_obj->status = Campaign::$CAMPAIGN_STATUS['booking-requested'];
        if ($campaign_obj->save()) {
          
            $bbi_sa_id = Client::where('company_slug', '=', 'bbi')->first()->super_admin;
            $bbi_sa = UserMongo::where('user_id', '=', $bbi_sa_id)->first();

            $noti_array = [
                'type' => Notification::$NOTIFICATION_TYPE['campaign-launch-requested'],
                'from_id' => $user_mongo['id'],
                'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
                'to_id' => null,
                'to_client' => null,
                'desc' => "Campaign launch request",
                'message' => $user_mongo['first_name'] . ' ' . $user_mongo['last_name'] . " has requested campaign launch.",
                'data' => ["campaign_id" => $campaign_obj->id]
            ];
            $mail_array = [
                'mail_tmpl_params' => [
                    'sender_email' => $user_mongo['email'],
                    'receiver_name' => $bbi_sa->first_name . ' ' . $bbi_sa->last_name,
                    'mail_message' => $user_mongo['first_name'] . ' ' . $user_mongo['last_name'] . 'has requested the launch of their campaign.'
                ],
                'email_to' => $bbi_sa->email,
                'recipient_name' => $bbi_sa->first_name,
                'subject' => 'New campaign launch request'
            ];
            event(new CampaignLaunchRequestedEvent($noti_array, $mail_array));
            
                 $notification_obj = new Notification;
                 $notification_obj->id = uniqid();
                $notification_obj->type = "campaign";
                $notification_obj->from_id = $user_mongo['id'];
                $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['bbi'];
                $notification_obj->to_id = null;
                $notification_obj->to_client = null;
                $notification_obj->desc = "Campaign launch request";
                $notification_obj->message = $user_mongo['first_name'] . ' ' . $user_mongo['last_name'] . " has requested a quote for Campaign";
                $notification_obj->campaign_id = $campaign_obj->id;
                $notification_obj->status = 0;
                $notification_obj->save();

            return response()->json(["status" => "1", "message" => "Campaign launch request sent successfully."]);
        } else {
            return response()->json(["status" => "0", "message" => "There was a technical error while sending the launch request. Please try again later."]);
        }
    }

    public function requestChangeQuote() {
        $this->validate($this->request, [
            'for_campaign_id' => 'required',
            'remark' => 'required'
                ], [
            'for_campaign_id.required' => 'Campaign id is required',
            'remark.required' => 'Remark is required'
                ]
        );
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];

        $campaign = Campaign::Where([
                    ['id', '=', $this->input['for_campaign_id']],
                        //['status', '=', Campaign::$CAMPAIGN_STATUS['quote-given']]
                ])->first();
        // print_r($campaign);
        $previous_quote_change = CampaignQuoteChange::where([
                    ['campaign_id', '=', $this->input['for_campaign_id']]
                ])->orderBy('iteration', 'desc')->first();
        $quote_change_obj = new CampaignQuoteChange;
        if (isset($previous_quote_change) && !empty($previous_quote_change)) {
            $quote_change_obj->iteration = $previous_quote_change->iteration + 1;
        } else {
            $quote_change_obj->iteration = 1;
        }
        $quote_change_obj->campaign_id = $this->input['for_campaign_id'];
        $quote_change_obj->remark = $this->input['remark'];
        $quote_change_obj->type = $this->input['type'];
        if ($quote_change_obj->save()) {
            return response()->json(["status" => "1", "message" => "Request for a change in quote sent successfully."]);
            /* if ($user_mongo['user_type'] != 'basic') {
              $campaign->status = Campaign::$CAMPAIGN_STATUS['change-requested'];
              } */

            //  Log::info(print_r($campaign, true));
            //if ($campaign->save()) {
            // print_r($campaign);
            /* NotificationHelper::createNotification([
              'type' => Notification::$NOTIFICATION_TYPE['campaign-quote-revision'],
              'from_id' => $user_mongo['id'],
              'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
              'to_id' => null,
              'to_client' => null,
              'desc' => "Quote revision",
              'message' => $user_mongo['first_name'] . ' ' . $user_mongo['last_name'] . " has requested a revision in quote provided for their campaign.",
              'data' => ["campaign_id" => $campaign->id]
              //'data' => ""
              ]); */

            /* $bbi_sa_id = Client::where('company_slug', '=', 'bbi')->first()->super_admin;
              $bbi_sa = UserMongo::where('user_id', '=', $bbi_sa_id)->first();
              $noti_array = [
              'type' => Notification::$NOTIFICATION_TYPE['campaign-quote-revision'],
              'from_id' => $user_mongo['id'],
              'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
              'to_id' => null,
              'to_client' => null,
              'desc' => "Quote revision",
              'message' => $user_mongo['first_name'] . ' ' . $user_mongo['last_name'] . " has requested a revision in quote provided for their campaign.",
              'data' => ["campaign_id" => $campaign->id]
              //'data' => ""
              ];
              $mail_array = [
              'mail_tmpl_params' => ['sender_email' => $user_mongo['email'],
              'receiver_name' => $bbi_sa->first_name . ' ' . $bbi_sa->last_name,
              'mail_message' => $user_mongo['first_name'] . ' ' . $user_mongo['last_name'] . 'has requested revision of quote for their campaign.'
              ],
              'email_to' => $bbi_sa->email,
              'recipient_name' => $bbi_sa->first_name,
              'subject' => 'Quote revision requested'
              ];

              event(new CampaignQuoteRevisionEvent($noti_array, $mail_array)); */
            // return response()->json(["status" => "1", "message" => "Request for a change in quote sent successfully."]);
            //} else {
            //   $quote_change_obj->delete();
            //  return response()->json(["status" => "0", "message" => "There was a technical error while sending the quote change request."]);
            // }
        } else {
            return response()->json(["status" => "0", "message" => "There was a technical error while sending the quote change request."]);
        }
    }

// public function confirmCampaignBooking($campaign_id,$flag,$gst)
    public function confirmCampaignBooking($campaign_id) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo['user_id'])->first();
        $campaign_obj = Campaign::where('id', '=', $campaign_id)->first();
        //$campaign_obj->gststatus = $flag;
        //$campaign_obj->gst_price = $gst;
        if (!isset($user) || empty($user)) {
            return response()->json(['status' => 0, 'message' => 'You are not authorized to do this operation.']);
        }
        $product_bookings = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
        if (count($product_bookings) == 0) {
            return response()->json(['status' => 0, 'message' => "Please add some products first."]);
        } else {
            $error = 0;
         
        }
		
		$campaign_payments_book = CampaignPayment::where([
				['campaign_id', '=', $campaign_id],
			])->get();
		if (count($campaign_payments_book) == 0) {
            return response()->json(['status' => 0, 'message' => "Payment is not done for this campaign. hence we cannot proceed. please try again."]);
        } else {
            $error = 0;
        }	
			
        
        $productIds = ProductBooking::where([['campaign_id', '=',  $campaign_id]])->get();

foreach($productIds as $productId){
    $product_occurances = ProductBooking::where([['product_id', '=',  $productId['product_id']],
        ['product_status', '>=',  ProductBooking::$PRODUCT_STATUS['scheduled']]])->get();
    $overlapping_dates = [];
    $dr = [];
    $productData = Product::where('id', '=', $productId['product_id'])->first();
    if($productData->type=='Bulletin'){
            foreach ($product_occurances as $po) {
                if (strtotime($po->booked_from) <= strtotime($productId['booked_to']) && strtotime($po->booked_to) >= strtotime($productId['booked_from'])) {
                    array_push($overlapping_dates, $dr);
                }

            }
    }

    
}
//dd($overlapping_dates);
if(count($overlapping_dates)>0){
    return response()->json(["status" => "0", "message" => "The shortlisted Products are not available in selected dates."]);
}
//dd(123);
        $unavailable_bookings = [];
        foreach ($product_bookings as $booking) {

            if (isset($booking->booked_to) && isset($booking->booked_from)) {
                $conflicting_bookings = ProductBooking::where([
                            ['product_id', '=', $booking->product_id],
                            ['booked_from', '<=', $booking->booked_to],
                            ['booked_to', '>=', $booking->booked_from],
                            // ['admin_price', '>=', $booking->admin_price],
                            ['product_status', '=', ProductBooking::$PRODUCT_STATUS['scheduled']]
                        ])->get();
                if (count($conflicting_bookings) > 0) {
                    array_push($unavailable_bookings, $booking->id);
                }
            }
        }

        if ($campaign_obj->type == Campaign::$CAMPAIGN_USER_TYPE['user']) {
            if (count($unavailable_bookings) > 0) {
                $campaign_obj->status = Campaign::$CAMPAIGN_STATUS['quote-requested'];
                
                if ($campaign_obj->save()) {

                    return response()->json(['status' => 0, 'message' => 'Some products from this campaign are already being used in other campaigns.', 'product_ids' => $unavailable_bookings]);
                } else {
                    return response()->json(['status' => 0, 'message' => 'Some products from this campaign are already being used in other campaigns. Campaign could not be reset.', 'product_ids' => $unavailable_bookings]);
                }
            }
            $campaign_obj->status = Campaign::$CAMPAIGN_STATUS['booking-requested'];
            if ($campaign_obj->save()) {
                foreach ($product_bookings as $booking) {
                    $check_prooduct = ProductBooking::where([
                                ['product_id', '=', $booking->product_id],
                                ['campaign_id', '>=', $booking->campaign_id],
                            ])->first();
                    // print_r($check_prooduct);
                    if (!$check_prooduct->admin_price) {
                       // $check_prooduct->delete();
                    }
                }
               

                // send the email to user.
                $campaign_user_mongo = UserMongo::where('id', '=', $campaign_obj->created_by)->first();
                
                // notifications and emails for user start 
                    event(new CampaignLaunchEvent([
                      'type' => Notification::$NOTIFICATION_TYPE['campaign-launched'],
                        'from_id' => null,
                        'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['user'],
                        'to_id' => $campaign_obj->created_by,
                        'to_client' => $campaign_obj->created_by,
                        'c_id' => $campaign_obj->cid,
                        'c_name' => $campaign_obj->name,
                        //'desc' => "Campaign launched",
                        'desc' => "Campaign confirmed",
                        //'message' => "Your Campaign has been launched!",
                        'message' => "Your Campaign has been confirmed!",
                        'data' => ["campaign_id" => $campaign_obj->id]
                    ]));
                    $notification_obj = new Notification;
                    $notification_obj->id = uniqid();
                    $notification_obj->type = "campaign";
                    $notification_obj->from_id = null;
                    $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['user'];
                    $notification_obj->to_id = $campaign_obj->created_by;
                    $notification_obj->to_client = $campaign_obj->created_by;
                    $notification_obj->c_id = $campaign_obj->cid;
                    $notification_obj->c_name = $campaign_obj->name;
                    //$notification_obj->desc = "Campaign launched";
                    $notification_obj->desc = "Campaign confirmed";
                    //$notification_obj->message = "Your Campaign has been launched!";
                    $notification_obj->message = "Your Campaign has been Confirmed!";
                    $notification_obj->campaign_id = $campaign_obj->id;
                    $notification_obj->status = 0;
                    $notification_obj->save();
  
                    //$pdf = PDF::loadView('pdf.launch_campaign_details_pdf');
                    $mail_tmpl_params = [
                      'sender_email' => $user['email'], 
                      //'receiver_name' => $bbi_sa->first_name . ' ' . $bbi_sa->last_name,
                      'receiver_name' => $campaign_user_mongo->first_name,
                      'mail_message' => "Your campaign '" . $campaign_obj->name . "' has been confirmed. Visit our website to see details."
                    ];
                    $mail_data = [
                      //'email_to' => $bbi_sa->email,
                      'email_to' => $campaign_user_mongo->email,
                      //'email_to1' => 'sandhyarani.manelli@peopletech.com',
                      'email_to1' => 'admin@advertisingmarketplace.com',
                      'recipient_name1' => 'Richard',
                      'recipient_name' => $campaign_user_mongo->first_name
                      //'pdf_file_name' => "Insertion Order". date('d-m-Y') . ".pdf",
                      //'pdf' => $pdf
                    ];
                    Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data){
                     // $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Super admin set up for company - Billboards India');
                      $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Campaign confirmed! - Advertising Marketplace');
                      $message->cc($mail_data['email_to1'], $mail_data['recipient_name1'])->subject('Campaign confirmed! - Advertising Marketplace');
                      //$message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
                    });
                    
                    // notifications and emails for user end
                
                
                // notifications and emails for owner
                
               /* $campaign_product_owner_ids = ProductBooking::raw(function($collection) use ($campaign_id) {
                            return $collection->distinct('product_owner', ['campaign_id' => $campaign_id]);
                        });
                $owner_notif_recipients = ClientMongo::whereIn('id', $campaign_product_owner_ids)->get();
                $owner_sa_ids = [];
                foreach ($owner_notif_recipients as $owner_notif_recipient) {
                    if (isset($owner_notif_recipient->super_admin_m_id) && !empty($owner_notif_recipient->super_admin_m_id)) {
                        array_push($owner_sa_ids, $owner_notif_recipient->super_admin_m_id);
                    }
                }
                $owner_sa_emails = UserMongo::whereIn('id', $owner_sa_ids)->pluck('email');

                $noti_array = [
                    'type' => Notification::$NOTIFICATION_TYPE['campaign-launched'],
                    'from_id' => null,
                    'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['owner'],
                    'to_id' => null,
                    'to_client' => $campaign_product_owner_ids,
                    'desc' => "Campaign launched",
                    'message' => "A campaign with your products in it has been launched.",
                    'data' => ["campaign_id" => $campaign_obj->id]
                ];
                $mail_array = [
                    'mail_tmpl_params' => [
                        'sender_email' => config('app.bbi_email'),
                        'receiver_name' => "",
                        'mail_message' => 'Campaign ' . $campaign_obj->name . 'has been launched.'
                    ],
                    'bcc' => $owner_sa_emails->toArray(),
                    'subject' => 'User campaign launched! - Billboards India'
                ];

                event(new CampaignLaunchEvent($noti_array, $mail_array));
                foreach ($campaign_product_owner_ids as $key => $val) {
                    $notification_obj = new Notification;
                    $notification_obj->id = uniqid();
                    $notification_obj->type = "campaign";
                    $notification_obj->from_id = null;
                    $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['owner'];
                    $notification_obj->to_id = $val;
                    $notification_obj->to_client = $val;
                    $notification_obj->desc = "Campaign launched";
                    $notification_obj->message = "A campaign with your products in it has been launched.";
                    $notification_obj->campaign_id = $campaign_obj->id;
                    $notification_obj->status = 0;
                    $notification_obj->save();
                }*/
                // }
                // send email
                return response()->json(["status" => "1", "message" => "Campaign confirmed successfully."]);
            } else {
                return response()->json(["status" => 0, "message" => "There was a technical error while launching your campaign."]);
            }
        }
        //print_r($user);
        if (isset($user->client)) {
            if ($user->client->client_type->type == "bbi") {
                echo "fdsfdgddddd";
                if ($campaign_obj->type == Campaign::$CAMPAIGN_USER_TYPE['user'] && $campaign_obj->status == Campaign::$CAMPAIGN_STATUS['booking-requested']) {
                    if (count($unavailable_bookings) > 0) {
                        $campaign_obj->status = Campaign::$CAMPAIGN_STATUS['quote-requested'];
                        if ($campaign_obj->save()) {
                            return response()->json(['status' => 0, 'message' => 'Some products from this campaign are already being used in other campaigns.', 'product_ids' => $unavailable_bookings]);
                        } else {
                            return response()->json(['status' => 0, 'message' => 'Some products from this campaign are already being used in other campaigns. Campaign could not be reset.', 'product_ids' => $unavailable_bookings]);
                        }
                    }
                    $campaign_obj->status = Campaign::$CAMPAIGN_STATUS['scheduled'];
                    if ($campaign_obj->save()) {
                        // camapign launch successful. lock the products.
                        $product_bookings = ProductBooking::where('campaign_id', '=', $campaign_obj->id)->get();
                        foreach ($product_bookings as $product_booking) {
                            $product_booking->product_status = ProductBooking::$PRODUCT_STATUS['scheduled'];
                            $product_booking->save();
                        }

                        // send the email to user.
                        $campaign_user_mongo = UserMongo::where('id', '=', $campaign_obj->created_by)->first();
                        
                        // notifications and emails for user start 
                    event(new CampaignLaunchEvent([
                      'type' => Notification::$NOTIFICATION_TYPE['campaign-launched'],
                        'from_id' => null,
                        'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['user'],
                        'to_id' => $campaign_obj->created_by,
                        'to_client' => $campaign_obj->created_by,
                        'c_id' => $campaign_obj->cid,
                        'c_name' => $campaign_obj->name,
                        //'desc' => "Campaign launched",
                        'desc' => "Campaign confirmed",
                        //'message' => "Your Campaign has been launched!",
                        'message' => "Your Campaign has been confirmed!",
                        'data' => ["campaign_id" => $campaign_obj->id]
                    ]));
                    $notification_obj = new Notification;
                    $notification_obj->id = uniqid();
                    $notification_obj->type = "campaign";
                    $notification_obj->from_id = null;
                    $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['user'];
                    $notification_obj->to_id = $campaign_obj->created_by;
                    $notification_obj->to_client = $campaign_obj->created_by;
                    $notification_obj->c_id = $campaign_obj->cid;
                    $notification_obj->c_name = $campaign_obj->name;
                    //$notification_obj->desc = "Campaign launched";
                    $notification_obj->desc = "Campaign confirmed";
                    //$notification_obj->message = "Your Campaign has been launched!";
                    $notification_obj->message = "Your Campaign has been Confirmed!";
                    $notification_obj->campaign_id = $campaign_obj->id;
                    $notification_obj->status = 0;
                    $notification_obj->save();
  
                    //$pdf = PDF::loadView('pdf.launch_campaign_details_pdf');
                    $mail_tmpl_params = [
                      'sender_email' => $user['email'], 
                      //'receiver_name' => $bbi_sa->first_name . ' ' . $bbi_sa->last_name,
                      'receiver_name' => $campaign_user_mongo->first_name,
                      'mail_message' => "Your campaign '" . $campaign_obj->name . "' has been confirmed. Visit our website to see details."
                    ];
                    $mail_data = [
                      //'email_to' => $bbi_sa->email,
                      'email_to' => $campaign_user_mongo->email,
                      //'email_to1' => 'sandhyarani.manelli@peopletech.com',
                      'email_to1' => 'admin@advertisingmarketplace.com',
                      'recipient_name1' => 'Richard',
                      'recipient_name' => $campaign_user_mongo->first_name
                      //'pdf_file_name' => "Insertion Order". date('d-m-Y') . ".pdf",
                      //'pdf' => $pdf
                    ];
                    Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data){
                     // $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Super admin set up for company - Billboards India');
                      $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Campaign confirmed! - Advertising Marketplace');
                      $message->cc($mail_data['email_to1'], $mail_data['recipient_name1'])->subject('Campaign confirmed! - Advertising Marketplace');
                      //$message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
                    });
                    
                    // notifications and emails for user end
                        
                        // notifications and emails for owner
                        $campaign_product_owner_ids = ProductBooking::raw(function($collection) use ($campaign_id) {
                                    return $collection->distinct('product_owner', ['campaign_id' => $campaign_id]);
                                });
                        $owner_notif_recipients = ClientMongo::whereIn('id', $campaign_product_owner_ids)->get();
                        $owner_sa_ids = [];
                        foreach ($owner_notif_recipients as $owner_notif_recipient) {
                            if (isset($owner_notif_recipient->super_admin_m_id) && !empty($owner_notif_recipient->super_admin_m_id)) {
                                array_push($owner_sa_ids, $owner_notif_recipient->super_admin_m_id);
                            }
                        }
                        $owner_sa_emails = UserMongo::whereIn('id', $owner_sa_ids)->pluck('email');
                        
                        //notification Email Owner Start
                    
                        event(new CampaignLaunchEvent([
                      'type' => Notification::$NOTIFICATION_TYPE['campaign-launched'],
                        'from_id' => null,
                        'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['owner'],
                        'to_id' => null,
                        'to_client' => $campaign_product_owner_ids,
                        //'desc' => "Campaign launched",
                        'desc' => "Campaign confirmed",
                        //'message' => "Your Campaign has been launched!",
                        'message' => "A campaign with your products in it has been confirmed.",
                        'data' => ["campaign_id" => $campaign_obj->id]
                    ]));
                    
                    foreach ($campaign_product_owner_ids as $key => $val) {
                    $notification_obj = new Notification;
                        $notification_obj->id = uniqid();
                        $notification_obj->type = "campaign";
                        $notification_obj->from_id = null;
                        $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['owner'];
                        $notification_obj->to_id = $val;
                        $notification_obj->to_client = $val;
                        //$notification_obj->desc = "Campaign launched";
                        $notification_obj->desc = "Campaign confirmed";
                        $notification_obj->message = "A campaign with your products in it has been confirmed!";
                        $notification_obj->campaign_id = $campaign_obj->id;
                        $notification_obj->status = 0;
                        $notification_obj->save();

                    }
                    //$pdf = PDF::loadView('pdf.launch_campaign_details_pdf'); 
                    $mail_tmpl_params = [
                      'sender_email' => config('app.bbi_email'), 
                      //'receiver_name' => $bbi_sa->first_name . ' ' . $bbi_sa->last_name,
                      'receiver_name' => '',
                      'mail_message' => 'Campaign ' . $campaign_obj->name . 'has been confirmed.'
                    ];
                    $mail_data = [
                      //'email_to' => $bbi_sa->email,
                      'bcc' => $owner_sa_emails->toArray(),
                      //'email_to1' => 'sandhyarani.manelli@peopletech.com',
                      'email_to1' => 'admin@advertisingmarketplace.com',
                      'recipient_name' => '',
                      'recipient_name1' => 'Richard'
                      //'pdf_file_name' => "Insertion Order". date('d-m-Y') . ".pdf",
                      //'pdf' => $pdf
                    ];
                    Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data){
                     // $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Super admin set up for company - Billboards India');
                      $message->bcc($mail_data['bcc'], $mail_data['recipient_name'])->subject('User campaign confirmed! - Advertising Marketplace');
                      $message->cc($mail_data['email_to1'], $mail_data['recipient_name1'])->subject('User campaign confirmed! - Advertising Marketplace');
                      //$message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
                    });
                    
                    //notification owner end
                        
                        // send email
                        return response()->json(["status" => "1", "message" => "Campaign confirmed successfully."]);
                    } else {
                        return response()->json(["status" => 0, "message" => "There was a technical error while launching your campaign."]);
                    }
                } else if ($campaign_obj->type == Campaign::$CAMPAIGN_USER_TYPE['bbi']) {
                    if (count($unavailable_products) > 0) {
                        return response()->json(['status' => 0, 'message' => 'Some products from this campaign are already being used in other campaigns.', 'product_ids' => $unavailable_products]);
                    }
                    $campaign_obj->status = Campaign::$CAMPAIGN_STATUS['booking-requested'];
                    if ($campaign_obj->save()) {
                        // camapign launch successful. lock the products.
                        $campaign_products = ProductBooking::where('campaign_id', '=', $campaign_obj->id)->get();
                        foreach ($campaign_products as $campaign_product) {
                            $campaign_product->product_status = ProductBooking::$PRODUCT_STATUS['scheduled'];
                            $campaign_product->save();
                        }
                        // notifications and emails for user
                       
                        $campaign_user = UserMongo::where('id', '=', $campaign_obj->created_by)->first();
                        
                        // notifications and emails for user start 
                    event(new CampaignLaunchEvent([
                      'type' => Notification::$NOTIFICATION_TYPE['campaign-launched'],
                        'from_id' => null,
                        'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['user'],
                        'to_id' => $campaign_obj->created_by,
                        'to_client' => $campaign_obj->created_by,
                        'c_id' => $campaign_obj->cid,
                        'c_name' => $campaign_obj->name,
                        //'desc' => "Campaign launched",
                        'desc' => "Campaign confirmed",
                        //'message' => "Your Campaign has been launched!", 
                        'message' => "A campaign with your products in it has been confirmed!",
                        'data' => ["campaign_id" => $campaign_obj->id]
                    ]));
                    $notification_obj = new Notification;
                    $notification_obj->id = uniqid();
                    $notification_obj->type = "campaign";
                    $notification_obj->from_id = null;
                    $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['user'];
                    $notification_obj->to_id = $campaign_obj->created_by;
                    $notification_obj->to_client = $campaign_obj->created_by;
                    $notification_obj->c_id = $campaign_obj->cid;
                    $notification_obj->c_name = $campaign_obj->name;
                    //$notification_obj->desc = "Campaign launched";
                    $notification_obj->desc = "Campaign confirmed";
                    //$notification_obj->message = "Your Campaign has been launched!";
                    $notification_obj->message = "A campaign with your products in it has been confirmed!";
                    $notification_obj->campaign_id = $campaign_obj->id;
                    $notification_obj->status = 0;
                    $notification_obj->save();

                    //$pdf = PDF::loadView('pdf.launch_campaign_details_pdf');
                    $mail_tmpl_params = [
                      'sender_email' => config('app.bbi_email'), 
                      //'receiver_name' => $bbi_sa->first_name . ' ' . $bbi_sa->last_name,
                      'receiver_name' => $campaign_user->first_name . ' ' . $campaign_user->last_name,
                      'mail_message' => 'Campaign ' . $campaign_obj->name . 'has been confirmed.'
                    ];
                    $mail_data = [
                      //'email_to' => $bbi_sa->email,
                      'email_to' => $campaign_user->email,
                      //'email_to1' => 'sandhyarani.manelli@peopletech.com',
                      'email_to1' => 'admin@advertisingmarketplace.com',
                      'recipient_name1' => 'Richard',
                      'recipient_name' => ''
                      //'pdf_file_name' => "Insertion Order". date('d-m-Y') . ".pdf",
                      //'pdf' => $pdf
                    ];
                    Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data){
                      $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Your campaign has been confirmed! - Advertising Marketplace');
                      $message->cc($mail_data['email_to1'], $mail_data['recipient_name1'])->subject('Your campaign has been confirmed! - Advertising Marketplace');
                      //$message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
                    });
                    
                    // notifications and emails for user end
                    
                        
                        // notifications and emails for owner
                        $campaign_product_owner_ids = ProductBooking::raw(function($collection) use ($campaign_id) {
                                    return $collection->distinct('product_owner', ['campaign_id' => $campaign_id]);
                                });
                        $owner_notif_recipients = ClientMongo::whereIn('id', $campaign_product_owner_ids)->get();
                        $owner_sa_ids = [];
                        foreach ($owner_notif_recipients as $owner_notif_recipient) {
                            if (isset($owner_notif_recipient->super_admin_m_id) && !empty($owner_notif_recipient->super_admin_m_id)) {
                                array_push($owner_sa_ids, $owner_notif_recipient->super_admin_m_id);
                            }
                        }
                        $owner_sa_emails = UserMongo::whereIn('id', $owner_sa_ids)->pluck('email');
                        //foreach($campaign_product_owner_ids as $oid){
                        /* NotificationHelper::createNotification([
                          'type' => Notification::$NOTIFICATION_TYPE['campaign-launched'],
                          'from_id' => null,
                          'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['owner'],
                          'to_id' => null,
                          'to_client' => $oid,
                          'desc' => "Campaign launched",
                          'message' => "A campaign with your products in it has been launched.",
                          'data' => ["campaign_id" => $campaign_obj->id]
                          ]); */
                        $campaign_user = UserMongo::where('id', '=', $campaign_obj->created_by)->first();
                        
                        //notification Email Owner Start
                    
                        event(new CampaignLaunchEvent([
                      'type' => Notification::$NOTIFICATION_TYPE['campaign-launched'],
                        'from_id' => null,
                        'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['owner'],
                        'to_id' => null,
                        'to_client' => $campaign_product_owner_ids,
                        //'desc' => "Campaign launched",
                        'desc' => "Campaign confirmed",
                        //'message' => "Your Campaign has been launched!",
                        'message' => "A campaign with your products in it has been confirmed.",
                        'data' => ["campaign_id" => $campaign_obj->id]
                    ]));
                    
                    //foreach ($campaign_product_owner_ids as $key => $val) {
                    $notification_obj = new Notification;
                        $notification_obj->id = uniqid();
                        $notification_obj->type = "campaign";
                        $notification_obj->from_id = null;
                        $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['owner'];
                        $notification_obj->to_id = null;
                        $notification_obj->to_client = $campaign_product_owner_ids;
                        //$notification_obj->desc = "Campaign launched";
                        $notification_obj->desc = "Campaign confirmed";
                        $notification_obj->message = "A campaign with your products in it has been confirmed!";
                        $notification_obj->campaign_id = $campaign_obj->id;
                        $notification_obj->status = 0;
                        $notification_obj->save();

                    //}
                    
                    //$pdf = PDF::loadView('pdf.launch_campaign_details_pdf');
                    
                    $mail_tmpl_params = [
                      'sender_email' => config('app.bbi_email'), 
                      //'receiver_name' => $bbi_sa->first_name . ' ' . $bbi_sa->last_name,
                      'receiver_name' => '',
                      'mail_message' => 'Campaign ' . $campaign_obj->name . 'has been confirmed.'
                    ];
                    $mail_data = [
                      //'email_to' => $bbi_sa->email,
                      'bcc' => $owner_sa_emails->toArray(),
                      //'email_to1' => 'sandhyarani.manelli@peopletech.com',
                      'email_to1' => 'admin@advertisingmarketplace.com',
                      'recipient_name1' => 'Richard',
                      'recipient_name' => ''
                      //'pdf_file_name' => "Insertion Order". date('d-m-Y') . ".pdf",
                      //'pdf' => $pdf
                    ];
                    Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data){
                      $message->bcc($mail_data['bcc'], $mail_data['recipient_name'])->subject('User campaign confirmed! - Advertising Marketplace');
                      $message->cc($mail_data['email_to1'], $mail_data['recipient_name1'])->subject('User campaign confirmed! - Advertising Marketplace');
                      //$message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
                    });
                    
                    //notification owner end
                        
                        return response()->json(["status" => "1", "message" => "Campaign confirmed successfully."]);
                    } else {
                        return response()->json(["status" => 0, "message" => "There was a technical error while launching your campaign."]);
                    }
                } else {
                    return response()->json(['status' => 0, 'message' => 'You are not authorized to launch this campaign.']);
                }
            }
        } else {
            // logged in user is owner 
            // check if the campaign belongs to owner. if yes, then launch campaign, otherwise, do nothing.
            if ($campaign_obj->type == Campaign::$CAMPAIGN_USER_TYPE['owner'] && $campaign_obj->client_mongo_id == $user_mongo['client_mongo_id']) {
                if (count($unavailable_products) > 0) {
                    return response()->json(['status' => 0, 'message' => 'Some products from this campaign are already being used in other campaigns.', 'product_ids' => $unavailable_products]);
                }
                $campaign_obj->status = Campaign::$CAMPAIGN_STATUS['booking-requested'];
                if ($campaign_obj->save()) {
                    $campaign_products = ProductBooking::where('campaign_id', '=', $campaign_obj->id)->get();
                    foreach ($campaign_products as $campaign_product) {
                        $campaign_product->product_status = ProductBooking::$PRODUCT_STATUS['scheduled'];
                        $campaign_product->save();
                    }
                    return response()->json(["status" => "1", "message" => "Campaign launched successfully."]);
                } else {
                    return response()->json(["status" => 0, "message" => "There was a technical error while launching your campaign."]);
                }
            } else {
                return response()->json(['status' => 0, 'message' => 'You are not authorized to launch this campaign.']);
            }
        }
    }


//($campaign_id,$flag,$gst) 
    public function bookNonUserCampaign($campaign_id) { 
        $campaign = Campaign::where('id', '=', $campaign_id)->first();
        $campaign->status = Campaign::$CAMPAIGN_STATUS['scheduled'];
        //$campaign->gststatus = $flag;
        //$campaign->gst_price = $gst;
        if ($campaign->save()) {
            // change status of all the products in this campaign
            ProductBooking::raw(function($collection) use ($campaign_id) {
                $collection->updateMany(["campaign_id" => $campaign_id], ['$set' => [
                        "product_status" => ProductBooking::$PRODUCT_STATUS['scheduled']
                    ]]
                );
            });
            return response()->json(['status' => 1, 'message' => 'Campaign booked successfully.']);
        } else {
            return response()->json(['status' => 0, 'message' => 'There was a technical error booking the campaign.']);
        }
    }


    public function getCampaignPayments($campaign_id) {
        $campaign = Campaign::where('id', '=', $campaign_id)->first();
        $campaign_payments = CampaignPayment::where('campaign_id', '=', $campaign_id)->get();
        //echo "<pre>"; print_r($campaign_payments);exit;
        // $campaign->total_amount = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('admin_price');
        //if (isset($campaign_payments) && count($campaign_payments) > 0) {
			$price = 0;
            $camptot = 0;
			$offershortlistedsum = 0;
			$tax_percentage_booking = 0;
			$tax_percentage_amount = 0;
			$tax_percentage_amount_total = 0;
			$newprocessingfeeamtSum = 0;
			$tax_percentage_booking_tot = 0;
			$tax_percentage_amount_tot = 0;
        if (isset($campaign) && count($campaign) > 0) {
			$total_paid = $campaign_payments->sum('amount');
        if ($campaign->status > 1000) {
            /*$campaign->total_amount = CampaignProduct::where('campaign_id', '=', $campaign_id)->sum('price');
            $campaign->no_of_products = CampaignProduct::where('campaign_id', '=', $campaign_id)->distinct('package_id')->get()->count();
            $campaign->totalamount = $campaign->total_amount;
            */
            
            $total_amount = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('admin_price');
			$totalpriceval = 0;
            if($total_amount==0){
				$campaign_products = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
					 //$totalpriceval += $price->price;
					if (isset($campaign_products) && count($campaign_products) > 0) {
						foreach ($campaign_products as $campaign_product_camp) {
							$tax_percentage_booking_tot = 0;
							$tax_percentage_amount_tot = 0;
							$diff=date_diff(date_create($campaign_product_camp->booked_from),date_create($campaign_product_camp->booked_to));
							$daysCount = $diff->format("%a");
							$daysCountCPM = $daysCount + 1;
							$getproductDetails =Product::where('id', '=', $campaign_product_camp->product_id)->get();
							if (!$getproductDetails->isEmpty()){
								$getproductDetails = $getproductDetails->first();
								$perdayprice = $getproductDetails->default_price/28;
								
								if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
									$price_cp1 = $campaign_product_camp->price*$campaign_product_camp->quantity;
									if($total_paid != 0){
										if(isset($campaign_product_camp->tax_percentage)){
											$tax_percentage_booking_tot = $campaign_product_camp->tax_percentage;
										}else{
											$tax_percentage_booking_tot = 0;
										}
									}else{
										$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
									}
									if($tax_percentage_booking_tot != 0){
										$tax_percentage_amount_tot = ($price_cp1 * ($tax_percentage_booking_tot))/100;
									}
									$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
									$camptot += $price_cp1+$tax_percentage_amount_tot;
								}else{
									$priceperselectedDates = $campaign_product_camp->price;
									if($total_paid != 0){
										if(isset($campaign_product_camp->tax_percentage)){
											$tax_percentage_booking_tot = $campaign_product_camp->tax_percentage;
										}else{
											$tax_percentage_booking_tot = 0;
										}
									}else{
										$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
									}
									if($tax_percentage_booking_tot != 0){
										$tax_percentage_amount_tot = ($priceperselectedDates * ($tax_percentage_booking_tot))/100;
									}
									$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
									$camptot += ($priceperselectedDates*$campaign_product_camp->quantity)+($tax_percentage_amount_tot*$campaign_product_camp->quantity);
								}
							}
						}
					}
					if (isset($campaign_products) && count($campaign_products) > 0) {
						
						foreach ($campaign_products as $campaign_product) {
							$campaign->tax_percentage_amount = 0;
							$tax_percentage_booking = 0;
							$tax_percentage_amount = 0;
							$diff=date_diff(date_create($campaign_product->booked_from),date_create($campaign_product->booked_to));
							$daysCount = $diff->format("%a");
							$daysCountCPM = $daysCount + 1;
							$getproductDetails =Product::where('id', '=', $campaign_product->product_id)->get();
							if (!$getproductDetails->isEmpty()){
								$getproductDetails = $getproductDetails->first();
								$perdayprice = $getproductDetails->default_price/28;
								
								$offerDetails = MakeOffer::where([
									['campaign_id', '=', $campaign_product->campaign_id],
									['status', '=', 20],
								])->get();
								if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
									if(isset($offerDetails) && count($offerDetails)==1){
										foreach($offerDetails as $offerDetails){
											$price_cp = $campaign_product->price;
											$offerprice = $offerDetails->price;
											$priceperday = $price_cp;
											$priceperselectedDates = $priceperday;
											$newpricepercentage = ($priceperselectedDates/$camptot) * 100;
											$stripe_percent=$getproductDetails->stripe_percent;
											$newofferprice = ($offerprice * ($newpricepercentage))/100;
											if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
												$newofferprice = $newofferprice*$campaign_product->quantity;
											}
											$price = $newofferprice;
										}
									}else{
										$priceperselectedDates = $campaign_product->price;
										$stripe_percent=$getproductDetails->stripe_percent;
										if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
											$price = $campaign_product->price*$campaign_product->quantity;
										}else{
											$price = $campaign_product->price;
										}
									}
									
									if($total_paid != 0){
										if(isset($campaign_product->tax_percentage)){
											$tax_percentage_booking = $campaign_product->tax_percentage;
										}else{
											$tax_percentage_booking = 0;
										}
									}else{
										$tax_percentage_booking = $getproductDetails->tax_percentage;
									}
									if($tax_percentage_booking != 0){
										$tax_percentage_amount = ($price * ($tax_percentage_booking))/100;
									}
									$campaign->tax_percentage_amount = round($tax_percentage_amount,2);
									$newofferStripepercentamt = (($price+$tax_percentage_amount) * ($stripe_percent))/100;
									$newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
									$tax_percentage_amount_total += round($tax_percentage_amount,2);
									$offershortlistedsum+= $price+$campaign->tax_percentage_amount;
								}else{
									if(isset($offerDetails) && count($offerDetails)==1){
										foreach($offerDetails as $offerDetails){
											$price_cp = $getproductDetails->default_price;
											$offerprice = $offerDetails->price;
											//$priceperday = $price_cp/28;
											//$priceperselectedDates = $priceperday * $daysCountCPM;
												
											///variable_offer///
											$priceperday = $campaign_product->price;
											$priceperselectedDates = $priceperday;
											
											$stripe_percent=$getproductDetails->stripe_percent;
											$newpricepercentage = ($priceperselectedDates/$camptot) * 100;
											
											$newofferprice = ($offerprice * ($newpricepercentage))/100;
											if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
												$newofferprice = $newofferprice*$campaign_product->quantity;
											}
											$price = $newofferprice;
										}
									}else{
										$priceperselectedDates = $campaign_product->price;
										$stripe_percent=$getproductDetails->stripe_percent;
										if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
											$price = $campaign_product->price*$campaign_product->quantity;
										}else{
											$price = $campaign_product->price;
										}
									}
									if($total_paid != 0){
										if(isset($campaign_product->tax_percentage)){
											$tax_percentage_booking = $campaign_product->tax_percentage;
										}else{
											$tax_percentage_booking = 0;
										}
									}else{
										$tax_percentage_booking = $getproductDetails->tax_percentage;
									}
									if($tax_percentage_booking != 0){
										$tax_percentage_amount = ($price * ($tax_percentage_booking))/100;
									}
									$campaign->tax_percentage_amount = round($tax_percentage_amount,2);
									$newofferStripepercentamt = (($price+$tax_percentage_amount) * ($stripe_percent))/100;
									$newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
									$tax_percentage_amount_total += round($tax_percentage_amount,2);
									$offershortlistedsum+= $price+$campaign->tax_percentage_amount;
								}
								$newprocessingfeeamtSum += $newprocessingfeeamt;
							}
							$totalpriceval = $offershortlistedsum;
						}
					}
                 //$total_amount = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('price');
                 $total_amount = $totalpriceval;
            }
			
			$campaign->gross_fee_price = 0;
			if(isset($campaign->gross_fee_percentage) && $campaign->gross_fee_percentage > 0){
				$campaign->gross_fee_price = ($offershortlistedsum*$campaign->gross_fee_percentage)/100;
			}else{
				$campaign->gross_fee_price = 0;
			}
            $campaign->tax_percentage_amount_total = $tax_percentage_amount_total;
            $campaign->total_amount = $total_amount;
			$campaign->newprocessingfeeamtSum = $newprocessingfeeamtSum;
            $campaign->no_of_products = ProductBooking::where('campaign_id', '=', $campaign_id)->distinct('product_id')->get()->count();
            
            /*$gststatus = isset($campaign->gststatus)?$campaign->gststatus:0;
                if($gststatus ==1){
                    $campaign->totalamount = $campaign->total_amount+round(($campaign->total_amount*(0.18)),2);
                }
                else{
                     $campaign->totalamount = $campaign->total_amount;
                }*/
                 
        
        } else {
            $total_amount = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('admin_price');
            if($total_amount==0){
				$campaign_products = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
				 //$totalpriceval += $price->price;
				if (isset($campaign_products) && count($campaign_products) > 0) {
					foreach ($campaign_products as $campaign_product_camp) {
						$tax_percentage_booking_tot = 0;
						$tax_percentage_amount_tot = 0;
						$diff=date_diff(date_create($campaign_product_camp->booked_from),date_create($campaign_product_camp->booked_to));
						$daysCount = $diff->format("%a");
						$daysCountCPM = $daysCount + 1;
						$getproductDetails =Product::where('id', '=', $campaign_product_camp->product_id)->get();
						if (!$getproductDetails->isEmpty()){
							$getproductDetails = $getproductDetails->first();
							$perdayprice = $getproductDetails->default_price/28;
							
							if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
								$price_cp1 = $campaign_product_camp->price*$campaign_product_camp->quantity;
								if($total_paid != 0){
									if(isset($campaign_product_camp->tax_percentage)){
										$tax_percentage_booking_tot = $campaign_product_camp->tax_percentage;
									}else{
										$tax_percentage_booking_tot = 0;
									}
								}else{
									$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
								}
								if($tax_percentage_booking_tot != 0){
									$tax_percentage_amount_tot = ($price_cp1 * ($tax_percentage_booking_tot))/100;
								}
								$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
								$camptot += $price_cp1+$tax_percentage_amount_tot;
							}else{
								$priceperselectedDates = $campaign_product_camp->price;
								if($total_paid != 0){
									if(isset($campaign_product_camp->tax_percentage)){
										$tax_percentage_booking_tot = $campaign_product_camp->tax_percentage;
									}else{
										$tax_percentage_booking_tot = 0;
									}
								}else{
									$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
								}
								if($tax_percentage_booking_tot != 0){
									$tax_percentage_amount_tot = ($priceperselectedDates * ($tax_percentage_booking_tot))/100;
								}
								$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
								$camptot += ($priceperselectedDates*$campaign_product_camp->quantity)+($tax_percentage_amount_tot*$campaign_product_camp->quantity);
							}
						}
					}
				}
				if (isset($campaign_products) && count($campaign_products) > 0) {
					foreach ($campaign_products as $campaign_product) {
						$campaign->tax_percentage_amount = 0;
						$tax_percentage_booking = 0;
						$tax_percentage_amount = 0;
						$diff=date_diff(date_create($campaign_product->booked_from),date_create($campaign_product->booked_to));
						$daysCount = $diff->format("%a");
						$daysCountCPM = $daysCount + 1;
						$getproductDetails =Product::where('id', '=', $campaign_product->product_id)->get();
						if (!$getproductDetails->isEmpty()){
							$getproductDetails = $getproductDetails->first();
							$perdayprice = $getproductDetails->default_price/28;
							
							$offerDetails = MakeOffer::where([
								['campaign_id', '=', $campaign_product->campaign_id],
								['status', '=', 20],
							])->get();
							if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
								if(isset($offerDetails) && count($offerDetails)==1){
									foreach($offerDetails as $offerDetails){
										$price_cp = $campaign_product->price;
										$offerprice = $offerDetails->price;
										$priceperday = $price_cp;
										$priceperselectedDates = $priceperday;
										$newpricepercentage = ($priceperselectedDates/$camptot) * 100;
										
										$newofferprice = ($offerprice * ($newpricepercentage))/100;
										if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
											$newofferprice = $newofferprice*$campaign_product->quantity;
										}
										$price = $newofferprice;
										$stripe_percent=$getproductDetails->stripe_percent;
									}
								}else{
									$priceperselectedDates = $campaign_product->price;
									if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
										$price = $campaign_product->price*$campaign_product->quantity;
									}else{
										$price = $campaign_product->price;
									}
									$stripe_percent=$getproductDetails->stripe_percent;
								}
								if($total_paid != 0){
									if(isset($campaign_product->tax_percentage)){
										$tax_percentage_booking = $campaign_product->tax_percentage;
									}else{
										$tax_percentage_booking = 0;
									}
								}else{
									$tax_percentage_booking = $getproductDetails->tax_percentage;
								}
								if($tax_percentage_booking != 0){
									$tax_percentage_amount = ($price * ($tax_percentage_booking))/100;
								}
								$campaign->tax_percentage_amount = round($tax_percentage_amount,2);
								$newofferStripepercentamt = (($price+$campaign->tax_percentage_amount) * ($stripe_percent))/100;
								$newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
								$tax_percentage_amount_total += round($tax_percentage_amount,2);
								$offershortlistedsum+= $price+$campaign->tax_percentage_amount;
							}else{
								if(isset($offerDetails) && count($offerDetails)==1){
									foreach($offerDetails as $offerDetails){
										$price_cp = $getproductDetails->default_price;
										$offerprice = $offerDetails->price;
										//$priceperday = $price_cp/28;
										//$priceperselectedDates = $priceperday * $daysCountCPM;
										
										$priceperday = $campaign_product->price;
										$priceperselectedDates = $priceperday;
										
										$newpricepercentage = ($priceperselectedDates/$camptot) * 100;
										
										$newofferprice = ($offerprice * ($newpricepercentage))/100;
										if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
											$newofferprice = $newofferprice*$campaign_product->quantity;
										}
										$price = $newofferprice;
										$stripe_percent=$getproductDetails->stripe_percent;
									}
								}else{
									$priceperselectedDates = $campaign_product->price;
									if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
										$price = $campaign_product->price*$campaign_product->quantity;
									}else{
										$price = $campaign_product->price;
									}
									$stripe_percent=$getproductDetails->stripe_percent;
								}
								if($total_paid != 0){
									if(isset($campaign_product->tax_percentage)){
										$tax_percentage_booking = $campaign_product->tax_percentage;
									}else{
										$tax_percentage_booking = 0;
									}
								}else{
									$tax_percentage_booking = $getproductDetails->tax_percentage;
								}
								if($tax_percentage_booking != 0){
									$tax_percentage_amount = ($price * ($tax_percentage_booking))/100;
								}
								$campaign->tax_percentage_amount = round($tax_percentage_amount,2);
								$newofferStripepercentamt = (($price+$campaign->tax_percentage_amount) * ($stripe_percent))/100;
								$newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
								$tax_percentage_amount_total += round($tax_percentage_amount,2);
								$offershortlistedsum+= $price+$campaign->tax_percentage_amount;
							}
							$newprocessingfeeamtSum += $newprocessingfeeamt;
						}
						$total_amount = $offershortlistedsum;
					}
				}
            }
			$campaign->gross_fee_price = 0;
			if(isset($campaign->gross_fee_percentage) && $campaign->gross_fee_percentage > 0){
				$campaign->gross_fee_price = ($offershortlistedsum*$campaign->gross_fee_percentage)/100;
				$newprocessingfeeamtGross = ($newprocessingfeeamtSum*$campaign->gross_fee_percentage)/100;
				$newprocessingfeeamtSum = $newprocessingfeeamtSum+$newprocessingfeeamtGross;
			}else{
				$campaign->gross_fee_price = 0;
			}
            $campaign->tax_percentage_amount_total = $tax_percentage_amount_total;
            $campaign->total_amount = $total_amount;
            $campaign->newprocessingfeeamtSum = $newprocessingfeeamtSum;
            /* $gststatus = isset($campaign->gststatus)?$campaign->gststatus:0;
                if($gststatus ==1){ 
                    $campaign->totalamount = $campaign->total_amount+round(($campaign->total_amount*(0.18)),2);
                }
                else{
                     $campaign->totalamount = $campaign->total_amount;
                } */
        
        $campaign->totalamount = $campaign->total_amount;
            $campaign->no_of_products = ProductBooking::where('campaign_id', '=', $campaign_id)->distinct('product_id')->get()->count();
        }
        //if (isset($campaign_payments) && count($campaign_payments) > 0) {
           
            $campaign_payments->total_paid = $total_paid;
			//echo '<pre>';print_r($campaign_payments->total_paid);exit
             $campaign->total_paid= $total_paid;
             $campaign->balance= ($campaign['total_amount']+$campaign->gross_fee_price) - ($total_paid);
			 //echo '<pre>';print_r($campaign_payments->balance);exit;
             $campaign->refunded_amount = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('refunded_amount');
             $campaign->bal_amount_available_with_amp = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('bal_amount_available_with_amp');
            return response()->json(["total_paid" => $total_paid, "balance"=>$campaign->balance,"all_payments" => $campaign_payments, 'campaign_details' => $campaign]);
        } else {
            $user_campaigns = Campaign::whereIn('format_type', [null, Format::$FORMAT_TYPE['ooh']])
                        //->where('type', '=', Campaign::$CAMPAIGN_USER_TYPE['user'])
                        ->where([
                            ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['user']],
                            ['status', '>=', Campaign::$CAMPAIGN_STATUS['booking-requested']]
                        ])
                        ->orderBy('updated_at', 'desc')->get();
        
        $current_date = date('d-m-Y');
        $colorcode = '';
        $diff= '';
        
        $user_campaigns_arr = [];       
        $j = 0;
        foreach ($user_campaigns as $user_campaign) {
            $total_paid = CampaignPayment::where('campaign_id', '=', $user_campaign->id)->sum('amount');
            $total_price = ProductBooking::where('campaign_id', '=', $user_campaign->id)->sum('price');
            $user_campaign->total_paid = $total_paid;
            $user_campaign->total_price = $total_price;
            //$user_campaign->total_paid = $total_paid;
            $count = ProductBooking::where('campaign_id', '=', $user_campaign->id)->count();
            $user_campaign->product_count = $count;
            $product_start_date = ProductBooking::where('campaign_id', '=', $user_campaign->id)->select("booked_from","booked_to")->orderBy('booked_from', 'asc')->first();
            if (!empty($product_start_date)) {
                $user_campaign->start_date = $product_start_date->booked_from;
                $user_campaign->end_date = $product_start_date->booked_to;
                $diff=date_diff(date_create($current_date),date_create($user_campaign->start_date));
                if($diff->days <=7 ){
                    $user_campaign->colorcode = 'red';
                }else{
                    $user_campaign->colorcode = 'black';
                }
            }
            $user_campaign->balance= ($user_campaign->total_price) - ($total_paid);
            $user_campaign->refunded_amount = CampaignPayment::where('campaign_id', '=', $user_campaign->id)->sum('refunded_amount');
			//echo '<pre>';print_r($user_campaign->refunded_amount);exit;
            $user_campaign->bal_amount_available_with_amp = CampaignPayment::where('campaign_id', '=', $user_campaign->id)->sum('bal_amount_available_with_amp');

            $user_details = UserMongo::select('first_name', 'last_name', 'email', 'phone')->where('id', '=', $user_campaign->created_by)->first();
            if($user_details){
               $user_detailstoArray = $user_details->toArray();
            }else{
                $user_detailstoArray = [];
            }
            array_push($user_campaigns_arr, array_merge($user_campaign->toArray(), $user_detailstoArray));
            ++$j;
        }

        $admin_campaigns = Campaign::whereIn('format_type', [null, Format::$FORMAT_TYPE['ooh']])->where([
            ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['bbi']],
        ])->orderBy('updated_at', 'desc')->get();
        if (!empty($admin_campaigns)) {
            $i = 0;
            foreach ($admin_campaigns as $campaign) {
                $total_paid = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('amount');
                $total_price = ProductBooking::where('campaign_id', '=', $campaign->id)->sum('price');
                $count = ProductBooking::where('campaign_id', '=', $campaign->id)->count();
                $admin_campaigns[$i]->total_paid = $total_paid;
                $admin_campaigns[$i]->total_price = $total_price;
                $admin_campaigns[$i]->product_count = $count;
                $product_start_date = ProductBooking::where('campaign_id', '=', $campaign->id)->select("booked_from","booked_to")->orderBy('booked_from', 'asc')->first();
                if (!empty($product_start_date)) {
                    $admin_campaigns[$i]->start_date = $product_start_date->booked_from;
                    $admin_campaigns[$i]->end_date = $product_start_date->booked_to;
                    $diff=date_diff(date_create($current_date),date_create($admin_campaigns[$i]->start_date));
                    if($diff->days <=7 ){
                        $admin_campaigns[$i]->colorcode = 'red';
                    }else{
                        $admin_campaigns[$i]->colorcode = 'black';
                    }
                }
                $admin_campaigns[$i]->balance= ($campaign['total_price']) - ($total_paid);
                $admin_campaigns[$i]->refunded_amount = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('refunded_amount');
                $admin_campaigns[$i]->bal_amount_available_with_amp = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('bal_amount_available_with_amp');
                ++$i;
            }
        }
        $campaigns = [
            'user_campaigns' => $user_campaigns_arr,
            'admin_campaigns' => $admin_campaigns
        ];
        return response()->json(["status" => "0", 'campaigns' => $campaigns]);
        
           // return response()->json(["status" => "0", "message" => "No payments found for the campaign.", 'campaign_details' => $campaign]);
        }
            return response()->json(["status" => "0", "message" => "No payments found for the campaign.", 'campaign_details' => $campaign]);
    }

    public function updateCampaignPayment() {
        $this->validate($this->request, [
            'campaign_payment.campaign_id' => 'required',
            'campaign_payment.amount' => 'required',
            'campaign_payment.type' => 'required',
            'campaign_payment.received_by' => 'required'
                ], [
            'campaign_payment.campaign_id.required' => 'Campaign id is required',
            'campaign_payment.amount.required' => 'Amount is required',
            'campaign_payment.type.required' => 'Type is required',
            'campaign_payment.received_by.required' => 'Field "Received By" is required'
                ]
        );
        $input = $this->input['campaign_payment'];
        if ($input['type'] != "Cash" && !isset($input['reference_no'])) {
            return response()->json(["status" => "0", "message" => "Cheque/Reference/Transaction No. is required in case of payment made other than by cash."]);
        }
        $campaign_id = $input['campaign_id'];

        $campaign = Campaign::where('id', '=', $campaign_id)->first();
        if ($campaign->status > 1000) {
            $act_budget_group = CampaignProduct::where('campaign_id', '=', $campaign_id)->sum('price');
        } else {
            $act_budget_group = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('admin_price');
            if($act_budget_group ==0)
            {
                  $act_budget_group = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('price');
            }
        }
        //dd($act_budget_group);
        /*$gst_price = isset($campaign->gst_price)?$campaign->gst_price:0;
        $campaign_act_budget = ($act_budget_group+$gst_price);*/
 //dd($act_budget_group);
    /*$gststatus = isset($campaign->gststatus)?$campaign->gststatus:0;
                if($gststatus ==1){
                    $campaign_act_budget = $act_budget_group+round(($act_budget_group*(0.18)),2);
                }
                else{
                     $campaign_act_budget = $act_budget_group;
                }*/
                $campaign_act_budget = $act_budget_group;
        $paid_group = CampaignPayment::where('campaign_id', '=', $campaign_id)->sum('amount');
        $campaign_paid = $paid_group > 0 ? $paid_group : 0;
       
//dd($paid_group);
        $remaining_campaign_payment = $campaign_act_budget - $campaign_paid;
        
//dd($remaining_campaign_payment);
        if ((int) $input['amount'] > $remaining_campaign_payment) {
            return response()->json(['status' => 0, 'message' => 'The given amount can not be larger than the pending amount.']);
        }
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $campaign_payment = new CampaignPayment;
        $campaign_payment->campaign_id = isset($input['campaign_id']) ? $input['campaign_id'] : "";
        $campaign_payment->amount = isset($input['amount']) ? (int) $input['amount'] : "";
        $campaign_payment->type = isset($input['type']) ? $input['type'] : "";
        $campaign_payment->comment = isset($input['comment']) ? $input['comment'] : "";
        $campaign_payment->reference_no = isset($input['reference_no']) ? $input['reference_no'] : "";
        $campaign_payment->received_by = isset($input['received_by']) ? $input['received_by'] : "";
        $campaign_payment->updated_by_id = $user_mongo['id'];
        $campaign_payment->updated_by_id_name = $user_mongo['first_name'] . " " . $user_mongo['last_name'];
        $payment_img_path = base_path() . '/html/uploads/images/campaign_payments';
        if ($this->request->hasFile('image')) {
            if ($this->request->file('image')->move($payment_img_path, $this->request->file('image')->getClientOriginalName())) {
                $campaign_payment->image = "/uploads/images/campaign_payments/" . $this->request->file('image')->getClientOriginalName();
            }
        }
        if ($campaign_payment->save()) {
            return response()->json(["status" => "1", "message" => "Campaign payment updated successfully."]);
        } else {
            return response()->json(["status" => "0", "message" => "There was a technical error while updating the payment. Please try again later."]);
        }
    }

    public function closeCampaign($campaign_id) {
        $campaign = Campaign::where([
                    ['id', '=', $campaign_id],
                    ['status', '=', Campaign::$CAMPAIGN_STATUS['suspended']],
                ])->orWhere([
                    ['id', '=', $campaign_id],
                    ['status', '=', Campaign::$CAMPAIGN_STATUS['scheduled']],
                ])->orWhere([
                    ['id', '=', $campaign_id],
                    ['status', '=', Campaign::$CAMPAIGN_STATUS['running']],
                ])->first();
        if (!isset($campaign) || empty($campaign)) {
            return response()->json(["status" => "0", "message" => "No such campaign found. please reload the page and try again."]);
        }
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        if ($campaign->type == Campaign::$CAMPAIGN_USER_TYPE['user']) {
            if ($user_mongo['user_type'] == 'basic' || $user_mongo['user_type'] == 'owner') {
                return response()->json(['status' => 0, 'message' => "You're not authorized to close this campaign."]);
            }
        } else if ($campaign->type == Campaign::$CAMPAIGN_USER_TYPE['bbi']) {
            if ($user_mongo['user_type'] == 'basic' || $user_mongo['user_type'] == 'owner') {
                return response()->json(['status' => 0, 'message' => "You're not authorized to close this campaign."]);
            }
        } else if ($campaign->type == Campaign::$CAMPAIGN_USER_TYPE['owner']) {
            if ($user_mongo['user_type'] == 'basic' || $user_mongo['user_type'] == 'bbi' ||
                    ($user_mongo['user_type'] == 'owner' && $campaign->client_mongo_id != $user_mongo['client_mongo_id'])) {
                return response()->json(['status' => 0, 'message' => "You're not authorized to close this campaign."]);
            }
        }
        ProductBooking::raw(function($collection) use ($campaign_id) {
            $collection->updateMany(["campaign_id" => $campaign_id], ['$set' => [
                    "product_status" => 2 // product freed
                ]]
            );
        });
        $campaign->status = Campaign::$CAMPAIGN_STATUS['stopped'];
        if ($campaign->save()) {
            $user = JWTAuth::parseToken()->getPayload()['user'];
            $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];

            $campaign_user_mongo = UserMongo::where('id', '=', $campaign->created_by)->first();

            $noti_array = [
                'type' => Notification::$NOTIFICATION_TYPE['campaign-closed'],
                'from_id' => null,
                'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['user'],
                'to_id' => $campaign->created_by,
                'to_client' => $campaign->created_by,
                'desc' => "Campaign closed",
                'message' => "Your campaign has been completed and closed successfully",
                'data' => ["campaign_id" => $campaign->id]
            ];
            $mail_array = [
                'mail_tmpl_params' => [
                    'sender_email' => $user['email'],
                    'receiver_name' => $campaign_user_mongo->first_name,
                    'mail_message' => "Your campaign '" . $campaign->name . "' has been closed successfully. Visit our website for further details."
                ],
                'email_to' => $campaign_user_mongo->email,
                'recipient_name' => $campaign_user_mongo->first_name,
                //'subject' => 'Campaign closed - Billboards India'
                'subject' => 'Campaign closed - Advertising Marketplace'
            ];
            event(new CampaignClosedEvent($noti_array, $mail_array));
            $notification_obj = new Notification;
            $notification_obj->id = uniqid();
            $notification_obj->type = "campaign";
            $notification_obj->from_id = null;
            $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['user'];
            $notification_obj->to_id = $campaign->created_by;
            $notification_obj->to_client = $campaign->created_by;
            $notification_obj->desc = "Campaign closed";
            $notification_obj->message = "Your campaign has been completed and closed successfully.";
            $notification_obj->campaign_id = $campaign->id;
            $notification_obj->status = 0;
            $notification_obj->save();
            // notifications and emails for owner
            $campaign_product_owner_ids = ProductBooking::raw(function($collection) use ($campaign_id) {
                        return $collection->distinct('product_owner', ['campaign_id' => $campaign_id]);
                    });
            $owner_notif_recipients = ClientMongo::whereIn('id', $campaign_product_owner_ids)->get();
            $owner_sa_ids = [];
            foreach ($owner_notif_recipients as $owner_notif_recipient) {
                if (isset($owner_notif_recipient->super_admin_m_id) && !empty($owner_notif_recipient->super_admin_m_id)) {
                    array_push($owner_sa_ids, $owner_notif_recipient->super_admin_m_id);
                }
            }
            $owner_sa_emails = UserMongo::whereIn('id', $owner_sa_ids)->pluck('email');

            $noti_array = [
                'type' => Notification::$NOTIFICATION_TYPE['campaign-closed'],
                'from_id' => null,
                'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['owner'],
                'to_id' => null,
                'to_client' => $campaign_product_owner_ids,
                'desc' => "Campaign closed",
                'message' => "A campaign with your products in it has been closed successfully.",
                'data' => ["campaign_id" => $campaign->id]
            ];

            $mail_array = [
                'mail_tmpl_params' => ['sender_email' => config('app.bbi_email'), //, 
                    'receiver_name' => "",
                    'mail_message' => 'Campaign ' . $campaign->name . 'has been closed.'
                ],
                'bcc' => $owner_sa_emails->toArray(),
                //'subject' => 'User campaign closed. - Billboards India'
                'subject' => 'User campaign closed. - Advertising Marketplace'
            ];
            event(new CampaignClosedEvent($noti_array, $mail_array));

            foreach ($campaign_product_owner_ids as $key => $val) {
                $notification_obj = new Notification;
                $notification_obj->id = uniqid();
                $notification_obj->type = "campaign";
                $notification_obj->from_id = null;
                $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['owner'];
                $notification_obj->to_id = $val;
                $notification_obj->to_client = $val;
                $notification_obj->desc = "Campaign closed";
                $notification_obj->message = "A campaign with your products in it has been closed successfully.";
                $notification_obj->campaign_id = $campaign->id;
                $notification_obj->status = 0;
                $notification_obj->save();
            }

            $mail_tmpl_params = [
                'sender_email' => config('app.bbi_email'), //, 
                'receiver_name' => "",
                'mail_message' => 'Campaign ' . $campaign->name . 'has been closed.'
            ];
            Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($owner_sa_emails) {
                //$message->bcc($owner_sa_emails->toArray())->subject('User campaign closed. - Billboards India');
                $message->bcc($owner_sa_emails->toArray())->subject('User campaign closed. - Advertising Marketplace');
            });
            return response()->json(["status" => "1", "message" => "The campaign has been successfully closed."]);
        } else {
            return response()->json(["status" => "0", "message" => "There was a technical error in closing the campaign. Please try again."]);
        }
    }

    public function searchCampaigns($searchTerm) {
        $searchTerm = strtolower($searchTerm);
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        if ($user_mongo['user_type'] == "owner") {
            $campaign_ids_for_owner = ProductBooking::where('product_owner', '=', $user_mongo['client_mongo_id'])
                            ->pluck('campaign_id')->toArray();
            $campaigns = Campaign::whereIn('id', $campaign_ids_for_owner)
                            ->where(function($q) use ($searchTerm) {
                                $q->where('user_full_name', 'like', "%$searchTerm%")
                                ->orWhere('user_phone', 'like', "%$searchTerm%")
                                ->orWhere('user_email', 'like', "%$searchTerm%")
                                ->orWhere('name', 'like', "%$searchTerm%")
                                ->orWhere('start_date', 'like', "%$searchTerm%")
                                ->orWhere('end_date', 'like', "%$searchTerm%");
                            })->get();
            ;
        } else {
            $campaigns = Campaign::where('user_full_name', 'like', "%$searchTerm%")
                    ->orWhere('user_phone', 'like', "%$searchTerm%")
                    ->orWhere('user_email', 'like', "%$searchTerm%")
                    ->orWhere('name', 'like', "%$searchTerm%")
                    ->orWhere('start_date', 'like', "%$searchTerm%")
                    ->orWhere('end_date', 'like', "%$searchTerm%")
                    ->get();
        }
        return response()->json($campaigns);
    }

    /*
      /////////// Floating campaign section ////////////
     */

    public function floatingCampaignPdf(Request $request) {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }

        // loop through the product_arr
        $products = $input['product_arr'];
        $temp_upload_path = base_path() . '/html/uploads/temp';
        // TODO: need to write a job to clean the temp directly every night

        $total_cost = 0;
        $total_impressions = 0;
        $unique_types = [];
        $image_name_arr = [];
        $i = 0; // to access which product_arr item we're traversing
        foreach ($products as $product) {
            $total_cost += isset($product['price']) ? $product['price'] : 0;
            $total_impressions += isset($product['impressions']) ? $product['impressions'] : 0;
            if (isset($product['type']) && !in_array($product['type'], $unique_types)) {
                array_push($unique_types, $product['type']);
            }
            $total_types = count($unique_types);
            // save image for each product
            $img = $request->product_arr[$i]['image'];
            $img->move($temp_upload_path, $img->getClientOriginalName());
            array_push($image_name_arr, $temp_upload_path . "/" . $img->getClientOriginalName());
            $i++;
        }

        $campaign_report = [
            "products" => $products,
            "total_cost" => $total_cost,
            "total_impressions" => $total_impressions,
            "total_types" => $total_types,
            "image_name_arr" => $image_name_arr
        ];
        // generate pdf     
        $pdf = PDF::loadView('pdf.floating_campaign_proposal_pdf', $campaign_report);
        if (!empty($pdf)) {
            return $pdf->download("Campaign Proposals.pdf");
        } else {
            return response()->json(["status" => 0, "message" => "PDF could not be generated."]);
        }
    }

    /*
      /////////// Floating campaign section ends ////////////
     */

    public function saveNonUserCampaign(Request $request) {
		
		 if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
		
        $this->validate($this->request, [
            'name' => 'required',
            'org_name' => 'required',
         //   'org_contact_name' => 'required',
            'org_contact_email' => 'required',
            'org_contact_phone' => 'required'
                ], [
            'name.required' => 'Campaign name is required',
            'org_name.required' => 'Organization name is required',
           // 'org_contact_name.required' => 'Contact name is required',
            'org_contact_email.required' => 'Contact phone is required',
            'org_contact_phone.required' => 'Contact email is required'
                ]
        );
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo['user_id'])->first();
        $client_type = $user->client->client_type->type;
		
		$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];

		if (isset($input['client'])) {
            $client = ClientMongo::where('id', '=', $input['client'])->first();
        }else{
			$client = ClientMongo::where('id', '=', $user_mongo['client_mongo_id'])->first();
		}
		
        $campaign_obj = new Campaign;
		$campaign_obj->client_id = isset($client) ? $client->client_id : "";
		$campaign_count = Campaign::count();
		$newSiteNo = $campaign_count+1;
		$siteNo = str_pad($newSiteNo, 6, '0', STR_PAD_LEFT);
		$siteNo1 = '_'.$siteNo;
		
		//campaign unique ID duplicate start
		$campaign_count = Campaign::latest()->first();
		$campaign_code_explode = explode("_", $campaign_count->cid);
		$uid_cid = '_'.str_pad(end($campaign_code_explode)+1, 6, '0', STR_PAD_LEFT);	

		//campaign unique ID duplicate end
		
		$buyer_id = '000'.$campaign_obj->client_id;
		
        $campaign_obj->id = $camp_id = uniqid();
        //$campaign_obj->cid = $this->generatecampaignID(); 
		//$campaign_obj->cid = 'AMP_'.'ABI'.$buyer_id.$siteNo1; 
		$campaign_obj->cid = 'AMP_'.'ABI'.$buyer_id.$uid_cid; 
		//echo "<pre>";print_r($campaign_obj->cid);exit;
        $campaign_obj->org_name = isset($this->input['org_name']) ? $this->input['org_name'] : "";
        $campaign_obj->org_contact_name = isset($this->input['org_contact_name']) ? $this->input['org_contact_name'] : "";
        $campaign_obj->org_contact_email = isset($this->input['org_contact_email']) ? $this->input['org_contact_email'] : "";
        $campaign_obj->org_contact_phone = isset($this->input['org_contact_phone']) ? $this->input['org_contact_phone'] : "";
        $campaign_obj->name = isset($this->input['name']) ? $this->input['name'] : "";
        $campaign_obj->slug = str_replace(" ", "-", strtolower($this->input['name']));
        $campaign_obj->referred_by = isset($this->input['org_contact_refered']) ? $this->input['org_contact_refered'] : "";
        $campaign_obj->created_by = $user_mongo['id'];
        $campaign_obj->est_budget = isset($this->input['est_budget']) ? (int) $this->input['est_budget'] : 0;
        if ($client_type == "bbi") {
            $client_mongo = ClientMongo::where('client_id', '=', $user->client->id)->first();
            $campaign_obj->type = Campaign::$CAMPAIGN_USER_TYPE['bbi'];
            $campaign_obj->client_mongo_id = $client_mongo->id;
            $campaign_obj->client_name = $client_mongo->name;
        } else {
            $client_mongo = ClientMongo::where('client_id', '=', $user->client->id)->first();
            $campaign_obj->type = Campaign::$CAMPAIGN_USER_TYPE['owner'];
            $campaign_obj->client_mongo_id = $client_mongo->id;
            $campaign_obj->client_name = $client_mongo->name;
        }
        $campaign_obj->status = Campaign::$CAMPAIGN_STATUS['campaign-preparing'];
        if ($campaign_obj->save()) {
            if (isset($this->input['from_shortlisted']) && $this->input['from_shortlisted'] == "1") {
                $shortlisted_products = ShortListedProduct::where('client_mongo_id', '=', $user_mongo['client_mongo_id'])->get();
                foreach ($shortlisted_products as $slp) {
                    $unavailable_products = [];
                    $locked_products = CampaignProduct::where([
                                ['product_id', '=', $slp->product_id],
                                ['product_status', '=', CampaignProduct::$PRODUCT_STATUS['locked']],
                                ['from_date', '<=', $campaign_obj->end_date],
                                ['to_date', '>=', $campaign_obj->start_date]
                            ])->get();
                    if (count($locked_products) > 0) {
                        array_push($unavailable_products, $slp);
                    } else {
                        $campaign_product = new CampaignProduct;
                        $campaign_product->campaign_id = $campaign_obj->id;
                        $campaign_product->product_id = $slp->product_id;
                        $campaign_product->from_date = $campaign_obj->start_date;
                        $campaign_product->to_date = $campaign_obj->end_date;
                        $campaign_product->price = $campaign_obj->default_price;
                        $campaign_product->product_status = CampaignProduct::$PRODUCT_STATUS['proposed'];
                        $campaign_product->product_owner = $slp->client_mongo_id;
                        $campaign_product->save();
                        ShortListedProduct::where([
                            ['product_id', '=', $slp->product_id],
                            ['client_mongo_id', '=', $user_mongo['client_mongo_id']],
                        ])->delete();
                    }
                }
                if (count($unavailable_products) > 0) {
                    return response()->json(["status" => "1", "camp_id" => $camp_id, "message" => "campaign saved successfully. But some shortlisted products were not added as they were unavailalble.", 'unavailable_product' => $unavailable_products]);
                } else {
                    return response()->json(["status" => "1", "camp_id" => $camp_id, "message" => "campaign saved successfully."]);
                }
            } else {
                return response()->json(["status" => "1", "camp_id" => $camp_id, "message" => "campaign saved successfully."]);
            }
        } else {
            return response()->json(["status" => "0", "message" => "Failed to save campaign."]);
        }
    }

public function getUserCampaignsForOwner(Request $request) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo']; 

        if (!isset($user_mongo) || !isset($user_mongo['client_mongo_id'])) {
            return response()->json(['status' => 0, 'message' => 'You are not authorized to view these campaigns.']);
        }

        $campaign_product_ids = ProductBooking::where('product_owner', '=', $user_mongo['client_mongo_id'])->pluck('campaign_id');
        
        $campaign_list[] = '';
		$new_stripe_percent_amount = 0;
		
		if ($request->isJson()) {
			$input = $request->json()->all();
		} else {
			$input = $request->all();
		}
		
		if (isset($this->input['tab_status'])){
			if($this->input['tab_status'] == 'insertion_order'){
				$campaigns = Campaign::whereIn('id', $campaign_product_ids)
					->where('type', '<>', Campaign::$CAMPAIGN_USER_TYPE['owner'])
					->where('status', '=', Campaign::$CAMPAIGN_STATUS['booking-requested'])
					->orderBy('updated_at', 'desc')
					->get();
			}else if($this->input['tab_status'] == 'scheduled'){
				$campaigns = Campaign::whereIn('id', $campaign_product_ids)
					->where('type', '<>', Campaign::$CAMPAIGN_USER_TYPE['owner'])
					->where('status', '=', Campaign::$CAMPAIGN_STATUS['scheduled'])
					->orderBy('updated_at', 'desc')
					->get();
			}else if($this->input['tab_status'] == 'running'){
				$campaigns = Campaign::whereIn('id', $campaign_product_ids)
					->where('type', '<>', Campaign::$CAMPAIGN_USER_TYPE['owner'])
					->where('status', '=', Campaign::$CAMPAIGN_STATUS['running'])
					->orderBy('updated_at', 'desc')
					->get();
			}else if($this->input['tab_status'] == 'closed'){
				$campaigns = Campaign::whereIn('id', $campaign_product_ids)
					->where('type', '<>', Campaign::$CAMPAIGN_USER_TYPE['owner'])
					->where('status', '!=', Campaign::$CAMPAIGN_STATUS['campaign-preparing'])
					->where('status', '!=', Campaign::$CAMPAIGN_STATUS['booking-requested'])
					->orderBy('updated_at', 'desc')
					->get();
			}else if($this->input['tab_status'] == 'cancelled'){
				$campaigns = Campaign::whereIn('id', $campaign_product_ids)
					->where('type', '<>', Campaign::$CAMPAIGN_USER_TYPE['owner'])
					->where('status', '=', Campaign::$CAMPAIGN_STATUS['deleted-cancelled'])
					->orderBy('updated_at', 'desc')
					->get();
			}else{
				return response()->json(['status' => 0, 'message' => "Tab is incorrect."]);
			}
		}else{
			$campaigns = Campaign::whereIn('id', $campaign_product_ids)
					->where('type', '<>', Campaign::$CAMPAIGN_USER_TYPE['owner'])
					->orderBy('updated_at', 'desc')
					->get();
		}
        foreach ($campaigns as $key => $val) {
			$tax_percentage_booking = 0;
			$tax_percentage_amount = 0;
			$total_paid_cal = 0;
			$tax_percentage_booking_tot = 0;
			$tax_percentage_amount_tot = 0;
            $campaign_payments = CampaignPayment::where('campaign_id', '=', $val->id)->get();
			$total_paid_cal = $campaign_payments->sum('amount');
			if($total_paid_cal > 0){
				$product_start_date = ProductBooking::where([
							['campaign_id', '=', $val->id],
						])->select("booked_from","booked_to","product_id")->orderBy('booked_from', 'asc')->first();
				if (!empty($product_start_date)) {
					$val['start_date'] = $product_start_date->booked_from;
					$val['end_date'] = $product_start_date->booked_to;
					if(!is_null($product_start_date->product_id)){
						$product_area =Product::where('id', '=', $product_start_date->product_id)->select("area")->first();
						if(!is_null($product_area)){	
							$area_time_zone_value = Area::select("area_time_zone","area_time_zone_type")->where('id', '=', $product_area->area)->first();
							if(!is_null($area_time_zone_value)){
								$area_time_zone_type = $area_time_zone_value['area_time_zone_type'];
								if(!is_null($area_time_zone_type)){
									$start_time = new DateTime( $val['start_date'] );
									$end_time = new DateTime( $val['end_date'] );
									$laTimezone = new DateTimeZone($area_time_zone_type);
									$start_time->setTimeZone( $laTimezone );
									$end_time->setTimeZone( $laTimezone );
									$val['start_date'] = $start_time->format( 'Y-m-d H:i:s' );
									$val['end_date'] = $end_time->format( 'Y-m-d H:i:s' );
								}
							}
						}
					}
				}
				if (isset($this->input['tab_status'])){
					if($this->input['tab_status'] == 'scheduled'){
						if(date('Y-m-d') > $val['start_date'] || date('Y-m-d') > $val['end_date']){
							unset($campaigns[$key]);
							continue;
						}
					}if($this->input['tab_status'] == 'running'){
						if(date('Y-m-d') < $val['start_date'] && date('Y-m-d') < $val['end_date']){
							unset($campaigns[$key]);
							continue;
						}
					}if($this->input['tab_status'] == 'closed'){
						if($val->status == Campaign::$CAMPAIGN_STATUS['stopped'] || $val->status == Campaign::$CAMPAIGN_STATUS['suspended'] || date('Y-m-d') > $val['end_date']){

						}else{
							unset($campaigns[$key]);
							continue;
						}
					} 
				}
				//$campaign_product = ProductBooking::where('campaign_id', '=', $val->id)->get();


				$total_price = ProductBooking::where('campaign_id', '=', $val->id)->where('product_owner', '=', $user_mongo['client_mongo_id'])->sum('price');
				if ($total_price == 0) {
					$total_price = ProductBooking::where('campaign_id', '=', $val->id)->where('product_owner', '=', $user_mongo['client_mongo_id'])->sum('admin_price');
				}
				//$val['total_price'] = $total_price;

				if($user_mongo['user_type'] == 'owner'){
					$getcampaigntot = ProductBooking::where('campaign_id', '=', $val->id)->get();
					$offershortlistedsum = 0;
					$offerpriceperselectedDates = 0;
					$newofferStripepercentamt = 0;
					$camptot = 0;
					$products_arr = [];
					$cancellationArray=[];
					if (isset($getcampaigntot) && count($getcampaigntot) > 0) {
						foreach ($getcampaigntot as $getcampaigntot) {
							$tax_percentage_booking_tot = 0;
							$tax_percentage_amount_tot = 0;
							$getcampaigntotproduct =Product::where('id', '=', $getcampaigntot->product_id)->first();
							$bookedfrom[] = strtotime($getcampaigntot->booked_from);
							$bookedto[] = strtotime($getcampaigntot->booked_to);
							$getproductDetails =Product::where('id', '=', $getcampaigntot->product_id)->first();
							$diff=date_diff(date_create($getcampaigntot->booked_from),date_create($getcampaigntot->booked_to));
							$daysCount = $diff->format("%a");
							$daysCountCPM = $daysCount + 1;
							$perdayprice = $getproductDetails->default_price/28;
							if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
								$price = $getcampaigntot->price*$getcampaigntot->quantity;
								if($total_paid_cal != 0){
									if(isset($getcampaigntot->tax_percentage)){
										$tax_percentage_booking_tot = $getcampaigntot->tax_percentage;
									}else{
										$tax_percentage_booking_tot = 0;
									}
								}else{
									$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
								}
								if($tax_percentage_booking_tot != 0){
									$tax_percentage_amount_tot = ($price * ($tax_percentage_booking_tot))/100;
								}
								$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
								$camptot += $price+$tax_percentage_amount_tot;
							}else{
								$priceperselectedDates = $getcampaigntot->price;
								if($total_paid_cal != 0){
									if(isset($getcampaigntot->tax_percentage)){
										$tax_percentage_booking_tot = $getcampaigntot->tax_percentage;
									}else{
										$tax_percentage_booking_tot = 0;
									}
								}else{
									$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
								}
								if($tax_percentage_booking_tot != 0){
									$tax_percentage_amount_tot = ($priceperselectedDates * ($tax_percentage_booking_tot))/100;
								}
								$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
								$camptot += ($priceperselectedDates*$getcampaigntot->quantity)+($tax_percentage_amount_tot*$getcampaigntot->quantity);
							}
						}
					}
					
					//$campaign_products = ProductBooking::where('campaign_id', '=', $val->id)->get();
					$campaign_products = ProductBooking::where([
						['campaign_id', '=', $val->id],
						['product_owner', '=', $user_mongo['client_mongo_id']]
					])->get();
					$val['total_paid_seller'] = 0;
					if (isset($campaign_products) && count($campaign_products) > 0) {
						foreach ($campaign_products as $campaign_product) {
							$campaign_product->tax_percentage_amount = 0;
							$tax_percentage_booking = 0;
							$tax_percentage_amount = 0;
							$getproductDetails =Product::where('id', '=', $campaign_product->product_id)->first();
							$diff=date_diff(date_create($campaign_product->booked_from),date_create($campaign_product->booked_to));
							$daysCount = $diff->format("%a");
							$daysCountCPM = $daysCount + 1;

							if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
								$offerDetails = MakeOffer::where([
									['campaign_id', '=', $campaign_product->campaign_id],
									['status', '=', 20],
								])->get();
								if(isset($offerDetails) && count($offerDetails)==1){
									foreach($offerDetails as $offerDetails){
										$offerprice = $offerDetails->price;
										$stripe_percent=$getproductDetails->stripe_percent;
										$price = $campaign_product->price;
										$priceperday = $price;
										$priceperselectedDates = $priceperday;
										$newpricepercentage = ($priceperselectedDates/$camptot) * 100;
										//$newofferprice = ($offerprice * ($newpricepercentage))/100;
										if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
											$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
											$newofferprice = $newofferprice_wq*$campaign_product->quantity;
										}else{
											$newofferprice = ($offerprice * ($newpricepercentage))/100;
										}
										$newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
										$offerpriceperselectedDates = $newofferprice;
									}
								}else{
									$priceperselectedDates = $campaign_product->price;
									$offerprice = $campaign_product->price;
									$stripe_percent=$getproductDetails->stripe_percent;
									if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
										$newofferprice = $offerprice*$campaign_product->quantity;
									}else{
										$newofferprice = $offerprice ;
									}
									$newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
									$offerpriceperselectedDates = $newofferprice;
								}
								if($total_paid_cal != 0){
									if(isset($campaign_product->tax_percentage)){
										$tax_percentage_booking = $campaign_product->tax_percentage;
									}else{
										$tax_percentage_booking = 0;
									}
								}else{
									$tax_percentage_booking = $getproductDetails->tax_percentage;
								}
								if($tax_percentage_booking != 0){
									$tax_percentage_amount = ($offerpriceperselectedDates * ($tax_percentage_booking))/100;
								}
								$campaign_product->tax_percentage_amount = round($tax_percentage_amount,2);
								$offershortlistedsum+= $offerpriceperselectedDates+$campaign_product->tax_percentage_amount;
								$val['total_paid_seller'] += (($offerpriceperselectedDates+$newofferStripepercentamt+$campaign_product->tax_percentage_amount)* ($stripe_percent))/100;
							}else{
								$offerDetails = MakeOffer::where([
									['campaign_id', '=', $campaign_product->campaign_id],
									['status', '=', 20],
								])->get();
								if(isset($offerDetails) && count($offerDetails)==1){
									foreach($offerDetails as $offerDetails){
										$offerprice = $offerDetails->price;
										$price = $getproductDetails->default_price;
										$stripe_percent=$getproductDetails->stripe_percent;
										//$priceperday = $price/28;//exit;
										//$priceperselectedDates = $priceperday * $daysCountCPM; 
										
										///variable_offer///
										$priceperday = $campaign_product->price;
										$priceperselectedDates = $priceperday;
										
										
										$newpricepercentage = ($priceperselectedDates/$camptot) * 100;
										//$newofferprice = ($offerprice * ($newpricepercentage))/100;//exit;
										if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
											$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
											$newofferprice = $newofferprice_wq*$campaign_product->quantity;
										}else{
											$newofferprice = ($offerprice * ($newpricepercentage))/100;
										}
										$newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
										$offerpriceperselectedDates = $newofferprice;
									}
								}else{
									$priceperselectedDates = $campaign_product->price;
									$offerprice = $campaign_product->price;
									$stripe_percent=$getproductDetails->stripe_percent;
									if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
										$newofferprice = $offerprice*$campaign_product->quantity;
									}else{
										$newofferprice = $offerprice;
									}
									$newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
									$offerpriceperselectedDates = $newofferprice;
								}
								if($total_paid_cal != 0){
									if(isset($campaign_product->tax_percentage)){
										$tax_percentage_booking = $campaign_product->tax_percentage;
									}else{
										$tax_percentage_booking = 0;
									}
								}else{
									$tax_percentage_booking = $getproductDetails->tax_percentage;
								}
								if($tax_percentage_booking != 0){
									$tax_percentage_amount = ($offerpriceperselectedDates * ($tax_percentage_booking))/100;
								}
								$campaign_product->tax_percentage_amount = round($tax_percentage_amount,2);
								$offershortlistedsum+= $offerpriceperselectedDates+$campaign_product->tax_percentage_amount;
								$val['total_paid_seller'] += (($offerpriceperselectedDates+$newofferStripepercentamt+$campaign_product->tax_percentage_amount)* ($stripe_percent))/100;
							}
							array_push($products_arr, array_merge(Product::where('id', '=', $campaign_product->product_id)->first()->toArray(), $campaign_product->toArray(),$cancellationArray));
						}
						$actbudg = $products_arr;
						$res = array_sum(array_map(function($item) { 
							if(isset($item['quantity']) && $item['quantity'] != '' && $item['quantity'] != 0){
								return ($item['price']+$item['tax_percentage_amount'])*$item['quantity']; 
							}else{
								return $item['price']+$item['tax_percentage_amount']; 
							}
						}, $actbudg));
					}
					
					$gross_fee_price = 0;
					if(isset($val->gross_fee_percentage) && $val->gross_fee_percentage > 0){
						$gross_fee_price = ($offershortlistedsum*$val->gross_fee_percentage)/100;
					}else{
						$gross_fee_price = 0;
					}
					
					if($offershortlistedsum!=0){
						$act_budget = $offershortlistedsum+$gross_fee_price;
						$val['total_price'] = $offershortlistedsum+$gross_fee_price; 
					}else{
						$act_budget = $res+$gross_fee_price;
						$val['total_price'] = $act_budget+$gross_fee_price;
					}
					
					if (isset($campaign_payments) && count($campaign_payments) > 0) {
						$total_paid = $campaign_payments->sum('amount');
						$val['total_paid'] = $val['total_paid_seller']; 
					} else {
						$val['total_paid'] = 0;
					}
					
				}else{
					if (isset($campaign_payments) && count($campaign_payments) > 0) {
						$total_paid = $campaign_payments->sum('amount');
						$val['total_paid'] = $total_paid; 
					} else {
						$val['total_paid'] = 0;
					}
				}

				$val['no_products'] = count($campaign_product);

				$campaign_list[] = $val;
			}
        }
        return response()->json($campaign_list);
    }

    public function getCampaignDetailsForOwner($campaign_id) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        if (!isset($user_mongo) || !isset($user_mongo['client_mongo_id'])) {
            return response()->json(['status' => 0, 'message' => 'You are not authorized to view these campaigns.']);
        }
        
        $shortlisted_products_arr = [];
        $shortlistedsum = 0;
		$shortlistedsumcpm = 0;
        $cpmsum = 0;
        $impressionSum = 0;
        $impressionSum4 = 0;
		
        $offershortlistedsum = 0;
		$offershortlistedsumcpm = 0;
        $negotiatedsum = 0;
        $offercpmsum = 0;
        $newofferStripepercentamtSum = 0;
        $newprocessingfeeamtSum = 0;
		$tax_percentage_booking = 0;
		$tax_percentage_amount = 0;
		$tax_percentage_booking_tot = 0;
		$tax_percentage_amount_tot = 0;
        $tax_percentage_amount_sum = 0;
		$offer_applied = 0;
        $campaign = Campaign::where('id', '=', $campaign_id)->first();
		//echo '<pre>';print_r($campaign);exit;
		/*$user_mongo_data = UserMongo::where('id', '=', $campaign->created_by)->first();
        $campaign->first_name = $user_mongo_data->first_name;
        $campaign->last_name = $user_mongo_data->last_name;
        $campaign->company_name = $user_mongo_data->company_name;
        $campaign->email = $user_mongo_data->email;
        $campaign->phone = $user_mongo_data->phone;*/
        $campaign_products = ProductBooking::where([
                    ['campaign_id', '=', $campaign_id],
                    ['product_owner', '=', $user_mongo['client_mongo_id']]
                ])->get();
        $products_in_campaign = [];
		$camptot = 0;
		$getcampaigntot = ProductBooking::where([['campaign_id', '=', $campaign_id]])->get();
		$campaign->total_paid = CampaignPayment::where('campaign_id', '=', $campaign_id)->sum('amount');
		if (isset($getcampaigntot) && count($getcampaigntot) > 0) {
            foreach ($getcampaigntot as $getcampaigntot) {
				$tax_percentage_booking_tot = 0;
				$tax_percentage_amount_tot = 0;
                $getproductDetails =Product::where('id', '=', $getcampaigntot->product_id)->first();
                $diff=date_diff(date_create($getcampaigntot->booked_from),date_create($getcampaigntot->booked_to));
                $daysCount = $diff->format("%a");
                $daysCountCPM = $daysCount + 1;
				$perdayprice = $getproductDetails->default_price/28;

                if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
                    $price = $getcampaigntot->price*$getcampaigntot->quantity;
                    if($campaign->total_paid != 0){
						if(isset($getcampaigntot->tax_percentage)){
							$tax_percentage_booking_tot = $getcampaigntot->tax_percentage;
						}else{
							$tax_percentage_booking_tot = 0;
						}
					}else{
						$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
					}
					if($tax_percentage_booking_tot != 0){
						$tax_percentage_amount_tot = ($price * ($tax_percentage_booking_tot))/100;
					}
					$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
					$camptot += $price+$tax_percentage_amount_tot;
                }else{
					$priceperselectedDates = $getcampaigntot->price;
					
                    if($campaign->total_paid != 0){
						if(isset($getcampaigntot->tax_percentage)){
							$tax_percentage_booking_tot = $getcampaigntot->tax_percentage;
						}else{
							$tax_percentage_booking_tot = 0;
						}
					}else{
						$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
					}
					if($tax_percentage_booking_tot != 0){
						$tax_percentage_amount_tot = ($priceperselectedDates * ($tax_percentage_booking_tot))/100;
					}
					$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
					$camptot += ($priceperselectedDates*$getcampaigntot->quantity)+($tax_percentage_amount_tot*$getcampaigntot->quantity);
                }
            }
        }
		$products_in_campaign_arr = array();
        if (isset($campaign_products) && count($campaign_products) > 0) {
			//RFP search criteria
			$f = $campaign_products;
            //echo "<pre>";print_r($f);exit;
             $campaigntotal = 0;
             foreach($f as $f){
                 $campaigntotal+= $f->price;
             }
			$campaign->rfp_search_criteria_preview = array();
			$campaign->area_rfp_details = array();
			
            foreach ($campaign_products as $campaign_product) {
				$campaign_product->tax_percentage_amount_shortlist = 0;
				$campaign_product->tax_percentage_amount = 0;
				$tax_percentage_booking = 0;
				$tax_percentage_amount = 0;
                // adding campaign products to it.
                $getproductDetails = Product::where('id', '=', $campaign_product->product_id)->first();
				$products_in_campaign_arr[] = $getproductDetails;
				$area_time_zone_value = Area::select("area_time_zone","area_time_zone_type")->where('id', '=', $getproductDetails->area)->first();
				$campaign_product->area_time_zone_type = $area_time_zone_value['area_time_zone_type'];
				
                /*CPM Calculation*/
                $diff=date_diff(date_create($campaign_product->booked_from),date_create($campaign_product->booked_to));
                $daysCount = $diff->format("%a");
                $daysCountCPM = $daysCount + 1;

                if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
					
					$offerDetails = MakeOffer::where([
                            ['campaign_id', '=', $campaign_product->campaign_id],
                            ['status', '=', 20],
                        ])->get();
					
					if(isset($offerDetails) && count($offerDetails)==1){
						foreach($offerDetails as $offerDetails){
							$offerprice = $offerDetails->price;
							$stripe_percent=$getproductDetails->stripe_percent;
							$prd_arr = array($getproductDetails->id);
							
							$getProductBookingID = ProductBooking::where([
								['campaign_id', '=', $campaign_product->campaign_id],
							])->whereIn('product_id', $prd_arr)->get();
							
							$deleteProductStatus = DeleteProduct::where([
								['campaign_id', '=', $campaign_product->campaign_id],
							])->whereIn('product_id', $prd_arr)->whereIn('productbookingid', array($campaign_product->id))->orderBy('created_at', 'desc')->first();

							if(isset($deleteProductStatus) && $deleteProductStatus!=''){
								$campaign_product->deleteProductStatus = $deleteProductStatus->status;
							}else{
								$campaign_product->deleteProductStatus = '';
							}
							
							$price = $campaign_product->price;

							$priceperday = $price;
							$priceperselectedDates = $priceperday;
							$newpricepercentage = ($priceperselectedDates/$camptot) * 100;

							//$newofferprice = ($offerprice * ($newpricepercentage))/100;
							if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
								$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
								$newofferprice = $newofferprice_wq*$campaign_product->quantity;
							}else{
								$newofferprice = ($offerprice * ($newpricepercentage))/100;
							}
							$offerpriceperselectedDates = $newofferprice;
							$campaign_product->stripe_percent = $stripe_percent;

							$negotiatedprice = $getproductDetails->negotiatedCost;
							$negotiatedpriceperday = $negotiatedprice;
							$negotiatedpriceperselectedDates = $negotiatedpriceperday;
						}
						$offer_applied=1;
					}else{
						/*$price = $getproductDetails->default_price;
						$priceperday = $price;
						$priceperselectedDates = $priceperday;
						$shortlistedsum+= $priceperselectedDates;
						$campaign_product->price = $priceperselectedDates;
						$cpmsum+= $getproductDetails->cpm;
						$impressions = $getproductDetails->secondImpression;
						$impressionsperday = (int)($impressions);
						$impressionsperselectedDates = $impressionsperday;
						
						if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
							$impressionsperselectedDates = $impressionsperselectedDates;
						}else{
							$impressionsperselectedDates = 1;
						}
						$impressionSum+= $impressionsperselectedDates;
						$campaign_product->secondImpression = round($impressionsperselectedDates, 2);
						$cpmcal = ($shortlistedsum/$impressionSum) * 1000;
						$campaign_product->cpmperselectedDates = $cpmcal;
						$campaign_product->cpm = $cpmcal;
						$campaign_product->impressionsperselectedDates = $impressionsperselectedDates;
						$campaign_product->priceperselectedDates = $priceperselectedDates;
						*/
						
						
						$prd_arr = array($getproductDetails->id);
									
						$deleteProductStatus = DeleteProduct::where([
							['campaign_id', '=', $campaign_product->campaign_id],
						])->whereIn('product_id', $prd_arr)->whereIn('productbookingid', array($campaign_product->id))->orderBy('created_at', 'desc')->first();

						if(isset($deleteProductStatus) && $deleteProductStatus!=''){
							$campaign_product->deleteProductStatus = $deleteProductStatus->status;
						}else{
							$campaign_product->deleteProductStatus = '';
						}

						$offerprice = $campaign_product->price;
						$stripe_percent=$getproductDetails->stripe_percent;
						$price = $campaign_product->price;
						$priceperday = $price;
						$priceperselectedDates = $priceperday;
						//$newpricepercentage = ($priceperselectedDates/$camptot) * 100;
						
						if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
							$newofferprice = $offerprice*$campaign_product->quantity;
						}else{
							$newofferprice = $offerprice ;
						}
						$offerpriceperselectedDates = $newofferprice;
						$campaign_product->stripe_percent = $stripe_percent;
						
						$negotiatedprice = $getproductDetails->negotiatedCost;
						$negotiatedpriceperday = $negotiatedprice;
						$negotiatedpriceperselectedDates = $negotiatedpriceperday;
						
						
						
						
					}
					
					if($campaign->total_paid != 0){
						if(isset($campaign_product->tax_percentage)){
							$tax_percentage_booking = $campaign_product->tax_percentage;
						}else{
							$tax_percentage_booking = 0;
						}
					}else{
						$tax_percentage_booking = $getproductDetails->tax_percentage;
					}
					if($tax_percentage_booking != 0){
						$campaign_product->tax_percentage_amount_shortlist = ($priceperselectedDates * ($tax_percentage_booking))/100;
						$tax_percentage_amount = ($offerpriceperselectedDates * ($tax_percentage_booking))/100;
					}
					$campaign_product->tax_percentage_amount = round($tax_percentage_amount,2);
					$tax_percentage_amount_sum += round($tax_percentage_amount,2);
					$newofferStripepercentamt = (($newofferprice+$tax_percentage_amount) * ($stripe_percent))/100;
					$newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
					$shortlistedsum+= ($priceperselectedDates+$campaign_product->tax_percentage_amount_shortlist)*$campaign_product->quantity;
					$shortlistedsumcpm+= $priceperselectedDates*$campaign_product->quantity;
					$campaign_product->price = $priceperselectedDates;
	
					$negotiatedsum+= $negotiatedpriceperselectedDates;
					
					$offershortlistedsum+= $offerpriceperselectedDates+$campaign_product->tax_percentage_amount;
					$campaign_product->offerprice = $offerpriceperselectedDates+$campaign_product->tax_percentage_amount;
					$offershortlistedsumcpm+= $offerpriceperselectedDates;
					$cpmsum+= $getproductDetails->cpm;
					$impressions = $getproductDetails->secondImpression;
					$impressionsperday = (float)($impressions/7);
					$impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
					
					if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
						$impressionsperselectedDates = $impressionsperselectedDates;
					}else{
						$impressionsperselectedDates = 1;
					}
					$impressionSum+= $impressionsperselectedDates*$campaign_product->quantity;
					$campaign_product->secondImpression = round($impressionsperselectedDates, 2);
					$cpmcal = ($priceperselectedDates/$impressionsperselectedDates) * 1000;
					$offercpmcal = ($offerpriceperselectedDates/$impressionsperselectedDates) * 1000;
					$campaign_product->cpmperselectedDates = $cpmcal;
					$campaign_product->offercpmperselectedDates = $offercpmcal;
					$campaign_product->cpm = $cpmcal;
					$campaign_product->offercpm = $offercpmcal;
					$campaign_product->impressionsperselectedDates = $impressionsperselectedDates;
					$campaign_product->priceperselectedDates = $priceperselectedDates;
					$campaign_product->negotiatedpriceperselectedDates = $negotiatedpriceperselectedDates;
					$campaign_product->offerpriceperselectedDates = $offerpriceperselectedDates+$campaign_product->tax_percentage_amount;
					
					$campaign_product->new_stripe_percent_amount = $newofferStripepercentamt;
					$campaign_product->newprocessingfeeamt = $newprocessingfeeamt;
	
					$newofferStripepercentamtSum += (($offerpriceperselectedDates+$campaign_product->tax_percentage_amount)* ($stripe_percent))/100;
					$newprocessingfeeamtSum += $newprocessingfeeamt;
					
					$campaign_product->productbookingid = $campaign_product->id;
					
					
                }else{
					
					$offerDetails = MakeOffer::where([
                            ['campaign_id', '=', $campaign_product->campaign_id],
                            ['status', '=', 20],
                        ])->get();
					
					if(isset($offerDetails) && count($offerDetails)==1){
						foreach($offerDetails as $offerDetails){
							$offerprice = $offerDetails->price;
							$stripe_percent=$getproductDetails->stripe_percent;
								 
							$price = $getproductDetails->default_price;
							$prd_arr = array($getproductDetails->id);                                                
							$deleteProductStatus = DeleteProduct::where([
								['campaign_id', '=', $campaign_product->campaign_id],
							])->whereIn('product_id', $prd_arr)->whereIn('productbookingid', array($campaign_product->id))->orderBy('created_at', 'desc')->first();

							if(isset($deleteProductStatus) && $deleteProductStatus!=''){
									$campaign_product->deleteProductStatus = $deleteProductStatus->status; 
							}else{
								$campaign_product->deleteProductStatus = '';
							}   

							//$priceperday = $price/28;
							//$priceperselectedDates = $priceperday * $daysCountCPM;
							
							///variable_offer///
							$priceperday = $campaign_product->price;
							$priceperselectedDates = $priceperday;
							
							
							$newpricepercentage = ($priceperselectedDates/$camptot) * 100;
							//$newofferprice = ($offerprice * ($newpricepercentage))/100;
							if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
								$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
								$newofferprice = $newofferprice_wq*$campaign_product->quantity;
							}else{
								$newofferprice = ($offerprice * ($newpricepercentage))/100;
							}
							$offerpriceperselectedDates = $newofferprice;
							$campaign_product->stripe_percent = $stripe_percent;

							$negotiatedprice = $getproductDetails->negotiatedCost;
							$negotiatedpriceperday = $negotiatedprice/28;
							$negotiatedpriceperselectedDates = $negotiatedpriceperday * $daysCountCPM;
						}
						$offer_applied=1;
                    }else{
						/*$price = $getproductDetails->default_price;
						$priceperday = $price/28;
						$priceperselectedDates = $priceperday * $daysCountCPM;
						$shortlistedsum+= $priceperselectedDates;
						$campaign_product->price = $priceperselectedDates;
						$cpmsum+= $getproductDetails->cpm;
						$impressions = $getproductDetails->secondImpression;
						$impressionsperday = (int)($impressions/7);
						$impressionsperselectedDates = $impressionsperday * $daysCountCPM;
						
						if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
							$impressionsperselectedDates = $impressionsperselectedDates;
						}else{
							$impressionsperselectedDates = 1;
						}
						$impressionSum+= $impressionsperselectedDates;
						$campaign_product->secondImpression = round($impressionsperselectedDates, 2);
						$cpmcal = ($shortlistedsum/$impressionSum) * 1000;
						$campaign_product->cpmperselectedDates = $cpmcal;
						$campaign_product->cpm = $cpmcal;
						$campaign_product->impressionsperselectedDates = $impressionsperselectedDates;
						$campaign_product->priceperselectedDates = $priceperselectedDates;
						*/
						
						
						$offerprice = $campaign_product->price;
						$stripe_percent=$getproductDetails->stripe_percent;
						$price = $campaign_product->price;
						$prd_arr = array($getproductDetails->id);
						$deleteProductStatus = DeleteProduct::where([
							['campaign_id', '=', $campaign_product->campaign_id],
						])->whereIn('product_id', $prd_arr)->whereIn('productbookingid', array($campaign_product->id))->orderBy('created_at', 'desc')->first();
						if(isset($deleteProductStatus) && $deleteProductStatus!=''){
								$campaign_product->deleteProductStatus = $deleteProductStatus->status;
						}else{
							$campaign_product->deleteProductStatus = '';
						}   
						
						$priceperday = $price;
						$priceperselectedDates = $priceperday;
						//$newpricepercentage = ($priceperselectedDates/$camptot) * 100;

						if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
						$newofferprice = $offerprice*$campaign_product->quantity;
						}else{
							$newofferprice = $offerprice;
						}
						$offerpriceperselectedDates = $newofferprice;
						$campaign_product->stripe_percent = $stripe_percent;

						$negotiatedprice = $getproductDetails->negotiatedCost;
						$negotiatedpriceperday = $negotiatedprice/28;
						$negotiatedpriceperselectedDates = $negotiatedpriceperday * $daysCountCPM;
												
					}
					if($campaign->total_paid != 0){
						if(isset($campaign_product->tax_percentage)){
							$tax_percentage_booking = $campaign_product->tax_percentage;
						}else{
							$tax_percentage_booking = 0;
						}
					}else{
						$tax_percentage_booking = $getproductDetails->tax_percentage;
					}
					if($tax_percentage_booking != 0){
						$campaign_product->tax_percentage_amount_shortlist = ($priceperselectedDates * ($tax_percentage_booking))/100;
						$tax_percentage_amount = ($offerpriceperselectedDates * ($tax_percentage_booking))/100;
					}
					$campaign_product->tax_percentage_amount = round($tax_percentage_amount,2);  
					$tax_percentage_amount_sum += round($tax_percentage_amount,2);
					$newofferStripepercentamt = (($newofferprice+$tax_percentage_amount) * ($stripe_percent))/100;
					$newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
					$shortlistedsum+= ($priceperselectedDates+$campaign_product->tax_percentage_amount_shortlist)*$campaign_product->quantity;
					$shortlistedsumcpm += $priceperselectedDates*$campaign_product->quantity;
					$negotiatedsum+= $negotiatedpriceperselectedDates;
					$campaign_product->price = $priceperselectedDates;

					$offershortlistedsum+= $offerpriceperselectedDates+$campaign_product->tax_percentage_amount;

					$offershortlistedsumcpm += $offerpriceperselectedDates;
					$campaign_product->offerprice = $offerpriceperselectedDates+$campaign_product->tax_percentage_amount;
					$cpmsum+= $getproductDetails->cpm;
					$impressions = $getproductDetails->secondImpression;
					$impressionsperday = (float)($impressions/7);
					$impressionsperselectedDates = $impressionsperday * $daysCountCPM;
					
					if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
						$impressionsperselectedDates = $impressionsperselectedDates;
					}else{
						$impressionsperselectedDates = 1;
					}
					$impressionSum+= $impressionsperselectedDates*$campaign_product->quantity;
					$campaign_product->secondImpression = round($impressionsperselectedDates, 2);
					$cpmcal = ($priceperselectedDates/$impressionsperselectedDates) * 1000;
					$offercpmcal = ($offerpriceperselectedDates/$impressionsperselectedDates) * 1000;
					$campaign_product->cpmperselectedDates = $cpmcal;
					$campaign_product->offercpmperselectedDates = $offercpmcal;
					$campaign_product->cpm = $cpmcal;
					$campaign_product->offercpm = $offercpmcal;
					$campaign_product->impressionsperselectedDates = $impressionsperselectedDates;
					$campaign_product->priceperselectedDates = $priceperselectedDates;
					$campaign_product->negotiatedpriceperselectedDates = $negotiatedpriceperselectedDates;
					$campaign_product->offerpriceperselectedDates = $offerpriceperselectedDates+$campaign_product->tax_percentage_amount;
					
					$campaign_product->new_stripe_percent_amount = $newofferStripepercentamt;
					$campaign_product->newprocessingfeeamt = $newprocessingfeeamt;

					$newofferStripepercentamtSum += (($offerpriceperselectedDates+$campaign_product->tax_percentage_amount)* ($stripe_percent))/100;
					$newprocessingfeeamtSum += $newprocessingfeeamt;
					
					$campaign_product->productbookingid = $campaign_product->id;

                }
                
                /*CPM Calculation*/
                if($campaign_product->booked_from!=null){
					$booked_from[] = strtotime($campaign_product->booked_from);
                }
                if($campaign_product->booked_to!=null){
                    $booked_to[] = strtotime($campaign_product->booked_to);
                }
					
				$offer_comments = Offer_admin_seller_comments::where('booking_id', '=', $campaign_product->productbookingid)->orderBy('created_at', 'ASC')->get();
				if(isset($offer_comments) && count($offer_comments) > 0){
					$first_offer_id = $offer_comments[0]->offer_id;
					foreach($offer_comments as $keys => $values){
						if($values->offer_id == $first_offer_id){
							$values->offer_type = 'First Offer Details';
						}else{
							$values->offer_type = 'Second Offer Details';
						}
						
						if($values->status == 1){
							$values->final_status = 'Send Request';
							$values->Sender = 'Admin';
						}else if($values->status == 2){
							$values->final_status = 'Approved';
							$values->Sender = 'Owner';
						}else if($values->status == 3){
							$values->final_status = 'Rejected';
							$values->Sender = 'Owner';
						}
					}
					$offer_comments_array = array('offer_comments' => $offer_comments->toArray());
				}else{
					$offer_comments_array = array('offer_comments' => array());
				}
				
				
                array_push($products_in_campaign, array_merge($getproductDetails->toArray(),$campaign_product->toArray(),$offer_comments_array));
            }
            $campaign->products = $products_in_campaign;
			$campaign->products_in_campaign = $products_in_campaign_arr;
            $quote_change = CampaignQuoteChange::select('remark','type')->where('campaign_id', '=', $campaign_id)->orderBy('created_at', 'desc')->get();
            if (!empty($quote_change)) {
                $campaign->quote_change = $quote_change;
            }

                    $total_price_array = ProductBooking::where('campaign_id', '=', $campaign_id)->where('product_owner', '=', $user_mongo['client_mongo_id'])->get(); 
                
                    foreach($total_price_array as $key=>$val)
                    {
                        $price = $val->price;
                        
                        if($price ==0){
                        $price = $val->admin_price;
                        }else 
                        if($price ==0){
                        $price = $val->owner_price;
                        }
						
						
						if($offershortlistedsum!=0){
							$campaign->act_budget = $offershortlistedsum;
						}else{
							$campaign->act_budget += $price;
							//$campaign->totalamount = $campaign->act_budget;
						}
                        //$campaign->act_budget += $price;
                    }
					if($offershortlistedsum!=0){
						$campaign->totalamount = $offershortlistedsum;
					}else{
						$campaign->totalamount = $campaign->act_budget;
					}
                 
        //}
		$campaign->gross_fee_price = 0;
		if(isset($campaign->gross_fee_percentage) && $campaign->gross_fee_percentage > 0){
			$campaign->gross_fee_price = ($offershortlistedsum*$campaign->gross_fee_percentage)/100;
			$newprocessingfeeamtGross = ($newprocessingfeeamtSum*$campaign->gross_fee_percentage)/100;
			$newprocessingfeeamtSum = $newprocessingfeeamtSum+$newprocessingfeeamtGross;
		}else{
			$campaign->gross_fee_price = 0;
		}
        $impressionSum4 = round($impressionSum, 2); /* As discussed with Richard on July 14) */
         if($impressionSum4>0){
            $cpmval = (($shortlistedsumcpm)/$impressionSum4) * 1000;
			$offercpmval = (($offershortlistedsumcpm)/$impressionSum4) * 1000;
			$campaign->grosscpmval = (($offershortlistedsumcpm+$campaign->gross_fee_price)/$impressionSum4) * 1000;
         }else{
             $cpmval = 0;
			 $offercpmval = 0;
			 $campaign->grosscpmval = 0; 
         }
		$campaign->act_budget = $campaign->act_budget+$campaign->gross_fee_price;
		
		 if($offershortlistedsum!=0){
                    $campaign->percentagevalue = ($newofferStripepercentamtSum * 100)/$offershortlistedsum;
                 }else{
                    $campaign->percentagevalue=5;
                 }
		 if($campaign->total_paid != 0){
			if($newofferStripepercentamtSum < 1){
				$campaign->total_paid = $campaign->total_paid;
			}else{
				$campaign->total_paid = $newofferStripepercentamtSum;
			}
		 }
		 
		 if($offershortlistedsum != 0){
			$campaign->campaign_cpmval = (($offershortlistedsumcpm+$campaign->gross_fee_price)/$impressionSum4) * 1000;
		 }else{
			 $campaign->campaign_cpmval = (($shortlistedsumcpm+$campaign->gross_fee_price)/$impressionSum4) * 1000;
		 }
		 
		 
		$campaign->offershortlistedsum = $offershortlistedsum;
		$campaign->grossshortlistedsum = $offershortlistedsum+$campaign->gross_fee_price;
        $campaign->offercpmval = $offercpmval;
		$campaign->tax_percentage_amount_sum = $tax_percentage_amount_sum;
        $campaign->shortlistedsum = $shortlistedsum;
        $campaign->cpmval = $cpmval;
        $campaign->impressionSum = $impressionSum4;
		$campaign->newofferStripepercentamtSum = $newofferStripepercentamtSum;
		$campaign->newprocessingfeeamtSum = $newprocessingfeeamtSum;
		$campaign->paid_percentage = $campaign->total_paid*100/($campaign->act_budget-$campaign->gross_fee_price-$tax_percentage_amount_sum);
        $campaign->offer_applied_res = $offer_applied;
        if(isset($booked_from) && !empty($booked_from)) {$campaign->startDate =  date('d-m-Y',min($booked_from));}
        if(isset($booked_to) && !empty($booked_to)) {$campaign->endDate = date('d-m-Y',max($booked_to));}
        }else{
			$campaign->rfp_search_criteria_preview=[];
			$campaign->rfp_search_criteria_preview = RFPSearchCriteria::where('campaign_id', '=', $campaign->id)->first();
			if(!empty($campaign->rfp_search_criteria_preview)){
				$area_full_details = array();
				$rfp_search_criteria_preview_dma_dates = array();
				if(isset($campaign->rfp_search_criteria_preview['dma_area'])){
				foreach($campaign->rfp_search_criteria_preview['dma_area'] as $key => $value){
					$area_full_details[] = Area::where('id', '=', $value)->first();
					if(!is_null($area_full_details[$key])){
						$area_time_zone_type = $area_full_details[$key]['area_time_zone_type'];
						if(!is_null($area_time_zone_type)){
							$explode_dates  = @explode('::',$campaign->rfp_search_criteria_preview['dma_dates'][$key]);
							$start_time = new DateTime( $explode_dates[0] );
							$end_time = new DateTime( $explode_dates[1] );
							$laTimezone = new DateTimeZone($area_time_zone_type);
							$start_time->setTimeZone( $laTimezone );
							$end_time->setTimeZone( $laTimezone );
							$start_time->format( 'Y-m-d H:i:s' );
							$end_time->format( 'Y-m-d H:i:s' );	
							$rfp_search_criteria_preview_dma_dates[] = $start_time->format( 'Y-m-d H:i:s' ).'::'.$end_time->format( 'Y-m-d H:i:s' );
						}							
					}
				}
				}
				$campaign->area_rfp_details = $area_full_details;
				if(isset($rfp_search_criteria_preview_dma_dates)){
					$campaign->rfp_search_criteria_preview['dma_dates'] = $rfp_search_criteria_preview_dma_dates;
				}
			}
			else
			{
				$campaigns = Campaign::where('id', '=', $campaign_id)->first();
				$campaign->rfp_search_criteria_preview = $campaigns;
			}
		}
        return response()->json($campaign);
    }
 
    public function getOwnerCampaigns() {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo['user_id'])->first();
        $client_mongo = ClientMongo::where('client_id', '=', $user->client->id)->first();

        $owner_campaigns = Campaign::where('client_mongo_id', '=', $client_mongo->id)->orderBy('updated_at', 'desc')->get();
        foreach ($owner_campaigns as $campaign_product) {
            $products = ProductBooking::where([
                        ['campaign_id', '=', $campaign_product->id],
                    ])->get();

            $product_start_date = ProductBooking::where([
                        ['campaign_id', '=', $campaign_product->id],
                    ])->select("booked_from","booked_to")->orderBy('booked_from', 'asc')->first();
            if (!empty($product_start_date)) {
                $campaign_product->start_date = $product_start_date->booked_from;
                $campaign_product->end_date = $product_start_date->booked_to;
            }

            $campaign_product->product_count = $products->count();
            $campaign_payments = CampaignPayment::where('campaign_id', '=', $campaign_product->id)->get();
            $total_price = ProductBooking::where('campaign_id', '=', $campaign_product->id)->where('product_owner', '=', $user_mongo['client_mongo_id'])->sum('owner_price');
            if ($total_price == 0) {
                $total_price = ProductBooking::where('campaign_id', '=', $campaign_product->id)->where('product_owner', '=', $user_mongo['client_mongo_id'])->sum('price');
            }
            $gstPrice = 0;
            $campaign_product->Totalamount = ($total_price+$gstPrice);
            $campaign_product->total_price = $total_price;
            if (isset($campaign_payments) && count($campaign_payments) > 0) {
                $total_paid = $campaign_payments->sum('amount');
                $campaign_product->total_paid = $total_paid;
            } else {
                $campaign_product->total_paid = 0;
            }
        }
        return response()->json($owner_campaigns);
    }

    public function getNonUserCampaignDetails($campaign_id) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo['user_id'])->first();
        $client_mongo = ClientMongo::where('client_id', '=', $user->client->id)->first();
        
        $shortlisted_products_arr = [];
        $shortlistedsum = 0;
        $cpmsum = 0;
        $impressionSum = 0;
        $impressionSum4 = 0;
        
        if (!isset($user_mongo) || !isset($user_mongo['client_mongo_id'])) {
            return response()->json(['status' => 0, 'message' => 'You are not authorized to view these campaigns.']);
        }
        $campaign = Campaign::where([
                    ['id', '=', $campaign_id],
                    //['client_mongo_id', '=', $client_mongo->id]
                ])->first();
                
        if (!isset($campaign) || empty($campaign)) {
            return response()->json(['status' => 0, 'message' => 'Either the campaign does not exist or you\'re not authorized to view this campaign']);
        }
        $client_type = $user->client->client_type->type;
        if ($client_type == "owner") {
            $campaign_filter_rule = [
                ['campaign_id', '=', $campaign_id],
                //['product_owner', '=', $user_mongo['client_mongo_id']]
            ];
        } else {
            $campaign_filter_rule = [
                ['campaign_id', '=', $campaign_id],
            ];
        }
    
        $campaign_products = ProductBooking::where($campaign_filter_rule)->get();
        $products_in_campaign = [];
        if (!empty($campaign_products)) {
            foreach ($campaign_products as $campaign_product) {
                // adding campaign products to it.
                if($campaign_product->booked_from!=null){
                $booked_from[] = strtotime($campaign_product->booked_from);
                }
                if($campaign_product->booked_to!=null){
                    $booked_to[] = strtotime($campaign_product->booked_to);
                }
                /*$product = Product::select('id', 'siteNo', 'format_name', 'area_name', 'panelSize', 'lighting', 'image','address','impressions','type','rateCard', 'address','addresstwo')
                                ->where('id', '=', $campaign_product->product_id)->first();*/
                $product = Product::where('id', '=', $campaign_product->product_id)->first();
                
                $diff=date_diff(date_create($campaign_product->booked_from),date_create($campaign_product->booked_to));
                //$diff=date_diff(($booked_from_date),($booked_to_date));
                $daysCount = $diff->format("%a");//exit;
                //$daysCountCPM = $daysCount + 1;
                $daysCountCPM = $daysCount;
                if(isset($product->fix) && $product->fix=="Fixed"){
                    $price = $campaign_product->price;
                    $priceperday = $price;//exit;
                    $priceperselectedDates = $priceperday;
                    $shortlistedsum+= $priceperselectedDates;
                    $campaign_product->price = $priceperselectedDates;
                    
                    $cpmsum+= $product->cpm;
                    $impressions = $product->secondImpression;
                    $impressionsperday = (int)($impressions);
                    $impressionsperselectedDates = $impressionsperday;//exit;
                    
                    if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                        $impressionsperselectedDates = $impressionsperselectedDates;
                    }else{
                        $impressionsperselectedDates = 1;
                    }
                    //$impressionSum+= $product_details->secondImpression;
                    $impressionSum+= $impressionsperselectedDates;
                    $campaign_product->secondImpression = round($impressionsperselectedDates, 2);
                    $cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                    $campaign_product->cpmperselectedDates = $cpmcal;
                    $campaign_product->cpm = $cpmcal;
                    $campaign_product->impressionsperselectedDates = $impressionsperselectedDates;
                    $campaign_product->priceperselectedDates = $priceperselectedDates;
                }else{
                    $price = $campaign_product->price;
                    //$priceperday = $price/28;//exit;
                    //$priceperselectedDates = $priceperday * $daysCount;
					
					if($daysCount < $product->minimumdays){
						$priceperday = $campaign_product->price;
						$priceperselectedDates = $priceperday;
					}else{
						$priceperday = $price/28;
						$priceperselectedDates = $priceperday * $daysCount;
					}
					
                    $shortlistedsum+= $priceperselectedDates;
                    $campaign_product->price = $priceperselectedDates;
                    
                    $cpmsum+= $product->cpm;
                    $impressions = $product->secondImpression;
                    $impressionsperday = (int)($impressions/7);
                    $impressionsperselectedDates = $impressionsperday * $daysCount;//exit;
                    
                    if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                        $impressionsperselectedDates = $impressionsperselectedDates;
                    }else{
                        $impressionsperselectedDates = 1;
                    }
                    //$impressionSum+= $product_details->secondImpression;
                    $impressionSum+= $impressionsperselectedDates;
                    $campaign_product->secondImpression = round($impressionsperselectedDates, 2);
                    $cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                    $campaign_product->cpmperselectedDates = $cpmcal;
                    $campaign_product->cpm = $cpmcal;
                    $campaign_product->impressionsperselectedDates = $impressionsperselectedDates;
                    $campaign_product->priceperselectedDates = $priceperselectedDates;
                }
                
                //echo "<pre>campaign_product"; print_r($campaign_product);exit;
        
                if (!empty($product)) {
                    array_push($products_in_campaign, array_merge($product->toArray(),$campaign_product->toArray()));
                }
            }
        }

        $campaign->products = $products_in_campaign;
        //echo "<pre>"; print_r($campaign->products);exit;
        if($campaign->type!=2){
        $act_budget = ProductBooking::raw(function($collection) use ($campaign_id) {
                    return $collection->aggregate(
                                    [
                                        [
                                            '$match' =>
                                            [
                                                "campaign_id" => $campaign_id
                                            ]
                                        ],
                                        [
                                            '$group' =>
                                            [
                                                '_id' => '$campaign_id',
                                                'total_price' => [
                                                    '$sum' => '$price'
                                                ]
                                            ]
                                        ]
                                    ]
                    );
                });
        }else if($campaign->type==2){
            
            /* $act_budget = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('owner_price');
                if($act_budget==0){ 
                $act_budget = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('price');
                }*/
                 $act_budgetArray = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
                 
                 //echo "<pre>"; print_r($act_budgetArray);exit;
       $sum_act_budget=0;
       if(!empty($act_budgetArray)){
                foreach($act_budgetArray as $key=>$val)
                {
                    if($val['owner_price']!=''){
                         $sum_act_budget+=$val['owner_price'];
                    }
                    else{
                         $sum_act_budget+=$val['price'];
                    }
                    
                }
       }
                 $act_budget = $sum_act_budget;
        }
        
          $paid = CampaignPayment::raw(function($collection) use ($campaign_id) {
                        return $collection->aggregate(
                                        [
                                            [
                                                '$match' =>
                                                [
                                                    "campaign_id" => $campaign_id
                                                ]
                                            ],
                                            [
                                                '$group' =>
                                                [
                                                    '_id' => '$campaign_id',
                                                    'paid' => [
                                                        '$sum' => '$amount'
                                                    ]
                                                ]
                                            ]
                                        ]
                        );
                    });
            $campaign->total_paid = count($paid) > 0 ? $paid[0]->paid : 0;
        $campaign->no_of_products = ProductBooking::where('campaign_id', '=', $campaign_id)->distinct('product_id')->get()->count();
        $campaign->act_budget = count($act_budget) > 0 ? $act_budget : 0;
        
        $impressionSum4 = round($impressionSum, 2); /* As discussed with Richard on July 14) */
         if($impressionSum4>0){
            $cpmval = ($shortlistedsum/$impressionSum4) * 1000;
         }else{
             $cpmval = 0;
         }
 
         $campaign->shortlistedsum = $shortlistedsum;
         $campaign->cpmval = $cpmval;
         $campaign->impressionSum = $impressionSum4;
        
        if(isset($booked_from) && !empty($booked_from)) {$campaign->startDate =  date('d-m-Y',min($booked_from));}
        if(isset($booked_to) && !empty($booked_to)) {$campaign->endDate = date('d-m-Y',max($booked_to));}
        return response()->json($campaign);
    }

    public function getCampaignWithPaymentsForOwner() {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $campaign_ids = ProductBooking::where('product_owner', '=', $user_mongo['client_mongo_id'])->pluck('campaign_id');
        $campaigns = Campaign::select('id', 'name', 'start_date', 'end_date', 'status', 'type')
                ->whereIn('id', $campaign_ids)
                ->where('status', '>=', Campaign::$CAMPAIGN_STATUS['scheduled'])
                ->get();
        foreach ($campaigns as $campaign) {
            $act_budget = ProductBooking::raw(function($collection) use ($campaign) {
                        return $collection->aggregate(
                                        [
                                            [
                                                '$match' =>
                                                [
                                                    "campaign_id" => $campaign->id
                                                ]
                                            ],
                                            [
                                                '$group' =>
                                                [
                                                    '_id' => '$campaign_id',
                                                    'total_price' => [
                                                        '$sum' => '$price'
                                                    ]
                                                ]
                                            ]
                                        ]
                        );
                    });
            $paid = CampaignPayment::raw(function($collection) use ($campaign) {
                        return $collection->aggregate(
                                        [
                                            [
                                                '$match' =>
                                                [
                                                    "campaign_id" => $campaign->id
                                                ]
                                            ],
                                            [
                                                '$group' =>
                                                [
                                                    '_id' => '$campaign_id',
                                                    'paid' => [
                                                        '$sum' => '$amount'
                                                    ]
                                                ]
                                            ]
                                        ]
                        );
                    });
            $campaign->act_budget = count($act_budget) > 0 ? $act_budget[0]->total_price : 0;
            $campaign->paid = count($paid) > 0 ? $paid[0]->paid : 0;
        }
        return response()->json($campaigns);
    }

    public function getCampaignPaymentDetailsForOwner($campaign_id) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $campaign = Campaign::where([
                    ['id', '=', $campaign_id],
                    ['status', '>=', Campaign::$CAMPAIGN_STATUS['scheduled']]
                ])->first();
                
        if (!isset($campaign) || empty($campaign)) {
            return response()->json(['status' => 0, 'message' => "Campaign not found."]);
        }
        if ($campaign->type != Campaign::$CAMPAIGN_USER_TYPE['owner']) {
            $campaign->org_contact_name = "";
            $campaign->org_contact_email = "";
            $campaign->org_contact_phone = "";
            $campaign->created_by = "";
        }

        if ($campaign->status > 1000) {

            $campaign_products = CampaignProduct::where([
                        ['campaign_id', '=', $campaign_id]
                    ])->get();
            $campaign->act_budget = 0;
            foreach ($campaign_products as $campaign_product) {
                $campaign->act_budget += $campaign_product->price;
            }
            $campaign->product_count = $campaign_products->count();
            $campaign->payment_details = CampaignPayment::where([['campaign_id', '=', $campaign_id]])->get();
        } else {
			$products_arr = [];
			$offershortlistedsum = 0;
			$newofferStripepercentamtSum = 0; 
			$tax_percentage_booking = 0;
			$tax_percentage_amount = 0;
			$tax_percentage_booking_tot = 0;
			$tax_percentage_amount_tot = 0;
			$getcampaigntot = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
			$camptot = 0;
			$campaign->payment_details = CampaignPayment::where([
			   // ['client_mongo_id', '=', $user_mongo['client_mongo_id']],
				['campaign_id', '=', $campaign_id]
			])->get();
			$total_paid = $campaign->payment_details->sum('amount');
			$campaign->total_paid = $total_paid;
			if (isset($getcampaigntot) && count($getcampaigntot) > 0) {
				foreach ($getcampaigntot as $getcampaigntot) {
					$tax_percentage_booking_tot = 0;
					$tax_percentage_amount_tot = 0;
					$getcampaigntotproduct =Product::where('id', '=', $getcampaigntot->product_id)->first();
					$bookedfrom[] = strtotime($getcampaigntot->booked_from);
					$bookedto[] = strtotime($getcampaigntot->booked_to);

					$getproductDetails =Product::where('id', '=', $getcampaigntot->product_id)->first();
					$diff=date_diff(date_create($getcampaigntot->booked_from),date_create($getcampaigntot->booked_to));
					$daysCount = $diff->format("%a");
					$daysCountCPM = $daysCount + 1;
					$perdayprice = $getproductDetails->default_price/28;

					if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
						$price = $getcampaigntot->price*$getcampaigntot->quantity;
						if($campaign->total_paid != 0){
							if(isset($getcampaigntot->tax_percentage)){
								$tax_percentage_booking_tot = $getcampaigntot->tax_percentage;
							}else{
								$tax_percentage_booking_tot = 0;
							}
						}else{
							$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
						}
						if($tax_percentage_booking_tot != 0){
							$tax_percentage_amount_tot = ($price * ($tax_percentage_booking_tot))/100;
						}
						$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
						$camptot += $price+$tax_percentage_amount_tot;
					}else{
						$priceperselectedDates = $getcampaigntot->price;
						
						if($campaign->total_paid != 0){
							if(isset($getcampaigntot->tax_percentage)){
								$tax_percentage_booking_tot = $getcampaigntot->tax_percentage;
							}else{
								$tax_percentage_booking_tot = 0;
							}
						}else{
							$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
						}
						if($tax_percentage_booking_tot != 0){
							$tax_percentage_amount_tot = ($priceperselectedDates * ($tax_percentage_booking_tot))/100;
						}
						$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
						$camptot += ($priceperselectedDates*$getcampaigntot->quantity)+($tax_percentage_amount_tot*$getcampaigntot->quantity);
					}
				}
			}
				
			$campaign_products = ProductBooking::where([
						['campaign_id', '=', $campaign_id],
						['product_owner', '=', $user_mongo['client_mongo_id']]
					])->get();

			$campaign->product_count = $campaign_products->count();
			$campaign->act_budget = 0;
			if (isset($campaign_products) && count($campaign_products) > 0) {
				foreach ($campaign_products as $campaign_product) {
					$campaign_product->tax_percentage_amount = 0;
					$tax_percentage_booking = 0;
					$tax_percentage_amount = 0;
					/*$price = $campaign_product->owner_price;
					if($price ==0){
						$price = $campaign_product->admin_price;
					}
					if($price ==0){
						$price = $campaign_product->price;
					}*/
					
					$getproductDetails =Product::where('id', '=', $campaign_product->product_id)->first();
					$diff=date_diff(date_create($campaign_product->booked_from),date_create($campaign_product->booked_to));
					$daysCount = $diff->format("%a");
					$daysCountCPM = $daysCount + 1;
					
					if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
						$offerDetails = MakeOffer::where([
								['campaign_id', '=', $campaign_product->campaign_id],
								['status', '=', 20],
							])->get();
						if(isset($offerDetails) && count($offerDetails)==1){
							foreach($offerDetails as $offerDetails){
								$price = $campaign_product->price;
								$stripe_percent=$getproductDetails->stripe_percent;
								$priceperday = $price;
								$priceperselectedDates = $priceperday;
								$newpricepercentage = ($priceperselectedDates/$camptot) * 100;
								//$newofferprice = ($offerprice * ($newpricepercentage))/100;
								if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
									$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
									$newofferprice = $newofferprice_wq*$campaign_product->quantity;
								}else{
									$newofferprice = ($offerprice * ($newpricepercentage))/100;
								}
								$offerpriceperselectedDates = $newofferprice;
								$newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
							}
						}else{
							$priceperselectedDates = $campaign_product->price;
							$offerprice = $campaign_product->price;
							$stripe_percent=$getproductDetails->stripe_percent;
							if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
								$newofferprice = $offerprice*$campaign_product->quantity;
							}else{
								$newofferprice = $offerprice ;
							}
							$offerpriceperselectedDates = $newofferprice;
							$newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
						}
						
						if($campaign->total_paid != 0){
							if(isset($campaign_product->tax_percentage)){
								$tax_percentage_booking = $campaign_product->tax_percentage;
							}else{
								$tax_percentage_booking = 0;
							}
						}else{
							$tax_percentage_booking = $getproductDetails->tax_percentage;
						}
						if($tax_percentage_booking != 0){
							$tax_percentage_amount = ($offerpriceperselectedDates * ($tax_percentage_booking))/100;
						}
						$campaign_product->tax_percentage_amount = round($tax_percentage_amount,2);
						$offershortlistedsum+= $offerpriceperselectedDates+$campaign_product->tax_percentage_amount;
						$newofferStripepercentamtSum += $newofferStripepercentamt+$campaign_product->tax_percentage_amount;

					}else{
						
						$offerDetails = MakeOffer::where([
								['campaign_id', '=', $campaign_product->campaign_id],
								['status', '=', 20],
							])->get();
						if(isset($offerDetails) && count($offerDetails)==1){
							foreach($offerDetails as $offerDetails){
								$price = $getproductDetails->default_price;
								$offerprice = $offerDetails->price;
								$stripe_percent=$getproductDetails->stripe_percent;
								//$priceperday = $price/28;
								//$priceperselectedDates = $priceperday * $daysCountCPM;
								
								///variable_offer///
								$priceperday = $campaign_product->price;
								$priceperselectedDates = $priceperday;
								
								
								$newpricepercentage = ($priceperselectedDates/$camptot) * 100;
								//$newofferprice = ($offerprice * ($newpricepercentage))/100;
								if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
									$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
									$newofferprice = $newofferprice_wq*$campaign_product->quantity;
								}else{
									$newofferprice = ($offerprice * ($newpricepercentage))/100;
								}
								$offerpriceperselectedDates = $newofferprice;
								$newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
							}
						}else{
							$priceperselectedDates = $campaign_product->price;
							$offerprice = $campaign_product->price;
							$stripe_percent=$getproductDetails->stripe_percent;
							if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
								$newofferprice = $offerprice*$campaign_product->quantity;
							}else{
								$newofferprice = $offerprice;
							}
							$offerpriceperselectedDates = $newofferprice;
							$newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
						}
						
						if($campaign->total_paid != 0){
							if(isset($campaign_product->tax_percentage)){
								$tax_percentage_booking = $campaign_product->tax_percentage;
							}else{
								$tax_percentage_booking = 0;
							}
						}else{
							$tax_percentage_booking = $getproductDetails->tax_percentage;
						}
						if($tax_percentage_booking != 0){
							$tax_percentage_amount = ($offerpriceperselectedDates * ($tax_percentage_booking))/100;
						}
						$campaign_product->tax_percentage_amount = round($tax_percentage_amount,2);

						$offershortlistedsum+= $offerpriceperselectedDates+$campaign_product->tax_percentage_amount;
						$newofferStripepercentamtSum += $newofferStripepercentamt+$campaign_product->tax_percentage_amount;

					}
					//$campaign->act_budget += $price;
					array_push($products_arr, array_merge(Product::where('id', '=', $campaign_product->product_id)->first()->toArray(), $campaign_product->toArray()));
				}
				$campaign->actbudg = $products_arr;
				$res = array_sum(array_map(function($item) { 
						if(isset($item['quantity']) && $item['quantity'] != '' && $item['quantity'] != 0){
							return ($item['price']*$item['quantity'])+$item['tax_percentage_amount']; 
						}else{
							return $item['price']+$item['tax_percentage_amount']; 
						}
					}, $campaign->actbudg));
					
			}		
			$campaign->newofferStripepercentamtSumSeller = $newofferStripepercentamtSum;
		   // $campaign->totalamount = $campaign->act_budget;
		   
		   $campaign->gross_fee_price = 0;
			if(isset($campaign->gross_fee_percentage) && $campaign->gross_fee_percentage > 0){
				$campaign->gross_fee_price = ($offershortlistedsum*$campaign->gross_fee_percentage)/100;
			}else{
				$campaign->gross_fee_price = 0;
			}
			if($offershortlistedsum!=0){
				$campaign->act_budget = $offershortlistedsum+$campaign->gross_fee_price;
				$campaign->totalamount = $offershortlistedsum+$campaign->gross_fee_price; 
			}else{
				$campaign->act_budget = $res+$campaign->gross_fee_price;
				$campaign->totalamount = $campaign->act_budget+$campaign->gross_fee_price;
			}
			if($campaign->total_paid != 0){
				if($newofferStripepercentamtSum < 1){
					$campaign->total_paid = $campaign->total_paid;
				}else{
					$campaign->total_paid = $newofferStripepercentamtSum;
				}
			}
        }
       
        return response()->json($campaign);
    }

    public function updateCampaignPaymentByOwner() {
        $this->validate($this->request, [
            'campaign_payment.campaign_id' => 'required',
            'campaign_payment.amount' => 'required',
            'campaign_payment.type' => 'required',
            'campaign_payment.received_by' => 'required'
                ], [
            'campaign_payment.campaign_id.required' => 'Campaign id is required',
            'campaign_payment.amount.required' => 'Amount is required',
            'campaign_payment.type.required' => 'Type is required',
            'campaign_payment.received_by.required' => 'Field "Received By" is required'
                ]
        );
        $input = $this->input['campaign_payment'];
        if ($input['type'] != "Cash" && !isset($input['reference_no'])) {
            return response()->json(["status" => 0, "message" => "Cheque/Reference/Transaction No. is required in case of payment made other than by cash."]);
        }
        $campaign_id = $input['campaign_id'];

        $campaign = Campaign::where('id', '=', $campaign_id)->first();
        if ($campaign->status > 1000) {
            $total_price = CampaignProduct::where('campaign_id', '=', $campaign_id)->sum('price');
            $campaign_act_budget = $total_price;
            $gst_price = isset($campaign->gst_price)?$campaign->gst_price:0;
			$campaign_act_budget = ($campaign_act_budget+$gst_price);
        } else {
           /* $act_budget_group = ProductBooking::raw(function($collection) use ($campaign_id) {
                        return $collection->aggregate(
                                        [
                                            [
                                                '$match' =>
                                                [
                                                    "campaign_id" => $campaign_id
                                                ]
                                            ],
                                            [
                                                '$group' =>
                                                [
                                                    '_id' => '$campaign_id',
                                                    'total_price' => [
                                                        '$sum' => '$owner_price'
                                                    ]
                                                ]
                                            ]
                                        ]
                        );
                    }); 
                    if($act_budget_group[0]->total_price==0){
                         $act_budget_group = ProductBooking::raw(function($collection) use ($campaign_id) {
                        return $collection->aggregate(
                                        [
                                            [
                                                '$match' =>
                                                [
                                                    "campaign_id" => $campaign_id
                                                ]
                                            ],
                                            [
                                                '$group' =>
                                                [
                                                    '_id' => '$campaign_id',
                                                    'total_price' => [
                                                        '$sum' => '$price'
                                                    ]
                                                ]
                                            ]
                                        ]
                        );
                    });
                    }
            $campaign_act_budget = count($act_budget_group) > 0 ? $act_budget_group[0]->total_price : 0;*/
            //$gst_price = isset($campaign->gst_price)?$campaign->gst_price:0;
         // $campaign_act_budget = ($campaign_act_budget+$gst_price);
         /* $gststatus = isset($campaign->gststatus)?$campaign->gststatus:0;
                if($gststatus ==1){
                    $campaign_act_budget = $campaign_act_budget+round(($campaign_act_budget*(0.18)),2);
                }
                else{
                     $campaign_act_budget = $campaign_act_budget;
                }*/
                            $campaign_products = ProductBooking::where([
                        ['campaign_id', '=', $campaign_id],
                      //  ['product_owner', '=', $user_mongo['client_mongo_id']]
                    ])->get();

            $campaign_act_budget = 0;
            foreach ($campaign_products as $campaign_product) {
                $price = $campaign_product->owner_price;
                if($price ==0){
                $price = $campaign_product->admin_price;
                }
                if($price ==0){
                $price = $campaign_product->price;
                }
                $campaign_act_budget += $price;
            }
			$campaign_act_budget = $campaign_act_budget;
        }

        $paid_group = CampaignPayment::raw(function($collection) use ($campaign_id) {
                    return $collection->aggregate(
                                    [
                                        [
                                            '$match' =>
                                            [
                                                "campaign_id" => $campaign_id
                                            ]
                                        ],
                                        [
                                            '$group' =>
                                            [
                                                '_id' => '$campaign_id',
                                                'paid' => [
                                                    '$sum' => '$amount'
                                                ]
                                            ]
                                        ]
                                    ]
                    );
                });
        $campaign_paid = count($paid_group) > 0 ? $paid_group[0]->paid : 0;
        $remaining_campaign_payment = $campaign_act_budget - $campaign_paid;
        if ($input['amount'] > $remaining_campaign_payment) {
            return response()->json(['status' => 0, 'message' => 'The given amount can not be larger than the pending amount.']);
        }
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $campaign_payment = new CampaignPayment;
        $campaign_payment->campaign_id = isset($input['campaign_id']) ? $input['campaign_id'] : "";
        $campaign_payment->amount = isset($input['amount']) ? (int) $input['amount'] : "";
        $campaign_payment->type = isset($input['type']) ? $input['type'] : "";
        $campaign_payment->reference_no = isset($input['reference_no']) ? $input['reference_no'] : "";
        $campaign_payment->received_by = isset($input['received_by']) ? $input['received_by'] : "";
        $campaign_payment->updated_by_id = $user_mongo['id'];
        $campaign_payment->updated_by_name = $user_mongo['first_name'] . " " . $user_mongo['last_name'];
        $campaign_payment->client_mongo_id = $user_mongo['client_mongo_id'];
          $campaign_payment->comment = isset($input['comment']) ? $input['comment'] : "";
        $payment_img_path = base_path() . '/html/uploads/images/campaign_payments';
        if ($this->request->hasFile('image')) {
            if ($this->request->file('image')->move($payment_img_path, $this->request->file('image')->getClientOriginalName())) {
                $campaign_payment->image = "/uploads/images/campaign_payments/" . $this->request->file('image')->getClientOriginalName();
            }
        }
        if ($campaign_payment->save()) {
            return response()->json(["status" => "1", "message" => "Campaign payment updated successfully."]);
        } else {
            return response()->json(["status" => "0", "message" => "There was a technical error while updating the payment. Please try again later."]);
        }
    }

    public function getOwnerFeeds() {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $campaign_product_ids = ProductBooking::where('product_owner', '=', $user_mongo['client_mongo_id'])->pluck('campaign_id')->toArray();
        $campaign_feeds = Campaign::raw(function($collection) use ($campaign_product_ids) {
                    return $collection->find(
                                    [
                                '$and' => [
                                    ['id' => ['$in' => $campaign_product_ids]],
                                 [
                                  '$or' => [
                                  ['status' => Campaign::$CAMPAIGN_STATUS['quote-requested']],
                                  ['status' => Campaign::$CAMPAIGN_STATUS['booking-requested']],
								  ['status' => Campaign::$CAMPAIGN_STATUS['change-requested']]
                                  ]
                                  ]
                                ]
                                    ], [
                                'sort' => [
                                    'updated_at' => -1
                                ]
                                    ]
                    );
                });
                
        $current_date = date('d-m-Y');
        $colorcode = '';
        $diff= '';
        
        $user_campaigns_arr = [];       
        $j = 0;
        
        foreach ($campaign_feeds as $ocf) {
            $user_mongo = UserMongo::select('first_name', 'last_name', 'email', 'phone', 'profile_pic')->where('id', '=', $ocf->created_by)->first();
            if(!empty($user_mongo)){
            $ocf->contact_name = $user_mongo->first_name . ' ' . $user_mongo->last_name;
            $ocf->email = $user_mongo->email;
            $ocf->phone = $user_mongo->phone;
            $ocf->avatar = $user_mongo->profile_pic;
            }
            //echo 'sasas';exit;
            $total_paid = CampaignPayment::where('campaign_id', '=', $ocf->id)->sum('amount');
            if(isset($total_paid) && !empty($total_paid)){
                $ocf->total_paid = $total_paid;
            }else{
                $ocf->total_paid = 0;
            }
            $total_price = ProductBooking::where('campaign_id', '=', $ocf->id)->sum('price');
            if(isset($total_price) && !empty($total_price)){
                $ocf->total_price = $total_price;
            }else{
                $ocf->total_price = 0;
            }
            //echo 'total_price'.$total_price;exit;
            //$ocf->total_paid = $total_paid;
            //$ocf->total_paid = 0;
            //$ocf->total_price = $total_price;
            //$ocf->total_paid = $total_paid;
            
            $count = ProductBooking::where('campaign_id', '=', $ocf->id)->count();
            $ocf->product_count = $count;
            $product_start_date = ProductBooking::where('campaign_id', '=', $ocf->id)->select("booked_from","booked_to")->orderBy('booked_from', 'asc')->first();
            if (!empty($product_start_date)) {
                $ocf->start_date = $product_start_date->booked_from;
                $ocf->end_date = $product_start_date->booked_to;
                $diff=date_diff(date_create($current_date),date_create($ocf->start_date));
                if($diff->days <=7 ){
                    $ocf->colorcode = 'red';
                }else{
                    $ocf->colorcode = 'black';
                }
            }
            
            $products_in_campaign = [];
            //$campaign_products = ProductBooking::where('product_owner', '=', $user_mongo['client_mongo_id'])->pluck('campaign_id')->toArray();
            $campaign_products = ProductBooking::where('product_owner', '=', $user_mongo['client_mongo_id'])->get()->toArray();
            //echo "<pre>campaign_products";print_r($campaign_products);exit;
            if (isset($campaign_products) && count($campaign_products) > 0) { 
            //if (isset($campaign_product_ids) && count($campaign_product_ids) > 0) { 
            foreach ($campaign_products as $campaign_product) {
            //foreach ($campaign_product_ids as $campaign_product) {
                // adding campaign products to it.
                //echo $campaign_product['product_id'];exit;
                //$product = Product::where('id', '=', $campaign_product->product_id)->first();
                $product = Product::where('id', '=', $campaign_product['product_id'])->first();
                
                //if($campaign_product->booked_from!=null){
                if($campaign_product['booked_from']!=null){
                //$booked_from[] = strtotime($campaign_product->booked_from);
                //$booked_from = $campaign_product->booked_from;
                $booked_from = $campaign_product['booked_from'];
                }
                //if($campaign_product->booked_to!=null){
                if($campaign_product['booked_to']!=null){
                    //$booked_to[] = strtotime($campaign_product->booked_to);
                    //$booked_to = $campaign_product->booked_to;
                    $booked_to = $campaign_product['booked_to'];
                }
                $ocf->contact_name = $user_mongo['first_name'] . ' ' . $user_mongo['last_name'];
                $ocf->email = $user_mongo['email'];
                $ocf->phone = $user_mongo['phone'];
                $ocf->from_date = $booked_from;
                $ocf->to_date = $booked_to;
            }
        }
            $user_details = UserMongo::select('first_name', 'last_name', 'email', 'phone')->where('id', '=', $ocf->created_by)->first();

            if(isset($user_details) && (!is_null($user_details)) && (!is_array($user_details))){
                $user_details_array = $user_details->toArray();
            }else{
                $user_details_array = array();
            }
            if(isset($ocf) && (!is_null($ocf)) && !is_array($ocf)){
                $ocf_array = $ocf->toArray();
           // }else{
             //   $ocf_array = $ocf;
            }
            //array_push($user_campaigns_arr, array_merge($ocf->toArray(), $user_details->toArray()));
            array_push($user_campaigns_arr, array_merge($user_details_array, $ocf_array));
            ++$j;
            
        }
        //echo "<pre>user_campaigns_arr";print_r($user_campaigns_arr);exit;
        //return response()->json($campaign_feeds);
        return response()->json($user_campaigns_arr);
    }

    public function getQuoteChangeHistory($campaign_id) {
        $quote_change_history = CampaignQuoteChange::where('campaign_id', '=', $campaign_id)
                        ->orderBy('iteration', 'desc')->get();
        return response()->json($quote_change_history);
    }

    public function notifyOwnersForQuote($campaign_id) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        if ($user_mongo['user_type'] != 'bbi') {
            return response()->json(['status' => 0, 'message' => "You can not request a quote."]);
        }
        $campaign_obj = Campaign::where([
                    ['id', '=', $campaign_id],
                    ['client_mongo_id', '=', $user_mongo['client_mongo_id']]
                ])->first();
        $campaign_products = ProductBooking::where([
                    ['campaign_id', '=', $campaign_id],
                ])->get();
        if (count($campaign_products) <= 0) {
            return response()->json(["status" => "0", "message" => "Add some products in the campaign first."]);
        }
        $campaign_obj->status = Campaign::$CAMPAIGN_STATUS['quote-requested'];
        if ($campaign_obj->save()) {
            // notification for owners whose products are in campaign.
            $campaign_product_owner_ids = ProductBooking::raw(function($collection) use ($campaign_id) {
                        return $collection->distinct('product_owner', ['campaign_id' => $campaign_id]);
                    });
            $owner_notif_recipients = ClientMongo::whereIn('id', $campaign_product_owner_ids)->get();
            $owner_sa_ids = [];
            foreach ($owner_notif_recipients as $owner_notif_recipient) {
                if (isset($owner_notif_recipient->super_admin_m_id) && !empty($owner_notif_recipient->super_admin_m_id)) {
                    array_push($owner_sa_ids, $owner_notif_recipient->super_admin_m_id);
                }
            }
            $owner_sa_emails = UserMongo::whereIn('id', $owner_sa_ids)->pluck('email');
            $noti_array = [
                'type' => Notification::$NOTIFICATION_TYPE['campaign-quote-requested'],
                'from_id' => null,
                'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['owner'],
                'to_id' => null,
                'to_client' => $campaign_product_owner_ids,
                'desc' => "Quote for a campaign requested",
                'message' => "Your product has been selected for a campaign. Please provide a quote.",
                'data' => ["campaign_id" => $campaign_obj->id]
            ];

            $mail_array = [
                'mail_tmpl_params' => ['sender_email' => config('app.bbi_email'), //, 
                    'receiver_name' => "",
                    //'mail_message' => 'You have received a quote request from Billboards India.'
                    'mail_message' => 'You have received a quote request from Advertising Marketplace.'
                ],
                'bcc' => $owner_sa_emails->toArray(),
                //'subject' => 'A user has requested a quote for their campaign - Billboards India'
                'subject' => 'A user has requested a quote for their campaign - Advertising Marketplace'
            ];
            event(new CampaignQuoteRequestedEvent($noti_array, $mail_array));
            foreach ($campaign_product_owner_ids as $key => $val) {
                $notification_obj = new Notification;
                $notification_obj->id = uniqid();
                $notification_obj->type = "campaign";
                $notification_obj->from_id = null;
                $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['owner'];
                $notification_obj->to_id = $val;
                $notification_obj->to_client = $val;
                $notification_obj->desc = "Quote for a campaign requested";
                $notification_obj->message = "Your product has been selected for a campaign. Please provide a quote";
                $notification_obj->campaign_id = $campaign_obj->id;
                $notification_obj->status = 0;
                $notification_obj->save();
            }
            return response()->json(["status" => "1", "message" => "Successfully sent a request for quote."]);
        } else {
            return response()->json(["status" => "0", "message" => "There was an error in sending the request."]);
        }
    }

    /* ==================================================
      ///////// Metro Campaigns related actions //////////
      ================================================== */

    public function saveMetroCampaign() {
        $this->validate($this->request, [
            'name' => 'required'
                ], [
            'name.required' => 'Name is required'
                ]
        );
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        // if($user_mongo['user_type'] != 'basic'){
        //  return response()->json(['status' => 0, 'message' => "You can not create a campaign from here. Please switch to your dashboard."]);
        // }
        $campaign_obj = new Campaign;
        $campaign_obj->id = $metro_camp_id = uniqid();
        $campaign_obj->cid = $this->generatecampaignID();
        $campaign_obj->name = isset($this->input['name']) ? $this->input['name'] : "";
        $campaign_obj->slug = str_replace(" ", "-", strtolower($this->input['name']));
        $campaign_obj->format_type = Format::$FORMAT_TYPE['metro'];
        if ($user_mongo['user_type'] == 'basic' || $user_mongo['user_type'] == 'owner') {
            $campaign_obj->created_by = $user_mongo['id'];
            $campaign_obj->type = Campaign::$CAMPAIGN_USER_TYPE['user'];
        } else if ($user_mongo['user_type'] == 'bbi') {
            $campaign_obj->client_mongo_id = $user_mongo['client_mongo_id'];
            $campaign_obj->org_contact_email = isset($this->input['org_contact_email']) ? $this->input['org_contact_email'] : "";
            $campaign_obj->org_contact_name = isset($this->input['org_contact_name']) ? $this->input['org_contact_name'] : "";
            $campaign_obj->org_contact_phone = isset($this->input['org_contact_phone']) ? $this->input['org_contact_phone'] : "";
            $campaign_obj->org_name = isset($this->input['org_name']) ? $this->input['org_name'] : "";
            $campaign_obj->type = Campaign::$CAMPAIGN_USER_TYPE['bbi'];
            $campaign_obj->created_by = $user_mongo['id'];
        }
        if (isset($this->input['packages']) && !empty($this->input['packages'])) {
            $user_packages = ShortListedProduct::where([
                        ['user_mongo_id', '=', $user_mongo['id']],
                        ['format_type', '=', Format::$FORMAT_TYPE['metro']]
                    ])->get();
            foreach ($user_packages as $pkg) {
                $campaign_product = new CampaignProduct;
                $campaign_product->campaign_id = $campaign_obj->id;
                $campaign_product->user_mongo_id = $user_mongo['id'];
                $campaign_product->format_type = Format::$FORMAT_TYPE['metro'];
                $campaign_product->package_id = $pkg->package_id;
                $campaign_product->package_name = $pkg->package_name;
                $campaign_product->corridor_id = $pkg->corridor_id;
                $campaign_product->corridor_name = $pkg->corridor_name;
                $campaign_product->selected_trains = $pkg->selected_trains;
                //$campaign_product->selected_slots = $pkg->selected_slots;
                $campaign_product->start_date = $pkg->start_date;
                $campaign_product->months = $pkg->months;
                $campaign_product->price = $pkg->price;
                //  $campaign_product->start_date = new \DateTime($pkg->start_date);
                $end_date = $campaign_product->start_date;
              //  $campaign_product->end_date = date('Y-m-d', strtotime($end_date . ' + ' . $pkg->months . ' months'));
              if($campaign_product->months == '.5'){
              $campaign_product->end_date = date('Y-m-d', strtotime($end_date . ' + 15 days'));
        }
        else{
        $campaign_product->end_date = date('Y-m-d', strtotime($end_date . ' + ' . $pkg->months . ' months'));
        }
                $campaign_product->save();
            }
            $success = ShortListedProduct::where([
                        ['user_mongo_id', '=', $user_mongo['id']],
                        ['format_type', '=', Format::$FORMAT_TYPE['metro']]
                    ])->delete();
        }
        $campaign_obj->status = Campaign::$CAMPAIGN_STATUS['metro-campaign-created'];
        if ($campaign_obj->save()) {

            return response()->json(["status" => "1",'user_type'=>$user_mongo['user_type'], "metro_camp_id" => $metro_camp_id, "message" => "Metro Campaign saved successfully."]);
        } else {
            return response()->json(["status" => "0", "message" => "Failed to save campaign."]);
        }
    }

    public function getMetroCampaigns() {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];

        if ($user_mongo['user_type'] == 'basic' || $user_mongo['user_type'] == 'owner') {
            $metro_campaigns = Campaign::where([
                        ['created_by', '=', $user_mongo['id']],
                        ['format_type', '=', Format::$FORMAT_TYPE['metro']],
                    ])->orderBy('created_at', 'desc')->get()->toArray();
        } else if ($user_mongo['user_type'] == 'bbi') {
            $user_campaigns = Campaign::where([
                        ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['bbi']],
                        ['format_type', '=', Format::$FORMAT_TYPE['metro']]
                    ])->orderBy('created_at', 'desc')->get();
            $bbi_campaigns = Campaign::where([
                        ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['user']],
                        ['format_type', '=', Format::$FORMAT_TYPE['metro']]
                    ])->orderBy('created_at', 'desc')->get();
            $metro_campaigns = array_merge($user_campaigns->toArray(), $bbi_campaigns->toArray());
            //print_r( $metro_campaigns);
        }
        if ($metro_campaigns){
            $metro_details = array();
            $g = 0;
            foreach ($metro_campaigns as $metro_camp) {
                $camp_id = $metro_camp['id'];
                $product_count = CampaignProduct::where('campaign_id', '=', $camp_id)->count();
                if ($product_count)
                    $metro_camp['product_count'] = $product_count;
                else
                    $metro_camp['product_count'] = 0;
                $start_date = CampaignProduct::where('campaign_id', '=', $camp_id)->orderBy('start_date', 'asc')->take(1)->pluck('start_date')->toArray();

                $act_budget = CampaignProduct::raw(function($collection) use ($camp_id) {
                            return $collection->aggregate(
                                            [
                                                [
                                                    '$match' =>
                                                    [
                                                        "campaign_id" => $camp_id
                                                    ]
                                                ],
                                                [
                                                    '$group' =>
                                                    [
                                                        '_id' => '$campaign_id',
                                                        'total_price' => [
                                                            '$sum' => '$price'
                                                        ]
                                                    ]
                                                ]
                                            ]
                            );
                        });
                $metro_camp['act_budget'] = count($act_budget) > 0 ? $act_budget[0]->total_price : 0;
                /*$gststatus = isset($metro_camp['gststatus'])?$metro_camp['gststatus']:0;
                if($gststatus ==1){
                    $metro_camp['Metrototalamount'] = $metro_camp['act_budget']+round(($metro_camp['act_budget']*(0.18)),2);
                }
                else{
                     $metro_camp['Metrototalamount'] = $metro_camp['act_budget'];
                }*/
        $metro_camp['Metrototalamount'] = $metro_camp['act_budget'];
                if (!empty($start_date)) {
                    $metro_camp['start_date'] = $start_date[0];
                }
                $paid_group = CampaignPayment::raw(function($collection) use ($camp_id) {
                            return $collection->aggregate(
                                            [
                                                [
                                                    '$match' =>
                                                    [
                                                        "campaign_id" => $camp_id
                                                    ]
                                                ],
                                                [
                                                    '$group' =>
                                                    [
                                                        '_id' => '$campaign_id',
                                                        'paid' => [
                                                            '$sum' => '$amount'
                                                        ]
                                                    ]
                                                ]
                                            ]
                            );
                        });
                $metro_camp['campaign_paid'] = count($paid_group) > 0 ? $paid_group[0]->paid : 0;
                $metro_campaigns[$g] = $metro_camp;
                ++$g;
            }


            return response()->json($metro_campaigns);
        } else {
            return response()->json(['status' => 0, 'message' => "Invalid user."]);
        }
    }

    public function checkoutMetroCampaign($metro_campaign_id,$flag,$gst) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user_metro_campaign = Campaign::where([
                    ['id', '=', $metro_campaign_id],
                    ['created_by', '=', $user_mongo['id']],
                    ['format_type', '=', Format::$FORMAT_TYPE['metro']]
                ])->first();
        if (!isset($user_metro_campaign) || empty($user_metro_campaign)) {
            return response()->json(['status' => 0, 'message' => 'Campaign not found.']);
        }
        $campaign_packages = CampaignProduct::where([
                    ['campaign_id', '=', $metro_campaign_id]
                ])->get();
        if (!isset($campaign_packages) || count($campaign_packages) <= 0) {
            return response()->json(['status' => 0, 'message' => 'Please add some packages first.']);
        }
        $user_metro_campaign->status = Campaign::$CAMPAIGN_STATUS['metro-campaign-checked-out'];
        $user_metro_campaign->gststatus = $flag;
        $user_metro_campaign->gst_price = $gst;
        if ($user_metro_campaign->save()) {
            // TODO: send notification/email to admin

            $noti_array = [
                'type' => Notification::$NOTIFICATION_TYPE['metro-camp-locked'],
                'from_id' => $user_mongo['id'],
                'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
                'to_id' => null,
                'to_client' => null,
                'desc' => "Campaign Checkout",
                'message' => $user_mongo['first_name'] . " " . $user_mongo['last_name'] . " has checkout to " . $user_metro_campaign->name,
                'data' => ["campaign_id" => $user_metro_campaign->id]
            ];
            $bbi_sa_id = Client::where('company_name', '=', 'BBI')->first()->super_admin;
            $bbi_sa = UserMongo::where('user_id', '=', $bbi_sa_id)->first();
            $mail_array = [
                'mail_tmpl_params' => [
                    'sender_email' => $user_mongo['email'],
                    'receiver_name' => $bbi_sa->first_name . ' ' . $bbi_sa->last_name,
                    'mail_message' => $user_mongo['first_name'] . ' ' . $user_mongo['last_name'] . 'has checked out a metro campaign. Please contact Mr./Ms. ' . $user_mongo['first_name'] . 'for the payment.'
                ],
                'email_to' => $bbi_sa->email,
                'recipient_name' => $bbi_sa->first_name,
                //'subject' => 'Metro campaign checked out. - Billboards India'
                'subject' => 'Metro campaign checked out. - Advertising Marketplace'
            ];
            event(new metroCampaignLockedEvent($noti_array, $mail_array));
            $notification_obj = new Notification;
            $notification_obj->id = uniqid();
            $notification_obj->type = "metro-campaign";
            $notification_obj->from_id = $user_mongo['id'];
            $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['bbi'];
            $notification_obj->to_id = null;
            $notification_obj->to_client = null;
            $notification_obj->desc = "Campaign Checkout";
            $notification_obj->message = $user_mongo['first_name'] . " " . $user_mongo['last_name'] . " has checkout to " . $user_metro_campaign->name;
            $notification_obj->campaign_id = $user_metro_campaign->id;
            $notification_obj->status = 0;
            $notification_obj->save();

            return response()->json(['status' => 1, 'message' => 'Our executive will contact you soon.']);
        } else {
            return response()->json(['status' => 0, 'message' => 'There was some problem while checking out your campaign. Please try again later.']);
        }
    }

    public function updateMetroCampaignStatus() {
        $this->validate($this->request, [
            'metro_campaign_id' => 'required',
            'amount' => 'required',
            'type' => 'required',
            'received_by' => 'required'
                ], [
            'metro_campaign_id.required' => 'Campaign id is required',
            'amount.required' => 'Amount is required',
            'type.required' => 'Type is required',
            'received_by.required' => 'Field "Received By" is required'
                ]
        );
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        if ($user_mongo['user_type'] != "bbi") {
            return response()->json(['status' => 0, "message" => "You're not authorized to perform this action."]);
        }
        $metro_campaign_id = $this->input['metro_campaign_id'];
        $metro_campaign = Campaign::where([
                    ['id', '=', $metro_campaign_id],
                    ['format_type', '=', Format::$FORMAT_TYPE['metro']]
                ])->first();
        if (!isset($metro_campaign) || empty($metro_campaign)) {
            return response()->json(['status' => 0, 'message' => 'Campaign not found.']);
        }
        $input = $this->input;
        if ($input['type'] != "Cash" && !isset($input['reference_no'])) {
            return response()->json(["status" => 0, "message" => "Cheque/Reference/Transaction No. is required in case of payment made other than by cash."]);
        }
        $act_budget_group = CampaignProduct::raw(function($collection) use ($metro_campaign_id) {
                    return $collection->aggregate(
                                    [
                                        [
                                            '$match' =>
                                            [
                                                "campaign_id" => $metro_campaign_id
                                            ]
                                        ],
                                        [
                                            '$group' =>
                                            [
                                                '_id' => '$metro_campaign_id',
                                                'total_price' => [
                                                    '$sum' => '$price'
                                                ]
                                            ]
                                        ]
                                    ]
                    );
                });
        $campaign_act_budget = count($act_budget_group) > 0 ? $act_budget_group[0]->total_price : 0;
        $paid_group = CampaignPayment::raw(function($collection) use ($metro_campaign_id) {
                    return $collection->aggregate(
                                    [
                                        [
                                            '$match' =>
                                            [
                                                "campaign_id" => $metro_campaign_id
                                            ]
                                        ],
                                        [
                                            '$group' =>
                                            [
                                                '_id' => '$metro_campaign_id',
                                                'paid' => [
                                                    '$sum' => '$amount'
                                                ]
                                            ]
                                        ]
                                    ]
                    );
                });
        $campaign_paid = count($paid_group) > 0 ? $paid_group[0]->paid : 0;
        /*$gst_price = isset($metro_campaign->gst_price)?$metro_campaign->gst_price:0;
          $campaign_act_budget = ($campaign_act_budget+$gst_price);*/

          /*$gststatus = isset($metro_campaign->gststatus)?$metro_campaign->gststatus:0;
                if($gststatus ==1){
                    $campaign_act_budget = $campaign_act_budget+round(($campaign_act_budget*(0.18)),2);
                }
                else{
                     $campaign_act_budget = $campaign_act_budget;
                }*/
                $campaign_act_budget = $campaign_act_budget;
        $remaining_campaign_payment = $campaign_act_budget - $campaign_paid;
        if ($input['amount'] > $remaining_campaign_payment) {
            return response()->json(['status' => 0, 'message' => 'The given amount can not be larger than the pending amount.']);
        }
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $campaign_payment = new CampaignPayment;
        $campaign_payment->campaign_id = isset($input['metro_campaign_id']) ? $input['metro_campaign_id'] : "";
        $campaign_payment->amount = isset($input['amount']) ? (int) $input['amount'] : "";
        $campaign_payment->type = isset($input['type']) ? $input['type'] : "";
        $campaign_payment->reference_no = isset($input['reference_no']) ? $input['reference_no'] : "";
        $campaign_payment->received_by = isset($input['received_by']) ? $input['received_by'] : "";
        $campaign_payment->updated_by_id = $user_mongo['id'];
        $campaign_payment->updated_by_name = $user_mongo['first_name'] . " " . $user_mongo['last_name'];
        $campaign_payment->client_mongo_id = $user_mongo['client_mongo_id'];
        $campaign_payment->campaign_format_type = Format::$FORMAT_TYPE['metro'];
        // $payment_img_path = base_path() . '/html/uploads/images/campaign_payments';
        // if ($this->request->hasFile('image')) {
        //  if($this->request->file('image')->move($payment_img_path, $this->request->file('image')->getClientOriginalName())){
        //      $campaign_payment->image = "/uploads/images/campaign_payments/" . $this->request->file('image')->getClientOriginalName();
        //  }
        // }
        if ($campaign_payment->save()) {
            $check_status = Campaign::where('id', '=', $metro_campaign_id)->where('status', '=', Campaign::$CAMPAIGN_STATUS['metro-campaign-running'])->count();
            if ($check_status == 0) {
                $metro_campaign->status = Campaign::$CAMPAIGN_STATUS['metro-campaign-locked'];
            } else {
                $metro_campaign->status = Campaign::$CAMPAIGN_STATUS['metro-campaign-running'];
            }

            if ($metro_campaign->save()) {

                // send the email to user.
                $campaign_user_data = (object) [];
                if ($metro_campaign->type == Campaign::$CAMPAIGN_USER_TYPE['user']) {
                    $campaign_user_mongo = UserMongo::where('id', '=', $metro_campaign->created_by)->first();
                    $campaign_user_data->org_name = $campaign_user_mongo->company_name;
                    $campaign_user_data->contact_name = $campaign_user_mongo->first_name . ' ' . $campaign_user_mongo->last_name;
                    $campaign_user_data->contact_phone = $campaign_user_mongo->phone;
                    $campaign_user_data->contact_email = $campaign_user_mongo->email;
                } else if ($metro_campaign->type == Campaign::$CAMPAIGN_USER_TYPE['bbi']) {
                    $campaign_user_data->org_name = $metro_campaign->org_name;
                    $campaign_user_data->contact_name = $metro_campaign->org_contact_name;
                    $campaign_user_data->contact_phone = $metro_campaign->org_contact_phone;
                    $campaign_user_data->contact_email = $metro_campaign->org_contact_email;
                } else {
                    // TODO: only matters after owner metro campaigns are implemented.
                }

                $campaign_user_mongo = UserMongo::where('id', '=', $metro_campaign->created_by)->first();
                $noti_array = [
                    'type' => Notification::$NOTIFICATION_TYPE['metro-camp-locked'],
                    'from_id' => null,
                    'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['user'],
                    'to_id' => $metro_campaign->created_by,
                    'to_client' => $metro_campaign->created_by,
                    'desc' => "Campaign Payment Confirmed",
                    //'message' => "Your payment has been received by Billboards India.",
                    'message' => "Your payment has been received by Advertising Marketplace.",
                    'data' => ["campaign_id" => $metro_campaign->id]
                ];
                $mail_array = [
                    'mail_tmpl_params' => [
                        'sender_email' => config('app.bbi_email'),
                        'receiver_name' => $campaign_user_data->contact_name,
                        'mail_message' => "Payment for your campaign '" . $metro_campaign->name . "' has been confirmed. Your campaign will be launched on selected date. Plese visit our website for more information."
                    ],
                    'email_to' => $campaign_user_data->contact_email,
                    'recipient_name' => $campaign_user_data->contact_name,
                    //'subject' => 'Campaign payment confirmed! - Billboards India'
                    'subject' => 'Campaign payment confirmed! - Advertising Marketplace'
                ];
                event(new metroCampaignLockedEvent($noti_array, $mail_array));

                $notification_obj = new Notification;
                $notification_obj->id = uniqid();
                $notification_obj->type = "metro-campaign";
                $notification_obj->from_id = null;
                $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['user'];
                $notification_obj->to_id = $metro_campaign->created_by;
                $notification_obj->to_client = $metro_campaign->created_by;
                $notification_obj->desc = "Campaign Payment Confirmed";
                //$notification_obj->message = "Your payment has been received by Billboards India.";
                $notification_obj->message = "Your payment has been received by Advertising Marketplace.";
                $notification_obj->campaign_id = $metro_campaign->id;
                $notification_obj->status = 0;
                $notification_obj->save();

                return response()->json(["status" => "1", "message" => "Campaign payment updated successfully."]);
            } else {
                return response()->json(['status' => 0, 'message' => 'Campaign payment updated, but campaign status failed to change.']);
            }
        } else {
            return response()->json(["status" => "0", "message" => "There was a technical error while updating the payment. Please try again later."]);
        }
    }

    public function getMetroCampaignDetails($metro_camp_id) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];

        if ($user_mongo['user_type'] == 'basic' || $user_mongo['user_type'] == 'owner') {
            $metro_camp = Campaign::where([
                        ['id', '=', $metro_camp_id],
                        ['created_by', '=', $user_mongo['id']],
                        ['format_type', '=', Format::$FORMAT_TYPE['metro']]
                    ])->first();
            if (!isset($metro_camp) || empty($metro_camp)) {
                return response()->json(['status' => 0, 'message' => "Campaign not found."]);
            }
            $user_details = UserMongo::where('id', '=', $metro_camp->created_by)->first();
            $metro_camp->user_details = $user_details;
        } else if ($user_mongo['user_type'] == 'bbi') {
            $metro_camp = Campaign::where([
                        ['id', '=', $metro_camp_id],
                        ['format_type', '=', Format::$FORMAT_TYPE['metro']]
                    ])->first();
            if (!isset($metro_camp) || empty($metro_camp)) {
                return response()->json(['status' => 0, 'message' => "Campaign not found."]);
            }
            if ($metro_camp->type == Campaign::$CAMPAIGN_USER_TYPE['user']) {
                $user_details = UserMongo::where('id', '=', $metro_camp->created_by)->first();
                $metro_camp->user_details = $user_details;
            } else if ($metro_camp->type == Campaign::$CAMPAIGN_USER_TYPE['bbi']) {
                
            } else {
                return response()->json(['status' => 0, 'message' => "Invalid metro campaign."]);
            }
        } else {
            return response()->json(['status' => 0, 'message' => "Invalid user."]);
        }
        $campaign_packages = CampaignProduct::where('campaign_id', '=', $metro_camp->id)->get();

        $start_date = CampaignProduct::where('campaign_id', '=', $metro_camp->id)->orderBy('start_date', 'asc')->take(1)->pluck('start_date')->toArray();
        //print_r($start_date);
        $metro_camp->packages = $campaign_packages;
        $act_budget = CampaignProduct::raw(function($collection) use ($metro_camp_id) {
                    return $collection->aggregate(
                                    [
                                        [
                                            '$match' =>
                                            [
                                                "campaign_id" => $metro_camp_id
                                            ]
                                        ],
                                        [
                                            '$group' =>
                                            [
                                                '_id' => '$campaign_id',
                                                'total_price' => [
                                                    '$sum' => '$price'
                                                ]
                                            ]
                                        ]
                                    ]
                    );
                });
        $metro_camp->act_budget = count($act_budget) > 0 ? $act_budget[0]->total_price : 0;
        if (!empty($start_date)) {
            $metro_camp->start_date = $start_date[0];
        }
        $paid_group = CampaignPayment::raw(function($collection) use ($metro_camp_id) {
                    return $collection->aggregate(
                                    [
                                        [
                                            '$match' =>
                                            [
                                                "campaign_id" => $metro_camp_id
                                            ]
                                        ],
                                        [
                                            '$group' =>
                                            [
                                                '_id' => '$campaign_id',
                                                'paid' => [
                                                    '$sum' => '$amount'
                                                ]
                                            ]
                                        ]
                                    ]
                    );
                });
        $campaign_paid = count($paid_group) > 0 ? $paid_group[0]->paid : 0;
        /*$gststatus = isset($metro_camp->gststatus)?$metro_camp->gststatus:0;
                if($gststatus ==1){
                    $metro_camp->Metrototalamount = $metro_camp->act_budget+round(($metro_camp->act_budget*(0.18)),2);
                }
                else{
                     $metro_camp->Metrototalamount = $metro_camp->act_budget;
                }*/
         $metro_camp->Metrototalamount = $metro_camp->act_budget;
        $remaining_campaign_payment = $metro_camp->Metrototalamount - $campaign_paid;
        $metro_camp->pending_payment = $remaining_campaign_payment;
        return response()->json($metro_camp);
    }

    public function addPackageToMetroCampaign() {
        if (isset($this->input['edit_id'])) {
            $campaign_product = CampaignProduct::where('_id', '=', $this->input['edit_id'])->first();
            $campaign_product->price = $this->input['price'];
            $campaign_product->start_date = $this->input['start_date'];
            if ($campaign_product->save()) {
                return response()->json(["status" => "1", "message" => "Price changed successfully."]);
            } else {
                return response()->json(["status" => "0", "message" => "An error occured while saving."]);
            }
        }
        $this->validate($this->request, [
            'campaign_id' => 'required',
            'package_id' => 'required',
            //'selected_slots' => 'required',
            'selected_trains' => 'required',
            'start_date' => 'required',
            'price' => 'required',
            'months' => 'required'
                ], [
            'campaign_id.required' => 'Campaign Id is required',
            'package_id.required' => 'Package Id is required',
            //'selected_slots.required' => 'Selected no. of slots is required',
            'selected_trains.required' => 'Selected no. of trains is required',
            'start_date.required' => 'Start Date is required',
            'price.required' => 'Price is required',
            'months.required' => 'Months is required'
                ]
        );
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        if ($user_mongo['user_type'] == 'bbi') {
            $campaign_obj = Campaign::where([
                        ['id', '=', $this->input['campaign_id']],
                        ['format_type', '=', Format::$FORMAT_TYPE['metro']]
                    ])->first();
        } else if ($user_mongo['user_type'] == 'basic' || $user_mongo['user_type'] == 'owner') {
            $campaign_obj = Campaign::where([
                        ['id', '=', $this->input['campaign_id']],
                        ['format_type', '=', Format::$FORMAT_TYPE['metro']],
                        ['created_by', '=', $user_mongo['id']]
                    ])->first();
        } else {
            return response()->json(['status' => 0, 'message' => "You are not authorized to perform this action."]);
        }
        if (!isset($campaign_obj) || empty($campaign_obj)) {
            return response()->json(['status' => 0, 'message' => 'Campaign not found.']);
        }
        $already_added = CampaignProduct::where([
                    ['campaign_id', '=', $this->input['campaign_id']],
                    ['package_id', '=', $this->input['package_id']]
                ])->get();
        if (count($already_added) > 0) {
            return response()->json(['status' => 0, 'message' => 'You have already added this package.']);
        }
        $package = MetroPackage::where('id', '=', $this->input['package_id'])->first();
        $campaign_product = new CampaignProduct;
        $campaign_product->campaign_id = $campaign_obj->id;
        $campaign_product->format_type = Format::$FORMAT_TYPE['metro'];
        $campaign_product->package_id = $package->id;
        $campaign_product->package_name = $package->name;
        $campaign_product->corridor_id = $package->corridor_id;
        $campaign_product->corridor_name = $package->corridor;
        $campaign_product->selected_trains = $this->input['selected_trains'];

        $campaign_product->start_date = $this->input['start_date'];
        $campaign_product->months = $this->input['months'];
        //$campaign_product->selected_slots = $this->input['max_slots'] * $this->input['days'];
        $campaign_product->price = $this->input['price_new'];
        //$campaign_product->start_date = new \DateTime($this->input['start_date']);
        $end_date = $campaign_product->start_date;
        if( $campaign_product->months == '.5'){
              $campaign_product->end_date = date('Y-m-d', strtotime($end_date . ' +  15 days'));
        }
        else{
        $campaign_product->end_date = date('Y-m-d', strtotime($end_date . ' + ' . $this->input['months'] . ' months'));
        }
        if ($campaign_product->save()) {
            return response()->json(["status" => "1", "message" => "Package added to campaign successfully."]);
        } else {
            return response()->json(["status" => "0", "message" => "An error occured while adding the package."]);
        }
    }

    public function launchMetroCampaign($metro_campaign_id) {
        $campaign = Campaign::where([
                    ['status', '=', Campaign::$CAMPAIGN_STATUS['metro-campaign-locked']],
                    ['format_type', '=', Format::$FORMAT_TYPE['metro']],
                    ['id', '=', $metro_campaign_id]
                ])->first();
        if (!isset($campaign) || empty($campaign)) {
            return response()->json(['status' => 0, 'message' => 'Campaign referenced not found in database.']);
        }
        $campaign->status = Campaign::$CAMPAIGN_STATUS['metro-campaign-running'];
        if ($campaign->save()) {
            // notifications and emails
            $campaign_user_mongo = UserMongo::where('id', '=', $campaign->created_by)->first();
            if ($campaign->type == Campaign::$CAMPAIGN_USER_TYPE['user']) {

                $campaign_user_mongo = UserMongo::where('id', '=', $campaign->created_by)->first();

                $noti_array = [
                    'type' => Notification::$NOTIFICATION_TYPE['metro-camp-launched'],
                    'from_id' => null,
                    'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['user'],
                    'to_id' => $campaign->created_by,
                    'to_client' => $campaign->created_by,
                    'desc' => "Metro campaign launched",
                    'message' => "Your metro campaign has been launched!",
                    'data' => ["campaign_id" => $campaign->id]
                ];
                $mail_array = [
                    'mail_tmpl_params' => [
                        'sender_email' => $campaign_user_mongo['email'],
                        'receiver_name' => $campaign_user_mongo->first_name,
                        'mail_message' => "Your metro campaign '" . $campaign->name . "' has been launched. Visit our website to see details."
                    ],
                    'email_to' => $campaign_user_mongo->email,
                    'recipient_name' => $campaign_user_mongo->first_name,
                    //'subject' => 'Metro campaign launched! - Billboards India'
                    'subject' => 'Metro campaign launched! - Advertising Marketplace'
                ];
                event(new metroCampaignLaunchEvent($noti_array, $mail_array));
                $notification_obj = new Notification;
                $notification_obj->id = uniqid();
                $notification_obj->type = "metro-campaign";
                $notification_obj->from_id = null;
                $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['user'];
                $notification_obj->to_id = $campaign->created_by;
                $notification_obj->to_client = $campaign->created_by;
                $notification_obj->desc = "Metro Campaign launched";
                $notification_obj->message = "Your metro campaign has been launched!";
                $notification_obj->campaign_id = $campaign->id;
                $notification_obj->status = 0;
                $notification_obj->save();
            } else if ($campaign->type == Campaign::$CAMPAIGN_USER_TYPE['bbi']) {

                $mail_tmpl_params = [
                    'sender_email' => $campaign_user_mongo['email'],
                    'receiver_name' => $campaign->org_contact_name,
                    'mail_message' => "Your metro campaign '" . $campaign->name . "' has been launched. Visit our website to see details."
                ];
                $mail_data = [
                    'email_to' => $campaign->org_contact_email,
                    'recipient_name' => $campaign->org_contact_name
                ];
                Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data) {
                    //$message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Metro campaign launched! - Billboards India');
                    $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Metro campaign launched! - Advertising Marketplace');
                });
            }
            return response()->json(["status" => "1", "message" => "The campaign has been successfully launched."]);
        } else {
            return response()->json(["status" => "0", "message" => "There was a technical error in launching the campaign. Please try again."]);
        }
    }

    public function closeMetroCampaign($campaign_id) {
        $campaign = Campaign::where([
                    ['status', '=', Campaign::$CAMPAIGN_STATUS['metro-campaign-running']],
                    ['format_type', '=', Format::$FORMAT_TYPE['metro']],
                    ['id', '=', $campaign_id]
                ])->first();
        $campaign->status = Campaign::$CAMPAIGN_STATUS['metro-campaign-stopped'];
        if ($campaign->save()) {
            $campaign_products = CampaignProduct::where('campaign_id', '=', $campaign->id)->get();
            foreach ($campaign_products as $cam_pro) {
                $cam_pro->active_status = 'closed';
                $cam_pro->save();
            }
            $user = JWTAuth::parseToken()->getPayload()['user'];
            $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
            // send the email to user.
            $campaign_user_mongo = UserMongo::where('id', '=', $campaign->created_by)->first();
            $noti_array = [
                'type' => Notification::$NOTIFICATION_TYPE['metro-camp-closed'],
                'from_id' => null,
                'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['user'],
                'to_id' => $campaign->created_by,
                'to_client' => $campaign->created_by,
                'desc' => "Metro Campaign closed",
                'message' => "Your campaign has been completed and closed successfully",
                'data' => ["campaign_id" => $campaign->id]
            ];
            $mail_array = [
                'mail_tmpl_params' => [
                    'sender_email' => $user['email'],
                    'receiver_name' => $campaign_user_mongo->first_name,
                    'mail_message' => "Your campaign '" . $campaign->name . "' has been closed successfully. Visit our website for further details."
                ],
                'email_to' => $campaign_user_mongo->email,
                'recipient_name' => $campaign_user_mongo->first_name,
                //'subject' => 'Campaign closed - Billboards India'
                'subject' => 'Campaign closed - Advertising Marketplace'
            ];
            event(new metroCampaignClosedEvent($noti_array, $mail_array));
            $notification_obj = new Notification;
            $notification_obj->id = uniqid();
            $notification_obj->type = "metro-campaign";
            $notification_obj->from_id = null;
            $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['user'];
            $notification_obj->to_id = $campaign->created_by;
            $notification_obj->to_client = $campaign->created_by;
            $notification_obj->desc = "Metro Campaign closed";
            $notification_obj->message = "Your campaign has been completed and closed successfully.";
            $notification_obj->campaign_id = $campaign->id;
            $notification_obj->status = 0;
            $notification_obj->save();
            return response()->json(["status" => "1", "message" => "The campaign has been successfully closed."]);
        } else {
            return response()->json(["status" => "0", "message" => "There was a technical error in closing the campaign. Please try again."]);
        }
    }

    public function deleteMetroProductFromCampaign($campaign_id, $product_id) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user_internal = User::where('id', '=', $user_mongo['user_id'])->first();
        if (isset($user_internal->client)) {
            $user_type = $user_internal->client->client_type->type;
        } else {
            $user_type = "basic";
        }
        if ($user_type == "bbi") {
            // bbi campaign
            $campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        ['type', '=', CAMPAIGN::$CAMPAIGN_USER_TYPE['bbi']]
                    ])->orWhere([
                        ['id', '=', $campaign_id],
                        ['type', '=', CAMPAIGN::$CAMPAIGN_USER_TYPE['user']]
                    ])->first();
            if (!isset($campaign) || empty($campaign)) {
                return response()->json(["status" => 0, "message" => "campaign referred not found in the database."]);
            }
            if ($campaign->status == Campaign::$CAMPAIGN_STATUS['metro-campaign-locked'] || $campaign->status == Campaign::$CAMPAIGN_STATUS['metro-campaign-running'] || $campaign->status == Campaign::$CAMPAIGN_STATUS['metro-campaign-stopped']) {
                return response()->json(["status" => "0", "message" => "You can not remove a product because its appear in running"]);
            }
            $campaign_product = CampaignProduct::where([
                        ['campaign_id', '=', $campaign_id],
                        ['package_id', '=', $product_id]
                    ])->first();
            if (isset($campaign_product) && !empty($campaign_product)) {
                $campaign_product->delete();
                return response()->json(['status' => 1, 'message' => 'Product removed from campaign successfully.']);
            } else {
                return response()->json(['status' => 0, 'message' => 'You are not authorized to delete this product']);
            }
        } else if ($user_type == "basic" || $user_type == "owner") {
            // user campaign

            $filter_criteria = [
                ['id', '=', $campaign_id],
                ['created_by', '=', $user_mongo['id']]
            ];
            $campaign = Campaign::where($filter_criteria)->first();
                    if (!isset($campaign) || empty($campaign)) {
                return response()->json(["status" => 0, "message" => "campaign referred not found in the database."]);
            }
            if ($campaign->status == Campaign::$CAMPAIGN_STATUS['metro-campaign-locked'] || $campaign->status == Campaign::$CAMPAIGN_STATUS['metro-campaign-running'] || $campaign->status == Campaign::$CAMPAIGN_STATUS['metro-campaign-stopped']) {
                return response()->json(["status" => "0", "message" => "You can not remove a product because its appear in running"]);
            }
            $campaign_product = CampaignProduct::where([
                        ['campaign_id', '=', $campaign_id],
                        ['package_id', '=', $product_id]
                    ])->first();
            if (isset($campaign_product) && !empty($campaign_product)) {
                $campaign_product->delete();
            } else {
                return response()->json(['status' => 0, 'message' => 'You are not authorized to delete this product']);
            }
        } else {
            return response()->json(['status' => 0, 'message' => 'You are not authorized to delete this product']);
        }
        return response()->json(["status" => "1", "message" => "Product deleted from campaign successfully."]);
    }

    public function deleteMetroCampaign($campaign_id) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        if ($user_mongo['user_type'] == "bbi") {
            $campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['bbi']]
                    ])->first();
        } else if ($user_mongo['user_type'] == "basic") {
            $campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['user']],
                        ['created_by', '=', $user_mongo['id']]
                    ])->first();
        } else {
            return response()->json(['status' => 0, 'message' => 'Invalid user.']);
        }
        if (isset($campaign) && !empty($campaign) && $campaign->status == (Campaign::$CAMPAIGN_STATUS['metro-campaign-created'] )) {
            if ($campaign->delete()) {
                return response()->json(['status' => 1, 'message' => "Campaign deleted successfully."]);
            } else {
                return response()->json(['status' => 0, 'message' => "Error deleting campaign."]);
            }
        } else {
            return response()->json(['status' => 0, 'message' => "You can not delete this campaign at this stage."]);
        }
    }

    /* ======================================================
      ///////// Metro Campaigns related actions end //////////
      ====================================================== */
      
    public function downloadCampaignQuote($campaign_id) {
		$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $shortlisted_products_arr = [];
        $shortlistedsum = 0;
		$shortlistedsumcpm = 0;
        $offershortlistedsum = 0;
		$offershortlistedsumcpm = 0;
        $cpmsum = 0;
        $offercpmsum = 0;
        $impressionSum = 0;
        $impressionSum4 = 0;
        $newofferStripepercentamtSum = 0;
        $newprocessingfeeamtSum = 0;
		$tax_percentage_booking = 0;
		$tax_percentage_amount = 0;

        try {
            $user = JWTAuth::parseToken()->getPayload()['user'];
            $campaign = Campaign::where('id', '=', $campaign_id)->first();
            //if($campaign->status < 1000){
			if($campaign->status <= 2000){
            $product_ids_in_campaign = ProductBooking::where('campaign_id', '=', $campaign_id)->pluck('product_id');
            $products_in_campaign = Product::whereIn('id', $product_ids_in_campaign)->get();
            $formats = $products_in_campaign->unique('type')->count();
            $areas = $products_in_campaign->unique('area')->count();
            $audience_reach = $products_in_campaign->each(function($v, $k) {
                        $v->impressions = intval(str_replace(",", "", $v->impressions));
                    })->sum('impressions');
            $repeated_audience = $audience_reach * 30 / 100;
            
             $campaign_products = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
        $products_arr = [];
        if (isset($campaign_products) && count($campaign_products) > 0) {
            foreach ($campaign_products as $campaign_product) {
                $product =Product::where('id', '=', $campaign_product->product_id)->first();
                //array_push($products_arr, array_merge(Product::where('id', '=', $campaign_product->product_id)->first()->toArray(), $campaign_product->toArray()));
            }
          
        }

        $getcampaigntot = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
        $campaign->total_paid = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('amount');
        $camptot = 0;
		$offer_applied = 0;
		$tax_percentage_booking_tot = 0;
		$tax_percentage_amount_tot = 0;
        if (isset($getcampaigntot) && count($getcampaigntot) > 0) {
            foreach ($getcampaigntot as $getcampaigntot) {
				$tax_percentage_booking_tot = 0;
				$tax_percentage_amount_tot = 0;
                $getcampaigntotproduct =Product::where('id', '=', $getcampaigntot->product_id)->first();
				
                $bookedfrom[] = strtotime($getcampaigntot->booked_from);
                $bookedto[] = strtotime($getcampaigntot->booked_to);

                $getproductDetails =Product::where('id', '=', $getcampaigntot->product_id)->first();
                $diff=date_diff(date_create($getcampaigntot->booked_from),date_create($getcampaigntot->booked_to));
                $daysCount = $diff->format("%a");
                $daysCountCPM = $daysCount + 1;
					$perdayprice = $getproductDetails->default_price/28;

                if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
                    $price = $getcampaigntot->price*$getcampaigntot->quantity;
					if($campaign->total_paid != 0){
						if(isset($getcampaigntot->tax_percentage)){
							$tax_percentage_booking_tot = $getcampaigntot->tax_percentage;
						}else{
							$tax_percentage_booking_tot = 0;
						}
					}else{
						$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
					}
					if($tax_percentage_booking_tot != 0){
						$tax_percentage_amount_tot = ($price * ($tax_percentage_booking_tot))/100;
					}
					$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
                    $camptot += $price+$tax_percentage_amount_tot;
                }else{
                    /*$price = $getproductDetails->default_price;
                    $priceperday = $price/28;
                    $priceperselectedDates = $priceperday * $daysCountCPM;
                    $camptot += $priceperselectedDates;*/
					$priceperselectedDates = $getcampaigntot->price;
					
					if($campaign->total_paid != 0){
						if(isset($getcampaigntot->tax_percentage)){
							$tax_percentage_booking_tot = $getcampaigntot->tax_percentage;
						}else{
							$tax_percentage_booking_tot = 0;
						}
					}else{
						$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
					}
					if($tax_percentage_booking_tot != 0){
						$tax_percentage_amount_tot = ($priceperselectedDates * ($tax_percentage_booking_tot))/100;
					}
					$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
                    $camptot += ($priceperselectedDates*$getcampaigntot->quantity)+($tax_percentage_amount_tot*$getcampaigntot->quantity);
                }
            }
        }
			if($user_mongo['user_type'] == 'owner'){	
				$campaignproducts = ProductBooking::where([
                    ['campaign_id', '=', $campaign_id],
                    ['product_owner', '=', $user_mongo['client_mongo_id']]
                ])->orderBy('booked_from','ASC')->orderBy('booked_to','ASC')->get();
			}else{
				$campaignproducts = ProductBooking::where('campaign_id', '=', $campaign_id)->orderBy('booked_from','ASC')->orderBy('booked_to','ASC')->get();
			}
        
        if (isset($campaignproducts) && count($campaignproducts) > 0) {
            
			
            foreach ($campaignproducts as $campaignproduct) {
				$campaignproduct->tax_percentage_amount_shortlist = 0;
				$campaignproduct->tax_percentage_amount = 0;
				$tax_percentage_booking = 0;
				$tax_percentage_amount = 0;
                $getproductDetails =Product::where('id', '=', $campaignproduct->product_id)->first();
                //echo "<pre>campaignproduct"; print_r($campaignproducts);exit;
                $product = Product::where('id', '=', $campaignproduct->product_id)->first();
                $area_time_zone_value = Area::select("area_time_zone","area_time_zone_type")->where('id', '=', $product->area)->first();
				$campaignproduct->area_time_zone_type = $area_time_zone_value['area_time_zone_type'];
				
                /*CPM Calculation*/
                $diff=date_diff(date_create($campaignproduct->booked_from),date_create($campaignproduct->booked_to));
                //$diff=date_diff(($booked_from_date),($booked_to_date));
                $daysCount = $diff->format("%a");//exit;
                $daysCountCPM = $daysCount + 1;
					$perdayprice = $getproductDetails->default_price/28;
                //$daysCoun1tCPM = $daysCount;
                //echo "<pre>diff";print_r($diff);exit;
                //echo "<pre>product";print_r($product);exit;
                //echo $daysCoun1tCPM;exit;
                
                //$price = $campaignproduct->price;
                if(isset($product->fix) && $product->fix=="Fixed"){
                    /*$price = $product->default_price;
                    $priceperday = $price;//exit;
                    $priceperselectedDates = $priceperday;
                    $shortlistedsum+= $priceperselectedDates;
                    $campaignproduct->price = $priceperselectedDates;
                    $cpmsum+= $product->cpm;
                    $impressions = $product->secondImpression;
                    $impressionsperday = (int)($impressions);
                    $impressionsperselectedDates = $impressionsperday;//exit;
                    
                    if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                        $impressionsperselectedDates = $impressionsperselectedDates;
                    }else{
                        $impressionsperselectedDates = 1;
                    }
                    $impressionSum+= $impressionsperselectedDates;
                    $campaignproduct->secondImpression = round($impressionsperselectedDates, 2);
                    $cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                    $campaignproduct->cpmperselectedDates = $cpmcal;
                    $campaignproduct->cpm = $cpmcal;
                    $campaignproduct->impressionsperselectedDates = $impressionsperselectedDates;
                    $campaignproduct->priceperselectedDates = $priceperselectedDates;*/
                    $offerDetails = MakeOffer::where([
                        ['campaign_id', '=', $campaignproduct->campaign_id],
                        ['status', '=', 20],
                    ])->get();

                    if(isset($offerDetails) && count($offerDetails)==1){
                        //echo 'offer exists';exit;
                        foreach($offerDetails as $offerDetails){
                             $offerprice = $offerDetails->price;
                             $stripe_percent=$getproductDetails->stripe_percent;
                             
                        //$price = $getproductDetails->default_price;
                        $price = $campaignproduct->price;
                        
                        //$price = $campaign_product->price;
                        $priceperday = $price;
                        $priceperselectedDates = $priceperday;
                        $newpricepercentage = ($priceperselectedDates/$camptot) * 100;
        
                        //$newofferprice = ($offerprice * ($newpricepercentage))/100;
						if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
							$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
							$newofferprice = $newofferprice_wq*$campaignproduct->quantity;
						}else{
							$newofferprice = ($offerprice * ($newpricepercentage))/100;
						}
                        //$offerpriceperday = $newofferprice/28;//exit;
                        //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                        $offerpriceperselectedDates = $newofferprice;
                        $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
                        $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                        $campaignproduct->stripe_percent = $stripe_percent;
                           }
								$offer_applied = 1;
                        }else{
                             //$offerprice = $getproductDetails->default_price;
                             //echo 'no offer exists';exit;
                             $offerprice = $campaignproduct->price;
                             //$offerprice = $getproductDetails->default_price;
                             $stripe_percent=$getproductDetails->stripe_percent;
                             //$price = $getproductDetails->default_price;
                             $price = $campaignproduct->price;
                             //$price = $campaign_product->price;
                             $priceperday = $price;//exit;
                             $priceperselectedDates = $priceperday;
                             $newpricepercentage = ($priceperselectedDates/$camptot) * 100;
            
							if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
                                $newofferprice = $offerprice*$campaignproduct->quantity;
							}else{
								$newofferprice = $offerprice ;
							}
                        //$offerpriceperday = $newofferprice/28;//exit;
                        //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                            $offerpriceperselectedDates = $newofferprice;
                            $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
                            $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                            $campaignproduct->stripe_percent = $stripe_percent;
                        }
                        
                        if($campaign->total_paid != 0){
							if(isset($campaignproduct->tax_percentage)){
								$tax_percentage_booking = $campaignproduct->tax_percentage;
							}else{
								$tax_percentage_booking = 0;
							}
						}else{
							$tax_percentage_booking = $getproductDetails->tax_percentage;
						}
						if($tax_percentage_booking != 0){
							$campaignproduct->tax_percentage_amount_shortlist = ($priceperselectedDates * ($tax_percentage_booking))/100;
							$tax_percentage_amount = ($offerpriceperselectedDates * ($tax_percentage_booking))/100;
						}
						$campaignproduct->tax_percentage_amount = round($tax_percentage_amount,2);
                                            
                        $shortlistedsum+= ($priceperselectedDates+$campaignproduct->tax_percentage_amount_shortlist)*$campaignproduct->quantity;
                        $shortlistedsumcpm+= $priceperselectedDates*$campaignproduct->quantity;
						$campaignproduct->price = $priceperselectedDates;

						$offershortlistedsum+= $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;
                        $campaignproduct->offerprice = $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;

						$offershortlistedsumcpm += $offerpriceperselectedDates;
                        $cpmsum+= $getproductDetails->cpm;
                        $impressions = $getproductDetails->secondImpression;
                        $impressionsperday = (float)($impressions/7);
                        $impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
                        
                        if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                            $impressionsperselectedDates = $impressionsperselectedDates;
                        }else{
                            $impressionsperselectedDates = 1;
                        }
                        //$impressionSum+= $product_details->secondImpression; 
                        $impressionSum+= $impressionsperselectedDates*$campaignproduct->quantity;
                        $campaignproduct->secondImpression = round($impressionsperselectedDates, 2);
                        //$cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                        //$offercpmcal = ($offershortlistedsum/$impressionSum) * 1000;
                        $cpmcal = ($priceperselectedDates/$impressionsperselectedDates) * 1000;
                        $offercpmcal = ($offerpriceperselectedDates/$impressionsperselectedDates) * 1000;
                        $campaignproduct->cpmperselectedDates = $cpmcal;
                        $campaignproduct->offercpmperselectedDates = $offercpmcal;
                        $campaignproduct->cpm = $cpmcal;
                        $campaignproduct->offercpm = $offercpmcal;
                        $campaignproduct->impressionsperselectedDates = $impressionsperselectedDates;
                        $campaignproduct->priceperselectedDates = $priceperselectedDates;
                        $campaignproduct->offerpriceperselectedDates = $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;
        
                        $campaignproduct->new_stripe_percent_amount = $newofferStripepercentamt;
                        $campaignproduct->newprocessingfeeamt = $newprocessingfeeamt;
        
                        $newofferStripepercentamtSum += $newofferStripepercentamt;
                        $newprocessingfeeamtSum += $newprocessingfeeamt;
                        //echo "<pre>";print_r($campaignproduct);exit;
                }else{
                    /*$price = $product->default_price;
                    $priceperday = $price/28;//exit;
                    $priceperselectedDates = $priceperday * $daysCountCPM;
                    $shortlistedsum+= $priceperselectedDates;
                    $campaignproduct->price = $priceperselectedDates;
                    $cpmsum+= $product->cpm;
                    $impressions = $product->secondImpression;
                    $impressionsperday = (int)($impressions/7);
                    $impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
                    
                    if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                        $impressionsperselectedDates = $impressionsperselectedDates;
                    }else{
                        $impressionsperselectedDates = 1;
                    }
                    $impressionSum+= $impressionsperselectedDates;
                    $campaignproduct->secondImpression = round($impressionsperselectedDates, 2);
                    $cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                    $campaignproduct->cpmperselectedDates = $cpmcal;
                    $campaignproduct->cpm = $cpmcal;
                    $campaignproduct->impressionsperselectedDates = $impressionsperselectedDates;
                    $campaignproduct->priceperselectedDates = $priceperselectedDates;*/
                    $offerDetails = MakeOffer::where([
                        ['campaign_id', '=', $campaignproduct->campaign_id],
                        ['status', '=', 20],
                    ])->get();
                    
                    
                    if(isset($offerDetails) && count($offerDetails)==1){
                        //echo 'variable -offer'; exit;;
                            foreach($offerDetails as $offerDetails){
                                    $offerprice = $offerDetails->price;
                                    $stripe_percent=$getproductDetails->stripe_percent;
                                    
                            $price = $getproductDetails->default_price;
                            
							///variable_offer///
                            //$price = $campaign_product->price;
                            $priceperday = $campaignproduct->price;//exit;
                            //echo '---camptot--'.$camptot;
                            $priceperselectedDates = $priceperday;
							
							
							if($daysCountCPM < $getproductDetails->minimumdays){
								$priceperday = $campaignproduct->price;
								$priceperselectedDates = $priceperday;
							}else{
								$priceperday = $price/28;
								$priceperselectedDates = $priceperday * $daysCountCPM;
							}
							
                            $newpricepercentage = ($priceperselectedDates/$camptot) * 100;

                            //$newofferprice = ($offerprice * ($newpricepercentage))/100;//exit;
							if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
								$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
								$newofferprice = $newofferprice_wq*$campaignproduct->quantity;
							}else{
								$newofferprice = ($offerprice * ($newpricepercentage))/100;
							}
                            //$offerpriceperday = $newofferprice/28;//exit;
                            //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                            $offerpriceperselectedDates = $newofferprice;
                            $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
                            $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                            $campaignproduct->stripe_percent = $stripe_percent;
                                }
								$offer_applied = 1;
                            }else{
                               //echo 'variable -no-offer'; exit;;
                                    //$offerprice = $getproductDetails->default_price;
                            $offerprice = $campaignproduct->price;
                            $stripe_percent=$getproductDetails->stripe_percent;
                            $price = $campaignproduct->price;
                            //$price = $campaign_product->price;
                            $priceperday = $price;//exit;
                            $priceperselectedDates = $priceperday;
                            $newpricepercentage = ($priceperselectedDates/$camptot) * 100;

							if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
								$newofferprice = $offerprice*$campaignproduct->quantity;
							}else{
								$newofferprice = $offerprice;
							}
                            //$offerpriceperday = $newofferprice/28;//exit;
                            //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                            $offerpriceperselectedDates = $newofferprice;
                            $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
                            $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                            $campaignproduct->stripe_percent = $stripe_percent;
                            }
                            
                            //if($daysCountCPM <= $product->minimumdays){
                            //    $priceperselectedDates = $priceperday * $product->minimumdays;
                            //}
							if($campaign->total_paid != 0){
								if(isset($campaignproduct->tax_percentage)){
									$tax_percentage_booking = $campaignproduct->tax_percentage;
								}else{
									$tax_percentage_booking = 0;
								}
							}else{
								$tax_percentage_booking = $getproductDetails->tax_percentage;
							}
							if($tax_percentage_booking != 0){
								$campaignproduct->tax_percentage_amount_shortlist = ($priceperselectedDates * ($tax_percentage_booking))/100;
								$tax_percentage_amount = ($offerpriceperselectedDates * ($tax_percentage_booking))/100;
							}
							$campaignproduct->tax_percentage_amount = round($tax_percentage_amount,2);
                                                
                            $shortlistedsum+= ($priceperselectedDates+$campaignproduct->tax_percentage_amount_shortlist)*$campaignproduct->quantity;
							$shortlistedsumcpm+= $priceperselectedDates*$campaignproduct->quantity;
                            $campaignproduct->price = $priceperselectedDates;

							$offershortlistedsum+= $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;
							$campaignproduct->offerprice = $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;

							$offershortlistedsumcpm += $offerpriceperselectedDates;
                            $cpmsum+= $getproductDetails->cpm;
                            $impressions = $getproductDetails->secondImpression;
                            $impressionsperday = (float)($impressions/7);
                            $impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
                            
                            if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                                $impressionsperselectedDates = $impressionsperselectedDates;
                            }else{
                                $impressionsperselectedDates = 1;
                            }
                            //$impressionSum+= $product_details->secondImpression; 
                            $impressionSum+= $impressionsperselectedDates*$campaignproduct->quantity;
                            $campaignproduct->secondImpression = round($impressionsperselectedDates, 2);
                            //$cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                            //$offercpmcal = ($offershortlistedsum/$impressionSum) * 1000;
                            $cpmcal = ($priceperselectedDates/$impressionsperselectedDates) * 1000;
                            $offercpmcal = ($offerpriceperselectedDates/$impressionsperselectedDates) * 1000;
                            $campaignproduct->cpmperselectedDates = $cpmcal;
                            $campaignproduct->offercpmperselectedDates = $offercpmcal;
                            $campaignproduct->cpm = $cpmcal;
                            $campaignproduct->offercpm = $offercpmcal;
                            $campaignproduct->impressionsperselectedDates = $impressionsperselectedDates;
                            $campaignproduct->priceperselectedDates = $priceperselectedDates;
                            $campaignproduct->offerpriceperselectedDates = $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;

                            $campaignproduct->new_stripe_percent_amount = $newofferStripepercentamt;
                            $campaignproduct->newprocessingfeeamt = $newprocessingfeeamt;

                            $newofferStripepercentamtSum += $newofferStripepercentamt;
                            $newprocessingfeeamtSum += $newprocessingfeeamt;
                }
                array_push($products_arr, array_merge(Product::where('id', '=', $campaignproduct->product_id)->first()->toArray(), $campaignproduct->toArray()));
            }
        }
        
        $campaign->products = $products_arr;
        $campaign->actbudg = $products_arr;
        
        
        $act_budget = ProductBooking::raw(function($collection) use ($campaign_id) {
            return $collection->aggregate(
                            [
                                [
                                    '$match' =>
                                    [
                                        "campaign_id" => $campaign_id
                                    ]
                                ],
                                [
                                    '$group' =>
                                    [
                                        '_id' => '$campaign_id',
                                        'total_price' => [
                                            '$sum' => '$admin_price'
                                        ]
                                    ]
                                ]
                            ]
            );
        });


        $res = array_sum(array_map(function($item) {
					if(isset($item['quantity']) && $item['quantity'] != '' && $item['quantity'] != 0){
						return ($item['price']+$item['tax_percentage_amount'])*$item['quantity']; 
					}else{
						return $item['price']+$item['tax_percentage_amount']; 
					}
        }, $campaign->actbudg));
        //echo "<pre>act_budget";print_r($res);exit;
        $campaign->act_budget = $res;

        $campaign->totalamount = $campaign->act_budget;
        $campaign->offer_applied_res = $offer_applied;

        
                 
        $campaign->refunded_amount = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('refunded_amount');
        
        $campaign->bal_amount_available_with_amp = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('bal_amount_available_with_amp');
        
		$campaign->gross_fee_price = 0;
		if(isset($campaign->gross_fee_percentage) && $campaign->gross_fee_percentage > 0){
			$campaign->gross_fee_price = ($offershortlistedsum*$campaign->gross_fee_percentage)/100;
		}else{
			$campaign->gross_fee_price = 0;
		}
        
        $impressionSum4 = round($impressionSum, 2); /* As discussed with Richard on July 14) */
         if($impressionSum4>0){
            $cpmval = (($shortlistedsumcpm+$campaign->gross_fee_price)/$impressionSum4) * 1000;
            $offercpmval = (($offershortlistedsumcpm+$campaign->gross_fee_price)/$impressionSum4) * 1000;
         }else{
             $cpmval = 0;
             $offercpmval = 0;
         }
 
         $campaign_shortlistedsum = $shortlistedsum;
		 if($offershortlistedsum != 0){
			$campaign_cpmval = $offercpmval;
		 }else{
			 $campaign_cpmval = $cpmval;
		 }
         $campaign_impressionSum = $impressionSum4;

             $total_price = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('owner_price');
             
             if($total_price == 0){
             $total_price = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('price');
             }

            $act_budget = ProductBooking::raw(function($collection) use ($campaign_id) {
                return $collection->aggregate(
                    [
                        [
                            '$match' =>
                            [
                                "campaign_id" => $campaign_id
                            ]
                        ],
                        [
                            '$group' =>
                            [
                                '_id' => '$campaign_id',
                                'total_price' => [
                                    '$sum' => '$admin_price'
                                ]
                            ]
                        ]
                    ]
                );
            });
        
            $res = array_sum(array_map(function($item) { 
				if(isset($item['quantity']) && $item['quantity'] != '' && $item['quantity'] != 0){
					return ($item['price']+$item['tax_percentage_amount'])*$item['quantity']; 
				}else{
					return $item['price']+$item['tax_percentage_amount']; 
				}
            }, $campaign->actbudg));
			if($offershortlistedsum != 0){
				$total_price = $offershortlistedsum+$campaign->gross_fee_price;
			}else{
				$total_price = $res+$campaign->gross_fee_price;
			}
			
            $campaign_report = [
                'campaign' => $campaign,
                'areas_covered' => $areas,
                'format_types' => $formats,
                'mediums_covered' => $products_in_campaign->count(),
                'audience_reach' => $audience_reach,
                'repeated_audience' => $repeated_audience,
                'products' => $products_in_campaign,
                'total_price'=>$total_price,
                'products_arr'=>$products_arr,
                'campaign_shortlistedsum'=>$campaign_shortlistedsum,
                'campaign_cpmval'=>$campaign_cpmval,
                'campaign_impressionSum'=>$campaign_impressionSum,
            ];
            //return response()->json($campaign_report);
            // return view('pdf.campaign_details_pdf', $campaign_report); exit;
            $pdf = PDF::loadView('pdf.campaign_details_pdf', $campaign_report);
            // $pdf->save('uploads/campaign' . uniqid() . '.pdf'); die();
            }
            else{
                 $packages_in_campaign = CampaignProduct::where('campaign_id', '=', $campaign_id)->get();
            foreach ($packages_in_campaign as $pkg) {
                $package_tplt = MetroPackage::where('id', '=', $pkg->package_id)->first();
                $pkg->format = $package_tplt->format;
                $pkg->max_trains = $package_tplt->max_trains;
                $pkg->max_slots = $package_tplt->max_slots;
                $pkg->months = $package_tplt->months;
                $pkg->days = $package_tplt->days;
            }
            $campaign_report = [
                'campaign' => $campaign
                // 'format_types' => $formats,
                //'packages' => $packages_in_campaign
            ];
            //$pdf = PDF::loadView('pdf.metro_campaign_details_pdf', $campaign_report);
            $pdf = PDF::loadView('pdf.RFP_campaign_pdf', $campaign_report);
            }
            if (!empty($pdf)) {
                return $pdf->download("campaign_details_pdf.pdf");
            } else {
                return response()->json(['status' => 0, 'message' => "There was an error sending the email. Please try again."]);
            }
        } catch (Exception $ex) {
            return response()->json(['status' => 0, 'message' => "There was an error generating the campaign report."]);
        }
          

      
    }

    public function cancelProductFromCampaign($campaign_id, $product_id) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user_internal = User::where('id', '=', $user_mongo['user_id'])->first();
        if (isset($user_internal->client)) {
            $user_type = $user_internal->client->client_type->type;
        } else {
            $user_type = "basic";
        }
              $campaign_product = ProductBooking::where([
                        ['campaign_id', '=', $campaign_id],
                        ['id', '=', $product_id]
                    ])->first();
                    
                    $booked_from_date = date('d-m-Y',strtotime($campaign_product->booked_from));
                    //dd($booked_from);
                    $current_date = date('d-m-Y');
                    //dd($current_date);
                    $diff=date_diff(date_create($current_date),date_create($booked_from_date));
                    $daysCount = $diff->format("%a");
                    $cancellationfeeArray = array('0-30'=>35,'30-60'=>20,'61-120'=>10,'121-0'=>0);
                    $cancellationfee =0;
                    if($daysCount >0){
                    foreach($cancellationfeeArray as $key=>$val){
                        $daysrange = explode("-",$key);
                         $mindays = $daysrange[0];
                         $maxdays = $daysrange[1];
                        if($mindays <= $daysCount && $daysCount <= $maxdays && $maxdays!=0 ){
                             $cancellationfee = (($campaign_product->admin_price)*($val/100));
                             
                        }else if($mindays <= $daysCount && $maxdays ==0 ){
                             $cancellationfee = (($campaign_product->admin_price)*($val/100));
                        }
                        
                        $cancellationArray = array('cancellation_charge'=>$cancellationfee,'cancel_remaingdays'=>$daysCount);
                       }
                    }
            if (isset($campaign_product) && !empty($campaign_product)) {
                $campaign_product->product_status = ProductBooking::$PRODUCT_STATUS['canceled'];
                $campaign_product->product_status = ProductBooking::$PRODUCT_STATUS['canceled'];
                $campaign_product->user_cancellation_charge = $cancellationfee;
                $campaign_product->cancelled_date = $current_date;
                $campaign_product->save();
                
            } else {
                return response()->json(['status' => 0, 'message' => 'You are not authorized to Cancel this product']);
            }
        return response()->json(["status" => "1", "message" => "Product cancelled from campaign successfully."]);
    }
    
    
      public function payAndLaunchCampaign() {
             $this->validate($this->request, [
                'name' => 'required'
                    ], [
                'name.required' => 'Name is required'
                    ]
            );
            $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo']; 
               $user = User::where('id', '=', $user_mongo['user_id'])->first();
            if ($user_mongo['user_type'] != 'basic' && $user_mongo['user_type'] != 'owner' ) {
                return response()->json(['status' => 0, 'message' => "You can not create a campaign from here. Please switch to your dashboard."]);
            }
            
            $campaign_obj = new Campaign;
            $campaign_obj->id = uniqid();
            $campaign_obj->cid = $this->generatecampaignID();
            $campaign_obj->name = isset($this->input['name']) ? $this->input['name'] : "";
            $campaign_obj->slug = str_replace(" ", "-", strtolower($this->input['name']));
            $campaign_obj->created_by = $user_mongo['id'];
            $campaign_obj->status = Campaign::$CAMPAIGN_STATUS['booking-requested'];
            if($user_mongo['user_type'] == 'basic'){
            $campaign_obj->type = Campaign::$CAMPAIGN_USER_TYPE['user'];
            }
            else if($user_mongo['user_type'] == 'owner'){
            $campaign_obj->type = Campaign::$CAMPAIGN_USER_TYPE['owner'];
            $client_mongo = ClientMongo::where('client_id', '=', $user->client->id)->first();
            $campaign_obj->client_mongo_id = $client_mongo->id;
            $campaign_obj->client_name = $client_mongo->name;
            }
            
            if ($campaign_obj->save()) {
                $success = true;
               // Log::info($campaign_obj->id);
                if (isset($this->input['shortlisted_products']) && !empty($this->input['shortlisted_products'])) {
                    // move products from shortlisted_products collection to product_bookings collection
                    foreach ($this->input['shortlisted_products'] as $shortlisted_id) {
                        $shortlisted = ShortListedProduct::where('id', '=', $shortlisted_id)->first();
                        $product = Product::where('id', '=', $shortlisted->product_id)->first();
                        $new_booking = new ProductBooking;
                        $new_booking->id = uniqid();
                        $new_booking->campaign_id = $campaign_obj->id;
                        $new_booking->product_id = $product->id;
                        $new_booking->booked_from = iso_to_mongo_date($shortlisted->from_date);
                        $new_booking->booked_to = iso_to_mongo_date($shortlisted->to_date);
                        $diff=date_diff(date_create($shortlisted->to_date),date_create($shortlisted->from_date));
                        $daysCount = $diff->format("%a");
                        //$price = round(($product->default_price*($daysCount+1))/28);
                        if($product->type=='Bulletin'){
                        //$price = round(($product->default_price*($daysCount+1))/28);
                        //$price = round(($product->rateCard*($daysCount+1))/28);
                        $price = round($product->rateCard);
                        }else{
                            //$price = round(($product->default_price*($daysCount+1))/7);
                            //$price = round(($product->rateCard*($daysCount+1))/7);
                            //$price = round(($product->rateCard*($daysCount+1))/28);
                            $price = round($product->rateCard);
                        }
                        $new_booking->price = $price;
                        //$new_booking->price = $product->default_price;
                        $new_booking->product_owner = $product->client_mongo_id;
                        $new_booking->product_status = ProductBooking::$PRODUCT_STATUS['scheduled'];
                        if (!$new_booking->save()) {
                            $success = false;
                            break;
                        } else {
                            $shortlisted->delete();
                        }
                    }
                } else  {
                    
                    $product_id = $this->input['productId'];
                  //  $product_id = $products[0]['product_id'];
                    if(isset($this->input['booking_slots'])){
                        foreach ($this->input['dates'] as $dr) {
                        $product = Product::where('id', '=', $product_id)->first();
                        $new_booking = new ProductBooking;
                        $new_booking->id = uniqid();
                        $new_booking->campaign_id = $campaign_obj->id;
                        $new_booking->product_id = $product_id;
                        $new_booking->booked_from = iso_to_mongo_date($dr['startDate'].'05:30:00');
                        $new_booking->booked_to = iso_to_mongo_date($dr['endDate'].'05:30:00');
                        $new_booking->booked_slots = isset($this->input['booking_slots'])?$this->input['booking_slots']:1;
                        $diff=date_diff(date_create($dr['endDate']),date_create($dr['startDate']));
                        $daysCount = $diff->format("%a");
                        //$price = round(($product->default_price*($daysCount+1))/7);
                        if($product->type=='Bulletin'){
                        //$price = round(($product->default_price*($daysCount+1))/28);
                        //$price = round(($product->rateCard*($daysCount+1))/28);
                        $price = round($product->rateCard);
                        }else{
                            //$price = round(($product->default_price*($daysCount+1))/7);
                            //$price = round(($product->rateCard*($daysCount+1))/7);
                            //$price = round(($product->rateCard*($daysCount+1))/28);
                            $price = round($product->rateCard);
                        }
                        $new_booking->price = $price;
                        $new_booking->product_owner = $product->client_mongo_id;
                        $new_booking->product_status = ProductBooking::$PRODUCT_STATUS['scheduled'];
                        
                        $new_booking->save();
                    }
                        
                        }else{
                    foreach ($this->input['dates'] as $dr) {
                        $product = Product::where('id', '=', $product_id)->first();
                        $new_booking = new ProductBooking;
                        $new_booking->id = uniqid();
                        $new_booking->campaign_id = $campaign_obj->id;
                        $new_booking->product_id = $product_id;
                        $new_booking->booked_from = iso_to_mongo_date($dr['startDate'].'05:30:00');
                        $new_booking->booked_to = iso_to_mongo_date($dr['endDate'].'05:30:00');
                        $diff=date_diff(date_create($dr['endDate']),date_create($dr['startDate']));
                        $daysCount = $diff->format("%a");
                        //$price = round(($product->default_price*($daysCount+1))/28);
                        if($product->type=='Bulletin'){
                        //$price = round(($product->default_price*($daysCount+1))/28);
                        //$price = round(($product->rateCard*($daysCount+1))/28);
                        $price = round($product->rateCard);
                        }else{
                            //$price = round(($product->default_price*($daysCount+1))/7);
                            //$price = round(($product->rateCard*($daysCount+1))/7);
                            //$price = round(($product->rateCard*($daysCount+1))/28);
                            $price = round($product->rateCard);
                        }
                        $new_booking->price = $price;
                        $new_booking->product_owner = $product->client_mongo_id;
                        $new_booking->product_status = ProductBooking::$PRODUCT_STATUS['scheduled'];
                        //dd($new_booking);
                        $new_booking->save();
                    }
                    
                    }
                }
                if ($success) {
                $bbi_sa_id = Client::where('company_slug', '=', 'bbi')->first()->super_admin;
                $bbi_sa = UserMongo::where('user_id', '=', $bbi_sa_id)->first();
                $noti_array = [
                'type' => Notification::$NOTIFICATION_TYPE['campaign-launch-requested'],
                'from_id' => $user_mongo['id'],
                'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
                'to_id' => null,
                'to_client' => null,
                'desc' => "Campaign launch request",
                'message' => $user_mongo['first_name'] . ' ' . $user_mongo['last_name'] . " has requested campaign launch.",
                'data' => ["campaign_id" => $campaign_obj->id]
            ];
            //dd($noti_array);
            $mail_array = [
                'mail_tmpl_params' => [
                    'sender_email' => $user_mongo['email'],
                    'receiver_name' => $bbi_sa->first_name . ' ' . $bbi_sa->last_name,
                    'mail_message' => $user_mongo['first_name'] . ' ' . $user_mongo['last_name'] . 'has requested the launch of their campaign.'
                ],
                'email_to' => $bbi_sa->email,
                'recipient_name' => $bbi_sa->first_name,
                'subject' => 'New campaign launch request'
            ];
            event(new CampaignLaunchRequestedEvent($noti_array, $mail_array));
            
                 $notification_obj = new Notification;
                 $notification_obj->id = uniqid();
                $notification_obj->type = "campaign";
                $notification_obj->from_id = $user_mongo['id'];
                $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['bbi'];
                $notification_obj->to_id = null;
                $notification_obj->to_client = null;
                $notification_obj->desc = "Campaign launch request";
                $notification_obj->message = $user_mongo['first_name'] . ' ' . $user_mongo['last_name'] . " has requested the launch of their campaign";
                $notification_obj->campaign_id = $campaign_obj->id;
                $notification_obj->status = 0;
                $notification_obj->save();
                
                $campaign_id = $campaign_obj->id;
                $campaign_product_owner_ids = ProductBooking::raw(function($collection) use ($campaign_id) {
                                return $collection->distinct('product_owner', ['campaign_id' => $campaign_id]);
                            });
                    $owner_notif_recipients = ClientMongo::whereIn('id', $campaign_product_owner_ids)->get();
                    $owner_sa_ids = [];
                    foreach ($owner_notif_recipients as $owner_notif_recipient) {
                        if (isset($owner_notif_recipient->super_admin_m_id) && !empty($owner_notif_recipient->super_admin_m_id)) {
                            array_push($owner_sa_ids, $owner_notif_recipient->super_admin_m_id);
                        }
                    }
                
                $owner_sa_emails = UserMongo::whereIn('id', $owner_sa_ids)->pluck('email');
                
                $noti_array = [
                'type' => Notification::$NOTIFICATION_TYPE['campaign-launch-requested'],
               'from_id' => null,
                'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['owner'],
                'to_id' => null,
                'to_client' => $campaign_product_owner_ids,
                'desc' => "Campaign launch request",
                'message' => $user_mongo['first_name'] . ' ' . $user_mongo['last_name'] . " has requested campaign launch.",
                'data' => ["campaign_id" => $campaign_obj->id]
            ];
                
                $mail_array = [
                        'mail_tmpl_params' => [
                            'sender_email' => config('app.bbi_email'),
                            'receiver_name' => "",
                            'mail_message' => $user_mongo['first_name'] . ' ' . $user_mongo['last_name'] . 'has requested the launch of their campaign.'
                        ],
                        'recipient_name' => '',
                        'bcc' => $owner_sa_emails->toArray(),
                        'subject' => 'New campaign launch request'
                    ];
                    event(new CampaignLaunchRequestedEvent($noti_array, $mail_array));
                    foreach ($campaign_product_owner_ids as $key => $val) {
                        $notification_obj = new Notification;
                        $notification_obj->id = uniqid();
                        $notification_obj->type = "campaign";
                        $notification_obj->from_id = null;
                        $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['owner'];
                        $notification_obj->to_id = $val;
                        $notification_obj->to_client = $val;
                        $notification_obj->desc = "Campaign launch request";
                        $notification_obj->message =  $user_mongo['first_name'] . ' ' . $user_mongo['last_name'] . " has requested the launch of their campaign";
                        $notification_obj->campaign_id = $campaign_obj->id;
                        $notification_obj->status = 0;
                        $notification_obj->save();
                    }               
                
                    return response()->json(["status" => "1", "message" => "campaign Booking Requested.",'campaign_id' => $campaign_obj->id]);
                } else {
                    return response()->json(["status" => "0", "message" => "campaign saved successfully but product addition failed."]);
                }
            } else {
                return response()->json(["status" => "0", "message" => "Failed to save campaign."]);
            }
    }

    
       public function generatePop($campaign_id){
         
             
             $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
           $user_internal = User::where('id', '=', $user_mongo['user_id'])->first();
          $productBooked = ProductBooking::where([['campaign_id', '=',  $campaign_id]])->get();
          $campaign = Campaign::where('id', '=', $campaign_id)->first();
          
         // $final_array[]='';
            foreach($productBooked as $product){
                $productData = Product::where('id', '=', $product['product_id'])->first();
                
                $booked_from[] = strtotime($product['booked_from']);
                $booked_to[] = strtotime($product['booked_to']);
                
                if($productData->type!='Bulletin'){
                 $slots = $productData->slots;
                 $hours = $productData->hour;
                $bonus =1;
                $startDate = date('d-m-Y',strtotime($product['booked_from']));
                $endDate =  date('d-m-Y',strtotime($product['booked_to']));
                $current_date = date('d-m-Y',strtotime("-1 day"));
                 $booked_slots = $product['booked_slots'];
                 if($booked_slots==''){
                     $booked_slots =1;
                 }
                if($endDate >= $current_date){
                    $calculationDate = $current_date;
                }
                else
                {
                    $calculationDate = $endDate;
                }
                $diff=date_diff(date_create($calculationDate),date_create($startDate));
                $daysCount = $diff->format("%a");
                $productBookedArray = ProductBooking::where('product_id', '=',  $product['product_id'])->get();
    foreach($productBookedArray as $key=>$value){
        if($value['booked_from'] >= $product['booked_from'] && $value['booked_to'] <= $product['booked_to'])    {
    $output_element = &$output[$value['booked_from'] . "_" . $value['booked_to']];
    !isset($output_element['booked_slots']) && $output_element['booked_slots'] = 0;
    $output_element['booked_slots'] += $value['booked_slots'];
        }
  }
        $arrayOutput = array_values($output);
        $productBooked = $arrayOutput[0]['booked_slots'] ;
        if($productBooked == 0 || $productBooked == ''){
            $productBooked = 1;
        }

                if($productBooked <= $slots && $bonus ==1){
                    $array = $productData->toArray();
                    $array['acctual_spots'] =$acctual_spots =$hours * $booked_slots * $daysCount ;
                    $array['deliverdSpots']= $deliverdSpots = round(($hours * $slots)/$productBooked) * $daysCount;
                    $array['varience']=$varience = abs($acctual_spots-$deliverdSpots);
                    $array['varience_percentage'] = ($varience/$acctual_spots)*100;
                    $final_array[]=$array;
                }                  
                               
                else{
                    $array = $productData->toArray();
                    $array['acctual_spots']  = $acctual_spots = $hours * $booked_slots * $daysCount ;
                    $array['deliverdSpots'] = $deliverdSpots =  $hours * $booked_slots * $daysCount;
                    $array['varience'] =$varience =abs($acctual_spots-$deliverdSpots);
                    $array['varience_percentage'] = ($varience/$acctual_spots)*100;
                    $final_array[]=$array;
                }
    }               
            }
             
            if(isset($booked_from) && !empty($booked_from)) {$campaign->startDate =  date('d-m-Y',min($booked_from));}
            if(isset($booked_from) && !empty($booked_from)) {$campaign->startDate =  date('d-m-Y',min($booked_from));}
        if(isset($booked_to) && !empty($booked_to)) {$campaign->endDate = date('d-m-Y',max($booked_to));}
        $daysdiff=date_diff(date_create($campaign->startDate),date_create($campaign->endDate));
                    $daysDiff = $daysdiff->format("%a");
                    $campaign->weeks = (($daysDiff+1)/7);
                      $user_mongo = UserMongo::where('id', '=', $campaign->created_by)->first();
        $campaign->first_name = $user_mongo->first_name;
        $campaign->last_name = $user_mongo->last_name;
        $campaign->email = $user_mongo->email;
        $campaign->phone = $user_mongo->phone;
             $pop_report = [
                'campaign' => $campaign,
                'poparray' => $final_array,
                'current_date'=>$current_date
            ];
            $pdf = PDF::loadView('pdf.campaign_pop_pdf', $pop_report);
              
            
            if (!empty($pdf)) {
                return $pdf->download("campaign_pop_pdf.pdf");
            } else {
                return response()->json(['status' => 0, 'message' => "There was an error sending the email. Please try again."]);
            }

      }
      
      
      
    public function paymentsinfoDownload($campaign_id) {
        $campaign = Campaign::where('id', '=', $campaign_id)->first();
        $campaign_payments = StripePayments::where('campaign_id', '=', $campaign_id)->get();
        //$campaign_payments = CampaignPayment::where('campaign_id', '=', $campaign_id)->get();
		$payments_report['user_mongo'] = JWTAuth::parseToken()->getPayload()['userMongo'];
       
		$price = 0;
		$camptot = 0;
		$offershortlistedsum = 0;
		$tax_percentage_booking = 0;
		$tax_percentage_amount = 0;
		$tax_percentage_amount_total = 0;
		$newprocessingfeeamtSum = 0;
		$tax_percentage_amount_tot = 0;
		$tax_percentage_booking_tot = 0;
		$payments_report['new_stripe_percent_amount'] = 0;
            $total_amount = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('admin_price');
            if($total_amount==0){
                 //$total_amount = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('price');
				 
				$campaign_products = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
				 $total_paid = $campaign_payments->sum('amount');
					
				if (isset($campaign_products) && count($campaign_products) > 0) {
					foreach ($campaign_products as $campaign_product_camp) {
						$tax_percentage_booking_tot = 0;
						$tax_percentage_amount_tot = 0;
						$diff=date_diff(date_create($campaign_product_camp->booked_from),date_create($campaign_product_camp->booked_to));
						$daysCount = $diff->format("%a");
						$daysCountCPM = $daysCount + 1;
						$getproductDetails =Product::where('id', '=', $campaign_product_camp->product_id)->first();
						$perdayprice = $getproductDetails->default_price/28;
						
						if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
							$price_cp1 = $campaign_product_camp->price*$campaign_product_camp->quantity;
							if($total_paid != 0){
								if(isset($campaign_product_camp->tax_percentage)){
									$tax_percentage_booking_tot = $campaign_product_camp->tax_percentage;
								}else{
									$tax_percentage_booking_tot = 0;
								}
							}else{
								$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
							}
							if($tax_percentage_booking_tot != 0){
								$tax_percentage_amount_tot = ($price_cp1 * ($tax_percentage_booking_tot))/100;
							}
							$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
							$camptot += $price_cp1+$tax_percentage_amount_tot;
						}else{
							$priceperselectedDates = $campaign_product_camp->price;
							if($total_paid != 0){
								if(isset($campaign_product_camp->tax_percentage)){
									$tax_percentage_booking_tot = $campaign_product_camp->tax_percentage;
								}else{
									$tax_percentage_booking_tot = 0;
								}
							}else{
								$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
							}
							if($tax_percentage_booking_tot != 0){
								$tax_percentage_amount_tot = ($priceperselectedDates * ($tax_percentage_booking_tot))/100;
							}
							$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
							$camptot += ($priceperselectedDates*$campaign_product_camp->quantity)+($tax_percentage_amount_tot*$campaign_product_camp->quantity);
						}
					}
				}
				
				if($payments_report['user_mongo']['user_type'] == 'owner'){
					$campaignproducts = ProductBooking::where([
							['campaign_id', '=', $campaign_id],
							['product_owner', '=', $payments_report['user_mongo']['client_mongo_id']]
						])->get();
				}else{
					$campaignproducts = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
				}
				
				if (isset($campaignproducts) && count($campaignproducts) > 0) {
					
					foreach ($campaignproducts as $campaign_product) {
						$campaign->tax_percentage_amount = 0;
						$tax_percentage_booking = 0;
						$tax_percentage_amount = 0;
						$diff=date_diff(date_create($campaign_product->booked_from),date_create($campaign_product->booked_to));
						$daysCount = $diff->format("%a");
						$daysCountCPM = $daysCount + 1;
						$getproductDetails =Product::where('id', '=', $campaign_product->product_id)->first();
						$perdayprice = $getproductDetails->default_price/28;
						
						$offerDetails = MakeOffer::where([
							['campaign_id', '=', $campaign_product->campaign_id],
							['status', '=', 20],
						])->get();
						if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
							if(isset($offerDetails) && count($offerDetails)==1){
								foreach($offerDetails as $offerDetails){
									$stripe_percent=$getproductDetails->stripe_percent;
									$price_cp = $campaign_product->price;
									$offerprice = $offerDetails->price;
									$priceperday = $price_cp;
									$priceperselectedDates = $priceperday;
									$newpricepercentage = ($priceperselectedDates/$camptot) * 100;
									
									//$newofferprice = ($offerprice * ($newpricepercentage))/100;
									if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
										$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
										$newofferprice = $newofferprice_wq*$campaign_product->quantity;
									}else{
										$newofferprice = ($offerprice * ($newpricepercentage))/100;
									}
									$price = $newofferprice;
									$newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
									$stripe_percent=$getproductDetails->stripe_percent;
								}
							}else{
								$priceperselectedDates = $campaign_product->price;
								$stripe_percent=$getproductDetails->stripe_percent;
								if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
									$price = $campaign_product->price*$campaign_product->quantity;
								}else{
									$price = $campaign_product->price;
								}
								$newofferStripepercentamt = ($price * ($stripe_percent))/100;
								$stripe_percent=$getproductDetails->stripe_percent;
							}
							if($total_paid != 0){
								if(isset($campaign_product->tax_percentage)){
									$tax_percentage_booking = $campaign_product->tax_percentage;
								}else{
									$tax_percentage_booking = 0;
								}
							}else{
								$tax_percentage_booking = $getproductDetails->tax_percentage;
							}
							if($tax_percentage_booking != 0){
								$tax_percentage_amount = ($price * ($tax_percentage_booking))/100;
							}
                            $campaign->tax_percentage_amount = round($tax_percentage_amount,2);
							$tax_percentage_amount_total += round($tax_percentage_amount,2);
							$newofferStripepercentamt = (($price+$tax_percentage_amount) * ($stripe_percent))/100;
							$newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
							$offershortlistedsum+= $price+$campaign->tax_percentage_amount;
							$newprocessingfeeamtSum += $newprocessingfeeamt;
							$payments_report['new_stripe_percent_amount'] += $newofferStripepercentamt;
						}else{
							if(isset($offerDetails) && count($offerDetails)==1){
								foreach($offerDetails as $offerDetails){
									$price_cp = $getproductDetails->default_price;
									$stripe_percent=$getproductDetails->stripe_percent;
									$offerprice = $offerDetails->price;
									//$priceperday = $price_cp/28;
									//$priceperselectedDates = $priceperday * $daysCountCPM;
											
									///variable_offer///
									$priceperday = $campaign_product->price;
									$priceperselectedDates = $priceperday;
									
									
									$newpricepercentage = ($priceperselectedDates/$camptot) * 100;
									
									//$newofferprice = ($offerprice * ($newpricepercentage))/100;
									if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
										$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
										$newofferprice = $newofferprice_wq*$campaign_product->quantity;
									}else{
										$newofferprice = ($offerprice * ($newpricepercentage))/100;
									}
									$price = $newofferprice;
									$newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
									$stripe_percent=$getproductDetails->stripe_percent;
								}
							}else{
								$priceperselectedDates = $campaign_product->price;
								$stripe_percent=$getproductDetails->stripe_percent;
								if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
									$price = $campaign_product->price*$campaign_product->quantity;
								}else{
									$price = $campaign_product->price;
								}
								$newofferStripepercentamt = ($price * ($stripe_percent))/100;
								$stripe_percent=$getproductDetails->stripe_percent;
							}
							if($total_paid != 0){
								if(isset($campaign_product->tax_percentage)){
									$tax_percentage_booking = $campaign_product->tax_percentage;
								}else{
									$tax_percentage_booking = 0;
								}
							}else{
								$tax_percentage_booking = $getproductDetails->tax_percentage;
							}
							if($tax_percentage_booking != 0){
								$tax_percentage_amount = ($price * ($tax_percentage_booking))/100;
							}
                            $campaign->tax_percentage_amount = round($tax_percentage_amount,2);
							$tax_percentage_amount_total += round($tax_percentage_amount,2);

							$newofferStripepercentamt = (($price+$tax_percentage_amount) * ($stripe_percent))/100;
							$newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
							$offershortlistedsum+= $price+$campaign->tax_percentage_amount;
							$payments_report['new_stripe_percent_amount'] += $newofferStripepercentamt;
							$newprocessingfeeamtSum += $newprocessingfeeamt;
						}
						$total_amount = $offershortlistedsum;
					}
				}
            }
            $campaign->tax_percentage_amount_total = $tax_percentage_amount_total;
			$campaign->gross_fee_price = 0;
			if(isset($campaign->gross_fee_percentage) && $campaign->gross_fee_percentage > 0){
				$campaign->gross_fee_price = ($offershortlistedsum*$campaign->gross_fee_percentage)/100;
				$newprocessingfeeamtGross = ($newprocessingfeeamtSum*$campaign->gross_fee_percentage)/100;
				$newprocessingfeeamtSum = $newprocessingfeeamtSum+$newprocessingfeeamtGross;
			}else{
				$campaign->gross_fee_price = 0;
			}
            $campaign->total_amount = $total_amount;
            $campaign->newprocessingfeeamtSum = $newprocessingfeeamtSum;
            $campaign->totalamount = $campaign->total_amount;
            $campaign->no_of_products = ProductBooking::where('campaign_id', '=', $campaign_id)->distinct('product_id')->get()->count();
            
        if (isset($campaign_payments) && count($campaign_payments) > 0) {
            $total_paid = $campaign_payments->sum('amount');
            $campaign_payments->total_paid = $total_paid;
             $campaign->total_paid= $total_paid;
			if($payments_report['user_mongo']['user_type'] == 'owner'){
				$payments_report['total_paid'] = $payments_report['new_stripe_percent_amount'];
			}else{
				$payments_report['total_paid'] = $total_paid;
			}
			
			if($payments_report['total_paid'] < 1){
				$payments_report['total_paid'] = $campaign_payments->sum('amount');
			}else{
				$payments_report['total_paid'] = $payments_report['total_paid'];
			}
			
            //$payments_report['total_pending'] = ($total_paid-$campaign->totalamount); 
            $payments_report['total_pending'] = (($campaign->totalamount+$campaign->gross_fee_price)-$payments_report['total_paid']);
            $payments_report['campaign_payments'] = $campaign_payments;
            $payments_report['campaign_details'] = $campaign;
              $pdf = PDF::loadView('pdf.campaign_paymnets_pdf', $payments_report);
              
            
            if (!empty($pdf)) {
                return $pdf->download("campaign_paymnets_pdf.pdf");
            } else {
                return response()->json(['status' => 0, 'message' => "There was an error sending the email. Please try again."]);
            }
        } else {
            return response()->json(["status" => "0", "message" => "No payments found for the campaign.", 'campaign_details' => $campaign]);
        }
    }
  
   
    public function offerForPrice() {
        //echo 'dssd';exit; 

        $this->validate($this->request, [
            'campaign_id' => 'required',
            'price' => 'required',
            'comments' => 'required',
                //'campaign_type' => 'required'
                ], [
            'campaign_id.required' => 'Campaign id is required',
            'price.required' => 'Price is required',
            'comments.required' => 'Comments are required',
                //'campaign_type.required' => 'Campaign type is required'
                ]
        );
        
            $shortlistedsum = 0;
			$shortlistedsumcpm = 0;
            $cpmsum = 0;
            $impressionSum = 0;
            $impressionSum4 = 0;
			$tax_percentage_booking = 0;
			$tax_percentage_amount = 0;
			
            
         
            $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo']; 
               $user = User::where('id', '=', $user_mongo['user_id'])->first();
            if ($user_mongo['user_type'] != 'basic' && $user_mongo['user_type'] != 'owner' ) {
                return response()->json(['status' => 0, 'message' => "You can not create a campaign from here. Please switch to your dashboard."]);
            }
         
            $repeated_offer = MakeOffer::where('campaign_id', '=', $this->input['campaign_id'])->get();
            if(!empty($repeated_offer) && count($repeated_offer) > 1){
              return response()->json(['status' => 0, 'message' => 'You have exhausted your offer limit...']);
            }
            
            $make_offer = new MakeOffer;
            $make_offer->price = isset($this->input['price']) ? $this->input['price'] : "";
            $make_offer->comments = isset($this->input['comments']) ? $this->input['comments'] : "";
            $make_offer->pro_percent = isset($this->input['pro_percent']) ? $this->input['pro_percent'] : "";
            $make_offer->loggedinUser = isset($this->input['loggedinUser']) ? $this->input['loggedinUser'] : "";
			$make_offer->AdminOfferAcceptReject = isset($this->input['AdminOfferAcceptReject']) ? $this->input['AdminOfferAcceptReject'] : "";
            $make_offer->campaign_id = isset($this->input['campaign_id']) ? $this->input['campaign_id'] : "";
            //$make_offer->status = isset($this->input['status']) ? $this->input['status'] : "";
            $make_offer->id = uniqid();
            $make_offer->created_by = $user_mongo['id'];
            $make_offer->status = MakeOffer::$OFFER_STATUS['offer-requested'];
            if($user_mongo['user_type'] == 'basic'){
            $make_offer->user_type = MakeOffer::$CAMPAIGN_USER_TYPE['user'];
            }
            else if($user_mongo['user_type'] == 'owner'){
            $make_offer->user_type = MakeOffer::$CAMPAIGN_USER_TYPE['owner'];
            $client_mongo = ClientMongo::where('client_id', '=', $user->client->id)->first();
            $make_offer->client_mongo_id = $client_mongo->id;
            $make_offer->client_name = $client_mongo->name;
            }
            $make_offer->save();
            try 
            {
            $user = JWTAuth::parseToken()->getPayload()['user'];
            //echo '<pre>user'; print_r($this->input);exit;
            $campaign = Campaign::where('id', '=', $this->input['campaign_id'])->first();
            $campaign_id=$this->input['campaign_id'];
            if($campaign->status < 1000){
            $product_ids_in_campaign = ProductBooking::where('campaign_id', '=', $campaign_id)->pluck('product_id');
            $products_in_campaign = Product::whereIn('id', $product_ids_in_campaign)->get();
            $formats = $products_in_campaign->unique('type')->count();
            $areas = $products_in_campaign->unique('area')->count();
            $audience_reach = $products_in_campaign->each(function($v, $k) {
                        $v->impressions = intval(str_replace(",", "", $v->impressions));
                    })->sum('impressions');
            $repeated_audience = $audience_reach * 30 / 100;
                 $campaign_products = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
                 
        $products_arr = [];
        if (isset($campaign_products) && count($campaign_products) > 0) {
			$price_pdf = 0;
            foreach ($campaign_products as $campaign_product) {
				$campaign_product->tax_percentage_amount_shortlist = 0;
				$tax_percentage_booking = 0;
				$tax_percentage_amount = 0;
                $product =Product::where('id', '=', $campaign_product->product_id)->first();
				if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
					$price_pdf = $campaign_product->price*$campaign_product->quantity;
				}else{
					$price_pdf = $campaign_product->price;
				}
				$tax_percentage_booking = $product->tax_percentage;
				if($tax_percentage_booking != 0){
					$campaign_product->tax_percentage_amount_shortlist = ($price_pdf * ($tax_percentage_booking))/100;
					$tax_percentage_amount = ($price_pdf * ($tax_percentage_booking))/100;
				}
				$campaign_product->tax_percentage_amount = round($tax_percentage_amount,2); 
				$area_time_zone_value = Area::select("area_time_zone","area_time_zone_type")->where('id', '=', $product->area)->first();
				$campaign_product->area_time_zone_type = $area_time_zone_value['area_time_zone_type'];
                array_push($products_arr, array_merge(Product::where('id', '=', $campaign_product->product_id)->first()->toArray(), $campaign_product->toArray()));
            } 
          
        }
        
        $campaignproducts = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
        
        if (isset($campaignproducts) && count($campaignproducts) > 0) {
            
            foreach ($campaignproducts as $campaignproduct) {
                $campaignproduct->tax_percentage_amount_shortlist = 0;
				$tax_percentage_booking = 0;
				$tax_percentage_amount = 0;
                //echo "<pre>campaignproduct"; print_r($campaignproducts);exit;
                $product = Product::where('id', '=', $campaignproduct->product_id)->first();
                
                /*CPM Calculation*/
                $diff=date_diff(date_create($campaignproduct->booked_from),date_create($campaignproduct->booked_to));
                //$diff=date_diff(($booked_from_date),($booked_to_date));
                $daysCount = $diff->format("%a");//exit;
                $daysCountCPM = $daysCount + 1;
                //$daysCoun1tCPM = $daysCount;
                //echo "<pre>diff";print_r($diff);exit;
                //echo "<pre>product";print_r($product);exit;
                //echo $daysCoun1tCPM;exit;
                
                //$price = $campaignproduct->price;
                if(isset($product->fix) && $product->fix=="Fixed"){
                    $price = $product->default_price;
                    $priceperday = $price;//exit;
                    $priceperselectedDates = $priceperday;
					if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
						$priceperselectedDates = $priceperselectedDates*$campaignproduct->quantity;
					}else{
						$priceperselectedDates = $priceperselectedDates ;
					}
					if(isset($product->tax_percentage)){
						$tax_percentage_booking = $product->tax_percentage;
					}else{
						$tax_percentage_booking = 0;
					}
					if($tax_percentage_booking != 0){
						$tax_percentage_amount = ($priceperselectedDates * ($tax_percentage_booking))/100;
					}
					$campaignproduct->tax_percentage_amount = round($tax_percentage_amount,2);
                    $shortlistedsum+= $priceperselectedDates+$campaignproduct->tax_percentage_amount;
                    $campaignproduct->price = $priceperselectedDates;
                    $cpmsum+= $product->cpm;
                    $impressions = $product->secondImpression;
                    $impressionsperday = (int)($impressions);
                    $impressionsperselectedDates = $impressionsperday;//exit;
                    
                    if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                        $impressionsperselectedDates = $impressionsperselectedDates;
                    }else{
                        $impressionsperselectedDates = 1;
                    }
                    $impressionSum+= $impressionsperselectedDates;
                    $campaignproduct->secondImpression = round($impressionsperselectedDates, 2);
                    $cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                    $campaignproduct->cpmperselectedDates = $cpmcal;
                    $campaignproduct->cpm = $cpmcal;
                    $campaignproduct->impressionsperselectedDates = $impressionsperselectedDates;
                    $campaignproduct->priceperselectedDates = $priceperselectedDates;
                }else{
                    $price = $product->default_price;
                    $priceperday = $price/28;//exit;
                    $priceperselectedDates = $priceperday * $daysCountCPM;
					if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
						$priceperselectedDates = $priceperselectedDates*$campaignproduct->quantity;
					}else{
						$priceperselectedDates = $priceperselectedDates ;
					}
					if(isset($product->tax_percentage)){
						$tax_percentage_booking = $product->tax_percentage;
					}else{
						$tax_percentage_booking = 0;
					}
					if($tax_percentage_booking != 0){
						$tax_percentage_amount = ($priceperselectedDates * ($tax_percentage_booking))/100;
					}
					$campaignproduct->tax_percentage_amount = round($tax_percentage_amount,2);
                    $shortlistedsum+= $priceperselectedDates+$campaignproduct->tax_percentage_amount;
                    $campaignproduct->price = $priceperselectedDates;
                    $cpmsum+= $product->cpm;
                    $impressions = $product->secondImpression;
                    $impressionsperday = (int)($impressions/7);
                    $impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
                    
                    if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                        $impressionsperselectedDates = $impressionsperselectedDates;
                    }else{
                        $impressionsperselectedDates = 1;
                    }
                    $impressionSum+= $impressionsperselectedDates;
                    $campaignproduct->secondImpression = round($impressionsperselectedDates, 2);
                    $cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                    $campaignproduct->cpmperselectedDates = $cpmcal;
                    $campaignproduct->cpm = $cpmcal;
                    $campaignproduct->impressionsperselectedDates = $impressionsperselectedDates;
                    $campaignproduct->priceperselectedDates = $priceperselectedDates;

                }
            }
        }
		
		$campaign->gross_fee_price = 0;
		if(isset($campaign->gross_fee_percentage) && $campaign->gross_fee_percentage > 0){
			$campaign->gross_fee_price = ($shortlistedsum*$campaign->gross_fee_percentage)/100;
		}else{
			$campaign->gross_fee_price = 0;
		}
        
        $impressionSum4 = round($impressionSum, 2); /* As discussed with Richard on July 14) */
         if($impressionSum4>0){
            $cpmval = (($shortlistedsum+$campaign->gross_fee_price)/$impressionSum4) * 1000;
         }else{
             $cpmval = 0;
         }
 
         $campaign_shortlistedsum = $shortlistedsum;
         $campaign_cpmval = $cpmval;
         $campaign_impressionSum = $impressionSum4;

            $total_price = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('owner_price');
             if($total_price == 0){
             $total_price = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('price');
             }
            $campaign_report = [
                'campaign' => $campaign,
                'areas_covered' => $areas,
                'format_types' => $formats,
                'mediums_covered' => $products_in_campaign->count(),
                'audience_reach' => $audience_reach,
                'repeated_audience' => $repeated_audience,
                'products' => $products_in_campaign,
                'total_price'=>$total_price+$campaign->gross_fee_price,
                'products_arr'=>$products_arr,
                'campaign_shortlistedsum'=>$campaign_shortlistedsum,
                'campaign_cpmval'=>$campaign_cpmval,
                'campaign_impressionSum'=>$campaign_impressionSum
            ];
            //echo '<pre>total_price'; print_r($products_arr);exit;
            //echo '<pre>campaign'; print_r($campaign);//exit;
            //echo '<pre>campaign_report'; print_r($campaign_report);exit;
            // return view('pdf.campaign_details_pdf', $campaign_report); exit;
            $pdf = PDF::loadView('pdf.campaign_details_pdf', $campaign_report);
            //echo '<pre>pdf'; print_r($pdf);exit;
            // $pdf->save('uploads/campaign' . uniqid() . '.pdf'); die(); 
            }
            else{
                 $packages_in_campaign = CampaignProduct::where('campaign_id', '=', $campaign_id)->get();
            foreach ($packages_in_campaign as $pkg) {
                $package_tplt = MetroPackage::where('id', '=', $pkg->package_id)->first();
                $pkg->format = $package_tplt->format;
                $pkg->max_trains = $package_tplt->max_trains;
                $pkg->max_slots = $package_tplt->max_slots;
                $pkg->months = $package_tplt->months;
                $pkg->days = $package_tplt->days;
            }
            $campaign_report = [
                'campaign' => $campaign,
                // 'format_types' => $formats,
                'packages' => $packages_in_campaign
            ];
            $pdf = PDF::loadView('pdf.metro_campaign_details_pdf', $campaign_report);
            }
            
            event(new OfferRequestedEvent([
              'type' => Notification::$NOTIFICATION_TYPE['request-offer'],
              'from_id' => null,
              'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
              'to_id' => null,
              'to_client' => null,
              'desc' => "Delete Campaign Request ",
              'message' => " Requested an offer for campaign",
              'data' => " Requested an offer for campaign"
            ]));
            $notification_obj = new Notification;
            $notification_obj->id = uniqid();
            $notification_obj->type = "offer_request";
            $notification_obj->from_id =  null;
            $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['bbi'];
            $notification_obj->to_id = null;
            $notification_obj->to_client = null;
            $notification_obj->desc = " Requested an offer for campaign";
            $notification_obj->message = " Requested an offer for campaign";
                    //$notification_obj->user_id = $user->id;
                    $notification_obj->status = 0;
                    $notification_obj->save();

            $mail_tmpl_params = [
                'sender_email' => $user['email'],
                'receiver_name' => 'Richard',
                'price' => $this->input['price'],
                'comments' => $this->input['comments']
            ];
            //echo '<pre>mail_tmpl_params'; print_r($mail_tmpl_params);exit;   
            $mail_data = [
                //'email_to' => $this->input['email'],
                //'email_to' => 'admin@advertisingmarketplace.com',
                //'email_to' => 'sandhyarani.manelli@peopletech.com',
                'email_to' => 'sandhyasandym.17@gmail.com',
                'user_price' => $this->input['price'],
                'user_comments' => $this->input['comments'],
                'pdf_file_name' => "Make-Offer-" . $campaign->cid . "-" . date('m-d-Y') . ".pdf",
                'pdf' => $pdf
            ];
            //echo '<pre>mail_data'; print_r($mail_data);exit;  
            Mail::send('mail.make_offer', $mail_tmpl_params, function($message) use ($mail_data) {
                $message->to($mail_data['email_to'], $mail_data['user_price'], $mail_data['user_comments'])->subject('Make an Offer-Advertising Marketplace!');
                //$message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Make an Offer-Advertising Marketplace!');
                $message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
            });
            //echo '<pre>Mail'; print_r($Mail);exit; 
             if (!Mail::failures()) {
                return response()->json(['status' => 1, 'message' => "Your request for price $" . number_format($this->input['price']) .  " has been sent successfully. We will get back to you shortly.."]);
            } else {
                return response()->json(['status' => 0, 'message' => "There was an error sending the email. Please try again."]);
            }
                    } catch (Exception $ex) {
            return response()->json(['status' => 0, 'message' => "There was an error generating the campaign report."]);
        }
    } 
      
    public function getAllOffers(){
        $user_offers = MakeOffer::orderBy('created_at', 'desc')->get();
        return response()->json($user_offers);
    }
    
    public function RFPCampaign() {
         if (isset($this->input['id']) && !empty($this->input['id'])) {
            $this->validate($this->request, [
                'name' => 'required',
                'start_date' => 'required',
                'end_date' => 'required'
                    ], [
                'name.required' => 'Name is required',
                'start_date.required' => 'Start date is required',
                'end_date.required' => 'End date is required'
                    ]
            );
            $start_date_obj = new \DateTime($this->input['start_date']);
            $end_date_obj = new \DateTime($this->input['end_date']);
            $min_end_date_required = $start_date_obj->add(new \DateInterval('P15D'));
            if ($start_date_obj < (new \DateTime('now'))->add(new \DateInterval('P5D'))) {
                return response()->json(['status' => 0, 'message' => ['Campaign start date has to be at least 5 days from today.']]);
            }
            if ($end_date_obj < $min_end_date_required) {
                return response()->json(['status' => 0, 'message' => ['Campaign duration has to be at least 15 days']]);
            }
            $name_slug_string = str_replace(" ", "-", strtolower($this->input['name']));
            $campaign_obj = Campaign::where('id', '=', $this->input['id'])->first();
            if ($name_slug_string == $campaign_obj->slug) {
                return response()->json(['status' => 0, 'message' => "Campaign name must be unique."]);
            }
            $campaign_obj->name = isset($this->input['name']) ? $this->input['name'] : "";
            $campaign_obj->slug = $name_slug_string;
            $campaign_obj->start_date = isset($this->input['start_date']) ? $this->input['start_date'] : "";
            $campaign_obj->end_date = isset($this->input['end_date']) ? $this->input['end_date'] : "";
            $campaign_obj->format_type = Format::$FORMAT_TYPE['ooh'];
            $campaign_obj->type = Campaign::$CAMPAIGN_USER_TYPE['user'];
            if ($campaign_obj->save()) {
                $campaign_suggestion_request = CampaignSuggestionRequest::where('campaign_id', '=', $campaign_obj->id)->first();
                $campaign_suggestion_request->processed = true;
                if (!$campaign_suggestion_request->save()) {
                    Log::error("campaign suggestion request status couldn't be changed. campaign suggestion request id:" . $campaign_suggestion_request->id);
                }
                return response()->json(["status" => "1", "message" => "campaign saved successfully."]);
            } else {
                return response()->json(["status" => "0", "message" => "Failed to save campaign."]);
            }
        } else {
            $this->validate($this->request, [
                'name' => 'required'
                    ], [
                'name.required' => 'Name is required'
                    ]
            );
            //echo "<pre>request";print_r($this->request);exit;
            $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
            if ($user_mongo['user_type'] != 'basic' ) {
                return response()->json(['status' => 0, 'message' => "You can not create a campaign from here. Please switch to your dashboard."]);
            }
            $campaign_obj = new Campaign;
            $campaign_obj->id = uniqid();
            $campaign_obj->cid = $this->generatecampaignID();
            $campaign_obj->name = isset($this->input['name']) ? $this->input['name'] : "";
            $campaign_obj->slug = str_replace(" ", "-", strtolower($this->input['name']));
            $campaign_obj->est_budget = isset($this->input['est_budget']) ? $this->input['est_budget'] : "";
            $campaign_obj->created_by = $user_mongo['id'];
            $campaign_obj->status = Campaign::$CAMPAIGN_STATUS['campaign-preparing'];
            $campaign_obj->type = Campaign::$CAMPAIGN_USER_TYPE['user'];
            //echo "<pre>campaign_obj";print_r($campaign_obj);exit;
            
            if ($campaign_obj->save()) {
                $success = true;
                Log::info($campaign_obj->id);
                if (isset($this->input['shortlisted_products']) && !empty($this->input['shortlisted_products'])) {
                    // move products from shortlisted_products collection to product_bookings collection

                    foreach ($this->input['shortlisted_products'] as $shortlisted_id) {
                        $shortlisted = ShortListedProduct::where('id', '=', $shortlisted_id)->first();
                        //echo "<pre>"; print_r($shortlisted);
                        $product = Product::where('id', '=', $shortlisted->product_id)->first();
                        $new_booking = new ProductBooking;
                        $new_booking->id = uniqid();
                        $new_booking->campaign_id = $campaign_obj->id;
                        $new_booking->product_id = $product->id;
                        $new_booking->booked_from = iso_to_mongo_date($shortlisted->from_date);
                        $new_booking->booked_to = iso_to_mongo_date($shortlisted->to_date);
                        if(isset($shortlisted->booked_slots) && $shortlisted->booked_slots!='' ){
                        $new_booking->booked_slots = $shortlisted->booked_slots;
                        }
                        $new_booking->price = $shortlisted->price;
                        $new_booking->product_owner = $product->client_mongo_id;
                        $new_booking->product_status = ProductBooking::$PRODUCT_STATUS['proposed'];
                        if (!$new_booking->save()) {
                            $success = false;
                            break;
                        } else {
                            $shortlisted->delete();
                        }
                    }
                    //exit;
                } else if (isset($this->input['products']) && !empty($this->input['products'])) {
                    $products = $this->input['products'];
                    $product_id = $products[0]['product_id'];
                    foreach ($products[0]['dates'] as $dr) {
                        $product = Product::where('id', '=', $product_id)->first();
                        $new_booking = new ProductBooking;
                        $new_booking->id = uniqid();
                        $new_booking->campaign_id = $campaign_obj->id;
                        $new_booking->product_id = $product_id;
                        $new_booking->booked_from = iso_to_mongo_date($dr['startDate']);
                        $new_booking->booked_to = iso_to_mongo_date($dr['endDate']);
                        //$new_booking->price = $product->default_price;
                        $new_booking->price = $product->rateCard;
                        $new_booking->product_owner = $product->client_mongo_id;
                        $new_booking->product_status = ProductBooking::$PRODUCT_STATUS['proposed'];
                        $new_booking->save();
                    }
                }
                if ($success) {
                    return response()->json(["status" => "1", "message" => "campaign saved successfully and products added."]);
                } else {
                    return response()->json(["status" => "0", "message" => "campaign saved successfully but product addition failed."]);
                }
            } else {
                return response()->json(["status" => "0", "message" => "Failed to save campaign."]);
            }
        }
       
    }
    
    public function getAdminCampaigns() {
        $admin_campaigns = Campaign::whereIn('format_type', [null, Format::$FORMAT_TYPE['ooh']])->where([
            ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['bbi']],
        ])->orderBy('updated_at', 'desc')->get();
        
        $current_date = date('d-m-Y');
        $colorcode = '';
        $diff= '';

        if (!empty($admin_campaigns)) {
            $i = 0;
            foreach ($admin_campaigns as $campaign) {
                $total_paid = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('amount');
                $total_price = ProductBooking::where('campaign_id', '=', $campaign->id)->sum('price');
                $count = ProductBooking::where('campaign_id', '=', $campaign->id)->count();
                $admin_campaigns[$i]->total_paid = $total_paid;
                $admin_campaigns[$i]->total_price = $total_price;
                $admin_campaigns[$i]->product_count = $count;
                $product_start_date = ProductBooking::where('campaign_id', '=', $campaign->id)->select("booked_from","booked_to")->orderBy('booked_from', 'asc')->first();
                if (!empty($product_start_date)) {
                    $admin_campaigns[$i]->start_date = $product_start_date->booked_from;
                    $admin_campaigns[$i]->end_date = $product_start_date->booked_to;
                    $diff=date_diff(date_create($current_date),date_create($admin_campaigns[$i]->start_date));
                    if($diff->days <=7 ){
                        $admin_campaigns[$i]->colorcode = 'red';
                    }else{
                        $admin_campaigns[$i]->colorcode = 'black';
                    }
                }
                ++$i;
            }
        }
        
        $owner_campaigns = Campaign::whereIn('format_type', [null, Format::$FORMAT_TYPE['ooh']])->where([
            ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['owner']],
        ])->orderBy('updated_at', 'desc')->get();

        if (!empty($owner_campaigns)) {
            $j = 0;
            foreach ($owner_campaigns as $campaign) {
                $total_paid = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('amount');
                $total_price = ProductBooking::where('campaign_id', '=', $campaign->id)->sum('price');
                $count = ProductBooking::where('campaign_id', '=', $campaign->id)->count();
                $owner_campaigns[$j]->total_paid = $total_paid;
                $owner_campaigns[$j]->total_price = $total_price;
                $owner_campaigns[$j]->product_count = $count;
                $product_start_date = ProductBooking::where('campaign_id', '=', $campaign->id)->select("booked_from","booked_to")->orderBy('booked_from', 'asc')->first();
                if (!empty($product_start_date)) {
                    $owner_campaigns[$j]->start_date = $product_start_date->booked_from;
                    $owner_campaigns[$j]->end_date = $product_start_date->booked_to;
                    $diff=date_diff(date_create($current_date),date_create($owner_campaigns[$j]->start_date));
                    if($diff->days <=7 ){
                        $owner_campaigns[$j]->colorcode = 'red';
                    }else{
                        $owner_campaigns[$j]->colorcode = 'black';
                    }
                }
                ++$j;
            }
        }
        
        $campaigns = [
            'admin_campaigns' => $admin_campaigns,
            'owner_campaigns' => $owner_campaigns
        ];
        return response()->json($campaigns);
    }
  
    public function findForMe() {
        //echo 'dssd';exit;

        $this->validate($this->request, [
            'user_query' => 'required',
                //'campaign_type' => 'required'
                ], [
            'user_query.required' => 'Text is required',
                //'campaign_type.required' => 'Campaign type is required'
                ]
        );
        
            $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo']; 
               $user = User::where('id', '=', $user_mongo['user_id'])->first();
            if ($user_mongo['user_type'] != 'basic' && $user_mongo['user_type'] != 'owner' ) {
                return response()->json(['status' => 0, 'message' => "You can not create a campaign from here. Please switch to your dashboard."]);
            }
         
        
            $find_for_me = new FindForMe;
            $find_for_me->user_query = isset($this->input['user_query']) ? $this->input['user_query'] : "";
            $find_for_me->budget_rfp = isset($this->input['budget_rfp']) ? $this->input['budget_rfp'] : "";
            $find_for_me->loggedinUser = isset($this->input['loggedinUser']) ? $this->input['loggedinUser'] : "";
			//$date_format1 = iso_to_mongo_date(strtotime("Y-m-d h:m:s"));
            //$find_for_me->bulkupload_uniqueID = $user_mongo['id'].'-'.date("Y-m-d h:m:s");
            $find_for_me->id = uniqid();
            $find_for_me->created_by = $user_mongo['id'];
            if($user_mongo['user_type'] == 'basic'){
            $find_for_me->user_type = FindForMe::$CAMPAIGN_USER_TYPE['user'];
            }
            else if($user_mongo['user_type'] == 'owner'){
            $find_for_me->user_type = FindForMe::$CAMPAIGN_USER_TYPE['owner'];
            $client_mongo = ClientMongo::where('client_id', '=', $user->client->id)->first();
            $find_for_me->client_mongo_id = $client_mongo->id;
            $find_for_me->client_name = $client_mongo->name;
            }
			if(isset($this->input['campaign_id'])){
				$find_for_me->campaign_id = isset($this->input['campaign_id']) ? $this->input['campaign_id'] : "";
			}
            //echo '<pre>';print_r($date_format1);exit; 
            $find_for_me->save();
            $user = JWTAuth::parseToken()->getPayload()['user'];
            $mail_tmpl_params = [
                'sender_email' => $user['email'],
                'receiver_name' => 'Richard',
                'user_query' => $this->input['user_query']
            ];
            //echo '<pre>mail_tmpl_params'; print_r($mail_tmpl_params);exit;      
            $mail_data = [
                //'email_to' => $this->input['email'],
               'email_to' => 'ganji.dileep@peopletech.com',
                //'email_to' => 'deekshitha.bhupathi@peopletech.com',
                'user_query' => $this->input['user_query']
            ];
            //echo '<pre>mail_data'; print_r($mail_data);exit;  
            Mail::send('mail.find_for_me', $mail_tmpl_params, function($message) use ($mail_data) {
                $message->to($mail_data['email_to'], $mail_data['user_query'])->subject('Find For Me-Advertising Marketplace!');
            });
            //echo '<pre>Mail'; print_r($Mail);exit; 
             if (!Mail::failures()) {
                return response()->json(['status' => 1, 'message' => "Your query has been sent successfully. We will get back to you shortly.."]);
            } else {
                return response()->json(['status' => 0, 'message' => "There was an error sending the email. Please try again."]);
            }
    }
     
    public function getFinForMe(){
		 $find_for_me = FindForMe::orderBy('updated_at', 'desc')->get();
		 $find_for_me_arr = [];     
        $j = 0;
        foreach ($find_for_me as $find_for_me) {
            
            $campaign_details = Campaign::select('name', 'cid')->where('id', '=', $find_for_me->campaign_id)->first();
            //echo '<pre>';print_r($campaign_details);exit; 
            if($campaign_details){
               $campaign_detailstoArray = $campaign_details->toArray();
            }else{
                $campaign_detailstoArray = [];
            }
            array_push($find_for_me_arr, array_merge($find_for_me->toArray(), $campaign_detailstoArray));
            ++$j;
        }
        return response()->json($find_for_me_arr);
    }
 
    public function requestForCancelCampaign() {

        $this->validate($this->request, [
            'user_query' => 'required',
            'campaign_id' => 'required',
                //'campaign_type' => 'required'
                ], [
            'user_query.required' => 'Text is required',
                //'campaign_type.required' => 'Campaign type is required'
                ]
        );
		
		$total_paid = CampaignPayment::where('campaign_id', '=', $this->input['campaign_id'])->sum('amount');
		if($total_paid < $this->input['price']){
			return response()->json(['status' => 0, 'message' => "Entered amount should be less than campaign paid amount. Please try again."]);
		}
		
            $cancel_campaign = new CancelCampaign;
            $cancel_campaign->campaign_id = isset($this->input['campaign_id']) ? $this->input['campaign_id'] : "";
            $cancel_campaign->user_query = isset($this->input['user_query']) ? $this->input['user_query'] : "";
            $cancel_campaign->price = isset($this->input['price']) ? $this->input['price'] : "";
            //$cancel_campaign->pro_percent = isset($this->input['pro_percent']) ? $this->input['pro_percent'] : "";
            $cancel_campaign->loggedinUser = isset($this->input['loggedinUser']) ? $this->input['loggedinUser'] : "";
            $cancel_campaign->status = CancelCampaign::$CAMPAIGN_STATUS['cancel-campaign-request'];
            $cancel_campaign->id = uniqid();
            $cancel_campaign->save();
            
            
            $campaign_obj = Campaign::where([
                    ['id', '=', $this->input['campaign_id']],
                ])->first();
            $campaign_user_mongo = UserMongo::where('id', '=', $campaign_obj->created_by)->first();
            
            event(new CampaignDeleteRequestedEvent([
              'type' => Notification::$NOTIFICATION_TYPE['delete-campaign'],
              'from_id' => null,
              'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
              'to_id' => null,
              'to_client' => null,
              'campaign_id' => $campaign_obj->id,
              'c_id' => $campaign_obj->cid,
              'c_name' => $campaign_obj->name,
              'desc' => "Delete Campaign Request ",
              'message' => " Requested to delete campaign",
              'data' => " Requested to delete campaign"
            ]));
            $notification_obj = new Notification;
            $notification_obj->id = uniqid();
            $notification_obj->type = "delete_campaign";
            $notification_obj->from_id =  null;
            $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['bbi'];
            $notification_obj->to_id = null;
            $notification_obj->to_client = null;
            $notification_obj->campaign_id = $campaign_obj->id;
            $notification_obj->c_id = $campaign_obj->cid;
            $notification_obj->c_name = $campaign_obj->name;
            $notification_obj->desc = "Delete Campaign Request";
            $notification_obj->message = " Requested to delete campaign";
                    //$notification_obj->user_id = $user->id;
                    $notification_obj->status = 0;
                    $notification_obj->save();
            
            $user = JWTAuth::parseToken()->getPayload()['user'];
            $mail_tmpl_params = [
                'sender_email' => $user['email'],
                'receiver_name' => 'Richard',
                'user_query' => $this->input['user_query']
            ];
            //echo '<pre>mail_tmpl_params'; print_r($mail_tmpl_params);exit;        
            $mail_data = [
                //'email_to' => $this->input['email'],
               //'email_to' => 'info@advertisingmarketplace.com',
                'email_to' => 'deekshitha.bhupathi@peopletech.com',
                'user_query' => $this->input['user_query']
            ];
            //echo '<pre>mail_data'; print_r($mail_data);exit;  
            Mail::send('mail.find_for_me', $mail_tmpl_params, function($message) use ($mail_data) {
                $message->to($mail_data['email_to'], $mail_data['user_query'])->subject('Request Cancel Campaign-Advertising Marketplace!');
            });
            if (!Mail::failures()) {
            //echo '<pre>Mail'; print_r($Mail);exit; 
                return response()->json(['status' => 1, 'message' => "Your request has been sent successfully. We will get back to you shortly.."]);
            } else {
                return response()->json(['status' => 0, 'message' => "There was an error sending the request. Please try again."]);
            }
    }
 
    public function getRequestedCampaigns(){
        $campaigns = CancelCampaign::orderBy('updated_at', 'desc')->get();
        //return response()->json($campaigns);
        
        
        $cancel_campaigns_arr = [];     
        $j = 0;
        foreach ($campaigns as $cancel_campaign) {
            
            $campaign_details = Campaign::select('name', 'cid')->where('id', '=', $cancel_campaign->campaign_id)->first();
            //echo '<pre>';print_r($campaign_details);exit; 
            if($campaign_details){
               $campaign_detailstoArray = $campaign_details->toArray();
            }else{
                $campaign_detailstoArray = [];
            }
            array_push($cancel_campaigns_arr, array_merge($cancel_campaign->toArray(), $campaign_detailstoArray));
            ++$j;
        }
         
        /*$campaigns = [
            'user_campaigns' => $cancel_campaigns_arr,
            //'admin_campaigns' => $admin_campaigns
        ];*/
        return response()->json($cancel_campaigns_arr);
        
          
    }
    
    
     //public function getCampaignDetails($campaign_id) {
     public function getOfferDetails($campaign_id) {
 
    /*$user_offer = MakeOffer::where('campaign_id', '=', $campaign_id)->first();
    if(!isset($user_offer) || empty($user_offer)){
      return response()->json(['status' => 0, 'message' => "Campaign not found in database."]);
    }
    
    $user_offers = [
      'id' => $user_offer['id'],
      '_id' => $user_offer['_id'],
      'price' => $user_offer['price'],
      'comments' => $user_offer['comments'],
      'status' => $user_offer['status']
      //'loggedinUser' => $user_offer[loggedinUser('user_id')]
    ];

    
    return response()->json($user_offer);*/  
    $product_detail = MakeOffer::where('campaign_id', '=', $campaign_id)->get()->toArray();
    if(empty($product_detail)){
              return response()->json(['status' => 0, 'message' => 'No Offer']);
            }
			$product_detail = MakeOffer::where('campaign_id', '=', $campaign_id)->first()->toArray();
    //echo '<pre>'; print_r($product_detail);exit;
        $campaign_product_ids = MakeOffer::where('campaign_id', '=', $campaign_id)->pluck('id')->toArray();
        //echo '<pre>'; print_r($campaign_product_ids);exit;
        $campaign_product_count = array_filter($campaign_product_ids);
        $campaigns = MakeOffer::whereIn('id', $campaign_product_count)->select('id','price','comments','status','campaign_id','message','AdminOfferAcceptReject')
                ->orderBy('created_at')
                ->get();
                
        if(!empty($campaigns)){
             
            
        
                
                //echo '<pre>'; print_r($user_offers);exit;yy
                //echo '<pre>'; print_r($campaigns);exit;   //all offer records per campaign 
        $campaign_list = array();
        //echo '<pre>'; print_r($campaign_list);exit;
        foreach ($campaigns as $val) {
            $campaign_product = MakeOffer::where('campaign_id', '=', $val->campaign_id)->get();
    
            
            //echo '<pre>'; print_r($campaign_product);exit; 
            /*$campaign_payments = CampaignPayment::where('campaign_id', '=', $val->id)->get();
            if (isset($campaign_payments) && count($campaign_payments) > 0) {
                $total_paid = $campaign_payments->sum('amount');
                $val['total_paid'] = $total_paid;
            } else {
                $val['total_paid'] = 0;
            }*/  
            //$val['no_offers'] = count($campaign_product);
            //echo '<pre>'; print_r($val['no_products']);exit; 
            //$campaign_list[] = $val['no_offers'];
            //echo '<pre>'; print_r($campaign_list);exit;
        }
       $send_details = []; 
        /*$send_details['campaign_list'] = $campaign_list;  
        //echo '<pre>'; print_r($campaign_list);exit;
        $send_details['product_detail'] = $campaigns;
        //echo '<pre>'; print_r($campaigns);exit;
        $send_details['product_detail']['offers'] = count($campaigns);*/
        
        return response()->json($campaigns);
        }
        else {

                 return response()->json(['status' => 0, 'message' => 'No Offer']);
            } 
    
    }
    
    public function acceptRejectOffer(Request $request){
        /*campaigns = MakeOffer::orderBy('updated_at', 'desc')->get();
        return response()->json($campaigns);*/
        
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        //echo '<pre>'; print_r($user_mongo); exit; 
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        
         if (isset($input['client'])) {
            $client = ClientMongo::where('id', '=', $input['client'])->first();
        }else{
            $client = ClientMongo::where('id', '=', $user_mongo['client_mongo_id'])->first();
        }
         
         if (isset($input['id'])) {
            //echo '<pre>in if'; print_r($input); exit;
            $product_obj = MakeOffer::where('id', '=', $input['id'])->first();
            /*if(!empty($product_obj) && count($product_obj) > 0){
              return response()->json(['status' => MakeOffer::$CAMPAIGN_STATUS['offer-accepted-one'], 'message' => 'Error']);
            }*/
            $product_obj->pro_percent = isset($input['percentage']) ? $input['percentage'] : $product_obj->pro_percent;
            $product_obj->campaign_id = isset($input['campaign_id']) ? $input['campaign_id'] : $product_obj->campaign_id;
            $product_obj->message = isset($input['message']) ? $input['message'] : $product_obj->message;
            //$product_obj->type = isset($input['type']) ? $input['type'] : $product_obj->type;
             $product_obj->type = isset($input['type']) ? $input['type'] : "";
            //echo '<pre>'; print_r($product_obj->type); exit; 
            //$product_obj->status = MakeOffer::$CAMPAIGN_STATUS['offer-rejected-one'];   
            $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
            $campaign_obj = Campaign::where([
                    ['id', '=', $product_obj->campaign_id],
                ])->first();
            //echo '<pre>'; print_r($campaign_obj); exit;
            if($product_obj->type == 'reject')
            {
                $product_obj->status = MakeOffer::$OFFER_STATUS['offer-rejected-one'];
                if($product_obj->save())
                {
                    $campaign_user_mongo = UserMongo::where('id', '=', $campaign_obj->created_by)->first();
                    event(new OfferRejectedEvent([
                      'type' => Notification::$NOTIFICATION_TYPE['offer-rejected'],
                      'from_id' => null,
                      'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['user'],
                      'to_id' => $campaign_obj->created_by,
                      'to_client' => $campaign_obj->created_by,
                      'c_id' => $campaign_obj->cid,
                      'c_name' => $campaign_obj->name,
                      'desc' => "Offer rejected",
                      'message' => " Admin has rejected your offer",
                      'data' => " Admin has rejected your offer"
                    ]));
                    $notification_obj = new Notification;
                    $notification_obj->id = uniqid();
                    $notification_obj->type = "offer_rejected";
                    $notification_obj->from_id = null;
                    $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['user'];
                    $notification_obj->to_id = $campaign_obj->created_by;
                    $notification_obj->to_client = $campaign_obj->created_by;
                    $notification_obj->c_id = $campaign_obj->cid;
                    $notification_obj->c_name = $campaign_obj->name;
                    $notification_obj->desc = "Offer rejected";
                    $notification_obj->message = " Admin has rejected your offer";
                    //$notification_obj->user_id = $user->id;
                    $notification_obj->status = 0;
                    $notification_obj->save();
                     
                    
                    $mail_tmpl_params = [
                    //'sender_email' => $user['email'],
                    'sender_email' => config('app.bbi_email'),
                    'receiver_name' => $campaign_user_mongo->first_name,
                    'mail_msg' => $this->input['message'],
                    'mail_message' => "Admin has rejected the offer for your campaign <b>'" . $campaign_obj->name . "'</b> "
                    ];
                    //echo '<pre>mail_tmpl_params'; print_r($mail_tmpl_params);exit;        
                    $mail_data = [
                        'email_to' => $campaign_user_mongo->email,
                        'mail_msg' => $this->input['message']
                       //'email_to' => 'sandhyasandym.17@gmail.com'
                    ];
                    //echo '<pre>mail_data'; print_r($mail_data);exit;  
                    Mail::send('mail.reject_offer', $mail_tmpl_params, function($message) use ($mail_data) {
                        $message->to($mail_data['email_to'], $mail_data['mail_msg'])->subject('Offer Rejected-Advertising Marketplace!');
                    });
                
                    
                    return response()->json(["status" => "1",'OfferStatus' => $product_obj->status, 'message' => 'Rejected Successfully']);
                }
                 else {
                    return response()->json(["status" => "0", "message" => "Failed to Update Status."]);
                }
                
                //return response()->json(["status" => "1",'OfferStatus' => $product_obj->status, 'message' => 'Rejected Successfully']); 
                
            }
            else if($product_obj->type == 'accept')
            {   
                //$product_obj->status = MakeOffer::$CAMPAIGN_STATUS['offer-accepted-one'];
                $product_obj->status = MakeOffer::$OFFER_STATUS['offer-accepted-one'];
                //echo '<pre>'; print_r($product_obj->status1); exit;  
                if($product_obj->save())
                {
                    
                    $campaign_user_mongo = UserMongo::where('id', '=', $campaign_obj->created_by)->first();
                    event(new OfferAcceptedEvent([
                      'type' => Notification::$NOTIFICATION_TYPE['offer-accepted'],
                      'from_id' => null,
                      'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['user'],
                      'to_id' => $campaign_obj->created_by,
                      'to_client' => $campaign_obj->created_by,
                      'c_id' => $campaign_obj->cid,
                      'c_name' => $campaign_obj->name,
                      'desc' => "Offer Accepted",
                      'message' => " Admin has accepted your offer",
                      'data' => " Admin has accepted your offer"
                    ]));
                    $notification_obj = new Notification;
                    $notification_obj->id = uniqid();
                    $notification_obj->type = "offer_accepted";
                    $notification_obj->from_id = null;
                    $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['user'];
                    $notification_obj->to_id = $campaign_obj->created_by;
                    $notification_obj->to_client = $campaign_obj->created_by;
                    $notification_obj->c_id = $campaign_obj->cid;
                    $notification_obj->c_name = $campaign_obj->name;
                    $notification_obj->desc = "Offer Accepted";
                    $notification_obj->message = " Admin has accepted your offer";
                            //$notification_obj->user_id = $user->id;
                    $notification_obj->status = 0;
                    $notification_obj->save();

                    $mail_tmpl_params = [
                    //'sender_email' => $user['email'],
                    'sender_email' => config('app.bbi_email'),
                    'receiver_name' => $campaign_user_mongo->first_name,
                    'mail_msg' => $this->input['message'],
                    'mail_message' => "Admin has accepted the offer for your campaign <b>'" . $campaign_obj->name . "'</b> "
                    ];
                    //echo '<pre>mail_tmpl_params'; print_r($mail_tmpl_params);exit;         
                    $mail_data = [
                        'email_to' => $campaign_user_mongo->email,
                        'mail_msg' => $this->input['message']
                       //'email_to' => 'sandhyasandym.17@gmail.com'
                    ];
                    //echo '<pre>mail_data'; print_r($mail_data);exit;  
                    Mail::send('mail.reject_offer', $mail_tmpl_params, function($message) use ($mail_data) {
                        $message->to($mail_data['email_to'], $mail_data['mail_msg'])->subject('Offer Accepted-Advertising Marketplace!');
                    });
                    
                    return response()->json(["status" => "1",'OfferStatus' => $product_obj->status, 'message' => 'Accepted Successfully']);
                }
                 else {
                    return response()->json(["status" => "0", "message" => "Failed to Update Status."]);
                }
            }
            else
            {
                return response()->json(["status" => "0", "message" => "Failed"]);
            }
              
    }
    }
    
    
    public function requestForDeleteProductFromCampaign(Request $request) {

        $this->validate($this->request, [
            'comments' => 'required'
            //'product_id' => 'required',
                //'campaign_type' => 'required'
                ], [
            'comments.required' => 'Text is required',
                //'campaign_type.required' => 'Campaign type is required'   
                ]
        );
        
        if ($request->isJson()) {
                $input = $request->json()->all();
            } else {
                $input = $request->all();
            }
        
            $productids = $input['product_id'];
            //echo '<pre>productids'; print_r($productids);exit;  
            /*$productids = $input['product_id'];
            //echo '<pre>'; print_r($productids);exit;  
            
            foreach($productids as $productids){
          
            $delete_prod = DeleteProduct::where([
                                                ['product_id', '=', $productids],
                                                ['campaign_id', '=', $this->input['campaign_id']]
                                            ])->get();*/
            /*$delete_prod = DeleteProduct::where([
                                                ['product_id', '=', $this->input['product_id']],
                                                ['campaign_id', '=', $this->input['campaign_id']]
                                            ])->get();
                                        
            //echo '<pre>'; print_r($delete_prod);exit;      
            
            if(!empty($delete_prod) && count($delete_prod) > 0){
              return response()->json(['status' => 0, 'message' => 'You have already requested this product to delete']);
            }*/ 
          
			//echo'<pre>';print_r($this->input['product_id']);
			foreach($this->input['product_id'] as $key => $value){
				$explode_product = @explode('-',$value);
				$explode_product_id[] = $explode_product[0];
				$explode_booking_id[] = $explode_product[1];
			}
				//echo'<pre>';print_r($explode_product_id);
				//echo'<pre>';print_r($explode_booking_id);exit;
				$delete_product = new DeleteProduct;
				//$explode_product = @explode('-',$value);
				
				$delete_product->campaign_id = isset($this->input['campaign_id']) ? $this->input['campaign_id'] : ""; 
				$delete_product->product_id = isset($explode_product_id) ? $explode_product_id : "";
				//$delete_product->product_id = $productids;
				$delete_product->comments = isset($this->input['comments']) ? $this->input['comments'] : "";
				$delete_product->price = isset($this->input['price']) ? $this->input['price'] : "";
				$delete_product->productbookingid = isset($explode_booking_id) ? $explode_booking_id : "";
				$delete_product->loggedinUser = isset($this->input['loggedinUser']) ? $this->input['loggedinUser'] : "";
				$delete_product->status = DeleteProduct::$PRODUCT_STATUS['delete-product-from-campaign'];
				$delete_product->id = uniqid();
				
				//$product_id = $input['product_id'];
				//echo '<pre>'; print_r($product_id);exit;       
				 
				$delete_product->save();
             
            /*if(is_array($productids)){
                //echo 'is_array_productids';print_r($productids);exit;
                foreach($productids as $product_id){
                    //$product_id = $product_id['product_id']; //product_id-productbookingid
                    $product_id = $product_id; //product_id-productbookingid
                    //echo 'product_id';print_r($product_id);exit;
                    $explode_product_id = explode("-", $product_id);
                    //echo 'explode_product_id';print_r($product_id);exit;
                    $productbookingid = $explode_product_id[1];
                    
                    $productbooking_obj = ProductBooking::where('id', '=', $productbookingid)->first();
                    $productbooking_obj->product_status = ProductBooking::$PRODUCT_STATUS['delete-requested'];
                    //echo 'psroductbooking_obj';print_r($productbooking_obj);exit;
                    $productbooking_obj->save();
                }
            }*/

            
            $campaign_obj = Campaign::where([
                    ['id', '=', $this->input['campaign_id']],
                ])->first();
            $campaign_user_mongo = UserMongo::where('id', '=', $campaign_obj->created_by)->first();
            
            $user = JWTAuth::parseToken()->getPayload()['user'];
            event(new ProductDeleteRequestedEvent([
              'type' => Notification::$NOTIFICATION_TYPE['delete-product-from-campaign'],
              'from_id' => null,
              'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
              'to_id' => null,
              'to_client' => null,
              'campaign_id' => $campaign_obj->id,
              'c_id' => $campaign_obj->cid,
              'c_name' => $campaign_obj->name,
              'product_id' => $delete_product->product_id,
              'desc' => "Delete Product Request ",
              'message' => " Requested to delete a product from his campaign",
              'data' => " Requested to delete a product from his campaign"
            ]));
            $notification_obj = new Notification;
            $notification_obj->id = uniqid();
            $notification_obj->type = "delete_product_from_campaign";
            $notification_obj->from_id =  null;
            $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['bbi'];
            $notification_obj->to_id = null;
            $notification_obj->to_client = null;
            $notification_obj->campaign_id = $campaign_obj->id;
            $notification_obj->c_id = $campaign_obj->cid;
            $notification_obj->c_name = $campaign_obj->name;
            $notification_obj->product_id = $delete_product->product_id;
            $notification_obj->desc = "Delete Product Request";
            $notification_obj->message = " Requested to delete a product from his campaign";
                    //$notification_obj->user_id = $user->id;
                    $notification_obj->status = 0;
                    $notification_obj->save();
            
            //$user = JWTAuth::parseToken()->getPayload()['user'];
            $mail_tmpl_params = [
                'sender_email' => $user['email'],
                'receiver_name' => 'Richard',
                'mail_message' => 'Helo'
                //'comments' => $this->input['comments']
            ];
            //echo '<pre>mail_tmpl_params'; print_r($mail_tmpl_params);exit;       
            $mail_data = [
                //'email_to' => $this->input['email'],
               //'email_to' => 'info@advertisingmarketplace.com',
                'email_to' => 'sandhyarani.manelli@peopletech.com'
                //'comments' => $this->input['comments']
            ];
            //echo '<pre>mail_data'; print_r($mail_data);exit;  
            Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data) {
                $message->to($mail_data['email_to'], $mail_data['email_to'])->subject('Request For Delete Product From Campaign-Advertising Marketplace!');
            });
            if (!Mail::failures()) {
            //echo '<pre>Mail'; print_r($Mail);exit; 
                return response()->json(['status' => 1, 'message' => "Your request has been sent successfully. We will get back to you shortly.."]);
            } else {
                return response()->json(['status' => 0, 'message' => "There was an error sending the request. Please try again."]);
            }
    }
      
    public function getDeleteRequestedProductsFromCampaign(){
        $products = DeleteProduct::orderBy('created_at', 'desc')->get(); 
        //return response()->json($products); 
        
        
        $delete_products_arr = [];      
        $j = 0;
        foreach ($products as $delete_product) {
            
            $campaign_details = Campaign::select('name', 'cid')->where('id', '=', $delete_product->campaign_id)->first();
            //echo '<pre>';print_r($campaign_details);exit;  
            if($campaign_details){
               $campaign_detailstoArray = $campaign_details->toArray();
            }else{
                $campaign_detailstoArray = [];
            }
            array_push($delete_products_arr, array_merge($delete_product->toArray(), $campaign_detailstoArray));
            ++$j;
        }
        return response()->json($delete_products_arr);
    }
     
    /* RFP without login*/
    public function saveRFPCampaignWithoutLogin() {
	//echo'<pre>';print_r($this->input['user_email']);exit;
		$this->validate($this->request, [
					'campaign_name' => 'required'
						], [
					'campaign_name.required' => 'Campaign Name is required'
						]
				);
			
		$productBooked_last = ProductBooking::select("quantity","campaign_id","id")->get()->toArray();
		foreach($productBooked_last as $key => $value){
			if(!isset($value['quantity']) && isset($value['id'])){
				$product_obj_add = new ProductBooking;
				$product_obj_add = ProductBooking::where('id', '=', $value['id'])->first();
				$product_obj_add->quantity = "1";
				$product_obj_add->save();
			}
		} 	
		$repeated_user = Campaign::where('user_email', 'like', '%' . $this->input['user_email'] . '%')->get()->toArray(); 
		if(!empty($repeated_user)){
			if(strcasecmp($repeated_user[0]['user_email'], $this->input['user_email']) == 0){
				return response()->json(['status' => 0, 'message' => 'You have already requested an RFP, Please register into AMP to access RFP.']);
			}
		} 
		$registered_user = UserMongo::where('email', '=', $this->input['user_email'])->get()->toArray();
		if(!empty($registered_user)){
			if(strcasecmp($registered_user[0]['email'], $this->input['user_email']) == 0){
				return response()->json(['status' => 0, 'message' => 'You have already an account with this Email in AMP, please login and submit an RFP']);
			}
		}
        $campaign_obj = new Campaign; 
			
		//campaign unique ID duplicate start  
		$campaign_count = Campaign::latest()->first();
		$campaign_code_explode = explode("_", $campaign_count->cid);
		$uid_cid = '_'.str_pad(end($campaign_code_explode)+1, 6, '0', STR_PAD_LEFT);	
		//campaign unique ID duplicate end
			
		$campaign_obj->id = uniqid();
		$campaign_id = $campaign_obj->id;
		$campaign_obj->name = isset($this->input['campaign_name']) ? $this->input['campaign_name'] : "";
		$camp_name = $campaign_obj->name;
		$campaign_obj->cid = 'AMP_'.$camp_name.$uid_cid;
		$campaign_obj->user_email = isset($this->input['user_email']) ? $this->input['user_email'] : "";
		$campaign_obj->due_date = isset($this->input['due_date']) ? $this->input['due_date'] : "";
		$campaign_obj->slug = str_replace(" ", "-", strtolower($this->input['campaign_name']));
		$campaign_obj->est_budget = isset($this->input['est_budget']) ? $this->input['est_budget'] : "";
		$campaign_obj->status = Campaign::$CAMPAIGN_STATUS['rfp-campaign'];
		$campaign_obj->cmp_type = 1;
		$campaign_obj->status_read = 0;
              
        /*$filters_array = [];
        array_push($filters_array, ["product_visibility" => ['$ne' => "0"]]);

        if (isset($this->input['area']) && !empty($this->input['area'])) {
            $area_filter = $this->input['area'];
            array_push($filters_array, ["area" => ['$eq' => $area_filter]]);
        }
        if (isset($this->input['producttype']) && !empty($this->input['producttype'])) {
            $type_filter = $this->input['producttype'];
            array_push($filters_array, ["type" => ['$eq' => $type_filter]]);
        }

        if (isset($this->input['startDate']) && isset($this->input['endDate'])) {
            if (isset($this->input['startDate']) && !empty($this->input['startDate'])) {
                $from = $this->input['startDate'];
            }
            if (isset($this->input['endDate']) && !empty($this->input['endDate'])) {
                $to = $this->input['endDate'];
            }
             
            $start = iso_to_mongo_date($from);
            $startdate = date_create($from);
            $enddate = date_create($to);
            $curdate1 = date_create(date("Y-m-d"));
			
            $product_List = ProductBooking::where('booked_from', '<=', $enddate)
            ->where('booked_to', '>=', $startdate)
            ->where('booked_to', '>=', $curdate1)
            ->get(); 
			
            $prod_filter = [];
            if (count($product_List) > 0) {
                foreach ($product_List as $val) {
                    $prod_filter[] = $val->product_id;
                }
                
                array_push($filters_array, ["id" => ['$in' => $prod_filter]]);
            } else {
                array_push($filters_array, ["id" => ['$nin' => $prod_filter]]);
            }
        }
                 
		$type = $this->input['producttype'];
		$prod_area = $this->input['area'];
		if($type == 'All'){
			$grouped_products = Product::where([
				['from_date', '<=', $enddate],
				['to_date', '>=', $startdate],
				['to_date', '>=', $curdate1],
				['product_visibility', '=', 1],
			])->whereIn('area', $prod_area)
                ->whereIn('id', $prod_filter)->get();  
        }else {
			$grouped_products = Product::where([
				['type', '=', $type],
				['from_date', '<=', $enddate],
				['to_date', '>=', $startdate],
				['to_date', '>=', $curdate1],
				['product_visibility', '=', 1],
			])->whereIn('area', $prod_area)
			->whereIn('id', $prod_filter)->get(); 
		}*/
			
		$grouped_products = array(); 
		$curdate1 = date_create(date("Y-m-d"));
		$type = $this->input['producttype'];
		foreach($this->input['area'] as $key => $value){
			$date_ranges = @explode('::',$this->input['date_ranges'][$key]);
			$grouped_products_arr = Product::where([
				['to_date', '>=', $curdate1],
				['product_visibility', '=', 1],
			])->where('area', $value)
			->where('from_date', '<=', date_create($date_ranges[1]))
			->where('to_date', '>=', date_create($date_ranges[0]))
			->whereIn('type', $type)->get()->toArray();
			if(isset($grouped_products_arr) && !empty($grouped_products_arr)){
				$grouped_products[] = $grouped_products_arr;
			}
		}
 
        $res = $grouped_products;
        $resval = [];
		$resval2 = [];
		$resval3 = [];
		$resval4 = [];
		if(isset($res) && !empty($res)){
			foreach (call_user_func_array("array_merge", $res) as $res) {
				$resval[] = $res;
			}
		}
		$products_not_found = "";
        if ($campaign_obj->save()) {
			$success = true;
			if (isset($resval) && !empty($resval)) {
				$status_avail = 0;
				foreach ($resval as $resval_avail) {
					$product_avail = Product::where('id', '=', $resval_avail['id'])->first();
					$productBooked_avail = ProductBooking::where('product_id', '=',  $product_avail->id)->where('quantity', '!=',  '')->get();
					$available_quantity_avail = $product_avail->unitQty;
					if(isset($productBooked_avail)){
						$productBooked_last_avail = ProductBooking::select("quantity","campaign_id","id")->where('product_id', '=',  $product_avail->id)->where('booked_from','<=',$product_avail->to_date)->where('booked_to','>=',$product_avail->from_date)->where('product_status','!=',100)->where('product_status','!=',400)->where('product_status','!=',700)->where('quantity', '!=',  '')->groupBy('group_slot_id')->get([DB::raw('MAX(quantity) as quantity')]);
						$sum_quantity_avail = 0;
						if(isset($productBooked_last_avail)){
							$productBooked_last_avail = $productBooked_last_avail->toArray();
							foreach($productBooked_last_avail as $key => $value){
									$delete_product_status_avail = DeleteProduct::where([
																		['campaign_id', '=', $value['campaign_id']],
																		['status', '=', DeleteProduct::$PRODUCT_STATUS['confirm-delete-product-from-campaign']],
																	])->whereIn('product_id', array($product_avail->id))->whereIn('productbookingid', array($value['id']))->orderBy('created_at', 'desc')->first();
								if($value['campaign_id'] != ''){
									$campaign_delete_avail = Campaign::select("status")->where('id', '=', $value['campaign_id'])->first();
									if(empty($delete_product_status_avail) && ($campaign_delete_avail->status != 1200)){
										$sum_quantity_avail += $value['quantity'];
									}
								}
							}
						} 
						$available_quantity_avail = $product_avail->unitQty-$sum_quantity_avail;
						if($available_quantity_avail >= 0){
							$available_quantity_avail = $available_quantity_avail;
						}else{
							$available_quantity_avail = 0;
						}
					}
					if($available_quantity_avail > 0 && $status_avail == 0){
						$status_avail = 1;
						break;
					}
				}
				if($status_avail == 1){
					$success = true;
					Log::info($campaign_obj->id);
					
					// move products from shortlisted_products collection to product_bookings collection
					$rand = substr(str_shuffle(str_repeat("ABCDEFGHJKLMNPQRSTUVWXYZ", 3)), 0, 3);
					$group_id ="AMP".date('Ymd').$rand;
					foreach ($resval as $resval) {
						$product = Product::where('id', '=', $resval['id'])->first();
						$productBooked = ProductBooking::where('product_id', '=',  $product->id)->where('quantity', '!=',  '')->get();
						
						$available_quantity = $product->unitQty;
						if(isset($productBooked)){
							
							$productBooked_last = ProductBooking::select("quantity","campaign_id","id")->where('product_id', '=',  $product->id)->where('booked_from','<=',$product->to_date)->where('booked_to','>=',$product->from_date)->where('product_status','!=',100)->where('product_status','!=',400)->where('product_status','!=',700)->where('quantity', '!=',  '')->groupBy('group_slot_id')->get([DB::raw('MAX(quantity) as quantity')]);
							$sum_quantity = 0;
							if(isset($productBooked_last)){
								$productBooked_last = $productBooked_last->toArray();
								foreach($productBooked_last as $key => $value){
									$delete_product_status = DeleteProduct::where([
																		['campaign_id', '=', $value['campaign_id']],
																		['status', '=', DeleteProduct::$PRODUCT_STATUS['confirm-delete-product-from-campaign']],
																	])->whereIn('product_id', array($product->id))->whereIn('productbookingid', array($value['id']))->orderBy('created_at', 'desc')->first();
									if($value['campaign_id'] != ''){
										$campaign_delete = Campaign::select("status")->where('id', '=', $value['campaign_id'])->first();
										if(empty($delete_product_status) && ($campaign_delete->status != 1200)){
											$sum_quantity += $value['quantity'];
										}
									}
								}
							} 
							$available_quantity = $product->unitQty-$sum_quantity;
							if($available_quantity >= 0){
								$available_quantity = $available_quantity;
							}else{
								$available_quantity = 0;
							}
						}
						if($available_quantity > 0){
							$diff=date_diff(date_create($product->to_date->toDateTime()->format("Y-m-d")),date_create($product->from_date->toDateTime()->format("Y-m-d")));
							$daysCount = $diff->format("%a");
							$perdayprice = $resval['default_price']/28;
							if(isset($product->fix) && $product->fix=="Fixed"){
								$price =0;
								if(($daysCount+1) <= $product->minimumdays){
									$price = $perdayprice * $product->minimumdays;
								}else{
									$y  = 1;
									$z = 'aa';
									for ($x = 1; $x <= $y; $x++) {
										if(($product->minimumdays*$x) >= ($daysCount+1) && $z == 'aa'){
											$price = $perdayprice*($product->minimumdays*$x);
											$z = 'bb';
											$y++;
										}if($z == 'aa'){
											$y++;
										}
									}
								}
							}else{
								if(($daysCount+1) <= $product->minimumdays){
									$price = $perdayprice * $product->minimumdays;
								}else{
									$price = $perdayprice * ($daysCount+1);
								}
							}
							$new_booking = new ProductBooking;
							$new_booking->id = uniqid();
							$new_booking->campaign_id = $campaign_obj->id;
							$new_booking->product_id = $product->id;
							$new_booking->booked_from = ($product->from_date);
							$new_booking->booked_to = ($product->to_date);
							$new_booking->price = $price;
							$new_booking->product_owner = $product->client_mongo_id;
							$new_booking->product_status = ProductBooking::$PRODUCT_STATUS['rfp_proposed'];
							$new_booking->quantity = "1";
							$new_booking->group_slot_id = $group_id.$product->id;
							$new_booking->save();
						}
					}
					$products_not_found = " and products added.";
				} else {
					$rfp_search_criteria = new RFPSearchCriteria;
					$rfp_search_criteria->id = uniqid();
					$rfp_search_criteria->campaign_id = $campaign_obj->id;
					$rfp_search_criteria->demo = isset($this->input['demo']) ? $this->input['demo'] : "";
					$rfp_search_criteria->product_type = isset($this->input['producttype']) ? $this->input['producttype'] : "";
					$rfp_search_criteria->budget = isset($this->input['budget']) ? $this->input['budget'] : "";
					$rfp_search_criteria->dma_area = isset($this->input['area']) ? $this->input['area'] : "";
					$rfp_search_criteria->dma_dates = isset($this->input['date_ranges']) ? $this->input['date_ranges'] : "";
					$rfp_search_criteria->email = isset($this->input['user_email']) ? $this->input['user_email'] : "";
					$rfp_search_criteria->instructions = isset($this->input['instructions']) ? $this->input['instructions'] : "";
					$rfp_search_criteria->due_date = isset($this->input['due_date']) ? $this->input['due_date'] : "";
					$rfp_search_criteria->save();
					$products_not_found = " and products not found.";
					//return response()->json(["status" => "0", "message" => "Failed to save campaign, No products available in the selected criteria"]);
				}
			} else {
				$rfp_search_criteria = new RFPSearchCriteria;
				$rfp_search_criteria->id = uniqid();
				$rfp_search_criteria->campaign_id = $campaign_obj->id;
				$rfp_search_criteria->demo = isset($this->input['demo']) ? $this->input['demo'] : "";
				$rfp_search_criteria->product_type = isset($this->input['producttype']) ? $this->input['producttype'] : "";
				$rfp_search_criteria->budget = isset($this->input['budget']) ? $this->input['budget'] : "";
				$rfp_search_criteria->dma_area = isset($this->input['area']) ? $this->input['area'] : "";
				$rfp_search_criteria->dma_dates = isset($this->input['date_ranges']) ? $this->input['date_ranges'] : "";
				$rfp_search_criteria->email = isset($this->input['user_email']) ? $this->input['user_email'] : "";
				$rfp_search_criteria->instructions = isset($this->input['instructions']) ? $this->input['instructions'] : "";
				$rfp_search_criteria->due_date = isset($this->input['due_date']) ? $this->input['due_date'] : "";
				$rfp_search_criteria->save();
				$products_not_found = " and products not found.";
				//return response()->json(["status" => "0", "message" => "Failed to save campaign, No products available in the selected criteria"]);
			}		
		}
				
		/*campaign-report data*/ 
		
		$campaign = Campaign::where('id', '=', $campaign_id)->first();
		
		$campaign_report = [
			'campaign' => $campaign
		];		
		//Notification to Admin 
		event(new RFPRequestedEvent([
		  'type' => Notification::$NOTIFICATION_TYPE['rfp-requested'],
		  'from_id' => null,
		  'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
		  'to_id' => null,
		  'to_client' => null,
		  'desc' => "RFP Request",
		  'message' => "New RFP Requested",
		  'data' => "New RFP Requested"
		]));
		$notification_obj = new Notification;
		$notification_obj->id = uniqid();
		$notification_obj->type = "rfp_request";
		$notification_obj->from_id =  null;
		$notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['bbi'];
		$notification_obj->to_id = null;
		$notification_obj->to_client = null;
		$notification_obj->desc = "New RFP Requested";
		$notification_obj->message = "New RFP Requested";
		$notification_obj->status = 0;
		$notification_obj->save();
		
		

		/*campaign-report data*/
		$pdf = PDF::loadView('pdf.RFP_campaign_pdf',$campaign_report);
		$mail_tmpl_params = [
			'sender_email' => config('app.bbi_email'),
			'receiver_name' => '',
			'mail_message' => "You have rquested an RFP and your campaign name is <b>'" . $campaign_obj->name . "'</b> <br> To access this RFP, Please register into AMP application from Home page."
		];     
		$mail_data = [
			'email_to' => $this->input['user_email']
		];
		Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data) {
			$message->to($mail_data['email_to'])->subject('RFP-Advertising Marketplace!');
		});
		//Mail
		
		if ($success) {
			return response()->json(["status" => "1", "message" => "campaign saved successfully".$products_not_found , "campaign_id"=>$campaign_id]);
		} else {
			return response()->json(["status" => "0", "message" => "campaign saved successfully but product addition failed."]);
		}
	}
    
    /* RFP without login*/ 

      public function getRFPRecords(){
        //$products = Campaign::orderBy('created_at', 'desc')->get();
        /*$products = Campaign::where([
            ['status', '=', 1300],
            //['type', '=', Campaign::$CAMPAIGN_USER_TYPE['user']]
        ])->orderBy("created_at", 'desc')->get();
        return response()->json($products);*/
        //user_type":"owner
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
		$user_owner = $user_mongo['user_type'];

		/*if($user_mongo['user_type'] == "owner") {
			$campaign_rfp = Campaign::where([
            ['status', '=', 1300],
        ])->orderBy("created_at", 'desc')->get();
		}*/
        $user = User::where('id', '=', $user_mongo['user_id'])->first();
        $campaign_rfp = Campaign::where([
            ['status', '=', 1300],
            //['type', '=', Campaign::$CAMPAIGN_USER_TYPE['user']]
        ])->orderBy("created_at", 'desc')->get();
        //echo '<pre>';print_r($campaign_rfp);exit();
        $processed_campaign_suggestion_ids = CampaignSuggestionRequest::where('processed', '=', true)->pluck('campaign_id')->toArray();
        $user_campaigns_rfp = Campaign::raw(function($collection) use ($processed_campaign_suggestion_ids) {
                    return $collection->find([
                                '$and' => [
                                    //['created_by' => $user_mongo['id']],
                                    [
                                        'format_type' => [
                                            '$in' => [null, Format::$FORMAT_TYPE['ooh']]
                                        ]
                                    ],
                                    [
                                        '$or' => [
                                            [
                                                '$and' => [
                                                    ['from_suggestion' => true],
                                                    [
                                                        'id' => [
                                                            '$in' => $processed_campaign_suggestion_ids
                                                        ]
                                                    ]
                                                ]
                                            ],
                                            [
                                                'from_suggestion' => [
                                                    '$in' => [null, false]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                                    ], [
                                'sort' => [
                                    'updated_at' => -1
                                ]
                    ]);
                });
				$user_campaigns = array();
				if(isset($user_campaigns_rfp)){
				foreach($user_campaigns_rfp as $key => $val){
					if(isset($val->user_email)){
						$user_campaigns[] = $val;
					}
				} 
				}
        foreach ($user_campaigns as $user_campaign) {
            $act_budget = ProductBooking::raw(function($collection) use ($user_campaign) {
                        return $collection->aggregate(
                                        [
                                            [
                                                '$match' =>
                                                [
                                                    "campaign_id" => $user_campaign->id
                                                    //"campaign_rfp" => ['status', '=', 1300],
                                                ]
                                            ],
                                            [
                                                '$group' =>
                                                [
                                                    '_id' => '$campaign_id',
                                                    'total_price' => [
                                                        '$sum' => '$price'
                                                    ],
                                                    'count' => [
                                                        '$sum' => 1
                                                    ]
                                                ]
                                            ]
                                        ]
                        );
                    });

            $total_paid = CampaignPayment::where('campaign_id', '=', $user_campaign->id)->sum('amount');
            /*if(isset($user->client->client_type->type) && $user->client->client_type->type == "owner"){
                $count = ProductBooking::where('campaign_id', '=', $user_campaign->id)->where('product_owner', '=', $user_mongo['client_mongo_id'])->count();
            }else{
                $count = ProductBooking::where('campaign_id', '=', $user_campaign->id)->count();
            }*/
            
			$count = ProductBooking::where('campaign_id', '=', $user_campaign->id)->count();
			
            $product_start_date = ProductBooking::where('campaign_id', '=', $user_campaign->id)->select("booked_from","booked_to")->orderBy('booked_from', 'asc')->first();
            if (!empty($product_start_date)) {
                $user_campaign->start_date = $product_start_date->booked_from;
                $user_campaign->end_date = $product_start_date->booked_to;
            }
            
            if (count($act_budget) > 0) {
                //$user_campaign->act_budget = $act_budget[0]->total_price;  
                if($act_budget[0]->total_price == 0){
                    //$camp_price = ProductBooking::where('campaign_id', '=', $user_campaign->id)->first();
                    $camp_price = ProductBooking::where('campaign_id', '=', $user_campaign->id)->get();
                    
                    if(count($camp_price)>0){
                        $price = 0;
                        foreach($camp_price as $camp_price){
                            $price+= $camp_price->price;
                            //echo "hhh<pre>";print_r($price);
                        }
                    }
                    
                    //echo "<pre>"; print_r($camp_price);exit;
                    //$user_campaign->act_budget = $camp_price->price;
                    $user_campaign->act_budget = $price;
                }else{
                    $user_campaign->act_budget = $act_budget[0]->total_price;
                }
            }
            $user_campaign->product_count = $count;
			//echo '<pre>';print_r($user_campaign->product_count);exit;
            $user_campaign->paid = $total_paid;
			
			$filtered_user_campaigns = [];
			if(isset($user->client->client_type->type) && $user->client->client_type->type == "owner"){
				$filtered_user_campaigns = array_filter($user_campaigns, function ($user_campaign) {
					return $user_campaign->product_count === 0;
				});
			}
			else {
				$filtered_user_campaigns = $user_campaigns;
			}
        }
        return response()->json($filtered_user_campaigns);
        
    }
    
    
    // RFP before login User Campaign 
    
    public function getRFPCampaignDetails($campaign_id) {
        
        $user_mongo_jwt = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo_jwt['user_id'])->first();
        $shortlisted_products_arr = [];
        $shortlistedsum = 0;
		$shortlistedsumcpm = 0;
        $offershortlistedsum = 0;
		$offershortlistedsumcpm = 0;
        $cpmsum = 0;
        $negotiatedsum = 0;
        $offercpmsum = 0;
        $impressionSum = 0;
        $impressionSum4 = 0;
        $newofferStripepercentamtSum = 0;
        $newprocessingfeeamtSum = 0;
		$tax_percentage_booking = 0;
		$tax_percentage_amount = 0;
		$tax_percentage_amount_tot = 0;
		$tax_percentage_booking_tot = 0;
		
        // $client = $user->client; 
        if (!isset($user->client) || empty($user->client)) {
            //echo 'client';//exit;
            $campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        //['created_by', '=', $user_mongo_jwt['id']],
                    ])->first();
                    //echo 'client';print_r($campaign);exit;
        } else if ($user->client->client_type->type == "bbi") {
            //echo 'bbi';exit;
            $campaign = Campaign::where('id', '=', $campaign_id)->first();
        } else if ($user->client->client_type->type == "owner") {
            //echo 'owner';exit;
            $campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        //['created_by', '=', $user_mongo_jwt['id']],
                    ])->first();
        } else {
            //echo 'no campaign';exit;
            return response()->json(['status' => 0, 'message' => 'Campaign not found']);
        }
        /*$user_mongo = UserMongo::where('id', '=', $campaign->created_by)->first();
        $campaign->first_name = $user_mongo->first_name;
        $campaign->last_name = $user_mongo->last_name;
        $campaign->email = $user_mongo->email;
        $campaign->phone = $user_mongo->phone;*/
        $campaign_products = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
        //echo "<pre>aa"; print_r($campaign_products);exit;
        $products_arr = [];
        $cancellationArray=[];
        $getcampaigntot = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
		$campaign->total_paid = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('amount');
        //echo "<pre>aa"; print_r($getcampaigntot);exit;
        $camptot = 0;
        if (isset($getcampaigntot) && count($getcampaigntot) > 0) {
            foreach ($getcampaigntot as $getcampaigntot) {
				$tax_percentage_booking_tot = 0;
				$tax_percentage_amount_tot = 0;
                $getcampaigntotproduct =Product::where('id', '=', $getcampaigntot->product_id)->first();
                $bookedfrom[] = strtotime($getcampaigntot->booked_from);
                $bookedto[] = strtotime($getcampaigntot->booked_to);

                $getproductDetails =Product::where('id', '=', $getcampaigntot->product_id)->first();
                $diff=date_diff(date_create($getcampaigntot->booked_from),date_create($getcampaigntot->booked_to));
                //$diff=date_diff(($booked_from_date),($booked_to_date));
                $daysCount = $diff->format("%a");//exit;
                $daysCountCPM = $daysCount + 1;
				
				$perdayprice = $getproductDetails->default_price/28;

                if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
                    //echo 'fix';exit;
                    $price = $getcampaigntot->price*$getcampaigntot->quantity;
                    //$priceperday = $price;
                    //$priceperselectedDates = $priceperday; 
                    //$camptot += $priceperselectedDates;
                    if($campaign->total_paid != 0){
						if(isset($getcampaigntot->tax_percentage)){
							$tax_percentage_booking_tot = $getcampaigntot->tax_percentage;
						}else{
							$tax_percentage_booking_tot = 0;
						}
					}else{
						$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
					}
					if($tax_percentage_booking_tot != 0){
						$tax_percentage_amount_tot = ($price * ($tax_percentage_booking_tot))/100;
					}
					$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
					$camptot += $price+$tax_percentage_amount_tot;
                }else{
                    //echo 'else';exit;
                    //$price = $getcampaigntot->price; 
                    //$price = $getproductDetails->default_price;
                    //$priceperday = $price/28;//exit;
                    //$priceperselectedDates = $priceperday * $daysCountCPM;
                    //echo '--daysCountCPM---'.$daysCountCPM;
                    //$camptot += $priceperselectedDates;
                    //$camptot += $price;
					$priceperselectedDates = $getcampaigntot->price;
					
					if($campaign->total_paid != 0){
						if(isset($getcampaigntot->tax_percentage)){
							$tax_percentage_booking_tot = $getcampaigntot->tax_percentage;
						}else{
							$tax_percentage_booking_tot = 0;
						}
					}else{
						$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
					}
					if($tax_percentage_booking_tot != 0){
						$tax_percentage_amount_tot = ($priceperselectedDates * ($tax_percentage_booking_tot))/100;
					}
					$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
					$camptot += ($priceperselectedDates*$getcampaigntot->quantity)+($tax_percentage_amount_tot*$getcampaigntot->quantity);
                }
                //echo '---camptot123---'.$camptot += $getcampaigntot->price;
            }
        }
//echo '--camptot--'.$camptot;
//exit;
        if (isset($campaign_products) && count($campaign_products) > 0) {
			
			
             
            $f = $campaign_products;
            //echo "<pre>";print_r($f);exit;
             $campaigntotal = 0;
             foreach($f as $f){
                 $campaigntotal+= $f->price;
             }
			$campaign->rfp_search_criteria_preview = array();
			$campaign->area_rfp_details = array();
            foreach ($campaign_products as $campaign_product) {
				$campaign_product->tax_percentage_amount_shortlist = 0;
				$campaign_product->tax_percentage_amount = 0;
				$tax_percentage_booking = 0;
				$tax_percentage_amount = 0;
                
                $product =Product::where('id', '=', $campaign_product->product_id)->first();
                $booked_from[] = strtotime($campaign_product->booked_from);
                $booked_to[] = strtotime($campaign_product->booked_to);
                if($product->cancelation =='Yes')
                {
                    $booked_from_date = date('m-d-Y',strtotime($campaign_product->booked_from));
                    
                    $current_date = date('m-d-Y');
                    
                    $getproductDetails =Product::where('id', '=', $campaign_product->product_id)->first();
                    $diff=date_diff(date_create($current_date),date_create($booked_from_date));
                    $daysCount = $diff->format("%a");
                    $daysCountCPM = $date_diff->days + 1;
                    
                    $date_diff=0;
                    
                    if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
                        /*$offerDetails = MakeOffer::where([
                            ['campaign_id', '=', $campaign_product->campaign_id],
                            ['status', '=', 40],
                        ])->get();*/
                        //echo "<pre>offerDetails"; print_r($offerDetails);exit;
                        //$price = $getproductDetails->default_price;
                        $price = $campaign_product->price;
                        $priceperday = $price;
                        $priceperselectedDates = $priceperday;
                
                        //$shortlistedsum+= $sup->price; 
                        $shortlistedsum+= $priceperselectedDates;
                        $campaign_product->price = $priceperselectedDates;
                        $cpmsum+= $campaign_product->cpm;
                        $impressions = $campaign_product->secondImpression;
                        $impressionsperday = (int)($impressions);
                        $impressionsperselectedDates = $impressionsperday;
                        
                        if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                            $impressionsperselectedDates = $impressionsperselectedDates;
                        }else{
                            $impressionsperselectedDates = 1;
                        }
    
                        //$impressionSum+= $product_details->secondImpression;
                        $impressionSum+= $impressionsperselectedDates;
                        $campaign_product->secondImpression = round($impressionsperselectedDates, 2);
                        $cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                        $campaign_product->cpm = $cpmcal;
                    }else{
                        /*$offerDetails = MakeOffer::where([
                            ['campaign_id', '=', $campaign_product->campaign_id],
                            ['status', '=', 40],
                        ])->get();
                        echo "<pre>offerDetails"; print_r($offerDetails);exit;*/
                        $price = $getproductDetails->default_price;
                        $priceperday = $price/28;
                        $priceperselectedDates = $priceperday * $daysCountCPM;
                
                        //$shortlistedsum+= $sup->price; 
                        $shortlistedsum+= $priceperselectedDates;
                        $campaign_product->price = $priceperselectedDates;
                        $cpmsum+= $campaign_product->cpm;
                        $impressions = $campaign_product->secondImpression;
                        $impressionsperday = (int)($impressions/7);
                        $impressionsperselectedDates = $impressionsperday * $daysCountCPM;
                        
                        if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                            $impressionsperselectedDates = $impressionsperselectedDates;
                        }else{
                            $impressionsperselectedDates = 1;
                        }
                        //$impressionSum+= $product_details->secondImpression;
                        $impressionSum+= $impressionsperselectedDates;
                        $campaign_product->secondImpression = round($impressionsperselectedDates, 2);
                        $cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                        $campaign_product->cpm = $cpmcal;
                    }
                    
                    $cancellationfeeArray = array('0-30'=>35,'30-60'=>20,'61-120'=>10,'121-0'=>0);
                    $cancellationfee =0;
                    if($daysCount >0){
                    foreach($cancellationfeeArray as $key=>$val){
                        $daysrange = explode("-",$key);
                         $mindays = $daysrange[0];
                         $maxdays = $daysrange[1];
                        if($mindays <= $daysCount && $daysCount <= $maxdays && $maxdays!=0 ){
                             $cancellationfee = (($campaign_product->price)*($val/100));
                             
                        }else if($mindays <= $daysCount && $maxdays ==0 ){
                             $cancellationfee = (($campaign_product->price)*($val/100));
                        }
                        
                        $cancellationArray = array('cancellation_charge'=>$cancellationfee,'cancel_remaingdays'=>$daysCount);
                       }
                    }
                }else{
                    //$booked_from_date = date('m-d-Y',strtotime($campaign_product->booked_from));
                    //$booked_to_date = date('m-d-Y',strtotime($campaign_product->booked_to));
                    //dd($booked_from);
                    //echo $current_date = date('m-d-Y');
                    ///echo ($booked_from_date);
                    //echo ($booked_to_date);
                    $getproductDetails =Product::where('id', '=', $campaign_product->product_id)->first();
                    $diff=date_diff(date_create($campaign_product->booked_from),date_create($campaign_product->booked_to));
                    //$diff=date_diff(($booked_from_date),($booked_to_date));
                    $daysCount = $diff->format("%a");//exit;
                    $daysCountCPM = $daysCount + 1;
					
					$perdayprice = $getproductDetails->default_price/28;
					
                    //$daysCountCPM = $daysCount;
                    //echo "<pre>daysCount"; print_r($daysCount);exit;
                    //echo "<pre>getproductDetails"; print_r($getproductDetails);//exit;
                    //echo "<pre>product"; print_r($campaign_product);exit;
                    if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
                        //echo 'dsdsds111';exit;
                        $offerDetails = MakeOffer::where([
                            ['campaign_id', '=', $campaign_product->campaign_id],
                            ['status', '=', 20],
                        ])->get();

                        if(isset($offerDetails) && count($offerDetails)==1){
                            //echo 'offer exists';exit;
                                foreach($offerDetails as $offerDetails){
                                            $offerprice = $offerDetails->price;
                                            $stripe_percent=$getproductDetails->stripe_percent;

                                            //$price = $getproductDetails->default_price;
                                            $price = $campaign_product->price;

                                            //$price = $campaign_product->price;
                                            $priceperday = $price;
                                            $priceperselectedDates = $priceperday;
                                            $newpricepercentage = ($priceperselectedDates/$camptot) * 100;

                                            //$newofferprice = ($offerprice * ($newpricepercentage))/100;
											if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
												$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
												$newofferprice = $newofferprice_wq*$campaign_product->quantity;
											}else{
												$newofferprice = ($offerprice * ($newpricepercentage))/100;
											}
                                            //$offerpriceperday = $newofferprice/28;//exit;
                                            //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                                            $offerpriceperselectedDates = $newofferprice;
                                            $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
                                            $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                                            $campaign_product->stripe_percent = $stripe_percent;

                                            $negotiatedprice = $getproductDetails->negotiatedCost;
                                            $negotiatedpriceperday = $negotiatedprice;
                                            $negotiatedpriceperselectedDates = $negotiatedpriceperday;

                                }
                            }else{
                                 //$offerprice = $getproductDetails->default_price;
                                 //echo 'no offer exists';exit;
                                 $offerprice = $campaign_product->price;
                                 //$offerprice = $getproductDetails->default_price;
                                 $stripe_percent=$getproductDetails->stripe_percent;
                                 //$price = $getproductDetails->default_price;
                                 $price = $campaign_product->price;
                                 //$price = $campaign_product->price;
                                 $priceperday = $price;//exit;
                                 $priceperselectedDates = $priceperday;
                                 $newpricepercentage = ($priceperselectedDates/$camptot) * 100;
                
                                if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
									$newofferprice = $offerprice*$campaign_product->quantity;
								}else{
									$newofferprice = $offerprice ;
								}
                            //$offerpriceperday = $newofferprice/28;//exit;
                            //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                                $offerpriceperselectedDates = $newofferprice;
                                $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
                                $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                                $campaign_product->stripe_percent = $stripe_percent;
                                
                                $negotiatedprice = $getproductDetails->negotiatedCost;
                                $negotiatedpriceperday = $negotiatedprice;
                                $negotiatedpriceperselectedDates = $negotiatedpriceperday;
                            }
                            
                            if($campaign->total_paid != 0){
								if(isset($campaign_product->tax_percentage)){
									$tax_percentage_booking = $campaign_product->tax_percentage;
								}else{
									$tax_percentage_booking = 0;
								}
							}else{
								$tax_percentage_booking = $getproductDetails->tax_percentage;
							}
							if($tax_percentage_booking != 0){
								$campaign_product->tax_percentage_amount_shortlist = ($priceperselectedDates * ($tax_percentage_booking))/100;
								$tax_percentage_amount = ($offerpriceperselectedDates * ($tax_percentage_booking))/100;
							}
                            $campaign_product->tax_percentage_amount = round($tax_percentage_amount,2);
                                                
                            $shortlistedsum+= ($priceperselectedDates+$campaign_product->tax_percentage_amount_shortlist)*$campaign_product->quantity;
							$shortlistedsumcpm += $priceperselectedDates*$campaign_product->quantity;
                            $campaign_product->price = $priceperselectedDates;
            
                            $negotiatedsum+= $negotiatedpriceperselectedDates;
							$offershortlistedsum+= $offerpriceperselectedDates+$campaign_product->tax_percentage_amount;
							$campaign_product->offerprice = $offerpriceperselectedDates+$campaign_product->tax_percentage_amount;
                            $offershortlistedsumcpm += $offerpriceperselectedDates;
                            $cpmsum+= $getproductDetails->cpm;
                            $impressions = $getproductDetails->secondImpression;
                            $impressionsperday = (float)($impressions/7);
                            $impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
                            
                            if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                                $impressionsperselectedDates = $impressionsperselectedDates;
                            }else{
                                $impressionsperselectedDates = 1;
                            }
                            //$impressionSum+= $product_details->secondImpression; 
                            $impressionSum+= $impressionsperselectedDates*$campaign_product->quantity;
                            $campaign_product->secondImpression = round($impressionsperselectedDates, 2);
                            //$cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                            //$offercpmcal = ($offershortlistedsum/$impressionSum) * 1000;
                            $cpmcal = ($priceperselectedDates/$impressionsperselectedDates) * 1000;
                            $offercpmcal = ($offerpriceperselectedDates/$impressionsperselectedDates) * 1000;
                            $campaign_product->cpmperselectedDates = $cpmcal;
                            $campaign_product->offercpmperselectedDates = $offercpmcal;
                            $campaign_product->cpm = $cpmcal;
                            $campaign_product->offercpm = $offercpmcal;
                            $campaign_product->impressionsperselectedDates = $impressionsperselectedDates;
                            $campaign_product->priceperselectedDates = $priceperselectedDates;
                            $campaign_product->negotiatedpriceperselectedDates = $negotiatedpriceperselectedDates;
                            $campaign_product->offerpriceperselectedDates = $offerpriceperselectedDates+$campaign_product->tax_percentage_amount;
            
                            $campaign_product->new_stripe_percent_amount = $newofferStripepercentamt;
                            $campaign_product->newprocessingfeeamt = $newprocessingfeeamt;
            
                            $newofferStripepercentamtSum += $newofferStripepercentamt;
                            $newprocessingfeeamtSum += $newprocessingfeeamt;

                    }else{
                        //echo 'dsdsds';exit;
                        $offerDetails = MakeOffer::where([
                            ['campaign_id', '=', $campaign_product->campaign_id],
                            ['status', '=', 20],
                        ])->get();
                        
                        
                        if(isset($offerDetails) && count($offerDetails)==1){
                                                foreach($offerDetails as $offerDetails){
                                                     $offerprice = $offerDetails->price;
                                                     $stripe_percent=$getproductDetails->stripe_percent;
                                                     
                                                $price = $getproductDetails->default_price;
                                                
                                                //$price = $campaign_product->price;
                                                //$priceperday = $price/28;//exit;
                                                //echo '---camptot--'.$camptot;
                                                //$priceperselectedDates = $priceperday * $daysCountCPM;
												
												///variable_offer///
												$priceperday = $campaign_product->price;
												$priceperselectedDates = $priceperday;
												
												
                                                $newpricepercentage = ($priceperselectedDates/$camptot) * 100;
                        
                                                //$newofferprice = ($offerprice * ($newpricepercentage))/100;//exit;
												if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
													$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
													$newofferprice = $newofferprice_wq*$campaign_product->quantity;
												}else{
													$newofferprice = ($offerprice * ($newpricepercentage))/100;
												}
                                                //$offerpriceperday = $newofferprice/28;//exit;
                                                //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                                                $offerpriceperselectedDates = $newofferprice;
                                                $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
                                                $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                                                $campaign_product->stripe_percent = $stripe_percent;

                                                $negotiatedprice = $getproductDetails->negotiatedCost;
                                                $negotiatedpriceperday = $negotiatedprice/28;
                                                $negotiatedpriceperselectedDates = $negotiatedpriceperday * $daysCountCPM;
                                                   }
                                                }else{
                                                     //$offerprice = $getproductDetails->default_price;
                                                $offerprice = $campaign_product->price;
                                                $stripe_percent=$getproductDetails->stripe_percent;
                                                $price = $campaign_product->price;
                                                //$price = $campaign_product->price;
                                                $priceperday = $price;//exit;
                                                $priceperselectedDates = $priceperday;
                                                $newpricepercentage = ($priceperselectedDates/$camptot) * 100;
                        
                                                if(isset($campaign_product->quantity) && $campaign_product->quantity != '' && $campaign_product->quantity != 0){
													$newofferprice = $offerprice*$campaign_product->quantity;
												}else{
													$newofferprice = $offerprice;
												}
                                                //$offerpriceperday = $newofferprice/28;//exit;
                                                //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                                                $offerpriceperselectedDates = $newofferprice;
                                                $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
                                                $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                                                $campaign_product->stripe_percent = $stripe_percent;

                                                $negotiatedprice = $getproductDetails->negotiatedCost;
                                                $negotiatedpriceperday = $negotiatedprice/28;
                                                $negotiatedpriceperselectedDates = $negotiatedpriceperday * $daysCountCPM;
                                                }
                                                
                                                if($campaign->total_paid != 0){
													if(isset($campaign_product->tax_percentage)){
														$tax_percentage_booking = $campaign_product->tax_percentage;
													}else{
														$tax_percentage_booking = 0;
													}
												}else{
													$tax_percentage_booking = $getproductDetails->tax_percentage;
												}
												if($tax_percentage_booking != 0){
													$campaign_product->tax_percentage_amount_shortlist = ($priceperselectedDates * ($tax_percentage_booking))/100;
													$tax_percentage_amount = ($offerpriceperselectedDates * ($tax_percentage_booking))/100;
												}
												$campaign_product->tax_percentage_amount = round($tax_percentage_amount,2);
												
												$shortlistedsum+= ($priceperselectedDates+$campaign_product->tax_percentage_amount_shortlist)*$campaign_product->quantity;
                                                $shortlistedsumcpm += $priceperselectedDates*$campaign_product->quantity;
                                                $negotiatedsum+= $negotiatedpriceperselectedDates;
                                                $campaign_product->price = $priceperselectedDates;
												
												$offershortlistedsum+= $offerpriceperselectedDates+$campaign_product->tax_percentage_amount;
												$campaign_product->offerprice = $offerpriceperselectedDates+$campaign_product->tax_percentage_amount;
                                                $offershortlistedsumcpm += $offerpriceperselectedDates;
                                                $cpmsum+= $getproductDetails->cpm;
                                                $impressions = $getproductDetails->secondImpression;
                                                $impressionsperday = (float)($impressions/7);
                                                $impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
                                                
                                                if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                                                    $impressionsperselectedDates = $impressionsperselectedDates;
                                                }else{
                                                    $impressionsperselectedDates = 1;
                                                }
                                                //$impressionSum+= $product_details->secondImpression; 
                                                $impressionSum+= $impressionsperselectedDates*$campaign_product->quantity;
                                                $campaign_product->secondImpression = round($impressionsperselectedDates, 2);
                                                //$cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                                                //$offercpmcal = ($offershortlistedsum/$impressionSum) * 1000;
                                                $cpmcal = ($priceperselectedDates/$impressionsperselectedDates) * 1000;
                                                $offercpmcal = ($offerpriceperselectedDates/$impressionsperselectedDates) * 1000;
                                                $campaign_product->cpmperselectedDates = $cpmcal;
                                                $campaign_product->offercpmperselectedDates = $offercpmcal;
                                                $campaign_product->cpm = $cpmcal;
                                                $campaign_product->offercpm = $offercpmcal;
                                                $campaign_product->impressionsperselectedDates = $impressionsperselectedDates;
                                                $campaign_product->priceperselectedDates = $priceperselectedDates;
                                                $campaign_product->negotiatedpriceperselectedDates = $negotiatedpriceperselectedDates;
                                                $campaign_product->offerpriceperselectedDates = $offerpriceperselectedDates+$campaign_product->tax_percentage_amount;
                        
                                                $campaign_product->new_stripe_percent_amount = $newofferStripepercentamt;
                                                $campaign_product->newprocessingfeeamt = $newprocessingfeeamt;
                        
                                                $newofferStripepercentamtSum += $newofferStripepercentamt;
                                                $newprocessingfeeamtSum += $newprocessingfeeamt;
//exit;
                       
                    }
                }
         
                array_push($products_arr, array_merge(Product::where('id', '=', $campaign_product->product_id)->first()->toArray(), $campaign_product->toArray(),$cancellationArray));
            }
            $campaign->products = $products_arr;
            $campaign->actbudg = $products_arr;

            $quote_change = CampaignQuoteChange::select('remark', 'type')->where('campaign_id', '=', $campaign_id)->get();
            //$quote_change = CampaignQuoteChange::select('remark','type')->where('campaign_id', '=', $campaign_id)->orderBy('created_at', 'desc')->get();
            if (!empty($quote_change)) {
                $campaign->quote_change = $quote_change;
            }

            // get campaign actual budget
            // $data = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
            // dd($data);
            $act_budget = ProductBooking::raw(function($collection) use ($campaign_id) {
                        return $collection->aggregate(
                                        [
                                            [
                                                '$match' =>
                                                [
                                                    "campaign_id" => $campaign_id
                                                ]
                                            ],
                                            [
                                                '$group' =>
                                                [
                                                    '_id' => '$campaign_id',
                                                    'total_price' => [
                                                        '$sum' => '$admin_price'
                                                    ]
                                                ]
                                            ]
                                        ]
                        );
                    });
            
        
                $res = array_sum(array_map(function($item) { 
					if(isset($item['quantity']) && $item['quantity'] != '' && $item['quantity'] != 0){
						return ($item['price']*$item['quantity'])+$item['tax_percentage_amount']; 
					}else{
						return $item['price']+$item['tax_percentage_amount']; 
					}
                }, $campaign->actbudg));
            //echo "<pre>act_budget";print_r($res);exit;
            $campaign->act_budget = $res;
            /*$campaign->act_budget = $act_budget[0]->total_price;
            
            if($campaign->act_budget == '0'){
                //dd(123)
                  $campaign->act_budget  = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('owner_price');
            }
            //dd($campaign->act_budget);
            if($campaign->act_budget == '0'){
                  $campaign->act_budget  = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('price');
            }*/
            
            /*$gststatus = isset($campaign->gststatus)?$campaign->gststatus:0;
                if($gststatus ==1){
                    $campaign->totalamount = $campaign->act_budget+round(($campaign->act_budget*(0.18)),2);
                }
                else{*/
                     $campaign->totalamount = $campaign->act_budget;
                //}
                 
                 $campaign->refunded_amount = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('refunded_amount');
                 
                 $campaign->bal_amount_available_with_amp = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('bal_amount_available_with_amp');
                 
                 //$campaign->payments = CampaignPayment::where('campaign_id', '=', $campaign->id)->get();
                 //echo "<pre>"; print_r($campaign->refunded_amount);exit;
                // echo 'ddddddddddd';exit;
                 $impressionSum4 = round($impressionSum, 2); /* As discussed with Richard on July 14) */
                 if($impressionSum4>0){
                    $cpmval = ($shortlistedsumcpm/$impressionSum4) * 1000;
                    $offercpmval = ($offershortlistedsumcpm/$impressionSum4) * 1000;
                 }else{
                     $cpmval = 0;
                     $offercpmval = 0;
                 }
         
                 $campaign->shortlistedsum = $shortlistedsum;
                 $campaign->negotiatedsum = $negotiatedsum;
                 $campaign->cpmval = $cpmval;

                 $campaign->offershortlistedsum = $offershortlistedsum;
                 $campaign->offercpmval = $offercpmval;

                 $campaign->impressionSum = $impressionSum4;

                 $campaign->newofferStripepercentamtSum = $newofferStripepercentamtSum;
                 $campaign->newprocessingfeeamtSum = $newprocessingfeeamtSum;
                 $campaign->percentagevalue = ($newofferStripepercentamtSum * 100)/$offershortlistedsum;
                 $campaign->finalpurchasepayment = $newofferStripepercentamtSum + $newprocessingfeeamtSum;
        }else{
			$campaign->rfp_search_criteria_preview=[];
			$campaign->rfp_search_criteria_preview = RFPSearchCriteria::where('campaign_id', '=', $campaign->id)->first();
			if(!empty($campaign->rfp_search_criteria_preview)){
				$area_full_details = array();
				$rfp_search_criteria_preview_dma_dates = array();
				foreach($campaign->rfp_search_criteria_preview['dma_area'] as $key => $value){
					$area_full_details[] = Area::where('id', '=', $value)->first();
					if(!is_null($area_full_details[$key])){
						$area_time_zone_type = $area_full_details[$key]['area_time_zone_type'];
						if(!is_null($area_time_zone_type)){
							$explode_dates  = @explode('::',$campaign->rfp_search_criteria_preview['dma_dates'][$key]);
							$start_time = new DateTime( $explode_dates[0] );
							$end_time = new DateTime( $explode_dates[1] );
							$laTimezone = new DateTimeZone($area_time_zone_type);
							$start_time->setTimeZone( $laTimezone );
							$end_time->setTimeZone( $laTimezone );
							$start_time->format( 'Y-m-d H:i:s' );
							$end_time->format( 'Y-m-d H:i:s' );	
							$rfp_search_criteria_preview_dma_dates[] = $start_time->format( 'Y-m-d H:i:s' ).'::'.$end_time->format( 'Y-m-d H:i:s' );
						}							
					}
				}
				$campaign->area_rfp_details = $area_full_details;
				if(isset($rfp_search_criteria_preview_dma_dates)){
					$campaign->rfp_search_criteria_preview['dma_dates'] = $rfp_search_criteria_preview_dma_dates;
				}
			}
			else
			{
				$campaign->rfp_search_criteria_preview = Campaign::where('id', '=', $campaign_id)->first();
				$campaign = $campaign->rfp_search_criteria_preview;
			}
		}
		if(isset($booked_from) && !empty($booked_from)) {$campaign->startDate =  date('m-d-Y',min($booked_from));}
        if(isset($booked_to) && !empty($booked_to)) {$campaign->endDate = date('m-d-Y',max($booked_to));}
        
        return response()->json($campaign);
        
    }

    /* get Products for Price In Delete Campaign Request*/ 
    public function getProductsPriceInCampaign(Request $request){
        $user_mongo_jwt = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo_jwt['user_id'])->first();
        
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        
         
        $campaign_id = $input['campaign_id'];
        $productids = $input['product_id'];
        
        $shortlisted_products_arr = [];
        $shortlistedsum = 0;
        $offershortlistedsum = 0;
        $cpmsum = 0;
        $offercpmsum = 0;
        $impressionSum = 0;
        $impressionSum4 = 0;
        $newofferStripepercentamtSum = 0;
        $newprocessingfeeamtSum = 0;
        $delofferpriceStripepercentamtSum =0;
        $delofferpriceStripepercentamt = 0;
        $delofferpriceprocessingfeeamtSum = 0;
        $delofferpriceprocessingfeeamt = 0;
        $finaldelpurchaseamt = 0;
        $deloffprice = 0;
        $deloffpriceSum = 0;
        // $client = $user->client; 
        if (!isset($user->client) || empty($user->client)) {
            //echo 'client';//exit;
            $campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        ['created_by', '=', $user_mongo_jwt['id']],
                    ])->first();
                    //echo 'client';print_r($campaign);exit;
        } else if ($user->client->client_type->type == "bbi") {
            //echo 'bbi';exit;
            $campaign = Campaign::where('id', '=', $campaign_id)->first();
        } else if ($user->client->client_type->type == "owner") {
            //echo 'owner';exit;
            $campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        ['created_by', '=', $user_mongo_jwt['id']],
                    ])->first();
        } else {
            //echo 'no campaign';exit;
            return response()->json(['status' => 0, 'message' => 'Campaign not found']);
        }
        //echo "<pre>campaign"; print_r($campaign);exit;
        $user_mongo = UserMongo::where('id', '=', $campaign->created_by)->first();
        $campaign->first_name = $user_mongo->first_name;
        $campaign->last_name = $user_mongo->last_name;
        $campaign->email = $user_mongo->email;
        $campaign->phone = $user_mongo->phone;
        //echo "<pre>campaign info"; print_r($campaign);exit;
        //$campaign_products = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
        $campaign_products = ProductBooking::where([
            ['campaign_id', '=', $campaign_id],
        ])
        ->whereIn('product_id', $productids)->get(); 
        //echo "<pre>campaign_products-count"; print_r(count($campaign_products));exit;
        $products_arr = [];
        $cancellationArray=[];
        //$getcampaigntot = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
        $getcampaigntot = ProductBooking::where([
            ['campaign_id', '=', $campaign_id],
        ])
        ->whereIn('product_id', $productids)->get(); 
        //echo "<pre>getcampaigntot"; print_r($getcampaigntot);exit;
        $camptot = 0;
        if (isset($getcampaigntot) && count($getcampaigntot) > 0) {
            foreach ($getcampaigntot as $getcampaigntot) {
                $getcampaigntotproduct =Product::where('id', '=', $getcampaigntot->product_id)->first();
                $bookedfrom[] = strtotime($getcampaigntot->booked_from);
                $bookedto[] = strtotime($getcampaigntot->booked_to);

                $getproductDetails =Product::where('id', '=', $getcampaigntot->product_id)->first();
                $diff=date_diff(date_create($getcampaigntot->booked_from),date_create($getcampaigntot->booked_to));
                //$diff=date_diff(($booked_from_date),($booked_to_date));
                $daysCount = $diff->format("%a");//exit;
                $daysCountCPM = $daysCount + 1;

                if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
                    //echo 'fix';exit;
                    $price = $getcampaigntot->price;
                    //$priceperday = $price;
                    //$priceperselectedDates = $priceperday;
                    //$camptot += $priceperselectedDates;
                    $camptot += $price;
                }else{
                    //echo 'else';exit;
                    //$price = $getcampaigntot->price;
                    $price = $getproductDetails->default_price;
                    $priceperday = $price/28;//exit;
                    $priceperselectedDates = $priceperday * $daysCountCPM;
                    //echo '--daysCountCPM---'.$daysCountCPM;
                    $camptot += $priceperselectedDates;
                    //$camptot += $price;
                }
                //echo '---camptot123---'.$camptot += $getcampaigntot->price;
            }
        }
//echo '--camptot--'.$camptot;
//exit;
        if (isset($campaign_products) && count($campaign_products) > 0) {
             
            $f = $campaign_products;
            //echo "<pre>";print_r($f);exit;
             $campaigntotal = 0;
             foreach($f as $f){
                 $campaigntotal+= $f->price;
             }

            foreach ($campaign_products as $campaign_product) {
                
                $product =Product::where('id', '=', $campaign_product->product_id)->first();
                $booked_from[] = strtotime($campaign_product->booked_from);
                $booked_to[] = strtotime($campaign_product->booked_to);
                if($product->cancelation =='Yes')
                {
                    $booked_from_date = date('m-d-Y',strtotime($campaign_product->booked_from));
                    
                    $current_date = date('m-d-Y');
                    
                    $getproductDetails =Product::where('id', '=', $campaign_product->product_id)->first();
                    $diff=date_diff(date_create($current_date),date_create($booked_from_date));
                    $daysCount = $diff->format("%a");
                    $daysCountCPM = $date_diff->days + 1;
                    
                    $date_diff=0;
                    
                    if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
                        /*$offerDetails = MakeOffer::where([
                            ['campaign_id', '=', $campaign_product->campaign_id],
                            ['status', '=', 40],
                        ])->get();*/
                        //echo "<pre>offerDetails"; print_r($offerDetails);exit;
                        //$price = $getproductDetails->default_price;
                        $price = $campaign_product->price;
                        $priceperday = $price;
                        $priceperselectedDates = $priceperday;
                
                        //$shortlistedsum+= $sup->price; 
                        $shortlistedsum+= $priceperselectedDates;
                        $campaign_product->price = $priceperselectedDates;
                        $cpmsum+= $campaign_product->cpm;
                        $impressions = $campaign_product->secondImpression;
                        $impressionsperday = (int)($impressions);
                        $impressionsperselectedDates = $impressionsperday;
                        
                        if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                            $impressionsperselectedDates = $impressionsperselectedDates;
                        }else{
                            $impressionsperselectedDates = 1;
                        }
    
                        //$impressionSum+= $product_details->secondImpression;
                        $impressionSum+= $impressionsperselectedDates;
                        $campaign_product->secondImpression = round($impressionsperselectedDates, 2);
                        $cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                        $campaign_product->cpm = $cpmcal;
                        $campaign_product->productbookingid = $campaign_product->id;
                    }else{
                        /*$offerDetails = MakeOffer::where([
                            ['campaign_id', '=', $campaign_product->campaign_id],
                            ['status', '=', 40],
                        ])->get();
                        echo "<pre>offerDetails"; print_r($offerDetails);exit;*/
                        $price = $getproductDetails->default_price;
                        $priceperday = $price/28;
                        $priceperselectedDates = $priceperday * $daysCountCPM;
                
                        //$shortlistedsum+= $sup->price; 
                        $shortlistedsum+= $priceperselectedDates;
                        $campaign_product->price = $priceperselectedDates;
                        $cpmsum+= $campaign_product->cpm;
                        $impressions = $campaign_product->secondImpression;
                        $impressionsperday = (int)($impressions/7);
                        $impressionsperselectedDates = $impressionsperday * $daysCountCPM;
                        
                        if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                            $impressionsperselectedDates = $impressionsperselectedDates;
                        }else{
                            $impressionsperselectedDates = 1;
                        }
                        //$impressionSum+= $product_details->secondImpression;
                        $impressionSum+= $impressionsperselectedDates;
                        $campaign_product->secondImpression = round($impressionsperselectedDates, 2);
                        $cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                        $campaign_product->cpm = $cpmcal;
                        $campaign_product->productbookingid = $campaign_product->id;
                    }
                    
                    $cancellationfeeArray = array('0-30'=>35,'30-60'=>20,'61-120'=>10,'121-0'=>0);
                    $cancellationfee =0;
                    if($daysCount >0){
                    foreach($cancellationfeeArray as $key=>$val){
                        $daysrange = explode("-",$key);
                         $mindays = $daysrange[0];
                         $maxdays = $daysrange[1];
                        if($mindays <= $daysCount && $daysCount <= $maxdays && $maxdays!=0 ){
                             $cancellationfee = (($campaign_product->price)*($val/100));
                             
                        }else if($mindays <= $daysCount && $maxdays ==0 ){
                             $cancellationfee = (($campaign_product->price)*($val/100));
                        }
                        
                        $cancellationArray = array('cancellation_charge'=>$cancellationfee,'cancel_remaingdays'=>$daysCount);
                       }
                    }
                }else{
                    //$booked_from_date = date('m-d-Y',strtotime($campaign_product->booked_from));
                    //$booked_to_date = date('m-d-Y',strtotime($campaign_product->booked_to));
                    //dd($booked_from);
                    //echo $current_date = date('m-d-Y');
                    ///echo ($booked_from_date);
                    //echo ($booked_to_date);
                    $getproductDetails =Product::where('id', '=', $campaign_product->product_id)->first();
                    $diff=date_diff(date_create($campaign_product->booked_from),date_create($campaign_product->booked_to));
                    //$diff=date_diff(($booked_from_date),($booked_to_date));
                    $daysCount = $diff->format("%a");//exit;
                    $daysCountCPM = $daysCount + 1;
                    //$daysCountCPM = $daysCount;
                    //echo "<pre>daysCount"; print_r($daysCount);exit;
                    //echo "<pre>product"; print_r($campaign_product);exit;
                    if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
                        //echo 'dsdsds111';exit;
                        $offerDetails = MakeOffer::where([
                            ['campaign_id', '=', $campaign_product->campaign_id],
                            ['status', '=', 20],
                        ])->get();

                        if(isset($offerDetails) && count($offerDetails)==1){
                            //echo 'offer exists';exit;
                            foreach($offerDetails as $offerDetails){
                                 $offerprice = $offerDetails->price;
                                 $delofferprice = $offerDetails->price;
                                 $stripe_percent=$getproductDetails->stripe_percent;
                                 
                            //$price = $getproductDetails->default_price;
                            $price = $campaign_product->price;
                            
                            //$price = $campaign_product->price;
                            $priceperday = $price;
                            $priceperselectedDates = $priceperday;
                            $newpricepercentage = ($priceperselectedDates/$camptot) * 100;
            
                            $newofferprice = ($offerprice * ($newpricepercentage))/100;
                            //$offerpriceperday = $newofferprice/28;//exit;
                            //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                            $offerpriceperselectedDates = $newofferprice;
                            $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;                            
                            $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;

                            $delofferpriceStripepercentamt = ($delofferprice * ($stripe_percent))/100;
                            $campaign_product->delofferpriceStripepercentamt = $delofferpriceStripepercentamt;

                            $delofferpriceprocessingfeeamt = ((2.9) * $delofferpriceStripepercentamt)/100;
                            $campaign_product->delofferpriceprocessingfeeamt = $delofferpriceprocessingfeeamt;                                                                              
                            $campaign_product->stripe_percent = $stripe_percent;
                            
                            //$deloffprice = $delofferprice + $delofferpriceStripepercentamt + $delofferpriceprocessingfeeamt;
                            $deloffprice = $delofferpriceStripepercentamt + $delofferpriceprocessingfeeamt;
                            $campaign_product->deloffprice = $deloffprice;
                            $deloffpriceSum += $deloffprice; 
                            $campaign_product->productbookingid = $campaign_product->id;
                               }
                            }else{
                                 //$offerprice = $getproductDetails->default_price;
                                 //echo 'no offer exists';exit;
                                 $offerprice = $campaign_product->price;
                                 $delofferprice = $campaign_product->price;
                                 //$offerprice = $getproductDetails->default_price;
                                 $stripe_percent=$getproductDetails->stripe_percent;
                                 //$price = $getproductDetails->default_price;
                                 $price = $campaign_product->price;
                                 //$price = $campaign_product->price;
                                 $priceperday = $price;//exit;
                                 $priceperselectedDates = $priceperday;
                                 $newpricepercentage = ($priceperselectedDates/$camptot) * 100;
                
                                $newofferprice = $offerprice ;
                            //$offerpriceperday = $newofferprice/28;//exit;
                            //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                                $offerpriceperselectedDates = $newofferprice;
                                $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
                                $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                                $campaign_product->stripe_percent = $stripe_percent;

                                
                                $delofferpriceStripepercentamt = ($delofferprice * ($stripe_percent))/100;
                                $campaign_product->delofferpriceStripepercentamt = $delofferpriceStripepercentamt;

                                $delofferpriceprocessingfeeamt = ((2.9) * $delofferpriceStripepercentamt)/100;
                                $campaign_product->delofferpriceprocessingfeeamt = $delofferpriceprocessingfeeamt;

                                //$deloffprice = $delofferprice + $delofferpriceStripepercentamt + $delofferpriceprocessingfeeamt;
                                $deloffprice = $delofferpriceStripepercentamt + $delofferpriceprocessingfeeamt;
                                $campaign_product->deloffprice = $deloffprice;
                                $deloffpriceSum += $deloffprice;      
                                $campaign_product->productbookingid = $campaign_product->id;
                                $campaign_product->productbookingid = $campaign_product->id;

                            }
                            
                            
                                                
                            $shortlistedsum+= $priceperselectedDates;
                            $campaign_product->price = $priceperselectedDates;
            
                            $offershortlistedsum+= $offerpriceperselectedDates;
                            $campaign_product->offerprice = $offerpriceperselectedDates;
                            $cpmsum+= $getproductDetails->cpm;
                            $impressions = $getproductDetails->secondImpression;
                            $impressionsperday = (int)($impressions/7);
                            $impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
                            
                            if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                                $impressionsperselectedDates = $impressionsperselectedDates;
                            }else{
                                $impressionsperselectedDates = 1;
                            }
                            //$impressionSum+= $product_details->secondImpression; 
                            $impressionSum+= $impressionsperselectedDates;
                            $campaign_product->secondImpression = round($impressionsperselectedDates, 2);
                            //$cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                            //$offercpmcal = ($offershortlistedsum/$impressionSum) * 1000;
                            $cpmcal = ($priceperselectedDates/$impressionsperselectedDates) * 1000;
                            $offercpmcal = ($offerpriceperselectedDates/$impressionsperselectedDates) * 1000;
                            $campaign_product->cpmperselectedDates = $cpmcal;
                            $campaign_product->offercpmperselectedDates = $offercpmcal;
                            $campaign_product->cpm = $cpmcal;
                            $campaign_product->offercpm = $offercpmcal;
                            $campaign_product->impressionsperselectedDates = $impressionsperselectedDates;
                            $campaign_product->priceperselectedDates = $priceperselectedDates;
                            $campaign_product->offerpriceperselectedDates = $offerpriceperselectedDates;
            
                            $campaign_product->new_stripe_percent_amount = $newofferStripepercentamt;
                            $campaign_product->newprocessingfeeamt = $newprocessingfeeamt;
            
                            $newofferStripepercentamtSum += $newofferStripepercentamt;
                            $newprocessingfeeamtSum += $newprocessingfeeamt;
                            $campaign_product->productbookingid = $campaign_product->id;
                            // $deloffprice = $delofferprice + $delofferpriceStripepercentamt + $delofferpriceprocessingfeeamt;
                            // $campaign_product->deloffprice = $deloffprice;
                            // $deloffpriceSum += $deloffprice; 

                    }else{
                        //echo 'dsdsds';exit;
                        $offerDetails = MakeOffer::where([
                            ['campaign_id', '=', $campaign_product->campaign_id],
                            ['status', '=', 20],
                        ])->get();
                        
                        
                        if(isset($offerDetails) && count($offerDetails)==1){
                            foreach($offerDetails as $offerDetails){
                                    $offerprice = $offerDetails->price;
                                    $delofferprice = $offerDetails->price;
                                    $stripe_percent=$getproductDetails->stripe_percent;
                                    
                                    $price = $getproductDetails->default_price;
                                    
                                    //$price = $campaign_product->price;
                                    //$priceperday = $price/28;//exit;
                                    //echo '---camptot--'.$camptot;
                                    //$priceperselectedDates = $priceperday * $daysCountCPM;
									
									///variable_offer///
									$priceperday = $campaign_product->price;
									$priceperselectedDates = $priceperday;
									
									
                                    $newpricepercentage = ($priceperselectedDates/$camptot) * 100;
            
                                    $newofferprice = ($offerprice * ($newpricepercentage))/100;//exit;
                                    //$offerpriceperday = $newofferprice/28;//exit;
                                    //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                                    $offerpriceperselectedDates = $newofferprice;
                                    $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
                                    $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                                    $campaign_product->stripe_percent = $stripe_percent;
                                    
                                    $delofferpriceStripepercentamt = ($delofferprice * ($stripe_percent))/100;
                                    $campaign_product->delofferpriceStripepercentamt = $delofferpriceStripepercentamt;

                                    $delofferpriceprocessingfeeamt = ((2.9) * $delofferpriceStripepercentamt)/100;
                                    $campaign_product->delofferpriceprocessingfeeamt = $delofferpriceprocessingfeeamt;

                                    //$deloffprice = $delofferprice + $delofferpriceStripepercentamt + $delofferpriceprocessingfeeamt;
                                    $deloffprice = $delofferpriceStripepercentamt + $delofferpriceprocessingfeeamt;
                                    $campaign_product->deloffprice = $deloffprice;
                                    $deloffpriceSum += $deloffprice;
                                    $campaign_product->productbookingid = $campaign_product->id;
                                }
                            }else{
                                        //$offerprice = $getproductDetails->default_price;
                                $offerprice = $campaign_product->price;
                                $delofferprice = $campaign_product->price;
                                $stripe_percent=$getproductDetails->stripe_percent;
                                $price = $getproductDetails->default_price;
                                //$price = $campaign_product->price;
                                $priceperday = $price/28;//exit;
                                $priceperselectedDates = $priceperday * $daysCountCPM;
                                $newpricepercentage = ($priceperselectedDates/$camptot) * 100;
        
                                $newofferprice = $offerprice ;
                                //$offerpriceperday = $newofferprice/28;//exit;
                                //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                                $offerpriceperselectedDates = $newofferprice;
                                $newofferStripepercentamt = ($newofferprice * ($stripe_percent))/100;
                                $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;
                                $campaign_product->stripe_percent = $stripe_percent;
                                
                                $delofferpriceStripepercentamt = ($delofferprice * ($stripe_percent))/100;
                                $campaign_product->delofferpriceStripepercentamt = $delofferpriceStripepercentamt;

                                $delofferpriceprocessingfeeamt = ((2.9) * $delofferpriceStripepercentamt)/100;
                                $campaign_product->delofferpriceprocessingfeeamt = $delofferpriceprocessingfeeamt;

                                //$deloffprice = $delofferprice + $delofferpriceStripepercentamt + $delofferpriceprocessingfeeamt;
                                $deloffprice = $delofferpriceStripepercentamt + $delofferpriceprocessingfeeamt;
                                $campaign_product->deloffprice = $deloffprice;
                                $deloffpriceSum += $deloffprice;
                                $campaign_product->productbookingid = $campaign_product->id;
                                }
                                                
                                                
                                                                    
                                                $shortlistedsum+= $priceperselectedDates;
                                                $campaign_product->price = $priceperselectedDates;
                        
                                                $offershortlistedsum+= $offerpriceperselectedDates;
                                                $campaign_product->offerprice = $offerpriceperselectedDates;
                                                $cpmsum+= $getproductDetails->cpm;
                                                $impressions = $getproductDetails->secondImpression;
                                                $impressionsperday = (int)($impressions/7);
                                                $impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
                                                
                                                if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                                                    $impressionsperselectedDates = $impressionsperselectedDates;
                                                }else{
                                                    $impressionsperselectedDates = 1;
                                                }
                                                //$impressionSum+= $product_details->secondImpression; 
                                                $impressionSum+= $impressionsperselectedDates;
                                                $campaign_product->secondImpression = round($impressionsperselectedDates, 2);
                                                //$cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                                                //$offercpmcal = ($offershortlistedsum/$impressionSum) * 1000;
                                                $cpmcal = ($priceperselectedDates/$impressionsperselectedDates) * 1000;
                                                $offercpmcal = ($offerpriceperselectedDates/$impressionsperselectedDates) * 1000;
                                                $campaign_product->cpmperselectedDates = $cpmcal;
                                                $campaign_product->offercpmperselectedDates = $offercpmcal;
                                                $campaign_product->cpm = $cpmcal;
                                                $campaign_product->offercpm = $offercpmcal;
                                                $campaign_product->impressionsperselectedDates = $impressionsperselectedDates;
                                                $campaign_product->priceperselectedDates = $priceperselectedDates;
                                                $campaign_product->offerpriceperselectedDates = $offerpriceperselectedDates;
                        
                                                $campaign_product->new_stripe_percent_amount = $newofferStripepercentamt;
                                                $campaign_product->newprocessingfeeamt = $newprocessingfeeamt;
                        
                                                $newofferStripepercentamtSum += $newofferStripepercentamt;
                                                $newprocessingfeeamtSum += $newprocessingfeeamt;

                                                $delofferpriceStripepercentamtSum += $delofferpriceStripepercentamt;
                                                $delofferpriceprocessingfeeamtSum += $delofferpriceprocessingfeeamt;
                                                $campaign_product->productbookingid = $campaign_product->id;

                                                
                                                // $deloffprice = $delofferprice + $delofferpriceStripepercentamt + $delofferpriceprocessingfeeamt;
                                                // $campaign_product->deloffprice = $deloffprice;
                                                // $deloffpriceSum += $deloffprice;
//exit;
                       
                    }
                }
         
                array_push($products_arr, array_merge(Product::where('id', '=', $campaign_product->product_id)->first()->toArray(), $campaign_product->toArray(),$cancellationArray));
            }
            $campaign->products = $products_arr;
            $campaign->actbudg = $products_arr;

            $quote_change = CampaignQuoteChange::select('remark', 'type')->where('campaign_id', '=', $campaign_id)->get();
            //$quote_change = CampaignQuoteChange::select('remark','type')->where('campaign_id', '=', $campaign_id)->orderBy('created_at', 'desc')->get();
            if (!empty($quote_change)) {
                $campaign->quote_change = $quote_change;
            }

            // get campaign actual budget
            // $data = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
            // dd($data);
            $act_budget = ProductBooking::raw(function($collection) use ($campaign_id) {
                        return $collection->aggregate(
                                        [
                                            [
                                                '$match' =>
                                                [
                                                    "campaign_id" => $campaign_id
                                                ]
                                            ],
                                            [
                                                '$group' =>
                                                [
                                                    '_id' => '$campaign_id',
                                                    'total_price' => [
                                                        '$sum' => '$admin_price'
                                                    ]
                                                ]
                                            ]
                                        ]
                        );
                    });
            
        
                $res = array_sum(array_map(function($item) { 
                    return $item['price']; 
                }, $campaign->actbudg));
            //echo "<pre>act_budget";print_r($res);exit;
            $campaign->act_budget = $res;
            /*$campaign->act_budget = $act_budget[0]->total_price;
            
            if($campaign->act_budget == '0'){
                //dd(123)
                  $campaign->act_budget  = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('owner_price');
            }
            //dd($campaign->act_budget);
            if($campaign->act_budget == '0'){
                  $campaign->act_budget  = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('price');
            }*/
            
            /*$gststatus = isset($campaign->gststatus)?$campaign->gststatus:0;
                if($gststatus ==1){
                    $campaign->totalamount = $campaign->act_budget+round(($campaign->act_budget*(0.18)),2);
                }
                else{*/
                     $campaign->totalamount = $campaign->act_budget;
                //}
                 $campaign->total_paid = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('amount');
                 
                 $campaign->refunded_amount = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('refunded_amount');
                 
                 $campaign->bal_amount_available_with_amp = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('bal_amount_available_with_amp');
                 
                 //$campaign->payments = CampaignPayment::where('campaign_id', '=', $campaign->id)->get();
                 //echo "<pre>"; print_r($campaign->refunded_amount);exit;
                // echo 'ddddddddddd';exit;
                 $impressionSum4 = round($impressionSum, 2); /* As discussed with Richard on July 14) */
                 if($impressionSum4>0){
                    $cpmval = ($shortlistedsum/$impressionSum4) * 1000;
                    $offercpmval = ($offershortlistedsum/$impressionSum4) * 1000;
                 }else{
                     $cpmval = 0;
                     $offercpmval = 0;
                 }
         
                 $campaign->shortlistedsum = $shortlistedsum;
                 $campaign->cpmval = $cpmval;

                 $campaign->offershortlistedsum = $offershortlistedsum;
                 $campaign->offercpmval = $offercpmval;

                 $campaign->impressionSum = $impressionSum4;

                 $campaign->newofferStripepercentamtSum = $newofferStripepercentamtSum;
                 $campaign->newprocessingfeeamtSum = $newprocessingfeeamtSum;

                 $campaign->delofferpriceStripepercentamtSum = $delofferpriceStripepercentamtSum;
                 $campaign->delofferpriceprocessingfeeamtSum = $delofferpriceprocessingfeeamtSum;
                 
                 $campaign->percentagevalue = ($newofferStripepercentamtSum * 100)/$offershortlistedsum;
                 $campaign->finalpurchasepayment = $newofferStripepercentamtSum + $newprocessingfeeamtSum;

                 $campaign->finaldelpurchaseamt = ceil($deloffpriceSum);
                 

        }
        if(isset($booked_from) && !empty($booked_from)) {$campaign->startDate =  date('m-d-Y',min($booked_from));}
        if(isset($booked_to) && !empty($booked_to)) {$campaign->endDate = date('m-d-Y',max($booked_to));}
        
        return response()->json($campaign);
    }
    
     public function getFinForMeCount(){
          $findForMe_count = FindForMe::count(); 
          //echo '<pre>'; print_r($findForMe_count); exit;
          $findForMeCount = [
                'find-for-me' => $findForMe_count
            ];
            
          return response()->json($findForMeCount);
    }
    
     public function getOffersCount(){
          $offer_request = MakeOffer::where('status', '=', 10)->count(); 
          $offer_accept = MakeOffer::where('status', '=', 20)->count(); 
          $offer_reject = MakeOffer::where('status', '=', 40)->count();
          //echo '<pre>'; print_r($offer_request); exit;
          $offersCount = [
                'requestedOffers' => $offer_request,
                'acceptedOffers' => $offer_accept,
                'rejectedOffers' => $offer_reject
            ];
            
          return response()->json($offersCount); 
    }
     
     public function getCounts(){
         //$clients_count = ClientMongo::count();
		 //$clients_count = UserMongo::where('company_type', '!=', '')->count();
		 $clients_count = UserMongo::where('company_type', '!=', '')->where('company_type', '!=', 'sub-seller')->where('company_type', '!=', 'bbi')->count();
          //$users_count = UserMongo::count();
          $users_count = UserMongo::where('company_type', '=', '')->count();
          //echo '<pre>'; print_r($users_count); exit;
          
          $static_count = Product::where('type', '=', 'Static')->count(); 
          $digital_count = Product::where('type', '=', 'Digital')->count(); 
          $digital_static_count = Product::where('type', '=', 'Digital/Static')->count(); 
          $media_count = Product::where('type', '=', 'Media')->count(); 
          
        $requested_campaigns_count = Campaign::where('status', '=', Campaign::$CAMPAIGN_STATUS['booking-requested'])->count();
          
		$scheduled_campaigns = Campaign::where('status', '=', Campaign::$CAMPAIGN_STATUS['scheduled'])->get();
		$scheduled_campaigns_count = 0;
		$current_date = date_create(date("d-m-Y"));
		if(isset($scheduled_campaigns)){
			$arr_scheduled_campaigns = $scheduled_campaigns->toArray();
			$arr_product_booking = array();
			foreach($arr_scheduled_campaigns as $key => $val){
				$arr_product_booking = ProductBooking::where('booked_to', '>', $current_date)->where('booked_from', '>', $current_date)->where('campaign_id', '=', $val['id'])->get()->toArray();
				if(isset($arr_product_booking) && !empty($arr_product_booking)){
					$scheduled_campaigns_count = $scheduled_campaigns_count+1;
				}
			}
		}
        $running_campaigns = Campaign::where('status', '=', Campaign::$CAMPAIGN_STATUS['running'])->get();
		$running_campaigns_count = 0;
		if(isset($running_campaigns)){
			$arr_running_campaigns = $running_campaigns->toArray();
			$arr_product_booking_running = array();
			foreach($arr_running_campaigns as $key => $val){
				$arr_product_booking_running = ProductBooking::where('booked_to', '>=', $current_date)->where('booked_from', '<=', $current_date)->where('campaign_id', '=', $val['id'])->get()->toArray();
				if(isset($arr_product_booking_running) && !empty($arr_product_booking_running)){
					$running_campaigns_count = $running_campaigns_count+1;
				}
			}
		}		  
		  $closed_campaigns = Campaign::where('status', '=', Campaign::$CAMPAIGN_STATUS['stopped'])->orwhere('status', '=', Campaign::$CAMPAIGN_STATUS['deleted-cancelled'])->orwhere('status', '=', Campaign::$CAMPAIGN_STATUS['scheduled'])->orwhere('status', '=', Campaign::$CAMPAIGN_STATUS['suspended'])->get();
		  $closed_campaigns_count = 0;
		if(isset($closed_campaigns)){
			$arr_closed_campaigns = $closed_campaigns->toArray();
			$arr_product_booking_closed = array();
			foreach($arr_closed_campaigns as $key => $val){
				$arr_product_booking_closed = ProductBooking::where('booked_to', '<', $current_date)->where('campaign_id', '=', $val['id'])->get()->toArray();
				if(isset($arr_product_booking_closed) && !empty($arr_product_booking_closed)){
					$closed_campaigns_count = $closed_campaigns_count+1;
				}
			}
		}
          $saved_campaigns_count = Campaign::where('status', '=', Campaign::$CAMPAIGN_STATUS['campaign-preparing'])->count();
           
          
          $offer_request = MakeOffer::where('status', '=', 10)->count(); 
          $offer_accept = MakeOffer::where('status', '=', 20)->count(); 
          $offer_reject = MakeOffer::where('status', '=', 40)->count();
          
          $findForMe_count = FindForMe::count(); 
          $feedback_count = CustomerQuery::count(); 
          //$cart_count = ProductBooking::count(); 
          $cart_count = ShortListedProduct::count(); 
          //$notifications_count = Notification::count(); 
          $notifications_count = Notification::where('to_type', '=', 1)->count();  
          $bulkUploads_count = BulkUpload::count(); 
          $rfp_without_count = Campaign::where('status', '=', Campaign::$CAMPAIGN_STATUS['rfp-campaign'])->count(); 
          $referrals_count = Campaign::orderBy('referred_by')->count();  
          $admin_campaigns_count = Campaign::where('client_mongo_id', '=', '5b9914753abe1')->count();
          //$payments_count = CampaignPayment::count();
          //$cancellations_count = CancelCampaign::count();
		  $payments_count = Campaign::count();
        $cancellations_count = Campaign::where('status', '=', Campaign::$CAMPAIGN_STATUS['deleted-cancelled'])->count();
        
          //echo '<pre>';print_r($admin_campaigns_count);exit;
          //$black = Campaign::where('colorcode', '=', 'black')->count();    
          //$red = Campaign::where('colorcode', '=', 'red')->count();    
		  
		  $other_campaign_feeds = Campaign::whereIn('format_type', [null, Format::$FORMAT_TYPE['ooh']])
                        ->where(function($query) {
                            $query->where([
                                ['status', '=', Campaign::$CAMPAIGN_STATUS['quote-requested']],
                                ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['user']]
                            ])
                            ->orWhere([
                                ['status', '=', Campaign::$CAMPAIGN_STATUS['booking-requested']],
                                ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['user']]
                            ])
                            ->orWhere([
                                ['status', '=', Campaign::$CAMPAIGN_STATUS['change-requested']],
                                ['type', '=', Campaign::$CAMPAIGN_USER_TYPE['user']]
                            ]);
                        })
                        ->orderBy('updated_at', 'desc')->get();
						$red_color = 0;
						$black_color = 0;
						$current_date = date('d-m-Y');
						$diff = 0;
			foreach ($other_campaign_feeds as $ocf) {
				$product_start_date = ProductBooking::where('campaign_id', '=', $ocf->id)->select("booked_from","booked_to")->orderBy('booked_from', 'asc')->first();
				if (!empty($product_start_date)) {
					$ocf->start_date = $product_start_date->booked_from;
					$ocf->end_date = $product_start_date->booked_to;
					$diff=date_diff(date_create($current_date),date_create($ocf->start_date));	
					if($diff->days <=7 ){
						$red_color = $red_color+1;
					}else{
						$black_color = $black_color+1;
					}
				}	
			}			 		
						
						
						
               
          $Counts = [
                'Owners' => $clients_count,
                'Buyers' => $users_count,
                'Total' => $clients_count + $users_count,
                'Static' => $static_count,
                'Digital' => $digital_count,
                'Digital/Static' => $digital_static_count,
                'Media' => $media_count,
                'TotalProducts' => $static_count + $digital_count + $digital_static_count + $media_count,
                'RequestedCampaigns' => $requested_campaigns_count,
                'RunningCampaigns' => $running_campaigns_count,
                'ScheduledCampaigns' => $scheduled_campaigns_count,
                'ClosedCampaigns' => $closed_campaigns_count,
                'SavedCampaigns' => $saved_campaigns_count,
                'RequestedOffers' => $offer_request,
                'AcceptedOffers' => $offer_accept,
                'RejectedOffers' => $offer_reject,
                'Find' => $findForMe_count,
                'Feedback' => $feedback_count,
                'Cart' => $cart_count,
                'Notices' => $notifications_count,
                'BulkUploads' => $bulkUploads_count,
                'RFPBeforeLogin' => $rfp_without_count,
                'Referrals' => $referrals_count,
                //'Black' => $black,
                //'Red' => $red
				'Black'	=> $black_color,
				'Red'	=> $red_color,
                'MyCampaigns' => $admin_campaigns_count,
                'Payments' => $payments_count,
                'Cancellations' => $cancellations_count

            ];
             
          return response()->json($Counts);
    }
    
    public function getUsersDetails(){
        $users_details = UserMongo::orderBy('created_at', 'desc')->get(); 
        $owners_details = ClientMongo::orderBy('created_at', 'desc')->get(); 
        $users__owners_details = [
            'Buyers' => $users_details,
            'Owners' => $owners_details
        ];
        return response()->json($users__owners_details);
    }
    public function getProductsDetails(){
        
        $static_products = Product::where([
                        ['type', '=', 'Static']
                    ])->orderBy('created_at', 'desc')->get();
        $digital_products = Product::where([
                        ['type', '=', 'Digital']
                    ])->orderBy('created_at', 'desc')->get();
        $digital_static_products = Product::where([
                        ['type', '=', 'Digital/Static']
                    ])->orderBy('created_at', 'desc')->get();
        $media_products = Product::where([
                        ['type', '=', 'Media']
                    ])->orderBy('created_at', 'desc')->get();
                    
                    
        $prodcuts_details = [
            'StaticProducts' => $static_products,
            'DigitalProducts' => $digital_products,
            'DigitalStaticProducts' => $digital_static_products,
            'MediaProducts' => $media_products
        ];
        return response()->json($prodcuts_details); 
    }
    public function getCampaignsDetails(){
        
        $requested_campaigns = Campaign::where([
                        ['status', '=', Campaign::$CAMPAIGN_STATUS['booking-requested']]
                    ])->orderBy('created_at', 'desc')->get();
                    
        $scheduled_campaigns = Campaign::where([
                        ['status', '=', Campaign::$CAMPAIGN_STATUS['scheduled']]
                    ])->orderBy('created_at', 'desc')->get();
                    
        $running_campaigns = Campaign::where([
                        ['status', '=', Campaign::$CAMPAIGN_STATUS['running']]
                    ])->orderBy('created_at', 'desc')->get();
                    
        $closed_campaigns = Campaign::where([
                        ['status', '=', Campaign::$CAMPAIGN_STATUS['stopped']]
                    ])->orderBy('created_at', 'desc')->get();
                    
        $saved_campaigns = Campaign::where([
                        ['status', '=', Campaign::$CAMPAIGN_STATUS['campaign-preparing']]
                    ])->orderBy('created_at', 'desc')->get();
                    
                    
        $campaign_details = [
            'RequestedCampaigns' => $requested_campaigns,
            'RunningCampaigns' => $scheduled_campaigns,
            'ScheduledCampaigns' => $running_campaigns,
            'ClosedCampaigns' => $closed_campaigns,
            'SavedCampaigns' => $saved_campaigns
        ];
        return response()->json($campaign_details);
    }
    
    public function updateCampaign(Request $request){
        
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
          
        if (isset($input['campaign_id'])) {
            //echo '<pre>in if'; print_r($input); exit;
            $campaign_edit = Campaign::where('id', '=', $input['campaign_id'])->first();
            $campaign_edit->name = isset($input['name']) ? $input['name'] : $campaign_edit->name;
            if ($campaign_edit->save()) {
                // Update data to elasticsearch :: Pankaj 19 Oct 2021
                $this->es_etl($campaign_edit, "update");
                return response()->json(["status" => "1", "message" => "Updated successfully"]);
            } else {
                return response()->json(["status" => "0", "message" => "Failed to Update"]);
            }
        
        }
    }
    
     public function filterUsers(Request $request) {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        
        $curdate1 = date_create(date("Y-m-d"));
        
        $filters_array = [];
        
        if (isset($input['from_date']) && isset($input['to_date'])) {
            if (isset($input['from_date']) && !empty($input['from_date'])) {
                $from = $input['from_date'];
            }
            if (isset($input['to_date']) && !empty($input['to_date'])) {
                $to = $input['to_date'];
            }
 
            //echo '<pre>';print_r($input['to_date']);exit; 
            
            $fromdate = iso_to_mongo_date($from);
            $todate = iso_to_mongo_date($to);
            
            $start = iso_to_mongo_date($from);
            $startdate = date_create($from);
            $enddate = date_create($to);
  
            //echo '<pre>';print_r($enddate);exit;
        
            $user_List = UserMongo::where('created_at', '<=', $enddate)
            ->where('created_at', '>=', $startdate)
            //->where('updated_at', '>=', $curdate1)
            ->get();  
                //echo '<pre>';print_r($user_List);exit;        
            $user_filter = [];
            if (count($user_List) > 0) {
                foreach ($user_List as $val) {
                    $user_filter[] = $val->id;
                }
                //echo "<pre>"; print_r($user_filter); exit;
                array_push($filters_array, ["id" => ['$in' => $user_filter]]);
                //echo 'if';
                //echo "<pre>"; print_r($user_filter);  
                //array_push($filters_array, ["id" => ['$nin' => $user_filter]]);
            } else {
                //array_push($filters_array, ["id" => ['$in' => $user_filter]]);
                array_push($filters_array, ["id" => ['$nin' => $user_filter]]);
                //echo 'else';
                //echo "<pre>"; print_r($filters_array); 
            }
            //echo "<pre>"; print_r($filters_array); exit;  
        }
        if(!empty($user_filter)){
        
        $grouped_users = UserMongo::raw(function($collection) use ($filters_array) {
                    return $collection->aggregate(
                                    [
                                        ['$match' => [
                                                '$and' => $filters_array
                                            ]
                                        ], 
                                        [
                                            '$group' => [
                                                '_id' => ['id' => '$id'],
                                                'user_details' => [
                                                    '$push' => ['id' => '$id',
                                                            'user_id' => '$user_id',
                                                            'first_name' => '$first_name',
                                                            'last_name' => '$last_name',
                                                            'email' => '$email',
                                                            'phone' => '$phone',
                                                            'company_name' => '$company_name',
                                                            'company_type' => '$company_type',
                                                            'address' => '$address',
                                                            'verified' => '$verified',
                                                            'created_at' => '$created_at'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                    );
                });
                
        // return response()->json($res); 
        return response()->json($grouped_users);
        }
        else{
            return response()->json(['status' => 0, 'message' => "No users registered in selected criteria"]);
        }
     }   
     
     public function filterProductsReport(Request $request) {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        
        $curdate1 = date_create(date("Y-m-d"));
        
        $filters_array = [];
        
        if (isset($input['from_date']) && isset($input['to_date'])) {
            if (isset($input['from_date']) && !empty($input['from_date'])) {
                $from = $input['from_date'];
            }
            if (isset($input['to_date']) && !empty($input['to_date'])) {
                $to = $input['to_date'];
            }
 
            //echo '<pre>';print_r($input['to_date']);exit;   
            
            $fromdate = iso_to_mongo_date($from);
            $todate = iso_to_mongo_date($to);
            
            $start = iso_to_mongo_date($from);
            $startdate = date_create($from);
            $enddate = date_create($to);
  
            //echo '<pre>';print_r($enddate);exit;
        
            $user_List = Product::where('created_at', '<=', $enddate)
            ->where('created_at', '>=', $startdate)
            //->where('updated_at', '>=', $curdate1)
            ->get();  
                //echo '<pre>';print_r($user_List);exit;        
            $user_filter = [];
            if (count($user_List) > 0) {
                foreach ($user_List as $val) {
                    $user_filter[] = $val->id;
                }
                //echo "<pre>"; print_r($user_filter); exit;
                array_push($filters_array, ["id" => ['$in' => $user_filter]]);
                //echo 'if';
                //echo "<pre>"; print_r($user_filter);  
                //array_push($filters_array, ["id" => ['$nin' => $user_filter]]);
            } else {
                //array_push($filters_array, ["id" => ['$in' => $user_filter]]);
                array_push($filters_array, ["id" => ['$nin' => $user_filter]]);
                //echo 'else';
                //echo "<pre>"; print_r($filters_array); 
            }
            //echo "<pre>"; print_r($filters_array); exit;  
        }
        
        if(!empty($user_filter)){
        
        $grouped_users = Product::raw(function($collection) use ($filters_array) {
                    return $collection->aggregate(
                                    [
                                        ['$match' => [
                                                '$and' => $filters_array
                                            ]
                                        ], 
                                        [
                                            '$group' => [
                                                '_id' => ['id' => '$id'],
                                                'product_details' => [
                                                    '$push' => ['id' => '$id',
                                                            'siteNo' => '$siteNo',
                                                            'adStrength' => '$adStrength',
                                                            'address' => '$address',
                                                            'title'=>'$title',
                                                            'addresstwo'=>'$addresstwo',
                                                            'from_date'=>'$from_date',
                                                            'to_date'=>'$to_date',
                                                            'venue'=>'$venue',
                                                            'ethnicity'=>'$ethnicity',
                                                            'impressions' => '$impressions',
                                                            'strengths'=>'$strengths',
                                                            'client_name' => '$client_name',
                                                            'direction' => '$direction',
                                                            'hoardingCost' => '$hoardingCost',
                                                            'image' => '$image',
                                                            'lighting' => '$lighting',
                                                            'symbol' => '$symbol',
                                                            'panelSize' => '$panelSize',
                                                            'height' => '$height',
                                                            'width' => '$width',
                                                            'type' => '$type',
                                                            'format_name' => '$format_name',
                                                            'country_name' => '$country_name',
                                                            'country' => '$country',
                                                            'state_name' => '$state_name',
                                                            'state' => '$state',
                                                            'city_name' => '$city_name',
                                                            'city' => '$city',
                                                            'zipcode' => '$zipcode',
                                                            'lat' => '$lat',
                                                            'lng' => '$lng',
                                                            'area_name' => '$area_name',
                                                            'videoUrl' => '$videoUrl',
                                                            'loops' => '$loops',
                                                            'flipsloops' => '$flipsloops',
                                                            'audited' => '$audited',
                                                            'cancellation_policy' => '$cancellation_policy',
                                                            'price' => '$default_price',
                                                            'product_visibility' => '$product_visibility',
                                                            'demographicsage'=>'$demographicsage',
                                                            'imgdirection'=>'$imgdirection',
                                                            'imgdrection'=>'$imgdrection',
                                                            'slots'=>'$slots',
                                                            'cancellation'=>'$cancellation',
                                                            'minimumbooking'=>'$minimumbooking',
                                                            'strengths'=>'$strengths',
                                                            'rateCard'=>'$rateCard',
                                                            'firstImpression'=>'$firstImpression',
                                                            'secondImpression'=>'$secondImpression',
                                                            'thirdImpression'=>'$thirdImpression',
                                                            'forthImpression'=>'$forthImpression',
                                                            'vendor'=>'$vendor',
                                                            'sellerId'=>'$sellerId',
                                                            'mediahhi'=>'$mediahhi',
                                                            'firstdayofpurchase'=>'$firstdayofpurchase',
                                                            'lastdayofpurchase'=>'$lastdayofpurchase',
                                                            'weekPeriod'=>'$weekPeriod',
                                                            'installCost'=>'$installCost',
                                                            'negotiatedCost'=>'$negotiatedCost',
                                                            'productioncost'=>'$productioncost',
                                                            'notes'=>'$notes',
                                                            'Comments'=>'$Comments',
                                                            'description'=>'$description',
                                                            'fliplength'=>'$fliplength',
                                                            'looplength'=>'$looplength',
                                                            'locationDesc'=>'$locationDesc',
                                                            'sound'=>'$sound',
                                                            'staticMotion'=>'$staticMotion',
                                                            'file_type'=>'$file_type',
                                                            'product_newAge'=>'$product_newAge',
                                                            'medium'=>'$medium', 
                                                            'cpm'=>'$cpm',
                                                            'firstcpm'=>'$firstcpm',
                                                            'thirdcpm'=>'$thirdcpm',
                                                            'forthcpm'=>'$forthcpm',
                                                            'ageloopLength'=>'$ageloopLength',
                                                            'product_newMedia'=>'$product_newMedia',
                                                            'placement'=>'$placement',
                                                            'spotLength'=>'$spotLength',
                                                            'unitQty'=>'$unitQty',
                                                            'billingYes'=>'$billingYes',
                                                            'billingNo'=>'$billingNo',
                                                            'servicingYes'=>'$servicingYes',
                                                            'servicingNo'=>'$servicingNo',
                                                            'fix'=>'$fix',
                                                            'minimumdays'=>'$minimumdays',
                                                            'network'=>'$network',
                                                            'nationloc'=>'$nationloc',
                                                            'daypart'=>'$daypart',
                                                            'genre'=>'$genre',
                                                            'costperpoint'=>'$costperpoint',
                                                            'length'=>'$length',
                                                            'reach'=>'$reach',
                                                            'daysselected'=>'$daysselected',
                                                            'stripe_percent'=>'$stripe_percent',
                                                            'created_at' => '$created_at'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                    );
                });
                
        // return response()->json($res); 
        return response()->json($grouped_users);
        }
        else{
            return response()->json(['status' => 0, 'message' => "No products available in selected criteria"]);
        }
         
     }
     
    public function filterCampaignsReport(Request $request) {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        
        $curdate1 = date_create(date("Y-m-d"));
        
        $filters_array = [];
        
        if (isset($input['from_date']) && isset($input['to_date'])) {
            if (isset($input['from_date']) && !empty($input['from_date'])) {
                $from = $input['from_date'];
            }
            if (isset($input['to_date']) && !empty($input['to_date'])) {
                $to = $input['to_date'];
            }
 
            //echo '<pre>';print_r($input['to_date']);exit; 
            
            $fromdate = iso_to_mongo_date($from);
            $todate = iso_to_mongo_date($to);
            
            $start = iso_to_mongo_date($from);
            $startdate = date_create($from);
            $enddate = date_create($to);
  
            //echo '<pre>';print_r($enddate);exit;
        
            $user_List = Campaign::where('created_at', '<=', $enddate)
            ->where('created_at', '>=', $startdate)
            //->where('updated_at', '>=', $curdate1)
            ->get();  
                //echo '<pre>';print_r($user_List);exit;        
            $user_filter = [];
            if (count($user_List) > 0) {
                foreach ($user_List as $val) {
                    $user_filter[] = $val->id;
                }
                //echo "<pre>"; print_r($user_filter); exit;
                array_push($filters_array, ["id" => ['$in' => $user_filter]]);
                //echo 'if';
                //echo "<pre>"; print_r($user_filter);  
                //array_push($filters_array, ["id" => ['$nin' => $user_filter]]);
            } else {
                //array_push($filters_array, ["id" => ['$in' => $user_filter]]);
                array_push($filters_array, ["id" => ['$nin' => $user_filter]]);
                //echo 'else';
                //echo "<pre>"; print_r($filters_array); 
            }
            //echo "<pre>"; print_r($filters_array); exit;  
        }
        
        if(!empty($user_filter)){
        
        $grouped_users = Campaign::raw(function($collection) use ($filters_array) {
                    return $collection->aggregate(
                                    [
                                        ['$match' => [
                                                '$and' => $filters_array
                                            ]
                                        ], 
                                        [
                                            '$group' => [
                                                '_id' => ['id' => '$id'],
                                                'campaign_details' => [
                                                    '$push' => ['id' => '$id',
                                                            'name' => '$name',
                                                            'cid' => '$cid',
                                                            'created_at' => '$created_at',
                                                            'status'=>'$status'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                    );
                });
                
        // return response()->json($res);  
        return response()->json($grouped_users);
        }
        else{
            return response()->json(['status' => 0, 'message' => "No campaigns available in selected criteria"]);
        }
     }
     
     public function getUsersDetailsTestingAuth(){
        $users_details = UserMongo::orderBy('created_at', 'desc')->get(); 
        $owners_details = ClientMongo::orderBy('created_at', 'desc')->get(); 
        $users__owners_details = [
            'Buyers' => $users_details,
            'Owners' => $owners_details
        ];
        return response()->json($users__owners_details);
    }

    public function filterUsersDownload(Request $request) {
        ini_set("memory_limit", "800M"); ini_set("max_execution_time", "800");
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }

        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo['user_id'])->first();        
        $user_email = $user->email;
        $from_date = ''; $to_date = ''; $final_array = [];

        if (isset($input['from_date']) && !empty($input['from_date'])) {
            $from_date = $input['from_date'];
        }
        if (isset($input['to_date']) && !empty($input['to_date'])) {
            $to_date = $input['to_date'];
        }

        $es_url = env('ES_SERVER_FILTER_USERS');
        $es_index = env('ES_USERS');
        $es_auth_index = env('ES_USERS_AUTH');

        $data_string = array(
            "email" => base64_encode($user_email),
            "index" => $es_index,
            "auth_index" => $es_auth_index,
            "from_date" => $from_date,
            "to_date" => $to_date
        );
        $data = json_encode($data_string);
        $ch = curl_init( $es_url );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch);
        curl_close($ch);
        
        $es_data = json_decode($result, true);
        $es_result = $es_data['data'];

        $searchparam = isset($input['searchparam']) ? $input['searchparam'] : "";
        if ( $searchparam != '' ) {
            $param = strtolower($searchparam);
            if (count($es_result) > 0) {
                foreach ($es_result as $val) {                
                    if ( in_array($param, array('yes','no')) ) {
                        if ($param == 'yes') {
                            $verified_search = $val['verified'] ? true : false;
                        } else {
                            $verified_search = !($val['verified']) ? true : false;
                        }
                    } else {
                        $verified_search = false;
                    }  

                    if ( str_contains(strtolower(trim($val['email'])), $param) || str_contains(strtolower(trim($val['first_name'])), $param) || str_contains(strtolower(trim($val['last_name'])), $param) || str_contains(strtolower(trim($val['phone'])), $param) || str_contains(strtolower(trim($val['company_name'])), $param) || $verified_search ){
                        $final_array[] = $val;
                    }    
                }         
            } else {
                $final_array = [];
            }
        } else {
            $final_array = $es_result;
        }

        $users_details_pdf = ['users_details' => $final_array];

        if ( count($final_array) > 0 ) {
            $pdf = PDF::loadView('pdf.users_pdf', $users_details_pdf);
            if (!empty($pdf)) {
                return $pdf->download("users.pdf");
            } else {
                return response()->json(['status' => 0, 'message' => "There was an error. Please try again."]);
            }
        } else {
            return response()->json(['status' => 0, 'message' => "No users registered in selected criteria"]);
        }
    }   
    
    public function filterProductsReportDownload(Request $request) {
        ini_set("memory_limit", "800M"); ini_set("max_execution_time", "800");
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo['user_id'])->first();        
        $user_email = $user->email;
        $from_date = ''; $to_date = ''; $final_array = [];

        if (isset($input['from_date']) && !empty($input['from_date'])) {
            $from_date = $input['from_date'];
        }
        if (isset($input['to_date']) && !empty($input['to_date'])) {
            $to_date = $input['to_date'];
        }
        $es_url = env('ES_SERVER_FILTER_PRODUCTS');
        $es_index = env('ES_PRODUCTS');
        $es_auth_index = env('ES_USERS_AUTH');

        $data_string = array(
            "email" => base64_encode($user_email),
            "index" => $es_index,
            "auth_index" => $es_auth_index,
            "from_date" => $from_date,
            "to_date" => $to_date
        );
        $data = json_encode($data_string);
        $ch = curl_init( $es_url );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch);
        curl_close($ch);
        
        $es_data = json_decode($result, true);
        $es_result = $es_data['data'];
        $searchparam = isset($input['searchparam']) ? $input['searchparam'] : "";

        if ( $searchparam != '' ) {
            $param = strtolower($searchparam);
            if (count($es_result) > 0) {
                foreach ($es_result as $val) { 
                    if ( str_contains(strtolower(trim($val['siteNo'])), $param) || str_contains(strtolower(trim($val['type'])), $param) || str_contains(strtolower(trim($val['title'])), $param) || str_contains(strtolower(trim($val['lat'])), $param) || str_contains(strtolower(trim($val['lng'])), $param) || str_contains(strtolower(trim($val['state'])), $param) || str_contains(strtolower(trim($val['city_name'])), $param) || str_contains(strtolower(trim($val['zipcode'])), $param) || str_contains(strtolower(trim($val['default_price'])), $param) ){
                        $final_array[] = $val;
                    }    
                }         
            } else {
                $final_array = [];
            }
        } else {
            $final_array = $es_result;
        }
        
        $product_details_pdf = ['product_details' => $final_array];
        if ( count($final_array) > 0 ) {
            $pdf = PDF::loadView('pdf.products_pdf', $product_details_pdf);
            if (!empty($pdf)) {
                return $pdf->download("products.pdf");
            } else {
                return response()->json(['status' => 0, 'message' => "There was an error. Please try again."]);
            }
        } else {
            return response()->json(['status' => 0, 'message' => "No data found in selected criteria"]);
        }
    }

    public function filterCampaignsReportDownload(Request $request) {
        ini_set("memory_limit", "800M"); ini_set("max_execution_time", "800");
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo['user_id'])->first();        
        $user_email = $user->email;
        $from_date = ''; $to_date = ''; $final_array = [];

        if (isset($input['from_date']) && !empty($input['from_date'])) {
            $from_date = $input['from_date'];
        }
        if (isset($input['to_date']) && !empty($input['to_date'])) {
            $to_date = $input['to_date'];
        }
        $es_url = env('ES_SERVER_FILTER_CAMPAIGNS');
        $es_index = env('ES_CAMPAIGNS');
        $es_auth_index = env('ES_USERS_AUTH');

        $data_string = array(
            "email" => base64_encode($user_email),
            "index" => $es_index,
            "auth_index" => $es_auth_index,
            "from_date" => $from_date,
            "to_date" => $to_date
        );
        $data = json_encode($data_string);
        $ch = curl_init( $es_url );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch);
        curl_close($ch);
        
        $es_data = json_decode($result, true);
        $es_result = $es_data['data'];
        $searchparam = isset($input['searchparam']) ? $input['searchparam'] : "";

        if ( $searchparam != '' ) {
            $param = strtolower($searchparam);
            if (count($es_result) > 0) {
                $campaign_status_array = array(
                    "Campaign Preparing",
                    "Campaign Created",
                    "Quote Created",
                    "Quote Given",
                    "Change Requested",
                    "Requested",
                    "Scheduled",
                    "Running",
                    "Suspended",
                    "Stopped",
                    "Deleted",
                    "RFP Campaign"
                );
                foreach ($es_result as $val) { 
                    if ( in_array(ucwords($param), $campaign_status_array) ) {
                        $status_key = 0;
                        foreach(Campaign::$CAMPAIGN_STATUS_1 as $k1 => $v1) {
                            if ( ucwords($param) == $v1 ) {
                                $status_key = $k1;
                            }
                        }
                        if ( $status_key != 0 ) {
                            if ( $status_key == $val['status'] ) { 
                                $campaign_status_search = true;
                            } else {
                                $campaign_status_search = false;
                            }
                        } else {
                            $campaign_status_search = false;
                        }

                    } else {
                        $campaign_status_search = false;
                    }  

                    if ( str_contains(strtolower(trim($val['cid'])), $param) || str_contains(strtolower(trim($val['name'])), $param) || $campaign_status_search ){
                        $final_array[] = $val;
                    }    
                }         
            } else {
                $final_array = [];
            }
        } else {
            $final_array = $es_result;
        }

        $final_array1 = [];
        foreach ($final_array as $key => $value) {
            $status = '';
            if ( strlen(array_search($value['status'], Campaign::$CAMPAIGN_STATUS)) > 0 ) {
                $status = Campaign::$CAMPAIGN_STATUS_1[$value['status']];
            }
            $final_array1[] = array(
                'cid' => $value['cid'],
                'name' => $value['name'],
                'status' => $status 
            );
        }
        
        $campaigns_details_pdf = ['campaigns' => $final_array1];

        if ( count($final_array1) > 0 ) {
            $pdf = PDF::loadView('pdf.campaigns_pdf', $campaigns_details_pdf);
            if (!empty($pdf)) {
                return $pdf->download("campaigns.pdf");
            } else {
                return response()->json(['status' => 0, 'message' => "There was an error. Please try again."]);
            }
        } else {
            return response()->json(['status' => 0, 'message' => "No data found in selected criteria"]);
        }
    }

    public function filterUsersExcelDownload1(Request $request) {        
        ini_set("memory_limit", "800M"); ini_set("max_execution_time", "800");
        $users_arr = [];$sno = 1;
        $users_data = UserMongo::orderBy('created_at', 'desc')->get(); 
        foreach ($users_data as $key => $value) {  
            if ((@array_key_exists("verified", $value)) && ($user['verified'])) { 
                $verified = "Yes"; 
            } else { 
                $verified = "No"; 
            }
            $users_arr[] = array(
                "sno" => $sno,
                "first_name" => $value['first_name'],
                "last_name" => $value['last_name'],
                "email" => $value['email'],
                "phone" => $value['phone'],
                "company_name" => $value['company_name'],
                "verified" => $verified
            );
            $sno++;
        }
        if ( count($users_arr) > 0 ) {
            $filename = 'users.csv';
            $file = fopen($filename,"w");
            fputcsv($file, array('S No.', 'First Name', 'Last Name', 'Email', 'Phone', 'Company', 'Approved'));
            foreach ($users_arr as $line){
                fputcsv($file,$line);
            }
            fclose($file);
            $headers = array(
                'Content-Type' => 'text/csv',
            );
            return response()->download($filename, 'users.csv', $headers);
            header("Content-type: text/csv");
            readfile($filename);
        } else {
            return response()->json(['status' => 0, 'message' => "No users registered in selected criteria"]);
        }
    }

    public function filterUsersExcelDownload(Request $request) {
        ini_set("memory_limit", "800M"); ini_set("max_execution_time", "800");
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        $searchparam = isset($input['searchparam']) ? $input['searchparam'] : "";
        $filters_array = [];$users_arr = [];$users_arr1 = [];$sno = 1;
        $user_filter = [];$user_filter1 = [];
            
        if (isset($input['from_date']) && isset($input['to_date'])) {
            $searchparam = isset($input['searchparam']) ? $input['searchparam'] : "";
            if (isset($input['from_date']) && !empty($input['from_date'])) {
                $from = $input['from_date'];
            }
            if (isset($input['to_date']) && !empty($input['to_date'])) {
                $to = $input['to_date'];
            }
            $fromdate = iso_to_mongo_date($from);
            $todate = iso_to_mongo_date($to);
            
            $start = iso_to_mongo_date($from);
            $startdate = date_create($from);
            $enddate = date_create($to);

            $users = UserMongo::where('created_at','<=' ,$enddate)
                ->where('created_at','>=' ,$startdate)
                ->get();
        } else {
            $users = UserMongo::get();
        } 
        if (count($users) > 0) {
            foreach ($users as $val) {
                if ( $searchparam != '' ) {
                    if ( (strpos(strtolower(trim($val->email)), strtolower(trim($searchparam))) === 0) || (strpos(strtolower(trim($val->first_name)), strtolower(trim($searchparam))) === 0) || (strpos(strtolower(trim($val->last_name)), strtolower(trim($searchparam))) === 0) || (strpos(strtolower(trim($val->company_name)), strtolower(trim($searchparam))) === 0) ){
                        $user_filter1[] = $val->id;
                    } else {
                        $user_filter[] = $val->id;
                    }                      
                } else {
                    $user_filter[] = $val->id;
                }
            }
            if ( count($user_filter1) > 0 ) {
                $user_filter = $user_filter1;
            }
            $users_data = UserMongo::whereIn('id', $user_filter)->orderBy('created_at', 'desc')->get();                 
        } else {
            $users_data = UserMongo::whereNotIn('id', $user_filter)->orderBy('created_at', 'desc')->get(); 
        }
        if (count($users_data) > 0){  
            foreach ($users_data as $key => $value) {  
                if ((@array_key_exists("verified", $value)) && ($user['verified'])) { 
                    $verified = "Yes"; 
                } else { 
                    $verified = "No"; 
                }
                $users_arr[] = array($sno,$value['first_name'],$value['last_name'],$value['email'],$value['phone'],$value['company_name'],$verified);
                $sno++;
            }
        } else {
            return response()->json(['status' => 0, 'message' => "No data found in selected criteria"]);
        }
        if ( count($users_arr) > 0 ) {
            $filename = 'users.csv';
            $file = fopen($filename,"w");
            fputcsv($file, array('S No.', 'First Name', 'Last Name', 'Email', 'Phone', 'Company', 'Approved'));
            foreach ($users_arr as $line){
                fputcsv($file,$line);
            }
            fclose($file);
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header("Content-type: text/csv");
            readfile($filename);
        } else {
            return response()->json(['status' => 0, 'message' => "No users registered in selected criteria"]);
        }
    }

    public function filterProductsExcelDownload(Request $request) {
        ini_set("memory_limit", "800M"); ini_set("max_execution_time", "800");
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        $searchparam = isset($input['searchparam']) ? $input['searchparam'] : "";
        $filters_array = [];$users_arr = [];$users_arr1 = [];$sno = 1;
        $user_filter = [];$user_filter1 = [];
            
        if (isset($input['from_date']) && isset($input['to_date'])) {
            $searchparam = isset($input['searchparam']) ? $input['searchparam'] : "";
            if (isset($input['from_date']) && !empty($input['from_date'])) {
                $from = $input['from_date'];
            }
            if (isset($input['to_date']) && !empty($input['to_date'])) {
                $to = $input['to_date'];
            }
            $fromdate = iso_to_mongo_date($from);
            $todate = iso_to_mongo_date($to);
            
            $start = iso_to_mongo_date($from);
            $startdate = date_create($from);
            $enddate = date_create($to);

            $users = Product::where('created_at','<=' ,$enddate)
                ->where('created_at','>=' ,$startdate)
                ->get();
        } else {
            $users = Product::get();
        }
        if (count($users) > 0) {
            foreach ($users as $val) {
                if ( $searchparam != '' ) {
                    if ( (strpos(strtolower(trim($val->siteNo)), strtolower(trim($searchparam))) === 0) || (strpos(strtolower(trim($val->title)), strtolower(trim($searchparam))) === 0) || (strpos(strtolower(trim($val->address)), strtolower(trim($searchparam))) === 0) || (strpos(strtolower(trim($val->area_name)), strtolower(trim($searchparam))) === 0) || (strpos(strtolower(trim($val->city_name)), strtolower(trim($searchparam))) === 0) || (strpos(strtolower(trim($val->state_name)), strtolower(trim($searchparam))) === 0) || (strpos(strtolower(trim($val->default_price)), strtolower(trim($searchparam))) === 0) || (strpos(strtolower(trim($val->type)), strtolower(trim($searchparam))) === 0) ){
                        $user_filter1[] = $val->id;
                    } else {
                        $user_filter[] = $val->id;
                    }                      
                } else {
                    $user_filter[] = $val->id;
                }
            }
            if ( count($user_filter1) > 0 ) {
                $user_filter = $user_filter1;
            }
            $users_data = Product::whereIn('id', $user_filter)->orderBy('created_at', 'desc')->get();                 
        } else {
            $users_data = Product::whereNotIn('id', $user_filter)->orderBy('created_at', 'desc')->get();
        }
        if (count($users_data) > 0){  
            foreach ($users_data as $key => $value) { 
                $users_arr[] = array($sno,$value['siteNo'],$value['type'],$value['title'],$value['lat'],$value['lng'],$value['state'],$value['city_name'],$value['zipcode'],$value['default_price']);
                $sno++;
            }
        } else {
            return response()->json(['status' => 0, 'message' => "No data found in selected criteria"]);
        }
        if ( count($users_arr) > 0 ) {
            $filename = 'products.csv';
            $file = fopen($filename,"w");
            fputcsv($file, array('S No.', 'ID', 'Type', 'Title', 'Lat', 'Long', 'State', 'City', 'Zipcode', 'Price'));
            foreach ($users_arr as $line){
                fputcsv($file,$line);
            }
            fclose($file);
            header("Content-type: text/csv");
            readfile($filename);
        } else {
            return response()->json(['status' => 0, 'message' => "No users registered in selected criteria"]);
        }
    }  

    public function filterCampaignsExcelDownload(Request $request) {
        ini_set("memory_limit", "800M"); ini_set("max_execution_time", "800");
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        $searchparam = isset($input['searchparam']) ? $input['searchparam'] : "";
        $filters_array = [];$users_arr = [];$users_arr1 = [];$sno = 1;
        $user_filter = [];$user_filter1 = [];
            
        if (isset($input['from_date']) && isset($input['to_date'])) {
            $searchparam = isset($input['searchparam']) ? $input['searchparam'] : "";
            if (isset($input['from_date']) && !empty($input['from_date'])) {
                $from = $input['from_date'];
            }
            if (isset($input['to_date']) && !empty($input['to_date'])) {
                $to = $input['to_date'];
            }
            $fromdate = iso_to_mongo_date($from);
            $todate = iso_to_mongo_date($to);
            
            $start = iso_to_mongo_date($from);
            $startdate = date_create($from);
            $enddate = date_create($to);

            $users = Campaign::where('created_at','<=' ,$enddate)
                ->where('created_at','>=' ,$startdate)
                ->get();
        } else {
            $users = Campaign::get();
        } 
        if (count($users) > 0) {
            foreach ($users as $val) {
                if ( $searchparam != '' ) {
                    if ( (strpos(strtolower(trim($val->name)), strtolower(trim($searchparam))) === 0) || (strpos(strtolower(trim($val->cid)), strtolower(trim($searchparam))) === 0) ){
                        $user_filter1[] = $val->id;
                    } else {
                        $user_filter[] = $val->id;
                    }                      
                } else {
                    $user_filter[] = $val->id;
                }
            }
            if ( count($user_filter1) > 0 ) {
                $user_filter = $user_filter1;
            }
            $users_data = Campaign::whereIn('id', $user_filter)->orderBy('created_at', 'desc')->get();                 
        } else {
            $users_data = Campaign::whereNotIn('id', $user_filter)->orderBy('created_at', 'desc')->get(); 
        }
        if (count($users_data) > 0){  
            foreach ($users_data as $key => $value) {  
                $status = '';
                if ( strlen(array_search($value['status'], Campaign::$CAMPAIGN_STATUS)) > 0 ) {
                    $status = Campaign::$CAMPAIGN_STATUS_1[$value['status']];
                }
                $users_arr[] = array($sno,$value['cid'],$value['name'],$status);
                $sno++;
            }
        } else {
            return response()->json(['status' => 0, 'message' => "No data found in selected criteria"]);
        }
        if ( count($users_arr) > 0 ) {
            $filename = 'campaigns.csv';
            $file = fopen($filename,"w");
            fputcsv($file, array('S No.', 'Campaign ID', 'Name', 'Status'));
            foreach ($users_arr as $line){
                fputcsv($file,$line);
            }
            fclose($file);
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header("Content-type: text/csv");
            readfile($filename);
        } else {
            return response()->json(['status' => 0, 'message' => "No users registered in selected criteria"]);
        }
    }

    public function unique_multidim_array($array, $key) {
        $unique_array = array();       
        foreach($array as $val) {
            if (!in_array($val[$key], $unique_array)) {
                $unique_array[] = $val[$key];
            }
        }
        return $unique_array;
    }

    public function downloadCampaignBuyerExportExcel($campaign_id) {
        $data = $this->getCampaignDetails($campaign_id, true);
        //return response()->json($data);
        if ( count($data) > 0 ) {
            $price = array_sum(array_column($data['products'], 'price')); 
            $status = 'Unknown';
            if (array_key_exists($data['status'], Product::$PRODUCT_STATUS_1)) {
                $status = Product::$PRODUCT_STATUS_1[$data['status']];
            }
            $blanks = array();
            for($i=0;$i<5;$i++) {
                array_push($blanks,array("\t","\t","\t","\t"));
            }

            $filename = 'campaigns.csv';
            $file = fopen($filename,"w");
            fputcsv($file, array('ADVERSTING MARKETPLACE'));
            foreach ($blanks as $fields) {
                fputcsv($file, $fields);
            }
            fputcsv($file, array('Campaign ID', 'Campaign Name', 'Client Name', 'Contact Number', 'Email', 'Number of Products', 'Date', 'Total Price', 'Paid', 'Status'));
            fputcsv($file, 
                array(
                    $data['cid'], 
                    $data['name'], 
                    $data['first_name']." ".$data['last_name'], 
                    $data['phone'], 
                    $data['email'], 
                    count($data['products']), 
                    $data['endDate'], 
                    "$".number_format($price, 2), 
                    "$".number_format($data['total_paid'], 2), 
                    $status
                )
            );
            foreach ($blanks as $fields) {
                fputcsv($file, $fields);
            }

            $product_types = $this->unique_multidim_array($data['products'],'type');

            foreach ($product_types as $key => $value) {
                fputcsv($file, array($value));
                fputcsv($file, array('siteNo', 'Title', 'Address', 'State', 'Facing', 'Product Direction', 'Size', 'Length', 'Impressions', 'CPM', 'Starting Date', 'Ending Date', 'Price in $', 'Status'));
                
                for ($s=0; $s < count($data['products']); $s++) { 
                    if ( $value == $data['products'][$s]['type'] ) {                        
                        $length = $data['products'][$s]['length'] != '' ? $data['products'][$s]['length'] : "N/A";

                        $product_status = 'Unknown';                        
                        if (array_key_exists($data['products'][$s]['product_status'], Product::$PRODUCT_STATUS_1)) {
                            $product_status = Product::$PRODUCT_STATUS_1[$data['products'][$s]['product_status']];
                        }
                        fputcsv($file, 
                            array(
                                $data['products'][$s]['siteNo'], 
                                $data['products'][$s]['title'], 
                                $data['products'][$s]['address']." ".$data['products'][$s]['city'], 
                                $data['products'][$s]['state_name'], 
                                $data['products'][$s]['direction'], 
                                $data['products'][$s]['imgdirection'], 
                                $data['products'][$s]['panelSize'], 
                                $length, 
                                number_format($data['products'][$s]['impressionsperselectedDates']), 
                                number_format($data['products'][$s]['cpm'], 2), 
                                date("m-d-Y", strtotime($data['products'][$s]['booked_from'])),
                                date("m-d-Y", strtotime($data['products'][$s]['booked_to'])), 
                                number_format($data['products'][$s]['price'], 2), 
                                $product_status
                            )
                        );
                    }
                }

                foreach ($blanks as $fields) {
                    fputcsv($file, $fields);
                }
            }
            fclose($file);
            header("Content-type: text/csv");
            readfile($filename);
        } else {
            return response()->json(['status' => 0, 'message' => "Something went wrong."]);
        }      
        //return response()->json($data);
    }
    
    public function userDetailsFiltersSearch(Request $request) {  
        ini_set("memory_limit", "800M"); ini_set("max_execution_time", "800");
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo['user_id'])->first();        
        $user_email = $user->email;
        $from_date = ''; $to_date = ''; $final_array = [];

        if (isset($input['from_date']) && !empty($input['from_date'])) {
            $from_date = $input['from_date'];
        }
        if (isset($input['to_date']) && !empty($input['to_date'])) {
            $to_date = $input['to_date'];
        }
        $es_url = env('ES_SERVER_FILTER_USERS');
        $es_index = env('ES_USERS');
        $es_auth_index = env('ES_USERS_AUTH');

        $data_string = array(
            "email" => base64_encode($user_email),
            "index" => $es_index,
            "auth_index" => $es_auth_index,
            "from_date" => $from_date,
            "to_date" => $to_date
        );
        $data = json_encode($data_string);
        $ch = curl_init( $es_url );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch);
        curl_close($ch);
        
        $es_data = json_decode($result, true);
        $es_result = $es_data['data'];
        $searchparam = isset($input['searchparam']) ? $input['searchparam'] : "";

        if ( $searchparam != '' ) {
            $param = strtolower($searchparam);
            if (count($es_result) > 0) {
                foreach ($es_result as $val) { 
                    if ( in_array($param, array('yes','no')) ) {
                        if ($param == 'yes') {
                            $verified_search = $val['verified'] ? true : false;
                        } else {
                            $verified_search = !($val['verified']) ? true : false;
                        }
                    } else {
                        $verified_search = false;
                    }  

                    if ( str_contains(strtolower(trim($val['email'])), $param) || str_contains(strtolower(trim($val['first_name'])), $param) || str_contains(strtolower(trim($val['last_name'])), $param) || str_contains(strtolower(trim($val['phone'])), $param) || str_contains(strtolower(trim($val['company_name'])), $param) || $verified_search ){
                        $final_array[] = $val;
                    }    
                }         
            } else {
                $final_array = [];
            }
        } else {
            $final_array = $es_result;
        }
        
        return response()->json($final_array);
    }
    
    public function productsDetailsFiltersSearch(Request $request) {
        ini_set("memory_limit", "800M"); ini_set("max_execution_time", "800");
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo['user_id'])->first();        
        $user_email = $user->email;
        $from_date = ''; $to_date = ''; $final_array = [];

        if (isset($input['from_date']) && !empty($input['from_date'])) {
            $from_date = $input['from_date'];
        }
        if (isset($input['to_date']) && !empty($input['to_date'])) {
            $to_date = $input['to_date'];
        }
        $es_url = env('ES_SERVER_FILTER_PRODUCTS');
        $es_index = env('ES_PRODUCTS');
        $es_auth_index = env('ES_USERS_AUTH');

        $data_string = array(
            "email" => base64_encode($user_email),
            "index" => $es_index,
            "auth_index" => $es_auth_index,
            "from_date" => $from_date,
            "to_date" => $to_date
        );
        $data = json_encode($data_string);
        $ch = curl_init( $es_url );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch);
        curl_close($ch);
        
        $es_data = json_decode($result, true);
        $es_result = $es_data['data'];
        $searchparam = isset($input['searchparam']) ? $input['searchparam'] : "";

        if ( $searchparam != '' ) {
            $param = strtolower($searchparam);
            if (count($es_result) > 0) {
                foreach ($es_result as $val) { 
                    if ( str_contains(strtolower(trim($val['siteNo'])), $param) || str_contains(strtolower(trim($val['type'])), $param) || str_contains(strtolower(trim($val['title'])), $param) || str_contains(strtolower(trim($val['lat'])), $param) || str_contains(strtolower(trim($val['lng'])), $param) || str_contains(strtolower(trim($val['state'])), $param) || str_contains(strtolower(trim($val['city_name'])), $param) || str_contains(strtolower(trim($val['zipcode'])), $param) || str_contains(strtolower(trim($val['default_price'])), $param) ){
                        $final_array[] = $val;
                    }    
                }         
            } else {
                $final_array = [];
            }
        } else {
            $final_array = $es_result;
        }
        
        return response()->json($final_array);
    }
    
    public function campaignDetailsFiltersSearch(Request $request) {
        ini_set("memory_limit", "800M"); ini_set("max_execution_time", "800");
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo['user_id'])->first();        
        $user_email = $user->email;
        $from_date = ''; $to_date = ''; $final_array = [];

        if (isset($input['from_date']) && !empty($input['from_date'])) {
            $from_date = $input['from_date'];
        }
        if (isset($input['to_date']) && !empty($input['to_date'])) {
            $to_date = $input['to_date'];
        }
        $es_url = env('ES_SERVER_FILTER_CAMPAIGNS');
        $es_index = env('ES_CAMPAIGNS');
        $es_auth_index = env('ES_USERS_AUTH');

        $data_string = array(
            "email" => base64_encode($user_email),
            "index" => $es_index,
            "auth_index" => $es_auth_index,
            "from_date" => $from_date,
            "to_date" => $to_date
        );
        $data = json_encode($data_string);
        $ch = curl_init( $es_url );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch);
        curl_close($ch);
        
        $es_data = json_decode($result, true);
        $es_result = $es_data['data'];
        $searchparam = isset($input['searchparam']) ? $input['searchparam'] : "";

        if ( $searchparam != '' ) {
            $param = strtolower($searchparam);
            if (count($es_result) > 0) {
                $campaign_status_array = array(
                    "Campaign Preparing",
                    "Campaign Created",
                    "Quote Created",
                    "Quote Given",
                    "Change Requested",
                    "Requested",
                    "Scheduled",
                    "Running",
                    "Suspended",
                    "Stopped",
                    "Deleted",
                    "RFP Campaign"
                );
                foreach ($es_result as $val) { 
                    if ( in_array(ucwords($param), $campaign_status_array) ) {
                        $status_key = 0;
                        foreach(Campaign::$CAMPAIGN_STATUS_1 as $k1 => $v1) {
                            if ( ucwords($param) == $v1 ) {
                                $status_key = $k1;
                            }
                        }
                        if ( $status_key != 0 ) {
                            if ( $status_key == $val['status'] ) { 
                                $campaign_status_search = true;
                            } else {
                                $campaign_status_search = false;
                            }
                        } else {
                            $campaign_status_search = false;
                        }

                    } else {
                        $campaign_status_search = false;
                    }  

                    if ( str_contains(strtolower(trim($val['cid'])), $param) || str_contains(strtolower(trim($val['name'])), $param) || $campaign_status_search ){
                        $final_array[] = $val;
                    }    
                }         
            } else {
                $final_array = [];
            }
        } else {
            $final_array = $es_result;
        }

        return response()->json($final_array);
    }
	
	public function filterProductsReportDownload1(Request $request) {
        ini_set("memory_limit", "800M"); ini_set("max_execution_time", "800");
        $products_arr = [];
        $product_details_pdf = ['product_details' => $products_arr];
        $pdf = PDF::loadView('pdf.products_pdf1', $product_details_pdf);
        if (!empty($pdf)) {
            return $pdf->download("products.pdf");
        }
    }
	
	public function getOwnerCounts(){
          $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
          $prodcuts = Product::where('client_mongo_id', '=', $user_mongo['client_mongo_id'])->count(); 
          $static_count = Product::where('type', '=', 'Static')->where('client_mongo_id', '=', $user_mongo['client_mongo_id'])->count(); 
          $digital_count = Product::where('type', '=', 'Digital')->where('client_mongo_id', '=', $user_mongo['client_mongo_id'])->count(); 
          $digital_static_count = Product::where('type', '=', 'Digital/Static')->where('client_mongo_id', '=', $user_mongo['client_mongo_id'])->count(); 
          $media_count = Product::where('type', '=', 'Media')->where('client_mongo_id', '=', $user_mongo['client_mongo_id'])->count(); 
		  
		  $owner_prodcuts_campaigns_count = Campaign::where('client_mongo_id', '=', $user_mongo['client_mongo_id'])->count();
		  $owner_campaigns_count = Campaign::where('client_mongo_id', '=', $user_mongo['client_mongo_id'])->count();
		  $bulk_upload_count = BulkUpload::where('client_mongo_id', '=', $user_mongo['client_mongo_id'])->count();
		  //$notifications_count = Notification::count();    
		  $notifications_count = Notification::where('to_client', '=', $user_mongo['client_mongo_id'])->count();
		  //echo '<pre>';print_r($notifications_count);exit;
		$campaign_product_ids = Product::where('client_mongo_id', '=', $user_mongo['client_mongo_id'])->get();
          
        $findForMe_count = FindForMe::count();   

        $redCout = 0; $blackCount = 0;
        $campaign_product_ids = ProductBooking::where('product_owner', '=', $user_mongo['client_mongo_id'])->pluck('campaign_id')->toArray();

        $campaign_feeds = Campaign::raw(function($collection) use ($campaign_product_ids) {
            return $collection->find([
                                '$and' => [
                                    ['id' => ['$in' => $campaign_product_ids]],
                                ]
                                    ], [
                                'sort' => [
                                    'updated_at' => -1
                                ]
                                    ]
                    );
        });
                
        $current_date = date('d-m-Y');
        $colorcode = '';
        $diff = '';  
        
        foreach ($campaign_feeds as $ocf) {
            $product_start_date = ProductBooking::where('campaign_id', '=', $ocf->id)->select("booked_from","booked_to")->orderBy('booked_from', 'asc')->first();
            if (!empty($product_start_date)) {
                $ocf->start_date = $product_start_date->booked_from;
                $ocf->end_date = $product_start_date->booked_to;
                $diff=date_diff(date_create($current_date),date_create($ocf->start_date));
                if($diff->days <=7 ){
                    $redCout++;
                }else{
                    $blackCount++;
                }
            }            
        }
        $Counts = [
            'Static' => $static_count,
            'Digital' => $digital_count,
            'Digital/Static' => $digital_static_count,
            'Media' => $media_count,
            'TotalProducts' => $static_count + $digital_count + $digital_static_count + $media_count,
            'Campaigns' => $owner_prodcuts_campaigns_count,
            'Find' => $findForMe_count,
            'Notices' => $notifications_count,
            'MyCampaigns' => $owner_campaigns_count,
            'BulkUploads' => $bulk_upload_count,
            'TodoRed' => $redCout,
            'TodoBlack' => $blackCount
        ];
        return response()->json($Counts);
    }

    public function getBuyerCounts(){
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        //print_r($user_mongo);exit();
		$current_date = date_create(date("d-m-Y"));
        $requested_campaigns_count = Campaign::where('status', '=', Campaign::$CAMPAIGN_STATUS['booking-requested'])->where('created_by', '=', $user_mongo['id'])->count();
        $scheduled_campaigns = Campaign::where('status', '=', Campaign::$CAMPAIGN_STATUS['scheduled'])->where('created_by', '=', $user_mongo['id'])->get();
		$scheduled_campaigns_count = 0;
		if(isset($scheduled_campaigns)){
			$arr_scheduled_campaigns = $scheduled_campaigns->toArray();
			$arr_product_booking = array();
			foreach($arr_scheduled_campaigns as $key => $val){
				$arr_product_booking = ProductBooking::where('booked_to', '>', $current_date)->where('booked_from', '>', $current_date)->where('campaign_id', '=', $val['id'])->get()->toArray();
				if(isset($arr_product_booking) && !empty($arr_product_booking)){
					$scheduled_campaigns_count = $scheduled_campaigns_count+1;
				}
			}
		}
        $running_campaigns = Campaign::where('status', '=', Campaign::$CAMPAIGN_STATUS['running'])->where('created_by', '=', $user_mongo['id'])->get();
		$running_campaigns_count = 0;
		if(isset($running_campaigns)){
			$arr_running_campaigns = $running_campaigns->toArray();
			$arr_product_booking_running = array();
			foreach($arr_running_campaigns as $key => $val){
				$arr_product_booking_running = ProductBooking::where('booked_to', '>=', $current_date)->where('booked_from', '<=', $current_date)->where('campaign_id', '=', $val['id'])->get()->toArray();
				if(isset($arr_product_booking_running) && !empty($arr_product_booking_running)){
					$running_campaigns_count = $running_campaigns_count+1;
				}
			}
		}
        $closed_campaigns = Campaign::where('status', '=', Campaign::$CAMPAIGN_STATUS['stopped'])->orwhere('status', '=', Campaign::$CAMPAIGN_STATUS['deleted-cancelled'])->orwhere('status', '=', Campaign::$CAMPAIGN_STATUS['scheduled'])->orwhere('status', '=', Campaign::$CAMPAIGN_STATUS['suspended'])->where('created_by', '=', $user_mongo['id'])->get();
		$closed_campaigns_count = 0;
		if(isset($closed_campaigns)){
			$arr_closed_campaigns = $closed_campaigns->toArray();
			$arr_product_booking_closed = array();
			foreach($arr_closed_campaigns as $key => $val){
				$arr_product_booking_closed = ProductBooking::where('booked_to', '<', $current_date)->where('campaign_id', '=', $val['id'])->get()->toArray();
				if(isset($arr_product_booking_closed) && !empty($arr_product_booking_closed)){
					$closed_campaigns_count = $closed_campaigns_count+1;
				}
			}
		}
        $saved_campaigns_count = Campaign::where('status', '=', Campaign::$CAMPAIGN_STATUS['campaign-preparing'])->orWhere('status', '=', Campaign::$CAMPAIGN_STATUS['rfp-campaign'])->where('created_by', '=', $user_mongo['id'])->count();

        $offer_request = MakeOffer::where('status', '=', 10)->where('created_by', '=', $user_mongo['id'])->count(); 
        $offer_accept = MakeOffer::where('status', '=', 20)->where('created_by', '=', $user_mongo['id'])->count(); 
        $offer_reject = MakeOffer::where('status', '=', 40)->where('created_by', '=', $user_mongo['id'])->count();
          
        $cart_count = ShortListedProduct::where('user_mongo_id', '=', $user_mongo['id'])->count();
        
        $buyer_campaigns_count = Campaign::where('created_by', '=', $user_mongo['id'])->count();
        
        $payments_count = Campaign::where('status', '>=', Campaign::$CAMPAIGN_STATUS['booking-requested'])->where('created_by', '=', $user_mongo['id'])->count();
        $cancellations_count = Campaign::where('status', '=', Campaign::$CAMPAIGN_STATUS['deleted-cancelled'])->where('created_by', '=', $user_mongo['id'])->count();
        $notifications_count = Notification::where('to_client', '=', $user_mongo['id'])->count();
        $rfp_without_count = Campaign::where('status', '=', Campaign::$CAMPAIGN_STATUS['rfp-campaign'])->where('created_by', '=', $user_mongo['id'])->count(); 

        $Counts = [
            'Cart' => $cart_count,
            'RFPBeforeLogin' => $rfp_without_count,
            'SavedCampaigns' => $saved_campaigns_count,
            'RequestedOffers' => $offer_request,
            'AcceptedOffers' => $offer_accept,
            'RejectedOffers' => $offer_reject,
            'RequestedCampaigns' => $requested_campaigns_count,
            'ScheduledCampaigns' => $scheduled_campaigns_count,
            'RunningCampaigns' => $running_campaigns_count,
            'ClosedCampaigns' => $closed_campaigns_count,
            'Notices' => $notifications_count,
            'MyCampaigns' => $buyer_campaigns_count,
            'Payments' => $payments_count,
            'Cancellations' => $cancellations_count
        ];
        return response()->json($Counts);
    }

    public function getProductStatusInCampaign($campaign_id){
        //$campaign_id='111';
        $expired_status='222';

        $user_mongo_jwt = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo_jwt['user_id'])->first();
		//echo 'user_mongo_jwt';print_r($user_mongo_jwt);exit;
		if (!isset($user->client) || empty($user->client)) {
            //echo 'client';exit;
            $campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        ['created_by', '=', $user_mongo_jwt['id']],
                    ])->first();
                    //echo 'clients';print_r($campaign);exit;
        } else if ($user->client->client_type->type == "bbi") {
            //echo 'bbi';exit;
            $campaign = Campaign::where('id', '=', $campaign_id)->first();
            //echo 'client';print_r($campaign);exit;
        } else if ($user->client->client_type->type == "owner") {
            //echo 'owner';exit;
            $campaign = Campaign::where([
                        ['id', '=', $campaign_id],
                        ['created_by', '=', $user_mongo_jwt['id']],
                    ])->first();
                    //echo 'client';print_r($campaign);exit;
        } else {
            //echo 'no campaign';exit;
            return response()->json(['status' => 0, 'message' => 'Campaign not found']);
        }
		//echo 'user';print_r($user);exit;
		//echo 'campaign';print_r($campaign);exit;
		$user_mongo = UserMongo::where('id', '=', $campaign->created_by)->first();
        //echo 'ususer_mongoer';print_r($user_mongo);exit;
        $campaign->first_name = $user_mongo->first_name;
        $campaign->last_name = $user_mongo->last_name;
        $campaign->email = $user_mongo->email;
        $campaign->phone = $user_mongo->phone;
        $campaign_products = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
		//echo 'campaign_products';print_r($campaign_products);exit;
        $products_arr = [];
		if (isset($campaign_products) && count($campaign_products) > 0) {
            foreach ($campaign_products as $campaign_products) {
                $getcampaignproductstatus =Product::where('id', '=', $campaign_products->product_id)->first();
                 $bookedfrom = strtotime($campaign_products->booked_from);
                 $bookedto = strtotime($campaign_products->booked_to);

                 $current_time_obj = new \MongoDB\BSON\UTCDateTime();
                 $current_time = array_values(get_object_vars($current_time_obj)); 
                 $timediff = ($current_time[0] - $bookedto);  
                 //$bookedto_expiry_time = array_values(get_object_vars($bookedto));
                 //echo 'current_time';print_r($current_time[0]);//exit;
                 $string = substr($current_time[0], 0, -3); // removing last 3 digits from time
                 //echo 'current_time minus';print_r($string);
                 //if($bookedto < $current_time[0]){
                 if($bookedto < $string){
                    $campaign_products->is_product_expired = 'Product Expired';
                 }else{
                    $campaign_products->is_product_expired = 'Product Not Expired';
                 }
                 //echo 'bookedto';print_r($bookedto);//exit;
                 //echo 'bookedfrom';print_r($bookedfrom);//exit;

                 //echo 'campaign_products';print_r($campaign_products);exit;
                 
                //echo 'bookedfrom'.$bookedfrom = strtotime($getcampaigntot->booked_from);
                //echo 'bookedto'.$bookedto = strtotime($getcampaigntot->booked_to);
                array_push($products_arr, array_merge(Product::where('id', '=', $campaign_products->product_id)->first()->toArray(), $campaign_products->toArray()));
			}
		}
        //echo 'campaign_products';print_r($campaign_products);exit;
        //echo 'products_arr';print_r($products_arr);exit;
        $campaign_products->products = $products_arr;
        //$search = array_search("Product Expired", array_column($campaign_products, 'product_expired_status'));
        // if (array_search('Product Expired', $campaign_products)) {
        //     echo "There are Expired Products";
        // }else{
        //     echo "There are No Expired Products";
        // }
        //echo 'search';print_r($search);exit;
        //$value = array_values(get_object_vars($campaign_products));
        $value = ($campaign_products->toArray());
        //echo 'value';print_r($value);exit;
        if(in_array('Product Expired', $value)){
            //echo "There are Expired Products";
            $expired_status = "There are Expired Products";
        }else{
            //echo "There are No Expired Products";
            $expired_status = "There are No Expired Products";
        }
        //echo 'campaign_products';print_r($campaign_products);exit;
//exit;
        return response()->json(["campaign_id"=>$campaign_id, "campaign_products"=>$campaign_products,"expired_status"=>$expired_status]);
    }
	
		
	// to delete sold out product from campaign
	public function  getDeleteSoldoutProductCampaign($campaign_id = '',$product_id = ''){
		$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
		if($campaign_id == ''|| $product_id == ''){
			return response()->json(["status" => "0", "message" => "Mandatory parameters are missing."]);
		}
		
		$product_to_del = ProductBooking::where('campaign_id', '=', $campaign_id)->where('product_id', '=', $product_id)->first();
		if(isset($product_to_del) && !empty($product_to_del)){
			$success = $product_to_del->delete();
            if ($success) {
                return response()->json(["status" => "1", "message" => "Product deleted successfully."]);
            } else {
                return response()->json(["status" => "1", "message" => "An error occured while deleting the product."]);
            }
		}else{
			return response()->json(["status" => "0", "message" => "Failed to delete product from campaign."]);
		}
	}
	
	// RFP without login status Read and Unread
	
	public function updateRFPStatus(Request $request)
	{
		$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo['user_id'])->first();
		
		if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
		$rfp_id = $input['rfp_ids'];
		foreach($rfp_id as $rfp_id)
		{
			$notification_Array = Campaign::where('id', '=',$rfp_id)->first();
			if(!empty($notification_Array)){
				$notification_Array->status_read=1;
				$notification_Array->save();
			}
		}
		return response()->json(["status" => "1", "message" => "RFP Read Successfully"]);
	}
	
	//ToDo List status Read and Unread  
	
	public function updateToDoListStatus(Request $request)
	{
		$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user = User::where('id', '=', $user_mongo['user_id'])->first();
		
		if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
		$todolist_id = $input['todolist_id'];
			$todo_list = Campaign::where('id', '=',$todolist_id)->first();
				$todo_list->todolist_read=1;
				$todo_list->save();
		return response()->json(["status" => "1", "message" => "ToDo List Read Successfully"]);
	}
	
	// To update dates and quantity to a saved campaign record.
	public function updateCampaignProductDateQuantity(Request $request){
		$this->validate($request, [
            'campaign_id' => 'required',
            'booking_id' => 'required',
            'product_id' => 'required',
            'dates' => 'required',
            'quantity' => 'required'
                ], [
            'campaign_id.required' => 'Campaign id is required',
            'booking_id.required' => 'Booking id is required',
            'product_id.required' => 'Product id is required',
            'dates.required' => 'Please provide dates you\'re selecting the product for',
            'quantity.required' => 'Quantity is required'
                ]
        );
		
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
		
		$var_booking_id = $input['booking_id'];
		
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $overlapping_dates = [];
		
		$product_occurances = ProductBooking::where([
					['campaign_id', '=', $input['campaign_id']],
					['product_id', '=', $input['product_id']],
					['id', '!=', $input['booking_id']]
				])->get();

		if(isset($product_occurances)){
			foreach ($product_occurances as $po) {
				if ($po->booked_from <= $input['dates'][0]['endDate'] && $po->booked_to >= $input['dates'][0]['startDate']) {
					array_push($overlapping_dates, $input['dates']);
				}
			}
		}
			
        if (count($overlapping_dates) == 0) {
				$success = true;
				
				$product = Product::where('id', '=', $input['product_id'])->first();
				
				$sl_product_obj = New ProductBooking;
				$sl_product_obj = ProductBooking::where([
					['id', '=', $input['booking_id']],
					['product_id', '=', $input['product_id']]
				])->first();
				$sl_product_obj->booked_from = iso_to_mongo_date($input['dates'][0]['startDate']);
				$sl_product_obj->booked_to = iso_to_mongo_date($input['dates'][0]['endDate']);
				
				$diff=date_diff(date_create($input['dates'][0]['endDate']),date_create($input['dates'][0]['startDate']));
				$daysCount = $diff->format("%a");
				$perdayprice = $product->default_price/28;
				if(isset($product->fix) && $product->fix=="Fixed"){
					$price =0;
					if(($daysCount+1) <= $product->minimumdays){
						$price = $perdayprice * $product->minimumdays;
					}else{
						$y  = 1;
						$z = 'aa';
						for ($x = 1; $x <= $y; $x++) {
							if(($product->minimumdays*$x) >= ($daysCount+1) && $z == 'aa'){
								$price = $perdayprice*($product->minimumdays*$x);
								$z = 'bb';
								$y++;
							}if($z == 'aa'){
								$y++;
							}
						}
					}
				}else{
					if(($daysCount+1) <= $product->minimumdays){
						$price = $perdayprice * $product->minimumdays;
					}else{
						$price = $perdayprice * ($daysCount+1);
					}
				}
				$sl_product_obj->price = $price;
				
				//$rand = substr(str_shuffle(str_repeat("ABCDEFGHJKLMNPQRSTUVWXYZ", 3)), 0, 3);
				//$group_id ="AMP".date('Ymd').$rand;
				//if($sl_product_obj->quantity != $input['quantity']){	
				//	$sl_product_obj->group_slot_id = $group_id.$product->id;
				//}
				
				$sl_product_obj->quantity = isset($input['quantity']) ? $input['quantity'] : "1";
				if (!$sl_product_obj->save()) {
					$success = false;
				}
				if ($success) {
					$product = Product::where('id', '=', $input['product_id'])->first();
					foreach ($input['dates'] as $key => $dates_val) {
                        if($key != 0){
							$alreadyCampaignProd = new ProductBooking;
							$alreadyCampaignProd = ProductBooking::where([
										['campaign_id', '=', $input['campaign_id']],
										['product_id', '=', $product->id],
										['booked_from', '<=', iso_to_mongo_date($dates_val['endDate'])],
										['booked_to', '>=', iso_to_mongo_date($dates_val['startDate'])]
									])->first();
							if (isset($alreadyCampaignProd) && !empty($alreadyCampaignProd)) {
								//return response()->json(["status" => "0", "message" => "This product is already added in this campaign."]);
								$productBooked = ProductBooking::where('product_id', '=',  $product->id)->where('quantity', '!=',  '')->get()->toArray();
								$available_quantity = $product->unitQty;
								if(!empty($productBooked)){
									$productBooked_last = ProductBooking::select("quantity","campaign_id","id")->where('product_id', '=',  $product->id)->where('booked_from','<=',iso_to_mongo_date($dates_val['endDate']))->where('booked_to','>=',iso_to_mongo_date($dates_val['startDate']))->where('product_status','!=',100)->where('product_status','!=',400)->where('product_status','!=',700)->where('quantity', '!=',  '')->groupBy('group_slot_id')->get([DB::raw('MAX(quantity) as quantity')])->toArray();
									$sum_quantity = 0;
									if(!empty($productBooked_last)){
										foreach($productBooked_last as $key => $value){
											$delete_product_status = DeleteProduct::where([
																				['campaign_id', '=', $value['campaign_id']],
																				['status', '=', DeleteProduct::$PRODUCT_STATUS['confirm-delete-product-from-campaign']],
																			])->whereIn('product_id', array($product->id))->whereIn('productbookingid', array($value['id']))->orderBy('created_at', 'desc')->first();
											if($value['campaign_id'] != ''){
												$campaign_delete = Campaign::select("status")->where('id', '=', $value['campaign_id'])->first();
												if(empty($delete_product_status) && ($campaign_delete->status != 1200)){
													$sum_quantity += $value['quantity'];
												}
											}
										}
									}
									$available_quantity = $product->unitQty-$sum_quantity;
									if($available_quantity >= 0){
										$available_quantity = $available_quantity;
									}else{
										$available_quantity = 0;
									}
								}
								$alreadyCampaignProd_quantity = isset($input['quantity']) ? $alreadyCampaignProd->quantity+$input['quantity'] : $alreadyCampaignProd->quantity+1;
								if($alreadyCampaignProd_quantity <= $available_quantity){
									$final_add_quantity = $alreadyCampaignProd_quantity;
								}else{
									$final_add_quantity = $available_quantity;
								}
								//$rand_exist = substr(str_shuffle(str_repeat("ABCDEFGHJKLMNPQRSTUVWXYZ", 3)), 0, 3);
								//$group_id_exist ="AMP".date('Ymd').$rand_exist;
								//if($alreadyCampaignProd->quantity != $final_add_quantity){	
								//	$alreadyCampaignProd->group_slot_id = $group_id_exist.$product->id;
								//}
								$alreadyCampaignProd->quantity = $final_add_quantity;
								//$alreadyCampaignProd->group_slot_id = $group_id_exist.$product->id;
								$alreadyCampaignProd->save();
							}else{
								$diff=date_diff(date_create($dates_val['endDate']),date_create($dates_val['startDate']));
								$daysCount = $diff->format("%a");
								$perdayprice = $product->default_price/28;
								if(isset($product->fix) && $product->fix=="Fixed"){
									$price =0;
									if(($daysCount+1) <= $product->minimumdays){
										$price = $perdayprice * $product->minimumdays;
									}else{
										$y  = 1;
										$z = 'aa';
										for ($x = 1; $x <= $y; $x++) {
											if(($product->minimumdays*$x) >= ($daysCount+1) && $z == 'aa'){
												$price = $perdayprice*($product->minimumdays*$x);
												$z = 'bb';
												$y++;
											}if($z == 'aa'){
												$y++;
											}
										}
									}
								}else{
									if(($daysCount+1) <= $product->minimumdays){
										$price = $perdayprice * $product->minimumdays;
									}else{
										$price = $perdayprice * ($daysCount+1);
									}
								}
								
								$new_booking = new ProductBooking;
								$new_booking->id = uniqid();
								$new_booking->campaign_id = $input['campaign_id'];
								$new_booking->product_id = $input['product_id'];
								$new_booking->booked_from = iso_to_mongo_date($dates_val['startDate']);
								$new_booking->booked_to = iso_to_mongo_date($dates_val['endDate']);
								$new_booking->price = $price;
								$new_booking->product_owner = $product->client_mongo_id;
								$new_booking->product_status = ProductBooking::$PRODUCT_STATUS['proposed'];
								$new_booking->quantity = isset($input['quantity']) ? $input['quantity'] : "1";
								$new_booking->group_slot_id = $sl_product_obj->group_slot_id;
								$new_booking->save();
							}
						}
                    }
					return response()->json(["status" => "1", "message" => "Product is updated."]);
				} else {
					return response()->json(["status" => "0", "message" => "Product could not be updated"]);
				}
		} else {
            return response()->json(["status" => "0", "message" => "The dates for selected product(s) is already shortlisted.", "overlapping_dates" => $overlapping_dates]);
        }
		
	}



    public function getContracts() {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user_id = $user_mongo['user_id'];   
        $user = User::where('id', '=', $user_id)->first();
        $um = UserMongo::where('id', '=', $user_mongo['id'])->first();
        $user_type = $um->company_type;
        if ( $user_type == 'bbi' ) {
            $contracts_data1 = Contracts::orderBy('updated_at', 'desc')->get();
        } else {
            $contracts_data1 = Contracts::where('user_id', '=', $user_id)->orderBy('updated_at', 'desc')->get();
        }
        
        $contracts_data = [];
        foreach ($contracts_data1 as $key => $value) {
            $user1 = User::where('id', '=', $value->user_id)->first();
            $um1 = UserMongo::where('user_id', '=', $value->user_id)->first();
            $user_type1 = '';
            if ($um1->company_type == 'owner') {
                $user_type1 = 'Seller';
            } else if ($um1->company_type == '') {
                $user_type1 = 'Buyer';
            } else if ($um1->company_type == 'bbi') {
                $user_type1 = 'Admin';
            }  

            $value->user_id = $value->user_id;
            $value->email = $user1->email;
            $value->user_type = $user_type1;
            $value->title = $value->title;
            $value->created_at = $value->created_at;
            $value->file_name = $value->file_name;
            $value->file = '/contracts/'.$value->file_name;
            $contracts_data[] = $value;
        }
        $contracts_list = ["contracts_data" => $contracts_data];
        return response()->json($contracts_list);
    }

    public function saveContracts(Request $request) {
        /*Contracts::where([
            ['id', '!=', 1]
        ])->delete();
        exit();*/ 
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user_id = $user_mongo['user_id'];

        $pdfFilePath = base_path() . '/html/contracts';
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }

        if (isset($input['title'])) {
            if ($input['title'] != '') {                
            } else {
                return response()->json(["status" => "0", "message" => "Title Is Required."], 400);
            }
        } else {
            return response()->json(["status" => "0", "message" => "PDF Title Is Required."], 400);
        }

        $title = $input['title'];

        $file_type_check = gettype($request->file('file'));
        if ($file_type_check == 'object') {
            $files_array[] = $request->file('file');
        } else if ($file_type_check == 'array') {
            $files_array = $request->file('file');
        } else {
            return response()->json(["status" => "0", "message" => "PDF File Is Missing OR Incorrect File Type."], 400);
        }

        if ( count($files_array) != 1 ) {
            return response()->json(["status" => "0", "message" => "Only One PDF File Is Required."], 400);
        }

        if ($request->hasFile('file')) {

            $val = $files_array[0];           
            
            $ext = $val->getClientOriginalExtension();
            if ( strtolower($ext) != 'pdf' ) {
                $errors[] = "File Type Is Invalid! File Type Must Be pdf Only.";
                return response()->json(["status" => "0", "message" => $errors], 400);
            }

            $msg = 'Contract Created Successfully.';

            $pdfFileName = \Carbon\Carbon::now()->timestamp . ".pdf";
            if ($val->move($pdfFilePath, $pdfFileName)) {
                $product_img = new Contracts; 
                $product_img->id = uniqid();
                $product_img->title = $title; 
                $product_img->user_id = $user_id; 
                $product_img->file_name = $pdfFileName; 

                $product_img->save();
            } else {
                $msg = 'Errors';
            }

            return response()->json(["status" => "1", "message" => $msg]);
        } else {
            return response()->json(["status" => "0", "message" => "PDF File Is Required."], 400);
        }
    }

    public function getInvoices() {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user_id = $user_mongo['user_id'];   
        $user = User::where('id', '=', $user_id)->first();
        $um = UserMongo::where('id', '=', $user_mongo['id'])->first();
        $user_type = $um->company_type;
        if ( $user_type == 'bbi' ) {
            $invoices_data1 = Invoices::orderBy('updated_at', 'desc')->get();
        } else {
            $invoices_data1 = Invoices::where('user_id', '=', $user_id)->orderBy('updated_at', 'desc')->get();
        }
        $invoices_data = [];
        foreach ($invoices_data1 as $key => $value) {
            $user1 = User::where('id', '=', $value->user_id)->first();
            $um1 = UserMongo::where('user_id', '=', $value->user_id)->first();
            $user_type1 = '';
            if ($um1->company_type == 'owner') {
                $user_type1 = 'Seller';
            } else if ($um1->company_type == '') {
                $user_type1 = 'Buyer';
            } else if ($um1->company_type == 'bbi') {
                $user_type1 = 'Admin';
            }  

            $value->user_id = $value->user_id;
            $value->email = $user1->email;
            $value->user_type = $user_type1;
            $value->title = $value->title;
            $value->created_at = $value->created_at;
            $value->file_name = $value->file_name;
            $value->file = '/invoices/'.$value->file_name;
            $invoices_data[] = $value;
        }
        $invoices_list = ["invoices_data" => $invoices_data];
        return response()->json($invoices_list);
    }

    public function saveInvoices(Request $request) {
        /*Contracts::where([
            ['id', '!=', 1]
        ])->delete();
        exit();*/ 
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user_id = $user_mongo['user_id'];

        $pdfFilePath = base_path() . '/html/invoices';
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }

        if (isset($input['title'])) {
            if ($input['title'] != '') {                
            } else {
                return response()->json(["status" => "0", "message" => "Title Is Required."], 400);
            }
        } else {
            return response()->json(["status" => "0", "message" => "PDF Title Is Required."], 400);
        }

        $title = $input['title'];

        $file_type_check = gettype($request->file('file'));
        if ($file_type_check == 'object') {
            $files_array[] = $request->file('file');
        } else if ($file_type_check == 'array') {
            $files_array = $request->file('file');
        } else {
            return response()->json(["status" => "0", "message" => "PDF File Is Missing OR Incorrect File Type."], 400);
        }

        if ( count($files_array) != 1 ) {
            return response()->json(["status" => "0", "message" => "Only One PDF File Is Required."], 400);
        }

        if ($request->hasFile('file')) {

            $val = $files_array[0];           
            
            $ext = $val->getClientOriginalExtension();
            if ( strtolower($ext) != 'pdf' ) {
                $errors[] = "File Type Is Invalid! File Type Must Be pdf Only.";
                return response()->json(["status" => "0", "message" => $errors], 400);
            }

            $msg = 'Invoice Created Successfully.';

            $pdfFileName = \Carbon\Carbon::now()->timestamp . ".pdf";
            if ($val->move($pdfFilePath, $pdfFileName)) {
                $product_img = new Invoices; 
                $product_img->id = uniqid();
                $product_img->title = $title; 
                $product_img->user_id = $user_id; 
                $product_img->file_name = $pdfFileName; 

                $product_img->save();
            } else {
                $msg = 'Errors';
            }

            return response()->json(["status" => "1", "message" => $msg]);
        } else {
            return response()->json(["status" => "0", "message" => "PDF File Is Required."], 400);
        }
    }

    public function deleteContracts(Request $request) {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        $id = isset($input['id']) ? $input['id'] : "";
        if ($id != '') {
            $contract_data = Contracts::where('id', $id)->first();             
            if (count($contract_data) == 1) {
                $unlink_file_path = base_path() . '/html/contracts/'.$contract_data->file_name;
                if ( is_file($unlink_file_path) ) {
                    unlink($unlink_file_path);
                }

                Contracts::where([
                    ['id', $id]
                ])->delete();

                return response()->json(["status" => "1", "message" => "Contract Deleted Successfully."]);
            } else {
                return response()->json(["status" => "0", "message" => "Record id not found or invalid record."], 400);
            } 
        } else {
            return response()->json(["status" => "0", "message" => "Record id is required."], 400);
        }        
    }    

    public function deleteInvoices(Request $request) {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        $id = isset($input['id']) ? $input['id'] : "";
        if ($id != '') {
            $invoice_data = Invoices::where('id', $id)->first();             
            if (count($invoice_data) == 1) {
                $unlink_file_path = base_path() . '/html/invoices/'.$invoice_data->file_name;
                if ( is_file($unlink_file_path) ) {
                    unlink($unlink_file_path);
                }

                Invoices::where([
                    ['id', $id]
                ])->delete();

                return response()->json(["status" => "1", "message" => "Invoice Deleted Successfully."]);
            } else {
                return response()->json(["status" => "0", "message" => "Record id not found or invalid record."], 400);
            } 
        } else {
            return response()->json(["status" => "0", "message" => "Record id is required."], 400);
        }
    }

    public function downloadContracts(Request $request) {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        $id = isset($input['id']) ? $input['id'] : "";
        if ($id != '') {
            $contract_data = Contracts::where('id', $id)->first();             
            if (count($contract_data) == 1) {
                $file_n = '/html/contracts/'.$contract_data->file_name;
                $file = base_path().$file_n;
                return response()->download($file); 
            } else {
                return response()->json(["status" => "0", "message" => "Record id not found or invalid record."], 400);
            } 
        } else {
            return response()->json(["status" => "0", "message" => "Record id is required."], 400);
        }
    }

    public function downloadInvoices(Request $request) {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        $id = isset($input['id']) ? $input['id'] : "";
        if ($id != '') {
            $invoices_data = Invoices::where('id', $id)->first();             
            if (count($invoices_data) == 1) {
                $file_n = '/html/invoices/'.$invoices_data->file_name;
                $file = base_path().$file_n;
                return response()->download($file); 
            } else {
                return response()->json(["status" => "0", "message" => "Record id not found or invalid record."], 400);
            } 
        } else {
            return response()->json(["status" => "0", "message" => "Record id is required."], 400);
        }
    }
	
	// Download Insertion Order PDF
	
	public function downloadInsertionOrder($campaign_id) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $shortlisted_products_arr = [];
        $shortlistedsum = 0;
		$shortlistedsumcpm = 0;
        $offershortlistedsum = 0;
        $cpmsum = 0;
        $offercpmsum = 0;
        $impressionSum = 0;
        $impressionSum4 = 0;
        $newofferStripepercentamtSum = 0;
        $newprocessingfeeamtSum = 0;
        $tax_percentage_amount_sum = 0;
		$tax_percentage_booking = 0;
		$tax_percentage_amount = 0;
		$offershortlistedsumTotal = 0;
		$tax_percentage_amount_tot = 0;
		$tax_percentage_booking_tot = 0;
        try {
            $user = JWTAuth::parseToken()->getPayload()['user'];
            $campaign = Campaign::where('id', '=', $campaign_id)->first();
			$user_mongo_data = UserMongo::where('id', '=', $campaign->created_by)->first();
			$campaign->first_name = $user_mongo_data->first_name;
			$campaign->last_name = $user_mongo_data->last_name;
			$campaign->company_name = $user_mongo_data->company_name;
			$campaign->email = $user_mongo_data->email;
			$campaign->phone = $user_mongo_data->phone;
            //if($campaign->status < 1000){
			if($campaign->status <= 2000){
            $product_ids_in_campaign = ProductBooking::where('campaign_id', '=', $campaign_id)->pluck('product_id');
            $products_in_campaign = Product::whereIn('id', $product_ids_in_campaign)->get();
            $formats = $products_in_campaign->unique('type')->count();
            $areas = $products_in_campaign->unique('area')->count();
            $audience_reach = $products_in_campaign->each(function($v, $k) {
                        $v->impressions = intval(str_replace(",", "", $v->impressions));
                    })->sum('impressions');
            $repeated_audience = $audience_reach * 30 / 100;
            
             $campaign_products = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
        $products_arr = [];
        if (isset($campaign_products) && count($campaign_products) > 0) {
            foreach ($campaign_products as $campaign_product) {
                $product =Product::where('id', '=', $campaign_product->product_id)->first();
                //array_push($products_arr, array_merge(Product::where('id', '=', $campaign_product->product_id)->first()->toArray(), $campaign_product->toArray()));
            }
          
        }

        $getcampaigntot = ProductBooking::where('campaign_id', '=', $campaign_id)->get();
        $campaign->total_paid = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('amount');
        $camptot = 0;
		$offer_applied = 0;
        if (isset($getcampaigntot) && count($getcampaigntot) > 0) {
            foreach ($getcampaigntot as $getcampaigntot) {
				$tax_percentage_booking_tot = 0;
				$tax_percentage_amount_tot = 0;
                $getcampaigntotproduct =Product::where('id', '=', $getcampaigntot->product_id)->first();
                $bookedfrom[] = strtotime($getcampaigntot->booked_from);
                $bookedto[] = strtotime($getcampaigntot->booked_to);

                $getproductDetails =Product::where('id', '=', $getcampaigntot->product_id)->first();
        
                $diff=date_diff(date_create($getcampaigntot->booked_from),date_create($getcampaigntot->booked_to));
                $daysCount = $diff->format("%a");
                $daysCountCPM = $daysCount + 1;
					$perdayprice = $getproductDetails->default_price/28;

                if(isset($getproductDetails->fix) && $getproductDetails->fix=="Fixed"){
                    $price = $getcampaigntot->price*$getcampaigntot->quantity;
					if($campaign->total_paid != 0){
						if(isset($getcampaigntot->tax_percentage)){
							$tax_percentage_booking_tot = $getcampaigntot->tax_percentage;
						}else{
							$tax_percentage_booking_tot = 0;
						}
					}else{
						$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
					}
					if($tax_percentage_booking_tot != 0){
						$tax_percentage_amount_tot = ($price * ($tax_percentage_booking_tot))/100;
					}
					$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
					$camptot += $price+$tax_percentage_amount_tot;
                }else{
                    /*$price = $getproductDetails->default_price;
                    $priceperday = $price/28;
                    $priceperselectedDates = $priceperday * $daysCountCPM;
                    $camptot += $priceperselectedDates;*/ 
					
					$priceperselectedDates = $getcampaigntot->price;
					if($campaign->total_paid != 0){
						if(isset($getcampaigntot->tax_percentage)){
							$tax_percentage_booking_tot = $getcampaigntot->tax_percentage;
						}else{
							$tax_percentage_booking_tot = 0;
						}
					}else{
						$tax_percentage_booking_tot = $getproductDetails->tax_percentage;
					}
					if($tax_percentage_booking_tot != 0){
						$tax_percentage_amount_tot = ($priceperselectedDates * ($tax_percentage_booking_tot))/100;
					}
					$tax_percentage_amount_tot = round($tax_percentage_amount_tot,2); 
					$camptot += ($priceperselectedDates*$getcampaigntot->quantity)+($tax_percentage_amount_tot*$getcampaigntot->quantity);
                }
            }
        }
		if($user_mongo['user_type'] == 'owner'){
            $campaignproducts = ProductBooking::where([
                    ['campaign_id', '=', $campaign_id],
                    ['product_owner', '=', $user_mongo['client_mongo_id']]
                ])->orderBy('booked_from','ASC')->orderBy('booked_to','ASC')->get();
		}else{
			$campaignproducts = ProductBooking::where('campaign_id', '=', $campaign_id)->orderBy('booked_from','ASC')->orderBy('booked_to','ASC')->get();
		}
        
        if (isset($campaignproducts) && count($campaignproducts) > 0) {
            
            foreach ($campaignproducts as $campaignproduct) {
				$campaignproduct->tax_percentage_amount_shortlist = 0;
				$campaignproduct->tax_percentage_amount = 0;
				$tax_percentage_booking = 0;
				$tax_percentage_amount = 0;
                $getproductDetails =Product::where('id', '=', $campaignproduct->product_id)->first();
                //echo "<pre>campaignproduct"; print_r($campaignproducts);exit;
                $product = Product::where('id', '=', $campaignproduct->product_id)->first();
				
				$area_time_zone_value = Area::select("area_time_zone","area_time_zone_type")->where('id', '=', $product->area)->first();
				$campaignproduct->area_time_zone_type = $area_time_zone_value['area_time_zone_type'];
                
                /*CPM Calculation*/
                $diff=date_diff(date_create($campaignproduct->booked_from),date_create($campaignproduct->booked_to));
                //$diff=date_diff(($booked_from_date),($booked_to_date));
                $daysCount = $diff->format("%a");//exit;
                $daysCountCPM = $daysCount + 1;
					$perdayprice = $getproductDetails->default_price/28;
                //$daysCoun1tCPM = $daysCount;
                //echo "<pre>diff";print_r($diff);exit;
                //echo "<pre>product";print_r($product);exit;
                //echo $daysCoun1tCPM;exit;
                
                //$price = $campaignproduct->price;
                if(isset($product->fix) && $product->fix=="Fixed"){
                    /*$price = $product->default_price;
                    $priceperday = $price;//exit;
                    $priceperselectedDates = $priceperday;
                    $shortlistedsum+= $priceperselectedDates;
                    $campaignproduct->price = $priceperselectedDates;
                    $cpmsum+= $product->cpm;
                    $impressions = $product->secondImpression;
                    $impressionsperday = (int)($impressions);
                    $impressionsperselectedDates = $impressionsperday;//exit;
                    
                    if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                        $impressionsperselectedDates = $impressionsperselectedDates;
                    }else{
                        $impressionsperselectedDates = 1;
                    }
                    $impressionSum+= $impressionsperselectedDates;
                    $campaignproduct->secondImpression = round($impressionsperselectedDates, 2);
                    $cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                    $campaignproduct->cpmperselectedDates = $cpmcal;
                    $campaignproduct->cpm = $cpmcal;
                    $campaignproduct->impressionsperselectedDates = $impressionsperselectedDates;
                    $campaignproduct->priceperselectedDates = $priceperselectedDates;*/
                    $offerDetails = MakeOffer::where([
                        ['campaign_id', '=', $campaignproduct->campaign_id],
                        ['status', '=', 20],
                    ])->get();

                    if(isset($offerDetails) && count($offerDetails)==1){
                        //echo 'offer exists';exit;
                        foreach($offerDetails as $offerDetails){
                             $offerprice = $offerDetails->price;
                             $stripe_percent=$getproductDetails->stripe_percent;
                             
                        //$price = $getproductDetails->default_price;
                        $price = $campaignproduct->price;
                        
                        //$price = $campaign_product->price;
                        $priceperday = $price;
                        $priceperselectedDates = $priceperday;
                        $newpricepercentage = ($priceperselectedDates/$camptot) * 100;
        
                        //$newofferprice = ($offerprice * ($newpricepercentage))/100;
						if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
							$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
							$newofferprice = $newofferprice_wq*$campaignproduct->quantity;
						}else{
							$newofferprice = ($offerprice * ($newpricepercentage))/100;
						}
                        //$offerpriceperday = $newofferprice/28;//exit;
                        //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                        $offerpriceperselectedDates = $newofferprice;
                        $campaignproduct->stripe_percent = $stripe_percent;
                           }
								$offer_applied = 1;
                        }else{
                             //$offerprice = $getproductDetails->default_price;
                             //echo 'no offer exists';exit;
                             $offerprice = $campaignproduct->price;
                             //$offerprice = $getproductDetails->default_price;
                             $stripe_percent=$getproductDetails->stripe_percent;
                             //$price = $getproductDetails->default_price;
                             $price = $campaignproduct->price;
                             //$price = $campaign_product->price;
                             $priceperday = $price;//exit;
                             $priceperselectedDates = $priceperday;
                             $newpricepercentage = ($priceperselectedDates/$camptot) * 100;
            
							if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
                                $newofferprice = $offerprice*$campaignproduct->quantity;
							}else{
								$newofferprice = $offerprice ;
							}
                        //$offerpriceperday = $newofferprice/28;//exit;
                        //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                            $offerpriceperselectedDates = $newofferprice;
                            $campaignproduct->stripe_percent = $stripe_percent;
                        }
                        
                        if($campaign->total_paid != 0){
							if(isset($campaignproduct->tax_percentage)){
								$tax_percentage_booking = $campaignproduct->tax_percentage;
							}else{
								$tax_percentage_booking = 0;
							}
						}else{
							$tax_percentage_booking = $getproductDetails->tax_percentage;
						}
						if($tax_percentage_booking != 0){
							$campaignproduct->tax_percentage_amount_shortlist = ($priceperselectedDates * ($tax_percentage_booking))/100;
							$tax_percentage_amount = ($offerpriceperselectedDates * ($tax_percentage_booking))/100;
						}
						$campaignproduct->tax_percentage_amount = round($tax_percentage_amount,2); 
						
						$tax_percentage_amount_sum += round($tax_percentage_amount,2);
                                 
						$newofferStripepercentamt = (($newofferprice+$tax_percentage_amount) * ($stripe_percent))/100;
						$newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;           
                        $shortlistedsum+= ($priceperselectedDates+$campaignproduct->tax_percentage_amount_shortlist)*$campaignproduct->quantity;
                        $shortlistedsumcpm += $priceperselectedDates*$campaignproduct->quantity;
						$campaignproduct->price = $priceperselectedDates;
        
						$offershortlistedsum+= $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;
						$campaignproduct->offerprice = $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;
						$offershortlistedsumTotal += $offerpriceperselectedDates;

                        $cpmsum+= $getproductDetails->cpm;
                        $impressions = $getproductDetails->secondImpression;
                        $impressionsperday = (float)($impressions/7);
                        $impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
                        
                        if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                            $impressionsperselectedDates = $impressionsperselectedDates;
                        }else{
                            $impressionsperselectedDates = 1;
                        }
                        //$impressionSum+= $product_details->secondImpression; 
                        $impressionSum+= $impressionsperselectedDates*$campaignproduct->quantity;
                        $campaignproduct->secondImpression = round($impressionsperselectedDates, 2);
                        //$cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                        //$offercpmcal = ($offershortlistedsum/$impressionSum) * 1000;
                        $cpmcal = ($priceperselectedDates/$impressionsperselectedDates) * 1000;
                        $offercpmcal = ($offerpriceperselectedDates/$impressionsperselectedDates) * 1000;
                        $campaignproduct->cpmperselectedDates = $cpmcal;
                        $campaignproduct->offercpmperselectedDates = $offercpmcal;
                        $campaignproduct->cpm = $cpmcal;
                        $campaignproduct->offercpm = $offercpmcal;
                        $campaignproduct->impressionsperselectedDates = $impressionsperselectedDates;
                        $campaignproduct->priceperselectedDates = $priceperselectedDates;
                        $campaignproduct->offerpriceperselectedDates = $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;
        
                        $campaignproduct->new_stripe_percent_amount = $newofferStripepercentamt;
                        $campaignproduct->newprocessingfeeamt = $newprocessingfeeamt;
        
                        $newofferStripepercentamtSum += (($offerpriceperselectedDates+$campaignproduct->tax_percentage_amount)* ($stripe_percent))/100;
                        $newprocessingfeeamtSum += $newprocessingfeeamt;
                        //echo "<pre>";print_r($campaignproduct);exit;
                }else{
                    /*$price = $product->default_price;
                    $priceperday = $price/28;//exit;
                    $priceperselectedDates = $priceperday * $daysCountCPM;
                    $shortlistedsum+= $priceperselectedDates;
                    $campaignproduct->price = $priceperselectedDates;
                    $cpmsum+= $product->cpm;
                    $impressions = $product->secondImpression;
                    $impressionsperday = (int)($impressions/7);
                    $impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
                    
                    if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                        $impressionsperselectedDates = $impressionsperselectedDates;
                    }else{
                        $impressionsperselectedDates = 1;
                    }
                    $impressionSum+= $impressionsperselectedDates;
                    $campaignproduct->secondImpression = round($impressionsperselectedDates, 2);
                    $cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                    $campaignproduct->cpmperselectedDates = $cpmcal;
                    $campaignproduct->cpm = $cpmcal;
                    $campaignproduct->impressionsperselectedDates = $impressionsperselectedDates;
                    $campaignproduct->priceperselectedDates = $priceperselectedDates;*/
                    $offerDetails = MakeOffer::where([
                        ['campaign_id', '=', $campaignproduct->campaign_id],
                        ['status', '=', 20],
                    ])->get();
                    
                    
                    if(isset($offerDetails) && count($offerDetails)==1){
                        //echo 'variable -offer'; exit;;
                            foreach($offerDetails as $offerDetails){
                                    $offerprice = $offerDetails->price;
                                    $stripe_percent=$getproductDetails->stripe_percent;
                                    
                            $price = $getproductDetails->default_price;
                            
                            //$price = $campaign_product->price;
                            //$priceperday = $price/28;//exit;
                            //echo '---camptot--'.$camptot;
                            //$priceperselectedDates = $priceperday * $daysCountCPM;
							
							///variable_offer///
							$priceperday = $campaignproduct->price;
							$priceperselectedDates = $priceperday;
							
							
                            $newpricepercentage = ($priceperselectedDates/$camptot) * 100;

                            //$newofferprice = ($offerprice * ($newpricepercentage))/100;//exit;
							if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
								$newofferprice_wq = ($offerprice * ($newpricepercentage))/100;
								$newofferprice = $newofferprice_wq*$campaignproduct->quantity;
							}else{
								$newofferprice = ($offerprice * ($newpricepercentage))/100;
							}
                            //$offerpriceperday = $newofferprice/28;//exit;
                            //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                            $offerpriceperselectedDates = $newofferprice;
                            $campaignproduct->stripe_percent = $stripe_percent;
                                }
								$offer_applied = 1;
                            }else{
                               //echo 'variable -no-offer'; exit;;
                                    //$offerprice = $getproductDetails->default_price;
                            $offerprice = $campaignproduct->price;
                            $stripe_percent=$getproductDetails->stripe_percent;
                            $price = $campaignproduct->price;
                            //$price = $campaign_product->price;
                            $priceperday = $price;//exit;
                            $priceperselectedDates = $priceperday;
                            $newpricepercentage = ($priceperselectedDates/$camptot) * 100;

							if(isset($campaignproduct->quantity) && $campaignproduct->quantity != '' && $campaignproduct->quantity != 0){
								$newofferprice = $offerprice*$campaignproduct->quantity;
							}else{
								$newofferprice = $offerprice;
							}
                            //$offerpriceperday = $newofferprice/28;//exit;
                            //$offerpriceperselectedDates = $offerpriceperday * $daysCountCPM;
                            $offerpriceperselectedDates = $newofferprice;
                            $campaignproduct->stripe_percent = $stripe_percent;
                            }
                            
                            //if($daysCountCPM <= $product->minimumdays){
                            //    $priceperselectedDates = $priceperday * $product->minimumdays;
                            //}
							
							
						   if($campaign->total_paid != 0){
								if(isset($campaignproduct->tax_percentage)){
									$tax_percentage_booking = $campaignproduct->tax_percentage;
								}else{
									$tax_percentage_booking = 0;
								}
							}else{
								$tax_percentage_booking = $getproductDetails->tax_percentage;
							}
							if($tax_percentage_booking != 0){
								$campaignproduct->tax_percentage_amount_shortlist = ($priceperselectedDates * ($tax_percentage_booking))/100;
								$tax_percentage_amount = ($offerpriceperselectedDates * ($tax_percentage_booking))/100;
							}  
							$campaignproduct->tax_percentage_amount = round($tax_percentage_amount,2); 
							$tax_percentage_amount_sum += round($tax_percentage_amount,2);
                                     
                            $newofferStripepercentamt = (($newofferprice+$tax_percentage_amount) * ($stripe_percent))/100;
                            $newprocessingfeeamt = ((2.9) * $newofferStripepercentamt)/100;           
                            $shortlistedsum+= ($priceperselectedDates+$campaignproduct->tax_percentage_amount_shortlist)*$campaignproduct->quantity;
							$shortlistedsumcpm += $priceperselectedDates*$campaignproduct->quantity;
                            $campaignproduct->price = $priceperselectedDates;

							$offershortlistedsum+= $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;
							$campaignproduct->offerprice = $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;
							$offershortlistedsumTotal += $offerpriceperselectedDates;

                            $cpmsum+= $getproductDetails->cpm;
                            $impressions = $getproductDetails->secondImpression;
                            $impressionsperday = (float)($impressions/7);
                            $impressionsperselectedDates = $impressionsperday * $daysCountCPM;//exit;
                            
                            if(isset($impressionsperselectedDates) && $impressionsperselectedDates!=0){
                                $impressionsperselectedDates = $impressionsperselectedDates;
                            }else{
                                $impressionsperselectedDates = 1;
                            }
                            //$impressionSum+= $product_details->secondImpression; 
                            $impressionSum+= $impressionsperselectedDates*$campaignproduct->quantity;
                            $campaignproduct->secondImpression = round($impressionsperselectedDates, 2);
                            //$cpmcal = ($shortlistedsum/$impressionSum) * 1000;
                            //$offercpmcal = ($offershortlistedsum/$impressionSum) * 1000;
                            $cpmcal = ($priceperselectedDates/$impressionsperselectedDates) * 1000;
                            $offercpmcal = ($offerpriceperselectedDates/$impressionsperselectedDates) * 1000;
                            $campaignproduct->cpmperselectedDates = $cpmcal;
                            $campaignproduct->offercpmperselectedDates = $offercpmcal;
                            $campaignproduct->cpm = $cpmcal;
                            $campaignproduct->offercpm = $offercpmcal;
                            $campaignproduct->impressionsperselectedDates = $impressionsperselectedDates;
                            $campaignproduct->priceperselectedDates = $priceperselectedDates;
                            $campaignproduct->offerpriceperselectedDates = $offerpriceperselectedDates+$campaignproduct->tax_percentage_amount;

                            $campaignproduct->new_stripe_percent_amount = $newofferStripepercentamt;
                            $campaignproduct->newprocessingfeeamt = $newprocessingfeeamt;

                            $newofferStripepercentamtSum += (($offerpriceperselectedDates+$campaignproduct->tax_percentage_amount)* ($stripe_percent))/100;
                            $newprocessingfeeamtSum += $newprocessingfeeamt;
                }
				$offer_comments = Offer_admin_seller_comments::where('booking_id', '=', $campaignproduct->id)->orderBy('created_at', 'ASC')->get();
				if(isset($offer_comments) && count($offer_comments) > 0){
					$first_offer_id = $offer_comments[0]->offer_id;
					foreach($offer_comments as $keys => $values){
						if($values->offer_id == $first_offer_id){
							$values->offer_type = 'First Offer Details';
						}else{
							$values->offer_type = 'Second Offer Details';
						}
						
						if($values->status == 1){
							$values->final_status = 'Send Request';
							$values->Sender = 'Admin';
						}else if($values->status == 2){
							$values->final_status = 'Approved';
							$values->Sender = 'Owner';
						}else if($values->status == 3){
							$values->final_status = 'Rejected';
							$values->Sender = 'Owner';
						}
					}
					$offer_comments_array = array('offer_comments' => $offer_comments->toArray());
				}else{
					$offer_comments_array = array('offer_comments' => array());
				}
                array_push($products_arr, array_merge(Product::where('id', '=', $campaignproduct->product_id)->first()->toArray(), $campaignproduct->toArray(),$offer_comments_array));
            }
        }
        
        $campaign->products = $products_arr;
        $campaign->actbudg = $products_arr;
        
        
        $act_budget = ProductBooking::raw(function($collection) use ($campaign_id) {
            return $collection->aggregate(
                            [
                                [
                                    '$match' =>
                                    [
                                        "campaign_id" => $campaign_id
                                    ]
                                ],
                                [
                                    '$group' =>
                                    [
                                        '_id' => '$campaign_id',
                                        'total_price' => [
                                            '$sum' => '$admin_price'
                                        ]
                                    ]
                                ]
                            ]
            );
        });


        $res = array_sum(array_map(function($item) {
					if(isset($item['quantity']) && $item['quantity'] != '' && $item['quantity'] != 0){
						return ($item['price']+$item['tax_percentage_amount'])*$item['quantity']; 
					}else{
						return $item['price']+$item['tax_percentage_amount']; 
					}
        }, $campaign->actbudg));
        //echo "<pre>act_budget";print_r($res);exit;
        $campaign->act_budget = $res;

        $campaign->totalamount = $campaign->act_budget;
        $campaign->offer_applied_res = $offer_applied;

                 
        $campaign->refunded_amount = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('refunded_amount');
        
        $campaign->bal_amount_available_with_amp = CampaignPayment::where('campaign_id', '=', $campaign->id)->sum('bal_amount_available_with_amp');
        
		$campaign->gross_fee_price = 0;
		if(isset($campaign->gross_fee_percentage) && $campaign->gross_fee_percentage > 0){
			$campaign->gross_fee_price = ($offershortlistedsum*$campaign->gross_fee_percentage)/100;
			$newprocessingfeeamtGross = ($newprocessingfeeamtSum*$campaign->gross_fee_percentage)/100;
			$newprocessingfeeamtSum = $newprocessingfeeamtSum+$newprocessingfeeamtGross;
		}else{ 
			$campaign->gross_fee_price = 0;
		}
        
        $impressionSum4 = round($impressionSum, 2); /* As discussed with Richard on July 14) */
         if($impressionSum4>0){
            $cpmval = ($shortlistedsumcpm/$impressionSum4) * 1000;
            $offercpmval = ($offershortlistedsumTotal/$impressionSum4) * 1000;
         }else{
             $cpmval = 0;
             $offercpmval = 0;
         }
         $campaign_shortlistedsum = $shortlistedsum;
		 if($offershortlistedsum != 0){
			$campaign_cpmval = $offercpmval;
		 }else{
			 $campaign_cpmval = $cpmval;
		 }
         $campaign_impressionSum = $impressionSum4;

             $total_price = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('owner_price');
             
             if($total_price == 0){
             $total_price = ProductBooking::where('campaign_id', '=', $campaign_id)->sum('price');
             }

            $act_budget = ProductBooking::raw(function($collection) use ($campaign_id) {
                return $collection->aggregate(
                    [
                        [
                            '$match' =>
                            [
                                "campaign_id" => $campaign_id
                            ]
                        ],
                        [
                            '$group' =>
                            [
                                '_id' => '$campaign_id',
                                'total_price' => [
                                    '$sum' => '$admin_price'
                                ]
                            ]
                        ]
                    ]
                );
            });
        
            $res = array_sum(array_map(function($item) { 
				if(isset($item['quantity']) && $item['quantity'] != '' && $item['quantity'] != 0){
					return ($item['price'])*$item['quantity']; 
				}else{
					return $item['price']; 
				}
            }, $campaign->actbudg));
			if($offershortlistedsum != 0){
				$total_price = $offershortlistedsumTotal;
			}else{
				$total_price = $res;
			}
			$client_details = array();
			$client_details_single = array();
			$temp = array_unique(array_column($products_arr, 'siteNo'));
			$unique_arr = array_intersect_key($products_arr, $temp);
			foreach($unique_arr as $key => $value){
				$j = 0;
				foreach ($products_in_campaign as $product) {
					if($value['siteNo'] == $product['siteNo']){
						if(!is_array($product['vendor'])){
							$client_details_single = ClientMongo::select('company_name','contact_email','phone','address')->where('client_id', '=', $product['client_id'])->first();
							if(isset($client_details_single)){
								$client_details[] = $client_details_single->toArray();
							}else{
								$client_details[] = array();
							}
						}else{
							$client_details[] = array();
						}
					}
				}
			}
			/*foreach($unique_arr as $key => $value){ 
				$j = 0;
				foreach ($products_in_campaign as $product) {
					if($value['siteNo'] == $product['siteNo']){
						if(is_array($product['vendor'])){
							
							echo'<pre>a';print_r($client_details[$key]['username']);
						}else{
							echo'<pre>b';print_r($client_details[$key]['phone']);
						}
					}
				}
			}
			
			
			
			foreach($products_arr as $key => $value){
				if(is_array($value['vendor'])){
					$client_details[] = User::where('id', '=', $value['client_id'])->first()->toArray();
				}else{
					$client_details[] = ClientMongo::select('company_name','contact_email','phone','address')->where('client_id', '=', $value['client_id'])->first()->toArray();
				}
			}*/
			if($offershortlistedsum!=0){
                    $campaign->percentagevalue = ($newofferStripepercentamtSum * 100)/$offershortlistedsum;
                 }else{
                    $campaign->percentagevalue=5;
                 }
			if($campaign->total_paid != 0){
				if($newofferStripepercentamtSum < 1){
					$campaign->total_paid = $campaign->total_paid;
				}else{
					$campaign->total_paid = $newofferStripepercentamtSum;
				}
			 }
			$paid_percentage = $campaign->total_paid*100/$total_price;
			$processing_fee_sum = $newprocessingfeeamtSum;
			
            $campaign_report = [
                'campaign' => $campaign,
                'areas_covered' => $areas,
                'format_types' => $formats,
                'mediums_covered' => $products_in_campaign->count(),
                'audience_reach' => $audience_reach,
                'repeated_audience' => $repeated_audience,
                'products' => $products_in_campaign,
                'total_price'=>$total_price,
                'products_arr'=>$products_arr,
                'campaign_shortlistedsum'=>$campaign_shortlistedsum,
                'campaign_cpmval'=>$campaign_cpmval,
                'campaign_impressionSum'=>$campaign_impressionSum,
                'client_details_billing'=>$client_details,
                'paid_percentage'=>$paid_percentage,
                'processing_fee_sum'=>$processing_fee_sum,
                'user_mongo_report'=>$user_mongo,
                'tax_percentage_amount_sum'=>$tax_percentage_amount_sum,
            ];
			//echo'<pre>b';print_r($campaign_report['client_details_billing'][0]['username']);exit;
            //return response()->json($campaign_report);
            // return view('pdf.campaign_details_pdf', $campaign_report); exit;
            //$pdf = PDF::loadView('pdf.campaign_details_pdf', $campaign_report);
            $pdf = PDF::loadView('pdf.IO_pdf', $campaign_report);
            // $pdf->save('uploads/campaign' . uniqid() . '.pdf'); die();
            }
            else{
                 $packages_in_campaign = CampaignProduct::where('campaign_id', '=', $campaign_id)->get();
            foreach ($packages_in_campaign as $pkg) {
                $package_tplt = MetroPackage::where('id', '=', $pkg->package_id)->first();
                $pkg->format = $package_tplt->format;
                $pkg->max_trains = $package_tplt->max_trains;
                $pkg->max_slots = $package_tplt->max_slots;
                $pkg->months = $package_tplt->months;
                $pkg->days = $package_tplt->days;
            }
            $campaign_report = [
                'campaign' => $campaign
                // 'format_types' => $formats,
                //'packages' => $packages_in_campaign
            ];
            //$pdf = PDF::loadView('pdf.metro_campaign_details_pdf', $campaign_report);
            $pdf = PDF::loadView('pdf.RFP_campaign_pdf', $campaign_report);
            }
            if (!empty($pdf)) {
                //return $pdf->download("campaign_details_pdf.pdf");
                return $pdf->download("IO_pdf.pdf");
            } else {
                return response()->json(['status' => 0, 'message' => "There was an error sending the email, Please try again."]);
            }
        } catch (Exception $ex) {
            return response()->json(['status' => 0, 'message' => "There was an error generating the campaign report."]);
        }
          

      
    }
	
	//Clone A Saved Campaign
	public function cloneSavedCampaign(Request $request) {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
		
		$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
            if (isset($input['client'])) {
            $client = userMongo::where('id', '=', $input['client'])->first();
			}else{
				$client = userMongo::where('id', '=', $user_mongo['id'])->first();
			}
		
		if (isset($input['id'])) {
            $campaign_obj = Campaign::where('id', '=', $input['id'])->first();
			if(isset($campaign_obj)){
				$campaign_obj_add = new Campaign;
				$campaign_obj_add->user_id = isset($client) ? $client->user_id : "";
				$campaign_count = Campaign::latest()->first();
				$campaign_code_explode = explode("_", $campaign_count->cid);
				$uid_cid = '_'.str_pad(end($campaign_code_explode)+1, 6, '0', STR_PAD_LEFT);
				$buyer_id = '000'.$campaign_obj_add->user_id;
				$campaign_obj_add->id = uniqid();
				$campaign_obj_add->cid = 'AMP_'.'ABI'.$buyer_id.$uid_cid; 
				$campaign_obj_add->name = isset($input['name']) ? $input['name'] : $campaign_obj->name;
				$campaign_obj_add->user_id = $campaign_obj->user_id;
				$campaign_obj_add->est_budget = $campaign_obj->est_budget;
				$campaign_obj_add->created_by = $campaign_obj->created_by;
				$campaign_obj_add->status = Campaign::$CAMPAIGN_STATUS['campaign-preparing'];
				$campaign_obj_add->type = Campaign::$CAMPAIGN_USER_TYPE['user'];
				$campaign_obj_add->todolist_read = $campaign_obj->todolist_read;
				if ($campaign_obj_add->save()) {
					$product_booking_obj = ProductBooking::where('campaign_id', '=', $input['id'])->get();
					if(isset($product_booking_obj)){
						$rand = substr(str_shuffle(str_repeat("ABCDEFGHJKLMNPQRSTUVWXYZ", 3)), 0, 3);
						$group_id ="AMP".date('Ymd').$rand;
						 foreach ($product_booking_obj as $key => $shortlisted_id) {
							$product_obj_add = new ProductBooking;
							$product_obj_add->id = uniqid();
							$product_obj_add->product_id = $shortlisted_id->product_id;
							$product_obj_add->booked_from = iso_to_mongo_date($shortlisted_id->booked_from);
							$product_obj_add->booked_to = iso_to_mongo_date($shortlisted_id->booked_to);
							if(isset($shortlisted_id->booked_slots) && $shortlisted_id->booked_slots!='' ){
								$product_obj_add->booked_slots = $shortlisted_id->booked_slots;
							}
							$product_obj_add->group_slot_id = $group_id.$shortlisted_id->product_id;
							$product_obj_add->campaign_id = $campaign_obj_add->id;
							$product_obj_add->price = $shortlisted_id->price;
							$product_obj_add->product_owner = $shortlisted_id->product_owner;
							$product_obj_add->product_status = ProductBooking::$PRODUCT_STATUS['proposed'];
							$product_obj_add->quantity = $shortlisted_id->quantity;
							$product_obj_add->save();
							
							/*$product = Product::select("unitQty","from_date","to_date","client_mongo_id")->where('id', '=', $shortlisted_id->product_id)->first();
							$productBooked = ProductBooking::where('product_id', '=',  $shortlisted_id->product_id)->where('quantity', '!=',  '')->get()->toArray();

								
							$available_quantity = $product->unitQty;
							if(!empty($productBooked)){
								$productBooked_last = ProductBooking::select("quantity","campaign_id","id")->where('product_id', '=',  $shortlisted_id->product_id)->where('booked_from','<=',iso_to_mongo_date($shortlisted_id->booked_to))->where('booked_to','>=',iso_to_mongo_date($shortlisted_id->booked_from))->where('product_status','!=',100)->where('product_status','!=',400)->where('product_status','!=',700)->where('quantity', '!=',  '')->groupBy('group_slot_id')->get([DB::raw('MAX(quantity) as quantity')])->toArray();
								$sum_quantity = 0;
								if(!empty($productBooked_last)){
									foreach($productBooked_last as $key => $value){
										$delete_product_status = DeleteProduct::where([
																			['campaign_id', '=', $value['campaign_id']],
																			['status', '=', DeleteProduct::$PRODUCT_STATUS['confirm-delete-product-from-campaign']],
																		])->whereIn('product_id', array($shortlisted_id->product_id))->whereIn('productbookingid', array($value['id']))->orderBy('created_at', 'desc')->first();
										if($value['campaign_id'] != ''){
											$campaign_delete = Campaign::select("status")->where('id', '=', $value['campaign_id'])->first();
											if(empty($delete_product_status) && ($campaign_delete->status != 1200)){
												$sum_quantity += $value['quantity'];
											}
										}
									}
								}
								$available_quantity = $product->unitQty-$sum_quantity;
								if($available_quantity >= 0){
									$available_quantity = $available_quantity;
								}else{
									$available_quantity = 0;
								}
							} 
							if($available_quantity == 0){
								$client_details = ClientMongo::where('id', '=', $product->client_mongo_id)->first()->toArray();
								$mail_tmpl_params = [
								'sender_email' => $client_details['email'],
								'receiver_name' => '',
								'mail_message' => 'One buyer is interented on this product in this date ranges Please increase the quantity'
								];
								$mail_data = [
									'email_to' => $client_details['email'],
									'recipient_name' => $client_details['name']
								];
								Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data) {
									$message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Clone a campaign - Advertising Marketplace');
								});
							}*/
						}
					}
					return response()->json(["status" => "1", "message" => "Campaign Cloned Successfully.","campign_id" => $campaign_obj_add->id]);
				}exit;	
			}else {
				return response()->json(["status" => "0", "message" => "Failed to Clone Campaign.","campign_id" => ""]);
			}
		}
	}
	
	// Mail functionality when product quantity in unavailable in saved campaign while Buyer going to purchase
	
	
	public function unitQuantityUnavailableEmail(Request $request) {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
		if (isset($input['id'])) {
            $campaign_obj = Campaign::where('id', '=', $input['id'])->first();
			if(isset($campaign_obj)){
					$product_booking_obj = ProductBooking::where('campaign_id', '=', $input['id'])->get();
					if(isset($product_booking_obj)){
						 foreach ($product_booking_obj as $key => $shortlisted_id) {
							
							$product = Product::select("unitQty","from_date","to_date","client_mongo_id")->where('id', '=', $shortlisted_id->product_id)->first();
							$productBooked = ProductBooking::where('product_id', '=',  $shortlisted_id->product_id)->where('quantity', '!=',  '')->get()->toArray();

								
							$available_quantity = $product->unitQty;
							if(!empty($productBooked)){
								$productBooked_last = ProductBooking::select("quantity","campaign_id","id")->where('product_id', '=',  $shortlisted_id->product_id)->where('booked_from','<=',iso_to_mongo_date($shortlisted_id->booked_to))->where('booked_to','>=',iso_to_mongo_date($shortlisted_id->booked_from))->where('product_status','!=',100)->where('product_status','!=',400)->where('product_status','!=',700)->where('quantity', '!=',  '')->groupBy('group_slot_id')->get([DB::raw('MAX(quantity) as quantity')])->toArray();
								$sum_quantity = 0;
								if(!empty($productBooked_last)){
									foreach($productBooked_last as $key => $value){
										$delete_product_status = DeleteProduct::where([
																			['campaign_id', '=', $value['campaign_id']],
																			['status', '=', DeleteProduct::$PRODUCT_STATUS['confirm-delete-product-from-campaign']],
																		])->whereIn('product_id', array($shortlisted_id->product_id))->whereIn('productbookingid', array($value['id']))->orderBy('created_at', 'desc')->first();
										if($value['campaign_id'] != ''){
											$campaign_delete = Campaign::select("status")->where('id', '=', $value['campaign_id'])->first();
											if(empty($delete_product_status) && ($campaign_delete->status != 1200)){
												$sum_quantity += $value['quantity'];
											}
										}
									}
								}
								$available_quantity = $product->unitQty-$sum_quantity;
								if($available_quantity >= 0){
									$available_quantity = $available_quantity;
								}else{
									$available_quantity = 0;
								}
							} 
							if($available_quantity == 0){
								$client_details = ClientMongo::where('id', '=', $product->client_mongo_id)->first()->toArray();
								$mail_tmpl_params = [
								'sender_email' => $client_details['email'],
								'receiver_name' => '',
								'mail_message' => 'One buyer is interested this product in these date ranges Please increase the quantity'
								];
								$mail_data = [
									'email_to' => $client_details['email'],
									//'email_to' => 'sandhyarani.manelli@peopletech.com',
									'recipient_name' => $client_details['name']
								];
								Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data) {
									$message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Product Quantity Unavailable - Advertising Marketplace');
								});
							}
						}
					}
					return response()->json(["status" => "1", "message" => "We have sent an email to Seller, he will increase quantity or you can delete this product to proceed payment"]);
			}else {
				return response()->json(["status" => "0", "message" => "Failed to Sending Mail."]);
			}
		}
	}
	
	//Offer Accept/Reject functinality for Seller
	
	public function offerAcceptRejectForSeller(Request $request) {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
		$user_mongo_jwt = JWTAuth::parseToken()->getPayload()['userMongo'];
		$user = User::select("email")->where('id', '=', $user_mongo_jwt['user_id'])->first();
		$notify_from_email = $user->email;
        $user = User::where('id', '=', $user_mongo_jwt['user_id'])->first();
		if (isset($input['campaign_id']) && isset($input['booking_id']) && isset($input['offer_id']) && isset($input['status']) && isset($input['comment'])) {
            $campaign_offer = MakeOffer::select("campaign_id","AdminOfferAcceptReject")->where('id', '=', $input['offer_id'])->first();
			if(isset($campaign_offer)){
				$campaign_details = Campaign::where('id', '=', $campaign_offer->campaign_id)->first()->toArray();
				$product_booking_id = ProductBooking::select("product_id")->where('id', '=', $input['booking_id'])->first();
				$product = Product::select("client_mongo_id","client_id")->where('id', '=', $product_booking_id->product_id)->first();
				$client_details = ClientMongo::where('id', '=', $product->client_mongo_id)->first()->toArray();
				
				$path_hint = "";
				if($input['status'] == 1){
					$notification_to_id = $client_details['id'];
					$final_email = '';
					if(isset($client_details['contact_email']) && $client_details['contact_email'] != ''){
						$notification_to_email = $client_details['contact_email'];
						$notification_to_name = $client_details['name'];
					}else{
						$owner_email = ClientMongo::where('id', '=', $product->client_mongo_id)->select("id","email","name")->first();
						$notification_to_email = $owner_email->email;
						$notification_to_name = $owner_email->name;
					}
					//$notification_to_email = $client_details['contact_email'];
					if($product->client_id == 1){
						$to_type_not = Notification::$NOTIFICATION_CLIENT_TYPE['bbi'];
					}else{
						$to_type_not = Notification::$NOTIFICATION_CLIENT_TYPE['owner'];
					}
					
					$u_type = "Admin";
					$mail_u_type = $notification_to_name;
					$path_hint = "To access this campaign offer request please open notifications after Logged In";
				}else if ($input['status'] == 2 || $input['status'] == 3){
					$user_internal = User::select("email")->where('username', '=', 'bbadmin')->first();
					$notification_to_id = $user_internal->id;
					$notification_to_email = $user_internal->email;
					$to_type_not = Notification::$NOTIFICATION_CLIENT_TYPE['bbi'];
					$u_type = "Owner";
					$mail_u_type = "Admin";
				}else{
					return response()->json(['status' => 0, 'message' => "Some thing went wrong. Please try again later."]);
				}
				$arr_AdminOfferAcceptReject = $campaign_offer['AdminOfferAcceptReject'];
				foreach($arr_AdminOfferAcceptReject as $key => $value){
					if($key == $input['booking_id']){
						$arr_AdminOfferAcceptReject[$key] = $input['status'];
					}
				}
				$campaign_offer->AdminOfferAcceptReject = $arr_AdminOfferAcceptReject;
                if($campaign_offer->save()){
					
					$offer_comment = new Offer_admin_seller_comments;
					$offer_comment->booking_id = $input['booking_id'];
					$offer_comment->offer_id = $input['offer_id'];
					$offer_comment->comment = $input['comment'];
					$offer_comment->status = $input['status'];
					$offer_comment->id = uniqid();
					$offer_comment->save();
					
					if($input['status'] == 1){
						$f_status = 'Accept/Reject';
					}else if($input['status'] == 2){
						$f_status = 'Approved';
					}else if($input['status'] == 3){
						$f_status = 'Rejected';
					}
					
					event(new OfferRequestedEvent([
					  'type' => Notification::$NOTIFICATION_TYPE['campaign-product-offer'],
					  'from_id' => $notify_from_email,
					  'to_type' => $to_type_not,
					  'to_id' => $notification_to_id,
					  'to_client' => $notification_to_email,
					  'desc' => "Offer ".$f_status." Request from ".$u_type." regarding the campaign - ( ".$campaign_details["cid"]." )",
					  'message' => "Offer ".$f_status." Request from ".$u_type." regarding the campaign - ( ".$campaign_details["cid"]." )",
					  'data' => "Offer Accept/Reject",
					  'campaign_id' => $campaign_details['id']
					]));
					$notification_obj = new Notification; 
					$notification_obj->id = uniqid();
					$notification_obj->type = "campaign_product_offer";
					$notification_obj->from_id = $notify_from_email;
					$notification_obj->to_type = $to_type_not;
					$notification_obj->to_id = $notification_to_id;
					$notification_obj->to_client = $notification_to_email;
					$notification_obj->desc = "Offer ".$f_status." Request from ".$u_type." regarding the campaign - ( ".$campaign_details["cid"]." )";
					$notification_obj->message = "Offer ".$f_status." Request from ".$u_type." regarding the campaign - ( ".$campaign_details["cid"]." )";
					$notification_obj->campaign_id = $campaign_details['id'];
					$notification_obj->status = 0;
					$notification_obj->save();

					$mail_tmpl_params = [
						'sender_email' => config('app.bbi_email'),
						'receiver_name' => $mail_u_type,
						'mail_message' => "Offer ".$f_status." Request from ".$u_type." regarding the campaign - ( ".$campaign_details["cid"]." ). ".$path_hint
					];
					$mail_data = [
						//'email_to' => $this->input['email'],
						'email_to' => $notification_to_email
					];  
					Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data) {
						$message->to($mail_data['email_to'])->subject('Offer Accept/Reject Request - Advertising Marketplace!');
					});
					 if (!Mail::failures()) {
						return response()->json(['status' => 1, 'message' => "Request Sent"]);
					 }
				}else{
					return response()->json(['status' => 0, 'message' => "Some thing went wrong. Please try again later."]);
				}
			}else {
                return response()->json(['status' => 0, 'message' => "Dont have offer request for this campaign.so we cannot proceed."]);
            }
		}else{
			return response()->json(['status' => 0, 'message' => "Mandatory feilds are missing."]);
		}
	}
	
	public function OfferAcceptRejectComments($booking_id = ''){
		$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
		if($booking_id == ''){
			return response()->json(["status" => "0", "message" => "Mandatory parameters are missing."]);
		}  
		
		$offer_comments = Offer_admin_seller_comments::where('booking_id', '=', $booking_id)->orderBy('created_at', 'ASC')->get();

		if(isset($offer_comments) && count($offer_comments) > 0){
			$first_offer_id = $offer_comments[0]->offer_id;
			foreach($offer_comments as $key => $value){
				if($value->offer_id == $first_offer_id){
					$value->offer_type = 'First Offer Details';
				}else{
					$value->offer_type = 'Second Offer Details';
				}
				
				if($value->status == 1){
					$value->final_status = 'Send Request';
					$value->Sender = 'Admin';
				}else if($value->status == 2){
					$value->final_status = 'Approved';
					$value->Sender = 'Owner';
				}else if($value->status == 3){
					$value->final_status = 'Rejected';
					$value->Sender = 'Owner';
				}
			}
            return response()->json(["status" => "1", "offer_comments" => $offer_comments, "message" => "Data successfull"]);
		}else{
			return response()->json(["status" => "0", "offer_comments" => array(), "message" => "Comments not found for this campaign product."]);
		}
	}
	
	//Gross fee post Campaign
	public function grossFeeUpdateCampaign(Request $request) {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
		if (isset($input['id']) && isset($input['gross_fee_percentage_status'])) {
            $campaign_obj = Campaign::where('id', '=', $input['id'])->first();
			if(isset($campaign_obj)){
				if($input['gross_fee_percentage_status'] == 0){
					$campaign_obj->gross_fee_percentage_status = 0;
					$campaign_obj->gross_fee_percentage = 0;
					if ($campaign_obj->save()) {
						return response()->json(["status" => "1", "message" => "Campaign Gross fee updated.","campign_id" => $campaign_obj->id]);
					}
				}else if($input['gross_fee_percentage_status'] == 1){
					$campaign_obj->gross_fee_percentage_status = isset($input['gross_fee_percentage_status']) ? $input['gross_fee_percentage_status'] : 1;
					$campaign_obj->gross_fee_percentage = isset($input['gross_fee_percentage']) ? $input['gross_fee_percentage'] : 0;
					if ($campaign_obj->save()) {
						return response()->json(["status" => "1", "message" => "Campaign Gross fee updated.","campign_id" => $campaign_obj->id]);
					}
				}else{
					return response()->json(["status" => "0", "message" => "Failed to update gross fee.","campign_id" => ""]);
				}
			}else {
				return response()->json(["status" => "0", "message" => "Failed to update gross fee.","campign_id" => ""]);
			}
		}else {
			return response()->json(["status" => "0", "message" => "Failed to update gross fee.","campign_id" => ""]);
		}
	}
	
	//Gross fee get Campaign
	public function grossFeeGetCampaign($campaign_id) {
		if (isset($campaign_id)) {
            $campaign_obj = Campaign::where('id', '=', $campaign_id)->select("id","gross_fee_percentage","gross_fee_percentage_status")->first();
			if(isset($campaign_obj)){
					return response()->json(["status" => "1","campign_gross_data" => $campaign_obj]);
			}else {
				return response()->json(["status" => "0","campign_gross_data" => ""]);
			}
		}
	}
	
	//RFP Search Criteria
	public function RFPSearchCriteriaDownload($campaign_id) {
		$rfp_search_criteria = []; 
        $rfp_search_criteria = RFPSearchCriteria::where('campaign_id', '=', $campaign_id)->first();
		if(empty($rfp_search_criteria)){
			$campaign = Campaign::where('id', '=', $campaign_id)->first();
			$campaign_data = [
				'name' => $campaign->name,
				'due_date' => $campaign->due_date
			];
			$area_full_details = [];
		}
		else {
        $area_full_details = [];
        $rfp_search_criteria_preview_dma_dates = [];
			foreach($rfp_search_criteria['dma_area'] as $key => $value){
				$area_full_details[] = Area::where('id', '=', $value)->first();
				if(!is_null($area_full_details[$key])){
					$area_time_zone_type = $area_full_details[$key]['area_time_zone_type'];
					if(!is_null($area_time_zone_type)){
						$explode_dates  = @explode('::',$rfp_search_criteria['dma_dates'][$key]);
						$start_time = new DateTime( $explode_dates[0] );
						$end_time = new DateTime( $explode_dates[1] );
						$laTimezone = new DateTimeZone($area_time_zone_type);
						$start_time->setTimeZone( $laTimezone );
						$end_time->setTimeZone( $laTimezone );
						$start_time->format( 'Y-m-d H:i:s' );
						$end_time->format( 'Y-m-d H:i:s' );	
						$rfp_search_criteria_preview_dma_dates[] = $start_time->format( 'Y-m-d H:i:s' ).'::'.$end_time->format( 'Y-m-d H:i:s' );
					}							
				}
			}
			if(isset($rfp_search_criteria_preview_dma_dates)){
				$rfp_search_criteria['dma_dates'] = $rfp_search_criteria_preview_dma_dates;
			}
		}
        $campaign_data = Campaign::select("name","due_date")->where('id', '=', $campaign_id)->first();
			//echo '<pre>';print_r($areas_data);exit(); 
			$campaign_report = [
					'rfp_search_criteria' => $rfp_search_criteria,
					'campaign_data' => $campaign_data ?? null,
					'areas_data' => $area_full_details,
				];
				$pdf = PDF::loadView('pdf.rfp_search_criteria_pdf', $campaign_report);
		if (!empty($pdf)) {
                return $pdf->download("rfp_search_criteria_pdf.pdf");
            } else {
                return response()->json(['status' => 0, 'message' => "There was an error sending the email. Please try again."]);
            }
    }
	
	//Find for RFP Campaign
	public function findForRFPCampaign() {
        
            $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo']; 
			$user = User::where('id', '=', $user_mongo['user_id'])->first();
            
            $find_for_me = new FindForMe;
            $find_for_me->campaign_id = isset($this->input['campaign_id']) ? $this->input['campaign_id'] : "";
            $find_for_me->budget_rfp = isset($this->input['budget_rfp']) ? $this->input['budget_rfp'] : "";
            $find_for_me->loggedinUser = isset($this->input['loggedinUser']) ? $this->input['loggedinUser'] : "";
            $find_for_me->id = uniqid();
            $find_for_me->created_by = $user_mongo['id'];  
            if($find_for_me->save()) {
                return response()->json(['status' => 1, 'message' => "Your query has been sent successfully. We will get back to you shortly.."]);
            } else {
                return response()->json(['status' => 0, 'message' => "There was an error sending the email. Please try again."]);
            }
    }
	
	public function getUserSavedCampaigns() {
		$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
		$user_mongo_data = UserMongo::select("id")->where('id', '=', $user_mongo['id'])->first();
		$user_campaigns = Campaign::select("id","cid","name")->where('created_by', '=', $user_mongo_data['id'])->orWhere([
					['status', '=', Campaign::$CAMPAIGN_STATUS['campaign-preparing']],
					['status', '=', Campaign::$CAMPAIGN_STATUS['rfp-campaign']]
				])->orderBy('created_at', 'desc')->get();
		return response()->json($user_campaigns);
	}
	//Send request to multiple Sellers to add products for RFP campaign serach filters
	public function sendAddProductRequestToSellers(Request $request) {
		if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
		$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
		$user = User::select("email")->where('id', '=', $user_mongo['user_id'])->first();
		$notify_from_email = $user->email;
		
		if (isset($input['campaign_id'])) {
            $campaign_details = RFPSearchCriteria::where('campaign_id', '=', $input['campaign_id'])->first();
			if($campaign_details) {
				$seller_ids = $input['seller_ids'];
				$campaign_details->seller_ids = isset($input['seller_ids']) ? $input['seller_ids'] : $campaign_details->seller_ids;
				$campaign_details->save();
			}
		}
		
		$seller_ids = $input['seller_ids'];
		$sellers = [];

		foreach ($seller_ids as $seller_id) {
			$userMongoRecord = UserMongo::where('id', $seller_id)->first();
			
			if ($userMongoRecord) {
				$to_seller_id = $userMongoRecord->id;
				$to_seller_email = $userMongoRecord->email;
				
				// Create an associative array with seller_id as key and email as value
				$sellers[$to_seller_id] = $to_seller_email;
			}
		}
		//echo '<pre>'; print_r($sellers);
		//if($campaign_details->save()){
		
		event(new AddProductRequestedEvent([
			  'type' => Notification::$NOTIFICATION_TYPE['add-product-request'],
			  'from_id' => $notify_from_email,
			  'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['owner'],
			  'to_id' => "",
			  'to_client' => "",
			  'desc' => "",
			  'message' => "",
			  'data' => "",
			  'campaign_id' => $campaign_details['campaign_id']
			]));
			$notification_obj = new Notification; 
			$notification_obj->id = uniqid();
			$notification_obj->type = "add_product_request";
			$notification_obj->from_id = $notify_from_email;
			$notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['owner'];
			$notification_obj->to_id = "";
			$notification_obj->to_client = "";
			$notification_obj->desc = "";
			$notification_obj->message = "";
			$notification_obj->campaign_id = $campaign_details['campaign_id'];
			$notification_obj->status = 0;
			$notification_obj->save();

			/*$mail_tmpl_params = [
				'sender_email' => config('app.bbi_email'),
				//'receiver_name' => $mail_u_type,
				'receiver_name' => "",
				'mail_message' => ""
			];
			$mail_data = [
				'email_to' => 'sandhyarani.manelli@peopletech.com'
			];  
			Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data) {
				$message->to($mail_data['email_to'])->subject('RFP');
			});
			 if (!Mail::failures()) {
				return response()->json(['status' => 1, 'message' => "Request Sent Successfully"]);
			 }
		else{
			return response()->json(['status' => 0, 'message' => "Some thing went wrong. Please try again later."]);
		}*/
		//}
		//return response()->json(['status' => 1, 'message' => "Request Sent Successfully"]);
	}
	public function getRFPSearchCriteriaRequests() {
		
	}
	
}