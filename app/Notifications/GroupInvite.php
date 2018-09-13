<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class GroupInvite extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var
     */
    public $group;

    /**
     * Create a new notification instance.
     *
     * @param $group
     */
    public function __construct($group)
    {
        $this->group = $group;
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
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = route('app.get.register', ['code' => $notifiable->code]);

        return (new MailMessage)
            ->subject(trans('messages.group_invite_subject'))
            ->markdown('mail.invite', ['url' => $url, 'title' => $this->group->title]);
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
