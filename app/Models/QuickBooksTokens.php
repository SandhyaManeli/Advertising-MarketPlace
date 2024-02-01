<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class QuickBooksTokens extends Moloquent{
  
/* 
  * QuickBooksTokens fields
  *
  * id
  * access_token
  * refresh_token
  * expires_at
  * updated_at
  * created_at
  * status
  */

  protected $connection = 'content';
  protected $collection = 'quickBooks_tokens';
 
}
