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
use Biospex\Repo\WorkFlow\WorkFlowInterface;
use Biospex\Services\Report\Report;

class WorkFlowManagerCommand extends Command {
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
     * Illuminate\Support\Contracts\MessageProviderInterface
     * @var
     */
    protected $messages;

    /**
     * Class constructor
     */
    public function __construct(
        WorkflowManagerInterface $manager,
        WorkFlowInterface $workflow,
        Report $report
    )
    {
        $this->manager = $manager;
        $this->workflow = $workflow;
        $this->report = $report;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $managers = $this->manager->all();

        if (empty($managers))
            return;

        foreach ($managers as $manager)
        {
            $workflow = $this->workflow->find($manager->workflow_id);
            $classNameSpace ='Biospex\Services\WorkFlow\\' . $workflow->class;
            try {
                $class = App::make($classNameSpace);
                $class->process($manager->expedition_id);

                //$this->manager->destroy($manager->id);
            }
            catch ( Exception $e )
            {
                $this->report->addError(trans('errors.error_workflow_manager',
                    array(
                        'class' => $workflow->class,
                        'id' => $manager->workflow_id,
                        'error' => var_export($e->getTrace(), true)
                    )));
                $this->report->reportSimpleError();
                continue;
            }

        }

    }
}

