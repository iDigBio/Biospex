<?php

namespace App\Jobs;

use App\Jobs\Traits\SkipNfn;
use App\Models\User;
use App\Notifications\JobError;
use App\Services\Process\PanoptesTranscriptionProcess;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ZooniverseTranscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SkipNfn;

    /**
     * @var int
     */
    private $expeditionId;

    /**
     * Create a new job instance.
     *
     * @param int $expeditionId
     */
    public function __construct(int $expeditionId)
    {
        $this->onQueue(config('config.reconcile_tube'));
        $this->expeditionId = $expeditionId;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Process\PanoptesTranscriptionProcess $transcriptionProcess
     * @return void
     */
    public function handle(PanoptesTranscriptionProcess $transcriptionProcess)
    {
        if ($this->skipReconcile($this->expeditionId)) {
            return;
        }

        try {
            $transcriptDir = config('config.nfn_downloads_transcript');

            if (! Storage::exists($transcriptDir.'/'.$this->expeditionId.'.csv')) {
                $this->delete();

                return;
            }

            $csvFile = Storage::path($transcriptDir.'/'.$this->expeditionId.'.csv');
            $transcriptionProcess->process($csvFile, $this->expeditionId);

            if ($transcriptionProcess->checkCsvError()) {
                throw new Exception('Error in Classification transcript job.');
            }

            return;
        }
        catch (\Exception $e) {
            $user = User::find(1);
            $messages = [
                t('Error: %s', $e->getMessage()),
                t('File: %s', $e->getFile()),
                t('Line: %s', $e->getLine()),
            ];
            $user->notify(new JobError(__FILE__, $messages));

            return;
        }
    }
}
