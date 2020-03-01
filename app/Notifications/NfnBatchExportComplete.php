<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NfnBatchExportComplete extends Notification implements ShouldQueue
{

    use Queueable;

    /**
     * @var string
     */
    private $message;

    /**
     * @var array
     */
    private $links;

    /**
     * NfnBatchExportComplete constructor.
     *
     * @param string $message
     * @param array $links
     */
    public function __construct(string $message, array $links)
    {
        $this->message = $message;
        $this->links = $links;
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

        $mailMessage->subject(__('pages.notice_subject_batch_export_complete'));

        $content = [
            'message' => $this->message,
            'links' => implode("<br>", $this->links)
        ];

        return $mailMessage->markdown('mail.nfnbatchexportcomplete', $content);
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
