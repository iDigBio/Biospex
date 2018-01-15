<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewNfnLegacyProject extends Notification implements ShouldQueue
{

    use Queueable;

    /**
     * @var
     */
    private $project;

    /**
     * Create a new notification instance.
     *
     * @param $project
     */
    public function __construct($project)
    {
        $this->project = $project;
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
        $vars = [
            'message'     => trans('emails.nfn_notification'),
            'contact'     => $this->project->contact,
            'email'       => $this->project->contact_email,
            'title'       => $this->project->title,
            'description' => $this->project->description_long
        ];

        return (new MailMessage)
            ->subject(trans('emails.nfn_notification_subject'))
            ->markdown('mail.newnfnlegacy', $vars);
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
