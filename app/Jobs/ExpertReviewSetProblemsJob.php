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

use App\Models\Expedition;
use App\Notifications\Generic;
use App\Notifications\Traits\ButtonTrait;
use App\Services\Reconcile\ExpertReconcileService;
use App\Traits\SkipZooniverse;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpertReviewSetProblemsJob implements ShouldQueue
{
    use Batchable, ButtonTrait, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SkipZooniverse;

    public int $timeout = 1800;

    /**
     * Indicate if the job should be marked as failed on timeout.
     */
    public bool $failOnTimeout = true;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Expedition $expedition)
    {
        $this->onQueue(config('config.queue.reconcile'));
    }

    /**
     * Execute the job.
     */
    public function handle(ExpertReconcileService $expertReconcileService): void
    {
        $this->expedition->load('project.group.owner', 'zooniverseActor');

        try {
            if ($this->skipReconcile($this->expedition->id)) {
                throw new \Exception(t('Expert Review for Expedition (:id) ":title" was skipped. Please contact Biospex Administration', [
                    ':id' => $this->expedition->id, ':title' => $this->expedition->title,
                ]));
            }

            $expertReconcileService->setReconcileProblems($this->expedition->id);

            $this->expedition->zooniverseActor->pivot->expert = 1;
            $this->expedition->zooniverseActor->pivot->save();

            $route = route('admin.reconciles.index', ['expeditions' => $this->expedition->id]);
            $btn = $this->createButton($route, t('Expert Review Start'));

            $attributes = [
                'subject' => t('Expert Review Job Complete'),
                'html' => [
                    t('The Expert Review job for %s is complete and you may start reviewing the reconciled records.', $this->expedition->title),
                    t('You may access the page by going to the Expedition Download modal and clicking the green button or click the button below and be taken to the page directly.'),
                ],
                'buttons' => $btn,
            ];

            $this->expedition->project->group->owner->notify(new Generic($attributes));

            $this->delete();

        } catch (\Throwable $throwable) {
            $attributes = [
                'subject' => t('Expert Review Job Error'),
                'html' => [
                    t('An error occurred while setting the problems for Expedition %s.', $this->expedition->title),
                    t('File: %s', $throwable->getFile()),
                    t('Line: %s', $throwable->getLine()),
                    t('Message: %s', $throwable->getMessage()),
                    t('The Administration has been notified. If you are unable to resolve this issue, please contact the Administration.'),
                ],
            ];

            $this->expedition->project->group->owner->notify(new Generic($attributes, true));

            $this->delete();
        }
    }
}
