<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BiospexExceptionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var array of error values: code, file, line, message, trace
     */
    public $error;

    /**
     * Create a new notification instance.
     *
     * @param array $error
     */
    public function __construct($error)
    {
        $this->error = $error;
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
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {

        return (new MailMessage)
            ->subject('Biospex Exception')
            ->line($this->error['message'] . ': ' . $this->error['file'] . ' ' . $this->error['line'])
            ->line($this->error['trace']);
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
