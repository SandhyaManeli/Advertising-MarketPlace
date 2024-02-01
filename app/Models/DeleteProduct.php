<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class DeleteProduct extends Moloquent{
  
/* 
  * DeleteProduct fields
  *
  * id
  * product_id
  * comments
  * price
  * updated_at
  * created_at
  * loggedinUser
  * status
  */

  protected $connection = 'content';
  protected $collection = 'delete_product';
  
  
    public static $PRODUCT_STATUS = [
    'delete-product-from-campaign'    =>    101,
    'confirm-delete-product-from-campaign'    =>    102
  ];

}
