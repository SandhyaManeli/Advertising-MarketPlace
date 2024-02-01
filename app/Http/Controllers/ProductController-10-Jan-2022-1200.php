<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Mail;
use App\Models\Product;
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
//use App\Models\TransactionQty;
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
use DB;

//date_default_timezone_set('America/Los_Angeles');
 
class ProductController extends Controller {

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
	

    /*
      ======= Product section ======
     */

public function getProducts(Request $request) {  
        $page_no = $request->input('page_no');
        $page_size = $request->input('page_size');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $format = $request->input('format');
        $budget = $request->input('budget');
	$product_name = $request->input('product_name');


        if (isset($page_no, $page_size) && !empty($page_no) && !empty($page_size)) {
            $offset = ($page_no - 1) * $page_size;

            if (isset($start_date) && isset($end_date)) {

               /*$unavailable_product_ids = ProductBooking::where([
                            [DB::raw("DATE(booked_from) <= $start_date")],
                            [DB::raw("DATE(booked_to) <= $end_date")]
                        ])->pluck('product_id');*/

		$explode_start_date = @explode('-',$start_date);
		$arranged_start_date = date('d-m-Y',strtotime($explode_start_date[1].'-'.$explode_start_date[0].'-'.$explode_start_date[2]));
		$explode_end_date = @explode('-',$end_date);
		$arranged_end_date = date('d-m-Y',strtotime($explode_end_date[1].'-'.$explode_end_date[0].'-'.$explode_end_date[2]));

		$startdate = date_create($arranged_start_date);
            	$enddate = date_create($arranged_end_date);

//$date_ranges = ProductBooking::select('booked_from', 'booked_to','product_id')->where('booked_from', '>=', $startdate)->where('booked_to', '<=', $enddate)->get()->toArray();
//echo'<pre>';print_r($date_ranges);exit;
            	$unavailable_product_ids = ProductBooking::where('booked_from', '>=', $startdate)->where('booked_to', '<=', $enddate)->pluck('product_id');

            }

            if (isset($format) && !empty($format)) {
		if($format != 'All'){
                	$product_array = [
                    		['type', '=', $format],
                    		['status', '=', Product::$PRODUCT_STATUS['approved']]
                	];
		}else{
			$product_array = [
                    		['status', '=', Product::$PRODUCT_STATUS['approved']]
                	];
		}
            } else {
                $product_array = [
                    ['status', '=', Product::$PRODUCT_STATUS['approved']]
                ];
            } 

		$product_name_like = '';
		if(isset($product_name) && !empty($product_name)){
			$product_name_like = $product_name;
		}

		$products = 0;
            if (isset($budget) && !empty($budget)) {
		if($product_name_like !=''){
                	if ($budget == 1) {
                    		if (isset($start_date) && isset($end_date))
                        		$products = Product::where($product_array)->where('title', 'LIKE',"%{$product_name_like}%")->whereIn('id', $unavailable_product_ids)->orderBy('default_price', 'asc')->skip($offset)->take((int) $page_size)->get();
                    		else
                        		$products = Product::where($product_array)->where('title', 'LIKE',"%{$product_name_like}%")->orderBy('default_price', 'asc')->skip($offset)->take((int) $page_size)->get();
                	} else if ($budget == 0) {
                    		if (isset($start_date) && isset($end_date))
                        		$products = Product::where($product_array)->where('title', 'LIKE',"%{$product_name_like}%")->whereIn('id', $unavailable_product_ids)->orderBy('default_price', 'desc')->skip($offset)->take((int) $page_size)->get();
                    		else
                        		$products = Product::where($product_array)->where('title', 'LIKE',"%{$product_name_like}%")->orderBy('default_price', 'desc')->skip($offset)->take((int) $page_size)->get();
                	}
		}else{
			if ($budget == 1) {
                    		if (isset($start_date) && isset($end_date))
                        		$products = Product::where($product_array)->whereIn('id', $unavailable_product_ids)->orderBy('default_price', 'asc')->skip($offset)->take((int) $page_size)->get();
                    		else
                        		$products = Product::where($product_array)->orderBy('default_price', 'asc')->skip($offset)->take((int) $page_size)->get();
                	} else if ($budget == 0) {
                    		if (isset($start_date) && isset($end_date))
                        		$products = Product::where($product_array)->whereIn('id', $unavailable_product_ids)->orderBy('default_price', 'desc')->skip($offset)->take((int) $page_size)->get();
                    		else
                        		$products = Product::where($product_array)->orderBy('default_price', 'desc')->skip($offset)->take((int) $page_size)->get();
                	}
		}
            } else { 
		if($product_name_like !=''){
                	if (isset($start_date) && isset($end_date))
                    		$products = Product::where($product_array)->where('title', 'LIKE',"%{$product_name_like}%")->whereIn('id', $unavailable_product_ids)->orderBy('created_at', 'desc')->skip($offset)->take((int) $page_size)->get();
                	else
                    		$products = Product::where($product_array)->where('title', 'LIKE',"%{$product_name_like}%")->orderBy('created_at', 'desc')->skip($offset)->take((int) $page_size)->get();
		}else{
			if (isset($start_date) && isset($end_date))
                    		$products = Product::where($product_array)->whereIn('id', $unavailable_product_ids)->orderBy('created_at', 'desc')->skip($offset)->take((int) $page_size)->get();
                	else
                    		$products = Product::where($product_array)->orderBy('created_at', 'desc')->skip($offset)->take((int) $page_size)->get();
		}
            }
		//$product_count = Product::all()->count();
		//$product_count = count($products);
		if($product_name_like !=''){
            		if (isset($start_date) && isset($end_date))
                		$product_count = Product::where($product_array)->where('title', 'LIKE',"%{$product_name_like}%")->whereIn('id', $unavailable_product_ids)->count();
            		else
                		$product_count = Product::where($product_array)->where('title', 'LIKE',"%{$product_name_like}%")->count();
		}else{
			if (isset($start_date) && isset($end_date))
                		$product_count = Product::where($product_array)->whereIn('id', $unavailable_product_ids)->count();
            		else
                		$product_count = Product::where($product_array)->count();
		}
            foreach ($products as $product) {
                $already_shortlisted = ShortlistedProduct::where([
                            ['product_id', '=', $product->id]
                        ])->get();
                if (count($already_shortlisted) > 0) {
                    $product->shortlisted = true;
                }
                $camapigns_count = ProductBooking::where('product_id', '=', $product->id)->pluck('campaign_id')->toArray();

                if ($camapigns_count) {
                    $camapigns_count = count(array_filter($camapigns_count));
                    $product->camapigns_count = $camapigns_count;
                }
            }

            $product_list_data = [
                "products" => $products,
		"json_response" => $request->input(),
                "page_count" => ceil($product_count / $page_size)
            ];
        } else {
            $products = Product::where([
                        ['status', '=', Product::$PRODUCT_STATUS['approved']]
                    ])->get();
            foreach ($products as $product) {
                $already_shortlisted = ShortlistedProduct::where([
                            ['product_id', '=', $product->id]
                        ])->get();
                if (count($already_shortlisted) > 0) {
                    $product->shortlisted = true;
                }
                $camapigns_count = ProductBooking::where('product_id', '=', $product->id)->pluck('campaign_id')->toArray();

                if ($camapigns_count) {
                    $camapigns_count = count(array_filter($camapigns_count));
                    $product->camapigns_count = $camapigns_count;
                }
            }
            $product_list_data = [
                "products" => $products,
		"json_response" => $request->input()
            ];
        }
        return response()->json($product_list_data);
    }

    public function getProductsSearchNPaginate(Request $request) {
        $page_no = $request->input('page_no');
        $page_size = $request->input('page_size');
        if (isset($page_no, $page_size) && !empty($page_no) && !empty($page_size)) {
            $offset = ($page_no - 1) * $page_size;
            if ($request->input('searchkey')) {
                $searchkey = $request->input('searchkey');
                $products_con = Product::Where('siteNo', 'like', "%$searchkey%")
                        ->orWhere('address', 'like', "%$searchkey%")
                        ->orWhere('area_name', 'like', "%$searchkey%")
                        ->orWhere('direction', 'like', "%$searchkey%");
                $count = $products_con->count();
                $products = $products_con->skip($offset)->take((int) $page_size)->get();
            } else {
                $products = Product::skip($offset)->take((int) $page_size)->get();
                $count = Product::count();
            }
            $product_list_data = [
                "products" => $products,
                "page_count" => ceil($count / (int) $page_size)
            ];
        } else {
            $products = Product::all();
            $product_list_data = [
                "products" => $products,
            ];
        }
        return response()->json($product_list_data);
    }

    public function getProductsForMap() {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];

        if ($user_mongo['user_type'] == 'owner') {
            $match_array = [
                '$and' => [
                    [
                        'client_mongo_id' => $user_mongo['client_mongo_id']
                    ],
                    [
                        "product_visibility" => ['$ne' => "0"]
                    ]
                ]
            ];
        } else {
            $match_array = array("product_visibility" => ['$ne' => "0"]);
        }
        $product_details = [
            '$push' => [
                'id' => '$id',
                'siteNo' => '$siteNo',
                'adStrength' => '$adStrength',
                'address' => '$address',
				'title'=>'$title',
				'addresstwo'=>'$addresstwo',
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
				'stripe_percent'=>'$stripe_percent'
				
				    
            ]
        ];
        $grouped_products = Product::raw(function($collection) use($product_details, $match_array) {
                    return $collection->aggregate(
                                    [
                                        ['$match' =>
                                            $match_array
                                        ],									
                                        [
                                            '$group' => [ 
                                                '_id' => ['lat' => '$lat', 'lng' => '$lng'],
                                                //'_id' => ['id' => '$id'],
                                                'product_details' => $product_details
                                            ]
                                        ]
                                    ]
                    );
                });
        return response()->json($grouped_products);
    }
	
	//Filter Shortlisted Products from Map for User
	public function getProductsForMapfilterShortlist() {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
		
		$match_array = [];
		
		$shortlisted_products_id_arr = [];
		$getshortlisteditems = $this->getShortlistedProducts();
		if(isset($getshortlisteditems) && !empty($getshortlisteditems)){
		$obj = json_decode(json_encode($getshortlisteditems), true);
		$res = $obj['original']['shortlisted_products'];
        //$curdate1 = date_create(date("Y-m-d"));
        $curdate1 = iso_to_mongo_date(date("Y-m-d"));
		foreach($res as $res){
			$shortlisted_products_id_arr[] = $res['product_id'];
		}
		}else{
			$shortlisted_products_id_arr = [];
		}
        if ($user_mongo['user_type'] == 'owner') {
            $match_array = [
                '$and' => [
                    [
                        'client_mongo_id' => $user_mongo['client_mongo_id']
                    ],
                    [
                        "product_visibility" => ['$ne' => "0"]
                    ],
                    [
                        "to_date" => ['$gte' => $curdate1]
                    ],
                    [
                        "id"=>['$nin' => $shortlisted_products_id_arr]
                    ]
                ]
            ];
        } else {
            //$match_array = array("product_visibility" => ['$ne' => "0"]);
			$match_array = [
                '$and' => [
                    [
                        "product_visibility" => ['$ne' => "0"]
                    ],
                    [
                        "to_date" => ['$gte' => $curdate1]
                    ],
					[
						"id"=>['$nin' => $shortlisted_products_id_arr]
					]
                ]
            ];
        }        
		 
        $product_details = [
            '$push' => [
                'id' => '$id',
                'siteNo' => '$siteNo',
                'adStrength' => '$adStrength',
                'address' => '$address',
				'created_at'=>'$created_at',
				'title'=>'$title',
				//'from_date' => date('Y-m-d',strtotime($from_date)),
				'from_date'=>'$from_date',
				//'to_date' => date('Y-m-d',strtotime($to_date)),
				'to_date'=>'$to_date',
				'addresstwo'=>'$addresstwo',
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
				'stripe_percent'=>'$stripe_percent'
				      
            ]
        ];
         
        $grouped_products = Product::raw(function($collection) use($product_details, $match_array) {
            return $collection->aggregate(
                [ 
					['$match' => $match_array],                                  
                    [
                        '$group' => [ 
                            '_id' => ['lat' => '$lat', 'lng' => '$lng', 'id' => '$id'],
                            'product_details' => $product_details
                        ]
                    ]
                ]
            );
        });
                $finalarray = [];
                $product_details_new = $grouped_products;
		foreach($product_details_new as $product_details){
            
            $product_id = $product_details->id;

            $product = Product::where('id', '=', $product_id->id)->first();
            $running_campaign_details = Campaign::where([
                ['product_id', '=', $product_id->id],
                ['status', '=', Campaign::$CAMPAIGN_STATUS['running']]
    ]);
    $campaigns_with_product = Campaign::where([
                ['product_id', '=', $product_id->id],
                ['status', '<', Campaign::$CAMPAIGN_STATUS['running']]
            ])->get();
    if ($running_campaign_details->count() > 0) {
        $running_campaign_details = $running_campaign_details->first();
    } else {
        $running_campaign_details = (object) [];
    }
    $savedcampaignscount = ProductBooking::where([
        ['product_id', '=', $product_id->id],
        ['product_status', '=', Campaign::$CAMPAIGN_STATUS['campaign-preparing']]
    ])->count();
    $shortlistedcount = ShortListedProduct::where([
        ['product_id', '=', $product_id->id]
    ])->count();
    
    $watchingthisproduct = $savedcampaignscount + $shortlistedcount;

    $result_arr = [
        'running_campaign_details' => $running_campaign_details,
        'campaigns_with_product' => $campaigns_with_product,
        'savedcampaignscount'=> $savedcampaignscount,
        'shortlistedcount'=> $shortlistedcount,
        'watchingthisproduct'=> $watchingthisproduct
    ];
        array_push($finalarray, array_merge($product_details->toArray(), $result_arr));
    }
        usort($finalarray, [$this, 'date_compare']);
        return response()->json($finalarray);
    }
    
    public function date_compare($element1, $element2) {
        $utcdatetime1 = $element1['product_details'][0]->created_at;
        $datetime_1 = $utcdatetime1->toDateTime();
        $datetime1 = strtotime($datetime_1->format('Y-m-d H:i:s.uZ'));

        $utcdatetime2 = $element2['product_details'][0]->created_at;
        $datetime_2 = $utcdatetime2->toDateTime();
        $datetime2 = strtotime($datetime_2->format('Y-m-d H:i:s.uZ'));

        return $datetime2 - $datetime1;
    }

    public function getProductDetails($product_id) {
        $product = Product::where('id', '=', $product_id)->first();
        $running_campaign_details = Campaign::where([
                    //['products.id', '=', $product_id],
                    ['product_id', '=', $product_id],
                    ['status', '=', Campaign::$CAMPAIGN_STATUS['running']]
        ]);
        $campaigns_with_product = Campaign::where([
                    //['products.id', '=', $product_id],
                    ['product_id', '=', $product_id],
                    ['status', '<', Campaign::$CAMPAIGN_STATUS['running']]
                ])->get();
        if ($running_campaign_details->count() > 0) {
            $running_campaign_details = $running_campaign_details->first();
        } else {
            $running_campaign_details = (object) [];
        }
        $savedcampaignscount = ProductBooking::where([
            ['product_id', '=', $product_id],
            ['product_status', '=', Campaign::$CAMPAIGN_STATUS['campaign-preparing']]
        ])->count();
        $shortlistedcount = ShortListedProduct::where([
            ['product_id', '=', $product_id]
        ])->count();
        
        $watchingthisproduct = $savedcampaignscount + $shortlistedcount;

        $result_arr = [
            'product_details' => $product,
            'running_campaign_details' => $running_campaign_details,
            'campaigns_with_product' => $campaigns_with_product,
            'savedcampaignscount'=> $savedcampaignscount,
            'shortlistedcount'=> $shortlistedcount,
            'watchingthisproduct'=> $watchingthisproduct
        ];
        return response()->json($result_arr);
    }

    public function saveProduct(Request $request) {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        $input = $input['product'];
        $product_img_path = base_path() . '/html/uploads/images/products';
        $product_symbol_path = base_path() . '/html/uploads/images/symbols';

        $format = Format::where('id', '=', $input['type'])->first();
       // $area = Area::where('id', '=', $input['area'])->first();
        if (isset($input['client'])) {
            $client = ClientMongo::where('id', '=', $input['client'])->first();
        }

        if (isset($input['id'])) {
            $this->validate($request, [
                'image' => 'max:307200',
                'symbol' => 'max:102400'
            ]);
// handling editing of product
            $product_obj = Product::where('id', '=', $input['id'])->first();
            $from_owner = ($product_obj->status == Product::$PRODUCT_STATUS['requested']) ? true : false;
            if ($from_owner) {
                $this->validate($request, [
                    'product.direction' => 'required',
                    'product.lat' => 'required',
                    'product.lng' => 'required',
                   // 'symbol' => 'required',
                        ], [
                    'product.direction.required' => 'Direction field is required',
                    'product.lat.required' => 'Latitude is required',
                    'product.lng.required' => 'Longitude is required',
                    //'symbol.required' => 'Direction image is required'
                ]);
            }
            if (isset($input['siteNo']) && !empty($input['siteNo']) && $product_obj->status == Product::$PRODUCT_STATUS['approved']) {
                $repeated_site_no = Product::where([
                            ['siteNo', '=', $input['siteNo']],
                            ['id', '<>', $input['id']]
                        ])->count();
                if ($repeated_site_no > 0) {
                    return response()->json(['status' => 0, 'message' => 'The site number provided already exists. Please edit the product instead.']);
                }
            }
            $product_obj->siteNo = isset($input['siteNo']) ? $input['siteNo'] : $product_obj->siteNo;
            $product_obj->adStrength = isset($input['adStrength']) ? $input['adStrength'] : $product_obj->adStrength;
            $product_obj->address = isset($input['address']) ? $input['address'] : $product_obj->address;
            $product_obj->area = isset($input['area']) ? $input['area'] : $product_obj->area;
            $product_obj->client_mongo_id = isset($input['client']) ? $input['client'] : $product_obj->client_mongo_id;
            $product_obj->client_name = isset($client) ? $client->name : $product_obj->client_name;
            $product_obj->direction = isset($input['direction']) ? $input['direction'] : $product_obj->direction;
            $product_obj->default_price = isset($input['default_price']) ? $input['default_price'] : "";
            /*if ($request->hasFile('image')) {
                if ($request->file('image')->move($product_img_path, $request->file('image')->getClientOriginalName())) {
                    $product_obj->image = "/uploads/images/products/" . $request->file('image')->getClientOriginalName();
                }
            }*/
			  if ($this->request->hasFile('image')) {
                foreach ($this->request->file('image') as $key => $val) {
                    $imageNmae = \Carbon\Carbon::now()->timestamp . $val->getClientOriginalName();
                    if ($val->move($product_img_path, $imageNmae)) {
                        $imageArray[] = "/uploads/images/products/" . $imageNmae;
                    }
                }
                $product_obj->image = $imageArray;
            }
			$product_obj->videoUrl = isset($input['videoUrl']) ? $input['videoUrl'] : $product_obj->videoUrl;
            $product_obj->impressions = isset($input['impressions']) ? $input['impressions'] : $product_obj->impressions;
			$product_obj->eyeimpression = isset($input['eyeimpression']) ? $input['eyeimpression'] : $product_obj->eyeimpression;
			$product_obj->cancelation = isset($input['cancelation']) ? $input['cancelation'] : $product_obj->cancelation;
			
            $product_obj->lat = isset($input['lat']) ? $input['lat'] : $product_obj->lat;
            $product_obj->lighting = isset($input['lighting']) ? $input['lighting'] : $product_obj->lighting;
            $product_obj->lng = isset($input['lng']) ? $input['lng'] : $product_obj->lng;
            if ($request->hasFile('symbol')) {
                if ($request->file('symbol')->move($product_symbol_path, $request->file('symbol')->getClientOriginalName())) {
                    $product_obj->symbol = "/uploads/images/symbols/" . $request->file('symbol')->getClientOriginalName();
                }
            }
           // $product_obj->panelSize = isset($input['panelSize']) ? $input['panelSize'] : $product_obj->panelSize;
		    $product_obj->panelSize = (isset($input['width']) && isset($input['height'])) ? $input['height'] . '*' . $input['width'] : $product_obj->panelSize;
            $product_obj->type = isset($input['type']) ? $input['type'] : $product_obj->type;
            $product_obj->format_name = isset($format) ? $format->name :$product_obj->format_name;
            $product_obj->country_name = isset($area) ? $area->country_name : $product_obj->country_name;
            $product_obj->country = isset($input['country']) ? $input['country'] : $product_obj->country;
            $product_obj->state_name = isset($area) ? $area->state_name : $product_obj->state_name;
            $product_obj->state = isset($input['state']) ? $input['state'] : $product_obj->state;
            $product_obj->city_name = isset($area) ? $area->city_name :$product_obj->city_name;
            $product_obj->city = isset($input['city']) ? $input['city'] : $product_obj->city;
			  $product_obj->nearlandmark = isset($input['nearlandmark']) ? $input['nearlandmark'] : $product_obj->nearlandmark;
			   $product_obj->visibility = isset($input['visibility']) ? $input['visibility'] : $product_obj->visibility;
        $product_obj->audience = isset($input['audience']) ? $input['audience'] : $product_obj->audience;
        $product_obj->targetedaudience = isset($input['targetedaudience']) ? $input['targetedaudience'] : $product_obj->targetedaudience;
		
            $product_obj->area_name = isset($area) ? $area->name : $product_obj->area_name;
            $product_obj->status = Product::$PRODUCT_STATUS['approved'];
            if ($product_obj->save()) {
                try {
                    dispatch(new UpdateProductEverywhere($product_obj));
//Log::info("job completed: UpdateProductEverywhere with data" . serialize($product_obj));
                } catch (Exception $ex) {
//Log::error($ex);
                }
// notifications and emails for owner
                if ($from_owner) {
                    $product_owner_email = ClientMongo::where('id', '=', $product_obj->client_mongo_id)->first()->email;
                   $noti_array = [
                        'type' => Notification::$NOTIFICATION_TYPE['product-approved'],
                        'from_id' => null,
                        'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['owner'],
                        'to_id' => $product_obj->client_mongo_id,
                        'to_client' => $product_obj->client_mongo_id,
                        'desc' => "Product approved",
                        'message' => "Your product has been added in the inventory",
                        'data' => ["product_id" => $product_obj->id]
                    ];
                    $mail_array = [
                        'mail_tmpl_params' => [
                            'sender_email' => config('app.bbi_email'),
                            'receiver_name' => "",
                            //'mail_message' => "'The product you requested to be added to Billboards inventory has been approved"
                            'mail_message' => "'The product you requested to be added to Advertising Marketplace inventory has been approved"
                        ],
                        //'subject' => 'New product Approved! - Billboards India'
                        'subject' => 'New product Approved! - Advertising Marketplace'
                    ];
					
					$notification_obj = new Notification;
					$notification_obj->id = uniqid();
                    $notification_obj->type = "product";
                    $notification_obj->from_id=null;
                    $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['owner'];
                    $notification_obj->to_id = $product_obj->client_mongo_id;
                    $notification_obj->to_client = $product_obj->client_mongo_id;
                    $notification_obj->desc = "Product approved";
                    $notification_obj->message ="Your product has been added in the inventory";
                    $notification_obj->product_id = $product_obj->id;
					$notification_obj->status = 0;
                    $notification_obj->save();
					
                    event(new ProductApprovedEvent($noti_array,$mail_array));
					
                  
// send email
                    $mail_tmpl_params = [
                        'sender_email' => config('app.bbi_email'), //, 
                        'receiver_name' => "",
                        //'mail_message' => 'The product you requested to be added to Billboards inventory has been approved by the admin.'
                        'mail_message' => 'The product you requested to be added to Advertising Marketplace inventory has been approved by the admin.'
                    ];
                    Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($product_owner_email) {
                        //$message->bcc([$product_owner_email])->subject('Requested product approved - Billboards India');
                        $message->bcc([$product_owner_email])->subject('Requested product approved - Advertising Marketplace');
                    });
                }
                return response()->json(["status" => "1", "message" => "product saved successfully."]);
            } else {
                return response()->json(["status" => "0", "message" => "Failed to save product."]);
            }
// handling editing of product ends
        } else {
            $this->validate($request, [
                'product.adStrength' => '',
               // 'product.address' => 'required',
               // 'product.area' => 'required',
                'product.client' => 'required',
                'product.direction' => 'required',
                'product.default_price' => 'integer',
                'image' => 'required',
                'product.impressions' => '',
                'product.lat' => 'required',
                'product.lighting' => '',
                'product.lng' => 'required',
               // 'symbol' => 'required',
                'product.panelSize' => '',
                'product.type' => 'required',
                'product.siteNo' => 'required'
                    ], [
                'product.address.required' => 'Product address is required',
               // 'product.area.required' => 'Product area is required',
                'product.client.required' => 'Product client is required',
                'product.direction.required' => 'Product direction is required',
                'image.required' => 'Image is required',
                'product.lat.required' => 'Product Latitude is required',
                'product.lng.required' => 'Product Longitude is required',
                //'symbol.required' => 'Symbol is required',
                'product.type.required' => 'Product type is required',
                'product.siteNo.required' => 'Product site number is required'
                    ]
            );
            $product_obj = new Product;
            $product_obj->id = uniqid();
            $product_obj->siteNo = isset($input['siteNo']) ? "BI-" . $input['siteNo'] : "";
            $repeated_site_no = Product::where('siteNo', '=', $product_obj->siteNo)->count();
            if ($repeated_site_no > 0) {
                return response()->json(['status' => 0, 'message' => 'The site number provided already exists. Please edit the product instead.']);
            }
            $product_obj->adStrength = isset($input['adStrength']) ? $input['adStrength'] : "";
            $product_obj->address = isset($input['address']) ? $input['address'] : "";
            $product_obj->area = isset($input['area']) ? $input['area'] : "";
            $product_obj->client_mongo_id = isset($input['client']) ? $input['client'] : "";
            $product_obj->client_name = isset($client) ? $client->name : "";
            $product_obj->direction = isset($input['direction']) ? $input['direction'] : "";
            $product_obj->default_price = isset($input['default_price']) ? $input['default_price'] : "";
            $product_obj->image = "";
            /*if ($request->hasFile('image')) {
                if ($request->file('image')->move($product_img_path, $request->file('image')->getClientOriginalName())) {
                    $product_obj->image = "/uploads/images/products/" . $request->file('image')->getClientOriginalName();
                }
            }*/
			
            if ($this->request->hasFile('image')) {
                foreach ($this->request->file('image') as $key => $val) {
                    $imageNmae = \Carbon\Carbon::now()->timestamp . $val->getClientOriginalName();
                    if ($val->move($product_img_path, $imageNmae)) {
                        $imageArray[] = "/uploads/images/products/" . $imageNmae;
                    }
                }
                $product_obj->image = $imageArray;
            }
			 $product_obj->videoUrl = isset($input['videoUrl']) ? $input['videoUrl'] : "";
            $product_obj->impressions = isset($input['impressions']) ? $input['impressions'] : "";
			$product_obj->eyeimpression = isset($input['eyeimpression']) ? $input['eyeimpression'] : "";
			$product_obj->cancelation = isset($input['cancelation']) ? $input['cancelation'] : "";
            $product_obj->lat = isset($input['lat']) ? $input['lat'] : "";
            $product_obj->lighting = isset($input['lighting']) ? $input['lighting'] : "";
            $product_obj->lng = isset($input['lng']) ? $input['lng'] : "";
            //$product_obj->symbol = "";
            if ($request->hasFile('symbol')) {
                if ($request->file('symbol')->move($product_symbol_path, $request->file('symbol')->getClientOriginalName())) {
                    $product_obj->symbol = "/uploads/images/symbols/" . $request->file('symbol')->getClientOriginalName();
                }
            }
           // $product_obj->panelSize = isset($input['panelSize']) ? $input['panelSize'] : "";
		    $product_obj->panelSize = (isset($input['width']) && isset($input['height'])) ? $input['height'] . '*' . $input['width'] : "";
            $product_obj->type = isset($input['type']) ? $input['type'] : "";
            $product_obj->format_name = isset($format) ? $format->name : "";
            $product_obj->country_name = isset($area) ? $area->country_name : "";
            $product_obj->country = isset($input['country']) ? $input['country'] : "";
            $product_obj->state_name = isset($area) ? $area->state_name : "";
            $product_obj->state = isset($input['state']) ? $input['state'] : "";
            $product_obj->city_name = isset($area) ? $area->city_name : "";
            $product_obj->city = isset($input['city']) ? $input['city'] : "";
			$product_obj->nearlandmark = isset($input['nearlandmark']) ? $input['nearlandmark'] : "";
			$product_obj->visibility = isset($input['visibility']) ? $input['visibility'] : "";
			$product_obj->audience = isset($input['audience']) ? $input['audience'] : "";
			$product_obj->targetedaudience = isset($input['targetedaudience']) ? $input['targetedaudience'] : "";
            $product_obj->area_name = isset($area) ? $area->name : "";
            $product_obj->status = Product::$PRODUCT_STATUS['approved'];
            if ($product_obj->save()) {
                return response()->json(["status" => "1", "message" => "product saved successfully."]);
            } else {
                return response()->json(["status" => "0", "message" => "Failed to save product."]);
            }
// saving new product ends
        }
    }

    public function deleteProduct($product_id) {
// block if product is shortlisted or in a campaign
        $product_to_del = Product::where('id', '=', $product_id)->first();
// check if it's shortlisted
        $sl_products = ShortListedProduct::where('product_id', '=', $product_id)->get()->toArray();
        if (count($sl_products) > 0) {
            return response()->json(["status" => "0", "message" => "Failed to delete product. Shortlisted by a user."]);
        }
// check if it's in a campaign
        $campaigns_with_product_in_question = CampaignProduct::where('product_id', '=', $product_id)->get()->toArray();
        if (count($campaigns_with_product_in_question) > 0) {
            return response()->json(["status" => "0", "message" => "Failed to delete product. Product in a campaign."]);
        } else {
            $success = $product_to_del->delete();
            if ($success) {
                return response()->json(["status" => "1", "message" => "Product deleted successfully."]);
            } else {
                return response()->json(["status" => "1", "message" => "An error occured while deleting the product."]);
            }
        }
    }

    public function saveProductsBulk(Request $request) {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        try {
            Product::saveBulkProducts($input);
            return response()->json(["message" => "Mediums added successfully."]);
        } catch (Exception $ex) {
            return response()->json(["message" => "Failed to add mediums to database. Please try again."]);
        }
    }

