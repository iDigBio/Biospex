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

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ramsey\Uuid\Uuid;

/**
 * Class AppCommand
 */
class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle()
    {
        echo Uuid::uuid4()->toString().PHP_EOL;
        echo Uuid::uuid4()->toString().PHP_EOL;
        echo Uuid::uuid4()->toString().PHP_EOL;

        $expedition = \App\Models\Expedition::with('actors')->find(308);

        $expedition->actors->each(function ($actor) {
            if ($actor->id == config('zooniverse.actor_id')) {
                $attributes = [
                    'total' => 485,
                ];

                $actor->pivot->update($attributes);

                //$actor->expeditions()->updateExistingPivot($expedition->id, $attributes);
                dd($actor->pivot->id);
            }
        });
        $this->info($expedition->actors->count());

        /*
        $results = Expedition::with(['panoptesProject', 'stat', 'zooniverseActor'])
            ->has('panoptesProject')->whereHas('actors', function ($q) {
                $q->zooniverse();
            })->where('completed', 0)->find(308);

        $this->info($results->zooActor->id);
        */

        /*
        $results = Expedition::has('panoptesProject')->whereHas('actors', function ($q) {
            $q->zooniverse();
        })->where('completed', 0)->get();
        */

        //$this->info($results->count());
        /*
        $expedition = \App\Models\Expedition::with('zooActor')->find(17);
        $this->info('Expedition: '.$expedition->zooActor->pivot->state);

        $attributes = [
            'state' => 3,
            'error' => 0,
        ];

        $expedition->zooActor->pivot->update($attributes);

        //$expedition->actors()->updateExistingPivot($expedition->zooActor->pivot->actor_id, $attributes);
        */
    }
}
