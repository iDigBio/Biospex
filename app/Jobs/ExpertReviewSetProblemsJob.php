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

use App\Jobs\Traits\SkipZooniverse;
use App\Notifications\Generic;
use App\Notifications\Traits\ButtonTrait;
use App\Repositories\ExpeditionRepository;
use App\Services\Reconcile\ExpertReconcileService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpertReviewSetProblemsJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SkipZooniverse, ButtonTrait;

    /**
     * @var int
     */
    private int $expeditionId;

    /**
     * @var int
     */
    public int $timeout = 1800;

    /**
     * Indicate if the job should be marked as failed on timeout.
     *
     * @var bool
     */
    public bool $failOnTimeout = true;

    /**
     * Create a new job instance.
     *
     * @param int $expeditionId
     */
    public function __construct(int $expeditionId)
    {
        $this->expeditionId = $expeditionId;
        $this->onQueue(config('config.queue.reconcile'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(
        ExpeditionRepository $expeditionRepo,
        ExpertReconcileService $expertReconcileService
    )
    {
        $expedition = $expeditionRepo->findExpeditionForExpertReview($this->expeditionId);
        $user = $expedition->project->group->owner;

        try {
            if ($this->skipReconcile($this->expeditionId)) {
                throw new \Exception(t('Expert Review for Expedition (:id) ":title" was skipped. Please contact Biospex Administration', [':id' => $expedition->id, ':title' => $expedition->title]));
            }

            $expertReconcileService->setReconcileProblems($expedition->id);

            $expedition->zooniverseActor->pivot->expert = 1;
            $expedition->zooniverseActor->pivot->save();

            $route = route('admin.reconciles.index', ['expeditions' => $this->expeditionId]);
            $btn = $this->createButton($route, t('Expert Review Start'));

            $attributes = [
                'subject' => t('Expert Review Job Complete'),
                'html'    => [
                    t('The Expert Review job for %s is complete and you may start reviewing the reconciled records.', $expedition->title),
                    t('You may access the page by going to the Expedition Download modal and clicking the green button or click the button below and be taken to the page directly.')
                ],
                'buttons' => [$btn]
            ];

            $user->notify(new Generic($attributes));

            $this->delete();

        } catch (\Throwable $throwable) {
            $attributes = [
                'subject' => t('Expert Review Job Error'),
                'html'    => [
                    t('An error occurred while setting the problems for Expedition %s.', $expedition->title),
                    t('File: %s', $throwable->getFile()),
                    t('Line: %s', $throwable->getLine()),
                    t('Message: %s', $throwable->getMessage()),
                    t('The Administration has been notified. If you are unable to resolve this issue, please contact the Administration.'),
                ],
            ];

            $user->notify(new Generic($attributes));

            $this->delete();
        }
    }
}
