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

use App\Facades\GeneralHelper;
use App\Notifications\DarwinCoreImportError;
use App\Repositories\ImportRepository;
use App\Repositories\ProjectRepository;
use Exception;
use finfo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Class DwcUriImportJob
 *
 * @package App\Jobs
 */
class DwcUriImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var
     */
    public $data;

    /**
     * @var \App\Repositories\ImportRepository
     */
    public $importRepo;

    /**
     * @var \App\Repositories\ProjectRepository
     */
    public $projectRepo;

    /**
     * Create a new job instance.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->onQueue(config('config.import_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Repositories\ImportRepository $importRepo
     * @param \App\Repositories\ProjectRepository $projectRepo
     * @return void
     */
    public function handle(
        ImportRepository $importRepo,
        ProjectRepository $projectRepo
    )
    {
        try
        {
            $fileName = basename($this->data['url']);
            $filePath = Storage::path(config('config.import_dir') . '/' . $fileName);

            $file = file_get_contents(GeneralHelper::urlEncode($this->data['url']));
            if ($file === false)
            {
                throw new Exception(t('Unable to complete zip download for Darwin Core Archive.'));
            }

            if (!$this->checkFileType($file))
            {
                throw new Exception(t('Wrong file type for zip download'));
            }

            if (Storage::put($filePath, $file) === false)
            {
                throw new Exception(t('An error occurred while attempting to save file: %s', $filePath));
            }

            $import = $importRepo->create([
                'user_id'    => $this->data['user_id'],
                'project_id' => $this->data['id'],
                'file'       => $filePath
            ]);

            DwcFileImportJob::dispatch($import);
        }
        catch (Exception $e)
        {
            $project = $projectRepo->findWith($this->data['id'], ['group.owner']);

            $project->group->owner->notify(new DarwinCoreImportError($project->title, $project->id, $e->getMessage()));
        }
    }

    /**
     * Check if file is zip.
     *
     * @param $file
     * @return bool
     */
    protected function checkFileType($file)
    {
        $finfo = new finfo(FILEINFO_MIME);
        [$mime] = explode(';', $finfo->buffer($file));
        $types = ['application/zip', 'application/octet-stream'];
        if (!in_array(trim($mime), $types))
        {
            return false;
        }

        return true;
    }
}
