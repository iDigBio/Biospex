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

use App\Models\Project;
use App\Models\Expedition;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\DB;

/**
 * Class UpdateQueries
 *
 * @package App\Console\Commands
 */
class UpdateQueries extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'update:queries {method?}';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';

    /**
     * UpdateQueries constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Fire command
     */
    public function handle()
    {
        if (! is_null($this->argument('method'))) {
            $method = $this->argument('method');
            $this->{$method}();
        }
    }

    private function moveProjectWorkflow()
    {
        Project::all()->each(function ($project) {
            DB::table('project_old_workflow')->insert(['project_id'  => $project->id,
                                                       'workflow_id' => $project->workflow_id,
            ]);
        });
    }

    private function expeditionWorkflowId()
    {
        Project::all()->each(function ($project) {
            Expedition::where('project_id', $project->id)->get()->each(function($expedition) use($project){
                $expedition->workflow_id = $project->workflow_id;
                $expedition->save();
            });
        });
    }

    private function updateWorkflowIds()
    {
        // Ids not used: 1, 2, 4
        // Ids used: 3, 5 (3 & 5 will be the same = Zooniverse)
        Expedition::all()->each(function($expedition){
            $expedition->workflow_id = 3;
            $expedition->save();
        });

    }

    private function updateExpeditionCompleted()
    {
        Expedition::with('actors')->get()->each(function($expedition){
            $expedition->actors->each(function($actor) use($expedition) {
                $expedition->completed = $actor->pivot->completed;
                $expedition->save();
            });
        });
    }
}