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

use App\Models\Bingo;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class BingoEvent
 */
class BingoEvent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    protected Bingo $bingo;

    protected string $data;

    /**
     * BingoEvent constructor.
     */
    public function __construct(Bingo $bingo, array $data)
    {
        $this->bingo = $bingo;
        $this->data = json_encode($data);
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
        return new Channel(config('config.poll_bingo_channel').'.'.$this->bingo->id);
    }
}
