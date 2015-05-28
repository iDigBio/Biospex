<?php namespace Biospex\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
        'Biospex\Console\Commands\WorkFlowManagerCommand',
        'Biospex\Console\Commands\DownloadCleanCommand',
        'Biospex\Console\Commands\ViewsCommand',
        'Biospex\Console\Commands\TestCommand',
        'Biospex\Console\Commands\ClearBeanstalkdQueueCommand',
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		//$schedule->command('inspire')->hourly();
	}

}
