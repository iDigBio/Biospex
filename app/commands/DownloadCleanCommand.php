<?php
/**
 * DownloadClean.php
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
use Symfony\Component\Console\Input\InputArgument;
use Biospex\Repo\Download\DownloadInterface;
use Biospex\Services\Report\Report;

class DownloadCleanCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'download:clean';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Remove expired download files.";

	/**
	 * Directory where nfn downloads are stored.
	 *
	 * @var string
	 */
	protected $nfnExportDir;

	/**
	 * Constructor
	 *
	 * @param Filesystem $filesystem
	 * @param DownloadInterface $download
	 * @param Report $report
	 */
	public function __construct(
		Filesystem $filesystem,
		DownloadInterface $download,
		Report $report
	)
	{
		parent::__construct();

		$this->filesystem = $filesystem;
		$this->download = $download;
		$this->report = $report;

		$this->nfnExportDir = Config::get('config.nfnExportDir');
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->report->setDebug($this->argument('debug'));

		$downloads = $this->download->getExpired();

		foreach ($downloads as $download)
		{
			try
			{
				$file = $this->nfnExportDir . "/" . $download->file;
				if ($this->filesystem->isFile($file))
					$this->filesystem->delete($file);

				$this->download->destroy($download->id);
			}
			catch (Exception $e)
			{
				$this->report->addError("Unable to process download id: {$download->id}. " . $e->getMessage() . " " . $e->getTraceAsString());
				$this->report->reportSimpleError();
				continue;
			}
		}

		return;
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