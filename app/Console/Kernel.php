<?php namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\Inspire::class,
        \App\Console\Commands\WorkFlowManagerCommand::class,
        \App\Console\Commands\DownloadCleanCommand::class,
        \App\Console\Commands\ViewsCommand::class,
        \App\Console\Commands\TestAppCommand::class,
        \App\Console\Commands\ClearBeanstalkdQueueCommand::class,
        \App\Console\Commands\DarwinCoreFileImportCommand::class,
        \App\Console\Commands\OcrQueuePushCommand::class,
        \App\Console\Commands\DatabaseQueryCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('inspire')->hourly();
    }
}
