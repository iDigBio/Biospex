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

use App\Services\Models\WorkflowManagerModelService;
use App\Services\Actor\ActorFactory;
use Illuminate\Console\Command;

/**
 * Class WorkFlowManagerCommand
 *
 * @package App\Console\Commands
 */
class WorkFlowManagerCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'workflow:manage {expeditionId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Workflow manager";

    /**
     * @var mixed
     */
    public mixed $tube;

    /**
     * WorkFlowManagerCommand constructor.
     *
     * @param \App\Services\Models\WorkflowManagerModelService $workflowManagerModelService
     */
    public function __construct(private readonly WorkflowManagerModelService $workflowManagerModelService)
    {
        parent::__construct();
        $this->tube = config('config.queue.workflow');
    }

    /**
     * Execute the console command.
     *
     * @see WorkflowManagerRepository::getWorkflowManagersForProcessing() Filters out error, queued, completed.
     * @return void
     * @see WorkflowManagerRepository::getWorkflowManagersForProcessing() Filters out error, queued, completed.
     */
    public function handle()
    {
        $expeditionId = $this->argument('expeditionId');

        $managers = $this->workflowManagerModelService->getWorkflowManagersForProcessing($expeditionId);

        if ($managers->isEmpty())
        {
            return;
        }

        $managers->each(function ($manager)
        {
            $this->processActors($manager->expedition);
        });
    }

    /**
     * Decide what actor to include in the array and being processed.
     *
     * @param $expedition
     */
    protected function processActors($expedition): void
    {
        $expedition->actors->each(function ($actor) use ($expedition) {
            if ($actor->id == config('zooniverse.actor_id')) {
                $attributes = [
                    'total' => $expedition->stat->local_subject_count,
                ];

                $actor->expeditions()->updateExistingPivot($expedition->id, $attributes);
            }

            ActorFactory::create($actor->class)->actor($actor);
        });
    }
}
