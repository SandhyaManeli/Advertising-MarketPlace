<?php 
namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Campaign;
use App\Models\ProductBooking;
use App\Models\Format;
use Carbon\Carbon;
use DateTime;
use Log;

class RunningCampaignProducts extends Command {
    
  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'run_campaign_product:command';
  
  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = "Close metro campaigns products if it meets to end  date";

  private $dev_permissions;
  // private $basic_user_permissions;

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
    Log::info("dfdfsdf Close Metro Camapins command has started running1");
    try{
      $camp_products = ProductBooking::where('product_status', '=', ProductBooking::$PRODUCT_STATUS['scheduled'])->orWhere('product_status', '=', ProductBooking::$PRODUCT_STATUS['running'])->get();
      Log::info(count($camp_products));
	  $now = Carbon::now();
	  Log::info($now);
      $i = 1;
      foreach($camp_products as $camp_product){
        $startdate =Carbon::parse($camp_product->booked_from);
        $enddate = Carbon::parse($camp_product->booked_to);
        Log::info($startdate);
        Log::info($enddate);
        Log::info($now);
		Log::info($camp_product->campaign_id);
        if($startdate <= $now && $now <= $enddate) {
          Log::info("runningupdate");
          $camp_product->product_status = ProductBooking::$PRODUCT_STATUS['running'];
          $camp_product->save();
          $campaign = Campaign::where('status', '=', Campaign::$CAMPAIGN_STATUS['scheduled'])->where('id', '=',  $camp_product->campaign_id)->first();
		    Log::info($campaign);
		  if(!empty($campaign)){
          $campaign->status = Campaign::$CAMPAIGN_STATUS['running'];
          $campaign->save();
		  }
        }elseif($now > $enddate){
			   Log::info("endUpdate");
          $camp_product->product_status = ProductBooking::$PRODUCT_STATUS['completed'];
          $camp_product->save();
          $campaign = Campaign::where('status', '=', Campaign::$CAMPAIGN_STATUS['running'])->where('id', '=',  $camp_product->campaign_id)->first();
		  Log::info($campaign);
		  if(!empty($campaign)){
          $campaign->status = Campaign::$CAMPAIGN_STATUS['stopped'];
          $campaign->save();
		  }
        }
        else{
          Log::info("no");
        }
        if( $camp_product->product_status == 'completed' || $camp_product->product_status == 'deleted'){
          ++$i;
        }
        if(count((array)$camp_product)  == $i ){
          $campaign = Campaign::where('id', '=',  $camp_product->campaign_id)->where('status', '=', Campaign::$CAMPAIGN_STATUS['running'])->orWhere('status', '=', Campaign::$PRODUCT_STATUS['scheduled'])->first();
          $campaign->status = Campaign::$CAMPAIGN_STATUS['stopped'];
          $campaign->save();
        }
      }
    }
    catch(Exception $ex){
        Log::error($ex);
    }
  }
}