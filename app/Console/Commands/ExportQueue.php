<?php

namespace App\Console\Commands;

use App\Jobs\ExportQueueJob;
use App\Repositories\Contracts\ExportQueueContract;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ExportQueue extends Command
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
     * @var ExportQueueContract
     */
    public $exportQueueContract;


    /**
     * ExportQueue constructor.
     * @param ExportQueueContract $exportQueueContract
     */
    public function __construct(ExportQueueContract $exportQueueContract)
    {
        parent::__construct();
        $this->exportQueueContract = $exportQueueContract;
    }

    public function handle()
    {
        $record = $this->exportQueueContract->setCacheLifetime(0)->getFirst();

        if ($record === null)
        {
            return;
        }

        if ($record->queued && ! $record->error)
        {
            $this->dispatch((new ExportQueueJob($record))->onQueue(config('config.beanstalkd.export')));

            return;
        }

        if (! $record->queued)
        {
            $this->exportQueueContract->update($record->id, ['queued' => 1]);
        }

        return;
    }
}