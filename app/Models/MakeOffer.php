<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class MakeOffer extends Moloquent{
  
/* 
  * MakeOffer fields
  *
  * id
  * campaign_id
  * price
  * comments
  * user_id
  * updated_at
  * created_at
  * status
  * status_accept_one
  * status_accept_two
  * status_reject_one
  * status_reject_two
  * type
  * message

  */

  protected $connection = 'content';
  protected $collection = 'make_offer';

  public static $OFFER_STATUS = [
    'offer-requested'    =>    10,
	'offer-accepted-one'      =>    20,
	'offer-accepted-two'       =>    30,
	'offer-rejected-one'       =>    40,
	'offer-rejected-two'       =>    50
  ]; 
  public static $OFFER_STATUS_ACCEPT_ONE = [
    'offer-accepted-one'      =>    20
  ];
  public static $OFFER_STATUS_ACCEPT_TWO = [
    'offer-accepted-two'       =>    30
  ];
  public static $OFFER_STATUS_REJECT_ONE = [
    'offer-rejected-one'       =>    40
  ];
  public static $OFFER_STATUS_REJECT_TWO = [
    'offer-rejected-two'       =>    50
  ];
  
  public static $CAMPAIGN_USER_TYPE = [
    'user' => 0,
    "bbi" => 1,
    "owner" => 2
  ];

}
