<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class Notification extends Moloquent{
   
  /*
  * ====================
  * |   Fields
  * ====================
  *
  * @_id
  * @id
  * @type
  * @from_id
  * @to_type
  * @to_id
  * @link
  * @desc
  * @message
  * @status
  * @customer_id
  * @data
  */
      
  protected $connection = 'content';

  public static $NOTIFICATION_STATUS = [
    'unread'  =>    0,
    'read'  =>    1
  ];

  public static $NOTIFICATION_TYPE = [
    'campaign-suggestion-requested'   =>    0,
    'campaign-quote-requested'        =>    1,
    'campaign-quote-provided'         =>    2,
    'campaign-launch-requested'       =>    3,
    'campaign-quote-revision'         =>    4,
    'campaign-launched'               =>    5,
    'campaign-suspended'              =>    6,
    'campaign-closed'                 =>    7,
    'account-super-admin-setup'       =>    8,
    'product-requested'               =>    9,
    'product-approved'                =>    10,
    'shortlisted-product-soldout'     =>    11,
    'delete-product-from-campaign'     =>    12,
    'delete-campaign'     =>    13,
    'request-offer'     =>    14,
    'offer-rejected'     =>    15,
    'offer-accepted'     =>    16,
    'rfp-requested'     =>    17,
    'product-expiry'     =>    18,
	'campaign-product-offer'     =>    19,
	'transfer-campaign'     =>    20,
	'bulk-upload'     =>    21,
	'bulk-upload-pending'     =>    22,
	'product-transfer'     =>    23,
    // Metro notifications
    'metro-camp-locked'               =>    111,
    'metro-camp-launched'             =>    121,
    'metro-camp-closed'               =>    131
    // Metro notifications end  
  ];

  public static $NOTIFICATION_CLIENT_TYPE = [
    'user'  => 0,
    'bbi'   => 1,
    'owner' => 2
  ];

}
