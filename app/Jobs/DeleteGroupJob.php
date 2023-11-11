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

use App\Models\Group;
use App\Notifications\JobError;
use App\Services\MongoDbService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Class DeleteGroupJob
 *
 * @package App\Jobs
 */
class DeleteGroupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\Group
     */
    public Group $group;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Group $group
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
        $this->onQueue(config('config.queue.default'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\MongoDbService $mongoDbService
     * @return void
     */
    public function handle(MongoDbService $mongoDbService): void
    {
        $this->group->load(['projects.expeditions.downloads', 'geoLocateForms', 'owner']);

        $this->group->projects->each(function ($project) use ($mongoDbService) {
            $project->expeditions->each(function ($expedition) use ($mongoDbService) {
                $expedition->downloads->each(function ($download) {
                    Storage::disk('s3')->delete(config('config.export_dir').'/'.$download->file);
                });

                $mongoDbService->setCollection('panoptes_transcriptions');
                $mongoDbService->deleteMany(['subject_expeditionId' => $expedition->id]);

                $mongoDbService->setCollection('transcriptions');
                $mongoDbService->deleteMany(['expedition_id' => $expedition->id]);

                $expedition->delete();
            });

            $mongoDbService->setCollection('subjects');
            $mongoDbService->deleteMany(['project_id' => $project->id]);
        });

        $this->group->delete();
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $throwable
     * @return void
     */
    public function failed(\Throwable $throwable): void
    {
        $messages = [
            'Error: '.t('Could not delete Group %s', $this->group->title),
            t('Error: %s', $throwable->getMessage()),
            t('File: %s', $throwable->getFile()),
            t('Line: %s', $throwable->getLine()),
        ];

        $this->group->owner->notify(new JobError(__FILE__, $messages));
    }
}
