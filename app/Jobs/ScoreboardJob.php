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

use App\Events\ScoreboardEvent;
use App\Services\Models\EventModelService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class ScoreboardJob
 *
 * @package App\Jobs
 */
class ScoreboardJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private int $projectId;

    /**
     * ScoreBoardJob constructor.
     *
     * @param int $projectId
     */
    public function __construct(int $projectId)
    {
        $this->projectId = $projectId;
        $this->onQueue(config('config.queue.event'));
    }

    /**
     * Job handle.
     *
     * @throws \Throwable
     */
    public function handle(EventModelService $eventModelService)
    {
        $events = $eventModelService->getEventsByProjectId($this->projectId);
        $data = $events->mapWithKeys(function($event) {
            return [$event->id => \View::make('common.scoreboard-content', ['event' => $event])->render()];
        });

        ScoreboardEvent::dispatch($this->projectId, $data->toArray());
    }
}
