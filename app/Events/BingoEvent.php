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

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BingoEvent extends Event implements ShouldBroadcast
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \Illuminate\Support\Collection
     */
    public $data;

    /**
     * @var int
     */
    public $bingoId;

    /**
     * The name of the queue on which to place the event.
     *
     * @var string
     */
    public $broadcastQueue;

    /**
     * @var
     */
    public $channel;

    /**
     * BingoEvent constructor.
     *
     * @param int $bingoId
     * @param array $data
     */
    public function __construct(int $bingoId, array $data)
    {
        $this->bingoId = $bingoId;
        $this->data = json_encode($data);
        $this->broadcastQueue = config('config.event_tube');
        $this->channel = config('config.poll_bingo_channel') . '.' . $this->bingoId;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn()
    {
        return new Channel($this->channel);
    }
}
