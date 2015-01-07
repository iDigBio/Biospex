<?php  namespace Biospex\Services\Queue;
/**
 * WorkflowManagerService.php
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

use Biospex\Repo\WorkflowManager\WorkflowManagerInterface;
use Biospex\Repo\Actor\ActorInterface;
use Biospex\Services\Report\Report;

class WorkflowManagerService {
	/**
	 * Illuminate\Support\Contracts\MessageProviderInterface
	 * @var
	 */
	protected $messages;

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
	}

	/**
	 * Fire queue.
	 *
	 * @param $job
	 * @param $data
	 */
	public function fire($job, $data)
	{
		$manager = $this->manager->findWith($data['id'], ['expedition.actors']);

		if (empty($manager) || $this->checkProcess($manager))
		{
			$this->delete($job);

			return;
		}

		$this->processActors($manager);

		$this->delete($job);

		return;
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
				$classNameSpace = 'Biospex\Services\Actor\\' . $actor->class;
				$class = App::make($classNameSpace);
				$class->setProperties($actor, $this->debug);
				$class->process();
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
	 * Delete a job from the queue
	 * @param $job
	 */
	public function delete($job)
	{
		$job->delete();
	}

	/**
	 * Release a job back to the queue
	 * @param $job
	 */
	public function release($job)
	{
		$job->release();
	}

	/**
	 * Return number of attempts on the job
	 *
	 * @param $job
	 * @return mixed
	 */
	public function getAttempts($job)
	{
		return $job->attempts();
	}

	/**
	 * Get id of job
	 *
	 * @param $job
	 * @return mixed
	 */
	public function getJobId($job)
	{
		return $job->getJobId();
	}
}