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

use App\Models\ExpeditionStat;
use App\Models\PanoptesProject;
use App\Models\User;
use App\Notifications\Generic;
use App\Services\Api\PanoptesApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Class PanoptesProjectUpdateJob
 */
class PanoptesProjectUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * PanoptesProjectUpdateJob constructor.
     */
    public function __construct(protected PanoptesProject $panoptesProject)
    {
        $this->panoptesProject = $panoptesProject->withoutRelations();
        $this->onQueue(config('config.queue.classification'));
    }

    /**
     * Execute job.
     */
    public function handle(PanoptesApiService $panoptesApiService, ExpeditionStat $expeditionStat)
    {

        $workflow = $panoptesApiService->getPanoptesWorkflow($this->panoptesProject->panoptes_workflow_id);

        $panoptesApiService->calculateTotals($workflow, $this->panoptesProject->expedition_id);

        $stat = $expeditionStat->where('expedition_id', $this->panoptesProject->expedition_id)->first();
        $stat->subject_count = $panoptesApiService->getSubjectCount();
        $stat->transcriptions_goal = $panoptesApiService->getTranscriptionsGoal();
        $stat->local_transcriptions_completed = $panoptesApiService->getLocalTranscriptionsCompleted();
        $stat->transcriptions_completed = $panoptesApiService->getTranscriptionsCompleted();
        $stat->percent_completed = $panoptesApiService->getPercentCompleted();
        $stat->save();

        $panoptes_project_id = $workflow['links']['project'];
        $subject_sets = $workflow['links']['subject_sets'] ?? '';

        $project = $panoptesApiService->getPanoptesProject($panoptes_project_id);

        $values = [
            'panoptes_project_id' => $panoptes_project_id,
            'subject_sets' => $subject_sets,
            'slug' => $project['slug'],
        ];

        $this->panoptesProject->fill($values);
        $this->panoptesProject->save();

        $this->delete();
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $throwable): void
    {
        $attributes = [
            'subject' => t('Panoptes Project Update Job Failed: %s', $this->panoptesProject->id),
            'html' => [
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage()),
            ],
        ];

        $user = User::find(config('config.admin.user_id'));
        $user->notify(new Generic($attributes));
    }
}
