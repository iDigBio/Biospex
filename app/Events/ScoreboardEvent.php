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
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * Class ScoreboardEvent
 *
 * @package App\Events
 */
class ScoreboardEvent extends Event implements ShouldBroadcast
{

    use Dispatchable;

    /**
     * @var array
     */
    public array $data = [];

    /**
     * @var
     */
    public $projectId;

    /**
     * ScoreboardEvent constructor.
     *
     * @param $projectId
     * @param $data
     */
    public function __construct($projectId, $data)
    {
        $this->projectId = $projectId;
        $this->data = $data;
    }

    /**
     * The name of the queue on which to place the broadcasting job.
     *
     * @return string
     */
    public function broadcastQueue(): string
    {
        return config('config.queue.event');
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn()
    {
        return new Channel(config('config.poll_board_channel') . '.' . $this->projectId);
    }
}