// Shortlisting products
    public function getShortlistedProducts() {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
		//echo "<pre>user_mongo"; print_r($user_mongo);exit;
        $shortlisted_products_arr = [];
        $shortlistedsum = 0;
        $cpmsum = 0;
        $impressionSum = 0;
        $impressionSum4 = 0;
        if ($user_mongo['user_type'] == "basic") {
            $sl_usr_products = ShortListedProduct::where('user_mongo_id', '=', $user_mongo['id'])->orderBy('from_date', 'asc')
                    ->where(function($q) {
                        $q->whereIn('format_type', [null, Format::$FORMAT_TYPE['ooh']]);
                    })
                    ->get();
			//echo "<pre>sl_usr_products"; print_r($sl_usr_products);exit;
			if(isset($sl_usr_products) && !empty($sl_usr_products) && (count($sl_usr_products)>0)){
				foreach ($sl_usr_products as $sup) {
					$product_details = Product::where('id', '=', $sup->product_id)->first();
					//$product_details = (array)$product_details;
					//echo "<pre>product_details"; print_r($product_details);exit;
					//echo "<pre>product_details"; print_r($product_details->cpm);exit;
					//$date_diff=date_diff(($sup->to_date),($sup->from_date));
					$date_diff=date_diff(date_create($sup->to_date),date_create($sup->from_date));
					if(isset($date_diff) && !empty($date_diff)){
					//echo "<pre>date_diff"; print_r($date_diff);exit;
					$daysCount = $date_diff->days + 1;
					}else{
						$daysCount = 0;
					}
					//echo "<pre>date_diff"; print_r($date_diff);exit;
					//$daysCount = $date_diff->days + 1;
					//echo "<pre>daysCount"; print_r($daysCount);exit;
					//$date_diff=0;
					if(isset($product_details->fix) && $product_details->fix=="Fixed"){
						//$price = $product_details->default_price;
						$price = $sup->price;
						$priceperday = $price;
						$priceperselectedDates = $priceperday;
						//$shortlistedsum+= $sup->price;
						$shortlistedsum+= $priceperselectedDates*$sup->quantity;
						$sup->price = $priceperselectedDates;
					}else{
						$price = $product_details->default_price;
						if($daysCount < $product_details->minimumdays){
                            $priceperday = $sup->price;
                            $priceperselectedDates = $priceperday;
                        }else{
                            $priceperday = $price/28;
                            $priceperselectedDates = $priceperday * $daysCount;
                        }	
						
				
						//$shortlistedsum+= $sup->price;
						$shortlistedsum+= $priceperselectedDates*$sup->quantity;
						$sup->price = $priceperselectedDates;
					}
					//$cpmsum+= $product_details->cpm;
					if(isset($product_details->cpm) && !empty($product_details->cpm) && ($product_details->cpm != 'Infinity')){
					$cpmsum+= $product_details->cpm;
					}else{
						$cpmsum+= 0;
					}
					if(isset($product_details->secondImpression) && ($product_details->secondImpression>0)){
						if(isset($product_details->fix) && $product_details->fix=="Fixed"){
							$impressions = $product_details->secondImpression;
							$impressionsperday = (int)($impressions);
							$impressionsperselectedDates = $impressionsperday;
							//$impressionSum+= $product_details->secondImpression;
							$impressionSum+= $impressionsperselectedDates;
							$product_details->secondImpression = round($impressionsperselectedDates, 2);
							$cpmcal = ($shortlistedsum/$impressionSum) * 1000;
							$product_details->cpm = $cpmcal;
						}else{
							$impressions = $product_details->secondImpression;
							$impressionsperday = (int)($impressions/7);
							$impressionsperselectedDates = $impressionsperday * $daysCount;
							//$impressionSum+= $product_details->secondImpression;
							$impressionSum+= $impressionsperselectedDates;
							$product_details->secondImpression = round($impressionsperselectedDates, 2);
							$cpmcal = ($shortlistedsum/$impressionSum) * 1000;
							$product_details->cpm = $cpmcal;
						}
						
					}else{
						$impressions=0;
						$impressionsperday = 0;
						$impressionsperselectedDates = 0;
						//$impressionSum+= $product_details->secondImpression;
						$impressionSum+= 0;
						$product_details->secondImpression = 0;
						$cpmcal = 0;
						$product_details->cpm = 0;
					}
					//echo "<pre>price"; print_r($shortlistedsum);	
					array_push($shortlisted_products_arr, array_merge($product_details->toArray(), $sup->toArray()));
					//array_push($shortlisted_products_arr, array_merge($product_details, $sup->toArray()));
				}
			}else{	
				//echo 'else';exit;
				$shortlisted_products_arr = [];
				$shortlistedsum= 0;
				$cpmsum= 0;
				$impressionSum=0;
				$price = 0;
				$priceperday = 0;
				$priceperselectedDates = 0;
				$impressions = 0;
				$impressionsperday = 0;
				$impressionsperselectedDates = 0;
				$date_diff=0;
				//echo "<pre>price"; print_r($impressionSum4);	exit;
			}
						
			//echo "<pre>shortlisted_products_arr"; print_r($shortlisted_products_arr);exit;	
        } else {
            $sl_usr_products = ShortListedProduct::where('client_mongo_id', '=', $user_mongo['client_mongo_id'])->get();
			if(isset($sl_usr_products) && !empty($sl_usr_products) && (count($sl_usr_products)>0)){
				foreach ($sl_usr_products as $sup) {
					$product_details = Product::where('id', '=', $sup->product_id)->first();
					//echo "<pre>price"; print_r($sup->price);	
					//echo "<pre>product_details"; print_r($product_details);exit;
					/*$shortlistedsum+= $sup->price;
					$cpmsum+= $product_details->cpm;
					$impressionSum+= $product_details->secondImpression;*/
					//echo "<pre>daysCount"; print_r($sup->to_date);exit;
					$date_diff=date_diff(date_create($sup->to_date),date_create($sup->from_date));
					if(isset($date_diff) && !empty($date_diff)){
					//echo "<pre>date_diff"; print_r($date_diff);exit;
					$daysCount = $date_diff->days + 1;
					}else{
						$daysCount = 0;
					}
					//echo "<pre>date_diff"; print_r($date_diff);exit;
					//$daysCount = $date_diff->days + 1;
					//echo "<pre>daysCount"; print_r($daysCount);exit;
					//$date_diff=0;
					if(isset($product_details->fix) && $product_details->fix=="Fixed"){
						//$price = $product_details->default_price;
						$price = $sup->price;
						$priceperday = $price;
						$priceperselectedDates = $priceperday;
						//$shortlistedsum+= $sup->price;
						$shortlistedsum+= $priceperselectedDates*$sup->quantity;
						$sup->price = $priceperselectedDates;
						//$cpmsum+= $product_details->cpm;
					}else{
						$price = $product_details->default_price;
                        if($daysCount < $product_details->minimumdays){
                            $priceperday = $sup->price;
                            $priceperselectedDates = $priceperday;
                        }else{
                            $priceperday = $price/28;
                            $priceperselectedDates = $priceperday * $daysCount;
                        }						
						
						//$shortlistedsum+= $sup->price;
						$shortlistedsum+= $priceperselectedDates*$sup->quantity;
						$sup->price = $priceperselectedDates;
						//$cpmsum+= $product_details->cpm;
					}
					
					if(isset($product_details->cpm) && !empty($product_details->cpm) && ($product_details->cpm != 'Infinity')){
					$cpmsum+= $product_details->cpm;
					}else{
						$cpmsum+= 0;
					}
					if(isset($product_details->secondImpression) && ($product_details->secondImpression>0)){
						if(isset($product_details->fix) && $product_details->fix=="Fixed"){
							$impressions = $product_details->secondImpression;
							$impressionsperday = (int)($impressions);
							$impressionsperselectedDates = $impressionsperday;
							//$impressionSum+= $product_details->secondImpression;
							$impressionSum+= $impressionsperselectedDates;
							$product_details->secondImpression = round($impressionsperselectedDates, 2);
							$cpmcal = ($shortlistedsum/$impressionSum) * 1000;
							$product_details->cpm = $cpmcal;
						}else{
							$impressions = $product_details->secondImpression;
							$impressionsperday = (int)($impressions/7);
							$impressionsperselectedDates = $impressionsperday * $daysCount;
							//$impressionSum+= $product_details->secondImpression;
							$impressionSum+= $impressionsperselectedDates;
							$product_details->secondImpression = round($impressionsperselectedDates, 2);
							$cpmcal = ($shortlistedsum/$impressionSum) * 1000;
							$product_details->cpm = $cpmcal;
						}
						
					}else{
						$impressions=0;
						$impressionsperday = 0;
						$impressionsperselectedDates = 0;
						//$impressionSum+= $product_details->secondImpression;
						$impressionSum+= 0;
						$product_details->secondImpression = 0;
						$cpmcal = 0;
						$product_details->cpm = 0;
					}
					//echo "<pre>price"; print_r($shortlistedsum);
					array_push($shortlisted_products_arr, array_merge($product_details->toArray(), $sup->toArray()));
				}
			}else{
				//echo 'else';exit;
				$shortlisted_products_arr = [];
				$shortlistedsum= 0;
				$cpmsum= 0;
				$impressionSum=0;
				$price = 0;
				$priceperday = 0;
				$priceperselectedDates = 0;
				$impressions = 0;
				$impressionsperday = 0;
				$impressionsperselectedDates = 0;
				$date_diff=0;
			}
        }
		
		//$cpmval = ($shortlistedsum/$impressionSum)*4;
		 //$impressionSum4 = $impressionSum * 4;
		 //$impressionSum4 = $impressionSum; /* As discussed with Richard on July 14) */
		 $impressionSum4 = round($impressionSum, 2); /* As discussed with Richard on July 14) */
		 if($impressionSum4>0){
			$cpmval = ($shortlistedsum/$impressionSum4) * 1000;
		 }else{
			 $cpmval = 0;
		 }
		 //echo 'cpmval'.$cpmval;exit;
		 //return response()->json($shortlisted_products_arr);
		return response()->json(["shortlisted_products" => $shortlisted_products_arr, "shortlistedsum" => $shortlistedsum, "cpmval" => $cpmval, "impressionSum" => $impressionSum4, 'pricechk'=>$price, 'priceperday'=>$priceperday, 'priceperselectedDates'=>$priceperselectedDates, 'impressionschk'=>$impressions, 'impressionsperday'=>$impressionsperday, 'impressionsperselectedDates'=>$impressionsperselectedDates, 'date_diff'=>$date_diff]);
    }

    public function changeProductPrice(Request $request) {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }

        if (isset($input['id'])) {
            $product_obj = Product::where('id', '=', $input['id'])->first();
            $product_obj->default_price = isset($input['default_price']) ? $input['default_price'] : "";
            if ($product_obj->save()) {
                return response()->json(["status" => "1", "message" => "Price saved successfully.", "data" => $product_obj]);
            } else {
                return response()->json(["status" => "0", "message" => "Failed to save product."]);
            }
            // handling editing of product ends
        }
    }

    public function changeCampaignProductPrice(Request $request) {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all(); 
        }
        if (isset($input['campaign_id']) && isset($input['product_id'])) {
			 $campaign = Campaign::Where('id', '=', $input['campaign_id'])->first();
            $campaign_product = ProductBooking::where('campaign_id', '=', $input['campaign_id'])->where('product_id', '=', $input['product_id'])->where('id', '=', $input['product'])->first();
            if (isset($input['admin_price'])) {
                $previous_quote_change = CampaignQuoteChange::where('campaign_id', '=', $input['campaign_id'])->orderBy('iteration', 'desc')->first();
                $quote_change_obj = new CampaignQuoteChange;
                if (isset($previous_quote_change) && !empty($previous_quote_change))
                    $quote_change_obj->iteration = $previous_quote_change->iteration + 1;
                else
                    $quote_change_obj->iteration = 1;
                $quote_change_obj->campaign_id = $this->input['campaign_id'];
                //$quote_change_obj->remark = 'BBI has give price Rs. ' . $input['admin_price']; // . ' for ' . $input['product']
                $quote_change_obj->remark = 'AMP has give price Rs. ' . $input['admin_price']; // . ' for ' . $input['product']
                $quote_change_obj->type = 'bbi';
                if ($quote_change_obj->save()) {
                    $campaign_product->admin_price = (int)$input['admin_price'];
                    $campaign_obj = Campaign::where('id', '=', $input['campaign_id'])->first();
                   // $campaign_obj->status = Campaign::$CAMPAIGN_STATUS['quote-given'];
                    $campaign_obj->save();
                }
            } else 
				if(isset($input['owner_price']) && $campaign->status<600) {
                $campaign_product->owner_price = (int)$input['owner_price'];
            }
			else{
				 return response()->json(["status" => "0", "message" => "You Can't edit the Price at this stage."]);
			}

            if ($campaign_product->save()) {
                return response()->json(["status" => "1", "message" => "Price saved successfully.", "data" => $campaign_product]);
            } else {
                $quote_change_obj->delete();
                return response()->json(["status" => "0", "message" => "Failed to save product."]);
            }
        }
        // handling editing of product ends
    }

    public function productVisibility($product_id) {
        if ($product_id) {
            $product_obj = Product::where('id', '=', $product_id)->first();
            $product_obj->product_visibility = isset($this->input['product_visibility']) ? $this->input['product_visibility'] : "";
            if ($product_obj->save()) {
                return response()->json(["status" => "1", "message" => "Visibility updated successfully.", "data" => $product_obj]);
            } else {
                return response()->json(["status" => "0", "message" => "Failed to save product."]);
            }
            // handling editing of product ends
        }
    }

    /*public function shortListProduct(Request $request) {
        $this->validate($request, [
            'product_id' => 'required',
            'dates' => 'required'
                ], [
            'product_id.required' => 'Product id is required',
            'dates.required' => 'Please provide dates you\'re selecting the product for'
                ]
        );
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $overlapping_dates = [];
        if ($user_mongo['user_type'] == "basic") {
            $product_occurances = ShortListedProduct::where([
                        ['user_mongo_id', '=', $user_mongo['id']],
                        ['product_id', '=', $input['product_id']]
                    ])->get();
            foreach ($product_occurances as $po) {
                foreach ($input['dates'] as $dr) {
                    if ($po->from_date <= $dr['endDate'] && $po->to_date >= $dr['startDate']) {
                        array_push($overlapping_dates, $dr);
                    }
                }
            }
        } else {
            $product_occurances = ShortListedProduct::where([
                        ['client_mongo_id', '=', $user_mongo['client_mongo_id']],
                        ['product_id', '=', $input['product_id']]
                    ])->get();
            foreach ($product_occurances as $po) {
                foreach ($input['dates'] as $dr) {
                    if ($po->from_date >= $dr['endDate'] && $po->to_date <= $dr['startDate']) {
                        array_push($overlapping_dates, $dr);
                    }
                }
            }
        }
        if (count($overlapping_dates) == 0) {
            $success = true;
            foreach ($input['dates'] as $dr) {
				$product = Product::where('id', '=', $input['product_id'])->first();
                $sl_product_obj = New ShortListedProduct;
                $sl_product_obj->id = uniqid();
                if ($user_mongo['user_type'] == "basic") {
                    $sl_product_obj->user_mongo_id = $user_mongo['id'];
                } else {
                    $sl_product_obj->client_mongo_id = $user_mongo['client_mongo_id'];
                }
				if($product->type !='Bulletin' && !isset($input['numOfSlots'])){
					 //return response()->json(["status" => "0", "message" => "Number of Slots is required."]);
					 $input['numOfSlots'] = 1;
				}
                $sl_product_obj->product_id = isset($input['product_id']) ? $input['product_id'] : "";
                $sl_product_obj->format_type = Format::$FORMAT_TYPE['ooh'];
                $sl_product_obj->from_date = iso_to_mongo_date($dr['startDate']);
                $sl_product_obj->to_date = iso_to_mongo_date($dr['endDate']);
				if(isset($input['numOfSlots'])&& $input['numOfSlots']!=''){
					$sl_product_obj->booked_slots = isset($input['numOfSlots']) ? $input['numOfSlots'] : 1;
				}
				$diff=date_diff(date_create($dr['endDate']),date_create($dr['startDate']));
						$daysCount = $diff->format("%a");
						if(isset($input['numOfSlots'])&& $input['numOfSlots']!=''){
							//$price = round((($product->default_price*($daysCount+1))/7)*($input['numOfSlots']));
							//$price = round((($product->rateCard*($daysCount+1))/7)*($input['numOfSlots']));
							$price = round((($product->rateCard*($daysCount+1))/28)*($input['numOfSlots']));
						}else{
						//$price = round(($product->default_price*($daysCount+1))/28);
						$price = round(($product->rateCard*($daysCount+1))/28);
						}
						$sl_product_obj->price = $price;
                if (!$sl_product_obj->save()) {
                    $success = false;
                    break;
                }
            }
            if ($success) {
                return response()->json(["status" => "1", "message" => "Product shortlisted."]);
            } else {
                return response()->json(["status" => "0", "message" => "Product could not be shortlisted"]);
            }
        } else {
            return response()->json(["status" => "0", "message" => "The dates for selected product is already shortlisted.", "overlapping_dates" => $overlapping_dates]);
        }
    }*/
	
	public function shortListProduct(Request $request) {
        $this->validate($request, [
            'product_id' => 'required',
            'dates' => 'required'
                ], [
            'product_id.required' => 'Product id is required',
            'dates.required' => 'Please provide dates you\'re selecting the product for'
                ]
        );
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
		
		$productids = $input['product_id'];
		
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
		//echo "<pre>"; print_r($user_mongo);exit;
        $overlapping_dates = [];
        if ($user_mongo['user_type'] == "basic") {
			
            /*$product_occurances = ShortListedProduct::where([
                        ['user_mongo_id', '=', $user_mongo['id']],
                        //['product_id', '=', $input['product_id']]
                    ])->get();*/
					
			if(is_array($productids))
			{
				//$product_occurances = ShortListedProduct::whereIn('product_id', $productids)->get();
				$product_occurances = ShortListedProduct::where([
                    ['user_mongo_id', '=', $user_mongo['id']],
                ])
				->whereIn('product_id', $productids)->get();
				//echo "<pre>product_occurances"; print_r($product_occurances);exit; 
			}
			else
			{	
				$product_occurances = ShortListedProduct::where([
                        ['user_mongo_id', '=', $user_mongo['id']],
                        ['product_id', '=', $input['product_id']]
                    ])->get();
			}
			//echo "<pre>product_occurances"; print_r(count($product_occurances));exit; 		
            foreach ($product_occurances as $po) {
                foreach ($input['dates'] as $dr) {
                    if ($po->from_date <= $dr['endDate'] && $po->to_date >= $dr['startDate']) {
                        array_push($overlapping_dates, $dr);
                    }
                }
            }
        } else {
            /*$product_occurances = ShortListedProduct::where([
                        ['client_mongo_id', '=', $user_mongo['client_mongo_id']],
                        ['product_id', '=', $input['product_id']]
                    ])->get();*/
			  if(is_array($productids))
			  {				  
				//$product_occurances = ShortListedProduct::whereIn('product_id', $productids)->get();
				$product_occurances = ShortListedProduct::where([
                    ['user_mongo_id', '=', $user_mongo['id']],
                ])
				->whereIn('product_id', $productids)->get();
			  }
			  else
			  {
				  $product_occurances = ShortListedProduct::where([
                        ['client_mongo_id', '=', $user_mongo['client_mongo_id']],
                        ['product_id', '=', $input['product_id']]
                    ])->get();
			  }
            foreach ($product_occurances as $po) {
                foreach ($input['dates'] as $dr) {
                    if ($po->from_date >= $dr['endDate'] && $po->to_date <= $dr['startDate']) {
                        array_push($overlapping_dates, $dr);
                    }
                }
            }
        }
        if (count($overlapping_dates) == 0) {
            $success = true;

$rand = substr(str_shuffle(str_repeat("ABCDEFGHJKLMNPQRSTUVWXYZ", 3)), 0, 3);
        			$group_id ="AMP".date('Ymd').$rand;

            foreach ($input['dates'] as $dr) {
				//$product = Product::where('id', '=', $input['product_id'])->first()
				//$product = Product::whereIn('id', $productids)->toSql();

				

				if(is_array($productids))
				{
					$productids1 = $productids;

					foreach($productids1 as $productids1){
                        //echo "<pre>productids1"; print_r($productids1);exit;
					//$product = Product::whereIn('id', $productids)->get();
					$product = Product::where('id', '=', $productids1['id'])->first();
						//echo "<pre>product"; print_r($product);exit;
						//dd(DB::getQueryLog());
						//foreach($product as $product){
							$sl_product_obj = New ShortListedProduct;
							$sl_product_obj->id = uniqid();
							if ($user_mongo['user_type'] == "basic") {
								$sl_product_obj->user_mongo_id = $user_mongo['id'];
							} else {
								$sl_product_obj->client_mongo_id = $user_mongo['client_mongo_id'];
							}
							if($product->type !='Bulletin' && !isset($input['numOfSlots'])){
								 //return response()->json(["status" => "0", "message" => "Number of Slots is required."]);
								 $input['numOfSlots'] = 1;
							}
							//$sl_product_obj->product_id = isset($input['product_id']) ? $input['product_id'] : "";
							$sl_product_obj->product_id = $product->id;
							$sl_product_obj->format_type = Format::$FORMAT_TYPE['ooh'];
							$sl_product_obj->from_date = iso_to_mongo_date($dr['startDate']);
							$sl_product_obj->to_date = iso_to_mongo_date($dr['endDate']);
							if(isset($input['numOfSlots'])&& $input['numOfSlots']!=''){
								$sl_product_obj->booked_slots = isset($input['numOfSlots']) ? $input['numOfSlots'] : 1;
							}
							
							$diff=date_diff(date_create($dr['endDate']),date_create($dr['startDate']));
									$daysCount = $diff->format("%a");
									$perdayprice = $product->default_price/28;
									if(isset($input['numOfSlots'])&& $input['numOfSlots']!=''){
										
										//$price = round((($product->default_price*($daysCount+1))/7)*($input['numOfSlots']));
										//$price = round((($product->rateCard*($daysCount+1))/7)*($input['numOfSlots']));
										if(isset($product->fix) && $product->fix=="Fixed"){
											//$price = round(($product->rateCard)*($input['numOfSlots']));
											//$price = round(($input['newratecard'])*($input['numOfSlots']));
											//echo "<pre>input"; print_r($productids1);exit;
											//if(isset($input['newratecard'])){
											/*if(isset($productids1['newratecard'])){
												//$price = round(($input['newratecard'])*($input['numOfSlots']));
												$price = round(($productids1['newratecard'])*($input['numOfSlots']));
												//echo 'of===fix--<pre>newratecard'.$price;exit;
											}else{
                                                if(isset($input['newratecard'])){
                                                    $price = round(($input['newratecard'])*($input['numOfSlots']));
                                                }else{
                                                    $price = round(($product->rateCard)*($input['numOfSlots']));
												    //echo 'of===fix<pre>'.$price;exit;
                                                }
												
											}*/
											
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
                                            /*if(isset($input['newratecard'])){
                                                //$price = round((( ($input['newratecard']) *($daysCount+1))/28)*($input['numOfSlots']));
                                                $price = round(($input['newratecard'])*($input['numOfSlots']));
                                            }else{
                                                $price = round((($product->rateCard*($daysCount+1))/28)*($input['numOfSlots']));
                                                //echo 'of===variable<pre>'.$price;exit;
                                                //$price = round(((($input['newratecard'])*($daysCount+1))/28)*($input['numOfSlots']));
                                            }*/
											
											if(($daysCount+1) <= $product->minimumdays){
												$price = $perdayprice * $product->minimumdays;
											}else{
												$price = $perdayprice * ($daysCount+1);
											}
											
										}								
									}else{
										//echo 'elsedsdsdsds';exit; 
									//$price = round(($product->default_price*($daysCount+1))/28);
										if(isset($product->fix) && $product->fix=="Fixed"){
											//echo "fix";exit;
											//$price = round(($product->rateCard));
											//if(isset($input['newratecard'])){
											/*if(isset($productids1['newratecard'])){
												$price = round(($productids1['newratecard']));
											}else{
												$price = round(($product->rateCard));
											}*/
											
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
                                            /*if(isset($productids1['newratecard'])){
                                                //$price = round(( ($productids1['newratecard']) *($daysCount+1))/28);
                                                $price = round($productids1['newratecard']);
                                            }else{
                                                //echo 'variable';exit;
											$price = round(($product->rateCard*($daysCount+1))/28);
											//$price = round((($input['newratecard'])*($daysCount+1))/28);
                                            }*/
											
											if(($daysCount+1) <= $product->minimumdays){
												$price = $perdayprice * $product->minimumdays;
											}else{
												$price = $perdayprice * ($daysCount+1);
											}
											
										}
									}
									$sl_product_obj->price = $price;
$sl_product_obj->quantity = isset($input['quantity']) ? $input['quantity'] : "";
$sl_product_obj->group_slot_id = $group_id.$product->id;
                                    //echo "<pre>sl_product_obj"; print_r($sl_product_obj);exit;
							if (!$sl_product_obj->save()) {
								$success = false;
								break;
							}
						//}
				}
			}else{
				//echo 'else';exit;
				$product = Product::where('id', '=', $input['product_id'])->first();
				$sl_product_obj = New ShortListedProduct;
					$sl_product_obj->id = uniqid();
					if ($user_mongo['user_type'] == "basic") {
						$sl_product_obj->user_mongo_id = $user_mongo['id'];
					} else {
						$sl_product_obj->client_mongo_id = $user_mongo['client_mongo_id'];
					}
					if($product->type !='Bulletin' && !isset($input['numOfSlots'])){
						 //return response()->json(["status" => "0", "message" => "Number of Slots is required."]);
						 $input['numOfSlots'] = 1;
					}
					$sl_product_obj->product_id = isset($input['product_id']) ? $input['product_id'] : "";
					//$sl_product_obj->product_id = $product->id;
					$sl_product_obj->format_type = Format::$FORMAT_TYPE['ooh'];
					$sl_product_obj->from_date = iso_to_mongo_date($dr['startDate']);
					$sl_product_obj->to_date = iso_to_mongo_date($dr['endDate']);
					if(isset($input['numOfSlots'])&& $input['numOfSlots']!=''){
						$sl_product_obj->booked_slots = isset($input['numOfSlots']) ? $input['numOfSlots'] : 1;
					}
					$diff=date_diff(date_create($dr['endDate']),date_create($dr['startDate']));
							$daysCount = $diff->format("%a");
							$perdayprice = $product->default_price/28;
							if(isset($input['numOfSlots'])&& $input['numOfSlots']!=''){
								//$price = round((($product->default_price*($daysCount+1))/7)*($input['numOfSlots']));
								//$price = round((($product->rateCard*($daysCount+1))/7)*($input['numOfSlots']));
								if(isset($product->fix) && $product->fix=="Fixed"){
									//$price = round(($product->rateCard)*($input['numOfSlots']));
									/*if(isset($input['newratecard'])){
										$price = round(($input['newratecard'])*($input['numOfSlots']));
									}else{
										$price = round(($product->rateCard)*($input['numOfSlots']));
									}*/
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
                                    /*if(isset($input['newratecard'])){
                                        //$price = round((( ($input['newratecard']) *($daysCount+1))/28)*($input['numOfSlots']));
                                        $price = round( ($input['newratecard'])*($input['numOfSlots']));
                                    }else{
                                        $price = round((($product->rateCard*($daysCount+1))/28)*($input['numOfSlots']));
									    //$price = round(((($input['newratecard'])*($daysCount+1))/28)*($input['numOfSlots']));
                                    }	*/
									if(($daysCount+1) <= $product->minimumdays){
										$price = $perdayprice * $product->minimumdays;
									}else{
										$price = $perdayprice * ($daysCount+1);
									}
								}
							}else{  
							//$price = round(($product->default_price*($daysCount+1))/28);
								if(isset($product->fix) && $product->fix=="Fixed"){
									//$price = round(($product->rateCard));
									/*if(isset($input['newratecard'])){
										$price = round(($input['newratecard']));
									}else{
										$price = round(($product->rateCard));
									}*/
									
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
                                    /*if(isset($input['newratecard'])){
                                        //$price = round(( ($input['newratecard']) *($daysCount+1))/28);
                                        $price = round($input['newratecard']);
                                    }else{
                                        $price = round(($product->rateCard*($daysCount+1))/28);
									    //$price = round((($input['newratecard'])*($daysCount+1))/28);
                                    }*/
									if(($daysCount+1) <= $product->minimumdays){
										$price = $perdayprice * $product->minimumdays;
									}else{
										$price = $perdayprice * ($daysCount+1);
									}
									
								}
							}
							$sl_product_obj->price = $price;
$sl_product_obj->quantity = isset($input['quantity']) ? $input['quantity'] : "";
$sl_product_obj->group_slot_id = $group_id.$product->id;
                            //echo "<pre>sl_product_obj"; print_r($sl_product_obj);exit;
					if (!$sl_product_obj->save()) {
						$success = false;
						break;
					}
			}
            }
            if ($success) {
                //return response()->json(["status" => "1", "message" => "Product(s) shortlisted."]);
                return response()->json(["status" => "1", "message" => "Product(s) placed in Cart"]);
            } else {
                return response()->json(["status" => "0", "message" => "Product(s) could not be shortlisted"]);
            }
        } else {
            return response()->json(["status" => "0", "message" => "The dates for selected product(s) is already shortlisted.", "overlapping_dates" => $overlapping_dates]);
        }
    }
	
	//Owner Bulk Shortlist
	
		public function bulkShortListProductForOwner(Request $request) {
        $this->validate($request, [
            'product_id' => 'required',
            'dates' => 'required'
                ], [
            'product_id.required' => 'Product id is required',
            'dates.required' => 'Please provide dates you\'re selecting the product for'
                ]
        );
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
		
		$productids = $input['product_id'];
		
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $overlapping_dates = [];
        if ($user_mongo['user_type'] == "owner") {
			
            /*$product_occurances = ShortListedProduct::where([
                        ['user_mongo_id', '=', $user_mongo['id']],
                        //['product_id', '=', $input['product_id']]
                    ])->get();*/
					
			if(is_array($productids))
			{
				$product_occurances = ShortListedProduct::whereIn('product_id', $productids)->get();
			}
			else
			{	
				$product_occurances = ShortListedProduct::where([
                        ['user_mongo_id', '=', $user_mongo['id']],
                        ['product_id', '=', $input['product_id']]
                    ])->get();
			}
					
            foreach ($product_occurances as $po) {
                foreach ($input['dates'] as $dr) {
                    if ($po->from_date <= $dr['endDate'] && $po->to_date >= $dr['startDate']) {
                        array_push($overlapping_dates, $dr);
                    }
                }
            }
        } else {
            /*$product_occurances = ShortListedProduct::where([
                        ['client_mongo_id', '=', $user_mongo['client_mongo_id']],
                        ['product_id', '=', $input['product_id']]
                    ])->get();*/
			  if(is_array($productids))
			  {				  
				$product_occurances = ShortListedProduct::whereIn('product_id', $productids)->get();
			  }
			  else
			  {
				  $product_occurances = ShortListedProduct::where([
                        ['client_mongo_id', '=', $user_mongo['client_mongo_id']],
                        ['product_id', '=', $input['product_id']]
                    ])->get();
			  }
            foreach ($product_occurances as $po) {
                foreach ($input['dates'] as $dr) {
                    if ($po->from_date >= $dr['endDate'] && $po->to_date <= $dr['startDate']) {
                        array_push($overlapping_dates, $dr);
                    }
                }
            }
        }
        if (count($overlapping_dates) == 0) {
            $success = true;
            foreach ($input['dates'] as $dr) {
				//$product = Product::where('id', '=', $input['product_id'])->first()
				//$product = Product::whereIn('id', $productids)->toSql();
				if(is_array($productids))
				{
					$product = Product::whereIn('id', $productids)->get();
					//echo "<pre>product"; print_r($product);exit;
					//dd(DB::getQueryLog());
					foreach($product as $product){
					$sl_product_obj = New ShortListedProduct;
					$sl_product_obj->id = uniqid();
					if ($user_mongo['user_type'] == "owner") {
						$sl_product_obj->user_mongo_id = $user_mongo['id'];
					} else {
						$sl_product_obj->client_mongo_id = $user_mongo['client_mongo_id'];
					}
					if($product->type !='Bulletin' && !isset($input['numOfSlots'])){
						 //return response()->json(["status" => "0", "message" => "Number of Slots is required."]);
						 $input['numOfSlots'] = 1;
					}
					//$sl_product_obj->product_id = isset($input['product_id']) ? $input['product_id'] : "";
					$sl_product_obj->product_id = $product->id;
					$sl_product_obj->format_type = Format::$FORMAT_TYPE['ooh'];
					$sl_product_obj->from_date = iso_to_mongo_date($dr['startDate']);
					$sl_product_obj->to_date = iso_to_mongo_date($dr['endDate']);
					if(isset($input['numOfSlots'])&& $input['numOfSlots']!=''){
						$sl_product_obj->booked_slots = isset($input['numOfSlots']) ? $input['numOfSlots'] : 1;
					}
					$diff=date_diff(date_create($dr['endDate']),date_create($dr['startDate']));
							$daysCount = $diff->format("%a");
							if(isset($input['numOfSlots'])&& $input['numOfSlots']!=''){
								//$price = round((($product->default_price*($daysCount+1))/7)*($input['numOfSlots']));
								//$price = round((($product->rateCard*($daysCount+1))/7)*($input['numOfSlots']));
								$price = round((($product->rateCard*($daysCount+1))/28)*($input['numOfSlots']));
							}else{
							//$price = round(($product->default_price*($daysCount+1))/28);
							$price = round(($product->rateCard*($daysCount+1))/28);
							}
							$sl_product_obj->price = $price;
					if (!$sl_product_obj->save()) {
						$success = false;
						break;
					}
				}
			}else{
				$product = Product::where('id', '=', $input['product_id'])->first();
				$sl_product_obj = New ShortListedProduct;
					$sl_product_obj->id = uniqid();
					if ($user_mongo['user_type'] == "owner") {
						$sl_product_obj->user_mongo_id = $user_mongo['id'];
					} else {
						$sl_product_obj->client_mongo_id = $user_mongo['client_mongo_id'];
					}
					if($product->type !='Bulletin' && !isset($input['numOfSlots'])){
						 //return response()->json(["status" => "0", "message" => "Number of Slots is required."]);
						 $input['numOfSlots'] = 1;
					}
					$sl_product_obj->product_id = isset($input['product_id']) ? $input['product_id'] : "";
					//$sl_product_obj->product_id = $product->id;
					$sl_product_obj->format_type = Format::$FORMAT_TYPE['ooh'];
					$sl_product_obj->from_date = iso_to_mongo_date($dr['startDate']);
					$sl_product_obj->to_date = iso_to_mongo_date($dr['endDate']);
					if(isset($input['numOfSlots'])&& $input['numOfSlots']!=''){
						$sl_product_obj->booked_slots = isset($input['numOfSlots']) ? $input['numOfSlots'] : 1;
					}
					$diff=date_diff(date_create($dr['endDate']),date_create($dr['startDate']));
							$daysCount = $diff->format("%a");
							if(isset($input['numOfSlots'])&& $input['numOfSlots']!=''){
								//$price = round((($product->default_price*($daysCount+1))/7)*($input['numOfSlots']));
								//$price = round((($product->rateCard*($daysCount+1))/7)*($input['numOfSlots']));
								$price = round((($product->rateCard*($daysCount+1))/28)*($input['numOfSlots']));
							}else{
							//$price = round(($product->default_price*($daysCount+1))/28);
							$price = round(($product->rateCard*($daysCount+1))/28);
							}
							$sl_product_obj->price = $price;
					if (!$sl_product_obj->save()) {
						$success = false;
						break;
					}
			}
            }
            if ($success) {
                return response()->json(["status" => "1", "message" => "Product(s) shortlisted."]);
            } else {
                return response()->json(["status" => "0", "message" => "Product(s) could not be shortlisted"]);
            }
        } else {
            return response()->json(["status" => "0", "message" => "The dates for selected product(s) is already shortlisted.", "overlapping_dates" => $overlapping_dates]);
        }
    }
	
	public function notifyUserShortlistedProduct(Request $request) {
        $this->validate($request, [
           // 'product_id' => 'required',
            //'dates' => 'required'
                ], [
            //'product_id.required' => 'Product id is required',
            //'dates.required' => 'Please provide dates you\'re selecting the product for'
                ]
        );
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
		
		$productids = $input['product_ids'];
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
		
		if(is_array($productids)){
			$product = ShortListedProduct::whereIn('product_id', $productids)->get();
			
			$success = true;
			 if(isset($product) && !empty($product)){
			 //foreach($product as $product){
			 for($i=0; $i<count($product) ; $i++){
				// echo $user_mongo_id = $product->user_mongo_id.'---';
				//$productDetails = Product::where('id', '=', $product[$i]->product_id)->first();
				$user_internal = UserMongo::where('id', '=', $product[$i]->user_mongo_id)->first();
				
				$shortlisted_products = Product::whereIn('id', $productids)->get();
            $formats = $shortlisted_products->unique('type')->count();
            $areas = $shortlisted_products->unique('area')->count();
            $audience_reach = $shortlisted_products->each(function($v, $k) {
                        $v->impressions = intval(str_replace(",", "", $v->impressions));
                    })->sum('impressions');
            $repeated_audience = $audience_reach * 30 / 100;
            $shortlisted_products_report = [
                'areas_covered' => $areas,
                'format_types' => $formats,
                'mediums_covered' => $shortlisted_products->count(),
                'audience_reach' => $audience_reach,
                'repeated_audience' => $repeated_audience,
                'products' => $shortlisted_products
            ];
			   
			/*event(new ShortlistedProductSoldOutEvent([
					  'type' => Notification::$NOTIFICATION_TYPE['shortlisted-product-soldout'],
                        'from_id' => null,
                        'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['user'],
                        'to_id' => null,
                        'to_client' => null,
                        'desc' => "Product Sold Out",
                        'message' => "The product you shortlisted has been sold out",
                        'data' => ["product_id" => $user_mongo_id->id]
					]));
					$notification_obj = new Notification;
					$notification_obj->id = uniqid();
                    $notification_obj->type = "shortlisted-product-soldout";
                    $notification_obj->from_id = null;
                    $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['user'];
                    $notification_obj->to_id = null;
                    $notification_obj->to_client = null;
                    $notification_obj->desc = "Product Sold Out";
                    $notification_obj->message = "The product you shortlisted has been sold out";
                    $notification_obj->product_id = $user_mongo_id->id;
					$notification_obj->status = 0;
                    $notification_obj->save();*/
			 
            $pdf = PDF::loadView('pdf.product_list_pdf', $shortlisted_products_report);
				
				$mail_tmpl_params = [
					'sender_email' => config('app.bbi_email'), //, 
					'receiver_name' => "",
					'mail_message' => 'The product you shortlisted has been sold out'
            ];
			
            $mail_data = [
                'email_to' => $user_internal->email,
                'recipient_name' => $user_internal->first_name.' '.$user_internal->last_name,
				'pdf_file_name' => "Shortlisted_" . $user_internal->id . "_" . date('d-m-Y') . ".pdf",
                'pdf' => $pdf
            ];
			
            Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data) {
                $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Shortlisted Product Sold Out');
				$message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
            });
            if (!Mail::failures()) {
				$success = true;
            } else {
				$success = false;
            }
				}
			 }
			 if ($success) {
            return response()->json(['status' => 1, 'message' => "Product sold notification sent successfully."]);
        } else {
            return response()->json(['status' => 0, 'message' => "There was an error sending the email. Please try again."]);
        }
			
		}else{
			
		}
		
		
        // if ($success) {
            // return response()->json(['status' => '1', 'message' => 'Product Notification sent.']);
        // } else {
            // return response()->json(['status' => '0', 'message' => 'Error in sending Notification.']);
        // }
    }
	
    public function deleteShortlistedProduct($shortlist_id) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        if ($user_mongo['user_type'] == 'basic') {
            $success = ShortListedProduct::where([
                        ['user_mongo_id', '=', $user_mongo['id']],
                        ['id', '=', $shortlist_id]
                    ])->delete();
        } else {
            $success = ShortListedProduct::where([
                        ['client_mongo_id', '=', $user_mongo['client_mongo_id']],
                        ['id', '=', $shortlist_id]
                    ])->delete();
        }
        if ($success) {
            return response()->json(['status' => '1', 'message' => 'Product entry removed from shortlist.']);
        } else {
            return response()->json(['status' => '0', 'message' => 'Error removing product entry from shortlist.']);
        }
    } 
	
	public function deleteShortlistedProducts(Request $request) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
		
		if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
		$productids = $input['product_id'];
			foreach($productids as $productids){
        if ($user_mongo['user_type'] == 'basic') {
            $success = ShortListedProduct::where([
                        ['user_mongo_id', '=', $user_mongo['id']],
                        //['id', '=', $shortlist_id]
                        ['id', '=', $productids]
                    ])->delete();
        } else {
            $success = ShortListedProduct::where([
                        ['client_mongo_id', '=', $user_mongo['client_mongo_id']],
                        //['id', '=', $shortlist_id]
                        ['id', '=', $productids]
                    ])->delete();
        }
		}
        if ($success) {
           // return response()->json(['status' => '1', 'message' => 'Product entry removed from shortlist.']);
            return response()->json(['status' => '1', 'message' => 'Product(s) removed from Cart']);
        } else {
            return response()->json(['status' => '0', 'message' => 'Error removing product entry from shortlist.']);
        }
    } 

