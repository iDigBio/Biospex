<?php namespace App\Console;

use App\Console\Commands\AustinProcessCommand;
use App\Console\Commands\ClearOcrResults;
use App\Console\Commands\ImageTest;
use App\Console\Commands\NfnClassificationsUpdate;
use App\Console\Commands\AmChartUpdate;
use App\Console\Commands\ClearBeanstalkdQueueCommand;
use App\Console\Commands\DarwinCoreFileImportCommand;
use App\Console\Commands\DownloadCleanCommand;
use App\Console\Commands\Inspire;
use App\Console\Commands\NfnWorkflowUpdate;
use App\Console\Commands\OcrDeleteFile;
use App\Console\Commands\OcrProcessCommand;
use App\Console\Commands\OcrQueuePushCommand;
use App\Console\Commands\ExpeditionStatUpdate;
use App\Console\Commands\TestAppCommand;
use App\Console\Commands\UpdateQueries;
use App\Console\Commands\ViewsCommand;
use App\Console\Commands\WorkFlowManagerCommand;
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
        OcrProcessCommand::class,
        OcrDeleteFile::class,
        AustinProcessCommand::class,
        UpdateQueries::class,
        AmChartUpdate::class,
        ExpeditionStatUpdate::class,
        ClearOcrResults::class,
        NfnClassificationsUpdate::class,
        NfnWorkflowUpdate::class
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
