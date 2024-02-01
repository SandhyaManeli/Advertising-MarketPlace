<?php 

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
//use Illuminate\Support\Facades\Mail;
use App\Models\UserMongo;
use App\Models\User;
use App\Models\ClientMongo;
use App\Models\Client;
use App\Models\Campaign;
use App\Models\Notification;
use App\Events\accountSuperAdminEvent;
use Log;
use PDF;
use Carbon\Carbon; 

class RegisteredUsers extends Command {
    
  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'RegisteredUsers:command';
  
  /**
   * The console command description.
   *
   * @var string
   */ 
  protected $description = "User registration from home page and static home page of AMP";

  public function __construct(){
    parent::__construct();
  }
  
  /**
   * Execute the console command.
   *
   * @return void
   */
  public function handle()
  {
    $current_date = Carbon::now()->subDays(7);
   $user_mongo = userMongo::select('first_name', 'last_name','email','phone','company_name','company_type','created_at','register_from')->where('created_at', '>=',  $current_date)->orderBy('created_at', 'desc')->get(); 
   $users_report = ['user_mongo' => $user_mongo];
	$pdf = PDF::loadView('pdf.registered_users',$users_report); 
	//$pdf = PDF::loadView('pdf.registered_users');
	 $mail_tmpl_params = [
                    'receiver_name' => 'Sand',
                    'mail_message' => 'Please find the attached PDF for last 7days registered users'            
                ];
                $mail_data = [
                    //'email_to' => 'sandhyarani.manelli@peopletech.com',
                    'email_to' => 'rajitha.gundru@peopletech.com',
                    'recipient_name' => 'Sand',
					'pdf_file_name' => "Users-" . date('m-d-Y') . ".pdf",
					//'pdf_file_name' => "Registered users from" . $current_date . "-" . date('m-d-Y') . ".pdf",
                    'pdf' => $pdf
                ];
                Mail::send('mail.general_notif', $mail_tmpl_params, function($message) use ($mail_data) {
                    $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject('Registered Users From AMP');
					$message->attachData($mail_data['pdf']->output(), $mail_data['pdf_file_name']);
                });
	
    }
 
  }