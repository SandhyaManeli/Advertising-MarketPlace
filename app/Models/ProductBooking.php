<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;
use Log;

class ProductBooking extends Moloquent{

  /*
  * ================
  * |   Fields
  * ================
  *
  * @_id 
  * @id 
  * @campaign_id
  * @product_id
  * @from_date
  * @to_date
  * @price
  * @product_status
  * @product_owner
  * @campaign_type
  * @quantity
  * @group_slot_id
  * 
  */
  
  protected $connection = 'content';

  public static $PRODUCT_STATUS = [
    'proposed'      =>    100, // available for adding into campaigns
    'scheduled'     =>    200, // not available for adding to campaigns
    'running'       =>    300,
    'completed'     =>    400,
    'deleted'       =>    500,
	'canceled'       =>    600,
	'rfp_proposed'  =>   700,
	'delete-requested'  =>   800,
	'delete-accepted'  =>   900
  ];

  public static $CAMPAIGN_USER_TYPE = [
    "user" => 0,
    "non-user" => 1
  ];
  
  /*==========================
  | Accessors
  ==========================*/
  public function getBookedFromAttribute($value)
  {
       if(!empty($value))
    // Log::info(print_r($value->toDateTime()->format('c'), true));
    return $value->toDateTime()->format('c');
  }
  
  public function getBookedToAttribute($value)
  {
       if(!empty($value))
    // Log::info(print_r($value->toDateTime()->format('c'), true));
    return $value->toDateTime()->format('c');
  }
public function getBookedSlotsAttribute($value)
  {
       if(!empty($value))
    // Log::info(print_r($value->toDateTime()->format('c'), true));
    return $value;
  }
}
