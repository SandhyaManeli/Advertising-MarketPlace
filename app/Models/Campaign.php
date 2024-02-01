<?php 
namespace App\Models;
use Moloquent\Eloquent\Model as Moloquent;

class Campaign extends Moloquent{

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
  * @prev_status
  *
  * ===== User campaign fields =====
  * @est_budget
  * @from_suggestion
  *
  * ===== non user cmapaign fields =====
  * @org_name
  * @org_contact_name
  * @org_contact_email
  * @org_contact_phone
  * @client_id
  * @client_name
  *
  * ===== RFP Camapign without login =====  
  * campaign_name
  * user_email
  * due_date
  */
   
  protected $connection = 'content';

  public static $CAMPAIGN_STATUS = [
    'campaign-preparing'    =>    100,
    'campaign-created'      =>    200,
    'quote-requested'       =>    300,
    'quote-given'           =>    400,
    'change-requested'      =>    500,
    'booking-requested'     =>    600,
    'scheduled'                =>    700,
    'running'             =>    800,
    'suspended'             =>    900,
    'stopped'               =>    1000,
    'deleted-cancelled'               =>    1200,
    'rfp-campaign'               =>    1300,

    // metro campaign statuses
    'metro-campaign-created'      =>    1101,
    'metro-campaign-checked-out'  =>    1121,
    'metro-campaign-locked'       =>    1131,
    'metro-campaign-running'      =>    1141,
    'metro-campaign-stopped'      =>    1151
  ];

  public static $CAMPAIGN_STATUS_1 = [
    100  =>  "Campaign Preparing",
    200  =>  "Campaign Created",
    300  =>  "Quote Created",
    400  =>  "Quote Given",
    500  =>  "Change Requested",
    600  =>  "Requested",
    700  =>  "Scheduled",
    800  =>  "Running",
    900  =>  "Suspended",
    1000   =>  "Stopped",
    1200   =>  "Deleted",
    1300   =>  "RFP Campaign"
  ];
  
  public static $CAMPAIGN_USER_TYPE = [
    'user' => 0,
    "bbi" => 1,
    "owner" => 2
  ];
}