// Shortlisting products ends
// Filtering products
    public function filterProducts(Request $request) {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }

        $curdate1 = date_create(date("Y-m-d"));
        $curdate11 = iso_to_mongo_date(date("Y-m-d"));

        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $filters_array = [];

        $shortlisted_products_id_arr = [];
        $getshortlisteditems = $this->getShortlistedProducts();
        if(isset($getshortlisteditems) && !empty($getshortlisteditems)){
            $obj = json_decode(json_encode($getshortlisteditems), true);
            $res = $obj['original']['shortlisted_products'];
            foreach($res as $res){
                $shortlisted_products_id_arr[] = $res['product_id'];
            }
        } else {
            $shortlisted_products_id_arr = [];
        }

        //array_push($filters_array, ["product_visibility" => ['$ne' => "0"]]);
        array_push($filters_array, ["product_visibility" => ['$ne' => "0"]]);
        array_push($filters_array, ["id" => ['$nin' => $shortlisted_products_id_arr]]);
        //array_push($filters_array, ["to_date" => ['$gte' => $curdate1]]); 
        if ($user_mongo['user_type'] == 'owner') {
            $client_mongo_id = $user_mongo['client_mongo_id'];
            array_push($filters_array, ["client_mongo_id" => $user_mongo['client_mongo_id']]);
        }/*else{
            $client_mongo_id = '';
        }*/

       /* if (isset($input['area']) && !empty($input['area'])) {
            $area_filter = $input['area'];
            array_push($filters_array, ["area" => ['$in' => $area_filter]]);
        }/*else{
            $area_filter = '';
        }*/
        if (isset($input['product_type']) && !empty($input['product_type'])) {
            $type_filter = $input['product_type'];
            array_push($filters_array, ["type" => ['$in' => $type_filter]]);
        }/*else{
            $type_filter = '';
        }*/
 
        //array_push($filters_array, ["to_date" => ['$gte' => $curdate1]]);

        if (isset($input['booked_from']) && isset($input['booked_to'])) {
            if (isset($input['booked_from']) && !empty($input['booked_from'])) {
                $from = $input['booked_from'];
            }
            if (isset($input['booked_to']) && !empty($input['booked_to'])) {
                $to = $input['booked_to'];
            }
            /*$product_List = ProductBooking::where("booked_from", '<=', new \DateTime($to))
                    ->where("booked_to", '>=', new \DateTime($from))
                    ->get();*/
            $fromdate = iso_to_mongo_date($from);
            $todate = iso_to_mongo_date($to);
            
            $start = iso_to_mongo_date($from);
            $startdate = date_create($from);
            $enddate = date_create($to);
            

            //array_push($filters_array, ["from_date" => ['$gte' => $startdate]]);
            //array_push($filters_array, ["to_date" => ['$gte' => $startdate]]);
            //array_push($filters_array, ["to_date" => ['$lte' => $enddate]]);
            //echo '<pre>';print_r(array_push($filters_array, ["to_date" => ['$lte' => $enddate]]));exit;
            /*$product_List = ProductBooking::where("booked_from", '>=', new \DateTime($from))
                    ->where("booked_to", '<=', new \DateTime($to))
                    ->get();*/
                     
            /*$product_List = ProductBooking::where('booked_from', '<=', $enddate)
                    ->where('booked_to', '>=', $startdate)
                    ->get();*/
            $product_List1 = ProductBooking::where('booked_from', '<=', $enddate)
            ->where('booked_to', '>=', $startdate)
            ->where('booked_to', '>=', $curdate1)
            ->get(); 
            $product_List2 = Product::where('to_date', '<=', $enddate)
            ->where('from_date', '>=', $startdate)
            ->where('to_date', '>=', $curdate1)
            ->get();  
            $product_List = Product::where('from_date', '>=', $startdate)
            ->where('to_date', '<=', $enddate)
            ->get(); 


                //echo '<pre>';print_r($product_List);exit;     
            // $product_List = ProductBooking::where("booked_from", '>=', ($fromdate))
                    // ->where("booked_to", '<=', ($todate))
                    // ->get();
            // $product_List = ProductBooking::where("booked_from", '<=', ($todate))
                        // ->where("booked_to", '>=', ($fromdate))
                        // ->get();
            //echo "<pre>product_List"; print_r($product_List); exit;       
            $prod_filter = [];
            foreach ($product_List as $val) {
                $prod_filter[] = $val->id;
            }
            array_push($filters_array, ["id" => ['$in' => $prod_filter]]);
        } else {
            array_push($filters_array, ["to_date" => ['$gte' => $curdate11]]); 
        }
        //return response()->json($filters_array);
        //if(!empty($filters_array)){ 
        if(!empty($type_filter)){
        //if(isset($product_List) && !empty($type_filter) && !empty($prod_filter)){
        //if(!empty($type_filter) && !empty($prod_filter)){  //product type
        //if(!empty($product_List)){
        //if(!empty($type_filter) || !empty($prod_filter)){ 
        //if(!empty($prod_filter)){ //dates
        //if(!empty($filters_array)){  //product type
        //echo "<pre>"; print_r($type_filter); exit;
       /* $grouped_products = Product::raw(function($collection) use ($filters_array) {
                    return $collection->aggregate(
                                    [
                                        ['$match' => [
                                                '$and' => $filters_array
                                            ]
                                        ],
                                        [
                                            '$group' => [
                                                '_id' => ['lat' => '$lat', 'lng' => '$lng'],
                                                'product_details' => [
                                                    '$push' => [
                                                        'id' => '$id',
                                                        'siteNo' => '$siteNo',
                                                        'adStrength' => '$adStrength',
                                                        'address' => '$address',
                                                        'impressions' => '$impressions',
                                                        'company' => '$company',
                                                        'direction' => '$direction',
                                                        'default_price' => '$default_price',
                                                        'image' => '$image',
                                                        'lighting' => '$lighting',
                                                        'symbol' => '$symbol',
                                                        'panelSize' => '$panelSize',
                                                        'type' => '$type',
                                                        'format_name' => '$format_name',
                                                        'country_name' => '$country_name',
                                                        'country' => '$country',
                                                        'state_name' => '$state_name',
                                                        'state' => '$state',
                                                        'city_name' => '$city_name',
                                                        'city' => '$city',
                                                        'area_name' => '$area_name',
                                                        'addresstwo'=>'$addresstwo',
                                                        'venue'=>'$venue',
                                                        'ethnicity'=>'$ethnicity',
                                                        'strengths'=>'$strengths',
                                                        'client_name' => '$client_name',
                                                        'hoardingCost' => '$hoardingCost',
                                                        'price' => '$default_price',
                                                        'product_visibility' => '$product_visibility',
                                                        'demographicsage'=>'$demographicsage',
                                                        'imgdirection'=>'$imgdirection',
                                                        'slots'=>'$slots',
                                                        'cancellation'=>'$cancellation',                'minimumbooking'=>'$minimumbooking'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                    );
                });*/
                
                $grouped_products = Product::raw(function($collection) use ($filters_array) {
                    return $collection->aggregate(
                                    [
                                        ['$match' => [
                                                '$and' => $filters_array
                                            ]
                                        ], 
                                        [
                                            '$group' => [
                                                '_id' => ['lat' => '$lat', 'lng' => '$lng', 'id' => '$id'],
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
                                                            'stripe_percent'=>'$stripe_percent'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                    );
                });
                //echo '<pre>';print_r($grouped_products);exit;    
                // $grouped_products['product_details'] = Product::where([
                    // ['product_visibility', '=', 1],
                // ])
                // ->whereIn('type', $type_filter)
                // ->whereIn('id', $prod_filter)->get();
                
                // $res = [];
                // foreach($grouped_products['product_details']  as $grouped_products){
                    // $res[]['product_details'] = $grouped_products;
                // }
                
        // return response()->json($res); 
        return response()->json($grouped_products);
        }
        else {
            return response()->json(["status" => "0", "message" => "No products available in the selected criteria"]);
        }
    }

    public function searchProductBySiteNo($site_no) {
        $word = strtolower($site_no);

        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];

        if ($user_mongo['user_type'] == 'owner') {
            $product = Product::where('siteNo', 'like', "%$site_no%")->where('client_mongo_id', '=', $user_mongo['client_mongo_id'])->where('product_visibility', '!=', '0')->get();
        } else {
            $product = Product::where('siteNo', 'like', "%$site_no%")->where('product_visibility', '!=', '0')->get();
        }

        return response()->json($product);
    }
	public function searchProductByCpm($cpm) {
        $word = strtolower($cpm);

        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];

        if ($user_mongo['user_type'] == 'owner') {
            $product = Product::where('cpm', 'like', "%$cpm%")->where('client_mongo_id', '=', $user_mongo['client_mongo_id'])->where('product_visibility', '!=', '0')->get();
        } else {
            $product = Product::where('cpm', 'like', "%$cpm%")->where('product_visibility', '!=', '0')->get();
        }

        return response()->json($product);
    }
	public function searchProductBySecondImpression($secondImpression) {
        $word = strtolower($secondImpression); 

        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];

        if ($user_mongo['user_type'] == 'owner') {
            $product = Product::where('secondImpression', 'like', "%$secondImpression%")->where('client_mongo_id', '=', $user_mongo['client_mongo_id'])->where('product_visibility', '!=', '0')->get();
        } else {
            $product = Product::where('secondImpression', 'like', "%$secondImpression%")->where('product_visibility', '!=', '0')->get();
        }

        return response()->json($product);
    }

