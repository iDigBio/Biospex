<?php

namespace App\Listeners;

use App\Models\OcrQueue;
use App\Services\Process\OcrService;
use Artisan;

class OcrEventListener
{
    /**
     * @var \App\Services\Process\OcrService
     */
    private $ocrService;

    /**
     * OcrEventListener constructor.
     *
     * @param \App\Services\Process\OcrService $ocrService
     */
    public function __construct(OcrService $ocrService)
    {

        $this->ocrService = $ocrService;
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

        $events->listen(
            'ocr.reset',
            'App\Listeners\OcrEventListener@reset'
        );

        $events->listen(
            'ocr.status',
            'App\Listeners\OcrEventListener@status'
        );

        $events->listen(
            'ocr.create',
            'App\Listeners\OcrEventListener@create'
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

    /**
     * Create ocr queue.
     *
     * @param int $projectId
     * @param int|null $expeditionId
     * @throws \Exception
     */
    public function create(int $projectId, int $expeditionId = null)
    {
        if (config('config.ocr_disable')) {
            return;
        }

        $queue = $this->ocrService->createOcrQueue($projectId, $expeditionId);
        $total = $this->ocrService->getSubjectCount($projectId, $expeditionId);

        if ($total === 0) {
            $queue->delete();
            event('ocr.poll');

            return;
        }

        $queue->total = $total;
        $queue->save();

        event('ocr.poll');

        Artisan::call('ocrprocess:records');

    }
}