<?php

namespace App\Jobs;

use App\Repositories\Interfaces\User;
use App\Notifications\JobError;
use App\Services\Process\PanoptesTranscriptionProcess;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NfnClassificationsTranscriptJob extends Job implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 7200;

    /**
     * @var array
     */
    private $expeditionIds;

    /**
     * NfnClassificationsTranscriptJob constructor.
     *
     * @param array $expeditionIds
     */
    public function __construct(array $expeditionIds = [])
    {
        $this->expeditionIds = collect($expeditionIds);
        $this->onQueue(config('config.beanstalkd.classification_tube'));
    }

    /**
     * Execute the job.
     *
     * @param PanoptesTranscriptionProcess $transcriptionProcess
     * @param User $userContract
     * @return void
     */
    public function handle(
        PanoptesTranscriptionProcess $transcriptionProcess,
        User $userContract
    )
    {
        if ($this->expeditionIds->isEmpty())
        {
            $this->delete();

            return;
        }

        try
        {
            $filePath = config('config.classifications_transcript');

            $this->expeditionIds->filter(function($expeditionId) use ($filePath) {
                return file_exists($filePath . '/' . $expeditionId . '.csv');
            })->each(function($expeditionId) use ($transcriptionProcess, $filePath) {
                $transcriptionProcess->process($filePath . '/' . $expeditionId . '.csv');
            });

            if ( ! empty($transcriptionProcess->getCsvError()))
            {
                $user = $userContract->find(1);
                $user->notify(new JobError(__FILE__, $transcriptionProcess->getCsvError()));
            }

            NfnClassificationPusherTranscriptionsJob::dispatch($this->expeditionIds);
        }
        catch (\Exception $e)
        {
            return;
        }
    }
}
