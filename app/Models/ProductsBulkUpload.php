<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class ProductsBulkUpload extends Moloquent{
  
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
  protected $collection = 'products_bulk_upload';
}
