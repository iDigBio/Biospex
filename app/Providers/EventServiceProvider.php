<?php

namespace App\Providers;

use App\Events\UserLoggedInEvent;
use App\Listeners\UserLoggedInEventListener;
use App\Events\UserLoggedOutEvent;
use App\Listeners\UserLoggedOutEventListener;
use App\Events\FlushCacheEvent;
use App\Events\UserRegisteredEvent;
use App\Listeners\UserRegisteredEventListener;
use App\Events\LostPasswordEvent;
use App\Listeners\LostPasswordEventListener;
use App\Events\SendInviteEvent;
use App\Listeners\SendInviteEventListener;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'eloquent.saved: *' => [
            FlushCacheEvent::class
        ],
        'eloquent.deleted: *' => [
            FlushCacheEvent::class
        ],
        UserLoggedInEvent::class => [
            UserLoggedInEventListener::class
        ],
        UserLoggedOutEvent::class => [
            UserLoggedOutEventListener::class
        ],
        UserRegisteredEvent::class => [
            UserRegisteredEventListener::class
        ],
        LostPasswordEvent::class => [
            LostPasswordEventListener::class
        ],
        SendInviteEvent::class => [
            SendInviteEventListener::class
        ]

        /*
        $events->listen('user.resend', 'App\Mailer\BiospexMailer@welcome', 10);
        $events->listen('user.forgot', 'App\Mailer\BiospexMailer@forgotPassword', 10);
        $events->listen('user.newpassword', 'App\Mailer\BiospexMailer@newPassword', 10);
        $events->listen('user.sendreport', 'App\Mailer\BiospexMailer@sendReport', 10);
        $events->listen('user.sendinvite', 'App\Mailer\BiospexMailer@sendInvite', 10);
        $events->listen('user.sendcontact', 'App\Mailer\BiospexMailer@sendContactForm', 10);
        */
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        \Queue::failing(function ($connection, $job) {
            if ($job->getQueue() == \Config::get('config.beanstalkd.default')) {
                return;
            }

            \Event::fire('user.sendreport', [
                'email'   => null,
                'subject' => trans('emails.failed_job_subject'),
                'view'    => 'emails.report-failed-jobs',
                'data'    => ['text' => trans('emails.failed_job_message', ['id' => $job->getJobId(), 'jobData' => $job->getRawBody()])],
            ]);

            return;
        });
    }
}
