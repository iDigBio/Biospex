<?php

namespace Biospex\Providers;

use Biospex\Events\FlushCacheEvent;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Biospex\Events\SendReportEvent;
use Biospex\Listeners\SendReportEventListener;
use Biospex\Events\SendInviteEvent;
use Biospex\Listeners\SendInviteEventListener;
use Biospex\Events\UserRegisteredEvent;
use Biospex\Listeners\UserRegisteredEventListener;

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
        SendReportEvent::class => [
            SendReportEventListener::class
        ],
        SendInviteEvent::class => [
            SendInviteEventListener::class
        ],
        UserRegisteredEvent::class => [
            UserRegisteredEventListener::class
        ]
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
    }
}
