<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ScoreboardEvent extends Event implements ShouldBroadcast
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var array
     */
    public $data = [];

    /**
     * @var
     */
    public $projectId;

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
     * ScoreboardEvent constructor.
     *
     * @param $projectId
     * @param $data
     */
    public function __construct($projectId, $data)
    {
        $this->projectId = $projectId;
        $this->data = $data;
        $this->broadcastQueue = config('config.event_tube');
        $this->channel = config('config.poll_scoreboard_channel') . '.' . $this->projectId;
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
