<?php

namespace App\Listeners;

use App\Repositories\Contracts\ExportQueueContract;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ExportQueueEventListener
{

    /**
     * @var ExportQueueContract
     */
    private $exportQueueContract;
    /**
     * @var DispatchesJobs
     */
    private $dispatchesJobs;

    /**
     * Create the event listener.
     *
     * @param ExportQueueContract $exportQueueContract
     * @param DispatchesJobs $dispatchesJobs
     */
    public function __construct(
        ExportQueueContract $exportQueueContract
    )
    {
        $this->exportQueueContract = $exportQueueContract;
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'exportQueue.saved',
            'App\Listeners\ExportQueueEventListener@saved'
        );

        $events->listen(
            'exportQueue.deleted',
            'App\Listeners\ExportQueueEventListener@deleted'
        );

    }

    public function saved()
    {
        if ( ! $this->exportQueueContract->checkForQueuedJob())
        {
            //$this->dispatchesJobs->fire();
        }
    }

    public function deleted()
    {

    }
}
