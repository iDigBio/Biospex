<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Jobs;

use App\Models\Project;
use App\Notifications\Generic;
use App\Services\MongoDbService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Class DeleteProjectJob
 */
class DeleteProjectJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Project $project)
    {
        $this->project = $project->withoutRelations();
        $this->onQueue(config('config.queue.default'));
    }

    /**
     * Execute the job.
     */
    public function handle(MongoDbService $mongoDbService): void
    {
        $this->project->load(['expeditions.downloads', 'group.owner']);

        $this->project->expeditions->each(function ($expedition) use ($mongoDbService) {
            $expedition->downloads->each(function ($download) {
                Storage::disk('s3')->delete(config('config.export_dir').'/'.$download->file);
            });

            $mongoDbService->setCollection('pusher_transcriptions');
            $mongoDbService->deleteMany(['expedition_uuid' => $expedition->uuid]);

            $expedition->delete();
        });

        $mongoDbService->setCollection('panoptes_transcriptions');
        $mongoDbService->deleteMany(['subject_projectId' => $this->project->id]);

        $mongoDbService->setCollection('subjects');
        $mongoDbService->deleteMany(['project_id' => $this->project->id]);

        $this->project->delete();
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $throwable): void
    {
        $attributes = [
            'subject' => t('Delete Project Job Failed'),
            'html' => [
                t('Error: Could not delete Group %s', $this->project->title),
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage()),
                t('The Administration has been notified. If you are unable to resolve this issue, please contact the Administration.'),
            ],
        ];

        $this->project->group->owner->notify(new Generic($attributes, true));
    }
}
