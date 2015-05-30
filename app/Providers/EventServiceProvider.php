<?php namespace Biospex\Providers;

use Biospex\Events\UserLoggedInEvent;
use Biospex\Handlers\Events\UserLoggedInEventHandler;
use Biospex\Events\UserLoggedOutEvent;
use Biospex\Handlers\Events\UserLoggedOutEventHandler;
use Biospex\Events\FlushCacheEvent;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider {

	/**
	 * The event handler mappings for the application.
	 *
	 * @var array
	 */
	protected $listen = [
        UserLoggedInEvent::class => [
            UserLoggedInEventHandler::class
        ],
        UserLoggedOutEvent::class => [
            UserLoggedOutEventHandler::class
        ],
        'eloquent.saved: *' => [
			FlushCacheEvent::class
		],
        'eloquent.deleted: *' => [
            FlushCacheEvent::class
        ],
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

        \Queue::failing(function ($connection, $job)
        {
            if ($job->getQueue() == \Config::get('config.beanstalkd.default'))
                return;

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
