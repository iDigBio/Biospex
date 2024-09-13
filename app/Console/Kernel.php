<?php

namespace App\Console;

use Cache;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

/**
 * Class Kernel
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
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('cache:prune-stale-tags')->hourly();

        $schedule->command('queue:prune-batches --hours=48 --unfinished=72')->daily();

        // Run ocr every 2 minutes.
        $schedule->command('export:queue')->everyTwoMinutes();
        $schedule->command('tesseract:ocr-process')->everyTwoMinutes();

        // Clean bingo maps
        $schedule->command('bingo:clean')->dailyAt('10:05');

        if ($this->app->environment('production')) {
            // Trigger workflow manager to handle csv creation and updating expedition/project
            $schedule->command('workflow:manage')->daily()->before(function () {
                Cache::flush();
                Artisan::call('lada-cache:flush');
            });

            /* May need to be re-enabled
            $schedule->command('workflow:manage')->days([0, 2, 4])->before(function () {
                Cache::flush();
                Artisan::call('lada-cache:flush');
            });
            */

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
