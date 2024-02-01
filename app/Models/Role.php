<?php 

namespace App\Models;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Role extends Eloquent
{
  protected $connection = 'accounts';

  /**
   * =================
   * | Fields
   * =================
   * 
   * id
   * name
   * display_name
   * description
   * created_at
   * updated_at   
   *  
   */

  public function client()
  {
      return $this->belongsTo('App\Models\Client');
  }

  public function permissions()
  {
      return $this->belongsToMany('App\Models\Permission');
  }

}