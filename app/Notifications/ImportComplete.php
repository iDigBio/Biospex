<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ImportComplete extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var string
     */
    public $project;

    /**
     * @var string|null
     */
    public $duplicates;

    /**
     * @var string|null
     */
    public $rejects;

    /**
     * Create a new notification instance.
     *
     * @param $project
     * @param $duplicates
     * @param $rejects
     */
    public function __construct($project, $duplicates = null, $rejects = null)
    {
        $this->project = $project;
        $this->duplicates = $duplicates;
        $this->rejects = $rejects;
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
        $mailMessage = new MailMessage;
        $mailMessage->markdown('mail.importcomplete', ['project' => $this->project]);

        if ($this->duplicates !== null)
        {
            $mailMessage->attach($this->duplicates, [
                'as' => 'duplicates.csv',
                'mime' => 'text/csv',
            ]);
        }

        if ($this->rejects !== null)
        {
            $mailMessage->attach('/path/to/file', [
                'as' => 'rejects.csv',
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
