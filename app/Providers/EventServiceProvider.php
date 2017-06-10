<?php

namespace App\Providers;

use App\Events\SendErrorEvent;
use App\Events\SendInviteEvent;
use App\Events\SendReportEvent;
use App\Events\UserRegisteredEvent;
use App\Listeners\ActorPivotUpdateEventListener;
use App\Listeners\DatabaseCacheEventListener;
use App\Listeners\GroupEventListener;
use App\Listeners\RepositoryEventListener;
use App\Listeners\SendErrorEventListener;
use App\Listeners\SendInviteEventListener;
use App\Listeners\SendReportEventListener;
use App\Listeners\ExportQueueEventListener;
use App\Listeners\UserRegisteredEventListener;
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
        SendReportEvent::class => [
            SendReportEventListener::class
        ],
        SendInviteEvent::class => [
            SendInviteEventListener::class
        ],
        UserRegisteredEvent::class => [
            UserRegisteredEventListener::class
        ],
        SendErrorEvent::class => [
            SendErrorEventListener::class
        ]
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        GroupEventListener::class,
        DatabaseCacheEventListener::class,
        RepositoryEventListener::class,
        ExportQueueEventListener::class,
        ActorPivotUpdateEventListener::class
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
