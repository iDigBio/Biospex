<?php

namespace App\Jobs;

use App\Jobs\Traits\SkipNfn;
use App\Notifications\JobError;
use App\Notifications\NfnExpertReviewJobComplete;
use App\Services\Model\ExpeditionService;
use App\Services\Process\ExpertReconcileProcess;
use App\Services\Process\ReconcileProcess;
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
     * @param \App\Services\Model\ExpeditionService $expeditionService
     * @param \App\Services\Process\ReconcileProcess $reconcileProcessService
     * @param \App\Services\Process\ExpertReconcileProcess $expertReconcileService
     * @return void
     */
    public function handle(
        ExpeditionService $expeditionService,
        ReconcileProcess $reconcileProcessService,
        ExpertReconcileProcess $expertReconcileService
    )
    {
        $expedition = $expeditionService->findExpeditionForExpertReview($this->expeditionId);
        $user = $expedition->project->group->owner;

        try {
            if ($this->skipReconcile($this->expeditionId)) {
                throw new \Exception(t('Expert Review for Expedition (:id) ":title" was skipped. Please contact Biospex Administration', [':id' => $expedition->id, ':title' => $expedition->title]));
            }

            $reconcileProcessService->processExplained($expedition);
            $expertReconcileService->migrateReconcileCsv($expedition->id);
            $expertReconcileService->setReconcileProblems($expedition->id);

            $expedition->nfnActor->pivot->expert = 1;
            $expedition->nfnActor->pivot->save();

            $user->notify(new NfnExpertReviewJobComplete($expedition->title, $expedition->id));

            $this->delete();

        } catch (\Exception $e) {
            $messages = [
                'Message: ' .  $e->getMessage(),
                'File : ' . $e->getFile() . ': ' . $e->getLine()
            ];
            $user->notify(new JobError(__FILE__, $messages));

            $this->delete();
        }
    }
}
