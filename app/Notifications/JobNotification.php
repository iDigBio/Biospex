<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobNotification extends Notification
{
    use Queueable;

    /**
     * @var string
     */
    private string $subject;

    /**
     * @var array
     */
    private array $message;

    /**
     * @var string|null
     */
    private ?string $fileUrl;

    /**
     * @var string
     */
    private string $adminEmail;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $subject, array $message, string $fileUrl = null)
    {
        $this->subject = $subject;
        $this->message = $message;
        $this->fileUrl = $fileUrl;
        $this->adminEmail = config('mail.from.address');
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
        $mailMessage = (new MailMessage)->subject($this->subject);

        $message = implode('<br><br>', $this->message);

        if($notifiable->email !== $this->adminEmail)
        {
            $mailMessage->bcc($this->adminEmail);
        }

        return $mailMessage->markdown('mail.jobnotification', [
            'subject' => $this->subject,
            'message' => $message,
            'url' => $this->fileUrl === null ? null : $this->fileUrl
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
