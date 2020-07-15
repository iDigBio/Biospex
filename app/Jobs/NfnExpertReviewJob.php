<?php

namespace App\Jobs;

use App\Jobs\Traits\SkipNfn;
use App\Notifications\JobError;
use App\Notifications\NfnExpertReviewJobComplete;
use App\Repositories\Interfaces\Expedition;
use App\Services\Model\ReconcileService;
use App\Services\Process\ReconcileProcessService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NfnExpertReviewJob implements ShouldQueue
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
    public function __construct(int $expeditionId )
    {
        $this->expeditionId = $expeditionId;
        $this->onQueue(config('config.classification_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param \App\Services\Process\ReconcileProcessService $reconcileProcessService
     * @param \App\Services\Model\ReconcileService $reconcileService
     * @return void
     */
    public function handle(
        Expedition $expeditionContract,
        ReconcileProcessService $reconcileProcessService,
        ReconcileService $reconcileService
    )
    {
        $expedition = $expeditionContract->findExpeditionForExpertReview($this->expeditionId);
        $user = $expedition->project->group->owner;

        try {
            if ($this->skipReconcile($this->expeditionId)) {
                throw new \Exception(__('pages.expert_review_job_create_skip_msg', ['id' => $expedition->id, 'title' => $expedition->title]));
            }

            $reconcileProcessService->processExplained($expedition);
            $reconcileService->migrateReconcileCsv($expedition->id);
            $reconcileService->setReconcileProblems($expedition->id);

            $expedition->nfnActor->pivot->expert = 1;
            $expedition->nfnActor->pivot->save();

            $user->notify(new NfnExpertReviewJobComplete($expedition->title, $expedition->id));

            return $this->deleteJob();

        } catch (\Exception $e) {
            $messages = [
                'Message: ' .  $e->getMessage(),
                'File : ' . $e->getFile() . ': ' . $e->getLine()
            ];
            $user->notify(new JobError(__FILE__, $messages));

            return $this->deleteJob();
        }
    }

    /**
     * Delete job and return.
     */
    private function deleteJob()
    {
        $this->delete();

        return;
    }
}
