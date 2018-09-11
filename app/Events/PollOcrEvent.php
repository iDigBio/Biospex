<?php 

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PollOcrEvent extends Event implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var array
     */
    public $data = [];

    /**
     * The name of the queue on which to place the event.
     *
     * @var string
     */
    public $broadcastQueue;

    /**
     * PollOcrEvent constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->broadcastQueue = config('config.beanstalkd.event_tube');
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn()
    {
        return new Channel(config('config.poll_ocr_channel'));
    }
}
