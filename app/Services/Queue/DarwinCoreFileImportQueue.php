<?php namespace App\Services\Queue;

use App\Exceptions\BiospexException;
use App\Jobs\BuildOcrBatchesJob;
use App\Repositories\Contracts\ImportContract;
use App\Services\File\FileService;
use App\Services\Mailer\BiospexMailer;
use App\Repositories\Contracts\ProjectContract;
use App\Services\Process\DarwinCore;
use App\Services\Process\Xml;
use App\Services\Report\DarwinCoreImportReport;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Exceptions\Handler;

class DarwinCoreFileImportQueue extends QueueAbstract
{

    use DispatchesJobs;

    public $subjectImportDir;

    /**
     * @var ImportContract
     */
    protected $importContract;

    /**
     * @var ProjectContract
     */
    protected $projectContract;

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
     * @param ImportContract $importContract
     * @param ProjectContract $projectContract
     * @param DarwinCoreImportReport $report
     * @param DarwinCore $process
     * @param Xml $xml
     * @param BiospexMailer $mailer
     * @param FileService $fileService
     * @param Handler $handler
     */
    public function __construct(
        ImportContract $importContract,
        ProjectContract $projectContract,
        DarwinCoreImportReport $report,
        DarwinCore $process,
        Xml $xml,
        BiospexMailer $mailer,
        FileService $fileService,
        Handler $handler
    )
    {
        $this->importContract = $importContract;
        $this->projectContract = $projectContract;
        $this->report = $report;
        $this->process = $process;
        $this->xml = $xml;
        $this->mailer = $mailer;
        $this->fileService = $fileService;
        $this->handler = $handler;

        $this->scratchDir = config('config.scratch_dir');
        $this->subjectImportDir = config('config.subject_import_dir');
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

        $import = $this->importContract->setCacheLifetime(0)->find($this->data['id']);
        $this->record = $this->projectContract->setCacheLifetime(0)
            ->with(['group.owner', 'workflow.actors'])
            ->find($import->project_id);

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
                $this->dispatch((new BuildOcrBatchesJob($this->record->id))->onQueue(config('config.beanstalkd.ocr')));
            }

            $this->fileService->filesystem->deleteDirectory($this->scratchFileDir);
            $this->fileService->filesystem->delete($zipFile);
            $this->importContract->delete($import->id);

            $this->delete();
        }
        catch (BiospexException $e)
        {
            $import->error = 1;
            $this->importContract->update($import->id, $import->toArray());
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
