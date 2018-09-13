<?php

namespace App\Console\Commands;

use App\Jobs\ExportQueueJob;
use App\Repositories\Interfaces\ExportQueue;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ExportQueueCommand extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fire export queue job.';

    /**
     * @var ExportQueue
     */
    public $exportQueueContract;


    /**
     * ExportQueueCommand constructor.
     * @param ExportQueue $exportQueueContract
     */
    public function __construct(ExportQueue $exportQueueContract)
    {
        parent::__construct();
        $this->exportQueueContract = $exportQueueContract;
    }

    /**
     * Handle job.
     */
    public function handle()
    {
        $record = $this->exportQueueContract->getFirstExportWithoutError();

        if ($record === null)
        {
            return;
        }

        if ($record->queued)
        {
            $this->dispatch((new ExportQueueJob($record))->onQueue(config('config.beanstalkd.export_tube')));

            return;
        }

        if (! $record->queued)
        {
            $this->exportQueueContract->update(['queued' => 1], $record->id);
        }

        return;
    }
}