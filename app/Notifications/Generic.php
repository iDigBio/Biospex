<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Generic extends Notification implements ShouldQueue
{
    use Queueable;

    private array $attributes;

    private bool $admin;

    /**
     * Create a new notification instance.
     *
     * @param array $attributes ['subject' => '', 'html' => '', 'buttons' => ['url' => ['color' => ''', 'text' => '']]]
     * color can be primary, success, and error
     */
    public function __construct(array $attributes, bool $admin = false)
    {
        $this->attributes = $attributes;
        $this->admin = $admin;
        $this->onQueue(config('config.queue.default'));
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = new MailMessage;

        $adminEmail = config('mail.from.address');
        if ($notifiable->email !== $adminEmail && $this->admin) {
            $mail->bcc($adminEmail);
        }

        $mail->subject($this->attributes['subject']);

        $this->attributes['message'] = implode('<br>', $this->attributes['html']);

        return $mail->markdown('mail.generic', $this->attributes);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [//
        ];
    }
}
