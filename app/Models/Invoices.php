<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class Invoices extends Moloquent{

/* 
  * Invoices fields
  *
  * id
  * user_mongo_id
  * user_type
  * title
  * file
  * updated_at
  * created_at
  * 
  *
  */

  protected $connection = 'content';
  protected $collection = 'invoices';

   // public static $PRODUCT_STATUS =[
   // 'requested' => 0,
     // 'approved' => 1,
     // 'rejected' => 2
   // ];

}
