<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class CampaignChatBox extends Moloquent{
  
  /* 
  * ChatBox fields
  *
  * id
  * receiver_id
  * campaign_id
  * product_id
  * product_siteNo
  * user_type_receiver
  * user_type_sender
  * message
  * created_by
  * status
  * updated_at
  * created_at
  */
 
  protected $connection = 'content';
}
