<?php

namespace App\Providers;

use App\Listeners\ActorPivotUpdateEventListener;
use App\Listeners\ExportQueueEventListener;
use App\Listeners\GroupEventListener;
use App\Listeners\OcrEventListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [];

    /**
     * Register any events for your application.
     *
     * @var array
     */
    protected $subscribe = [
        GroupEventListener::class,
        ActorPivotUpdateEventListener::class,
        ExportQueueEventListener::class,
        OcrEventListener::class
    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
