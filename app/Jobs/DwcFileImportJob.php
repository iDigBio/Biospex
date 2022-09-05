<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Jobs;

use App\Models\Import;
use App\Notifications\DarwinCoreImportError;
use App\Notifications\ImportComplete;
use App\Repositories\ProjectRepository;
use App\Services\Process\CreateReportService;
use App\Services\Process\DarwinCore;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Notification;

/**
 * Class DwcFileImportJob
 *
 * @package App\Jobs
 */
class DwcFileImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 1800;

    /**
     * @var Import
     */
    public Import $import;

    /**
     * Create a new job instance.
     *
     * @param Import $import
     */
    public function __construct(Import $import)
    {
        $this->import = $import;
        $this->onQueue(config('config.queues.import'));
    }

    /**
     * @param \App\Repositories\ProjectRepository $projectRepo
     * @param \App\Services\Process\DarwinCore $dwcProcess
     * @param \App\Services\Process\CreateReportService $createReportService
     */
    public function handle(
        ProjectRepository $projectRepo,
        DarwinCore $dwcProcess,
        CreateReportService $createReportService
    ) {
        $scratchFileDir = Storage::disk('efs')->path(config('config.aws_efs_scratch_dir').'/'.md5($this->import->file));
        $importFilePath = Storage::disk('efs')->path($this->import->file);

        $project = $projectRepo->getProjectForDarwinImportJob($this->import->project_id);
        $users = $project->group->users->push($project->group->owner);

        try {
            $this->makeDirectory($scratchFileDir);

            $this->unzip($importFilePath, $scratchFileDir);

            $dwcProcess->process($this->import->project_id, $scratchFileDir);

            $dupsCsvName = md5($this->import->id).'dup.csv';
            $dupName = $createReportService->createCsvReport($dupsCsvName, $dwcProcess->getDuplicates());

            $rejCsvName = md5($this->import->id).'rej.csv';
            $rejName = $createReportService->createCsvReport($rejCsvName, $dwcProcess->getRejectedMedia());

            Notification::send($users, new ImportComplete($project->title, $dupName, $rejName));

            if ($project->workflow->actors->contains('title', 'OCR') && $dwcProcess->getSubjectCount() > 0) {
                OcrCreateJob::dispatch($project->id);
            }

            File::cleanDirectory($scratchFileDir);
            File::deleteDirectory($scratchFileDir);
            File::delete($importFilePath);
            $this->import->delete();
            $this->delete();

        } catch (Exception $e) {
            $this->import->error = 1;
            $this->import->save();
            File::cleanDirectory($scratchFileDir);
            File::deleteDirectory($scratchFileDir);

            Notification::send($users, new DarwinCoreImportError($project->title, $project->id, $e->getMessage() . $e->getFile() . $e->getLine()));

            $this->delete();
        }
    }

    /**
     * @param $dir
     * @throws \Exception
     */
    private function makeDirectory($dir)
    {
        if ( ! File::isDirectory($dir) && ! File::makeDirectory($dir, 0775, true))
        {
            throw new Exception(t('Unable to create directory: :directory', [':directory' => $dir]));
        }

        if ( ! File::isWritable($dir) && ! chmod($dir, 0775))
        {
            throw new Exception(t('Unable to make directory writable: %s', $dir));
        }
    }

    /**
     * Unzip file in directory.
     *
     * @param $zipFile
     * @param $dir
     */
    private function unzip($zipFile, $dir)
    {
        shell_exec("unzip $zipFile -d $dir");
    }
}
