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
use App\Services\Helpers\GeneralService;
use App\Services\Project\ProjectService;
use Exception;
use finfo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Class DwcUriImportJob
 */
class DwcUriImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 1800;

    /**
     * Create a new job instance.
     */
    public function __construct(protected array $data)
    {
        $this->onQueue(config('config.queue.import'));
    }

    /**
     * Execute the job.
     */
    public function handle(
        Import $import,
        ProjectService $projectService,
        GeneralService $generalService
    ): void {
        $project = $projectService->getProjectForDarwinImportJob($this->data['id']);
        $users = $project->group->users->push($project->group->owner);

        try {
            $fileName = basename($this->data['url']);
            $filePath = config('config.import_dir').'/'.$fileName;

            $file = file_get_contents($generalService->urlEncode($this->data['url']));
            if ($file === false) {
                throw new Exception(t('Unable to complete zip download for Darwin Core Archive.'));
            }

            if (! $this->checkFileType($file)) {
                throw new Exception(t('Wrong file type for zip download'));
            }

            if (Storage::disk('efs')->put($filePath, $file) === false) {
                throw new Exception(t('An error occurred while attempting to save file: %s', $filePath));
            }

            $import = $import->create([
                'user_id' => $this->data['user_id'],
                'project_id' => $this->data['id'],
                'file' => $filePath,
            ]);

            DwcFileImportJob::dispatch($import);
        } catch (Throwable $throwable) {
            $attributes = [
                'subject' => 'DWC Uri Import Error',
                'html' => [
                    t('An error occurred while importing the Darwin Core Archive using a uri.'),
                    t('Project: %s', $project->title),
                    t('ID: %s'.$project->id),
                    t('File: %s', $throwable->getFile()),
                    t('Line: %s', $throwable->getLine()),
                    t('Message: %s', $throwable->getMessage()),
                    t('The Administration has been notified. If you are unable to resolve this issue, please contact the Administration.'),
                ],
            ];
            Notification::send($users, new Generic($attributes, true));
        }
    }

    /**
     * Check if file is zip.
     */
    protected function checkFileType($file): bool
    {
        $finfo = new finfo(FILEINFO_MIME);
        [$mime] = explode(';', $finfo->buffer($file));
        $types = ['application/zip', 'application/octet-stream'];
        if (! in_array(trim($mime), $types)) {
            return false;
        }

        return true;
    }
}
