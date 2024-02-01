<?php

namespace App\Listeners;

use App\Events\CampaignSuspendedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;


class CampaignSuspendedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ExampleEvent  $event
     * @return void
     */
    public function handle(CampaignSuspendedEvent $event)
    {
            // print_r($event);
             if(isset($event->mail)) {
                $mail = $event->mail;
                if(isset($mail['email_to'])){
                    $mail_data = [
                        'email_to' => $mail['email_to'],
                        'recipient_name' => $mail['recipient_name'],
                        'subject' => $mail['subject']
                    ];
                }else{
                    $mail_data = [
                        'bcc' => $mail['bcc'],
                        'subject' => $mail['subject']
                    ];

                }
                
                Mail::send('mail.general_notif', $mail['mail_tmpl_params'], function($message) use ($mail_data){
                    if(isset($mail_data['email_to']))  $message->to($mail_data['email_to'], $mail_data['recipient_name'])->subject($mail_data['subject']);
                    else $message->bcc($mail_data['bcc'])->subject($mail_data['subject']);
                });
             }
           
    }
}
