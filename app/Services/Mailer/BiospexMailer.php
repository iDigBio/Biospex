<?php namespace App\Services\Mailer;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class BiospexMailer
{
    /**
     * @var
     */
    public $emailAddress;

    /**
     * @var Config
     */
    public $config;

    /**
     * BiospexMailer constructor.
     */
    public function __construct()
    {
        $this->emailAddress = Config::get('mail.from');
    }

    /**
     * Send mail
     * @param $email
     * @param $subject
     * @param $view
     * @param $data
     * @param array $attachments
     * @return mixed
     */
    public function send($email, $subject, $view, $data, $attachments = [])
    {
        return Mail::queueOn(Config::get('config.beanstalkd.default'), $view, $data, function ($message) use ($email, $subject, $attachments) {
            $message->from($this->emailAddress['address'], $this->emailAddress['name'])->subject($subject)->to($email);
            $size = sizeof($attachments);
            for ($i = 0; $i < $size; $i++) {
                $message->attach($attachments[$i]);
            }
        });
    }

    /**
     * Send welcome email
     * @param $email
     * @param $data
     * @return mixed
     */
    public function sendWelcome($email, $data)
    {
        return $this->send($email, trans('users.welcome'), 'front.emails.welcome', $data);
    }

    /**
     * Send contact form
     * @param $data
     * @return mixed
     */
    public function sendContact($data)
    {
        $subject = trans('emails.contact_subject');
        $view = 'front.emails.contact';

        return $this->send($this->emailAddress['address'], $subject, $view, $data);
    }

    /**
     * Send report
     * @param $email
     * @param $subject
     * @param $view
     * @param $data
     * @param array $attachments
     * @return mixed
     */
    public function sendReport($email, $subject, $view, $data, $attachments = [])
    {
        if (is_null($email)) {
            $email = $this->emailAddress['address'];
        }

        return $this->send($email, $subject, $view, $data, $attachments);
    }

    /**
     * Send invite to group
     * @param $data
     * @return mixed
     */
    public function sendInvite($data)
    {
        $subject = trans('emails.group_invite_subject');
        $view = 'front.emails.group-invite';

        return $this->send($data['email'], $subject, $view, $data);
    }

}
