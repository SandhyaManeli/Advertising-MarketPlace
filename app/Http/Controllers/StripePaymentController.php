<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Mail;
use Illumninate\support\Collection;
use App\Models\Product;
use App\Models\Campaign;
use App\Models\CampaignSuggestionRequest;
use App\Models\Format;
use App\Models\Area;
use App\Models\Client;
use App\Stripe\Stripe;
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
use App\Models\DeleteProduct;
//conflicts fixing on 12-Apr-2022
use App\Models\MakeOffer;
use JWTAuth;
use Auth;
use Entrust;
use PDF;
use App\Jobs\UpdateProductEverywhere;
use Log;
use App\Events\ProductApprovedEvent;
use App\Events\ProductRequestedEvent;
use DB;
//use Session;
//use Stripe\Stripe;
//use Stripe as StripePayment;
//use Stripe\Error\Card;
//use Cartalyst\Stripe\Stripe;
//use App\stripe\stripe-php\lib\Stripe.php;
//use App\stripe\stripe-php\lib\Stripe.php;
use Stripe as StripePayment;
use App\Models\StripePayments;
use App\Helpers\QuickbooksCustomerHelper;


class StripePaymentController extends Controller
{
  
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function stripePayment(Request $request)
    {		
	//echo 'req';print_r($request);exit;
	
	if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all(); 
        }
		
		//echo 'input--';print_r($input['payable_amount']);exit;
		//echo 'input--';print_r($input['result']['token']['id']);exit;
		//echo 'input--';print_r($input['token']['id']);exit;
		//echo 'req';print_r($request->input ('name'));exit;
		//echo 'dsds';exit;
		//echo "";print_r($stripepayment);exit;
		/*$stripeToken =  \Stripe\Token::create(array(
                       "card" => array(
                           "number"    =>  $request->input ('cardNumber'),
                           "exp_month" => $request->input ('expMonth'),
                           "exp_year"  => $request->input ('expYear'),
                           "cvc"       =>  $request->input  ('cvvNumber'),
                           "name"      =>  $request->input ('name')
                       )
                   ));*/
			//$stripepayment = \Stripe\Stripe::setApiKey ( 'sk_test_DMqyt20idfn80aqzcIBccPYY00r1SpQzZ2' );
			$stripepayment = \Stripe\Stripe::setApiKey ( 'sk_test_DMqyt20idfn80aqzcIBccPYY00r1SpQzZ2' );
			//$stripepayment = \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
			//$stripepayment = Stripe::setApiKey ( 'sk_test_DMqyt20idfn80aqzcIBccPYY00r1SpQzZ2' );
			// $stripeToken =  \Stripe\Token::create(array(
                       // "card" => array(
                           // "number"    =>  $request->input ('cardNumber'),
                           // "exp_month" => $request->input ('expMonth'),
                           // "exp_year"  => $request->input ('expYear'),
                           // "cvc"       =>  $request->input  ('cvvNumber'),
                           // "name"      =>  $request->input ('name')
                       // )
                   // ));
			//echo "<pre>stripepayment";print_r($stripepayment);exit;
			//echo "<pre>stripeToken";print_r($stripeToken);exit;
			//Stripe::setApiKey ( 'sk_test_DMqyt20idfn80aqzcIBccPYY00r1SpQzZ2' );
			// $token = \Stripe\Token::create(array(
  // "card" => array(
    // "number" => "4242424242424242",
    // "exp_month" => 12,
    // "exp_year" => 2022,
    // "cvc" => "311"
  // )
// // ));

// $Token = \Stripe\Token::create ( array (
		// //"card" => array(
				// "number" => '4242424242424242',
				// "exp_month" => "12",
				// "exp_year" => '2022', // obtained with Stripe.js
				// //'source' => $token,
				// "cvc" => "123." 
		// //)
		// ) );
			 //echo "<pre>stripeToken";print_r($stripepayment);//exit;			 
			 
