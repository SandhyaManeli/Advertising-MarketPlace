<?php 

namespace App\Models;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Permission extends Eloquent
{
  protected $connection = 'accounts';
  
  public function roles()
  {
      return $this->belongsToMany('App\Models\Role');
  }

  public function users()
  {
      return $this->belongsToMany('App\Models\User');
  }
}