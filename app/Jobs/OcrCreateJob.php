<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\JobError;
use App\Services\Process\OcrService;
use Artisan;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class OcrCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $timeout = 36000;

    /**
     * @var
     */
    private $projectId;

    /**
     * @var null
     */
    private $expeditionId;

    /**
     * OcrCreateJob constructor.
     *
     * @param $projectId
     * @param null $expeditionId
     */
    public function __construct($projectId, $expeditionId = null)
    {
        $this->projectId = $projectId;
        $this->expeditionId = $expeditionId;
        $this->onQueue(config('config.default_tube'));
    }

    /**
     * Handle Job.
     *
     * @param \App\Services\Process\OcrService $ocrService
     */
    public function handle(OcrService $ocrService)
    {
        if (config('config.ocr_disable')) {
            $this->delete();

            return;
        }

        try {
            $queue = $ocrService->createOcrQueue($this->projectId, $this->expeditionId);

            if (! $queue) {
                $this->delete();

                return;
            }

            $total = $ocrService->getSubjectCount($this->projectId, $this->expeditionId);

            if ($total === 0) {
                $queue->delete();
                event('ocr.poll');

                return;
            }

            $queue->total = $total;
            $queue->save();

            event('ocr.poll');

            Artisan::call('ocrprocess:records');

        } catch (Exception $e) {
            $user = User::find(1);
            $messages = [
                'Project Id: '.$this->projectId,
                'Expedition Id: '.$this->expeditionId,
                'Message:' . $e->getFile() . ': ' . $e->getLine() . ' - ' . $e->getMessage()
            ];
            $user->notify(new JobError(__FILE__, $messages));
        }

        $this->delete();

        return;
    }
}
