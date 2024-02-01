<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\Role;
use App\Models\Permission;

class User extends Eloquent implements JWTSubject, AuthenticatableContract, AuthorizableContract
{
    protected $connection = 'accounts';

    use Authenticatable, EntrustUserTrait;

    /**
     * ===================
     * | Fields
     * ===================
     * 
     * id 
     * username       
     * email                     
     * password                         
     * salt    
     * activated 
     * created_at          
     * updated_at
     */

    /**
     * Role Relationship
     */
    public function client(){
        return $this->belongsTo('App\Models\Client');
    }

    public function roles(){
        return $this->belongsToMany('App\Models\Role');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'salt',
        'hidden'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
