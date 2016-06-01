<?php 

namespace App\Jobs;

use Illuminate\Queue\SerializesModels;
use App\Services\Mailer\BiospexMailer;
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
     * @param BiospexMailer $mailer
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
    }

}