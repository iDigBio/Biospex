<?php
/**
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
use App\Services\Process\TranscriptionChartService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Model\ProjectService;

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
    public $timeout = 3600;

    /**
     * @var
     */
    protected $projectId;

    /**
     * AmChartJob constructor.
     *
     * @param int $projectId
     */
    public function __construct(int $projectId)
    {
        $this->projectId = $projectId;
        $this->onQueue(config('config.chart_tube'));
    }

    /**
     * Handle job.
     *
     * @param \App\Services\Model\ProjectService $projectService
     * @param \App\Services\Process\TranscriptionChartService $service
     */
    public function handle(ProjectService $projectService, TranscriptionChartService $service)
    {
        try {
            $project = $projectService->getProjectForAmChartJob($this->projectId);

            $service->process($project);

            $this->delete();
        }
        catch (Exception $e)
        {
            $user = User::find(1);

            $messages = [
                'Project Id: '.$this->projectId,
                'Message:' . $e->getFile() . ': ' . $e->getLine() . ' - ' . $e->getMessage()
            ];

            $user->notify(new JobError(__FILE__, $messages));
        }
    }
}
