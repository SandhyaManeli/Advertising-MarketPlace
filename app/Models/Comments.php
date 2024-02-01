<?php 
namespace App\Models;
use Moloquent\Eloquent\Model as Moloquent;

class Comments extends Moloquent{

  /*
  * ================
  * |   Fields
  * ================
  *
  * @_id 
  * @id 
  * @sender_id
  * @receiver_id
  * @campaign_id
  * @message
  * @created_at
  * @created_by
  * @status
  *
  
  */
  
  protected $connection = 'content';

 
}