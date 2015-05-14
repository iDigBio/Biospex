<?php namespace Biospex\Console\Commands;
/**
 * ClearBeanstalkdQueueCommand.php.php
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

class ClearBeanstalkdQueueCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'tube:beanstalkd:clear';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Clear a Beanstalkd tube, by deleting all pending jobs.';

	/**
	 * All the queues defined for beanstalkd.
	 * @var array
	 */
	protected $tubes;

	/**
	 * Create a new command instance.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Defines the arguments.
	 *
	 * @return array
	 */
	public function getArguments()
	{
		return array(
			array('tube', InputArgument::OPTIONAL, 'The name of the tube to clear.'),
		);
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->tubes = Config::get('queue.tubes');

		$tubes = ($this->argument('tube')) ? $this->argument('tube') : $this->tubes;

		is_array($tubes) ? $this->loopQueues($tubes) : $this->clearQueue($tubes);

		return;
	}

	/**
	 * Loop through queues and remove.
	 *
	 * @param $tubes
	 */
	protected function loopQueues($tubes)
	{
		foreach ($tubes as $tube)
		{
			$this->clearQueue($tube);
		}

		return;
	}

	/**
	 * Clear Queue.
	 *
	 * @param $queue
	 */
	protected function clearQueue($tube)
	{
		$this->info(sprintf('Clearing queue: %s', $tube));
		$pheanstalk = Queue::getPheanstalk();
		$pheanstalk->useTube($tube);
		$pheanstalk->watch($tube);

		while ($job = $pheanstalk->reserve(0)) {
			$pheanstalk->delete($job);
		}

		$this->info('...cleared.');

		return;
	}

}