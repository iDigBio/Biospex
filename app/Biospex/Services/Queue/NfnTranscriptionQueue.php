<?php  namespace Biospex\Services\Queue;
/**
 * NfnResultsService.php.php
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

use Illuminate\Filesystem\Filesystem;
use Cartalyst\Sentry\Sentry;
use Biospex\Repo\Import\ImportInterface;
use Biospex\Repo\Project\ProjectInterface;
use Biospex\Repo\Subject\SubjectInterface;
use Biospex\Repo\Transcription\TranscriptionInterface;
use Biospex\Mailer\BiospexMailer;
use Illuminate\Support\Facades\Config;
use Maatwebsite\Excel\Excel;

class NfnTranscriptionQueue extends QueueAbstract {

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Sentry
     */
    protected $sentry;

    /**
     * @var ImportInterface
     */
    protected $import;

    /**
     * @var ProjectInterface
     */
    protected $project;

    /**
     * @var SubjectInterface
     */
    protected $subject;

    /**
     * @var TranscriptionInterface
     */
    protected $transcription;

    /**
     * @var BiospexMailer
     */
    protected $mailer;

    /**
     * @var Excel
     */
    protected $excel;

    /**
     * Directory where transcriptions files are stored.
     *
     * @var string
     */
    protected $transcriptionImportDir;

    /**
     * Constructor
     */
    public function __construct(
        Filesystem $filesystem,
        Sentry $sentry,
        ImportInterface $import,
        ProjectInterface $project,
        SubjectInterface $subject,
        TranscriptionInterface $transcription,
        BiospexMailer $mailer,
        Excel $excel
    )
    {
        $this->filesystem = $filesystem;
        $this->sentry = $sentry;
        $this->import = $import;
        $this->project = $project;
        $this->subject = $subject;
        $this->transcription = $transcription;
        $this->mailer = $mailer;
        $this->excel = $excel;

        $this->transcriptionImportDir = Config::get('config.transcriptionImportDir');
    }

    /**
     * Fire method
     * @param $job
     * @param $data
     */
    public function fire($job, $data)
    {
        $this->job = $job;
        $this->data = $data;

        $import = $this->import->find($this->data['id']);
        $user = $this->sentry->findUserById($import->user_id);
        $project = $this->project->find($import->project_id);

        $file = $this->transcriptionImportDir . '/' . $import->file;

        try
        {
            $data = $this->excel->load($file)->get();
            $header = [];
            foreach($data as $key => $row)
            {
                if ($key == 0)
                {
                    $row[0] = 'nfnId';
                    $header = $row;
                    continue;
                }
                $result = array_combine($header, $row);
                print_r($result);
                exit;
            }
            return;

            $this->report->complete($user->email, $project->title, $duplicates, $rejects);

            $this->filesystem->deleteDirectory($this->scratchFileDir);
            $this->filesystem->delete($zipFile);

            $this->import->destroy($import->id);
        }
        catch (Exception $e)
        {
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

    /**
     * Create tmp data directory
     *
     * @throws \Exception
     */
    protected function makeTmp()
    {
        if ( ! $this->filesystem->isDirectory($this->scratchFileDir))
        {
            if ( ! $this->filesystem->makeDirectory($this->scratchFileDir, 0777, true))
                throw new \Exception(trans('emails.error_create_dir', ['directory' => $this->scratchFileDir]));
        }

        if ( ! $this->filesystem->isWritable($this->scratchFileDir))
        {
            if ( ! chmod($this->scratchFileDir, 0777))
                throw new \Exception(trans('emails.error_write_dir', ['directory' => $this->scratchFileDir]));
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

    /**
     * Delete a job from the queue
     */
    public function delete()
    {
        $this->job->delete();
    }

    /**
     * Release a job ack to the queue
     */
    public function release()
    {
        $this->job->release();
    }

    /**
     * Return number of attempts on the job
     *
     * @return mixed
     */
    public function getAttempts()
    {
        return $this->job->attempts();
    }

    /**
     * Get id of job
     *
     * @return mixed
     */
    public function getJobId()
    {
        return $this->job->getJobId();
    }
}