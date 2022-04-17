<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobComplete extends Notification
{
    use Queueable;

    /**
     * @var string
     */
    private $file;

    /**
     * @var array
     */
    private $messages;

    /**
     * @var
     */
    private $fileName;

    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    private $adminEmail;

    /**
     * Create a new notification instance.
     *
     * @param string $file
     * @param array $messages
     * @param string|null $fileName
     */
    public function __construct(string $file, array $messages = [],  string $fileName = null)
    {
        $this->messages = $messages;
        $this->file = $file;
        $this->fileName = $fileName;
        $this->adminEmail = config('mail.from.address');
        $this->onQueue(config('config.default_tube'));
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
        $message = implode('<br><br>', $this->messages);

        $mailMessage = new MailMessage;

        if($notifiable->email !== $this->adminEmail)
        {
            $mailMessage->bcc($this->adminEmail);
        }

        return $mailMessage->markdown('mail.jobcomplete', [
            'file' => $this->file,
            'message' => $message,
            'url' => isset($this->fileName) ? route('admin.downloads.report', ['file' => $this->fileName]) : null
        ]);
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
