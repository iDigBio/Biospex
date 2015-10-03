<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Config\Repository as Config;

class SendContactEmail extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param Config $config
     * @param $request
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @param Config $config
     * @param Mail|Mailer $mailer
     */
    public function handle(Config $config, Mailer $mailer)
    {
        $emailAddress = $config->get('mail.from');
        $emailName = $config->get('mail.name');

        $input = $this->request->only('first_name', 'last_name', 'email', 'message');

        $data = [
            'firstName'    => $input['first_name'],
            'lastName'     => $input['last_name'],
            'email'        => $input['email'],
            'emailMessage' => $input['message'],
            'emailAddress' => $this->emailAddress
        ];

        $mailer->send('emails.contact', $data, function ($m) use($emailAddress, $emailName) {
            $m->to($emailAddress, $emailName)->subject(trans('emails.contact_subject'));
        });

        return;
    }

}