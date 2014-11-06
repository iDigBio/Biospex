<?php
/**
 * ViewsCommand.php
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
use Illuminate\Filesystem\Filesystem;

class ViewsCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'views:clear';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Clear views folder';

	/**
	 * The file system instance.
	 *
	 * @var \Illuminate\Filesystem\Filesystem
	 */
	protected $files;

	/**
	 * Create a new command instance.
	 *
	 * @param Filesystem $files
	 */
	public function __construct(Filesystem $files)
	{
		parent::__construct();
		$this->files = $files;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{

		foreach ($this->files->files(storage_path().'/views') as $file)
		{
			$this->files->delete($file);
		}

		$this->info('Views deleted from cache');
	}

}