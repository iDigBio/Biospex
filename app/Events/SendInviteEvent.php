<?php namespace App\Events;

use Illuminate\Queue\SerializesModels;

class SendInviteEvent extends Event
{
    use SerializesModels;

    public $data;

    /**
     * Create a new event instance.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
}
