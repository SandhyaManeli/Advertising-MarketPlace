<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;


class SubSellerUser extends Eloquent 
{
    protected $connection = 'accounts';
	protected $table = 'subsellusers';
	

    /**	
     * ===================
     * | Fields
     * ===================
     * 
     * id 
     * subseller_username       
     * subseller_email                     
     * subseller_password                         
     * salt    
     * activated 
     * created_at          
     * updated_at
     */

  public function roles() 
  {
      return $this->belongsToMany('App\Models\Role');
  }

  public function users()
  {
      return $this->belongsToMany('App\Models\User');
  }
  
  public function client_type()
  {
	return $this->belongsTo('App\Models\ClientType', 'type');
  }
}
