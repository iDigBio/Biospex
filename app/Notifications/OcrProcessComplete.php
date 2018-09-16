<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class OcrProcessComplete extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var string Title of project
     */
    private $title;

    /**
     * @var string Csv of ocr results.
     */
    private $csv;

    /**
     * Create a new notification instance.
     *
     * @param $title
     * @param $csv
     */
    public function __construct($title, $csv = null)
    {
        $this->title = $title;
        $this->csv = $csv;
        $this->onQueue(config('config.default_tube'));
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
        $mailMessage = new MailMessage;
        $mailMessage->markdown('mail.ocrprocesscomplete', ['title' => $this->title]);

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
