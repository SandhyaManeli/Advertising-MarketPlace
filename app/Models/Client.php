<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Client extends Eloquent
{
    protected $connection = 'accounts';    

    /**
     * ===================
     * | Fields
     * ===================
     * 
     * id
     * company_name
     * activated
     * created_at          
     * updated_at
     */

    public function users(){
        return $this->hasMany('App\Models\User');
    }

    public function roles(){
        return $this->hasMany('App\Models\Role');
    }

    public function client_type(){
        return $this->belongsTo('App\Models\ClientType', 'type');
    }

    public function super_admin(){
        return $this->hasOne('App\Models\User', 'id', 'super_admin');
    }
}
