<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Queue\SerializesModels;
//use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\UserMongo;
use JWTAuth;

class CampaignQuoteRequestedEvent implements ShouldBroadcast {

    use InteractsWithSockets,
        SerializesModels;

    public $desc;
    public $to_type;
    public $message;
    public $data;
    public $from_id;
    public $to_client;
    public $mail;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data, $mail) {

        $this->desc = $data['desc'];
        $this->to_type = $data['to_type'];
        $this->message = $data['message'];
        $this->data = $data['data'];
        $this->from_id = $data['from_id'];
        $this->to_client = $data['to_client'];
        $this->mail = $mail;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn() {
        if (Notification::$NOTIFICATION_CLIENT_TYPE['bbi'] == $this->to_type) {
            return ['CampaignQuoteRequested-superAdmin'];
        } else if (Notification::$NOTIFICATION_CLIENT_TYPE['owner'] == $this->to_type) {
			foreach($this->to_client as $value){
            return 'CampaignQuoteRequested-' . $value;
			}
        } else {
            $user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
           // return 'CampaignQuoteRequested' . $this->to_client;
		   foreach($this->to_client as $value){
            return 'CampaignQuoteRequested'.$value;
			}
        }
    }

    public function broadcastAs() {
        return 'CampaignQuoteRequestedEvent';
    }

}
