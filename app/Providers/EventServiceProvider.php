<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Providers;

use App\Events\ImageExportEvent;
use App\Events\LabelReconciliationEvent;
use App\Events\TesseractOcrEvent;
use App\Listeners\GroupEventSubscriber;
use App\Listeners\ImageExportListener;
use App\Listeners\LabelReconciliationListener;
use App\Listeners\TesseractOcrListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Class EventServiceProvider
 *
 * @package App\Providers
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class               => [
            SendEmailVerificationNotification::class,
        ],
        LabelReconciliationEvent::class => [
            LabelReconciliationListener::class,
        ],
        ImageExportEvent::class         => [
            ImageExportListener::class,
        ],
        TesseractOcrEvent::class        => [
            TesseractOcrListener::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @var array
     */
    protected $subscribe = [
        GroupEventSubscriber::class,
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
