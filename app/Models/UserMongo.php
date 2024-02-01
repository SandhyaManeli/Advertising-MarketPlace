<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class UserMongo extends Moloquent{

  /**
   * ================
   * | Fields
   * ================
   * 
   * @_id
   * @id
   * @user_id
   * @first_name
   * @last_name
   * @phone
   * @profile_pic
   * @company_name
   * @company_type
   * @address
   * @street
   * @city
   * @zipcode
   * @website
   * @updated_at
   * @created_at
   * @loggedInUser
   * @password_reset_request_count
   * @register_from
   */  
  
  protected $connection = 'content';

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
  
  public static $REQUEST_STATUS = [
    'requested-count'    =>    1
  ]; 
    
  public static $REQUEST__PASSWORD_STATUS = [
    'password-link-status'    =>    10,
    'password-link-success'    =>    20
  ]; 

}