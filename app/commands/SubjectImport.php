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
use Biospex\Repo\Import\ImportInterface;
use Biospex\Services\Subject\Subject;
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
     * @var
     */
    protected $importId;

    /**
     * Constructor
     *
     * @param ImportInterface $import
     * @param Filesystem $filesystem
     * @param Subject $subject
     * @param UserInterface $user
     * @param ProjectInterface $project
     * @param BiospexMailer $mailer
     * @param MessageProviderInterface $messages
     */
    public function __construct(
        ImportInterface $import,
        Filesystem $filesystem,
        Subject $subject,
        UserInterface $user,
        ProjectInterface $project,
        BiospexMailer $mailer,
        MessageProviderInterface $messages
    )
    {
        parent::__construct();

        $this->import = $import;
        $this->filesystem = $filesystem;
        $this->subject = $subject;
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
        $imports = $this->import->all();

        if (count($imports) == 0)
            exit;

        foreach ($imports as $import)
        {
            if ($import->error)
                continue;

            $this->importId = $import->id;

            $this->makeTmp();

            $file = $this->dataDir . '/' . $import->file;
            $fileTmp = $this->dataTmp . '/' . $import->file;

            $this->moveFile($file, $fileTmp);

            $this->unzip($fileTmp);

            if ( ! $xml = $this->subject->loadDom($this->dataTmp . '/' . 'meta.xml'))
            {
                $this->messages->add("error", "Unable to load meta.xml to dom.");
                $this->report();
            }

            $this->subject->setFiles();

            $multiMediaFile = $this->subject->getMultiMediaFile();
            $occurrenceFile = $this->subject->getOccurrenceFile();

            $multimedia = $this->subject->loadCsv("{$this->dataTmp}/$multiMediaFile", 'multimedia');
            $occurrence = $this->subject->loadCsv("{$this->dataTmp}/$occurrenceFile", 'occurrence');

            $meta = $this->subject->saveMeta($xml, $import->project_id);

            $subjects = $this->subject->buildSubjectsArray($multimedia, $occurrence, $import->project_id, $meta->id);

            $duplicates = $this->subject->insertDocs($subjects);

            $this->report($duplicates);

            $this->destroyTmp();

            $this->import->destroy($import->id);
        }

        return;
    }

    /**
     * Extract files from zip
     *
     * @param $file
     */
    public function unzip($file)
    {
        $zip = new ZipArchive;
        $res = $zip->open($file);
        if ($res === true) {
            $zip->extractTo($this->dataTmp);
            $zip->close();
        } else {
            $this->messages->add("error", "Unable unzip file.");
            $this->report();
        }
    }

    /**
     * Move file to tmp directory
     * @param $file
     * @param $fileTmp
     */
    public function moveFile($file, $fileTmp)
    {
        if ( ! $this->filesystem->move($file, $fileTmp))
        {
            $this->messages->add("error", "Unable to move file to temp directory.");
            $this->report();
        }
    }

    /**
     * Create tmp dataDir
     */
    protected function makeTmp()
    {
        if ( ! $this->filesystem->isDirectory($this->dataTmp))
        {
            if ( ! $this->filesystem->makeDirectory($this->dataTmp))
            {
                $this->messages->add("error", "Unable to create temporary directory.");
                $this->report();
            }
        }

        if ( ! $this->filesystem->isWritable($this->dataTmp))
        {
            if ( ! chmod($this->dataTmp, 0777))
            {
                $this->messages->add("error", "Unable to make temporary directory writable.");
                $this->report();
            }
        }

        return;
    }

    /**
     * Iterate over tmp dataDir and destroy
     */
    protected function destroyTmp()
    {
        $it = new RecursiveDirectoryIterator($this->dataTmp, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->getFilename() === '.' || $file->getFilename() === '..') {
                continue;
            }
            if ($file->isDir()){
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($this->dataTmp);
    }

    /**
     * Send report for import
     *
     * @param $duplicates
     */
    public function report($duplicates = array())
    {
        $import = $this->import->find($this->importId);
        $user = $this->user->find($import->user_id);
        $project = $this->project->find($import->project_id);

        $emails = array();
        $attachment = '';

        if ($this->messages->any())
        {
            $this->import->update(array('id' => $this->importId, 'error' => 1));

            // error exists
            $emails[] = $this->adminEmail;
            $emails[] = $user->email;
            $subject = trans('errors.error_import');
            $data = array(
                'importId' => $this->importId,
                'projectTitle' => $project->title,
                'errorMessage' => $this->messages->first('error')
            );
            $view = 'emails.reporterror';
        }
        else
        {
            // no errors but possible duplicates
            $duplicateCount = count($duplicates);
            if ($duplicateCount)
            {
                $attachment = "{$this->dataTmp}/{$user->id}_{$project->id}.csv";
                $fp = fopen($attachment, 'w');
                foreach ($duplicates as $fields) {
                    fputcsv($fp, $fields);
                }
                fclose($fp);
            }
            $emails[] = $user->email;
            $data = array('projectTitle' => $project->title, 'duplicateCount' => $duplicateCount);
            $subject = trans('emails.import_complete');
            $view = 'emails.reportsubject';
        }

        $this->mailer->sendReport($emails, $subject, $view, $data, $attachment);

        if ($this->messages->any())
            die();

    }
}