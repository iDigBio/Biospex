<?php

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
