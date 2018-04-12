<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        // Clean imports directory
        $schedule->command('download:clean')->dailyAt('4:00')->before(function () {
            \Artisan::call('lada-cache:flush');
        });

        // Check ocr queue for error records
        $schedule->command('ocrqueue:check')->dailyAt('4:15');

        // Clean report directory
        $schedule->command('report:clean')->dailyAt('4:30');

        // Check ocr processing records and call ocr polling
        $schedule->command('ocrprocess:records')->everyFiveMinutes();

        // Trigger export polling
        $schedule->command('export:poll')->everyFiveMinutes();

        if ($this->app->environment() === 'prod') {
            // Create Notes From Nature csv files
            $schedule->command('nfn:csvcreate')->dailyAt('5:00')->before(function () {
                \Artisan::call('lada-cache:flush');
                \Artisan::call('test:test');
            });

            // Trigger workflow manager to update expeditions and projects
            $schedule->command('workflow:manage')->dailyAt('11:00')->before(function () {
                \Artisan::call('lada-cache:flush');
            });
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
