<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\Models\Campaign;
//use DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // register custom commands
        Commands\RefreshRouteTableCommand::class,
        Commands\CloseMetroCampaigns::class,
        Commands\CloseMetroCampaignProducts::class,
        Commands\FreeProductsByToDate::class,
        Commands\RunningCampaignProducts::class,
        Commands\ProductsExpiration::class,
        Commands\BulkUploads::class,
        Commands\RegisteredUsers::class
    ];

    /**
     * Define the application's command schedule.
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('CloseMetroCampaigns:command')->dailyAt('00:00');
        $schedule->command('CloseMetroCampaignProducts:command')->dailyAt('00:00');
        $schedule->command('product_availibility:refresh')->dailyat('00:00');
        $schedule->command('run_campaign_product:command')->daily()->at('00:00');
        $schedule->command('ProductsExpiration:command')->daily()->at('00:00');
        //$schedule->command('ProductsExpiration:command')->everyMinute();
        $schedule->command('BulkUploads:command')->everyThirtyMinutes();
        //$schedule->command('BulkUploads:command')->everyMinute();
		$schedule->command('RegisteredUsers:command')->everyMinute();
    }
}
