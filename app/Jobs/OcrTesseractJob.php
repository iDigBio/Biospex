<?php

namespace App\Jobs;

use App\Models\OcrQueue;
use App\Models\User;
use App\Notifications\JobError;
use App\Services\Actor\Ocr\OcrTesseract;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OcrTesseractJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $timeout = 36000;

    /**
     * @var \App\Models\OcrQueue
     */
    private $record;

    /**
     * ocrTesseractJob constructor.
     *
     * @param \App\Models\OcrQueue $record
     */
    public function __construct(OcrQueue $record)
    {
        $this->record = $record;
        $this->onQueue(config('config.ocr_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Actor\Ocr\OcrTesseract $ocrTesseract
     * @return void
     */
    public function handle(OcrTesseract $ocrTesseract)
    {
        if (config('config.ocr_disable')) {
            $this->delete();

            return;
        }

        try {
            $ocrTesseract->process($this->record->mongo_id);
        }
        catch(\Exception $e) {
            event('ocr.error', $this->record);

            $user = User::find(1);
            $messages = [
                'Record Id: ' . $this->record->id,
                'Message:' . $e->getFile() . ': ' . $e->getLine() . ' - ' . $e->getMessage()
            ];
            $user->notify(new JobError(__FILE__, $messages));
        }

        $this->delete();

        return;
    }
}
