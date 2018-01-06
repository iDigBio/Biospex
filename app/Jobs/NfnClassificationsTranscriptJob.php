<?php

namespace App\Jobs;

use App\Interfaces\User;
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
     * @var array
     */
    private $ids;

    /**
     * NfnClassificationsTranscriptJob constructor.
     *
     * @param array $ids
     */
    public function __construct(array $ids = [])
    {
        $this->ids = collect($ids);
        $this->onQueue(config('config.beanstalkd.classification'));
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
        if ($this->ids->isEmpty())
        {
            $this->delete();

            return;
        }

        try
        {
            $filePath = config('config.classifications_transcript');

            $this->ids->filter(function($id) use ($filePath) {
                return file_exists($filePath . '/' . $id . '.csv');
            })->each(function($id) use ($transcriptionProcess, $filePath) {
                $transcriptionProcess->process($filePath . '/' . $id . '.csv');
            });

            if ( ! empty($transcriptionProcess->getCsvError()))
            {
                $user = $userContract->find(1);
                $user->notify(new JobError(__FILE__, $transcriptionProcess->getCsvError()));
            }

            WeDigBioDashboardJob::dispatch($this->ids);
        }
        catch (\Exception $e)
        {
            return;
        }
    }
}
