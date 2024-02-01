<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class ProductExport extends Moloquent{
  
  /* 
  * Product fields
  *
  * id
  * user_id
  * report_type
  * selected_columns
  * status
  * created_at
  * updated_at
  */
 
  protected $connection = 'content';
  protected $collection = 'user_preference_export';

  public static $PRODUCT_STATUS =[
    'requested' => 0,
    'approved' => 1,
    'rejected' => 2
  ];

}
