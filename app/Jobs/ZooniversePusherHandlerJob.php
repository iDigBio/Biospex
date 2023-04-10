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

use App\Repositories\PanoptesProjectRepository;
use App\Repositories\WeDigBioProjectRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class ZooniversePusherHandlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 60;

    /**
     * @var array
     */
    private array $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->onQueue(config('config.queues.zooniverse_pusher_handler'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(
        PanoptesProjectRepository $panoptesProjectRepo,
        WeDigBioProjectRepository $weDigBioprojectRepo,
    ) {
        $panoptesProject = $panoptesProjectRepo->findByProjectIdAndWorkflowId($this->data['project_id'], $this->data['workflow_id']);
        $weDigBioProject = $weDigBioprojectRepo->findByProjectIdAndWorkflowId($this->data['project_id'], $this->data['workflow_id']);

        if ($panoptesProject === null && $weDigBioProject === null) {
            $this->delete();

            return;
        }

        $title = $weDigBioProject === null ? $panoptesProject->title : $weDigBioProject->title;
        $projectId = $weDigBioProject === null ? null : $panoptesProject->project_id;
        $expeditionId = $weDigBioProject === null ? null : $panoptesProject->expedition_id;

        ZooniverseClassificationJob::dispatch($this->data, $title);

        if ($expeditionId !== null) {
            ZooniverseBiospexEventJob::dispatch($this->data, $expeditionId);
        }

        if ($projectId !== null) {
            ZooniverseWeDigBioEventJob::dispatch($this->data, $projectId);
        }
    }
}
