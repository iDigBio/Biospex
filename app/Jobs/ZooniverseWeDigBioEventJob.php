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

use App\Services\Event\WeDigBioEventService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class ZooniverseWeDigBioEventJob implements ShouldQueue
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
     * @var int
     */
    private int $projectId;

    /**
     * Create a new job instance.
     * Handles WeDigBio even transcriptions
     * @see \App\Jobs\ZooniversePusherHandlerJob
     *
     * @return void
     */
    public function __construct(array $data, int $projectId)
    {
        $this->data = $data;
        $this->projectId = $projectId;
        $this->onQueue(config('config.queues.wedigbio_event'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WeDigBioEventService $weDigBioEventService)
    {
        $weDigBioEventService->process($this->data, $this->projectId);
    }
}
