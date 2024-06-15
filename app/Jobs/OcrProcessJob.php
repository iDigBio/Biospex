<?php

namespace App\Jobs;

use App\Models\OcrQueue;
use App\Models\User;
use App\Notifications\Generic;
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
    public int $timeout = 86400;

    /**
     * @var \App\Models\OcrQueue
     */
    private OcrQueue $ocrQueue;

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
    public function handle(OcrService $ocrService): void
    {
        $queue = $ocrService->findOcrQueueById($this->ocrQueue->id);

        try {
            if ($queue->processed === $queue->total) {
                $ocrService->sendNotify($queue);

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

            Artisan::call('ocrprocess:records');

            return;
        } catch (\Throwable $throwable) {
            $queue->error = 1;
            $queue->save();

            $attributes = [
                'subject' => t('Ocr Process Error'),
                'html'    => [
                    t('Queue Id: %s', $queue->id),
                    t('Project Id: %s'.$queue->project->id),
                    t('File: %s', $throwable->getFile()),
                    t('Line: %s', $throwable->getLine()),
                    t('Message: %s', $throwable->getMessage())
                ],
            ];
            $user = User::find(config('config.admin.user_id'));
            $user->notify(new Generic($attributes));

            $this->delete();

            return;
        }
    }
}
