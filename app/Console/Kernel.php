<?php

namespace App\Console;

use App\Console\Commands\Cards\DebitPackagesCommand;
use App\Console\Commands\Cards\RefundTransactionCommand;
use App\Console\Commands\Package\PackageSyncEvents;
use App\Console\Commands\Package\SyncWarehouseUnknowns;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Console\Commands\PreAlerts\MultipleTrackings;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        MultipleTrackings::class,
        DebitPackagesCommand::class,
        RefundTransactionCommand::class,
        PackageSyncEvents::class,
        SyncWarehouseUnknowns::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $platform = current_platform();
        if ($platform && $platform->isMailamericas()) {
            // Search warehouse unknowns every 3 hours
            $schedule->command('warehouse:sync-unknowns')->cron('0 */3 * * *')->withoutOverlapping();

            // Debit rejected packages
            // $schedule->command('packages:debit')->days([1,3,5])->at('12:00')->withoutOverlapping();
        } else {
            // Debit rejected packages
            // $schedule->command('packages:debit')->weeklyOn(1)->at('12:00')->withoutOverlapping();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
