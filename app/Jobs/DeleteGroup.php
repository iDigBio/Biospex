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
use App\Services\Model\GroupService;
use App\Services\MongoDbService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

/**
 * Class DeleteGroup
 *
 * @package App\Jobs
 */
class DeleteGroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\Group
     */
    public $group;

    /**
     * Create a new job instance.
     *
     * @param $group
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
        $this->onQueue(config('config.default_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Model\GroupService $groupService
     * @param \App\Services\MongoDbService $mongoDbService
     * @return void
     */
    public function handle(GroupService $groupService, MongoDbService $mongoDbService)
    {
        $group = $groupService->findWith($this->group->id, ['projects.expeditions.downloads']);

        $group->projects->each(function ($project) use ($mongoDbService) {
            $project->expeditions->each(function ($expedition) use ($mongoDbService) {
                $expedition->downloads->each(function ($download) {
                    Storage::delete(config('config.export_dir').'/'.$download->file);
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

        $group->delete();
    }
}
