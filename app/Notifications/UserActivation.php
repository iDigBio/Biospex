<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UserActivation extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Route for button action.
     *
     * @var
     */
    private $route;

    /**
     * Create a new notification instance.
     *
     * @param $route
     */
    public function __construct($route)
    {
        $this->route = $route;
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
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        return (new MailMessage)
            ->line(trans('emails.activate_intro'))
            ->line(trans('emails.activate_message'))
            ->action(trans('buttons.activate'), $this->route);
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
