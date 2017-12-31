<?php

namespace App\Observers;

use App\Interfaces\ExportQueue;
use App\Jobs\ExportQueueJob;
use App\Models\ExportQueue as Model;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ExportQueueObserver
{

    use DispatchesJobs;

    /**
     * @var ExportQueue
     */
    private $exportQueue;

    /**
     * ExportQueueObserver constructor.
     * @param ExportQueue $exportQueue
     */
    public function __construct(ExportQueue $exportQueue)
    {
        $this->exportQueue = $exportQueue;
    }

    /**
     * Entity Updated.
     *
     * @param Model $model
     *
     * @see NfnPanoptesExport::exportQueue() Fired when new exports added.
     * @see ExportQueueJob::handle() Call job if a record exists and not queued.
     * @see ExportQueueRepository::getFirstExportWithoutError() Get first record with no error.
     */
    public function created(Model $model)
    {
        $record = $this->exportQueue->getFirstExportWithoutError();
        if ($record !== null && ! $record->queued)
        {
            $record->queued = 1;
            $record->save();
        }
    }

    /**
     * Entity Updated.
     *
     * @param Model $model
     * @see ExportQueueRepository::getFirstExportWithoutError() Get first record with no error.
     */
    public function updated(Model $model)
    {
        if ($model->queued && ! $model->error)
        {
            $this->dispatch((new ExportQueueJob($model))->onQueue(config('config.beanstalkd.export')));

            return;
        }

        $record = $this->exportQueue->getFirstExportWithoutError();
        if ($record !== null && ! $record->queued)
        {
            $model->queued = 1;
            $model->save();
        }

    }

    /**
     * Entity Deleted.
     *
     * @param Model $model
     */
    public function deleted(Model $model)
    {
        $record = $this->exportQueue->getFirst();
        if ($record !== null && ! $record->queued)
        {
            $record->queued = 1;
            $record->save();
        }
    }
}