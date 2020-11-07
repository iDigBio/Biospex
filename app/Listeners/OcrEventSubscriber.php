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

use App\Models\OcrQueue;

/**
 * Class OcrEventSubscriber
 *
 * @package App\Listeners
 */
class OcrEventSubscriber
{
    /**
     * Register the listeners for the subscriber.
     *
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'ocr.error',
            'App\Listeners\OcrEventSubscriber@error'
        );

        $events->listen(
            'ocr.reset',
            'App\Listeners\OcrEventSubscriber@reset'
        );

        $events->listen(
            'ocr.status',
            'App\Listeners\OcrEventSubscriber@status'
        );
    }

    /**
     * Record error.
     *
     * @param \App\Models\OcrQueue $queue
     */
    public function error(OcrQueue $queue)
    {
        $queue->status = 0;
        $queue->error = 1;
        $queue->save();
    }

    /**
     * Reset queue record.
     *
     * @param \App\Models\OcrQueue $queue
     * @param $count
     */
    public function reset(OcrQueue $queue, $count)
    {
        $queue->total = $count;
        $queue->processed = 0;
        $queue->status = 1;
        $queue->save();
    }

    /**
     * Set status to zero.
     *
     * @param \App\Models\OcrQueue $queue
     */
    public function status(OcrQueue $queue)
    {
        $queue->status = 0;
        $queue->save();
    }
}