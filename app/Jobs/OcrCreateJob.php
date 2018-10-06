<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\JobError;
use App\Services\Actor\Ocr\OcrCreate;
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
        $this->onQueue(config('config.ocr_tube'));
    }

    /**
     * Handle Job.
     *
     * @param \App\Services\Actor\Ocr\OcrCreate $ocrCreate
     */
    public function handle(OcrCreate $ocrCreate)
    {
        if (config('config.ocr_disable')) {
            $this->delete();

            return;
        }

        try {
            $queue = $ocrCreate->create($this->projectId, $this->expeditionId);

            if (! $queue) {
                $this->delete();

                return;
            }

            event('ocr.poll');

        } catch (\Exception $e) {
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
