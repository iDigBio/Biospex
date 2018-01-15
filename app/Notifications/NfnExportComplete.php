<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NfnExportComplete extends Notification implements ShouldQueue
{

    use Queueable;

    /**
     * @var array
     */
    private $message;

    /**
     * @var null
     */
    private $csv;

    /**
     * Create a new notification instance.
     *
     * @param array $message
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

        $mailMessage->subject(trans('emails.biospex_export_completed'));

        if ($this->csv !== null)
        {
            $mailMessage->attachData($this->csv, 'missingImages.csv', [
                'mime' => 'text/csv',
            ]);
        }

        $message = implode('<br />', $this->message);

        return $mailMessage->markdown('mail.nfnexportcomplete', ['message' => $message]);
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
