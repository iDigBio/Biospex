<?php namespace App\Services\Queue;

use App\Exceptions\BiospexException;
use App\Jobs\BuildOcrBatches;
use App\Services\File\FileService;
use App\Services\Mailer\BiospexMailer;
use App\Repositories\Contracts\Import;
use App\Repositories\Contracts\Project;
use App\Services\Process\DarwinCore;
use App\Services\Process\Xml;
use App\Services\Report\DarwinCoreImportReport;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Config;
use App\Exceptions\Handler;

class DarwinCoreFileImportQueue extends QueueAbstract
{

    use DispatchesJobs;

    public $subjectImportDir;

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
     * @var
     */
    protected $record;

    /**
     * @var FileService
     */
    protected $fileService;
    /**
     * @var Handler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param Import $import
     * @param Project $project
     * @param DarwinCoreImportReport $report
     * @param DarwinCore $process
     * @param Xml $xml
     * @param BiospexMailer $mailer
     * @param FileService $fileService
     * @param Handler $handler
     */
    public function __construct(
        Import $import,
        Project $project,
        DarwinCoreImportReport $report,
        DarwinCore $process,
        Xml $xml,
        BiospexMailer $mailer,
        FileService $fileService,
        Handler $handler
    )
    {
        $this->import = $import;
        $this->project = $project;
        $this->report = $report;
        $this->process = $process;
        $this->xml = $xml;
        $this->mailer = $mailer;
        $this->fileService = $fileService;
        $this->handler = $handler;

        $this->scratchDir = Config::get('config.scratch_dir');
        $this->subjectImportDir = Config::get('config.subject_import_dir');
        if ( ! $this->fileService->filesystem->isDirectory($this->subjectImportDir))
        {
            $this->fileService->filesystem->makeDirectory($this->subjectImportDir);
        }
    }

    /**
     * Fire method.
     *
     * @param $job
     * @param $data
     * @throws BiospexException
     */
    public function fire($job, $data)
    {
        $this->job = $job;
        $this->data = $data;

        $import = $this->import->skipCache()->find($this->data['id']);
        $this->record = $this->project->skipCache()->with(['group.owner', 'workflow.actors'])->find($import->project_id);

        $fileName = pathinfo($this->subjectImportDir . '/' . $import->file, PATHINFO_FILENAME);
        $this->scratchFileDir = $this->scratchDir . '/' . $import->id . '-' . md5($fileName);
        $zipFile = $this->subjectImportDir . '/' . $import->file;

        try
        {
            $this->fileService->makeDirectory($this->scratchFileDir);
            $this->fileService->unzip($zipFile, $this->scratchFileDir);

            $this->process->process($import->project_id, $this->scratchFileDir);

            $duplicates = $this->process->getDuplicates();
            $rejects = $this->process->getRejectedMedia();

            $this->report->complete($this->record->group->owner->email, $this->record->title, $duplicates, $rejects);

            if ($this->record->workflow->actors->contains('title', 'OCR') && $this->process->getSubjectCount() > 0)
            {
                $this->dispatch((new BuildOcrBatches($this->record->id))->onQueue(Config::get('config.beanstalkd.ocr')));
            }

            $this->fileService->filesystem->deleteDirectory($this->scratchFileDir);
            $this->fileService->filesystem->delete($zipFile);
            $this->import->delete($import->id);

            $this->delete();
        }
        catch (BiospexException $e)
        {
            $import->error = 1;
            $this->import->update($import->toArray(), $import->id);
            $this->fileService->filesystem->deleteDirectory($this->scratchFileDir);

            $this->report->addError(trans('errors.import_process', [
                'title'   => $this->record->title,
                'id'      => $this->record->id,
                'message' => $e->getMessage()
            ]));

            $this->report->reportError($this->record->group->owner->email);

            $this->handler->report($e);

            $this->delete();
        }
    }
}
