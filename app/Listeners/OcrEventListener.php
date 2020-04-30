<?php

namespace App\Listeners;

use App\Models\OcrQueue;
use Artisan;

class OcrEventListener
{
    /**
     * Register the listeners for the subscriber.
     *
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'ocr.poll',
            'App\Listeners\OcrEventListener@poll'
        );

        $events->listen(
            'ocr.error',
            'App\Listeners\OcrEventListener@error'
        );

        $events->listen(
            'ocr.reset',
            'App\Listeners\OcrEventListener@reset'
        );

        $events->listen(
            'ocr.status',
            'App\Listeners\OcrEventListener@status'
        );
    }

    /**
     * Record created.
     */
    public function poll()
    {
        Artisan::call('ocr:poll');
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