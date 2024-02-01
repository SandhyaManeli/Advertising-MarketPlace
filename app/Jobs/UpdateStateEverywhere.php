<?php

namespace App\Jobs;

use App\Models\State;
use App\Models\City;
use App\Models\Product;


class UpdateStateEverywhere extends Job
{
    private $state;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(State $state)
    {
        $this->state = $state;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        $new_state = $this->state;
        
        // Updating Cities
        /* 
        * {
        *    state_name
        * }
        *
        */        
        City::raw(function($collection) use ($new_state){
            $collection->updateMany(["state_id" => $new_state->id], 
                ['$set' => [
                    "state_name" => $new_state->name
                ]]
            );
        });

        // Updating Product
        /* 
        * {
        *    state_name
        * }
        *
        */        
        Product::raw(function($collection) use ($new_state){
            $collection->updateMany(["state" => $new_state->id], 
                ['$set' => [
                    "state_name" => $new_state->name
                ]]
            );
        });
    }
}
