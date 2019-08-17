<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class GridCsvExport extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var string
     */
    public $message;

    /**
     * GridCsvExport constructor.
     *
     * @param $message
     */
    public function __construct($message)
    {
        $this->message = $message;
        $this->onQueue(config('config.default_tube'));
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail()
    {
        return (new MailMessage)->markdown('mail.gridcsvexport', ['message' => $this->message]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            //
        ];
    }
}
