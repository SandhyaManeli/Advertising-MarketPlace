<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class CustomerQuery extends Moloquent{

/**===============
   * | Fields
   * ================
   * 
   * @_id
   * @id
   * @customer_id
   */ 

	protected $connection = 'content';

  public static $QUERY_TYPE = [
    'newsletter-subscription'   => 1,
    'request-callback'          => 2,
    'user-query'                => 3
  ];

}
