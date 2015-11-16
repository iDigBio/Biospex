<?php
/**
 * Workflow.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <bruhnrp@gmail.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */
use Illuminate\Console\Command;
use Biospex\Repo\WorkflowManager\WorkflowManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\Queue;

class WorkFlowManagerCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'workflow:manage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Workflow manager";

    /**
     * @var WorkflowManagerInterface
     */
    protected $workflow;

    /**
     * Class constructor
     *
     * @param WorkflowManagerInterface $workflow
     * @param QueueFactory $factory
     * @internal param WorkflowManagerInterface $manager
     * @internal param ActorInterface $actor
     * @internal param Report $report
     */
    public function __construct(WorkflowManagerInterface $workflow)
    {
        parent::__construct();
        $this->queue = \Config::get('config.beanstalkd.workflow');
        $this->workflow = $workflow;
    }

    /**
     * Defines the arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return [
            ['expedition', InputArgument::OPTIONAL, 'The id of an Expedition to process.'],
        ];
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $id = $this->argument('expedition');

        if ( ! empty($id)) {
            $workFlows = $this->workflow->findByExpeditionIdWith($id, ['expedition.actors']);
        } else {
            $workFlows = $this->workflow->allWith(['expedition.actors']);
        }


        if ($workFlows->isEmpty()) {
            return;
        }

        $actors = $this->processWorkFlows($workFlows);

        foreach ($actors as $actor) {
            $actor->pivot->queued = 1;
            $actor->pivot->save();
            Queue::push('Biospex\Services\Queue\ActorQueue', serialize($actor), $this->queue);
        }
    }

    /**
     * Process each workflow and actors
     * @param $workFlows
     * @return array
     */
    protected function processWorkFlows($workFlows)
    {
        $actors = [];
        foreach ($workFlows as $workFlow) {
            if ($workFlow->stopped) {
                continue;
            }

            $this->processActors($workFlow, $actors);
        }

        return $actors;
    }

    /**
     * Decide what actor to include in the array and being processed
     * @param $workFlow
     * @param $actors
     */
    protected function processActors($workFlow, &$actors)
    {
        foreach ($workFlow->expedition->actors as $actor) {
            if ($this->checkErrorQueued($actor)) {
                return;
            }

            if ($actor->completed) {
                continue;
            }

            $actors[] = $actor;
        }

        return;
    }

    /**
     * Check if actor is in error or queued
     * @param $actor
     * @return bool
     */
    protected function checkErrorQueued($actor)
    {
        return ($actor->error || $actor->queued) ? true : false;
    }
}
