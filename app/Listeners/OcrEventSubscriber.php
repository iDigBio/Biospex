<?php

namespace App\Listeners;

use App\Models\OcrQueue;

/**
 * Class OcrEventSubscriber
 *
 * @package App\Listeners
 */
class OcrEventSubscriber
{
    /**
     * Register the listeners for the subscriber.
     *
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'ocr.error',
            'App\Listeners\OcrEventSubscriber@error'
        );

        $events->listen(
            'ocr.reset',
            'App\Listeners\OcrEventSubscriber@reset'
        );

        $events->listen(
            'ocr.status',
            'App\Listeners\OcrEventSubscriber@status'
        );
    }

    /**
     * Record error.
     *
     * @param \App\Models\OcrQueue $queue
     */
    public function error(OcrQueue $queue)
    {
        $queue->status = 0;
        $queue->error = 1;
        $queue->save();
    }

    /**
     * Reset queue record.
     *
     * @param \App\Models\OcrQueue $queue
     * @param $count
     */
    public function reset(OcrQueue $queue, $count)
    {
        $queue->total = $count;
        $queue->processed = 0;
        $queue->status = 1;
        $queue->save();
    }

    /**
     * Set status to zero.
     *
     * @param \App\Models\OcrQueue $queue
     */
    public function status(OcrQueue $queue)
    {
        $queue->status = 0;
        $queue->save();
    }
}