// Filtering products
// searching products 
    public function searchProductsByQuery($word) {
        $word = strtolower($word);
        $products = Product::where('siteNo', 'like', "%$word%")
                ->orWhere('format_name', 'like', "%$word%")
                ->orWhere('company_name', 'like', "%$word%")
                ->orWhere('address', 'like', "%$word%")
                ->orWhere('area_name', 'like', "%$word%")
                ->orWhere('panelSize', 'like', "%$word%")
                ->orWhere('direction', 'like', "%$word%")
                ->get();
        return response()->json($products);
    }

    public function searchOwnerProductsByQuery($word) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $word = strtolower($word);
        $products = Product::where('client_mongo_id', '=', $user_mongo['client_mongo_id'])
                ->where(function($q) use ($word) {
                    $q->where('siteNo', 'like', "%$word%")
                    ->orWhere('format_name', 'like', "%$word%")
                    ->orWhere('company_name', 'like', "%$word%")
                    ->orWhere('address', 'like', "%$word%")
                    ->orWhere('area_name', 'like', "%$word%")
                    ->orWhere('panelSize', 'like', "%$word%")
                    ->orWhere('direction', 'like', "%$word%");
                })
                ->get();
        return response()->json($products);
    }
	   
	public function searchByProductsDetails(Request $request) {
	if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
		$searchparam = $this->input['searchparam'];
        $word = strtolower($searchparam);
	$products = Product::where('siteNo', 'like', "%$word%")
			->orWhere('zipcode', 'like', "%$word%")
			->orWhere('title', 'like', "%$word%")
			->orWhere('address', 'like', "%$word%")
			->orWhere('area_name', 'like', "%$word%")
			->orWhere('city', 'like', "%$word%")
			->orWhere('state_name', 'like', "%$word%")
			->orWhere('default_price', 'like', "%$word%")
			->orWhere('type', 'like', "%$word%")
			->get();
	return response()->json($products);
    }
	
	public function searchByUserDetails(Request $request) {
		if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
		$searchparam = $this->input['searchparam'];
        $word = strtolower($searchparam);
        $users = UserMongo::where('first_name', 'like', "%$word%")
                ->orWhere('last_name', 'like', "%$word%")
                ->orWhere('email', 'like', "%$word%")
                ->orWhere('company_name', 'like', "%$word%")
                ->get();
        return response()->json($users);
    }
	  
	public function searchByCampaignDetails(Request $request) {
		if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        $searchparam = $this->input['searchparam'];
        $word = strtolower($searchparam);
        $campaigns = Campaign::where('cid', 'like', "%$word%")
                ->orWhere('name', 'like', "%$word%")
                ->get();
        return response()->json($campaigns);
    }

// searching products ends
// sharing list of shortlited products
    public function shareShortlistedProducts(Request $request) {
        $this->validate($request, [
            'email' => 'required',
            'receiver_name' => 'required'
                ], [
            'email.required' => 'Email is required',
            'receiver_name.required' => 'Receiver name is required'
                ]
        );

        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        try {
            $user = JWTAuth::parseToken()->getPayload()['user'];
            $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
            if ($user_mongo['user_type'] == 'basic') {
                $shortlisted_product_ids = ShortListedProduct::where('user_mongo_id', '=', $user_mongo['id'])->pluck('product_id')->toArray();
            } else {
                $shortlisted_product_ids = ShortListedProduct::where('client_mongo_id', '=', $user_mongo['client_mongo_id'])->pluck('product_id')->toArray();
            }
            if (count($shortlisted_product_ids) == 0) {
                return response()->json(['status' => 0, 'message' => 'You have not shortlisted products.']);
            }
            $shortlisted_products = Product::whereIn('id', $shortlisted_product_ids)->get();
            $formats = $shortlisted_products->unique('type')->count();
            $areas = $shortlisted_products->unique('area')->count();
            $audience_reach = $shortlisted_products->each(function($v, $k) {
                        $v->impressions = intval(str_replace(",", "", $v->impressions));
                    })->sum('impressions');
            $repeated_audience = $audience_reach * 30 / 100;
            $shortlisted_products_report = [
                'areas_covered' => $areas,
                'format_types' => $formats,
                'mediums_covered' => $shortlisted_products->count(),
                'audience_reach' => $audience_reach,
                'repeated_audience' => $repeated_audience,
                'products' => $shortlisted_products
            ];
            $pdf = PDF::loadView('pdf.product_list_pdf', $shortlisted_products_report);
// $pdf->save('uploads/campaign' . uniqid() . '.pdf');
// return response()->json([]);	
            $mail_tmpl_params = [
                'sender_email' => $user['email'],
                'receiver_name' => $input['receiver_name']
            ];
            $mail_data = [
                'email_to' => $input['email'],
                'recipient_name' => $input['receiver_name'],
                'pdf_file_name' => "Shortlisted_" . $user_mongo['id'] . "_" . date('d-m-Y') . ".pdf",
                'pdf' => $pdf
            ];
            Mail::send('mail.product_list', $mail_tmpl_params, function($message) use ($mail_data) {
                $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Product list');
                $message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
            });
            if (!Mail::failures()) {
                return response()->json(['status' => 1, 'message' => "Your shortlisted products have been shared successfully."]);
            } else {
                return response()->json(['status' => 0, 'message' => "There was an error sending the email. Please try again."]);
            }
        } catch (Exception $ex) {
            return response()->json(['status' => 0, 'message' => "There was an error generating the shortlisted product report."]);
        }
    }

