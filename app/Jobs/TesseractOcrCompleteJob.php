<?php

namespace App\Jobs;

use App\Models\OcrQueue;
use App\Models\User;
use App\Notifications\Generic;
use App\Services\Actor\TesseractOcr\TesseractOcrService;
use Artisan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class TesseractOcrCompleteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public function __construct(protected OcrQueue $ocrQueue)
    {
        $this->ocrQueue = $ocrQueue->withoutRelations();
        $this->onQueue(config('config.queue.ocr'));
    }

    public function handle(TesseractOcrService $service): void
    {
        // Run completion logic (report + notify)
        $service->ocrCompleted($this->ocrQueue);

        Artisan::call('ocr:listen-controller stop');

        // Delete the queue record
        $this->ocrQueue->delete();
    }

    public function failed(Throwable $throwable): void
    {
        // Mark queue as errored
        $this->ocrQueue->error = 1;
        $this->ocrQueue->save();

        $attributes = [
            'subject' => t('OCR Completion Job Failed'),
            'html' => [
                t('OCR Queue ID: %s', $this->ocrQueue->id),
                t('Project ID: %s', $this->ocrQueue->project_id),
                t('Expedition ID: %s', $this->ocrQueue->expedition_id ?? 'None'),
                t('Error: %s', $throwable->getMessage()),
            ],
        ];

        $user = User::find(config('config.admin.user_id'));
        $user->notify(new Generic($attributes));
    }
}