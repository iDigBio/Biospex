<?php
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
//use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;


class ClearBeanstalkdQueueCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'queue:beanstalkd:clear';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Clear a Beanstalkd queue, by deleting all pending jobs.';

	/**
	 * All the queues defined for beanstalkd.
	 * @var array
	 */
	protected $queues;

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
			array('queue', InputArgument::OPTIONAL, 'The name of the queue to clear.'),
		);
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->queues = Config::get('config.beanstalkd');

		$queues = ($this->argument('queue')) ? $this->argument('queue') : $this->queues;

		is_array($queues) ? $this->loopQueues($queues) : $this->clearQueue($queues);

		return;
	}

	/**
	 * Loop through queues and remove.
	 *
	 * @param $queues
	 */
	protected function loopQueues($queues)
	{
		foreach ($queues as $queue)
		{
			$this->clearQueue($queue);
		}

		return;
	}

	/**
	 * Clear Queue.
	 *
	 * @param $queue
	 */
	protected function clearQueue($queue)
	{
		$this->info(sprintf('Clearing queue: %s', $queue));
		$pheanstalk = Queue::getPheanstalk();
		$pheanstalk->useTube($queue);
		$pheanstalk->watch($queue);

		while ($job = $pheanstalk->reserve(0)) {
			$pheanstalk->delete($job);
		}

		$this->info('...cleared.');

		return;
	}

}