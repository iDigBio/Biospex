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

use App\Models\User;
use App\Notifications\JobError;
use App\Services\Actor\ActorFactory;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ActorJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var \App\Models\Actor
     */
    private $actor;

    /**
     * ActorJob constructor.
     *
     * @param $actor string
     */
    public function __construct($actor)
    {
        $this->onQueue(config('config.workflow_tube'));
        $this->actor = unserialize($actor);
    }

    /**
     * Handle Job.
     */
    public function handle()
    {
        try
        {
            $actorClass = ActorFactory::create($this->actor->class);
            $actorClass->actor($this->actor);
            $this->delete();
        }
        catch (Exception $e)
        {
            event('actor.pivot.error', $this->actor);

            $user = User::find(1);
            $message = [
                'Actor:' . $this->actor->id,
                'Expedition: ' . $this->actor->pivot->expedition_id,
                'Message:' . $e->getFile() . ': ' . $e->getLine() . ' - ' . $e->getMessage()
            ];
            $user->notify(new JobError(__FILE__, $message));

            $this->delete();
        }
    }
}

