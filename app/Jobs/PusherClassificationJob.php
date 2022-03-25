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

use App\Services\Process\PusherDashboardService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class PusherClassificationJob
 *
 * @package App\Jobs
 */
class PusherClassificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 7200;

    /**
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private string $title;

    /**
     * Create a new job instance.
     *
     * @param array $data
     * @param string $title
     */
    public function __construct(array $data, string $title)
    {
        $this->data = $data;
        $this->title = $title;
        $this->onQueue(config('config.pusher_process_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Process\PusherDashboardService $service
     * @throws \Exception
     */
    public function handle(PusherDashboardService $service)
    {
        $service->process($this->data, $this->title);

        $this->delete();
    }
}
