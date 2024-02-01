<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class Company extends Moloquent{
  
  /* 
  * Company Fields
  *  
  * @id
  * @name
  * @company_type
  * @company_slug
  * @contact_email
  * @contact_phone
  * @contact_name
  * @address
  * @updated_at
  * @created_at
  */

  protected $connection = 'content';
  
}