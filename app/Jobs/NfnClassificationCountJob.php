<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\JobError;
use App\Repositories\Interfaces\ExpeditionStat;
use App\Repositories\Interfaces\PanoptesProject;
use App\Services\Api\PanoptesApiService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NfnClassificationCountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $this->expeditionId = $expeditionId;
        $this->onQueue(config('config.classification_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Repositories\Interfaces\PanoptesProject $panoptesProject
     * @param \App\Services\Api\PanoptesApiService $panoptesApiService
     * @param \App\Repositories\Interfaces\ExpeditionStat $expeditionStat
     * @return void
     */
    public function handle(
        PanoptesProject $panoptesProject,
        PanoptesApiService $panoptesApiService,
        ExpeditionStat $expeditionStat
    )
    {

        try {
            $record = $panoptesProject->findBy('expedition_id', $this->expeditionId);

            if ($record === null) {
                $this->delete();

                return;
            }

            $workflow = $panoptesApiService->getPanoptesWorkflow($record->panoptes_workflow_id);
            $panoptesApiService->calculateTotals($workflow, $this->expeditionId);

            $stat = $expeditionStat->findBy('expedition_id', $this->expeditionId);
            $stat->subject_count = $panoptesApiService->getSubjectCount();
            $stat->transcriptions_goal = $panoptesApiService->getTranscriptionsGoal();
            $stat->local_transcriptions_completed = $panoptesApiService->getLocalTranscriptionsCompleted();
            $stat->transcriptions_completed = $panoptesApiService->getTranscriptionsCompleted();
            $stat->percent_completed = $panoptesApiService->getPercentCompleted();

            $stat->save();
        }
        catch (Exception $e) {
            $messages = [
                'Message: ' .  $e->getMessage(),
                'File : ' . $e->getFile() . ': ' . $e->getLine()
            ];

            $user = User::find(1);
            $user->notify(new JobError(__FILE__, $messages));

            return $this->deleteJob();

        }
    }
}
