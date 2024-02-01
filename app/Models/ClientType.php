<?php 

namespace App\Models;
use Illuminate\Database\Eloquent\Model as Eloquent;

class ClientType extends Eloquent
{
  protected $connection = 'accounts';

  public function clients(){
    return $this->hasMany('App\Models\Client');
  }
}