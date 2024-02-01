<?php

namespace App\Jobs;

use App\Models\UserMongo;
use App\Models\User;
use App\Models\Campaign;
use App\Models\CampaignPayment;


class UpdateUserEverywhere extends Job
{
    private $user_mongo;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(UserMongo $user_mongo)
    {
        $this->user_mongo = $user_mongo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $new_user_mongo = $this->user_mongo;

        // Updating Campaigns
        /* 
        * @user_mongo_id
        * @user_full_name
        * @user_phone
        * @user_email
        * @user_avatar
        */        
        Campaign::raw(function($collection) use ($new_user_mongo){
            $collection->updateMany(["user_mongo_id" => $new_user_mongo->id], 
                ['$set' => [
                    "user_full_name" => $new_user_mongo->first_name . " " . $new_user_mongo->last_name, 
                    "user_phone" => $new_user_mongo->phone,
                    "user_email" => $new_user_mongo->email,
                    "user_avatar" => $new_user_mongo->profile_pic,
                ]]
            );
        });

        // Updating Campaign Payments
        /* 
        * @updated_by_admin
        * @updated_by_admin_name
        */
        CampaignPayment::raw(function($collection) use ($new_user_mongo){
            $collection->updateMany(["updated_by_admin" => $new_user_mongo->id], 
                ['$set' => [
                    "updated_by_admin_name" => $new_user_mongo->first_name . " " . $new_user_mongo->last_name
                ]]
            );
        });
    }
}
