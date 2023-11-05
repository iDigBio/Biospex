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

use App\Models\Actor;
use App\Models\Project;
use App\Models\Expedition;
use App\Models\Workflow;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        $this->saveProjectWorkflowOrigAndMoveToExpedition();
        $this->addWorkflowForeignIdToExpedition();
        $this->dropWorkflowIdFromProjects();
        $this->updateExpeditionCompleted();
        $this->dropCompletedFromActorExpedition();
        $this->updateWorkflowIds();
        $this->deleteActors();
        $this->addActorGeoLocate();
        $this->deleteWorkflows();
        $this->addWorkflowGeoLocate();
        $this->updateActorExpeditionState();
        $this->alterBingoMapIp();
        $this->alterEventTransactionIds();
    }

    private function saveProjectWorkflowOrigAndMoveToExpedition(): void
    {
        echo 'Running '.__METHOD__.PHP_EOL;

        $projects = DB::table('projects')->select('id', 'workflow_id')->get();
        $projects->each(function ($project) {
            DB::table('project_old_workflow')->insert([
                'project_id'  => $project->id,
                'workflow_id' => $project->workflow_id,
            ]);

            Expedition::where('project_id', $project->id)->get()->each(function ($expedition) use ($project) {
                $expedition->workflow_id = $project->workflow_id;
                $expedition->save();
            });
        });
    }

    public function addWorkflowForeignIdToExpedition(): void
    {
        echo 'Running '.__METHOD__.PHP_EOL;

        Schema::table('expeditions', function (Blueprint $table) {
            $table->foreign('workflow_id')->references('id')->on('workflows')->nullable()->constrained()->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }

    public function dropWorkflowIdFromProjects(): void
    {
        echo 'Running '.__METHOD__.PHP_EOL;

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign('projects_workflow_id_foreign');
            $table->dropColumn('workflow_id');
        });
    }

    private function updateExpeditionCompleted()
    {
        echo 'Running '.__METHOD__.PHP_EOL;

        $results = DB::table('expeditions')->select('expeditions.id', 'actor_expedition.actor_id', 'actor_expedition.completed')->join('actor_expedition', 'actor_expedition.expedition_id', '=', 'expeditions.id')->get();

        $results->each(function ($result) {
            if ($result->completed === 1) {
                $expedition = Expedition::find($result->id);
                $expedition->completed = 1;
                $expedition->save();

                $expedition->actors()->updateExistingPivot($result->actor_id, ['state' => 2]);
            }
        });
    }

    public function dropCompletedFromActorExpedition(): void
    {
        echo 'Running '.__METHOD__.PHP_EOL;

        Schema::table('actor_expedition', function (Blueprint $table) {
            $table->dropColumn('completed');
        });
    }

    private function updateWorkflowIds()
    {
        echo 'Running '.__METHOD__.PHP_EOL;
        // Ids not used: 1, 2, 4
        // Ids used: 3, 5 (3 & 5 will be the same = Zooniverse)
        Expedition::all()->each(function ($expedition) {
            $expedition->workflow_id = 3;
            $expedition->save();
        });
    }

    private function deleteActors()
    {
        echo 'Running '.__METHOD__.PHP_EOL;

        Actor::all()->each(function ($actor) {
            if ($actor->id === 2) {
                return;
            }

            $actor->delete();
        });
    }

    private function addActorGeoLocate()
    {
        echo 'Running '.__METHOD__.PHP_EOL;

        Actor::create(['title' => 'GeoLocateExport', 'url' => 'https://www.geo-locate.org/', 'class' => 'GeoLocateExport']);
    }

    private function deleteWorkflows()
    {
        echo 'Running '.__METHOD__.PHP_EOL;

        Workflow::all()->each(function ($workflow) {
            if ($workflow->id === 3) {
                return;
            }

            $workflow->delete();
        });
    }

    private function addWorkflowGeoLocate()
    {
        echo 'Running '.__METHOD__.PHP_EOL;

        $workflow = Workflow::create(['title' => 'Zooniverse -> GeoLocateExport', 'enabled' => 1]);
        $sync = [
            2 => ['order' => 1],
            4 => ['order' => 2],
        ];
        $workflow->actors()->sync($sync, false);
    }

    public function updateActorExpeditionState()
    {
        echo 'Running '.__METHOD__.PHP_EOL;

        $expeditions = Expedition::with(['actors', 'zooniverseExport'])->get();
        $expeditions->each(function ($expedition) {
            $expedition->actors->each(function ($actor) use ($expedition) {
                if ($actor->pivot->state === 2) {
                    $expedition->nfnActor()->updateExistingPivot($actor->id, ['state' => 3]);
                }
                if ($actor->pivot->state === 1) {
                    $expedition->nfnActor()->updateExistingPivot($actor->id, ['state' => 2]);
                }
                if ($actor->pivot->state === 0 && $expedition->zooniverseExport !== null) {
                    $expedition->nfnActor()->updateExistingPivot($actor->id, ['state' => 1]);
                }
            });
        });
    }

    public function alterBingoMapIp()
    {
        echo 'Running '.__METHOD__.PHP_EOL;
        DB::raw("ALTER TABLE `bingo_maps` CHANGE `ip` `ip` VARCHAR(30) NOT NULL;");
    }

    public function alterEventTransactionIds()
    {
        echo 'Running '.__METHOD__.PHP_EOL;

        DB::raw("ALTER TABLE `event_transcriptions` CHANGE `team_id` `team_id` INT UNSIGNED NOT NULL, CHANGE `user_id` `event_user_id` INT UNSIGNED NOT NULL; ");
    }
}