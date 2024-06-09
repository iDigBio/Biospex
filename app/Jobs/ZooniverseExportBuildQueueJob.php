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

namespace App\Jobs;

use App\Models\Actor;
use App\Models\User;
use App\Notifications\Generic;
use App\Services\Actor\Zooniverse\Traits\ZooniverseErrorNotification;
use App\Services\Actor\Zooniverse\ZooniverseBuildQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

/**
 * Class ZooniverseExportBuildQueueJob
 *
 * @package App\Jobs
 */
class ZooniverseExportBuildQueueJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, ZooniverseErrorNotification;

    /**
     * @var \App\Models\Actor
     */
    private Actor $actor;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Actor $actor
     */
    public function __construct(Actor $actor)
    {
        $this->actor = $actor;
        $this->onQueue(config('config.queue.default'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Actor\Zooniverse\ZooniverseBuildQueue $zooniverseBuildQueue
     * @throws \Exception
     */
    public function handle(ZooniverseBuildQueue $zooniverseBuildQueue)
    {
        $zooniverseBuildQueue->process($this->actor);

        event('exportQueue.check');
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $throwable
     * @return void
     */
    public function failed(Throwable $throwable): void
    {
        $attributes = [
            'subject' => t('Zooniverse Export Build Queue Job Failed'),
            'html'    => [
                t('An error occurred building the export queue.'),
                t('Actor Id: %s', $this->actor->id),
                t('Expedition Id: %s', $this->actor->pivot->id),
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage())
            ],
        ];

        $user = User::find(config('config.admin.user_id'));
        $user->notify(new Generic($attributes));
    }
}
