<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class FindForMe extends Moloquent{
  
/* 
  * FindForMe fields
  *
  * id
  * campaign_id
  * price
  * comments
  * user_id
  * updated_at
  * created_at
  * loggedinUser
  * status
  */

  protected $connection = 'content';
  protected $collection = 'find_for_me';

  public static $CAMPAIGN_USER_TYPE = [
    'user' => 0,
    "bbi" => 1,
    "owner" => 2
  ];
 
}
