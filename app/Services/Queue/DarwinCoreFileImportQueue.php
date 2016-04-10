<?php namespace App\Services\Queue;

use App\Jobs\BuildOcrBatches;
use App\Services\Mailer\BiospexMailer;
use App\Repositories\Contracts\Import;
use App\Repositories\Contracts\OcrQueue;
use App\Repositories\Contracts\Project;
use App\Repositories\Contracts\User;
use App\Services\Process\DarwinCore;
use App\Services\Process\Xml;
use App\Services\Report\DarwinCoreImportReport;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Config;
use Exception;

class DarwinCoreFileImportQueue extends QueueAbstract
{
    use DispatchesJobs;
    
    public $subjectImportDir;
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Import
     */
    protected $import;

    /**
     * @var Project
     */
    protected $project;

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
     * Tmp directory for extracted files
     *
     * @var string
     */
    protected $scratchFileDir;

    /**
     * @var User
     */
    protected $user;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param Import $import
     * @param Project $project
     * @param User $user
     * @param DarwinCoreImportReport $report
     * @param DarwinCore $process
     * @param Xml $xml
     * @param BiospexMailer $mailer
     */
    public function __construct(
        Filesystem $filesystem,
        Import $import,
        Project $project,
        User $user,
        DarwinCoreImportReport $report,
        DarwinCore $process,
        Xml $xml,
        BiospexMailer $mailer
    ) {
        $this->filesystem = $filesystem;
        $this->import = $import;
        $this->project = $project;
        $this->user = $user;
        $this->report = $report;
        $this->process = $process;
        $this->xml = $xml;
        $this->mailer = $mailer;

        $this->scratchDir = Config::get('config.scratch_dir');
        $this->subjectImportDir = Config::get('config.subject_import_dir');
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
        $user = $this->user->find($import->user_id);
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

            $this->dispatch((new BuildOcrBatches($project))->onQueue(Config::get('config.beanstalkd.ocr')));

            $this->filesystem->deleteDirectory($this->scratchFileDir);
            $this->filesystem->delete($zipFile);
            $this->import->destroy($import->id);

        } catch (Exception $e) {
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
        if (! $this->filesystem->isDirectory($this->scratchFileDir)) {
            if (! $this->filesystem->makeDirectory($this->scratchFileDir, 0777, true)) {
                throw new Exception(trans('emails.error_create_dir', ['directory' => $this->scratchFileDir]));
            }
        }

        if (! $this->filesystem->isWritable($this->scratchFileDir)) {
            if (! chmod($this->scratchFileDir, 0777)) {
                throw new Exception(trans('emails.error_write_dir', ['directory' => $this->scratchFileDir]));
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
