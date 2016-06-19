<?php

namespace App\Providers;

use App\Listeners\FlushCacheEventListener;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\SendReportEvent;
use App\Listeners\SendReportEventListener;
use App\Events\SendInviteEvent;
use App\Listeners\SendInviteEventListener;
use App\Events\UserRegisteredEvent;
use App\Listeners\UserRegisteredEventListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'eloquent.saved: *' => [
            FlushCacheEventListener::class
        ],
        'eloquent.deleted: *' => [
            FlushCacheEventListener::class
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
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [];

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
