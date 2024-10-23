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
use App\Notifications\Generic;
use App\Notifications\Traits\ButtonTrait;
use App\Services\Process\CreateReportService;
use App\Services\Process\DarwinCore;
use App\Services\Project\ProjectService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Notification;
use Throwable;

/**
 * Class DwcFileImportJob
 */
class DwcFileImportJob implements ShouldQueue
{
    use ButtonTrait, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 1800;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Import $import)
    {
        $this->onQueue(config('config.queue.import'));
    }

    public function handle(
        ProjectService $projectService,
        DarwinCore $dwcProcess,
        CreateReportService $createReportService
    ) {
        $scratchFileDir = Storage::disk('efs')->path(config('config.scratch_dir').'/'.md5($this->import->file));
        $importFilePath = Storage::disk('efs')->path($this->import->file);

        $project = $projectService->getProjectForDarwinImportJob($this->import->project_id);
        $users = $project->group->users->push($project->group->owner);

        try {
            $this->makeDirectory($scratchFileDir);

            $this->unzip($importFilePath, $scratchFileDir);

            $dwcProcess->process($this->import->project_id, $scratchFileDir);

            $dupsCsvName = md5($this->import->id).'dup.csv';
            $dupName = $createReportService->createCsvReport($dupsCsvName, $dwcProcess->getDuplicates());
            $dupButton = [];
            if ($dupName !== null) {
                $dupRoute = route('admin.downloads.report', ['file' => $dupName]);
                $dupButton = $this->createButton($dupRoute, t('View Duplicate Records'));
            }

            $rejCsvName = md5($this->import->id).'rej.csv';
            $rejName = $createReportService->createCsvReport($rejCsvName, $dwcProcess->getRejectedMedia());
            $rejButton = [];
            if ($rejName !== null) {
                $rejRoute = route('admin.downloads.report', ['file' => $rejName]);
                $rejButton = $this->createButton($rejRoute, t('View Rejected Records'), 'error');
            }

            $buttons = array_merge($dupButton, $rejButton);

            $attributes = [
                'subject' => t('DWC File Import Complete'),
                'html' => [
                    t('The subject import for %s has been completed.', $project->title),
                    t('OCR processing may take longer and you will receive an email when it is complete.'),
                ],
                'buttons' => $buttons,
            ];

            Notification::send($users, new Generic($attributes));

            TesseractOcrCreateJob::dispatch($project->id);
            File::cleanDirectory($scratchFileDir);
            File::deleteDirectory($scratchFileDir);
            File::delete($importFilePath);
            $this->import->delete();
            $this->delete();

        } catch (Throwable $throwable) {
            $this->import->error = 1;
            $this->import->save();
            File::cleanDirectory($scratchFileDir);
            File::deleteDirectory($scratchFileDir);

            $attributes = [
                'subject' => t('DWC File Import Error'),
                'html' => [
                    t('An error occurred while importing the Darwin Core Archive.'),
                    t('Project: %s', $project->title),
                    t('ID: %s'.$project->id),
                    t('File: %s', $throwable->getFile()),
                    t('Line: %s', $throwable->getLine()),
                    t('Message: %s', $throwable->getMessage()),
                    t('The Administration has been notified. If you are unable to resolve this issue, please contact the Administration.'),
                ],
            ];
            Notification::send($users, new Generic($attributes, true));

            $this->delete();
        }
    }

    /**
     * @throws \Exception
     */
    private function makeDirectory($dir)
    {
        if (! File::isDirectory($dir) && ! File::makeDirectory($dir, 0775, true)) {
            throw new Exception(t('Unable to create directory: :directory', [':directory' => $dir]));
        }

        if (! File::isWritable($dir) && ! chmod($dir, 0775)) {
            throw new Exception(t('Unable to make directory writable: %s', $dir));
        }
    }

    /**
     * Unzip file in directory.
     */
    private function unzip($zipFile, $dir)
    {
        shell_exec("unzip $zipFile -d $dir");
    }
}
