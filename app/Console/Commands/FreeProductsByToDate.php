<?php 

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use App\Models\CampaignProduct;
use Log;

class FreeProductsByToDate extends Command {
    
  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'product_availibility:refresh';
  
  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = "changes the status of products from locked to free after their duration in a campaign is over.";

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
      $camp_products = CampaignProduct::where('product_status', '=', CampaignProduct::$PRODUCT_STATUS['locked'])->get();
      $today = new DateTime();
      foreach($camp_products as $camp_product){
        $product_release_date = new DateTime($camp_product->to_date);
        if($today > $product_release_date){
          $camp_product->product_status = CampaignProduct::$PRODUCT_STATUS['free'];
          $camp_product->save();
        }
      }
    }
    catch(Exception $ex){
        Log::error($ex);
    }
  }
}