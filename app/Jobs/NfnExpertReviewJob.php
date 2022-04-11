<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
namespace App\Jobs;

use App\Jobs\Traits\SkipNfn;
use App\Notifications\JobError;
use App\Notifications\NfnExpertReviewJobComplete;
use App\Repositories\ExpeditionRepository;
use App\Services\Reconcile\ExpertReconcileProcess;
use App\Services\Reconcile\ReconcileProcess;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class NfnExpertReviewJob
 *
 * @package App\Jobs
 */
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
        $this->onQueue(config('config.reconcile_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Repositories\ExpeditionRepository $expeditionRepo
     * @param \App\Services\Reconcile\ReconcileProcess $reconcileProcessService
     * @param \App\Services\Reconcile\ExpertReconcileProcess $expertreconcileRepo
     * @return void
     */
    public function handle(
        ExpeditionRepository $expeditionRepo,
        ReconcileProcess $reconcileProcessService,
        ExpertReconcileProcess $expertreconcileRepo
    )
    {
        $expedition = $expeditionRepo->findExpeditionForExpertReview($this->expeditionId);
        $user = $expedition->project->group->owner;

        try {
            if ($this->skipReconcile($this->expeditionId)) {
                throw new \Exception(t('Expert Review for Expedition (:id) ":title" was skipped. Please contact Biospex Administration', [':id' => $expedition->id, ':title' => $expedition->title]));
            }

            $reconcileProcessService->processExplained($expedition);
            $expertreconcileRepo->migrateReconcileCsv($expedition->id);
            $expertreconcileRepo->setReconcileProblems($expedition->id);

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
