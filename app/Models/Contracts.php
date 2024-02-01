<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class Contracts extends Moloquent{
  
/* 
  * Contracts fields
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
  protected $collection = 'contracts';

   // public static $PRODUCT_STATUS =[
   // 'requested' => 0,
     // 'approved' => 1,
     // 'rejected' => 2
   // ];

}
