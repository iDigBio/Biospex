<?php namespace App\Console;

use App\Console\Commands\AustinProcessCommand;
use App\Console\Commands\ClearOcrResults;
use App\Console\Commands\ExportPollCommand;
use App\Console\Commands\NfnClassificationsCsvCreate;
use App\Console\Commands\NfnClassificationsCsvFile;
use App\Console\Commands\NfnClassificationsFusionTable;
use App\Console\Commands\NfnClassificationsReconciliation;
use App\Console\Commands\NfnClassificationsTranscript;
use App\Console\Commands\NfnClassificationsUpdate;
use App\Console\Commands\AmChartUpdate;
use App\Console\Commands\ClearBeanstalkdQueueCommand;
use App\Console\Commands\DarwinCoreFileImportCommand;
use App\Console\Commands\DownloadCleanCommand;
use App\Console\Commands\Inspire;
use App\Console\Commands\NfnWorkflowUpdate;
use App\Console\Commands\NotificationsUpdateCommand;
use App\Console\Commands\OcrDeleteFile;
use App\Console\Commands\OcrPollCommand;
use App\Console\Commands\OcrProcessCommand;
use App\Console\Commands\OcrQueuePushCommand;
use App\Console\Commands\ExpeditionStatUpdate;
use App\Console\Commands\TestAppCommand;
use App\Console\Commands\UpdateGoogleStateCountyTable;
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
        NfnWorkflowUpdate::class,
        ExportPollCommand::class,
        OcrPollCommand::class,
        NotificationsUpdateCommand::class,
        NfnClassificationsCsvCreate::class,
        NfnClassificationsCsvFile::class,
        NfnClassificationsReconciliation::class,
        NfnClassificationsTranscript::class,
        UpdateGoogleStateCountyTable::class,
        NfnClassificationsFusionTable::class
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
