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

use App\Models\Event;
use Illuminate\Console\Command;

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
        $event = Event::find(3);

        $event->loadCount('transcriptions')->load([
            'project:id,title,slug,logo_file_name',
            'project.lastPanoptesProject:id,project_id,panoptes_project_id,panoptes_workflow_id',
            'teams:id,uuid,event_id,title', 'teams.users' => function ($q) use ($event) {
                $q->withcount([
                    'transcriptions' => function ($q) use ($event) {
                        $q->where('event_id', $event->id);
                    },
                ]);
            },
        ]);

        dd($event);
    }
}
