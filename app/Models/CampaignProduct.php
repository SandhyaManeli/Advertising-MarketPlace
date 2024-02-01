<?php 
namespace App\Models;
use Moloquent\Eloquent\Model as Moloquent;

class CampaignProduct extends Moloquent{

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
  * 
  */
  
  protected $connection = 'content';

  public static $PRODUCT_STATUS = [
    'proposed'    =>    0,
    'locked'     =>    1,
    'free'       =>    2 // product is freed when a running campaign cancels
  ];

  public static $CAMPAIGN_USER_TYPE = [
    "user" => 0,
    "non-user" => 1
  ];
  
}