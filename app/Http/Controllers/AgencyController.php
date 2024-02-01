<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Hash;
use App\Models\Marker;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\UserMongo;
use JWTAuth;
use JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;

class AgencyController extends Controller
{
  private $input;
  private $request;
  private $default_password = "BbiUser789#";
  /**
  * Create a new controller instance.
  *
  * @return void
  */
  public function __construct(Request $request)
  {
    // Resolve dependencies out of container
    $this->request = $request;
    if ($request->isJson()) {
      $this->input = $request->json()->all();
    } else {
      $this->input = $request->all();
    }

    // $this->middleware('jwt.auth', ['only' => [
    //   'addAgency',
    //   'getAllAgencies'
    // ]]);
    // $this->middleware('role:admin|owner', ['only' => [
    //   'addAgency',
    //   'getAllAgencies'
    // ]]);
  }

  public function addAgency(){
    $this->validate($this->request, 
      [
        'email' => 'required',
        'agency_name' => 'required'
        // 'first_name' => 'required',
        // 'last_name' => 'required'
      ],
      [
        'email.required' => 'Email is required',
        'agency_name.required' => 'Agency name is required'
      ]
    );
    $user = new User();    
    $user->email = $this->input['email'];
    $user->salt = str_random(7);
    $user->password = md5($this->default_password . $user->salt);
    $agency_role = Role::where('name', '=', 'agency')->first();
    $user->attachRole($agency_role);
    if($user->save()){
      $user_mongo = new UserMongo;
      $user_mongo->id = uniqid();
      $user_mongo->user_id = $user->id;
      $user_mongo->agency_name = isset($this->input['agency_name']) ? $this->input['agency_name'] : "";
      $user_mongo->phone = isset($this->input['phone']) ? $this->input['phone'] : "";
      $user_mongo->company_type = isset($this->input['company_type']) ? $this->input['company_type'] : "";
      if($user_mongo->save()){
        return response()->json(['status' => 1, 'message' => 'agency saved successfully.']);
      }
      else{
        return response()->json(['status' => 0, 'message' => 'agency saved partially.']);
      }
    }
    else{
      return response()->json(['status' => 1, 'message' => 'failed to save agency.']);
    }
  }

  public function getAllAgencies(){
    $users = User::whereHas('roles', function($query) {
              $query->where('name', '=', 'agency');
            })->get();
    $usersToReturn = [];
    foreach($users as $user){
      $user_mongo = UserMongo::where('user_id', $user->id)->first();
      array_push($usersToReturn, array_merge($user->toArray(), $user_mongo->toArray()));
    }            
    return response()->json($usersToReturn);
  }
}


