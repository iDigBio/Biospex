<?php

namespace App\Jobs;

use App\Repositories\Interfaces\Project;
use App\Models\Import;
use App\Notifications\DarwinCoreImportError;
use App\Notifications\ImportComplete;
use App\Services\File\FileService;
use App\Services\Process\DarwinCore;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DwcFileImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var Import
     */
    public $import;

    /**
     * Create a new job instance.
     *
     * @param Import $import
     */
    public function __construct($import)
    {
        $this->import = $import;
        $this->onQueue(config('config.beanstalkd.import'));
    }

    /**
     * Execute the job.
     *
     * @param Project $projectContract
     * @param DarwinCore $dwcProcess
     * @param FileService $fileService
     */
    public function handle(
        Project $projectContract,
        DarwinCore $dwcProcess,
        FileService $fileService
    )
    {

        $scratchFileDir = config('config.scratch_dir') . '/' . md5($this->import->file);

        $project = $projectContract->findWith($this->import->project_id, ['group.owner', 'workflow.actors']);

        try
        {
            $fileService->makeDirectory($scratchFileDir);
            $fileService->unzip(storage_path($this->import->file), $scratchFileDir);

            $dwcProcess->process($this->import->project_id, $scratchFileDir);

            $duplicates = create_csv($dwcProcess->getDuplicates());
            $rejects = create_csv($dwcProcess->getRejectedMedia());

            $project->group->owner->notify(new ImportComplete($project->title, $duplicates, $rejects));

            if ($project->workflow->actors->contains('title', 'OCR') && $dwcProcess->getSubjectCount() > 0)
            {
                BuildOcrBatchesJob::dispatch($project->id);
            }

            $fileService->filesystem->deleteDirectory($scratchFileDir);
            $fileService->filesystem->delete(storage_path($this->import->file));
            $this->import->delete();

            $this->delete();
        }
        catch (\Exception $e)
        {
            $this->import->error = 1;
            $this->import->save();
            $fileService->filesystem->deleteDirectory($scratchFileDir);

            $message = trans('errors.import_process', [
                'title'   => $project->title,
                'id'      => $project->id,
                'message' => $e->getMessage()
            ]);

            $project->group->owner->notify(new DarwinCoreImportError($message, __FILE__));

            $this->delete();
        }
    }
}
