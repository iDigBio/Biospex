<?php

namespace App\Listeners;

use App\Repositories\Interfaces\OcrQueue;
use Artisan;

class OcrEventListener
{

    /**
     * @var \App\Repositories\Interfaces\OcrQueue
     */
    private $ocrQueue;

    /**
     * Create the event listener.
     *
     * @param \App\Repositories\Interfaces\OcrQueue $ocrQueue
     */
    public function __construct(OcrQueue $ocrQueue)
    {
        $this->ocrQueue = $ocrQueue;
    }

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
     * @param \App\Models\OcrQueue $record
     */
    public function error(\App\Models\OcrQueue $record)
    {
        $record->queued = 0;
        $record->error = 1;
        $record->save();
    }
}