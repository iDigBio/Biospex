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
use Symfony\Component\Console\Input\InputArgument;
use Biospex\Repo\WorkflowManager\WorkflowManagerInterface;
use Biospex\Repo\Actor\ActorInterface;
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
	 * Debug argument from command line for testing
	 *
	 * @var
	 */
	protected $debug;

	/**
	 * Class constructor
	 *
	 * @param WorkflowManagerInterface $manager
	 * @param ActorInterface $actor
	 * @param Report $report
	 */
    public function __construct(
        WorkflowManagerInterface $manager,
        ActorInterface $actor,
        Report $report
    )
    {
        $this->manager = $manager;
        $this->actor = $actor;
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
		$this->debug = $this->argument('debug');
		$this->report->setDebug($this->debug);

        $managers = $this->manager->allWith(['expedition.actors']);

        if (empty($managers))
            return;

        foreach ($managers as $manager)
		{
			if ($this->checkProcess($manager))
				continue;

			$this->processActors($manager);
		}
    }

	/**
	 * @param $manager
	 * @return bool
	 */
	public function checkProcess ($manager)
	{
		return $manager->stopped == 1 || $manager->error == 1;
	}

	/**
	 * @param $manager
	 */
	public function processActors ($manager)
	{
		foreach ($manager->expedition->actors as $actor)
		{
			try
			{
				$actor = $this->actor->find($manager->actor_id);
				$classNameSpace = 'Biospex\Services\Actor\\' . $actor->class;
				$class = App::make($classNameSpace);
				$class->setProperties($actor->id, $this->debug);
				$class->process($manager->expedition_id);
			} catch (Exception $e)
			{
				$manager->error = 1;
				$this->manager->save($manager);
				$this->createError($manager, $actor, $e);
				break;
			}
		}
	}

	/**
	 * Create and send error email
	 *
	 * @param $manager
	 * @param $actor
	 * @param $e
	 */
	public function createError ($manager, $actor, $e)
	{
		$this->report->addError(trans('errors.error_workflow_manager',
			array(
				'class' => $actor->class,
				'id'    => $manager->id . ':' . $actor->id,
				'error' => $e->getFile() . " - " . $e->getLine() . ": " . $e->getMessage()
			)));
		$this->report->reportSimpleError();
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('debug', InputArgument::OPTIONAL, 'Debug option. Default false.', false),
		);
	}
}

