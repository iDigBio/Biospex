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
use Biospex\Mailer\UserMailer;

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
     * Class constructor
     *
     * @param ImportInterface $import
     * @param SubjectInterface $subject
     * @param SubjectDocInterface $subjectdoc
     * @param Excel $excel
     * @param Filesystem $filesystem
     */
    public function __construct(
        ImportInterface $import,
        Filesystem $filesystem,
        Subject $subject,
        UserInterface $user,
        ProjectInterface $project,
        UserMailer $mailer
    )
    {
        parent::__construct();

        $this->import = $import;
        $this->filesystem = $filesystem;
        $this->subject = $subject;
        $this->user = $user;
        $this->project = $project;
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
        $imports = $this->import->all();

        foreach ($imports as $import)
        {
            $this->makeTmp();

            $file = $this->dataDir . '/' . $import->file;
            $fileTmp = $this->dataTmp . '/' . $import->file;

            $this->filesystem->move($file, $fileTmp);

            $this->unzip($fileTmp);

            $xml = $this->subject->loadDom($this->dataTmp . '/' . 'meta.xml');

            $this->subject->setFiles();

            $multiMediaFile = $this->subject->getMultiMediaFile();
            $occurrenceFile = $this->subject->getOccurrenceFile();

            $multimedia = $this->subject->loadCsv("{$this->dataTmp}/$multiMediaFile", 'multimedia');
            $occurrence = $this->subject->loadCsv("{$this->dataTmp}/$occurrenceFile", 'occurrence');

            $meta = $this->subject->saveMeta($xml, $import->project_id);

            $subjects = $this->subject->buildSubjectsArray($multimedia, $occurrence, $import->project_id, $meta->id);

            $duplicates = $this->subject->insertDocs($subjects);

            $this->report($import->user_id, $import->project_id, $duplicates);

            $this->destroyTmp();

            $this->import->destroy($import->id);

        }

        return;

    }

    /**
     * Send report for import
     *
     * @param $userId
     * @param $projectId
     * @param $duplicates
     */
    public function report($userId, $projectId, $duplicates)
    {
        $user = $this->user->find($userId);
        $project = $this->project->find($projectId);

        $email = $user->email;
        $data['projectTitle'] = $project->title;
        $data['duplicateCount'] = count($duplicates);

        if ( ! empty($duplicates))
        {
            $attachment = "{$this->dataTmp}/{$userId}_{$projectId}.csv";
            $fp = fopen($attachment, 'w');
            foreach ($duplicates as $fields) {
                fputcsv($fp, $fields);
            }
            fclose($fp);
        }

        $this->mailer->reportImport($email, $data, $attachment);
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
            echo 'Failed to extract' . PHP_EOL;
        }
    }

    /**
     * Create tmp dataDir
     */
    protected function makeTmp()
    {
        if ( ! $this->filesystem->isDirectory($this->dataTmp))
            $this->filesystem->makeDirectory($this->dataTmp);

        if ( ! $this->filesystem->isWritable($this->dataTmp))
            chmod($this->dataTmp, 0777);
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
}