// sharing list of shortlited products ends

    /*
      ======= Product section ends ======
     */


    /*
      ======= Formats section =======
     */
    public function getFormats(Request $request) {
        $type = $request->input('type');
        if (isset($type) && !empty($type)) {
            if ($type == "ooh") {
                /*$format = Format::where('type', 'exists', false)
                                ->orWhere('type', '=', Format::$FORMAT_TYPE[$type])->get();*/
								
				/*$format[0]['id']='Bulletin';	 
				$format[0]['name']='Bulletin';		
				$format[0]['image']='/uploads/images/formats/bulletin.png';	
				$format[1]['id']='Digital';	
				$format[1]['name']='Digital';		
				$format[1]['image']='/uploads/images/formats/digital.png';	
				$format[2]['id']='Transit Digital';	
				$format[2]['name']='Transit Digital';		
				$format[2]['image']='/uploads/images/formats/transit.png';*/  
				
				$format[0]['id']='Digital';	
				$format[0]['name']='Digital';		
				$format[0]['image']='/uploads/images/formats/Digital_New.png';	
				$format[1]['id']='Digital/Static';	
				$format[1]['name']='Digital/Static';		
				$format[1]['image']='/uploads/images/formats/DigitalStatic_New.png';	
				$format[2]['id']='Static';	
				$format[2]['name']='Static';		
				$format[2]['image']='/uploads/images/formats/Static_New.png';	
				//$format[3]['id']='New Age';	
				//$format[3]['id']='New Media';	
				$format[3]['id']='Media';	
				//$format[3]['name']='New Age';		
				//$format[3]['name']='New Media';		
				$format[3]['name']='Media';		
				$format[3]['image']='/uploads/images/formats/NewAge_New.png';	

				
            } else {
                $format = Format::where('type', '=', Format::$FORMAT_TYPE[$type])->get();
            }
        } else {
            $format = Format::all();
        }
        return response()->json($format);
    }

    public function saveFormat(Request $request) {
        $format_img_path = base_path() . '/html/uploads/images/formats';
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        $input = $input['format'];
        if (!isset($input['id'])) {
            $this->validate($request, [
                'format.name' => 'required',
                'format.type' => 'required',
                'image' => 'required|image'
                    ], [
                'format.name.required' => 'Format name is required.',
                'format.type.required' => 'Format type is required.',
                'image.required' => 'Image is required',
                'image.image' => 'Please check image type. only jpeg and png is supported.'
                    ]
            );
            $format_obj = new Format;
            $format_obj->id = uniqid();
            $format_obj->name = isset($input['name']) ? $input['name'] : "";
            $format_obj->type = isset($input['type']) ? Format::$FORMAT_TYPE[$input['type']] : "";
            $format_obj->image = "";
            if ($request->hasFile('image')) {
                if ($request->file('image')->move($format_img_path, $request->file('image')->getClientOriginalName())) {
                    $format_obj->image = "/uploads/images/formats/" . $request->file('image')->getClientOriginalName();
                }
            }
        } else {
            $format_obj = Format::where('id', '=', $input['id'])->first();
            $format_obj->name = isset($input['name']) ? $input['name'] : $format_obj->name;
            if ($request->hasFile('image')) {
                if ($request->file('image')->move($format_img_path, $request->file('image')->getClientOriginalName())) {
                    $format_obj->image = "/uploads/images/formats/" . $request->file('image')->getClientOriginalName();
                }
            }
        }
        if ($format_obj->save()) {
            return response()->json(["status" => "1", "message" => "Format saved successfully."]);
        } else {
            return response()->json(["status" => "0", "message" => "Failed to save Format."]);
        }
    }

    public function deleteFormat($format_id) {
        $products_with_given_format = Product::where('type', '=', $format_id)->get();
        if (count($products_with_given_format) > 0) {
            return response()->json(['status' => 0, 'message' => "This format has products associated with it. Please delete those products first."]);
        } else {
            $success = Format::where('id', '=', $format_id)->delete();
            if ($success) {
                return response()->json(['status' => 1, 'message' => 'The format was deleted successfully.']);
            } else {
                return response()->json(['status' => 0, 'message' => 'There was a trouble deleting the format. Please try again.']);
            }
        }
    }

    /*
      ======= Formats section =======
     */

    public function getApprovedOwnerProducts(Request $request) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        if (!isset($user_mongo) || empty($user_mongo)) {
            return response()->json(['status' => 0, 'message' => 'Invalid user. Please log in again and try.']);
        } else {
            $client_mongo = ClientMongo::where('id', '=', $user_mongo['client_mongo_id'])->first();
            if (!isset($client_mongo) || empty($client_mongo)) {
                return response()->json(['status' => 0, 'message' => 'You can not view the products of a company you\'re not a part of.']);
            } else {
                $page_no = $request->input('page_no');
                $page_size = $request->input('page_size');
                $start_date = $request->input('start_date');
                $end_date = $request->input('end_date');
                $format = $request->input('format');
				//dd($format['name']);
                $budget = $request->input('budget');
		$product_name = $request->input('product_name');

                if (isset($page_no, $page_size) && !empty($page_no) && !empty($page_size)) {
                    $offset = ($page_no - 1) * $page_size;
                    if (isset($start_date) && isset($end_date)) {
                        /*$unavailable_product_ids = ProductBooking::where([
                                    ['product_owner', '=', $user_mongo['client_mongo_id']],
                                    ['booked_from', '<=', $start_date],
                                    ['booked_to', '>=', $end_date]
                                ])->pluck('product_id');*/

			$explode_start_date = @explode('-',$start_date);
			$arranged_start_date = date('d-m-Y',strtotime($explode_start_date[1].'-'.$explode_start_date[0].'-'.$explode_start_date[2]));
			$explode_end_date = @explode('-',$end_date);
			$arranged_end_date = date('d-m-Y',strtotime($explode_end_date[1].'-'.$explode_end_date[0].'-'.$explode_end_date[2]));

			$startdate = date_create($arranged_start_date);
            		$enddate = date_create($arranged_end_date);

            		$unavailable_product_ids = ProductBooking::where('product_owner', '=', $user_mongo['client_mongo_id'])->where('booked_from', '>=', $startdate)->where('booked_to', '<=', $enddate)->pluck('product_id');

                    }
                    if (isset($format) && !empty($format)) {
			if($format['name'] != 'All'){
                        	$product_array = [
                            		['client_mongo_id', '=', $client_mongo->id],
                            		['type', '=', $format['name']],
                            		['status', '=', Product::$PRODUCT_STATUS['approved']]
                        	];
			}else{
				$product_array = [
                            		['client_mongo_id', '=', $client_mongo->id],
                            		['status', '=', Product::$PRODUCT_STATUS['approved']]
                        	];
			}
                    } else {
                        $product_array = [
                            ['client_mongo_id', '=', $client_mongo->id],
                            ['status', '=', Product::$PRODUCT_STATUS['approved']]
                        ];
                    }

                    /*if (isset($budget) && !empty($budget)) {
			//echo "1223";
                        if ($budget == 1) {
                            if (isset($start_date) && isset($end_date))
                                $products = Product::where($product_array)->whereNotIn('id', $unavailable_product_ids)->orderBy('default_price', 'asc')->skip($offset)->take((int) $page_size)->get();
                            else
                                $products = Product::where($product_array)->orderBy('updated_at', 'desc')->skip($offset)->take((int) $page_size)->get();
                        } else if ($budget == 0) {
                            if (isset($start_date) && isset($end_date))
                                $products = Product::where($product_array)->whereNotIn('id', $unavailable_product_ids)->orderBy('default_price', 'desc')->skip($offset)->take((int) $page_size)->get();
                            else
                                $products = Product::where($product_array)->orderBy('updated_at', 'desc')->skip($offset)->take((int) $page_size)->get();
                        }
                    } else {
                        if (isset($start_date) && isset($end_date))
                            $products = Product::where($product_array)->whereNotIn('id', $unavailable_product_ids)->skip($offset)->take((int) $page_size)->get();
                        else
                            $products = Product::where($product_array)->skip($offset)->take((int) $page_size)->get();
                    }

		$products = 0;
            if (isset($budget) && !empty($budget)) {
                if ($budget == 1) {
                    if (isset($start_date) && isset($end_date))
                        $products = Product::where($product_array)->whereIn('id', $unavailable_product_ids)->orderBy('default_price', 'asc')->skip($offset)->take((int) $page_size)->get();
                    else
                        $products = Product::where($product_array)->orderBy('default_price', 'asc')->skip($offset)->take((int) $page_size)->get();
                } else if ($budget == 0) {
                    if (isset($start_date) && isset($end_date))
                        $products = Product::where($product_array)->whereIn('id', $unavailable_product_ids)->orderBy('default_price', 'desc')->skip($offset)->take((int) $page_size)->get();
                    else
                        $products = Product::where($product_array)->orderBy('default_price', 'desc')->skip($offset)->take((int) $page_size)->get();
                }
            } else { 
                if (isset($start_date) && isset($end_date))
                    	$products = Product::where($product_array)->whereIn('id', $unavailable_product_ids)->orderBy('created_at', 'desc')->skip($offset)->take((int) $page_size)->get();
                else
                    	$products = Product::where($product_array)->orderBy('created_at', 'desc')->skip($offset)->take((int) $page_size)->get();
            }*/


		$product_name_like = '';
		if(isset($product_name) && !empty($product_name)){
			$product_name_like = $product_name;
		}

		$products = 0;
            if (isset($budget) && !empty($budget)) {
		if($product_name_like !=''){
                	if ($budget == 1) {
                    		if (isset($start_date) && isset($end_date))
                        		$products = Product::where($product_array)->where('title', 'LIKE',"%{$product_name_like}%")->whereIn('id', $unavailable_product_ids)->orderBy('default_price', 'asc')->skip($offset)->take((int) $page_size)->get();
                    		else
                        		$products = Product::where($product_array)->where('title', 'LIKE',"%{$product_name_like}%")->orderBy('default_price', 'asc')->skip($offset)->take((int) $page_size)->get();
                	} else if ($budget == 0) {
                    		if (isset($start_date) && isset($end_date))
                        		$products = Product::where($product_array)->where('title', 'LIKE',"%{$product_name_like}%")->whereIn('id', $unavailable_product_ids)->orderBy('default_price', 'desc')->skip($offset)->take((int) $page_size)->get();
                    		else
                        		$products = Product::where($product_array)->where('title', 'LIKE',"%{$product_name_like}%")->orderBy('default_price', 'desc')->skip($offset)->take((int) $page_size)->get();
                	}
		}else{
			if ($budget == 1) {
                    		if (isset($start_date) && isset($end_date))
                        		$products = Product::where($product_array)->whereIn('id', $unavailable_product_ids)->orderBy('default_price', 'asc')->skip($offset)->take((int) $page_size)->get();
                    		else
                        		$products = Product::where($product_array)->orderBy('default_price', 'asc')->skip($offset)->take((int) $page_size)->get();
                	} else if ($budget == 0) {
                    		if (isset($start_date) && isset($end_date))
                        		$products = Product::where($product_array)->whereIn('id', $unavailable_product_ids)->orderBy('default_price', 'desc')->skip($offset)->take((int) $page_size)->get();
                    		else
                        		$products = Product::where($product_array)->orderBy('default_price', 'desc')->skip($offset)->take((int) $page_size)->get();
                	}
		}
            } else { 
		if($product_name_like !=''){
                	if (isset($start_date) && isset($end_date))
                    		$products = Product::where($product_array)->where('title', 'LIKE',"%{$product_name_like}%")->whereIn('id', $unavailable_product_ids)->orderBy('created_at', 'desc')->skip($offset)->take((int) $page_size)->get();
                	else
                    		$products = Product::where($product_array)->where('title', 'LIKE',"%{$product_name_like}%")->orderBy('created_at', 'desc')->skip($offset)->take((int) $page_size)->get();
		}else{
			if (isset($start_date) && isset($end_date))
                    		$products = Product::where($product_array)->whereIn('id', $unavailable_product_ids)->orderBy('created_at', 'desc')->skip($offset)->take((int) $page_size)->get();
                	else
                    		$products = Product::where($product_array)->orderBy('created_at', 'desc')->skip($offset)->take((int) $page_size)->get();
		}
            }




		//$product_count = Product::all()->count();
		//$product_count = count($products);
		if($product_name_like !=''){
            		if (isset($start_date) && isset($end_date))
                		$product_count = Product::where($product_array)->where('title', 'LIKE',"%{$product_name_like}%")->count();
            		else
                		$product_count = Product::where($product_array)->where('title', 'LIKE',"%{$product_name_like}%")->count();
		}else{
			if (isset($start_date) && isset($end_date))
                		$product_count = Product::where($product_array)->count();
            		else
                		$product_count = Product::where($product_array)->count();
		}

                    foreach ($products as $product) {
                        $already_shortlisted = ShortlistedProduct::where([
                                    ['client_mongo_id', '=', $client_mongo->id],
                                    ['product_id', '=', $product->id]
                                ])->get();
                        if (count($already_shortlisted) > 0) {
                            $product->shortlisted = true;
                        }
                        $camapigns_count = ProductBooking::where('product_id', '=', $product->id)->pluck('campaign_id')->toArray();

                        if ($camapigns_count) {
                            $camapigns_count = count(array_filter($camapigns_count));
                            $product->camapigns_count = $camapigns_count;
                        }
                    }

                    $owner_products = [
                        "products" => $products,
                        "page_count" => ceil($product_count / $page_size)
                    ];
                } else {
                    $products = Product::where([
                                ['client_mongo_id', '=', $client_mongo->id],
                                ['status', '=', Product::$PRODUCT_STATUS['approved']]
                            ])->orderBy('updated_at', 'desc')->get();
                    foreach ($products as $product) {
                        $already_shortlisted = ShortlistedProduct::where([
                                    ['client_mongo_id', '=', $client_mongo->id],
                                    ['product_id', '=', $product->id]
                                ])->get();
                        if (count($already_shortlisted) > 0) {
                            $product->shortlisted = true;
                        }
                        $camapigns_count = ProductBooking::where('product_id', '=', $product->id)->pluck('campaign_id')->toArray();

                        if ($camapigns_count) {
                            $camapigns_count = count(array_filter($camapigns_count));
                            $product->camapigns_count = $camapigns_count;
                        }
                    }
                    $owner_products = [
                        "products" => $products,
                    ];
                }
                return response()->json($owner_products);
            }
        }
    }

    /*
     * Adding New Inventory From Owner
     */
    public function requestHoardingIntoInventory() {

	//dd($this->input);
        if (isset($this->input['editRequestedhordings']) && !empty($this->input['editRequestedhordings'])) {
            $this->input['product'] = $this->input['editRequestedhordings'];
        }
        if (!isset($this->input['product']) || empty($this->input['product'])) {
            return response()->json(['status' => 0, 'message' => 'Input received is in incorrect format.']);
        }
        $input = $this->input['product'];
        $product_img_path = base_path() . '/html/uploads/images/products';
        if (!isset($input['type']) || !isset($input['area']) || empty($input['type']) || empty($input['area'])) {
            return response()->json(['status' => 0, 'message' => 'Product type and area is required.']);
        }
        $format = Format::where('id', '=', $input['type'])->first();
        $area = Area::where('id', '=', $input['area'])->first();
        if (isset($input['client'])) {
            $client = ClientMongo::where('id', '=', $input['client'])->first();
        }
        if (!isset($this->input['editRequestedhordings'])) {
            $this->validate($this->request, [
                'product.area' => 'required',
                'image' => 'required',
                'product.lighting' => 'required',
                'product.type' => 'required',
                'product.siteNo' => 'required',
                'product.direction' => 'required',
              //  'product.address' => 'required',
                    //'product.adStrength' => 'required'
                    ], [
                'product.area.required' => 'Product area is required',
                'image.required' => 'Image is required',
                'product.lighting.required' => 'Lighting field is required',
                'product.type.required' => 'Product type is required',
                'product.siteNo.required' => 'Product site number is required',
                'product.direction.required' => "Direction is required",
              //  'product.address.required' => "Address is required",
                    //'product.adStrength.required' => "Advertising strength is required"
                    ]
            );
        }
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        if (!isset($user_mongo) || empty($user_mongo)) {
            return response()->json(['status' => 0, 'errors' => ['Invalid user. Please log in again and try.']]);
        } else {
            $client_mongo = ClientMongo::where('id', '=', $user_mongo['client_mongo_id'])->first();
            if (!isset($client_mongo) || empty($client_mongo)) {
                return response()->json(['status' => 0, 'errors' => ['You can not view the products of a company you\'re not a part of.']]);
            }
            if (isset($input['siteNo']) && !empty($input['siteNo'])) {
                $repeated_site_no = Product::where([
                            ['siteNo', '=', $input['siteNo']]
                        ])->count();
                if ($repeated_site_no > 0 && !isset($this->input['editRequestedhordings'])) {
                    return response()->json(['status' => 0, 'message' => 'The site number provided already exists.']);
                } else if ($repeated_site_no > 1 && isset($this->input['editRequestedhordings'])) {
                    return response()->json(['status' => 0, 'message' => 'The site number provided already exists.']);
                }
            }

            if (isset($input['id']) && !empty($input['id'])) {
                $product_obj = Product::where('id', '=', $input['id'])->first();
				$imageArray = isset($product_obj->image)?$product_obj->image:"";
				
            } else {
                // saving new product
                $product_obj = new Product;
                $product_obj->id = uniqid();
            }

            $product_obj->siteNo = isset($input['siteNo']) ? "BI-" . $input['siteNo'] : "";
            $product_obj->area = isset($input['area']) ? $input['area'] : "";
            $product_obj->client_mongo_id = $client_mongo->id;
            $product_obj->client_name = $client_mongo->company_name;
            $product_obj->default_price = isset($input['default_price']) ? $input['default_price'] : "";
            $product_obj->image = "";

            if ($this->request->hasFile('image')) {
                foreach ($this->request->file('image') as $key => $val) {
                    $imageNmae = \Carbon\Carbon::now()->timestamp . $val->getClientOriginalName();
                    if ($val->move($product_img_path, $imageNmae)) {
                        $imageArray[] = "/uploads/images/products/" . $imageNmae;
                    }
                }
                $product_obj->image = $imageArray;
            }
            $product_obj->image = $imageArray;
        }


        $product_obj->lat = isset($input['lat']) ? $input['lat'] : "";
        $product_obj->lng = isset($input['long']) ? $input['long'] : "";

        $product_obj->lighting = isset($input['lighting']) ? $input['lighting'] : "";
        $product_obj->panelSize = (isset($input['width']) && isset($input['height'])) ? $input['height'] . '*' . $input['width'] : "";
        $product_obj->nearlandmark = isset($input['nearlandmark']) ? $input['nearlandmark'] : "";
        $product_obj->videoUrl = isset($input['videoUrl']) ? $input['videoUrl'] : "";
        $product_obj->impressionsperweek = isset($input['impressionsperweek']) ? $input['impressionsperweek'] : "";
        $product_obj->visibility = isset($input['visibility']) ? $input['visibility'] : "";
        $product_obj->audience = isset($input['audience']) ? $input['audience'] : "";
        $product_obj->targetedaudience = isset($input['targetedaudience']) ? $input['targetedaudience'] : "";
		$product_obj->eyeimpression = isset($input['eyeimpression']) ? $input['eyeimpression'] : "";
		$product_obj->cancelation = isset($input['cancelation']) ? $input['cancelation'] : "";
		
        $product_obj->type = isset($input['type']) ? $input['type'] : "";
        $product_obj->format_name = isset($format) ? $format->name : "";
        $product_obj->country_name = isset($area) ? $area->country_name : "";
        $product_obj->country = isset($input['country']) ? $input['country'] : "";
        $product_obj->state_name = isset($area) ? $area->state_name : "";
        $product_obj->state = isset($input['state']) ? $input['state'] : "";
        $product_obj->city_name = isset($area) ? $area->city_name : "";
        $product_obj->city = isset($input['city']) ? $input['city'] : "";
        $product_obj->direction = isset($input['direction']) ? $input['direction'] : "";
        $product_obj->address = isset($input['address']) ? $input['address'] : "";
        $product_obj->adStrength = isset($input['adStrength']) ? $input['adStrength'] : "";
        $product_obj->area_name = isset($area) ? $area->name : "";
        $product_obj->status = Product::$PRODUCT_STATUS['requested'];

        if ($product_obj->save()) {
            if (isset($input['dates']) && !empty($input['dates'])) {
                $success = true;
                foreach ($input['dates'] as $date_range) {
                    $booking = new ProductBooking;
                    $booking->product_id = $product_obj->id;
                    $booking->booked_from = iso_to_mongo_date($date_range['startDate']);
                    $booking->booked_to = iso_to_mongo_date($date_range['endDate']);
                    $booking->product_owner = $product_obj->client_mongo_id;
                    $booking->product_status = ProductBooking::$PRODUCT_STATUS['scheduled'];
                    if (!$booking->save()) {
                        $success = false;
                    }
                }
                if ($success) {
                    $noti_array = [
                        'type' => Notification::$NOTIFICATION_TYPE['product-requested'],
                        'from_id' => $product_obj->client_mongo_id,
                        'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
                        'to_id' => null,
                        'to_client' => null,
                        'desc' => "New product requested by " . $client_mongo->name,
                        'message' => $client_mongo->name . " requested a product to be added to our inventory.",
                        'data' => ["product_id" => $product_obj->id]
                    ];
                    $mail_array = [
                        'mail_tmpl_params' => [
                            'sender_email' => config('app.bbi_email'),
                            'receiver_name' => "",
                            'mail_message' => $client_mongo->name . " requested a product to be added to our inventory"
                        ],
                        //'subject' => 'New product requested! - Billboards India'
                        'subject' => 'New product requested! - Advertising Marketplace'
                    ];
                    event(new ProductRequestedEvent($noti_array,$mail_array));
                    $notification_obj = new Notification;
					$notification_obj->id = uniqid();
                    $notification_obj->type = "product-request";
                    $notification_obj->from_id = $product_obj->client_mongo_id;
                    $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['bbi'];
                    $notification_obj->to_id = null;
                    $notification_obj->to_client = null;
                    $notification_obj->desc = "New product requested";
                    $notification_obj->message = $client_mongo->name . " requested a product to be added to our inventory.";
                    $notification_obj->product_id = $product_obj->id;
					$notification_obj->status = 0;
                    $notification_obj->save();
                    $bbi_sa_id = Client::where('company_name', '=', 'BBI')->first()->super_admin;
                    $bbi_sa = UserMongo::where('user_id', '=', $bbi_sa_id)->first();
                    $mail_tmpl_params = [
                        'receiver_name' => $bbi_sa->first_name,
                        'mail_body' => "<b>" . $client_mongo->company_name . "</b> has requested a new product to be added to our inventory.",
                        //'mail_message' => $client_mongo->company_name . ' requests you to add a product into Billboards inventory.'
                        'mail_message' => $client_mongo->company_name . ' requests you to add a product into Advertising Marketplace inventory.'
                    ];
                    $mail_data = [
                        'email_to' => $bbi_sa->email,
                        'recipient_name' => $bbi_sa->first_name
                    ];
                    Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data) {
                        $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('An Owner has requested a product to be added to our inventory.');
                    });
                    return response()->json(['status' => 1, 'message' => "Your product addition request is received along with all the booking dates."]);
                } else {
                    return response()->json(['status' => 0, 'message' => "Your product is saved but we had trouble saving the booking dates. Please contact adming so they can update the bookings."]);
                }
            }
        }
        // saving new product ends  
    }

     /*
     * Getting Requested Hoardings
     */
    public function getRequestedHoardings(Request $request) {
        $page_no = $request->input('page_no');
        $page_size = $request->input('page_size');
        if (isset($page_no, $page_size) && !empty($page_no) && !empty($page_size)) {
            $offset = ($page_no - 1) * $page_size;
            $products = Product::where('status', '=', Product::$PRODUCT_STATUS['requested'])
                            ->skip($offset)->take((int) $page_size)->orderBy('created_at', 'desc')->get();
            $product_list_data = [
                "products" => $products,
                "page_count" => ceil(Product::where('status', '=', Product::$PRODUCT_STATUS['requested'])->count() / $page_size)
            ];
        } else {
            $products = Product::where('status', '=', Product::$PRODUCT_STATUS['requested'])->orderBy('created_at', 'desc')->get();
            $product_list_data = [
                "products" => $products,
            ];
        }
        return response()->json($product_list_data);
    }

    public function getRequestedHoardingsForOwner(Request $request) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        if (!isset($user_mongo) || empty($user_mongo)) {
            return response()->json(['status' => 0, 'errors' => ['Invalid user. Please log in again and try.']]);
        } else {
            $client_mongo = ClientMongo::where('id', '=', $user_mongo['client_mongo_id'])->first();
            if (!isset($client_mongo) || empty($client_mongo)) {
                return response()->json(['status' => 0, 'errors' => ['You can not view the products of a company you\'re not a part of.']]);
            }
            $page_no = $request->input('page_no');
            $page_size = $request->input('page_size');
            if (isset($page_no, $page_size) && !empty($page_no) && !empty($page_size)) {
                $offset = ($page_no - 1) * $page_size;
                $products = Product::where([
                                    ['client_mongo_id', '=', $client_mongo->id],
                                    ['status', '=', Product::$PRODUCT_STATUS['requested']]
                                ])
                                ->skip($offset)->take((int) $page_size)->orderBy('created_at', 'desc')->get();
                $product_list_data = [
                    "products" => $products,
                    "page_count" => ceil(Product::where('status', '=', Product::$PRODUCT_STATUS['requested'])->where('client_mongo_id', '=', $client_mongo->id)->count() / $page_size)
                ];
            } else {
                $products = Product::where([
                            ['client_mongo_id', '=', $client_mongo->id],
                            ['status', '=', Product::$PRODUCT_STATUS['requested']]
                        ])->orderBy('created_at', 'desc')->get();
                $product_list_data = [
                    "products" => $products,
                ];
            }
            return response()->json($product_list_data);
        }
    }

    public function getOwnerProductDetails($product_id) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $product_obj = Product::where([
                    ['id', '=', $product_id],
                    ['client_mongo_id', '=', $user_mongo['client_mongo_id']]
                ])->first();
        if (!isset($product_obj) || empty($product_obj)) {
            return response()->json(['status' => 0, 'message' => 'Product not found. Please check the product id again.']);
        }
		//echo '<pre>';print_r($product_obj);exit; 	 	
		//echo '<pre>user_mongo';print_r($user_mongo);exit; 	 	
        $campaigns_with_this_product = [];
        $product_campaign_ids = ProductBooking::where('product_id', '=', $product_id)->pluck('campaign_id')->toArray();
        $product_campaigns = Campaign::whereIn('id', $product_campaign_ids)->get();
        foreach ($product_campaigns as $product_campaign) {
            if ($product_campaign->type == Campaign::$CAMPAIGN_USER_TYPE['user']) {
                $product_campaign->created_by = "";
            } else if ($product_campaign->type == Campaign::$CAMPAIGN_USER_TYPE['bbi']) {
                $product_campaign->org_name = "";
                $product_campaign->org_contact_name = "";
                $product_campaign->org_contact_email = "";
                $product_campaign->org_contact_phone = "";
            } else {
                $campaign_id = $product_campaign->id;
                $product_campaign->act_budget = ProductBooking::raw(function($collection) use ($campaign_id) {
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
                        })[0]->total_price;
            }
            array_push($campaigns_with_this_product, $product_campaign);
        }
		//echo "<pre>";print_r($product_obj);exit; 
        $product_obj->campaigns = $campaigns_with_this_product;
        return ($product_obj);
    }

    public function getProductUnavailableDates($product_id) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $date_ranges = ProductBooking::select('booked_from', 'booked_to')->where([
                    ['product_id', '=', $product_id],
                    ['product_status', '=', ProductBooking::$PRODUCT_STATUS['scheduled']]
                ])->get();
				$daterangeArray= array();
				foreach($date_ranges  as $key=>$val){
					$dateRange['booked_from'] =date('Y-m-d',strtotime('+5 hour +30 minutes',strtotime($val['booked_from'])));
					$dateRange['booked_to'] =date('Y-m-d',strtotime('+5 hour +30 minutes',strtotime($val['booked_to'])));
					//date('Y-m-d',strtotime($val['booked_to']));
					$daterangeArray[]= $dateRange;
					}
        return response()->json($daterangeArray);
    }

    public function getProductUnavailableDatesWithoutLogin($product_id) {
        //$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $date_ranges = ProductBooking::select('booked_from', 'booked_to')->where([
                    ['product_id', '=', $product_id],
                    ['product_status', '=', ProductBooking::$PRODUCT_STATUS['scheduled']]
                ])->get();
				$daterangeArray= array();
				foreach($date_ranges  as $key=>$val){
					$dateRange['booked_from'] =date('Y-m-d',strtotime('+5 hour +30 minutes',strtotime($val['booked_from'])));
					$dateRange['booked_to'] =date('Y-m-d',strtotime('+5 hour +30 minutes',strtotime($val['booked_to'])));
					//date('Y-m-d',strtotime($val['booked_to']));
					$daterangeArray[]= $dateRange;
					}
        return response()->json($daterangeArray);
    }

    public function getCampaignsFromProduct($product_id) {
        $product_detail = Product::where('id', '=', $product_id)->first()->toArray();
        $campaign_product_ids = ProductBooking::where('product_id', '=', $product_id)->pluck('campaign_id')->toArray();
        $campaign_product_count = array_filter($campaign_product_ids);
        $campaigns = Campaign::whereIn('id', $campaign_product_count)
                ->orderBy('updated_at', 'desc')
                ->get();
        $campaign_list = array();
        foreach ($campaigns as $val) {
            $campaign_product = ProductBooking::where('campaign_id', '=', $val->id)->get();
            $campaign_payments = CampaignPayment::where('campaign_id', '=', $val->id)->get();
            if (isset($campaign_payments) && count($campaign_payments) > 0) {
                $total_paid = $campaign_payments->sum('amount');
                $val['total_paid'] = $total_paid;
            } else {
                $val['total_paid'] = 0;
            }
            $val['no_products'] = count($campaign_product);
            $campaign_list[] = $val;
        }
        $send_details = [];
        $send_details['campaign_list'] = $campaign_list;
        $send_details['product_detail'] = $product_detail;
        $send_details['product_detail']['shorlist_count'] = count($campaign_product_count);
        return response()->json($send_details);
    }

    /* ===================================
      ///////// Metro section /////////////
      =================================== */

    public function saveMetroCorridor(Request $request) {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        if (!isset($input['id'])) {
            $this->validate($request, [
                'name' => 'required',
                'city_id' => 'required',
                'from' => 'required',
                'to' => 'required'
                    ], [
                'name.required' => 'Corridor name is required.',
                'city_id.required' => 'City is required.',
                'from.required' => 'from is required.',
                'to.required' => 'to is required.'
                    ]
            );
            $corridor_city = City::where('id', '=', $input['city_id'])->first();
            if (!isset($corridor_city) || empty($corridor_city)) {
                return response()->json(['status' => 0, 'message' => "City not found."]);
            }
            $metro_corr_obj = new MetroCorridor;
            $metro_corr_obj->id = uniqid();
            $metro_corr_obj->name = isset($input['name']) ? $input['name'] : "";
            $metro_corr_obj->from = isset($input['from']) ? $input['from'] : "";
            $metro_corr_obj->to = isset($input['to']) ? $input['to'] : "";
            $metro_corr_obj->city_id = $corridor_city->id;
            $metro_corr_obj->city_name = $corridor_city->name;
            $metro_corr_obj->activated = 1;
        } else {
            $metro_corr_obj = MetroCorridor::where('id', '=', $input['id'])->first();
            $metro_corr_obj->name = isset($input['name']) ? $input['name'] : $metro_corr_obj->name;
            $metro_corr_obj->from = isset($input['from']) ? $input['from'] : $metro_corr_obj->from;
            $metro_corr_obj->to = isset($input['to']) ? $input['to'] : $metro_corr_obj->to;
            if (isset($this->input['city_id'])) {
                $corridor_city = City::where('id', '=', $input['city_id'])->first();
                $metro_corr_obj->city_id = $corridor_city->id;
                $metro_corr_obj->city_name = $corridor_city->name;
            }
        }
        if ($metro_corr_obj->save()) {
            return response()->json(["status" => "1", "message" => "Corridor saved successfully."]);
        } else {
            return response()->json(["status" => "0", "message" => "Failed to save Format."]);
        }
    }

    public function getMetroCorridors() {
        $metro_corridors = MetroCorridor::where('activated', '=', 1)->get();
        return response()->json($metro_corridors);
    }

    public function saveMetroPackage(Request $request) {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        if (!isset($input['id'])) {
            $this->validate($request, [
                'corridor_id' => 'required',
                'format_id' => 'required',
                //	'months' => 'required',
                //'days' => 'required',
                'price' => 'required',
                'max_trains' => 'required',
                'description' => 'required'
                    ], [
                'corridor_id.required' => 'Corridor is required',
                'format_id.required' => 'Format is required',
                'price.required' => 'Price is required',
                'max_trains.required' => 'Maximum number of trains is required',
                'description.required' => 'Description is required'
                    ]
            );
            $corridor = MetroCorridor::where('id', '=', $input['corridor_id'])->first();
            if (!isset($corridor) || empty($corridor)) {
                return response()->json(['status' => 0, 'message' => "Corridor not found."]);
            }
            $format = Format::where([
                        ['id', '=', $input['format_id']],
                        ['type', '=', Format::$FORMAT_TYPE['metro']]
                    ])->first();
            if (!isset($format) || empty($format)) {
                return response()->json(['status' => 0, 'message' => "Format not found."]);
            }
            /* 	if(!(isset($input['months']) && !empty($input['months']) || 
              isset($input['days']) && !empty($input['days']))){
              return response()->json(['status' => 0, 'message' => "Package duration is required."]);
              } */
            $metro_package_obj = new MetroPackage;
            $metro_package_obj->id = uniqid();
            $metro_package_obj->name = isset($input['name']) ? $input['name'] : "";
            $metro_package_obj->corridor_id = $corridor->id;
            $metro_package_obj->corridor = $corridor->name;
            $metro_package_obj->format_id = $format->id;
            $metro_package_obj->format = $format->name;
            //	$metro_package_obj->months = isset($input['months']) ? (int) $input['months'] : 0 ;
            //	$metro_package_obj->days = isset($input['days']) ? (int) $input['days'] : 0 ;
            $metro_package_obj->price = isset($input['price']) ? (int) $input['price'] : 0;
            $metro_package_obj->max_trains = isset($input['max_trains']) ? (int) $input['max_trains'] : 0;
            $metro_package_obj->description = isset($input['description']) ? $input['description'] : '';
            $metro_package_obj->activated = 1;
        } else {
            $metro_package_obj = MetroPackage::where('id', '=', $input['id'])->first();
            $metro_package_obj->name = isset($input['name']) ? $input['name'] : $metro_package_obj->name;
            //	$metro_package_obj->months = isset($input['months']) ? (int) $input['months'] : $metro_package_obj->months ;
            //	$metro_package_obj->days = isset($input['days']) ? (int) $input['days'] : $metro_package_obj->days ;
            $metro_package_obj->price = isset($input['price']) ? (int) $input['price'] : $metro_package_obj->price;
            $metro_package_obj->max_trains = isset($input['max_trains']) ? (int) $input['max_trains'] : $metro_package_obj->max_trains;
            $metro_package_obj->description = isset($input['description']) ? $input['description'] : $metro_package_obj->description;
            if (isset($this->input['corridor_id'])) {
                $corridor = MetroCorridor::where('id', '=', $input['corridor_id'])->first();
                $metro_package_obj->corridor_id = $corridor->id;
                $metro_package_obj->corridor = $corridor->name;
            }
            if (isset($this->input['format_id'])) {
                $format = Format::where([
                            ['id', '=', $input['format_id']],
                            ['type', '=', Format::$FORMAT_TYPE['metro']]
                        ])->first();
                $metro_package_obj->format_id = $format->id;
                $metro_package_obj->format = $format->name;
            }
        }
        if ($metro_package_obj->save()) {
            return response()->json(["status" => "1", "message" => "Package saved successfully."]);
        } else {
            return response()->json(["status" => "0", "message" => "Failed to save Format."]);
        }
    }

    public function getMetroPackages(Request $request) {
        //$corridor_id = $request->input('corridor_id');
        if (isset($corridor_id) && !empty($corridor_id)) {
            $metro_packages = MetroPackage::where([
                        ['corridor_id', '=', $corridor_id],
                        ['activated', '=', 1]
                    ])->get();
        } else {
            $metro_packages = MetroPackage::where('activated', '=', 1)->get();
        }
        return response()->json($metro_packages);
    }

    public function shortlistMetroPackage() {
        $this->validate($this->request, [
            'id' => 'required',
            'start_date' => 'required'
                ], [
            'id.required' => 'At least one package is required',
            'start_date.required' => "Start Date is required"
                ]
        );
        if ($this->request->isJson()) {
            $input = $this->request->json()->all();
        } else {
            $input = $this->request->all();
        }
        if ((int) $input['selected_trains'] == 0) {
            return response()->json(['status' => 0, 'message' => "Please select at least 1 train."]);
        }
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
       
        $metro_package = MetroPackage::where('id', '=', $input['id'])->first();
        if (!isset($metro_package) || empty($metro_package)) {
            return response()->json(['status' => 0, 'message' => "The package referenced not found in database."]);
        }
        $already_shortlisted = ShortListedProduct::where([
                    ['user_mongo_id', '=', $user_mongo['id']],
                    ['package_id', '=', $input['id']]
                ])->count();
        if ($already_shortlisted == 0) {
            $sl_product_obj = New ShortListedProduct;
            $sl_product_obj->id = uniqid();
            $sl_product_obj->user_mongo_id = $user_mongo['id'];
            $sl_product_obj->format_type = Format::$FORMAT_TYPE['metro'];
            $sl_product_obj->package_id = isset($input['id']) ? $input['id'] : "";
            $sl_product_obj->package_name = $metro_package->name;
            $sl_product_obj->corridor_id = $metro_package->corridor_id;
            $sl_product_obj->corridor_name = $metro_package->corridor;
            $sl_product_obj->selected_trains = isset($input['selected_trains']) ? (int) $input['selected_trains'] : 0;
            //	$selected_slots = isset($input['max_slots']) ? (int) $input['max_slots'] : 0;
            $sl_product_obj->start_date = isset($input['start_date']) ? $input['start_date'] : "";
            $price = isset($input['price_new']) ? $input['price_new'] : "";
            $sl_product_obj->months = isset($input['months']) ? $input['months'] : "";
            //	$sl_product_obj->selected_slots = $selected_slots* $sl_product_obj->days;
            $sl_product_obj->price = $price;
            //$sl_product_obj->price =$sl_product_obj->days;
            if ($sl_product_obj->save()) {
                return response()->json(["status" => "1", "message" => "Package shortlisted."]);
            } else {
                return response()->json(["status" => "0", "message" => "Package could not be shortlisted"]);
            }
        } else {
            return response()->json(["status" => "0", "message" => "You have already shortlisted this package."]);
        }
    }

    public function getShortlistedMetroPackages() {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $sl_usr_packages = ShortListedProduct::where([
                    ['user_mongo_id', '=', $user_mongo['id']],
                    ['format_type', '=', Format::$FORMAT_TYPE['metro']]
                ])->get();
				
        foreach ($sl_usr_packages as $sup) {
		
            $m_package = MetroPackage::where('id', '=', $sup->package_id)->first();
				if(!empty($m_package)){
            $sup->name = $m_package->corridor . " - " . $m_package->name;
				$sup->format = $m_package->format;}
            //$sup->months = $m_package->months;
            //$sup->days = $m_package->days;
        }
        return response()->json($sl_usr_packages);
    }

    public function deleteShortlistedMetroPackage($package_id) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $success = ShortListedProduct::where([
                    ['user_mongo_id', '=', $user_mongo['id']],
                    ['package_id', '=', $package_id]
                ])->delete();
        if ($success) {
            return response()->json(['status' => '1', 'message' => 'Package removed from shortlist.']);
        } else {
            return response()->json(['status' => '0', 'message' => 'Error removing package from shortlist.']);
        }
    }

    public function deletMetroCorridors($corridor_id) {

        $success = MetroCorridor::where('id', '=', $corridor_id)->delete();
        if ($success) {
            return response()->json(['status' => 1, 'message' => 'The corridor was  deleted successfully.']);
        } else {
            return response()->json(['status' => 0, 'message' => 'There was a trouble deleting the corridor. Please try again.']);
        }
    }

    public function deletMetroPackage($package_id) {

        $success = MetroPackage::where('id', '=', $package_id)->delete();
        if ($success) {
            return response()->json(['status' => 1, 'message' => 'The Package was  deleted successfully.']);
        } else {
            return response()->json(['status' => 0, 'message' => 'There was a trouble deleting the Package. Please try again.']);
        }
    }

    /* ========================================
      ///////// Metro section Ends /////////////
      ======================================== */

    public function commentPost(Request $request) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];


        $comments_obj = New Comments;

        $comments_obj->id = uniqid();
        $comments_obj->sender_id = $user_mongo['id'];
        //  $comments_obj->receiver_id = $this->input['reciever_id'];
        $comments_obj->campaign_id = $this->input['id'];
        $comments_obj->message = $this->input['message'];
        // $comments_obj->status = $this->input['status'];
        if ($comments_obj->save()) {
            return response()->json(['status' => 1, 'message' => 'The Comment posted Sucessfully.']);
        } else {
            return response()->json(['status' => 0, 'message' => 'There was a trouble at posting Comment. Please try again.']);
        }
    }

    public function CommentsView(Request $request) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $userId = $user_mongo['id'];
        $campaignId = $this->input['id'];
        $comments = Comments::where('sender_id', '=', $userId)
                ->where('campaign_id', '=', $campaignId)
                ->get();
        $comments['send_user_id'] = $userId;
        return response()->json($comments);
    }

    public function filterProductsByDate(Request $request) {
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
        $filters_array = [];

        if (isset($input['booked_from']) && !empty($input['booked_from'])) {
            $from = $input['booked_from'];
        }
        if (isset($input['booked_to']) && !empty($input['booked_to'])) {
            $to = $input['booked_to'];
        }
        $product_List = ProductBooking::where("booked_from", '>=', [$from])
                ->where("booked_to", '<=', $to)
                ->get();
        foreach ($product_List as $val) {
            $area_filter = $val->product_id;
            array_push($filters_array, ["id" => ['$in' => $area_filter]]);
        }

        $grouped_products = Product::raw(function($collection) use ($filters_array) {
                    return $collection->aggregate(
                                    [
                                        ['$match' => [
                                                '$and' => $filters_array
                                            ]
                                        ],
                                        [
                                            '$group' => [
                                                '_id' => ['lat' => '$lat', 'lng' => '$lng'],
                                                'product_details' => [
                                                    '$push' => [
                                                        'id' => '$id',
                                                        'siteNo' => '$siteNo',
                                                        'adStrength' => '$adStrength',
                                                        'address' => '$address',
                                                        'impressions' => '$impressions',
                                                        'company' => '$company',
                                                        'direction' => '$direction',
                                                        'default_price' => '$default_price',
                                                        'image' => '$image',
                                                        'lighting' => '$lighting',
                                                        'symbol' => '$symbol',
                                                        'panelSize' => '$panelSize',
                                                        'type' => '$type',
                                                        'format_name' => '$format_name',
                                                        'country_name' => '$country_name',
                                                        'country' => '$country',
                                                        'state_name' => '$state_name',
                                                        'state' => '$state',
                                                        'city_name' => '$city_name',
                                                        'city' => '$city',
                                                        'area_name' => '$area_name'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                    );
                });
        return response()->json($grouped_products);
    }


//Adding Product Details.
public function saveProductDetails(Request $request){
	//dd('shiva');die();
	//echo '<pre>'; print_r($request); exit;
	   $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
	   //echo '<pre>'; print_r($user_mongo); exit;
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }

        //Pankaj temp purpose will remove
        // $get_data = Product::where('id', '=', '617066a76565f')->first();
        // $this->es_etl($get_data, "insert");
        // exit;
		
		//echo '<pre>input'; print_r($this->input); exit;
		//dd($this->input['editRequestedhordings']);
        //$input = $input['product'];
		 /*if (isset($this->input['editRequestedhordings']) && !empty($this->input['editRequestedhordings'])) {
            $input['product'] = $this->input['editRequestedhordings'];
        }*/
       if (isset($this->input['editRequestedhordings']) && !empty($this->input['editRequestedhordings'])) {
            $input['product'] = $this->input['editRequestedhordings'];
        }else{
			$input['product']=$this->input;
			//$input=$this->input;
		}
		//$input = $input['product'];
		//echo '<pre>sss'; print_r($input); exit;
		//dd($input);
        $product_img_path = base_path() . '/html/uploads/images/products';
        $product_symbol_path = base_path() . '/html/uploads/images/symbols';
      
        if (isset($input['client'])) {
            $client = ClientMongo::where('id', '=', $input['client'])->first();
        }else{
			$client = ClientMongo::where('id', '=', $user_mongo['client_mongo_id'])->first();
		}
	
	// handling editing of product
        if (isset($input['id'])) {
			//echo '<pre>in if'; print_r($input); exit;
            $product_obj = Product::where('id', '=', $input['id'])->first();
			$product_obj->siteNo = $product_obj->siteNo;
			$product_obj->lighting = isset($input['lighting']) ? $input['lighting'] : $product_obj->lighting;;
			 $product_obj->cancellation = isset($input['cancellation']) ? $input['cancellation'] : $product_obj->cancellation;
			 $product_obj->type = isset($input['type']) ? $input['type'] : $product_obj->type;
			 $product_obj->title = isset($input['title']) ? $input['title'] : $product_obj->title;
			$product_obj->minimumbooking = isset($input['minimumbooking']) ? $input['minimumbooking'] : $product_obj->minimumbooking;
			 $product_obj->panelSize = (isset($input['width']) && isset($input['height'])) ? $input['height'] . '*' . $input['width'] : $product_obj->panelSize;
			 $product_obj->venue = isset($input['venue']) ? $input['venue'] : $product_obj->venue;
			 $product_obj->address = isset($input['address']) ? $input['address'] : $product_obj->address;
			 $product_obj->city = isset($input['city']) ? $input['city'] : $product_obj->city;
			 $product_obj->zipcode = isset($input['zipcode']) ? $input['zipcode'] : $product_obj->zipcode;
			 $product_obj->lat = isset($input['lat']) ? $input['lat'] : $product_obj->lat;
             $product_obj->lng = isset($input['lng']) ? $input['lng'] : $product_obj->lng;
			 $product_obj->direction = isset($input['direction']) ? $input['direction'] : $product_obj->direction;
			 //$product_obj->default_price = isset($input['default_price']) ? $input['default_price'] :  $product_obj->default_price;
			 $product_obj->default_price = isset($input['rateCard']) ? str_replace( ',', '', $input['rateCard']) : str_replace( ',', '', $product_obj->rateCard);
			 $product_obj->impressions = isset($input['impressions']) ? $input['impressions'] : $product_obj->impressions;
			 $product_obj->demographicsage = isset($input['demographicsage']) ? $input['demographicsage'] : $product_obj->demographicsage;
			 $product_obj->strengths = isset($input['strengths']) ? $input['strengths'] : $product_obj->strengths;
			 $product_obj->ethnicity = isset($input['ethnicity']) ? $input['ethnicity'] : $product_obj->ethnicity;
			 $product_obj->videoUrl = isset($input['videoUrl']) ? $input['videoUrl'] : $product_obj->videoUrl;
			  $product_obj->imgdirection = isset($input['imgdirection']) ? $input['imgdirection'] :$product_obj->imgdirection;
			 $product_obj->slots = isset($input['slots']) ? $input['slots'] : $product_obj->slots;
			 $product_obj->hour = isset($input['hour']) ? $input['hour'] : $product_obj->hour;
			 $product_obj->flipsloops = isset($input['flipsloops']) ? $input['flipsloops'] :  $product_obj->flipsloops;
			
			 //Editing New Fields Start
			 $product_obj->audited = isset($input['audited']) ? $input['audited'] : $product_obj->audited;
			 $product_obj->firstImpression = isset($input['firstImpression']) ? $input['firstImpression'] : $product_obj->firstImpression;
			 $product_obj->secondImpression = isset($input['secondImpression']) ? $input['secondImpression'] : $product_obj->secondImpression;
			 $product_obj->thirdImpression = isset($input['thirdImpression']) ? $input['thirdImpression'] : $product_obj->thirdImpression;
			 $product_obj->forthImpression = isset($input['forthImpression']) ? $input['forthImpression'] : $product_obj->forthImpression;
			 $product_obj->cancellation_policy = isset($input['cancellation_policy']) ? $input['cancellation_policy'] : $product_obj->cancellation_policy;
			 $product_obj->state = isset($input['state']) ? $input['state'] : $product_obj->state;
			 $product_obj->height = isset($input['height']) ? $input['height'] : $product_obj->height;
			 $product_obj->width = isset($input['width']) ? $input['width'] : $product_obj->width;
			 $product_obj->vendor = isset($input['vendor']) ? $input['vendor'] : $product_obj->vendor;
			 $product_obj->sellerId = isset($input['sellerId']) ? $input['sellerId'] : $product_obj->sellerId;
			 $product_obj->mediahhi = isset($input['mediahhi']) ? $input['mediahhi'] : $product_obj->mediahhi;
			 $product_obj->firstdayofpurchase = isset($input['firstdayofpurchase']) ? $input['firstdayofpurchase'] : $product_obj->firstdayofpurchase;
			 $product_obj->lastdayofpurchase = isset($input['lastdayofpurchase']) ? $input['lastdayofpurchase'] : $product_obj->lastdayofpurchase;
			 $product_obj->weekPeriod = isset($input['weekPeriod']) ? $input['weekPeriod'] : $product_obj->weekPeriod;
			 //$product_obj->rateCard = isset($input['rateCard']) ? $input['rateCard'] : $product_obj->rateCard; 
			 $product_obj->rateCard = isset($input['rateCard']) ? str_replace( ',', '', $input['rateCard']) : str_replace( ',', '', $product_obj->rateCard);
			 $product_obj->installCost = isset($input['installCost']) ? $input['installCost'] : $product_obj->installCost;
			 $product_obj->negotiatedCost = isset($input['negotiatedCost']) ? $input['negotiatedCost'] : $product_obj->negotiatedCost;
			 $product_obj->productioncost = isset($input['productioncost']) ? $input['productioncost'] : $product_obj->productioncost;
			 $product_obj->notes = isset($input['notes']) ? $input['notes'] : $product_obj->notes;
			 $product_obj->Comments = isset($input['Comments']) ? $input['Comments'] : $product_obj->Comments;
			 $product_obj->fliplength = isset($input['fliplength']) ? $input['fliplength'] : $product_obj->fliplength;
			 $product_obj->looplength = isset($input['looplength']) ? $input['looplength'] : $product_obj->looplength;
			 $product_obj->locationDesc = isset($input['locationDesc']) ? $input['locationDesc'] : $product_obj->locationDesc;
			 $product_obj->description = isset($input['description']) ? $input['description'] : $product_obj->description;
			 $product_obj->sound = isset($input['sound']) ? $input['sound'] : $product_obj->sound;
			 $product_obj->staticMotion = isset($input['staticMotion']) ? $input['staticMotion'] : $product_obj->staticMotion;
			 $product_obj->file_type = isset($input['file_type']) ? $input['file_type'] : $product_obj->file_type;
			 $product_obj->product_newAge = isset($input['product_newAge']) ? $input['product_newAge'] : $product_obj->product_newAge;
			 $product_obj->medium = isset($input['medium']) ? $input['medium'] : $product_obj->medium;
			 $product_obj->firstDay = isset($input['firstDay']) ? $input['firstDay'] : $product_obj->firstDay;
			 $product_obj->lastDay = isset($input['lastDay']) ? $input['lastDay'] : $product_obj->lastDay;
			 $product_obj->cpm = isset($input['cpm']) ? $input['cpm'] : $product_obj->cpm;
			 $product_obj->firstcpm = isset($input['firstcpm']) ? $input['firstcpm'] : $product_obj->firstcpm;
			 $product_obj->thirdcpm = isset($input['thirdcpm']) ? $input['thirdcpm'] : $product_obj->thirdcpm;
			 $product_obj->forthcpm = isset($input['forthcpm']) ? $input['forthcpm'] : $product_obj->forthcpm;
			 $product_obj->ageloopLength = isset($input['ageloopLength']) ? $input['ageloopLength'] : $product_obj->ageloopLength;
			 $product_obj->product_newMedia = isset($input['product_newMedia']) ? $input['product_newMedia'] : $product_obj->product_newMedia;
			 $product_obj->placement = isset($input['placement']) ? $input['placement'] : $product_obj->placement; 
			 $product_obj->spotLength = isset($input['spotLength']) ? $input['spotLength'] : $product_obj->spotLength;
			 $product_obj->unitQty = isset($input['unitQty']) ? $input['unitQty'] : $product_obj->unitQty;
			 $product_obj->billingYes = isset($input['billingYes']) ? $input['billingYes'] : $product_obj->billingYes;
			 $product_obj->billingNo = isset($input['billingNo']) ? $input['billingNo'] : $product_obj->billingNo;
			 $product_obj->servicingYes = isset($input['servicingYes']) ? $input['servicingYes'] : $product_obj->servicingYes;
			 $product_obj->servicingNo = isset($input['servicingNo']) ? $input['servicingNo'] : $product_obj->servicingNo; 
			 $product_obj->fix = isset($input['fix']) ? $input['fix'] : $product_obj->fix; 
			 $product_obj->minimumdays = isset($input['minimumdays']) ? $input['minimumdays'] : $product_obj->minimumdays; 
			 $product_obj->network = isset($input['network']) ? $input['network'] : $product_obj->network; 
			 $product_obj->nationloc = isset($input['nationloc']) ? $input['nationloc'] : $product_obj->nationloc; 
			 $product_obj->daypart = isset($input['daypart']) ? $input['daypart'] : $product_obj->daypart; 
			 $product_obj->genre = isset($input['genre']) ? $input['genre'] : $product_obj->genre; 
			 $product_obj->costperpoint = isset($input['costperpoint']) ? $input['costperpoint'] : $product_obj->costperpoint; 
			 $product_obj->length = isset($input['length']) ? $input['length'] : $product_obj->length; 
			 $product_obj->reach = isset($input['reach']) ? $input['reach'] : $product_obj->reach; 
			 $product_obj->daysselected = isset($input['daysselected']) ? $input['daysselected'] : $product_obj->daysselected;
             $product_obj->stripe_percent = isset($input['stripe_percent']) ? $input['stripe_percent'] : $product_obj->stripe_percent;  
             			 
			 
			 //Editing New Fields End

			 if (isset($input['dates']) && !empty($input['dates'])) {  
                foreach ($input['dates'] as $date_range) {
				$product_obj->from_date = iso_to_mongo_date($date_range['startDate']);
				$product_obj->to_date = iso_to_mongo_date($date_range['endDate']);	
					
				}
			 }
				
			 
			 if(isset($input['area'])){
			 $area = Area::where('id', '=', $input['area'])->first();
			 }else{
				 $area = Area::where('id', '=', $product_obj->area)->first();
			 }
			 $product_obj->area = isset($input['area']) ? $input['area'] : $product_obj->area;
			  $product_obj->buses = isset($input['buses']) ? $input['buses'] : $product_obj->buses;
			 $product_obj->country_name = isset($area) ? $area->country_name : $product_obj->country_name;
             $product_obj->state_name = isset($area) ? $area->state_name : $product_obj->state_name;
             $product_obj->city_name = isset($area) ? $area->city_name : $product_obj->city_name;
             $product_obj->area_name = isset($area) ? $area->name : $product_obj->area_name;
             $product_obj->status = Product::$PRODUCT_STATUS['approved'];
             $product_obj->client_mongo_id = $product_obj->client_mongo_id;
             $product_obj->client_name = $product_obj->client_name;
			 $product_obj->product_visibility = $product_obj->product_visibility;
			  
             $product_obj->image = $product_obj->image;
            if ($this->request->hasFile('image')) {
                foreach ($this->request->file('image') as $key => $val) {
                    $imageNmae = \Carbon\Carbon::now()->timestamp . $val->getClientOriginalName();
                    if ($val->move($product_img_path, $imageNmae)) {
                        $imageArray[] = "/uploads/images/products/" . $imageNmae;
                    }
                }
                $product_obj->image = $imageArray;
            }
				$product_obj->symbol = $product_obj->symbol;
			 if ($request->hasFile('symbol')) {
                if ($request->file('symbol')->move($product_symbol_path, $request->file('symbol')->getClientOriginalName())) {
                    $product_obj->symbol = "/uploads/images/symbols/" . $request->file('symbol')->getClientOriginalName();
                }
            }
			$symbolPath='';
			if(isset($input['imgdirection'])){
				if($input['imgdirection'] == 'Left'){
				$symbolPath = "/uploads/images/symbols/".$product_obj->type."-left.png";
					
				}
			if($input['imgdirection'] == 'Right'){
				$symbolPath = "/uploads/images/symbols/".$product_obj->type."-right.png";
				}
				$product_obj->symbol = $symbolPath;
			}
			
			
				// if($product_obj->imgdirection == 'Left'){
				// }
			 // if($product_obj->imgdirection == 'Right'){
				// }
           //echo '<pre>in if'; print_r($product_obj); exit;
			
            if ($product_obj->save()) {
				    // Update data to elasticsearch :: Pankaj 22 Oct 2021    
                    $get_data = Product::where('id', '=', $product_obj->id)->first();
                    $this->es_etl($get_data, "update");
                    $success = true;
				    if (isset($input['dates']) && !empty($input['dates'])) {
               
                foreach ($input['dates'] as $date_range) {
                    $booking = new ProductBooking;
                    $booking->product_id = $product_obj->id;
                    $booking->booked_from = iso_to_mongo_date($date_range['startDate']);
                    $booking->booked_to = iso_to_mongo_date($date_range['endDate']);

                    $booking->product_owner = $product_obj->client_mongo_id;
					if($product_obj->slots!=''){
						$booking->booked_slots = $product_obj->slots;
					}
                    $booking->product_status = ProductBooking::$PRODUCT_STATUS['scheduled'];
                    if (!$booking->save()) {
                        $success = false;
                    }
                }
			}

            /*if(isset($input['stripe_percent']) && ($input['stripe_percent']!= $product_obj->stripe_percent)){
                //mail functionality 
             }*/  
               
				if($success == true){
                return response()->json(["status" => "1", "message" => "product Updated successfully."]);
			}
            } else {
                return response()->json(["status" => "0", "message" => "Failed to Update product."]);
            }
// handling editing of product ends
        }
//Handling New Product Adding
		else {
			try{
			//echo '<pre>in `else'; print_r($input); exit;
            $this->validate($request, [
                'product.price' => 'integer',
                //'image' => 'required',
                //'product.lat' => 'required',
                //'product.lng' => 'required',
                //'product.type' => 'required',
                    ], [
                'product.address.required' => 'Product address is required',
                'image.required' => 'Image is required',
                'product.lat.required' => 'Product Latitude is required',
                'product.lng.required' => 'Product Longitude is required',
                'product.type.required' => 'Product type is required',
                    ]
            );
			
            $product_obj = new Product; 
			$from_owner = ($product_obj->status == Product::$PRODUCT_STATUS['requested']);
            $product_obj->id = uniqid();
           
		     //$product_count = Product::where('client_mongo_id', '=', $client->id)->count();
		     //$product_count = Product::count();
		     $client_count = Client::count();
			 //$newSiteNo = $product_count+1;
			 //$newSiteNo = $product_count+1;
			 $newClientID = $client_count+1;
			 
			 //$siteNo = str_pad($newSiteNo, 3, '0', STR_PAD_LEFT);  
			 //$siteNo = str_pad($newSiteNo, 6, '0', STR_PAD_LEFT);
			 //$siteNo1 = '_'.$siteNo;

			$product_count = Product::latest()->first();
			$product_code_explode = explode("_", $product_count->siteNo);
			$siteNo1 = '_'.str_pad(end($product_code_explode)+1, 6, '0', STR_PAD_LEFT);			


			 //echo '<pre>';print_r($siteNo);exit; 
			 //$product_obj->siteNo = 'BA-'.$siteNo;  
			 $product_type = isset($input['type']) ? $input['type'] : "";
			 $product_type1 = '_'.$product_type;
			 
			 if($product_type == 'Static')
			 {
				 $product_obj->status = Product::$STATIC_PRODUCT['product-static'];
				 //echo '<pre>';print_r($product_obj->status);exit;   
			 }
			 else if($product_type == 'Digital')
			 {
				 $product_obj->status = Product::$DIGITAL_PRODUCT['product-digital'];
				 //echo '<pre>';print_r($product_obj->status);exit;   
			 }
			 else if($product_type == 'Digital/Static')
			 {
				 $product_obj->status = Product::$STATIC_DIGITAL_PRODUCT['product-digital-static'];
				 //echo '<pre>';print_r($st_product);exit;  
			 }
			 else if($product_type == 'Media')
			 {
				 $product_obj->status = Product::$MEDIA_PRODUCT['product-media'];
				 //$st_product = $product_obj->status; 
				 //echo '<pre>';print_r($st_product);exit;   
			 }
			 else
			 {
				$product_obj->status = $product_obj->status; 
			 }
			 
			 //echo '<pre>';print_r($product_obj->status);exit;  
			 
			 //$product_obj->client_mongo_id = isset($client) ? $client->id : "";
            //$product_obj->client_name = isset($client) ? $client->name : "";
			$clientId = str_pad($newClientID, 6, '0', STR_PAD_LEFT);
            $product_obj->client_id = isset($client) ? $client->client_id : "";
			 $clientId_uniqueID = $clientId.$product_obj->client_id;
			 $ASI_id = '000'.$product_obj->client_id;
			 $ASI_id1 = $clientId.$product_obj->client_id;
			 //echo '<pre>';print_r($ASI_id);exit;  
			 $seller_Id = isset($input['sellerId']) ? $input['sellerId'] : "";
			 $seller_Id1 = '_'.$seller_Id;
			 //echo '<pre>';print_r($seller_Id1);exit;  
			 //$product_obj->siteNo = 'AMP_'.$seller_Id.$product_type1.$siteNo1; 
			 //$product_obj->siteNo = 'AMP_'.$seller_Id.$product_obj->status.$siteNo1;  
			 //$product_obj->siteNo = 'AMP'.$product_obj->status.'_ASI'.$product_obj->client_id.$siteNo1; 
			 $product_obj->siteNo = 'AMP'.$product_obj->status.'_ASI'.$ASI_id.$siteNo1; 
			 //$product_obj->siteNo = 'AMP'.$product_obj->status.'_ASI'.$clientId_uniqueID.$siteNo1; 
			 //echo '<pre>';print_r($product_obj->siteNo);exit; 
			 
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
			 //$product_obj->default_price = isset($input['price']) ? $input['price'] : "";
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
			 
			 /*New Fields Adding Starts*/
			 $product_obj->audited = isset($input['audited']) ? $input['audited'] : "";
			 $product_obj->cancellation_policy = isset($input['cancellation_policy']) ? $input['cancellation_policy'] : "";
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
			 //$product_obj->rateCard = isset($input['rateCard']) ? $input['rateCard'] : "";
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
			 
			 /*New Fields Adding Ends*/
			 
			 
			 if (isset($input['dates']) && !empty($input['dates'])) {  
			 //echo 'dsdsd';exit;
                		foreach ($input['dates'] as $date_range) {
					$product_obj->from_date = iso_to_mongo_date($date_range['startDate']);
					//$product_obj->pro_from_date = iso_to_mongo_date($date_range['startDate']);
					$product_obj->to_date = iso_to_mongo_date($date_range['endDate']);	
					//$product_obj->pro_to_date = iso_to_mongo_date($date_range['endDate']);	
				}
			 }/*else if(isset($input['dates_clone']) && !empty($input['dates_clone']) && empty($input['dates'])){
				$explode_date = @explode('||',$input['dates_clone']);
				foreach ($explode_date as $date_range) {
					$from_to_date = @explode('**',$date_range);
					$product_obj->from_date = iso_to_mongo_date($from_to_date[0]);
					$product_obj->to_date = iso_to_mongo_date($from_to_date[1]);
				}
			 }*/
			 
			 $product_obj->buses = isset($input['buses']) ? $input['buses'] : "";
			 //echo '<pre>product_obj'; print_r($product_obj); exit;
			 $area = Area::where('id', '=', $input['area'])->first();
			//dd($area);die();
			 $product_obj->area = isset($input['area']) ? $input['area'] : "";
			 $product_obj->country_name = isset($area) ? $area->country_name : "";
             $product_obj->state_name = isset($area) ? $area->state_name : "";
             $product_obj->city_name = isset($area) ? $area->city_name : "";
             $product_obj->area_name = isset($area) ? $area->name : "";
             $product_obj->status = Product::$PRODUCT_STATUS['approved'];
			 $product_obj->product_visibility = 1;
          
            $product_obj->client_mongo_id = isset($client) ? $client->id : "";
            $product_obj->client_name = isset($client) ? $client->name : "";
            $product_obj->client_id = isset($client) ? $client->client_id : "";
            // $product_obj->client_email = isset($client) ? $client->email : "";
            // $product_obj->client_phone = isset($client) ? $client->phone : "";
            // $product_obj->client_address = isset($client) ? $client->address : "";
            //echo '<pre>product_obj--'; print_r($product_obj); exit;
            $product_obj->image = "";

		

           
            if ($this->request->hasFile('image')) {
                foreach ($this->request->file('image') as $key => $val) {
                    $imageNmae = \Carbon\Carbon::now()->timestamp . $val->getClientOriginalName();
                    if ($val->move($product_img_path, $imageNmae)) {
                        $imageArray[] = "/uploads/images/products/" . $imageNmae;
                    }
                }
                $product_obj->image = $imageArray;
            }/*else if(empty($this->request->hasFile('image')) && isset($input['image_clone']) && !empty($input['image_clone'])){
				$imageNmae = \Carbon\Carbon::now()->timestamp . $input['image_clone'];
			copy("/uploads/images/products/".$input['image_clone'], "/uploads/images/products/".$imageNmae);
			$product_obj->image = array($imageNmae);
		}*/



			//$product_obj->symbol = '';
			 if ($request->hasFile('symbol')) {
                if ($request->file('symbol')->move($product_symbol_path, $request->file('symbol')->getClientOriginalName())) {
                    $product_obj->symbol = "/uploads/images/symbols/" . $request->file('symbol')->getClientOriginalName();
                }
            }
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
           //echo '<pre>product_obj-'; print_r($product_obj); exit; 
           // dd($product_obj);die(); 
           
            if ($product_obj->save()) {
                // Insert data to elasticsearch :: Pankaj 22 Oct 2021    
                $get_data = Product::where('id', '=', $product_obj->id)->first();
                $this->es_etl($get_data, "insert");
				    if (isset($input['dates']) && !empty($input['dates'])) {
                foreach ($input['dates'] as $date_range) {
                    $booking = new ProductBooking;
                    $booking->product_id = $product_obj->id;
                    $booking->booked_from = iso_to_mongo_date($date_range['startDate']);
                    $booking->booked_to = iso_to_mongo_date($date_range['endDate']);
					if(isset($input['flips'])){
						$booking->booked_slots = $input['flips'];
					}
                    $booking->product_owner = $product_obj->client_mongo_id;
                    $booking->product_status = ProductBooking::$PRODUCT_STATUS['scheduled'];
                    $booking->save();
                }
			}
			//Notification Starts
			if($user_mongo['client_mongo_id']== $client->id)
			 {
				 $client_mongo= $client;
                    $noti_array = [
                        'type' => Notification::$NOTIFICATION_TYPE['product-requested'],
                        'from_id' => $product_obj->client_mongo_id,
                        'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
                        'to_id' => null,
                        'to_client' => null,
                        'desc' => "New product added by " . $client_mongo->name,
                        'message' => $client_mongo->name . " added a product to our inventory.",
                        'data' => ["product_id" => $product_obj->id]
                    ];
                    $mail_array = [
                        'mail_tmpl_params' => [
                            'sender_email' => config('app.bbi_email'),
                            'receiver_name' => "",
                            'mail_message' => $client->name . " added a product to our inventory"
                        ],
                        //'subject' => 'New product added! - Billboards India'
                        'subject' => 'New product added! - Advertising Marketplace'
                    ];
                    event(new ProductRequestedEvent($noti_array,$mail_array));
                    /*$notification_obj = new Notification;
					$notification_obj->id = uniqid();
                    $notification_obj->type = "product-request";
                    $notification_obj->from_id = $product_obj->client_mongo_id;
                    $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['bbi'];
                    $notification_obj->to_id = null;
                    $notification_obj->to_client = null;
                    $notification_obj->desc = "New product requested";
                    $notification_obj->message = $client_mongo->name . " added a product to our inventory.";
                    $notification_obj->product_id = $product_obj->id;
					$notification_obj->status = 0;
                    $notification_obj->save();*/
                    $bbi_sa_id = Client::where('company_name', '=', 'BBI')->first()->super_admin;
                    $bbi_sa = UserMongo::where('user_id', '=', $bbi_sa_id)->first();
                    $mail_tmpl_params = [
                        'receiver_name' => $bbi_sa->first_name,
                        'mail_body' => "<b>" . $client_mongo->company_name . "</b> added a product to our inventory..",
                        //'mail_message' => $client_mongo->company_name . ' added a product into Billboards inventory.'
                        'mail_message' => $client_mongo->company_name . ' added a product into Advertising Marketplace inventory.'
                    ];
                    $mail_data = [
                        'email_to' => $bbi_sa->email,
                        'email_to1' => 'admin@advertisingmarketplace.com',
                        'recipient_name' => $bbi_sa->first_name
                    ];
                    Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data) {
                        //$message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('An Owner has added a product to our inventory.');
						$message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('A customer has added a product to our inventory.');
						$message->cc($mail_data['email_to1'], $mail_data['recipient_name'])->subject('A customer has added a product to our inventory.');
                    });
                }
				//echo '<pre>input'; print_r($notification_obj); exit;
				//Noification Ends. 
				
				if ($from_owner) {
					$product_owner_email = ClientMongo::where('id', '=', $product_obj->client_mongo_id)->first()->email;
                   $noti_array = [
                        'type' => Notification::$NOTIFICATION_TYPE['product-requested'],
                        'from_id' => null,
                        'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['owner'],
                        'to_id' => $product_obj->client_mongo_id,
                        'to_client' => $product_obj->client_mongo_id,
                        'desc' => "Product requested",
                        'message' => "Your product has been added in the inventory",
                        'data' => ["product_id" => $product_obj->id]
                    ];
                    $mail_array = [
                        'mail_tmpl_params' => [
                            'sender_email' => config('app.bbi_email'),
                            'receiver_name' => "",
                            //'mail_message' => "'The product you requested to be added to Billboards inventory has been approved"
                            'mail_message' => "'The product you requested to be added to Advertising Marketplace inventory has been requested"
                        ],
                        //'subject' => 'New product Approved! - Billboards India'
                        'subject' => 'New product Requested! - Advertising Marketplace'
                    ];
					
					event(new ProductApprovedEvent($noti_array,$mail_array));
					 
					/*$notification_obj = new Notification;
					$notification_obj->id = uniqid();
					
                    $notification_obj->type = "product";
                    $notification_obj->from_id=null;
                    $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['owner'];
					
                    $notification_obj->to_id = $product_obj->client_mongo_id;
                    $notification_obj->to_client = $product_obj->client_mongo_id;
					
                    $notification_obj->desc = "Product requested";
                    $notification_obj->message ="Your product has been added in the inventory";
                    $notification_obj->product_id = $product_obj->id;
					
					$notification_obj->status = 0;
                    $notification_obj->save();*/
					
					$bbi_sa_id = Client::where('company_name', '=', 'BBI')->first()->super_admin;
                    $bbi_sa = UserMongo::where('user_id', '=', $bbi_sa_id)->first();
                    //event(new ProductApprovedEvent($noti_array,$mail_array));
					
                   
// send email
                    $mail_tmpl_params = [
                        'sender_email' => config('app.bbi_email'), //, 
                        'receiver_name' => "",
                        //'mail_message' => 'The product you requested to be added to Billboards inventory has been approved by the admin.'
                        'mail_message' => 'The product you requested to be added to Advertising Marketplace inventory'
                    ];
					$mail_data = [
                        'email_to' => $bbi_sa->email,
                        'email_to1' => 'admin@advertisingmarketplace.com',
                        'recipient_name' => $bbi_sa->first_name
                    ];
                   /*Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($product_owner_email) {
                        //$message->bcc([$product_owner_email])->subject('Requested product approved - Billboards India');
                        $message->bcc([$product_owner_email])->subject('Product added - Advertising Marketplace');
                    });*/
					Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data) {
                        //$message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('An Owner has added a product to our inventory.');
						$message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('A customer has added a product to our inventory.');
						$message->cc($mail_data['email_to1'], $mail_data['recipient_name'])->subject('A customer has added a product to our inventory.');
                    });
					//echo '<pre>input'; print_r($notification_obj); exit;
					
				}
				
                return response()->json(["status" => "1", "message" => "Product saved successfully."]);
            } else {
                return response()->json(["status" => "0", "message" => "Failed to save product."]);
            }
// saving new product ends 
		} catch (Exception $ex) {
Log::error($ex);
print_r($ex);
                }
	}
		 
  
}

    public function es_etl($get_data, $opr){
        $url_insert = env('ES_SERVER_URL_INSERT');
        $url_delete = env('ES_SERVER_URL_DELETE');

        $index = env('ES_PRODUCTS');   
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

            if ( is_null($get_data->from_date) ) {
                $new_from_date = "";
            } else {
                $from_date = $get_data->from_date;
                $d_from_date = date("Y-m-d", strtotime($from_date));
                $t_from_date = date("H:i:s", strtotime($from_date));
                $new_from_date = $d_from_date."T".$t_from_date.".000Z";    
            }  
            if ( is_null($get_data->to_date) ) {
                $new_to_date = "";
            } else {
                $to_date = $get_data->to_date;
                $d_to_date = date("Y-m-d", strtotime($to_date));
                $t_to_date = date("H:i:s", strtotime($to_date));
                $new_to_date = $d_to_date."T".$t_to_date.".000Z";
            }                           
            
            $data_string = array(
                "index" => $index,
                "data" => array (
                    array (
                        "id" => $get_data->id,
                        "siteNo" => $get_data->siteNo,
                        "client_mongo_id" => $get_data->client_mongo_id,
                        "lighting" => $get_data->lighting,
                        "cancellation" => $get_data->cancellation,
                        "type" => $get_data->type,
                        "minimumbooking" => $get_data->minimumbooking,
                        "title" => $get_data->title,
                        "panelSize" => $get_data->panelSize,
                        "venue" => $get_data->venue,
                        "address" => $get_data->address,
                        "city" => $get_data->city,
                        "zipcode" => $get_data->zipcode,
                        "lat" => $get_data->lat,
                        "lng" => $get_data->lng,
                        "direction" => $get_data->direction,
                        "default_price" => $get_data->default_price,
                        "impressions" => $get_data->impressions,
                        "demographicsage" => $get_data->demographicsage,
                        "strengths" => $get_data->strengths,
                        "ethnicity" => $get_data->ethnicity,
                        "videoUrl" => $get_data->videoUrl,
                        "imgdirection" => $get_data->imgdirection,
                        "slots" => $get_data->slots,
                        "hour" => $get_data->hour,
                        "flipsloops" => $get_data->flipsloops,
                        "audited" => $get_data->audited,
                        "cancellation_policy" => $get_data->cancellation_policy,
                        "firstImpression" => $get_data->firstImpression,
                        "secondImpression" => $get_data->secondImpression,
                        "thirdImpression" => $get_data->thirdImpression,
                        "forthImpression" => $get_data->forthImpression,
                        "state" => $get_data->state,
                        "height" => $get_data->height,
                        "width" => $get_data->width,
                        "sellerId" => $get_data->sellerId,
                        "mediahhi" => $get_data->mediahhi,
                        "firstdayofpurchase" => $get_data->firstdayofpurchase,
                        "lastdayofpurchase" => $get_data->lastdayofpurchase,
                        "weekPeriod" => $get_data->weekPeriod,
                        "rateCard" => $get_data->rateCard,
                        "installCost" => $get_data->installCost,
                        "negotiatedCost" => $get_data->negotiatedCost,
                        "productioncost" => $get_data->productioncost,
                        "notes" => $get_data->notes,
                        "Comments" => $get_data->Comments,
                        "fliplength" => $get_data->fliplength,
                        "looplength" => $get_data->looplength,
                        "locationDesc" => $get_data->locationDesc,
                        "sound" => $get_data->sound,
                        "staticMotion" => $get_data->staticMotion,
                        "file_type" => $get_data->file_type,
                        "product_newAge" => $get_data->product_newAge,
                        "medium" => $get_data->medium,
                        "firstDay" => $get_data->firstDay,
                        "lastDay" => $get_data->lastDay,
                        "buses" => $get_data->buses,
                        "area" => $get_data->area,
                        "country_name" => $get_data->country_name,
                        "state_name" => $get_data->state_name,
                        "city_name" => $get_data->city_name,
                        "area_name" => $get_data->area_name,
                        "status" => $get_data->status,
                        "product_visibility" => $get_data->product_visibility,
                        "client_name" => $get_data->client_name,
                        "symbol" => $get_data->symbol,
                        "cpm" => $get_data->cpm,
                        "firstcpm" => $get_data->firstcpm,
                        "thirdcpm" => $get_data->thirdcpm,
                        "forthcpm" => $get_data->forthcpm,
                        "description" => $get_data->description,
                        "ageloopLength" => $get_data->ageloopLength,
                        "spotLength" => $get_data->spotLength,
                        "unitQty" => $get_data->unitQty,
                        "billingYes" => $get_data->billingYes,
                        "billingNo" => $get_data->billingNo,
                        "servicingYes" => $get_data->servicingYes,
                        "servicingNo" => $get_data->servicingNo,
                        "fix" => $get_data->fix,
                        "product_newMedia" => $get_data->product_newMedia,
                        "placement" => $get_data->placement,
                        "minimumdays" => $get_data->minimumdays,
                        "network" => $get_data->network,
                        "nationloc" => $get_data->nationloc,
                        "daypart" => $get_data->daypart,
                        "genre" => $get_data->genre,
                        "costperpoint" => $get_data->costperpoint,
                        "length" => $get_data->length,
                        "reach" => $get_data->reach,
                        "daysselected" => $get_data->daysselected,
                        "pixelsFeet" => $get_data->pixelsFeet,
                        "product" => $get_data->product,
                        "client_email" => $get_data->client_email,
                        "client_phone" => $get_data->client_phone,
                        "client_address" => $get_data->client_address,
                        "stripe_percent" => $get_data->stripe_percent,
                        "updated_at" => $new_updated_at,
                        "created_at" => $new_created_at,
                        "from_date" => $new_from_date,
                        "to_date" => $new_to_date
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
  
 public function getDigitalProductUnavailableDates(Request $request) { 
 
 $input =$request->input();
$product_id = $input['product_id'];
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $date_ranges = ProductBooking::select('booked_from', 'booked_to','booked_slots')
		->where([['product_id', '=', $product_id],
                    ['product_status', '=', ProductBooking::$PRODUCT_STATUS['scheduled']]
                ])
				->get();
				$arraynew = array();
				//dd($date_ranges);
				$tempbooked_from = '';
		foreach($date_ranges as $key=>$val){
	$booked_from =date('Y-m-d',strtotime($val['booked_from']));
	$booked_to =date('Y-m-d',strtotime($val['booked_to']));
	$diff=date_diff(date_create($booked_from),date_create($booked_to));
	$daysCount = $diff->format("%a");
	$tempbooked_from = $booked_from;
	$count = ($daysCount+1);
	for($i=0; $i<$count; $i++){
		$newArray['booked_from'] =$tempbooked_from;
		$newArray['booked_to'] =date('Y-m-d',strtotime($tempbooked_from."+ 0day"));
		$newArray['booked_slots'] =$val['booked_slots'];
		$newArray['no'] =$i;
		$tempbooked_from = date('Y-m-d',strtotime($tempbooked_from."+ 1day"));
		$arraynew[]=$newArray;
		//$i++;
	}
	}
	 $output = Array();
//echo '<pre>'; dd($arraynew); 
  foreach($arraynew as $value) {
    $output_element = &$output[$value['booked_from'] . "_" . $value['booked_to']];
    $output_element['booked_from'] = $value['booked_from'];
    $output_element['booked_to'] = $value['booked_to'];
    !isset($output_element['booked_slots']) && $output_element['booked_slots'] = 0;
    $output_element['booked_slots'] += $value['booked_slots'];
  }

	$arrayOutput = array_values($output);
	
        return response()->json($arrayOutput);
    }

 public function generatePop($campaign_id){ 
	   $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        $user_internal = User::where('id', '=', $user_mongo['user_id'])->first();
		  $productBooked = ProductBooking::where([['campaign_id', '=',  $campaign_id]])->get();
		  $campaign = Campaign::where('id', '=', $campaign_id)->first();
			foreach($productBooked as $product){
				
				$productData = Product::where('id', '=', $product['product_id'])->first();

				$slots = $productData->slots;
				$hours = $productData->hour;
				$bonus =1;
				$startDate = date('d-m-Y',strtotime($product['booked_from']));
				$endDate =  date('d-m-Y',strtotime($product['booked_to']));
				$current_date = date('d-m-Y',strtotime("-1 day"));
				
				$booked_slots = $product['booked_slots'];

				if($endDate >= $current_date){
					$calculationDate = $current_date;
				}
				else
				{
					$calculationDate = $endDate;
				}
				$diff=date_diff(date_create($calculationDate),date_create($booked_from_date));
				$daysCount = $diff->format("%a");

				$productBooked = ProductBooking::where('product_id', '=',  $product['product_id'])
										   ->where('booked_from','<=',$product['booked_to'])
										   ->where('booked_to','>=',$product['booked_from'])
										   ->sum('booked_slots');

				if($productBooked <= $slots && $bonus ==1){
					$array['acctual_spots'] =$hours * $booked_slots * $daysCount ;
					$array['deliverdSpots']= round(($hours * $slots)/$productBooked) * $daysCount;
					$array['varience']=$acctual_spots-$deliverdSpots;
					$array['varience_percentage'] = ($varience/$acctual_spots)*100;
					$final_array[]=$array;
				}				   
							   
				else{
					$array['acctual_spots'] =$hours * $booked_slots * $daysCount ;
					$array['deliverdSpots']= $hours * $booked_slots * $daysCount;
					$array['varience']=$acctual_spots-$deliverdSpots;
					$array['varience_percentage'] = ($varience/$acctual_spots)*100;
					$final_array[]=$array;
				}	
			}
			 return response()->json(["status" => "1", "pop" => $final_array]);
	  }
	  
	  	  //Bulk Upload Start
	  
	  public function addBulkUpload(Request $request){
	   $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
	   //echo '<pre>'; print_r($user_mongo); exit;
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }
		
		//echo '<pre>input'; print_r($this->input); exit;
		//dd($this->input['editRequestedhordings']);
        //$input = $input['product'];
		 /*if (isset($this->input['editRequestedhordings']) && !empty($this->input['editRequestedhordings'])) {
            $input['product'] = $this->input['editRequestedhordings'];
        }*/
       if (isset($this->input['editRequestedhordings']) && !empty($this->input['editRequestedhordings'])) {
            $input['product'] = $this->input['editRequestedhordings'];
        }else{
			$input['product']=$this->input;
			//$input=$this->input;
		}
		//$input = $input['product'];
		//echo '<pre>sss'; print_r($input); exit;
		//dd($input);
        $product_img_path = base_path() . '/html/uploads/images/products';
        $product_symbol_path = base_path() . '/html/uploads/images/symbols';
      
        if (isset($input['client'])) {
            $client = ClientMongo::where('id', '=', $input['client'])->first();
        }else{
			$client = ClientMongo::where('id', '=', $user_mongo['client_mongo_id'])->first();
		}
	

			//echo '<pre>request'; print_r($input); exit;
            $this->validate($request, [
                'product.price' => 'integer',
                //'image' => 'required',
                //'product.lat' => 'required',
                //'product.lng' => 'required',
                //'product.type' => 'required',
                    ], [
                'image.required' => 'Image is required',
                    ]
            );
			
            $product_obj = new BulkUpload;
			
			
            $product_obj->id = uniqid();
			 //$product_obj->imgdirection = isset($input['imgdirection']) ? $input['imgdirection'] : "";
             //$product_obj->status = BulkUpload::$PRODUCT_STATUS['approved'];
			 //$product_obj->product_visibility = 1;
          
            $product_obj->client_mongo_id = isset($client) ? $client->id : "";
            $product_obj->client_name = isset($client) ? $client->name : "";
			$product_obj->seller_name = isset($input['seller_name']) ? $input['seller_name'] : "";
			$product_obj->subseller_name = isset($input['subseller_name']) ? $input['subseller_name'] : "";
            
            $product_obj->image = "";
           
            if ($this->request->hasFile('image')) {
                foreach ($this->request->file('image') as $key => $val) {
                    $imageNmae = \Carbon\Carbon::now()->timestamp . $val->getClientOriginalName();
                    if ($val->move($product_img_path, $imageNmae)) {
                        $imageArray[] = "/uploads/images/products/" . $imageNmae;
                    }
                }
                $product_obj->image = $imageArray;
            }
			//echo '<pre>input'; print_r($product_obj); exit;
			/*$symbolPath = '';
			if(isset($input['imgdirection'])){
				if($input['imgdirection'] == 'Left'){
				$symbolPath = "/uploads/images/symbols/".$product_obj->type."-left.png";
					
				}
			if($input['imgdirection'] == 'Right'){
				$symbolPath = "/uploads/images/symbols/".$product_obj->type."-right.png";
				}
			}
			$product_obj->symbol = $symbolPath;*/
           //echo '<pre>input'; print_r($product_obj); exit;
           // dd($product_obj);die();
           
            if ($product_obj->save()) {
				   /* if (isset($input['dates']) && !empty($input['dates'])) {
                foreach ($input['dates'] as $date_range) {
                    $booking = new ProductBooking;
                    $booking->product_id = $product_obj->id;
                    $booking->booked_from = iso_to_mongo_date($date_range['startDate']);
                    $booking->booked_to = iso_to_mongo_date($date_range['endDate']);
					if(isset($input['flips'])){
						$booking->booked_slots = $input['flips'];
					}
                    $booking->product_owner = $product_obj->client_mongo_id;
                    $booking->product_status = ProductBooking::$PRODUCT_STATUS['scheduled'];
                    $booking->save();
                }
			}*/
			//Notification Starts
			if($user_mongo['client_mongo_id']== $client->id)
			 {
				 $client_mongo= $client;
                    $noti_array = [
                        'type' => Notification::$NOTIFICATION_TYPE['product-requested'],
                        'from_id' => $product_obj->client_mongo_id,
                        'to_type' => Notification::$NOTIFICATION_CLIENT_TYPE['bbi'],
                        'to_id' => null,
                        'to_client' => null,
                        'desc' => "Bulk Products uploaded by " . $client_mongo->name,
                        //'message' => $client_mongo->name . " added a product to our inventory.",
                        'message' => $client_mongo->name . " uploaded bulk Products.",
                        'data' => ["product_id" => $product_obj->id]
                    ];
                    $mail_array = [
                        'mail_tmpl_params' => [
                            'sender_email' => config('app.bbi_email'),
                            'receiver_name' => "",
                            //'mail_message' => $client->name . " added a product to our inventory"
                            'mail_message' => $client->name . " uploaded bulk Products"
                        ],
                        //'subject' => 'New product added! - Billboards India'
                        'subject' => 'Bulk Products uploaded! - Advertising Marketplace'
                    ];
                    event(new ProductRequestedEvent($noti_array,$mail_array));
                    $notification_obj = new Notification;
					$notification_obj->id = uniqid();
                    $notification_obj->type = "product-request";
                    $notification_obj->from_id = $product_obj->client_mongo_id;
                    $notification_obj->to_type = Notification::$NOTIFICATION_CLIENT_TYPE['bbi'];
                    $notification_obj->to_id = null;
                    $notification_obj->to_client = null;
                    $notification_obj->desc = "Bulk Products requested";
                    $notification_obj->message = $client_mongo->name . " uploaded bulk Products.";
                    $notification_obj->product_id = $product_obj->id;
					$notification_obj->status = 0;
                    $notification_obj->save();
                    $bbi_sa_id = Client::where('company_name', '=', 'BBI')->first()->super_admin;
                    $bbi_sa = UserMongo::where('user_id', '=', $bbi_sa_id)->first();
                    $mail_tmpl_params = [
                        'receiver_name' => $bbi_sa->first_name,
                        'mail_body' => "<b>" . $client_mongo->company_name . "</b> uploaded bulk Products..",
                        //'mail_message' => $client_mongo->company_name . ' added a product into Billboards inventory.'
                        'mail_message' => $client_mongo->company_name . ' uploaded bulk products into Advertising Marketplace.'
                    ];
                    $mail_data = [
                        'email_to' => $bbi_sa->email,
                        'recipient_name' => $bbi_sa->first_name
                    ];
                    Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data) {
                        $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('An Owner has uploaded bulk Products.');
                    });
                }
				
				//Noification Ends.
                return response()->json(["status" => "1", "message" => "File Uploaded Successfully."]);
            } else {
                return response()->json(["status" => "0", "message" => "Failed to Upload File."]);
            }

		 
  
}
	  
 //Bulk Upload End
 
     public function getBulkUploadProducts(Request $request) {
        $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        if (!isset($user_mongo) || empty($user_mongo)) {
            return response()->json(['status' => 0, 'message' => 'Invalid user. Please log in again and try.']);
        } else {
            $client_mongo = ClientMongo::where('id', '=', $user_mongo['client_mongo_id'])->first();
			//echo '<pre>';print_r($client_mongo);exit;
            if (!isset($client_mongo) || empty($client_mongo)) {
                return response()->json(['status' => 0, 'message' => 'You can not view the products of a company you\'re not a part of.']);
            } else {
                $page_no = $request->input('page_no');
                $page_size = $request->input('page_size');
                $start_date = $request->input('start_date');
                $end_date = $request->input('end_date');
                $format = $request->input('format');
				//dd($format['name']);
                $budget = $request->input('budget');
                if (isset($page_no, $page_size) && !empty($page_no) && !empty($page_size)) {
                    /*$offset = ($page_no - 1) * $page_size;
                    if (isset($start_date) && isset($end_date)) {
                        $unavailable_product_ids = ProductBooking::where([
                                    ['product_owner', '=', $user_mongo['client_mongo_id']],
                                    ['booked_from', '<=', $start_date],
                                    ['booked_to', '>=', $end_date]
                                ])->pluck('product_id');
                    }
                    if (isset($format) && !empty($format)) {
                        $product_array = [
                            ['client_mongo_id', '=', $client_mongo->id],
                            ['type', '=', $format['name']],
                            ['status', '=', BulkUpload::$PRODUCT_STATUS['approved']]
                        ];
                    } else {
                        $product_array = [
                            ['client_mongo_id', '=', $client_mongo->id],
                            ['status', '=', BulkUpload::$PRODUCT_STATUS['approved']]
                        ];
                    }

                    if (isset($budget) && !empty($budget)) {
//echo "1223";
                        if ($budget == 1) {
                            if (isset($start_date) && isset($end_date))
                                $bulk_upload = BulkUpload::where($product_array)->whereNotIn('id', $unavailable_product_ids)->orderBy('default_price', 'asc')->skip($offset)->take((int) $page_size)->get();
                            else
                                $bulk_upload = BulkUpload::where($product_array)->orderBy('updated_at', 'desc')->skip($offset)->take((int) $page_size)->get();
                        } else if ($budget == 0) {
                            if (isset($start_date) && isset($end_date))
                                $bulk_upload = BulkUpload::where($product_array)->whereNotIn('id', $unavailable_product_ids)->orderBy('default_price', 'desc')->skip($offset)->take((int) $page_size)->get();
                            else
                                $bulk_upload = BulkUpload::where($product_array)->orderBy('updated_at', 'desc')->skip($offset)->take((int) $page_size)->get();
                        }
                    } else {
                        if (isset($start_date) && isset($end_date))
                            $bulk_upload = BulkUpload::where($product_array)->whereNotIn('id', $unavailable_product_ids)->skip($offset)->take((int) $page_size)->get();
                        else
                            $bulk_upload = BulkUpload::where($product_array)->skip($offset)->take((int) $page_size)->get();
                    }
                    if (isset($start_date) && isset($end_date))
                        $product_count = BulkUpload::where($product_array)->count();
                    else
                        $product_count = BulkUpload::where($product_array)->count();

                    foreach ($bulk_upload as $product) {
                        $already_shortlisted = ShortlistedProduct::where([
                                    ['client_mongo_id', '=', $client_mongo->id],
                                    ['product_id', '=', $product->id]
                                ])->get();
                        if (count($already_shortlisted) > 0) {
                            $product->shortlisted = true;
                        }
                        $camapigns_count = ProductBooking::where('product_id', '=', $product->id)->pluck('campaign_id')->toArray();

                        if ($camapigns_count) {
                            $camapigns_count = count(array_filter($camapigns_count));
                            $product->camapigns_count = $camapigns_count;
                        }
                    }*/

$bulk_upload = BulkUpload::where([
                                //['client_mongo_id', '=', $client_mongo->id],
                                ['client_mongo_id', '!=', ''],
                               // ['status', '=', BulkUpload::$PRODUCT_STATUS['approved']]
                            ])->orderBy('updated_at', 'desc')->get();
                    $owner_products = [
                        "bulk_upload" => $bulk_upload,
                        //"page_count" => ceil($product_count / $page_size)
                    ];
                } else {
					//echo $client_mongo->id;//exit;
                    $bulk_upload = BulkUpload::where([
                                //['client_mongo_id', '=', $client_mongo->id],
                                ['client_mongo_id', '!=', ''],
                                //['status', '=', BulkUpload::$PRODUCT_STATUS['approved']]
                            ])->orderBy('updated_at', 'desc')->get();
							//echo '<pre>';print_r($bulk_upload);exit;
                    /*foreach ($bulk_upload as $product) {
                        $already_shortlisted = ShortlistedProduct::where([
                                    ['client_mongo_id', '=', $client_mongo->id],
                                    ['product_id', '=', $product->id]
                                ])->get();
                        if (count($already_shortlisted) > 0) {
                            $product->shortlisted = true;
                        }
                        $camapigns_count = ProductBooking::where('product_id', '=', $product->id)->pluck('campaign_id')->toArray();

                        if ($camapigns_count) {
                            $camapigns_count = count(array_filter($camapigns_count));
                            $product->camapigns_count = $camapigns_count;
                        } 
                    }*/
                    $owner_products = [
                        "bulk_upload" => $bulk_upload,
                    ];
                }
                return response()->json($owner_products); 
            }
        }
    }
	
	 public function getProductsCount(){
	  $static_count = Product::where('type', '=', 'Static')->count(); 
	  $digital_count = Product::where('type', '=', 'Digital')->count(); 
	  $digital_static_count = Product::where('type', '=', 'Digital/Static')->count(); 
	  $media_count = Product::where('type', '=', 'Media')->count(); 
	  //echo '<pre>'; print_r($users_count); exit;
	  $productsCount = [
            'static' => $static_count,
            'digital' => $digital_count,
            'digital/static' => $digital_static_count,
            'media' => $media_count
        ];
		
	  return response()->json($productsCount);
  }


  
  //Clone Product  
  public function cloneProductDetails(Request $request){
	  
	//dd('shiva');die();
	//echo '<pre>'; print_r($request); exit;
	   $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
	   //echo '<pre>'; print_r($user_mongo); exit;
        if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all();
        }

       if (isset($this->input['editRequestedhordings']) && !empty($this->input['editRequestedhordings'])) {
            $input['product'] = $this->input['editRequestedhordings'];
        }else{
			$input['product']=$this->input;
			//$input=$this->input;
		}
		//$input = $input['product'];
		//echo '<pre>sss'; print_r($input); exit;
		//dd($input);
        $product_img_path = base_path() . '/html/uploads/images/products';
        $product_symbol_path = base_path() . '/html/uploads/images/symbols';
      
        if (isset($input['client'])) {
            $client = ClientMongo::where('id', '=', $input['client'])->first();
        }else{
			$client = ClientMongo::where('id', '=', $user_mongo['client_mongo_id'])->first();
		}
	
	// handling editing of product
        if (isset($input['id'])) {
			//echo '<pre>in if'; print_r($input); exit; 
            $product_obj = Product::where('id', '=', $input['id'])->first();
			//echo '<pre>'; print_r($product_obj); exit;
			$product_obj_add = new Product;
			$product_obj_add->id = uniqid();
			
			$client_count = Client::count();
			 $newClientID = $client_count+1;

			$product_count = Product::latest()->first();
			$product_code_explode = explode("_", $product_count->siteNo);
			$siteNo1 = '_'.str_pad(end($product_code_explode)+1, 6, '0', STR_PAD_LEFT);		 
			 $product_type = isset($input['type']) ? $input['type'] : "";
			 $product_type1 = '_'.$product_type;
			 
			 if($product_type == 'Static')
			 {
				 $product_obj_add->status = Product::$STATIC_PRODUCT['product-static'];
			 }
			 else if($product_type == 'Digital')
			 {
				 $product_obj_add->status = Product::$DIGITAL_PRODUCT['product-digital'];
			 }
			 else if($product_type == 'Digital/Static')
			 {
				 $product_obj_add->status = Product::$STATIC_DIGITAL_PRODUCT['product-digital-static']; 
			 }
			 else if($product_type == 'Media')
			 {
				 $product_obj_add->status = Product::$MEDIA_PRODUCT['product-media']; 
			 }
			 else
			 {
				$product_obj_add->status = $product_obj_add->status; 
			 }
			 
			$clientId = str_pad($newClientID, 6, '0', STR_PAD_LEFT);
            $product_obj_add->client_id = isset($client) ? $client->client_id : "";
			 $clientId_uniqueID = $clientId.$product_obj_add->client_id;
			 $ASI_id = '000'.$product_obj_add->client_id;
			 $ASI_id1 = $clientId.$product_obj_add->client_id;
			 //echo '<pre>';print_r($ASI_id);exit;   
			 $seller_Id = isset($input['sellerId']) ? $input['sellerId'] : "";
			 $seller_Id1 = '_'.$seller_Id;
			 $product_obj_add->siteNo = 'AMP'.$product_obj_add->status.'_ASI'.$ASI_id.$siteNo1; 
			
			//$product_obj_add->siteNo = $product_obj->siteNo;
			$product_obj_add->lighting = isset($input['lighting']) ? $input['lighting'] : $product_obj->lighting;;
			 $product_obj_add->cancellation = isset($input['cancellation']) ? $input['cancellation'] : $product_obj->cancellation;
			 $product_obj_add->type = isset($input['type']) ? $input['type'] : $product_obj->type;
			 $product_obj_add->title = isset($input['title']) ? $input['title'] : $product_obj->title;
			$product_obj_add->minimumbooking = isset($input['minimumbooking']) ? $input['minimumbooking'] : $product_obj->minimumbooking;
			 $product_obj_add->panelSize = (isset($input['width']) && isset($input['height'])) ? $input['height'] . '*' . $input['width'] : $product_obj->panelSize;
			 $product_obj_add->venue = isset($input['venue']) ? $input['venue'] : $product_obj->venue;
			 $product_obj_add->address = isset($input['address']) ? $input['address'] : $product_obj->address;
			 $product_obj_add->city = isset($input['city']) ? $input['city'] : $product_obj->city;
			 $product_obj_add->zipcode = isset($input['zipcode']) ? $input['zipcode'] : $product_obj->zipcode;
			 $product_obj_add->lat = isset($input['lat']) ? $input['lat'] : $product_obj->lat;
             $product_obj_add->lng = isset($input['lng']) ? $input['lng'] : $product_obj->lng;
			 $product_obj_add->direction = isset($input['direction']) ? $input['direction'] : $product_obj->direction;
			 //$product_obj->default_price = isset($input['default_price']) ? $input['default_price'] :  $product_obj->default_price;
			 $product_obj_add->default_price = isset($input['rateCard']) ? str_replace( ',', '', $input['rateCard']) : str_replace( ',', '', $product_obj->rateCard);
			 $product_obj_add->impressions = isset($input['impressions']) ? $input['impressions'] : $product_obj->impressions;
			 $product_obj_add->demographicsage = isset($input['demographicsage']) ? $input['demographicsage'] : $product_obj->demographicsage;
			 $product_obj_add->strengths = isset($input['strengths']) ? $input['strengths'] : $product_obj->strengths;
			 $product_obj_add->ethnicity = isset($input['ethnicity']) ? $input['ethnicity'] : $product_obj->ethnicity;
			 $product_obj_add->videoUrl = isset($input['videoUrl']) ? $input['videoUrl'] : $product_obj->videoUrl;
			  $product_obj_add->imgdirection = isset($input['imgdirection']) ? $input['imgdirection'] :$product_obj->imgdirection;
			 $product_obj_add->slots = isset($input['slots']) ? $input['slots'] : $product_obj->slots;
			 $product_obj_add->hour = isset($input['hour']) ? $input['hour'] : $product_obj->hour;
			 $product_obj_add->flipsloops = isset($input['flipsloops']) ? $input['flipsloops'] :  $product_obj->flipsloops;
			
			 //Editing New Fields Start
			 $product_obj_add->audited = isset($input['audited']) ? $input['audited'] : $product_obj->audited;
			 $product_obj_add->firstImpression = isset($input['firstImpression']) ? $input['firstImpression'] : $product_obj->firstImpression;
			 $product_obj_add->secondImpression = isset($input['secondImpression']) ? $input['secondImpression'] : $product_obj->secondImpression;
			 $product_obj_add->thirdImpression = isset($input['thirdImpression']) ? $input['thirdImpression'] : $product_obj->thirdImpression;
			 $product_obj_add->forthImpression = isset($input['forthImpression']) ? $input['forthImpression'] : $product_obj->forthImpression;
			 $product_obj_add->cancellation_policy = isset($input['cancellation_policy']) ? $input['cancellation_policy'] : $product_obj->cancellation_policy;
			 $product_obj_add->state = isset($input['state']) ? $input['state'] : $product_obj->state;
			 $product_obj_add->height = isset($input['height']) ? $input['height'] : $product_obj->height;
			 $product_obj_add->width = isset($input['width']) ? $input['width'] : $product_obj->width;
			 $product_obj_add->vendor = isset($input['vendor']) ? $input['vendor'] : $product_obj->vendor;
			 $product_obj_add->sellerId = isset($input['sellerId']) ? $input['sellerId'] : $product_obj->sellerId;
			 $product_obj_add->mediahhi = isset($input['mediahhi']) ? $input['mediahhi'] : $product_obj->mediahhi;
			 $product_obj_add->firstdayofpurchase = isset($input['firstdayofpurchase']) ? $input['firstdayofpurchase'] : $product_obj->firstdayofpurchase;
			 $product_obj_add->lastdayofpurchase = isset($input['lastdayofpurchase']) ? $input['lastdayofpurchase'] : $product_obj->lastdayofpurchase;
			 $product_obj_add->weekPeriod = isset($input['weekPeriod']) ? $input['weekPeriod'] : $product_obj->weekPeriod;
			 //$product_obj->rateCard = isset($input['rateCard']) ? $input['rateCard'] : $product_obj->rateCard; 
			 $product_obj_add->rateCard = isset($input['rateCard']) ? str_replace( ',', '', $input['rateCard']) : str_replace( ',', '', $product_obj->rateCard);
			 $product_obj_add->installCost = isset($input['installCost']) ? $input['installCost'] : $product_obj->installCost;
			 $product_obj_add->negotiatedCost = isset($input['negotiatedCost']) ? $input['negotiatedCost'] : $product_obj->negotiatedCost;
			 $product_obj_add->productioncost = isset($input['productioncost']) ? $input['productioncost'] : $product_obj->productioncost;
			 $product_obj_add->notes = isset($input['notes']) ? $input['notes'] : $product_obj->notes;
			 $product_obj_add->Comments = isset($input['Comments']) ? $input['Comments'] : $product_obj->Comments;
			 $product_obj_add->fliplength = isset($input['fliplength']) ? $input['fliplength'] : $product_obj->fliplength;
			 $product_obj_add->looplength = isset($input['looplength']) ? $input['looplength'] : $product_obj->looplength;
			 $product_obj_add->locationDesc = isset($input['locationDesc']) ? $input['locationDesc'] : $product_obj->locationDesc;
			 $product_obj_add->description = isset($input['description']) ? $input['description'] : $product_obj->description;
			 $product_obj_add->sound = isset($input['sound']) ? $input['sound'] : $product_obj->sound;
			 $product_obj_add->staticMotion = isset($input['staticMotion']) ? $input['staticMotion'] : $product_obj->staticMotion;
			 $product_obj_add->file_type = isset($input['file_type']) ? $input['file_type'] : $product_obj->file_type;
			 $product_obj_add->product_newAge = isset($input['product_newAge']) ? $input['product_newAge'] : $product_obj->product_newAge;
			 $product_obj_add->medium = isset($input['medium']) ? $input['medium'] : $product_obj->medium;
			 $product_obj_add->firstDay = isset($input['firstDay']) ? $input['firstDay'] : $product_obj->firstDay;
			 $product_obj_add->lastDay = isset($input['lastDay']) ? $input['lastDay'] : $product_obj->lastDay;
			 $product_obj_add->cpm = isset($input['cpm']) ? $input['cpm'] : $product_obj->cpm;
			 $product_obj_add->firstcpm = isset($input['firstcpm']) ? $input['firstcpm'] : $product_obj->firstcpm;
			 $product_obj_add->thirdcpm = isset($input['thirdcpm']) ? $input['thirdcpm'] : $product_obj->thirdcpm;
			 $product_obj_add->forthcpm = isset($input['forthcpm']) ? $input['forthcpm'] : $product_obj->forthcpm;
			 $product_obj_add->ageloopLength = isset($input['ageloopLength']) ? $input['ageloopLength'] : $product_obj->ageloopLength;
			 $product_obj_add->product_newMedia = isset($input['product_newMedia']) ? $input['product_newMedia'] : $product_obj->product_newMedia;
			 $product_obj_add->placement = isset($input['placement']) ? $input['placement'] : $product_obj->placement; 
			 $product_obj_add->spotLength = isset($input['spotLength']) ? $input['spotLength'] : $product_obj->spotLength;
			 $product_obj_add->unitQty = isset($input['unitQty']) ? $input['unitQty'] : $product_obj->unitQty;
			 $product_obj_add->billingYes = isset($input['billingYes']) ? $input['billingYes'] : $product_obj->billingYes;
			 $product_obj_add->billingNo = isset($input['billingNo']) ? $input['billingNo'] : $product_obj->billingNo;
			 $product_obj_add->servicingYes = isset($input['servicingYes']) ? $input['servicingYes'] : $product_obj->servicingYes;
			 $product_obj_add->servicingNo = isset($input['servicingNo']) ? $input['servicingNo'] : $product_obj->servicingNo; 
			 $product_obj_add->fix = isset($input['fix']) ? $input['fix'] : $product_obj->fix; 
			 $product_obj_add->minimumdays = isset($input['minimumdays']) ? $input['minimumdays'] : $product_obj->minimumdays; 
			 $product_obj_add->network = isset($input['network']) ? $input['network'] : $product_obj->network; 
			 $product_obj_add->nationloc = isset($input['nationloc']) ? $input['nationloc'] : $product_obj->nationloc; 
			 $product_obj_add->daypart = isset($input['daypart']) ? $input['daypart'] : $product_obj->daypart; 
			 $product_obj_add->genre = isset($input['genre']) ? $input['genre'] : $product_obj->genre; 
			 $product_obj_add->costperpoint = isset($input['costperpoint']) ? $input['costperpoint'] : $product_obj->costperpoint; 
			 $product_obj_add->length = isset($input['length']) ? $input['length'] : $product_obj->length; 
			 $product_obj_add->reach = isset($input['reach']) ? $input['reach'] : $product_obj->reach; 
			 $product_obj_add->daysselected = isset($input['daysselected']) ? $input['daysselected'] : $product_obj->daysselected;
             $product_obj_add->stripe_percent = isset($input['stripe_percent']) ? $input['stripe_percent'] : $product_obj->stripe_percent;  
             	
			

			 if (isset($input['dates']) && !empty($input['dates'])) {  
                foreach ($input['dates'] as $date_range) {
				$product_obj_add->from_date = iso_to_mongo_date($date_range['startDate']);
				$product_obj_add->to_date = iso_to_mongo_date($date_range['endDate']);	
					
				}
			 }
			 
			 else
			 {
				 //if dates exists in record then same dates should be saved in new record //start
				
			 $product = Product::where('id', '=', $this->input['id'])->first();
			  //$product[0]->id;
			  $product->id;
			  //$product_id = $product[0]->id;  
			  $product_id = $product->id;
			  //echo '<pre>';print_r($product_id);exit;  
			  //echo '<pre>';print_r($product);exit; 
				if(!empty($product)){
				if (isset($product_id)) {
					
						//echo '<pre>in if'; print_r($input); exit;  
						$product_edit = Product::where('id', '=', $product_id)->first();
						$product_obj_add->from_date = $product_edit->from_date;
						$product_obj_add->to_date = $product_edit->to_date;
						$product_edit->save();
					}
				}
			 //End
			 }
				
			 
			 if(isset($input['area'])){
			 $area = Area::where('id', '=', $input['area'])->first();
			 }else{
				 $area = Area::where('id', '=', $product_obj_add->area)->first();
			 }
			 $product_obj_add->area = isset($input['area']) ? $input['area'] : $product_obj->area;
			  $product_obj_add->buses = isset($input['buses']) ? $input['buses'] : $product_obj->buses;
			 $product_obj_add->country_name = isset($area) ? $area->country_name : $product_obj->country_name;
             $product_obj_add->state_name = isset($area) ? $area->state_name : $product_obj->state_name;
             $product_obj_add->city_name = isset($area) ? $area->city_name : $product_obj->city_name;
             $product_obj_add->area_name = isset($area) ? $area->name : $product_obj->area_name;
             $product_obj_add->status = Product::$PRODUCT_STATUS['approved'];
             $product_obj_add->client_mongo_id = $product_obj->client_mongo_id;
             $product_obj_add->client_name = $product_obj->client_name;
			 $product_obj_add->product_visibility = $product_obj->product_visibility;
			  
             $product_obj_add->image = $product_obj->image;
            if ($this->request->hasFile('image')) {
                foreach ($this->request->file('image') as $key => $val) {
                    $imageNmae = \Carbon\Carbon::now()->timestamp . $val->getClientOriginalName();
                    if ($val->move($product_img_path, $imageNmae)) {
                        $imageArray[] = "/uploads/images/products/" . $imageNmae;
                    }
                }
                $product_obj_add->image = $imageArray;
            }
				$product_obj_add->symbol = $product_obj->symbol;
			 if ($request->hasFile('symbol')) {
                if ($request->file('symbol')->move($product_symbol_path, $request->file('symbol')->getClientOriginalName())) {
                    $product_obj_add->symbol = "/uploads/images/symbols/" . $request->file('symbol')->getClientOriginalName();
                }
            }
			$symbolPath='';
			if(isset($input['imgdirection'])){
				if($input['imgdirection'] == 'Left'){
				$symbolPath = "/uploads/images/symbols/".$product_obj_add->type."-left.png";
					
				}
			if($input['imgdirection'] == 'Right'){
				$symbolPath = "/uploads/images/symbols/".$product_obj_add->type."-right.png";
				}
				$product_obj_add->symbol = $symbolPath;
			}
			
			
				// if($product_obj->imgdirection == 'Left'){
				// }
			 // if($product_obj->imgdirection == 'Right'){
				// }
           //echo '<pre>in if'; print_r($product_obj); exit;
			
            if ($product_obj_add->save()) {
                // Insert data to elasticsearch :: Pankaj 17 Nov 2021    
                $get_data = Product::where('id', '=', $product_obj_add->id)->first();
                $this->es_etl($get_data, "insert");
				   $success = true;
				    if (isset($input['dates']) && !empty($input['dates'])) {
               
                foreach ($input['dates'] as $date_range) {
                    $booking = new ProductBooking;
                    $booking->product_id = $product_obj_add->id;
                    $booking->booked_from = iso_to_mongo_date($date_range['startDate']);
                    $booking->booked_to = iso_to_mongo_date($date_range['endDate']);

                    $booking->product_owner = $product_obj_add->client_mongo_id;
					if($product_obj_add->slots!=''){
						$booking->booked_slots = $product_obj_add->slots;
					}
                    $booking->product_status = ProductBooking::$PRODUCT_STATUS['scheduled'];
                    if (!$booking->save()) {
                        $success = false;
                    }
                }
			}

            /*if(isset($input['stripe_percent']) && ($input['stripe_percent']!= $product_obj->stripe_percent)){
                //mail functionality 
             }*/   
               
				if($success == true){
                return response()->json(["status" => "1", "message" => "Product Cloned Successfully."]);
			}
            } else {
                return response()->json(["status" => "0", "message" => "Failed to Clone Product."]);
            }
// handling editing of product ends
        }
  
}


