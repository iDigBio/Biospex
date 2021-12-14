<?php

namespace App\Console;

use Cache;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

/**
 * Class Kernel
 *
 * @package App\Console
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [//
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Run ocr every 15 minutes.
        $schedule->command('ocrprocess:records')->everyFiveMinutes();

        // Failed jobs report used to check ocr
        $schedule->command('report:failed')->timezone('America/New_York')->dailyAt('05:30');

        // Clean imports directory
        $schedule->command('download:clean')->timezone('America/New_York')->dailyAt('06:00');

        // Clean bingo maps
        $schedule->command('bingo:clean')->timezone('America/New_York')->dailyAt('06:05');

        if ($this->app->environment('prod')) {
            // Create Zooniverse csv files Mon, Wed, Fri
            /*
            $schedule->command('zooniverse:csv')
                ->timezone('America/New_York')
                ->days([1,3,5])->at('01:00')->before(function () { //mon, wed, fri
                    Cache::flush();
                    Artisan::call('lada-cache:flush');
                });
            */

            // Trigger workflow manager to handle csv creation and updating expedition/project
            $schedule->command('workflow:manage')->timezone('America/New_York')->days([
                    1,
                    3,
                    5,
                ])->at('01:00')->before(function () {
                    Cache::flush();
                    Artisan::call('lada-cache:flush');
                });

            // WeDigBio classification cron
            $schedule->command('dashboard:records')->everyThirtyMinutes();
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

        require base_path('routes/console/console.php');
    }
}
