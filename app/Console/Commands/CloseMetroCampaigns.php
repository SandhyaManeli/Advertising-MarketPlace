<?php 

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Campaign;
use App\Models\CampaignProduct;
use App\Models\Format;
use Log;

class CloseMetroCampaigns extends Command {
    
  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'CloseMetroCampaigns:command';
  
  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = "Close metro campaigns if it meets to end  date";

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
    //Log::info("Close Metro Camapins command has started running");
    try{
    	$campaign = Campaign::where([
        ['status', '=', Campaign::$CAMPAIGN_STATUS['metro-campaign-running']],
        ['format_type', '=', Format::$FORMAT_TYPE['metro']]
      ])->get();
      foreach ($campaign as $cam){
         $campaign_products = CampaignProduct::where('campaign_id', '=', $cam->id)->count();
         $campaign_close_count = CampaignProduct::where('campaign_id', '=', $cam->id)->where('active_status', '=', 'closed' )->count();
         if($campaign_products <= $campaign_close_count){
          $campaign_update = Campaign::where('id', '=', $cam->id)->get();
          $campaign_update->status = Campaign::$CAMPAIGN_STATUS['metro-campaign-stopped'];
              $campaign_update->save();
         }
      }
      //Log::info("Close Metro Camapins command has finished running");
    }
    catch(Exception $ex){
        Log::error($ex);
    }
  }
}