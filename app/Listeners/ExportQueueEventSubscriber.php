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

namespace App\Listeners;

use App\Repositories\Interfaces\ExportQueue;
use App\Jobs\ExportQueueJob;

/**
 * Class ExportQueueEventSubscriber
 *
 * @package App\Listeners
 */
class ExportQueueEventSubscriber
{
    /**
     * @var ExportQueue
     */
    private $exportQueue;

    /**
     * ExportQueueEventSubscriber constructor.
     *
     * @param \App\Repositories\Interfaces\ExportQueue $exportQueue
     */
    public function __construct(ExportQueue $exportQueue)
    {
        $this->exportQueue = $exportQueue;
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen('exportQueue.updated', 'App\Listeners\ExportQueueEventSubscriber@updated');
    }

    /**
     * Entity Updated.
     *
     * @see ExportQueueRepository::getFirstExportWithoutError() Get first record with no error.
     */
    public function updated()
    {

        $record = $this->exportQueue->getFirstExportWithoutError();

        if ($record === null) {
            return;
        }

        if ($record->queued) {
            ExportQueueJob::dispatch($record);

            return;
        }

        if (! $record->queued) {
            $record->queued = 1;
            $record->save();

            ExportQueueJob::dispatch($record);

            return;
        }
    }
}