// get available quantity after selecting dates implementation of quantity
public function  getProductAvailabilityQuantity($product_id = '',$from_date = '',$to_date = ''){
	$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
	if($product_id == ''|| $from_date == ''|| $to_date == ''){
		return response()->json(["status" => "0", "message" => "Mandatory parameters missing."]);
	}

	$product = Product::select("unitQty")->where('id', '=', $product_id)->first();
	$productBooked = ProductBooking::where('product_id', '=',  $product_id)->where('quantity', '!=',  '')->get()->toArray();

	$available_quantity = $product->unitQty;
	if(!empty($productBooked)){
		
		$productBooked_last = ProductBooking::select("quantity")->where('product_id', '=',  $product_id)->where('booked_from','<=',iso_to_mongo_date($to_date))->where('booked_to','>=',iso_to_mongo_date($from_date))->where('product_status','!=',100)->where('product_status','!=',400)->where('quantity', '!=',  '')->groupBy('group_slot_id')->get()->toArray();
		
		$sum_quantity = 0;
		if(!empty($productBooked_last)){
			foreach($productBooked_last as $key => $value){
				$sum_quantity += $value['quantity'];
			}
		}
		$available_quantity = $product->unitQty-$sum_quantity;
		if($available_quantity >= 0){
			$available_quantity = $available_quantity;
		}else{
			$available_quantity = 0;
		}
	}
	return response()->json($available_quantity);
}
  
	  
}

