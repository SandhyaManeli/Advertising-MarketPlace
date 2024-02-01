<?php

namespace App\Jobs;

use App\Models\Area;
use App\Models\Product;


class UpdateAreaEverywhere extends Job
{
    private $area;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Area $area)
    {
        $this->area = $area;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $new_area = $this->area;

        // Updating Products
        /* 
        * {
        *    area_name
        * }
        *
        */        
        Product::raw(function($collection) use ($new_area){
            $collection->updateMany(["area" => $new_area->id], 
                ['$set' => [
                    "area_name" => $new_area->name
                ]]
            );
        });
    }
}
