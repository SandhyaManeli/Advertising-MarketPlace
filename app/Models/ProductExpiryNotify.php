<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class ProductExpiryNotify extends Moloquent{
  
/* 
  * ProductExpiryNotify fields
  *
  * id
  * product_id
  * user_id
  * updated_at
  * created_at
  * status
  * message
  * user_message
  * loggedinUser

  */

  protected $connection = 'content';
  protected $collection = 'product_expiry_notify';



}
