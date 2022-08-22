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

use App\Models\Project;
use App\Repositories\ProjectRepository;
use App\Services\MongoDbService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Class DeleteProject
 *
 * @package App\Jobs
 */
class DeleteProject implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\Project
     */
    public $project;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
        $this->onQueue(config('config.queues.default'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Repositories\ProjectRepository $projectRepo
     * @param \App\Services\MongoDbService $mongoDbService
     * @return void
     */
    public function handle(ProjectRepository $projectRepo, MongoDbService $mongoDbService)
    {
        $project = $projectRepo->findWith($this->project->id, ['expeditions.downloads']);

        $project->expeditions->each(function ($expedition) use ($mongoDbService) {
            $expedition->downloads->each(function ($download){
                Storage::delete(config('config.export_dir').'/'.$download->file);
            });

            $mongoDbService->setCollection('pusher_transcriptions');
            $mongoDbService->deleteMany(['expedition_uuid' => $expedition->uuid]);

            $expedition->delete();
        });

        $mongoDbService->setCollection('panoptes_transcriptions');
        $mongoDbService->deleteMany(['subject_projectId' => $project->id]);

        $mongoDbService->setCollection('subjects');
        $mongoDbService->deleteMany(['project_id' => $project->id]);

        $project->delete();
    }
}
