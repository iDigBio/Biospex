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

use App\Models\ActorExpedition;
use App\Models\User;
use App\Notifications\Generic;
use App\Services\Actor\Zooniverse\ZooniverseBuildQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

/**
 * Class ZooniverseExportBuildQueueJob
 */
class ZooniverseExportBuildQueueJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected ActorExpedition $actorExpedition)
    {
        $this->actorExpedition = $actorExpedition->withoutRelations();
        $this->onQueue(config('config.queue.default'));
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle(ZooniverseBuildQueue $zooniverseBuildQueue): void
    {
        $zooniverseBuildQueue->process($this->actorExpedition);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $throwable): void
    {
        $attributes = [
            'subject' => t('Zooniverse Export Build Queue Job Failed'),
            'html' => [
                t('An error occurred building the export queue.'),
                t('Actor Id: %s', $this->actorExpedition->actor_id),
                t('Expedition Id: %s', $this->actorExpedition->expedition_id),
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage()),
            ],
        ];

        $user = User::find(config('config.admin.user_id'));
        $user->notify(new Generic($attributes));
    }
}
