<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class BulkUpload extends Moloquent{
  
/* 
  * BulkUpload fields
  *
  * id
  * client_mongo_id
  * client_name
  * seller_name
  * subseller_name
  * image
  * updated_at
  * created_at
  * 
  *
  */

  protected $connection = 'content';
  protected $collection = 'bulk_upload';

   // public static $PRODUCT_STATUS =[
   // 'requested' => 0,
     // 'approved' => 1,
     // 'rejected' => 2
   // ];

}
