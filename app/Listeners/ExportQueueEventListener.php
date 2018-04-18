<?php

namespace App\Listeners;


use App\Repositories\Interfaces\ExportQueue;
use App\Jobs\ExportQueueJob;

class ExportQueueEventListener
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
     * Register the listeners for the subscriber.
     *
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'exportQueue.updated',
            'App\Listeners\ExportQueueEventListener@updated'
        );
    }

    /**
     * Entity Updated.
     *
     * @see ExportQueueRepository::getFirstExportWithoutError() Get first record with no error.
     */
    public function updated()
    {
        $record = $this->exportQueue->getFirstExportWithoutError();

        if ($record === null)
        {
            return;
        }

        if ($record->queued)
        {
            ExportQueueJob::dispatch($record);

            return;
        }

        if ( ! $record->queued)
        {
            $record->queued = 1;
            $record->save();

            ExportQueueJob::dispatch($record);

            return;
        }

    }

}

