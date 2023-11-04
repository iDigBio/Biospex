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

use App\Models\User;
use App\Notifications\JobError;
use App\Repositories\ProjectRepository;
use App\Services\Chart\TranscriptionChartService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Class AmChartJob
 *
 * @package App\Jobs
 */
class AmChartJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 3600;

    /**
     * @var int
     */
    protected int $projectId;

    /**
     * AmChartJob constructor.
     *
     * @param int $projectId
     */
    public function __construct(int $projectId)
    {
        $this->projectId = $projectId;
        $this->onQueue(config('config.queues.chart'));
    }

    /**
     * Handle job.
     *
     * @param \App\Repositories\ProjectRepository $projectRepo
     * @param \App\Services\Chart\TranscriptionChartService $service
     */
    public function handle(ProjectRepository $projectRepo, TranscriptionChartService $service): void
    {
        $project = $projectRepo->getProjectForAmChartJob($this->projectId);

        $service->process($project);

        $this->delete();
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $throwable
     * @return void
     */
    public function failed(\Throwable $throwable): void
    {
        $user = User::find((int) config('config.admin_user_id'));
        $messages = [
            t('Error: %s', $throwable->getMessage()),
            t('File: %s', $throwable->getFile()),
            t('Line: %s', $throwable->getLine()),
        ];
        $user->notify(new JobError(__FILE__, $messages));
    }
}
