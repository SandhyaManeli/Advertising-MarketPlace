<?php

namespace App\Events;
use App\Models\Notification;
use Illuminate\Queue\SerializesModels;
//use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\UserMongo;
use JWTAuth;

class LaunchCampaignEvent implements ShouldBroadcast
{
    use  InteractsWithSockets, SerializesModels;

    public $username;

    public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($username)
    {
        $this->username = $username;
        $this->message  = "{$username} liked your status";
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return ['launch-campaign'];
    }

    public function broadcastAs()
    {
        return 'LaunchCampaignEvent';
    }
}