<?php

namespace App\Observers;

use App\Repositories\Interfaces\ExportQueue;
use App\Jobs\ExportQueueJob;
use App\Models\ExportQueue as Model;

class ExportQueueObserver
{

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
            \Log::alerts('created and updated');
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
            \Log::alerts('dispatched');
            ExportQueueJob::dispatch($model);

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
        $record = $this->exportQueue->getFirstExportWithoutError();
        if ($record !== null && ! $record->queued)
        {
            $record->queued = 1;
            $record->save();
        }
    }
}