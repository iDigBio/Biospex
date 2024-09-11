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

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Class ScoreboardEvent
 */
class ScoreboardEvent extends Event implements ShouldBroadcast
{
    use Dispatchable;

    public array $data = [];

    public $projectId;

    /**
     * ScoreboardEvent constructor.
     */
    public function __construct($projectId, $data)
    {
        $this->projectId = $projectId;
        $this->data = $data;
    }

    /**
     * The name of the queue on which to place the broadcasting job.
     */
    public function broadcastQueue(): string
    {
        return config('config.queue.event');
    }

    /**
     * Get the channels the event should be broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel(config('config.poll_scoreboard_channel').'.'.$this->projectId);
    }
}
