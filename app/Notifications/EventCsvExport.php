<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EventCsvExport extends Notification implements ShouldQueue
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
        $this->onQueue(config('config.beanstalkd.default'));
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        $mailMessage = new MailMessage;
        $mailMessage->markdown('mail.eventcsvexport', ['message' => $this->message]);

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
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
