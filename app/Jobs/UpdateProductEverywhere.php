<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Campaign;


class UpdateProductEverywhere extends Job
{
    private $product;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Updating Campaigns
        /* 
        "products": [
            {
            "siteNo": "BI-B007"
            }
        ]
        */   
        $new_product = $this->product;
        Campaign::raw(function($collection) use ($new_product){
            $collection->updateMany(["products.id" => $new_product->id], 
                ['$set' => [
                    "products.$.siteNo" => $new_product->siteNo,
                ]]
            );
        });
    }
}
