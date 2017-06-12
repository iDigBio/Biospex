<?php 

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PollOcrEvent extends Event implements ShouldBroadcast
{
    /**
     * @var array
     */
    public $data = [];

    /**
     * PollOcrEvent constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Set the name of the queue the event should be placed on.
     *
     * @return string
     */
    public function onQueue()
    {
        return config('config.beanstalkd.event');
    }

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'app.polling';
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [config('config.poll_ocr_channel')];
    }
}
