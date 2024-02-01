<?php

namespace App\Jobs;

use App\Models\City;
use App\Models\Area;
use App\Models\Product;


class UpdateCityEverywhere extends Job
{
    private $city;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(City $city)
    {
        $this->city = $city;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $new_city = $this->city;

        // Updating Areas
        /* 
        * {
        *    city_name
        * }
        *
        */        
        Area::raw(function($collection) use ($new_city){
            $collection->updateMany(["city_id" => $new_city->id], 
                ['$set' => [
                    "city_name" => $new_city->name
                ]]
            );
        });

        // Updating Products
        /* 
        * {
        *    city_name
        * }
        *
        */        
        Product::raw(function($collection) use ($new_city){
            $collection->updateMany(["city" => $new_city->id], 
                ['$set' => [
                    "city_name" => $new_city->name
                ]]
            );
        });
    }
}
