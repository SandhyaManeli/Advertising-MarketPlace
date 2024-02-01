<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class CampaignPayment extends Moloquent{

  /*
  * ================
  * |   Fields
  * ================
  *
  * @_id 
  * @id 
  * @campaign_id
  * @amount
  * @refunded_amount
  * @bal_amount_available_with_amp
  * @type
  * @reference_no
  * @client_mongo_id
  * @received_by
  * @image 
  * @updated_by_id
  * @updated_by_name
  * @updated_at 
  * @created_at
  *
  */
  
  protected $connection = 'content';

}