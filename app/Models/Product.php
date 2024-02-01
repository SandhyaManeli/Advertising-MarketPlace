<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class Product extends Moloquent{
  
  /* 
  * Product fields
  *
  * id
  * siteNo
  * @from_date
  * @pro_from_date
  * @to_date
  * @pro_to_date
  * adStrength
  * address
  * area
  * client_name
  * direction
  * default_price
  * image
  * impressions
  * lat
  * lighting
  * pixelsFeet
  * lng
  * symbol
  * panelSize
  * type
  * format_name
  * country_name
  * country
  * state_name
  * state
  * city_name
  * city
  * area_name
  * status
  * updated_at
  * created_at
  * client_mongo_id
  * fix
  * minimumdays
  * network
  * nationloc
  * daypart
  * genre
  * costperpoint
  * length
  * reach
  * daysselected
  * stripe_percent
  */
 
  protected $connection = 'content';

  public static $PRODUCT_STATUS =[
    'requested' => 0,
    'approved' => 1,
    'rejected' => 2
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
  
     
  public static $STATIC_PRODUCT = [
    'product-static'    =>    '_ST'
  ];
  public static $DIGITAL_PRODUCT = [
	'product-digital'      =>    '_DT'
  ];
  
  public static $STATIC_DIGITAL_PRODUCT = [
	'product-digital-static'       =>    '_D/S'
  ];
  public static $MEDIA_PRODUCT = [
	'product-media'       =>    '_MD'
  ];

}
