<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Mail;
use App\Models\Campaign;
use App\Models\ShortListedProduct;
use App\Models\User;
use App\Models\UserMongo;
use App\Models\Product;
use Auth;
use Entrust;
use JWTAuth;

class MailController extends Controller
{
	/**
		* Create a new controller instance.
		*
		* @return void
		*/

	private $request, $input;

	
	public function __construct(Request $request)
	{
		$this->request = $request;
    if ($request->isJson()) {
			$this->input = $request->json()->all();
    } else {
			$this->input = $request->all();
    }

		// Resolve dependencies out of container
		// $this->middleware('jwt.auth', ['only' => [
		// 	'getCampaigns',
		// 	'getCampaignDetails',
		// 	'addProductToCampaign',
		// 	'saveCampaign',
		// 	'saveSuggestionRequest',
		// 	'deleteCampaign'
		// ]]);
		// $this->middleware('role:admin|owner', ['only' => [
    //   'getAllCampaignRequests'
    // ]]);
	}

	public function testMail(){
    Mail::send('mail.registration', ['key' => 'value'], function($message){
        $message->to('mridulkashyap57@gmail.com', 'Mridul Kashyap')->subject('Welcome!');
    });
	}

}
