<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class CancelCampaign extends Moloquent{
  
/* 
  * CancelCampaign fields
  *
  * id
  * campaign_id
  * user_query
  * price
  * updated_at
  * created_at
  * loggedinUser
  * status
  */

  protected $connection = 'content';
  protected $collection = 'cancel_campaign';
  
  
    public static $CAMPAIGN_STATUS = [
    'cancel-campaign-request'    =>    99
  ];

}
