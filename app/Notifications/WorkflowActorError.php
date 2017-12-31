<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class WorkflowActorError extends Notification
{
    use Queueable;

    /**
     * @var string
     */
    private $message;

    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    private $adminEmail;

    /**
     * Create a new notification instance.
     *
     * @param string $message
     */
    public function __construct($message)
    {
        $this->message = $message;
        $this->adminEmail = config('mail.from.address');
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
            ->bcc($this->adminEmail)
            ->markdown('mail.workflowactorerror', ['message' => $this->message]);
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
