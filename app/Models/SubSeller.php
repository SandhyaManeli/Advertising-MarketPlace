<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class SubSeller extends Moloquent{
  
  /* 
  * SubSeller fields
  * _id
  * id
  * subseller_id
  * seller_id
  * subseller_name 
  * name
  * designation
  * email
  * phone
  * company_name
  * password
  * status
  * updated_at
  * created_at
  * product_id
  */

  protected $connection = 'content';
  protected $collection = 'subsellerInformation';


  
  protected $hidden = [
    '_id',
    'created_at',
    'updated_at',
    'verification_code',
    'verification_code_expiry',
    'verification_type'
  ];

  public static $VERIFICATION_TYPES = [
    "verify-email" => 0,
    "generate-password" => 1,
    "verify-email-company" => 2,
    "user-invitation" => 3,
    "subseller-generate-pwd" => 4
  ];
  
}