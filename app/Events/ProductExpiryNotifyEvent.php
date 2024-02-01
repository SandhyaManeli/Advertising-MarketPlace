<?php

namespace App\Events;
use App\Models\Notification;
use Illuminate\Queue\SerializesModels;
//use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\UserMongo;
use App\Models\CustomerQuery;
use JWTAuth;


class ProductExpiryNotifyEvent implements ShouldBroadcast
{
    use  InteractsWithSockets, SerializesModels;

    public $desc;
    public $to_type;
    public $message;
    public $data;
    public $from_id;
    public $to_client;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data)
    {

            $this->desc = $data['desc'] ;
            $this->to_type = $data['to_type'];
            $this->message  =  $data['message'];
            $this->data  = $data['data'];
            $this->from_id = $data['from_id'];
            $this->to_client = $data['to_client'];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        //$user_mongo = JWTAuth::parseToken()->getPayload()['userMongo'];
        return ['OfferRequested-superAdmin'];
    }

    public function broadcastAs()
    {
        return 'ProductExpiryNotifyEvent';
    }
}
