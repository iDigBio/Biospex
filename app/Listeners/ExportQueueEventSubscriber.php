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

namespace App\Listeners;

use App\Jobs\ExportQueueJob;
use App\Repositories\ExportQueueRepository;

/**
 * Class ExportQueueEventSubscriber
 *
 * @package App\Listeners
 */
class ExportQueueEventSubscriber
{
    /**
     * @var \App\Repositories\ExportQueueRepository
     */
    private ExportQueueRepository $exportQueueRepo;

    /**
     * ExportQueueEventSubscriber constructor.
     *
     * @param \App\Repositories\ExportQueueRepository $exportQueueRepo
     */
    public function __construct(ExportQueueRepository $exportQueueRepo)
    {
        $this->exportQueueRepo = $exportQueueRepo;
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen('exportQueue.check', 'App\Listeners\ExportQueueEventSubscriber@check');
        $events->listen('exportQueue.retry', 'App\Listeners\ExportQueueEventSubscriber@retry');
    }

    /**
     * Check queue.
     */
    public function check()
    {
        $exportQueue = $this->exportQueueRepo->findBy('error', 0);

        if ($exportQueue === null || $exportQueue->queued) {
            return;
        }

        $exportQueue->queued = 1;
        $exportQueue->save();

        ExportQueueJob::dispatch($exportQueue);
    }

    /**
     * Retry currently queued expedition.
     *
     * @return void
     */
    public function retry()
    {
        $exportQueue = $this->exportQueueRepo->getQueueForRetry();

        if ($exportQueue === null) {
            return;
        }

        $exportQueue->error = 0;
        $exportQueue->processed = 0;
        $exportQueue->save();

        ExportQueueJob::dispatch($exportQueue);
    }
}

