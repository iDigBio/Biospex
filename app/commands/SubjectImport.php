<?php
/**
 * ImportCommand.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <79e6ef82@opayq.com>
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
use Biospex\Repo\Import\ImportInterface;
use Biospex\Repo\Project\ProjectInterface;
use Biospex\Repo\User\UserInterface;
use Biospex\Services\Report\Report;
use Biospex\Services\Subject\SubjectProcess;
use Biospex\Services\Xml\XmlProcess;
use Biospex\Mailer\BiospexMailer;

class SubjectImport extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'subject:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Import darwin core files";

    /**
     * Directory where darwin core files are stored
     *
     * @var string
     */
    protected $dataDir;

    /**
     * Tmp directory for extracted files
     * @var string
     */
    protected $dataTmp;

	/**
	 * Constructor
	 *
	 * @param ImportInterface $import
	 * @param Filesystem $filesystem
	 * @param SubjectProcess $subjectProcess
	 * @param XmlProcess $xmlProcess
	 * @param UserInterface $user
	 * @param ProjectInterface $project
	 * @param BiospexMailer $mailer
	 * @param MessageProviderInterface $messages
	 * @param Report $report
	 */
    public function __construct(
		Filesystem $filesystem,
		ImportInterface $import,
		ProjectInterface $project,
		UserInterface $user,
		Report $report,
		SubjectProcess $subjectProcess,
		XmlProcess $xmlProcess,
        BiospexMailer $mailer

    )
    {
        parent::__construct();

        $this->filesystem = $filesystem;
		$this->import = $import;
		$this->project = $project;
		$this->user = $user;
		$this->report = $report;
		$this->subjectProcess = $subjectProcess;
		$this->xmlProcess = $xmlProcess;
        $this->mailer = $mailer;

        $this->dataDir = Config::get('config.dataDir');
        $this->dataTmp = Config::get('config.dataTmp');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
		$this->report->setDebug($this->argument('debug'));

        $imports = $this->import->all();

        if (count($imports) == 0)
            exit;

        foreach ($imports as $import)
        {
			if ($import->error)
				continue;

			$user = $this->user->find($import->user_id);
			$project = $this->project->find($import->project_id);

			$file = "{$this->dataDir}/{$import->file}";
			$fileDir = "{$this->dataTmp}/" . md5($import->file);

			try
			{
				$this->makeTmp($fileDir);
				$this->unzip($file, $fileDir);

				$this->subjectProcess->processSubjects($import->project_id, $fileDir);

				$duplicates = $this->subjectProcess->getDuplicates();
				$rejects = $this->subjectProcess->getRejectedMedia();

				list($duplicated, $rejected, $attachments) = $this->createDuplicateReject($duplicates, $rejects);

				$this->report->importComplete($user->email, $project->title, $duplicated, $rejected, $attachments);

				$this->destroyDir($this->dataTmp, true);

				$this->filesystem->delete(array($file));

				$this->import->destroy($import->id);
			}
			catch (Exception $e)
			{
				die($e->getMessage() . $e->getTraceAsString());
				$this->report->addError("Unable to process import id: {$import->id}. " . $e->getMessage() . " " . $e->getTraceAsString());
				$this->report->importError($import->id, $user->email, $project->title);
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

	/**
	 * Extract files from zip
	 * TODO: ZipArchive causes MAC uploaded files to extract with two folders. Need to determine better solution.
	 *
	 * @param $file
	 * @param $fileDir
	 * @throws Exception
	 */
    public function unzip($file, $fileDir)
    {
		shell_exec("unzip $file -d $fileDir");

		return;
    }

	/**
	 * Copy file to tmp directory
	 *
	 * @param $file
	 * @param $fileDirTmp
	 * @throws Exception
	 */
    public function copyFile($file, $fileDirTmp)
    {
        if ( ! $this->filesystem->copy($file, $fileDirTmp))
			throw new \Exception('Unable to copy file to temp directory:' . $file);

		return;
    }

	/**
	 * Create tmp data directory
	 *
	 * @param $dir
	 * @throws Exception
	 */
    protected function makeTmp($dir)
    {
        if ( ! $this->filesystem->isDirectory($dir))
        {
            if ( ! $this->filesystem->makeDirectory($dir, 0777, true))
				throw new \Exception('"Unable to create temporary directory:' . $dir);
        }

        if ( ! $this->filesystem->isWritable($dir))
        {
            if ( ! chmod($dir, 0777))
				throw new \Exception('"Unable to make temporary directory writable:' . $dir);
        }

        return;
    }

	/**
	 * Check if directory exists and destroy
	 *
	 * @param $dir
	 * @param $parent
	 */
	public function destroyDir($dir, $parent = false)
	{
		if ( ! $this->filesystem->isDirectory($dir))
			return;

		Helpers::destroyDir($dir, $parent);
	}

	/**
	 * Set error column on import
	 */
	private function importError($import)
	{
		$import->error = 1;
		$this->import->save($import);

		return;
	}

	/**
	 * Create duplicate and reject files if any
	 *
	 * @param array $duplicates
	 * @param array $rejects
	 * @return array
	 */
    public function createDuplicateReject($duplicates = array(), $rejects = array())
    {
		$attachments = array();
		$duplicated = 0;
		$rejected = 0;
		if ( ! empty($duplicates))
		{
			$file = "{$this->dataTmp}/duplicates.csv";
			$this->writeCsv($file, $duplicates);
			$attachments[] = $file;
			$duplicated = count($duplicates);
		}

		if ( ! empty($rejects))
		{
			// empty image ids
			$file = "{$this->dataTmp}/rejected.csv";
			$this->writeCsv($file, $rejects);
			$attachments[] = $file;
			$rejected = count($rejects);
		}

		return array($duplicated, $rejected, $attachments);
    }

	/**
	 * Write to csv file
	 *
	 * @param $file
	 * @param $array
	 */
	private function writeCsv($file, $array)
	{
		$fp = fopen($file, 'w');
		foreach ($array as $fields)
		{
			fputcsv($fp, $fields);
		}
		fclose($fp);
	}
}