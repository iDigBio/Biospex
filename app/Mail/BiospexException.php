<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BiospexException extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var
     */
    public $exception;

    /**
     * Create a new message instance.
     *
     * @param $exception
     */
    public function __construct($exception)
    {
        $this->exception = $exception;
        $this->onQueue(config('beanstalkd.default'));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mail.exception')->with('exception', $this->exception);
    }
}
