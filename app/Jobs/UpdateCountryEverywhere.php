<?php

namespace App\Jobs;

use App\Models\Country;
use App\Models\Area;
use App\Models\City;
use App\Models\State;
use App\Models\Product;

class UpdateCountryEverywhere extends Job
{
    private $country;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Country $country)
    {
        $this->country = $country;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $new_country = $this->country;

        // Updating Areas
        /* 
        * {
        *    country_name
        * }
        *
        */        
        Area::raw(function($collection) use ($new_country){
            $collection->updateMany(["country_id" => $new_country->id], 
                ['$set' => [
                    "country_name" => $ $new_country->name
                ]]
            );
        });

        // Updating Cities
        /* 
        * {
        *    country_name
        * }
        *
        */        
        City::raw(function($collection) use ($new_country){
            $collection->updateMany(["country_id" => $new_country->id], 
                ['$set' => [
                    "country_name" => $ $new_country->name
                ]]
            );
        });

        // Updating Product
        /* 
        * {
        *    country_name
        * }
        *
        */        
        Product::raw(function($collection) use ($new_country){
            $collection->updateMany(["country" => $new_country->id], 
                ['$set' => [
                    "country_name" => $ $new_country->name
                ]]
            );
        });

        // Updating State
        /* 
        * {
        *    country_name
        * }
        *
        */        
        State::raw(function($collection) use ($new_country){
            $collection->updateMany(["country_id" => $new_country->id], 
                ['$set' => [
                    "country_name" => $ $new_country->name
                ]]
            );
        });
    }
}
