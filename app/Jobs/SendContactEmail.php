<?php namespace Biospex\Jobs;

use Illuminate\Queue\SerializesModels;
use Biospex\Services\Mailer\BiospexMailer;
use Illuminate\Contracts\Config\Repository as Config;

class SendContactEmail extends Job
{
    use SerializesModels;

    public $data;

    /**
     * SendContactEmail constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @param Config $config
     * @param Mail|Mailer $mailer
     */
    public function handle(BiospexMailer $mailer)
    {
        $data = [
            'firstName'    => $this->data['first_name'],
            'lastName'     => $this->data['last_name'],
            'email'        => $this->data['email'],
            'emailMessage' => $this->data['message']
        ];

        $mailer->sendContact($data);

        return;
    }

}