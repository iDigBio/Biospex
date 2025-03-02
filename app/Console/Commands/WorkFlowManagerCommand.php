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

use App\Services\Actor\ActorFactory;
use App\Services\Workflow\WorkflowManagerService;
use Illuminate\Console\Command;

/**
 * Class WorkFlowManagerCommand
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
    protected $description = 'Workflow manager';

    public mixed $tube;

    /**
     * WorkFlowManagerCommand constructor.
     */
    public function __construct(protected WorkflowManagerService $workflowManagerService)
    {
        parent::__construct();
        $this->tube = config('config.queue.workflow');
    }

    /**
     * Execute the console command.
     *
     * @see WorkflowManagerRepository::getWorkflowManagersForProcessing() Filters out error, queued, completed.
     * @see WorkflowManagerRepository::getWorkflowManagersForProcessing() Filters out error, queued, completed.
     */
    public function handle(): void
    {
        $expeditionId = $this->argument('expeditionId');

        $managers = $this->workflowManagerService->getWorkflowManagersForProcessing($expeditionId);

        if ($managers->isEmpty()) {
            return;
        }

        $managers->each(function ($manager) {
            $this->processExpeditions($manager->expedition);
        });
    }

    /**
     * Process each Expedition send to actor classes.
     */
    protected function processExpeditions($expedition): void
    {
        $count = $expedition->stat->local_subject_count;

        $expedition->actorExpeditions->each(function ($actorExpedition) use ($count) {
            $actorExpedition->total = $count;
            $actorExpedition->save();

            ActorFactory::create($actorExpedition->actor->class)->process($actorExpedition);
        });
    }
}
