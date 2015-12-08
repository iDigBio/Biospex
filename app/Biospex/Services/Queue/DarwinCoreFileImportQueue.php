<?php namespace Biospex\Services\Queue;

/**
 * DarwinCoreImportService.php
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

use Biospex\Repo\OcrQueue\OcrQueueInterface;
use Biospex\Repo\Subject\SubjectInterface;
use Biospex\Services\Process\Ocr as OcrProcess;
use Illuminate\Filesystem\Filesystem;
use Cartalyst\Sentry\Sentry;
use Biospex\Repo\Import\ImportInterface;
use Biospex\Repo\Project\ProjectInterface;
use Biospex\Services\Report\DarwinCoreImportReport;
use Biospex\Services\Process\DarwinCoreImport;
use Biospex\Services\Process\Xml;
use Biospex\Mailer\BiospexMailer;
use Illuminate\Support\Facades\Config;

class DarwinCoreFileImportQueue extends QueueAbstract
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ImportInterface
     */
    protected $import;

    /**
     * @var ProjectInterface
     */
    protected $project;

    /**
     * @var Sentry
     */
    protected $sentry;

    /**
     * @var DarwinCoreImportReport
     */
    protected $report;

    /**
     * @var DarwinCore
     */
    protected $process;

    /**
     * @var xml
     */
    protected $xml;

    /**
     * @var BiospexMailer
     */
    protected $mailer;

    /**
     * Scratch directory.
     */
    protected $scratchDir;

    /**
     * Directory where darwin core files are stored during processing.
     *
     * @var string
     */
    protected $subjectsImportDir;

    /**
     * Tmp directory for extracted files
     *
     * @var string
     */
    protected $scratchFileDir;

    /**
     * @var OcrQueueInterface
     */
    protected $ocr;

    /**
     * @var SubjectInterface
     */
    protected $subjectInterface;

    /**
     * @var OcrProcess
     */
    protected $ocrProcess;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param ImportInterface $import
     * @param ProjectInterface $project
     * @param Sentry $sentry
     * @param DarwinCoreImportReport $report
     * @param DarwinCoreImport|DarwinCore $process
     * @param OcrQueueInterface $ocr
     * @param SubjectInterface $subjectInterface
     * @param Sentry $sentry
     * @param OcrProcess $ocrProcess
     * @param Xml $xml
     * @param BiospexMailer $mailer
     */
    public function __construct(
        Filesystem $filesystem,
        ImportInterface $import,
        ProjectInterface $project,
        Sentry $sentry,
        DarwinCoreImportReport $report,
        DarwinCoreImport $process,
        OcrQueueInterface $ocr,
        SubjectInterface $subjectInterface,
        Sentry $sentry,
        OcrProcess $ocrProcess,
        Xml $xml,
        BiospexMailer $mailer
    ) {
        $this->filesystem = $filesystem;
        $this->import = $import;
        $this->project = $project;
        $this->sentry = $sentry;
        $this->report = $report;
        $this->process = $process;
        $this->subjectInterface = $subjectInterface;
        $this->ocrProcess = $ocrProcess;
        $this->ocr = $ocr;
        $this->xml = $xml;
        $this->mailer = $mailer;

        $this->scratchDir = Config::get('config.scratchDir');
        $this->subjectImportDir = Config::get('config.subjectImportDir');
        if (! $this->filesystem->isDirectory($this->subjectImportDir)) {
            $this->filesystem->makeDirectory($this->subjectImportDir);
        }
    }

    /**
     * Fire method.
     *
     * @param $job
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function fire($job, $data)
    {
        $this->job = $job;
        $this->data = $data;

        $import = $this->import->find($this->data['id']);
        $user = $this->sentry->findUserById($import->user_id);
        $project = $this->project->findWith($import->project_id, ['workflow.actors']);

        $fileName = pathinfo($this->subjectImportDir . '/' . $import->file, PATHINFO_FILENAME);
        $this->scratchFileDir = $this->scratchDir . '/' . $import->id . '-' . md5($fileName);
        $zipFile = $this->subjectImportDir . '/' . $import->file;

        try {
            $this->makeTmp();
            $this->unzip($zipFile);

            $this->process->process($import->project_id, $this->scratchFileDir);

            $duplicates = $this->process->getDuplicates();
            $rejects = $this->process->getRejectedMedia();

            $this->report->complete($user->email, $project->title, $duplicates, $rejects);

            $this->filesystem->deleteDirectory($this->scratchFileDir);
            $this->filesystem->delete($zipFile);

            $this->import->destroy($import->id);

            $this->processOcr($project);

        } catch (\Exception $e) {
            $import->error = 1;
            $import->save();
            $this->report->addError(trans('emails.error_import_process',
                ['id' => $import->id, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]
            ));
            $this->report->error($import->id, $user->email, $project->title);
        }

        $this->delete();

        return;
    }

    protected function processOcr($project)
    {
        if ($this->ocrProcess->disableOcr)
            return;

        if ( ! $this->checkOcrActor($project))
            return;

        $queue = $this->ocr->findByProjectId($project->id);
        if ( ! empty($queue))
            return;

        $subjects = $this->subjectInterface->findByProjectId($project->id);
        foreach ($subjects as $subject) {
            if ( ! empty($subject->ocr)) {
                continue;
            }
            $this->ocrProcess->buildOcrQueueData($subject);
        }

        $data = $this->ocrProcess->getOcrQueueData();
        $count = $this->ocrProcess->getOcrQueueDataCount();

        if ($count > 0) {
            $id = $this->ocrProcess->saveOcrQueue($project->id, $data, $count);
            \Queue::push('Biospex\Services\Queue\OcrProcessQueue', ['id' => $id], Config::get('config.beanstalkd.ocr'));
        }

        return;
    }

    /**
     * Check if project has ocr actor.
     * @param $project
     * @return bool
     */
    protected function checkOcrActor($project)
    {
        foreach($project->workflow->actors as $actor) {
            if ($actor->title == 'OCR') {
                return true;
            }
        }

        return false;
    }

    /**
     * Create tmp data directory
     *
     * @throws \Exception
     */
    protected function makeTmp()
    {
        if (! $this->filesystem->isDirectory($this->scratchFileDir)) {
            if (! $this->filesystem->makeDirectory($this->scratchFileDir, 0777, true)) {
                throw new \Exception(trans('emails.error_create_dir', ['directory' => $this->scratchFileDir]));
            }
        }

        if (! $this->filesystem->isWritable($this->scratchFileDir)) {
            if (! chmod($this->scratchFileDir, 0777)) {
                throw new \Exception(trans('emails.error_write_dir', ['directory' => $this->scratchFileDir]));
            }
        }

        return;
    }

    /**
     * Extract files from zip
     * ZipArchive causes MAC uploaded files to extract with two folders.
     *
     * @param $zipFile
     * @throws Exception
     */
    public function unzip($zipFile)
    {
        shell_exec("unzip $zipFile -d $this->scratchFileDir");

        return;
    }
}
