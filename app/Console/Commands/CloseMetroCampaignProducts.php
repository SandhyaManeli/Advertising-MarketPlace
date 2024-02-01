<?php 

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Campaign;
use App\Models\CampaignProduct;
use App\Models\Format;
use Log;

class CloseMetroCampaignProducts extends Command {
    
  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'CloseMetroCampaignProducts:command';
  
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
    //Log::info("Close Metro Camapins command has started running1");
    try{
        // create permission list
        $campaign = Campaign::where([
          ['status', '=', Campaign::$CAMPAIGN_STATUS['metro-campaign-running']],
          ['format_type', '=', Format::$FORMAT_TYPE['metro']]
        ])->get();
        foreach ($campaign as $cam){
          $campaign_products = CampaignProduct::where('campaign_id', '=', $cam->id)->where('end_date', '<', date("Y-m-d"))->get();
          foreach($campaign_products as $cam_pro){
            $campaign_products->active_status = 'closed';
                $campaign_products->save();
          }
          //Log::info("Close Metro Camapins command has started running111");
        }
    }
    catch(Exception $ex){
        Log::error($ex);
    }
  }
}