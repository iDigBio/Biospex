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
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Filesystem\Filesystem;
use Biospex\Repo\Import\ImportInterface;
use Biospex\Services\Subject\SubjectProcess;
use Biospex\Services\Subject\SubjectProcessException;
use Biospex\Services\Xml\XmlProcess;
use Biospex\Services\Xml\XmlProcessException;
use Biospex\Repo\User\UserInterface;
use Biospex\Repo\Project\ProjectInterface;
use Biospex\Mailer\BiospexMailer;
use Illuminate\Support\Contracts\MessageProviderInterface;

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
	 * Current import record
	 *
	 * @var
	 */
	protected $record;

	/**
	 * @var bool
	 */
	protected $debug;

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
	 * @param PropertyInterface $property
	 */
    public function __construct(
        ImportInterface $import,
        Filesystem $filesystem,
        SubjectProcess $subjectProcess,
		XmlProcess $xmlProcess,
        UserInterface $user,
        ProjectInterface $project,
        BiospexMailer $mailer,
        MessageProviderInterface $messages
    )
    {
        parent::__construct();

        $this->import = $import;
        $this->filesystem = $filesystem;
		$this->xmlProcess = $xmlProcess;
        $this->subjectProcess = $subjectProcess;
        $this->user = $user;
        $this->project = $project;
        $this->mailer = $mailer;
        $this->messages = $messages;
        $this->dataDir = Config::get('config.dataDir');
        $this->dataTmp = Config::get('config.dataTmp');
        $this->adminEmail = Config::get('config.adminEmail');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
		$this->debug = $this->argument('debug');

        $imports = $this->import->all();

        if (count($imports) == 0)
            exit;

        foreach ($imports as $import)
        {
			$this->record = $import;

            if ($this->record->error)
                continue;

			$file = "{$this->dataDir}/{$this->record->file}";
			$fileDir = "{$this->dataTmp}/" . md5($this->record->file);

			try
			{
				$this->makeTmp($fileDir);
				$this->unzip($file, $fileDir);

				$this->subjectProcess->processSubjects($this->record->project_id, $fileDir);

				$duplicates = $this->subjectProcess->getDuplicates();
				$rejected = $this->subjectProcess->getRejectedMedia();

				$this->report($duplicates, $rejected);

				$this->filesystem->delete(array($file));

				$this->import->destroy($this->record->id);
			}
			catch (XmlProcessException $e)
			{
				$this->messages->add("error", "Unable to process import id: {$this->record->id}. " . $e->getMessage() . " " . $e->getTraceAsString());
				$this->report();
				continue;
			}
			catch (SubjectProcessException $e)
			{
				$this->messages->add("error", "Unable to process import id: {$this->record->id}. " . $e->getMessage() . " " . $e->getTraceAsString());
				$this->report();
				continue;
			}
			catch (Exception $e)
			{
				$this->messages->add("error", "Unable to process import id: {$this->record->id}. " . $e->getMessage() . " " . $e->getTraceAsString());
				$this->report();
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
     *
     * @param $file
	 * @param $fileDir
     */
    public function unzip($file, $fileDir)
    {
        $zip = new ZipArchive;
        $res = $zip->open($file);
        if ($res === true) {
            $zip->extractTo($fileDir);
            $zip->close();
        } else {
            $this->messages->add("error", "Unable unzip file.");
            $this->report();
        }
    }

    /**
     * Copy file to tmp directory
     * @param $file
     * @param $fileDirTmp
     */
    public function copyFile($file, $fileDirTmp)
    {
        if ( ! $this->filesystem->copy($file, $fileDirTmp))
        {
            $this->messages->add("error", "Unable to copy file to temp directory.");
            $this->report();
        }
    }

	/**
	 * Create tmp dataDir
	 *
	 * @param $dir
	 */
    protected function makeTmp($dir)
    {
        if ( ! $this->filesystem->isDirectory($dir))
        {
            if ( ! $this->filesystem->makeDirectory($dir, 0755, true))
            {
                $this->messages->add("error", "Unable to create temporary directory.");
                $this->report();
            }
        }

        if ( ! $this->filesystem->isWritable($dir))
        {
            if ( ! chmod($dir, 0777))
            {
                $this->messages->add("error", "Unable to make temporary directory writable.");
                $this->report();
            }
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
     * Send report for import
     *
     * @param $duplicates
     */
    public function report($duplicates = array(), $rejected = array())
    {
		$user = $this->user->find($this->record->user_id);
        $project = $this->project->find($this->record->project_id);

        $emails = array();
		$attachment = array();
		$data = array();
		$from = $this->adminEmail;

        if ($this->messages->any())
		{
			$this->record->error = 1;
			$this->import->save($this->record);

            // error exists
            $emails[] = $this->adminEmail;
            $emails[] = $user->email;
            $subject = trans('errors.error_import');
            $data = array(
                'importId' => $this->record->id,
                'projectTitle' => $project->title,
                'errorMessage' => print_r($this->messages->all(), true)
            );
            $view = 'emails.reporterror';
        }
		elseif ( ! empty($duplicates) || ! empty($rejected))
		{
			$data = array(
				'projectTitle' => $project->title,
				'duplicateCount' => 0,
				'rejectedCount' => 0,
			);

			$emails[] = $user->email;
			$subject = trans('emails.import_complete');
			$view = 'emails.reportsubject';

			if ( ! empty($duplicates))
			{
				// no errors but possible duplicates
				$file = "{$this->dataTmp}/{$user->id}_{$project->id}_duplicates.csv";
				$this->writeCsv($file, $duplicates);
				$attachment[] = $file;
				$data['duplicateCount'] = count($duplicates);
			}

			if ( ! empty($rejected))
			{
				// empty image ids
				$file = "{$this->dataTmp}/{$user->id}_{$project->id}_rejected.csv";
				$this->writeCsv($file, $rejected);
				$attachment[] = $file;
				$data['rejectedCount'] = count($rejected);
			}
		}

		if ($this->debug)
			$this->debug($data);

		$this->mailer->sendReport($from, $emails, $subject, $view, $data, $attachment);

		$this->destroyDir($this->dataTmp, true);

		return;

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

	/**
	 * Dump all messages during debug
	 */
	private function debug($data)
	{
		dd($data);
	}
}