	try {
		//echo 'exx';exit;
		/*$Charge = \Stripe\Charge::create ( array (
		//Charge::create ( array (
				"amount" => $input['payable_amount'] * 100,
				//"amount" => 1000 * 100,
				"currency" => "usd",				//"source" => $request->input ( 'stripeToken' ), // obtained with Stripe.js
				"source" => $input['result']['token']['id'], // obtained with Stripe.js
				//'source' => $token,
				"description" => "Test Payment." 
		) );*/	
		/*$amount = $input['payable_amount'];
		$amount = $amount * 100;
		$amount = $amount * uc_stripe_get_currency_multiplier();*/
		//$StripePayment = new StripePayment;
		//echo "<pre>Charge";print_r($StripePayment);exit;
		
		/* Save Payment Information in CampaignPayment table */
		$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
		
        //$campaign_payment = new CampaignPayment;
        //$campaign_payment->campaign_id = isset($input['campaignId']) ? $input['campaignId'] : "";
        //$campaign_payment->amount = isset($input['payable_amount']) ?  $input['payable_amount'] : "";
        //$campaign_payment->type = isset($input['type']) ? $input['type'] : "";
        //$campaign_payment->reference_no = isset($input['reference_no']) ? $input['reference_no'] : "";
        //$campaign_payment->received_by = isset($input['received_by']) ? $input['received_by'] : "";
        //$campaign_payment->updated_by_id = $user_mongo['id'];
        //$campaign_payment->updated_by_name = $user_mongo['first_name'] . " " . $user_mongo['last_name'];
        //$campaign_payment->client_mongo_id = $user_mongo['client_mongo_id'];
		//$campaign_payment->comment = isset($input['comment']) ? $input['comment'] : "";
        //$payment_img_path = base_path() . '/html/uploads/images/campaign_payments';
        /*if ($this->request->hasFile('image')) {
            if ($this->request->file('image')->move($payment_img_path, $this->request->file('image')->getClientOriginalName())) {
                $campaign_payment->image = "/uploads/images/campaign_payments/" . $this->request->file('image')->getClientOriginalName();
            }
        }*/
		
		
		//$campaign_payment->save();
        // if ($campaign_payment->save()) {
            // return response()->json(["status" => "1", "message" => "Campaign payment updated successfully."]);
        // } else {
            // return response()->json(["status" => "0", "message" => "There was a technical error while updating the payment. Please try again later."]);
        // }
		/* Save Payment Information in CampaignPayment table */
		
		
		$res =	\Stripe\Charge::create ( array (
				//"amount" => 300 * 100,
				"amount" => ($input['payable_amount']) * 100,
				//"amount" => 15*100,
				"currency" => "usd",
				"source" => $input['result']['token']['id'], // obtained with Stripe.js
				"description" => "Test payment." 
		) );
		//echo "<pre>Charge";print_r($res);exit;
		$resp_obj = array(
			'ch_id'=>$res['id'],
			'object'=>$res['object'],
			'amount'=>$res['amount']/100,
			'amount_refunded'=>$res['amount_refunded'],
			'application_fee'=>$res['application_fee'],
			'application_fee_amount'=>$res['application_fee_amount'],
			'billing_details'=>$res['billing_details'],
			'calculated_statement_descriptor'=>$res['calculated_statement_descriptor'],
			'created'=>$res['created'],
			'currency'=>$res['currency'],
			'customer'=>$res['customer'],
			'description'=>$res['description'],
			'payment_intent'=>$res['payment_intent'],
			'payment_method'=>$res['payment_method'],
			'payment_method_details'=>$res['payment_method_details'],
			'receipt_email'=>$res['receipt_email'],
			'receipt_number'=>$res['receipt_number'],
			'receipt_url'=>$res['receipt_url'],
			'refunded'=>$res['refunded'],
			'refunds'=>$res['refunds'],
			'review'=>$res['review'],
			'shipping'=>$res['shipping'],
			'source'=>$res['source'],
			'source_transfer'=>$res['source_transfer'],
			'statement_descriptor'=>$res['statement_descriptor'],
			'statement_descriptor_suffix'=>$res['statement_descriptor_suffix'],
			'status'=>$res['status'],
			'transfer_data'=>$res['transfer_data'],
			'transfer_group'=>$res['transfer_group']
		);
		$stripe_payment = new StripePayments;
		$stripe_payment->id = uniqid();
		$stripe_payment->ch_id = $res['id'];
		$stripe_payment->campaign_id = $input['campaignId'];
		$stripe_payment->objectval = $res['object'];
		$stripe_payment->amount = $res['amount']/100;
		$stripe_payment->amount_refunded = $res['amount_refunded'];
		$stripe_payment->application_fee = $res['application_fee'];
		$stripe_payment->application_fee_amount = $res['application_fee_amount'];
		$stripe_payment->billing_details = $res['billing_details'];
		$stripe_payment->calculated_statement_descriptor = $res['calculated_statement_descriptor'];
		$stripe_payment->created = $res['created'];
		$stripe_payment->currency = $res['currency'];
		$stripe_payment->customer = $res['customer'];
		$stripe_payment->description = $res['description'];
		$stripe_payment->payment_intent = $res['payment_intent'];
		$stripe_payment->payment_method = $res['payment_method'];
		$stripe_payment->payment_method_details = $res['payment_method_details'];
		$stripe_payment->receipt_email = $res['receipt_email'];
		$stripe_payment->receipt_number = $res['receipt_number'];
		$stripe_payment->receipt_url = $res['receipt_url'];
		$stripe_payment->refunded = $res['refunded'];
		$stripe_payment->refunds = $res['refunds'];
		$stripe_payment->review = $res['review'];
		$stripe_payment->shipping = $res['shipping'];
		$stripe_payment->source = $res['source'];
		$stripe_payment->source_transfer = $res['source_transfer'];
		$stripe_payment->statement_descriptor = $res['statement_descriptor'];
		$stripe_payment->statement_descriptor_suffix = $res['statement_descriptor_suffix'];
		$stripe_payment->status = $res['status'];
		$stripe_payment->transfer_data = $res['transfer_data'];
		$stripe_payment->transfer_group = $res['transfer_group'];
		$stripe_payment->brand = $input['result']['token']['card']['brand'];
		$stripe_payment->received_by = $input['result']['token']['card']['name'];
		//echo "<pre>Charge";print_r($resp_obj);exit;
		if($stripe_payment->save()){
			$campaign_payment = new CampaignPayment;
			$campaign_payment->campaign_id = isset($input['campaignId']) ? $input['campaignId'] : "";
			$campaign_payment->amount = isset($input['payable_amount']) ?  $input['payable_amount'] : "";
			$campaign_payment->updated_by_id = $user_mongo['id'];
			$campaign_payment->updated_by_name = $user_mongo['first_name'] . " " . $user_mongo['last_name'];
			if($campaign_payment->save()){
				$campaign_products = ProductBooking::where('campaign_id', '=', $input['campaignId'])->get();
				$invoice_data_arr = array();
				foreach ($campaign_products as $campaign_product) {
					$product_data = Product::select("siteNo","title","rateCard","unitQty","tax_percentage")->where('id', '=', $campaign_product->product_id)->first();
					$tax_percentage_f = 0;
					if(isset($product_data->tax_percentage)){
						$tax_percentage_f = $product_data->tax_percentage;
					}
					$campaign_product->tax_percentage = $tax_percentage_f;
					$campaign_product->save();
					
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
							"UnitPrice" => $campaign_product->price
						),
						"Description" => '"'.$product_data['title'].'"'
					);
				} 
				$invoice_data = '"Line": 
						'.json_encode($invoice_data_arr).'
					  ';	  
				$products_data = QuickbooksCustomerHelper::getMainMethod($invoice_data);
			}
		}
		//Session::flash ( 'success-message', 'Payment done successfully !' );
		return response()->json(["status" => "1", 'response_obj'=>$resp_obj, "message" => "Payment Done Successfully !"]);
		
		//return Redirect::back ();
		//return back ();
	} catch ( \Exception $e ) {
		//Session::flash ( 'fail-message', "Error! Please Try again." );
		//return Redirect::back ();
		//echo "<pre>Exception";print_r($e);exit;
		return response()->json(["message" => "Failed to do payment. Please try again."]);
	}
	
    }
	
	public function stripeRefund(Request $request)
    {
		if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all(); 
        }
		//echo 'input--';print_r($input);exit;
		try {
			$stripepayment = \Stripe\Stripe::setApiKey ('sk_test_DMqyt20idfn80aqzcIBccPYY00r1SpQzZ2');
			//echo $input['campaign_id'];exit;
			$strp_campaign = StripePayments::where([
							['campaign_id', '=', $input['campaign_id']],
						])->first();
						
			$campaign_payments = CampaignPayment::where([
				['campaign_id', '=', $input['campaign_id']],
			])->first();
			
			$campaigns = Campaign::where([
				['id', '=', $input['campaign_id']],
			])->first();
			$ch_id = $strp_campaign['ch_id'];
			$strp_campaign_id = $strp_campaign['campaign_id'];
			$campaign_payments_campaign_id = $campaign_payments['campaign_id'];
			$campaigns_campaign_id = $campaigns['id'];
			$amount = $input['pric'];
			//echo 'input--s';print_r($input);exit;
			// $refund = \Stripe\Refund::create([
				// 'charge' => $ch_id,
				// 'amount' => $amount,  // For 10 $
				// 'reason' => 'refund'
			// ]);
			$stripe = new \Stripe\StripeClient(
					  'sk_test_DMqyt20idfn80aqzcIBccPYY00r1SpQzZ2'
					);
			$res = $stripe->refunds->create([
			  'charge' => $ch_id,
			  'amount' => $amount * 100,  // For 10 $
			   //'reason' => 'refund'
			]);
			
			$resp_obj = array(
							'refund_id'=>$res['id'],
							'object'=>$res['object'],
							'amount'=>$res['amount']/100,
							'balance_transaction'=>$res['balance_transaction'],
							'charge'=>$res['charge'],
							'created'=>$res['created'],
							'currency'=>$res['currency'],
							'payment_intent'=>$res['payment_intent'],
							'reason'=>$res['reason'],
							'receipt_number'=>$res['receipt_number'],
							'source_transfer_reversal'=>$res['source_transfer_reversal'],
							'status'=>$res['status'],
							'transfer_reversal'=>$res['transfer_reversal'],
						);
			if (isset($strp_campaign_id)) {
				$stripe_obj = StripePayments::where('campaign_id', '=', $strp_campaign_id)->first();
				$stripe_obj->refund_id = $res['id'];
				$stripe_obj->refund_amount = $res['amount']/100;
				$stripe_obj->refund_balance_transaction = $res['balance_transaction'];
				$stripe_obj->refund_charge_id = $res['charge'];
				$stripe_obj->refund_created = $res['created'];
				$stripe_obj->refund_currency = $res['currency'];
				$stripe_obj->refund_payment_intent = $res['payment_intent'];
				$stripe_obj->refund_reason = $res['reason'];
				$stripe_obj->refund_receipt_number = $res['receipt_number'];
				$stripe_obj->refund_source_transfer_reversal = $res['source_transfer_reversal'];
				$stripe_obj->refund_status = $res['status'];
				$stripe_obj->refund_transfer_reversal = $res['transfer_reversal'];
				$stripe_obj->save();
			}
			
			if (isset($campaign_payments_campaign_id)) {
				$campaign_payments_obj = CampaignPayment::where('campaign_id', '=', $campaign_payments_campaign_id)->first();
				$campaign_payments_obj->refunded_amount = $res['amount']/100;
				$campaign_payments_obj->bal_amount_available_with_amp = ($campaign_payments_obj->amount)-($campaign_payments_obj->refunded_amount);
				$campaign_payments_obj->save();
			}
			
			if (isset($campaigns_campaign_id)) {
				$campaigns_obj = Campaign::where('id', '=', $campaigns_campaign_id)->first();
				$campaigns_obj->prev_status = $campaigns_obj->status;
				$campaigns_obj->status = Campaign::$CAMPAIGN_STATUS['deleted-cancelled'];
				$campaigns_obj->save();
			}
			//return response()->json(["status" => "1","campaign_id" => $strp_campaign_id, 'response_obj'=>$resp_obj, "message" => "Refund Successful !"]);
			$user = JWTAuth::parseToken()->getPayload()['userMongo'];
			$mail_tmpl_params = [
                'sender_email' => $user['email'],
                'receiver_name' => 'Richard',
				'mail_message' => 'Refund Successfully..'
            ];
			//echo '<pre>mail_tmpl_params'; print_r($mail_tmpl_params);exit;        
            $mail_data = [
                //'email_to' => $this->input['email'],  
               //'email_to' => 'info@advertisingmarketplace.com',
                //'email_to' => 'deekshitha.bhupathi@peopletech.com',
                'email_to' => 'sandhyarani.manelli@peopletech.com',
				'recipient_name' => 'Richard'
                //'email_to' => $user['email'] 
            ];
			//echo '<pre>mail_data'; print_r($mail_data);exit;  
            Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data) {
                $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Payment Refund-Advertising Marketplace!');
            });
			//echo '<pre>Mail'; print_r($Mail);exit; 
			
			//echo 'stripe';print_r($res);exit;
			if (!Mail::failures()) {
					return response()->json(["status" => "1","campaign_id" => $strp_campaign_id, 'response_obj'=>$resp_obj, "message" => "Refund Successful !"]);
			} else {
					
					return response()->json(['status' => 0, 'message' => "There was an error sending the request. Please try again."]);
            }
		} catch ( \Exception $e ) {
		//return Redirect::back ();
		//echo "<pre>Exception";print_r($e);exit;
		return response()->json(["message" => "Failed to do payment. Please try again."]);
	}
		return response()->json(["status" => "1", "message" => "Stripe Refund Service Called Successfully !"]);
	}
	 
	public function stripeRefundForDeletedProduct(Request $request)
    {
		if ($request->isJson()) {
            $input = $request->json()->all();
        } else {
            $input = $request->all(); 
        }
		$productids = $input['product_id'];
		try {
			$stripepayment = \Stripe\Stripe::setApiKey ('sk_test_DMqyt20idfn80aqzcIBccPYY00r1SpQzZ2');
			//echo $input['campaign_id'];exit;
			//echo '<pre>';print_r($stripepayment);exit;		
			$strp_campaign = StripePayments::where([
							['campaign_id', '=', $input['campaign_id']],
						])->first();		
			$campaign_payments = CampaignPayment::where([
				['campaign_id', '=', $input['campaign_id']],
			])->first();
			//echo '<pre>';print_r($campaign_payments);exit;	
			$campaigns = Campaign::where([
				['id', '=', $input['campaign_id']],
			])->first();
			//echo '<pre>';print_r($campaigns);exit;
			$ch_id = $strp_campaign['ch_id'];
			$strp_campaign_id = $strp_campaign['campaign_id'];
			$campaign_payments_campaign_id = $campaign_payments['campaign_id'];
			$campaigns_campaign_id = $campaigns['id'];
			$amount = $input['price'];
			//echo 'input--s';print_r($input);exit;
			// $refund = \Stripe\Refund::create([
				// 'charge' => $ch_id,
				// 'amount' => $amount,  // For 10 $
				// 'reason' => 'refund'
			// ]);
			$stripe = new \Stripe\StripeClient(
					  'sk_test_DMqyt20idfn80aqzcIBccPYY00r1SpQzZ2'
					);
			$res = $stripe->refunds->create([
			  'charge' => $ch_id,
			  'amount' => $amount * 100,  // For 10 $
			   //'reason' => 'refund'
			]);
			$resp_obj = array(
							'refund_id'=>$res['id'],
							'object'=>$res['object'],
							'amount'=>$res['amount']/100,
							'balance_transaction'=>$res['balance_transaction'],
							'charge'=>$res['charge'],
							'created'=>$res['created'],
							'currency'=>$res['currency'],
							'payment_intent'=>$res['payment_intent'],
							'reason'=>$res['reason'],
							'receipt_number'=>$res['receipt_number'],
							'source_transfer_reversal'=>$res['source_transfer_reversal'],
							'status'=>$res['status'],
							'transfer_reversal'=>$res['transfer_reversal'],
						);
			if (isset($strp_campaign_id)) {
				$stripe_obj = StripePayments::where('campaign_id', '=', $strp_campaign_id)->first();
				$stripe_obj->refund_id = $res['id'];
				$stripe_obj->refund_amount = $res['amount']/100;
				$stripe_obj->refund_balance_transaction = $res['balance_transaction'];
				$stripe_obj->refund_charge_id = $res['charge'];
				$stripe_obj->refund_created = $res['created'];
				$stripe_obj->refund_currency = $res['currency'];
				$stripe_obj->refund_payment_intent = $res['payment_intent'];
				$stripe_obj->refund_reason = $res['reason'];
				$stripe_obj->refund_receipt_number = $res['receipt_number'];
				$stripe_obj->refund_source_transfer_reversal = $res['source_transfer_reversal'];
				$stripe_obj->refund_status = $res['status'];
				$stripe_obj->refund_transfer_reversal = $res['transfer_reversal'];
				$stripe_obj->save();
			}
			
			if (isset($campaign_payments_campaign_id)) {
				$campaign_payments_obj = CampaignPayment::where('campaign_id', '=', $campaign_payments_campaign_id)->first();
				if(isset($campaign_payments_obj->refunded_amount) && !empty($campaign_payments_obj->refunded_amount)){
					$campaign_payments_obj->refunded_amount = $campaign_payments_obj->refunded_amount + $res['amount']/100;
				}else{
					$campaign_payments_obj->refunded_amount = $res['amount']/100;
				}
				
				if(isset($campaign_payments_obj->bal_amount_available_with_amp) && !empty($campaign_payments_obj->bal_amount_available_with_amp)){
					$campaign_payments_obj->bal_amount_available_with_amp = ($campaign_payments_obj->bal_amount_available_with_amp)-($campaign_payments_obj->refunded_amount);
				}else{
					$campaign_payments_obj->bal_amount_available_with_amp = ($campaign_payments_obj->amount)-($campaign_payments_obj->refunded_amount);
				}
				
				$campaign_payments_obj->save();
			}
			
			 
			if (isset($campaigns_campaign_id)) {
				//$campaigns_obj = Campaign::where('id', '=', $campaigns_campaign_id)->first();
				/*$product_obj = new DeleteProduct;
				$product_obj->comments = isset($input['comments']) ? $input['comments'] : $product_obj->comments;
				$product_obj->campaign_id = isset($input['campaign_id']) ? $input['campaign_id'] : $product_obj->campaign_id;
				$product_obj->product_id = isset($input['product_id']) ? $input['product_id'] : $product_obj->product_id;
				$product_obj->loggedinUser = isset($this->input['loggedinUser']) ? $this->input['loggedinUser'] : "";
				$product_obj->price = isset($input['price']) ? $input['price'] : $product_obj->price;
				$product_obj->status = DeleteProduct::$PRODUCT_STATUS['confirm-delete-product-from-campaign'];
				$product_obj->id = uniqid(); 
				$product_obj->save(); */
				
				 if (isset($input['campaign_id'])) {
					//echo '<pre>in if'; print_r($input); exit;
					$product_obj = DeleteProduct::where('campaign_id', '=', $input['campaign_id'])->first();
					$product_obj->status = DeleteProduct::$PRODUCT_STATUS['confirm-delete-product-from-campaign'];
					$product_obj->save();
				 }
				
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
						$productbooking_obj->product_status = ProductBooking::$PRODUCT_STATUS['delete-accepted'];
						//echo 'psroductbooking_obj';print_r($productbooking_obj);exit;
						$productbooking_obj->save();
					}
				}*/
				
				// if(is_array($productids)){
				// 	$deleteproducts = ProductBooking::where([
				// 		['campaign_id', '=', $input['campaign_id']],
				// 	])
				// 	->whereIn('product_id', $productids)
				// 	->delete();
				// 	//echo "<pre>";print_r($getproducts); 
				// 	//$campaigns_obj->prev_status = $campaigns_obj->status;
				// 	//$campaigns_obj->status = Campaign::$CAMPAIGN_STATUS['deleted-cancelled'];
				// 	//$campaigns_obj->save();
				// }
			}
			 
			// if (isset($campaigns_campaign_id)) {
			// 	$campaigns_obj = Campaign::where('id', '=', $campaigns_campaign_id)->first();
			// 	//echo '<pre>';print_r(campaigns_obj);exit;
			// 	$campaigns_obj->prev_status = $campaigns_obj->status;
			// 	$campaigns_obj->status = Campaign::$CAMPAIGN_STATUS['deleted-cancelled']; 
			// 	$campaigns_obj->save();
			// }
			
			//return response()->json(["status" => "1","campaign_id" => $strp_campaign_id, 'response_obj'=>$resp_obj, "message" => "Refund Successful !"]);
			
			$campaign_obj = Campaign::where([
                    ['id', '=', $product_obj->campaign_id],
                ])->first();
				
			$campaign_user_mongo = UserMongo::where('id', '=', $campaign_obj->created_by)->first();
			
			$user = JWTAuth::parseToken()->getPayload()['userMongo'];
			$mail_tmpl_params = [
                'sender_email' => $user['email'],
                'receiver_name' => '',
				'mail_message' => 'Refund Successfully..'
            ];
			//echo '<pre>mail_tmpl_params'; print_r($mail_tmpl_params);exit;         
            $mail_data = [
                //'email_to' => $this->input['email'],  
               //'email_to' => 'info@advertisingmarketplace.com', 
                //'email_to' => 'deekshitha.bhupathi@peopletech.com',
                //'email_to' => 'sandhyarani.manelli@peopletech.com', 
                'email_to' => $campaign_user_mongo->email,
				'recipient_name' => ''
                //'email_to' => $user['email'] 
            ];
			//echo '<pre>mail_data'; print_r($mail_data);exit;  
            Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data) {
                $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Payment Refund-Advertising Marketplace!');
            });
			//echo '<pre>Mail'; print_r($Mail);exit; 
			
			//echo 'stripe';print_r($res);exit; 
			if (!Mail::failures()) {
					return response()->json(["status" => "1","campaign_id" => $strp_campaign_id, 'response_obj'=>$resp_obj, "message" => "Refund Successful !"]);
			} else {
					
					return response()->json(['status' => 0, 'message' => "There was an error sending the request. Please try again."]);
            }
		} catch ( \Exception $e ) {
		//return Redirect::back ();
		//echo "<pre>Exception";print_r($e);exit;
		return response()->json(["message" => "Failed to do payment. Please try again."]);
	}
		return response()->json(["status" => "1", "message" => "Stripe Refund Service Called Successfully !"]);
	}
}

