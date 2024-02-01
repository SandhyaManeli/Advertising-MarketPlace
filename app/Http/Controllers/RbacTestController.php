<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\DatabaseManager;
use App\Models\Product;
use App\Models\ShortListedProduct;
use App\Models\User;
use Auth;
use Entrust;
use PDF;

class RbacTestController extends Controller
{
  private $result;
	/**
		* Create a new controller instance.
		*
		* @return void
		*/
	public function __construct()
	{
		// Resolve dependencies out of container
		$this->middleware('jwt.auth', ['only' => [
      // paths that just need a logged in user
			'loginRequired'
    ]]);
    $this->middleware('role:owner', ['only' => [
      // paths that need the user to have owner role
      'isOwner'
    ]]);
    $this->middleware('role:admin', ['only' => [
      // paths that need the user to have owner role
      'isAdmin'
    ]]);
    $this->middleware('role:admin|owner', ['only' => [
      // paths that need the user to have owner role
      'isAdmin',
      'isOwner',
      'isAdminOrOwner'
    ]]);
    $this->middleware('permission:create-user', ['only' => [
      // paths that need to have permission 'create-user'
    ]]);
    $this->middleware('ability:admin|owner, , false', ['only' => [
      //paths that need the user to have either admin or owner role, or the permissions create-user or edit-user
      'isAdminOrOwner'
    ]]);
    $this->result = ['status' => '1', "message" => "success"];
	}

	public function loginRequired(){
		return response()->json($this->result);
	}

  public function isOwner(){
		return response()->json($this->result);
  }
  
  public function isAdmin(){
		return response()->json($this->result);
  }
  
  public function isAdminOrOwner(){
		return response()->json($this->result);
  }
  
  public function generatePdf(){
    $pdf = PDF::loadHTML('<h1>Test</h1>');
    return $pdf->download('invoice.pdf');
  }

  public function loadTestView($view_name){
    // return response()->json($view_name);
    return view('mail.campaign_details');
  }
  
}
