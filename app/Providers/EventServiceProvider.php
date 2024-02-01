<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\SomeEvent' => [
            'App\Listeners\EventListener',
        ],
         'App\Events\CampaignQuoteRequestedEvent' => [
            'App\Listeners\CampaignQuoteRequestedListener',
        ],
        'App\Events\CampaignClosedEvent' => [
            'App\Listeners\CampaignClosedListener',
        ],
        'App\Events\CampaignLaunchEvent' => [
            'App\Listeners\CampaignLaunchListener',
        ],
        'App\Events\CampaignLaunchRequestedEvent' => [
            'App\Listeners\CampaignLaunchRequestedListener',
        ],
        'App\Events\CampaignQuoteProvidedEvent' => [
            'App\Listeners\CampaignQuoteProvidedListener',
        ],
        'App\Events\CampaignQuoteRevisionEvent' => [
            'App\Listeners\CampaignQuoteRequestedListener',
        ],
        'App\Events\CampaignSuspendedEvent' => [
            'App\Listeners\CampaignSuspendedListener',
        ],
        'App\Events\metroCampaignClosedEvent' => [
            'App\Listeners\metroCampaignClosedListener',
        ],
        'App\Events\metroCampaignLaunchEvent' => [
            'App\Listeners\metroCampaignLaunchListener',
        ],
        'App\Events\metroCampaignLockedEvent' => [
            'App\Listeners\metroCampaignLockedListener',
        ]
    ];
}
