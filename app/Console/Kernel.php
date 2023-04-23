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
        $schedule->command('queue:prune-batches --hours=48 --unfinished=72')->daily();

        // Run ocr every 15 minutes.
        $schedule->command('ocrprocess:records')->everyFiveMinutes();

        // Failed jobs report used to check ocr
        $schedule->command('report:failed')->dailyAt('09:30');

        // Clean bingo maps
        $schedule->command('bingo:clean')->dailyAt('10:05');

        if ($this->app->environment('production')) {
            // Trigger workflow manager to handle csv creation and updating expedition/project
            $schedule->command('workflow:manage')->at('05:00')->before(function () {
                Cache::flush();
                Artisan::call('lada-cache:flush');
            });

            // WeDigBio classification cron. Pulls pusher records from MySql table and enters into pusher_transcriptions
            $schedule->command('dashboard:records')->everyFiveMinutes();
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
