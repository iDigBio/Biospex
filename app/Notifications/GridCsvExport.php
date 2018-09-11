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
     * @var null
     */
    public $csv;

    /**
     * Create a new notification instance.
     *
     * @param $message
     * @param null $csv
     */
    public function __construct($message, $csv = null)
    {
        $this->message = $message;
        $this->csv = $csv;
        $this->onQueue(config('config.beanstalkd.default_tube'));
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
        $mailMessage = new MailMessage;
        $mailMessage->markdown('mail.gridcsvexport', ['message' => $this->message]);

        if ($this->csv !== null)
        {
            $mailMessage->attach($this->csv, [
                'mime' => 'text/csv',
            ]);
        }

        return $mailMessage;
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
