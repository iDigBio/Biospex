<?php

namespace App\Jobs;

use App\Models\OcrQueue;
use App\Models\User;
use App\Notifications\JobError;
use App\Services\Process\OcrService;
use Artisan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class OcrProcessJob
 *
 * @package App\Jobs
 */
class OcrProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $timeout = 86400;

    /**
     * @var \App\Models\OcrQueue
     */
    private $ocrQueue;

    /**
     * Create a new job instance.
     * Queue is already created and has subject count.
     *
     * @param \App\Models\OcrQueue $ocrQueue
     */
    public function __construct(OcrQueue $ocrQueue)
    {
        $this->ocrQueue = $ocrQueue;
        $this->onQueue(config('config.queue.ocr'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Process\OcrService $ocrService
     */
    public function handle(OcrService $ocrService)
    {
        $queue = $ocrService->findOcrQueueById($this->ocrQueue->id);

        try {
            if ($queue->processed === $queue->total) {
                $ocrService->complete($queue);
                $queue->delete();
                Artisan::call('ocr:poll');

                $this->delete();

                return;
            }

            $queue->total = $ocrService->getSubjectCountForOcr($queue->project_id, $queue->expedition_id);
            $queue->status = 1;
            $queue->processed = 0;
            $queue->save();

            $query = $ocrService->getSubjectQueryForOcr($queue->project_id, $queue->expedition_id);

            $query->chunk(5, function ($chunk) use (&$queue) {
                $chunk->each(function ($subject) {
                    OcrTesseractJob::dispatchSync($this->ocrQueue->id, $subject);
                });

                $queue->processed = $queue->processed + $chunk->count();
                $queue->save();

                Artisan::call('ocr:poll');
            });

            $queue->status = 0;
            $queue->save();

            $this->delete();

            return;
        } catch (\Exception $e) {
            $queue->error = 1;
            $queue->save();

            $user = User::find(1);
            $messages = [
                'Queue Id:'.$queue->id,
                'Project Id: '.$queue->project_id,
                'Expedition Id: '.$queue->expedition_id,
                'Message:'.$e->getFile().': '.$e->getLine().' - '.$e->getMessage(),
            ];
            $user->notify(new JobError(__FILE__, $messages));

            $this->delete();

            return;
        }
    }
}
