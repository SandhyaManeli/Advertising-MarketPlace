<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class NonUserCampaign extends Moloquent{

  /*
  * ================
  * |   Fields
  * ================
  *
  * @_id 
  * @id 
  * @created_by
  * @type
  * @name
  * @start_date
  * @end_date
  * @status
  * @org_name
  * @org_contact_name
  * @org_contact_email
  * @org_contact_phone
  * @client_id
  * @client_name
  * 
  */
  
  protected $connection = 'content';

  public static $CAMPAIGN_STATUS = [
    'preparing'    =>    0,
    'prepared'     =>    1,
    'quoted'       =>    2,
    'launched'     =>    3,
    'stopped'      =>    4
  ];

  public static $CAMPAIGN_USER_TYPE = [
    "bbi" => 1,
    "owner" => 2
  ];
  
}