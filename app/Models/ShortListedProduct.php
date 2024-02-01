<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class ShortListedProduct extends Moloquent{
  

  /*
  ================
  |   Fields
  ================

  _id 
  user_mongo_id 
  product_id 
  updated_at 
  created_at 
  quantity
  group_slot_id
  */  

  protected $connection = 'content';

  
  /*==========================
  | Accessors
  ==========================*/
  public function getFromDateAttribute($value)
  {
    // Log::info(print_r($value->toDateTime()->format('c'), true));
    return $value->toDateTime()->format('c');
  }
  
  public function getToDateAttribute($value)
  {
    // Log::info(print_r($value->toDateTime()->format('c'), true));
    return $value->toDateTime()->format('c');
  }

}