<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class ClientMongo extends Moloquent{
  
  /* 
  * Company Fields
  *  
  * @email
  * @name
  * @phone
  * @client_type
  * @person_name
  * @address
  * @owner
  * @company_type
  * @updated_at
  * @created_at
  * @id
  * @loggedInUser
  * @register_from
  */

  protected $connection = 'content';
  
}