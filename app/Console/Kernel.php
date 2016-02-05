<?php namespace Biospex\Console;

use Biospex\Console\Commands\AustinProcessCommand;
use Biospex\Console\Commands\ClearBeanstalkdQueueCommand;
use Biospex\Console\Commands\DarwinCoreFileImportCommand;
use Biospex\Console\Commands\DatabaseQueryCommand;
use Biospex\Console\Commands\DownloadCleanCommand;
use Biospex\Console\Commands\Inspire;
use Biospex\Console\Commands\OcrQueuePushCommand;
use Biospex\Console\Commands\TestAppCommand;
use Biospex\Console\Commands\ViewsCommand;
use Biospex\Console\Commands\WorkFlowManagerCommand;
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
        Inspire::class,
        WorkFlowManagerCommand::class,
        DownloadCleanCommand::class,
        ViewsCommand::class,
        TestAppCommand::class,
        ClearBeanstalkdQueueCommand::class,
        DarwinCoreFileImportCommand::class,
        OcrQueuePushCommand::class,
        DatabaseQueryCommand::class,
        AustinProcessCommand::class,
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
