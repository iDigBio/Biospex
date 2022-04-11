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

/**
 * Class PanoptesPusherJob
 *
 * @package App\Jobs
 */
class PanoptesPusherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * @var array
     */
    private $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->onQueue(config('config.pusher_tube'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PanoptesProjectRepository $panoptesProjectRepo, WeDigBioProjectRepository $weDigBioprojectRepo)
    {
        $panoptesProject = $panoptesProjectRepo->findByProjectIdAndWorkflowId($this->data['project_id'], $this->data['workflow_id']);
        $weDigBioProject = $weDigBioprojectRepo->findByProjectIdAndWorkflowId($this->data['project_id'], $this->data['workflow_id']);

        if ($panoptesProject === null && $weDigBioProject === null){
            $this->delete();
            return;
        }

        $title = $weDigBioProject === null ? $panoptesProject->title : $weDigBioProject->title;

        PusherEventTranscriptionJob::dispatch($this->data);
        PusherClassificationJob::dispatch($this->data, $title);
    }
}
