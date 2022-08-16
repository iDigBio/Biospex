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

use App\Models\Actor;
use App\Repositories\ExportQueueFileRepository;
use App\Repositories\ExportQueueRepository;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Class ZooniverseProcessCsvJob
 *
 * @package App\Jobs
 */
class ZooniverseExportCheckImageProcessJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * @var \App\Models\Actor
     */
    private Actor $actor;

    /**
     * Create a new job instance.
     *
     */
    public function __construct()
    {
        //$this->actor = $actor;
    }

    /**
     * Execute the job.
     *
     * @param \App\Repositories\ExportQueueFileRepository $exportQueueFileRepository
     * @param \App\Repositories\ExportQueueRepository $exportQueueRepository
     * @return void
     * @throws \Exception
     */
    public function handle(
        ExportQueueFileRepository $exportQueueFileRepository,
        ExportQueueRepository $exportQueueRepository
    )
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        \Log::alert('Check Image Process');
        return;

        $queue = $exportQueueRepository->findByExpeditionAndActorId($this->actor->pivot->expedition_id, $this->actor->id);
        $queue->stage = 2;
        $queue->save();

        $count = $exportQueueFileRepository->getUncompletedCount($queue->id);

        if ($count === 0) {
            return;
        }

        if ($this->attempts() > 3) {
            throw new \Exception(t('Queue %s exceeded number of tries.', $queue->id));
        }

        $this->release(900);
    }


}
