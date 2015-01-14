<?php  namespace Biospex\Services\Queue;
/**
 * OcrService.php
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

use Biospex\Services\Report\Report;

class OcrService {
	/**
	 * Illuminate\Support\Contracts\MessageProviderInterface
	 * @var
	 */
	protected $messages;

	/**
	 * Class constructor
	 *
	 * @param Report $report
	 */
	public function __construct(
		Report $report
	)
	{
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
		$this->delete($job);

		return;
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