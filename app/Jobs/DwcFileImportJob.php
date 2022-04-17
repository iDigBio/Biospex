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
use App\Services\Csv\Csv;
use App\Services\File\FileService;
use App\Services\Process\DarwinCore;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
    public function __construct(Import $import)
    {
        $this->import = $import;
        $this->onQueue(config('config.import_tube'));
    }

    /**
     * @param \App\Repositories\ProjectRepository $projectRepo
     * @param \App\Services\Process\DarwinCore $dwcProcess
     * @param \App\Services\File\FileService $fileService
     * @param \App\Services\Csv\Csv $csv
     */
    public function handle(
        ProjectRepository $projectRepo,
        DarwinCore $dwcProcess,
        FileService $fileService,
        Csv $csv
    ) {
        $scratchFileDir = Storage::path(config('config.scratch_dir').'/'.md5($this->import->file));

        $project = $projectRepo->getProjectForDarwinImportJob($this->import->project_id);
        $users = $project->group->users->push($project->group->owner);

        try {
            $fileService->makeDirectory($scratchFileDir);
            $importFilePath = Storage::path($this->import->file);

            $fileService->unzip($importFilePath, $scratchFileDir);

            $dwcProcess->process($this->import->project_id, $scratchFileDir);

            $dupsCsvName = md5($this->import->id).'dup.csv';
            $dupName = $csv->createReportCsv($dwcProcess->getDuplicates(), $dupsCsvName);

            $rejCsvName = md5($this->import->id).'rej.csv';
            $rejName = $csv->createReportCsv($dwcProcess->getRejectedMedia(), $rejCsvName);

            Notification::send($users, new ImportComplete($project->title, $dupName, $rejName));

            if ($project->workflow->actors->contains('title', 'OCR') && $dwcProcess->getSubjectCount() > 0) {
                OcrCreateJob::dispatch($project->id);
            }

            $fileService->filesystem->cleanDirectory($scratchFileDir);
            $fileService->filesystem->deleteDirectory($scratchFileDir);
            $fileService->filesystem->delete($importFilePath);
            $this->import->delete();
            $this->delete();
        } catch (Exception $e) {
            $this->import->error = 1;
            $this->import->save();
            $fileService->filesystem->cleanDirectory($scratchFileDir);
            $fileService->filesystem->deleteDirectory($scratchFileDir);

            Notification::send($users, new DarwinCoreImportError($project->title, $project->id, $e->getMessage()));

            $this->delete();
        }
    }
}
