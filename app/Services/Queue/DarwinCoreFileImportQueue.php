<?php

namespace App\Services\Queue;

use App\Jobs\BuildOcrBatchesJob;
use App\Notifications\DarwinCoreImportError;
use App\Notifications\ImportComplete;
use App\Interfaces\Import;
use App\Services\File\FileService;
use App\Services\Mailer\BiospexMailer;
use App\Interfaces\Project;
use App\Services\Process\DarwinCore;
use App\Services\Process\Xml;
use Illuminate\Foundation\Bus\DispatchesJobs;

class DarwinCoreFileImportQueue extends QueueAbstract
{

    use DispatchesJobs;

    public $subjectImportDir;

    /**
     * @var Import
     */
    protected $importContract;

    /**
     * @var Project
     */
    protected $projectContract;

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
     * Constructor
     *
     * @param Import $importContract
     * @param Project $projectContract
     * @param DarwinCore $process
     * @param Xml $xml
     * @param BiospexMailer $mailer
     * @param FileService $fileService
     */
    public function __construct(
        Import $importContract,
        Project $projectContract,
        DarwinCore $process,
        Xml $xml,
        BiospexMailer $mailer,
        FileService $fileService
    )
    {
        $this->importContract = $importContract;
        $this->projectContract = $projectContract;
        $this->process = $process;
        $this->xml = $xml;
        $this->mailer = $mailer;
        $this->fileService = $fileService;

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
     */
    public function fire($job, $data)
    {
        $this->job = $job;
        $this->data = $data;

        $import = $this->importContract->find($this->data['id']);

        try
        {
            $this->record = $this->projectContract->findWith($import->project_id, ['group.owner', 'workflow.actors']);

            $fileName = pathinfo($this->subjectImportDir . '/' . $import->file, PATHINFO_FILENAME);
            $this->scratchFileDir = $this->scratchDir . '/' . $import->id . '-' . md5($fileName);
            $zipFile = $this->subjectImportDir . '/' . $import->file;

            $this->fileService->makeDirectory($this->scratchFileDir);
            $this->fileService->unzip($zipFile, $this->scratchFileDir);

            $this->process->process($import->project_id, $this->scratchFileDir);

            $duplicates = create_csv($this->process->getDuplicates());
            $rejects = create_csv($this->process->getRejectedMedia());

            $this->record->group->owner->notify(new ImportComplete($this->record->title, $duplicates, $rejects));

            if ($this->record->workflow->actors->contains('title', 'OCR') && $this->process->getSubjectCount() > 0)
            {
                $this->dispatch((new BuildOcrBatchesJob($this->record->id))->onQueue(config('config.beanstalkd.ocr')));
            }

            $this->fileService->filesystem->deleteDirectory($this->scratchFileDir);
            $this->fileService->filesystem->delete($zipFile);
            $this->importContract->delete($import->id);

            $this->delete();
        }
        catch (\Exception $e)
        {
            $import->error = 1;
            $this->importContract->update($import->toArray(), $import->id);
            $this->fileService->filesystem->deleteDirectory($this->scratchFileDir);

            $message = trans('errors.import_process', [
                'title'   => $this->record->title,
                'id'      => $this->record->id,
                'message' => $e->getMessage()
            ]);

            $this->record->group->owner->notify(new DarwinCoreImportError($message, __FILE__));

            $this->delete();
        }
    }
}
