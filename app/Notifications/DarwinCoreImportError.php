<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class DarwinCoreImportError extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    private $adminEmail;

    /**
     * @var string
     */
    private $message;

    /**
     * Create a new notification instance.
     *
     * @param string $message
     */
    public function __construct($message)
    {
        $this->message = $message;
        $this->adminEmail = config('mail.from.address');
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
            ->bcc($this->adminEmail)
            ->markdown('mail.importerror', ['message' => $this->message]);
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
