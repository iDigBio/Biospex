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

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DwcFileImportJob;
use App\Jobs\DwcUriImportJob;
use App\Jobs\RecordsetImportJob;
use App\Models\Import;
use App\Repositories\ProjectRepository;
use Auth;
use Exception;

/**
 * Class ImportController
 *
 * @package App\Http\Controllers\Admin
 */
class ImportController extends Controller
{
    /**
     * Add data to project
     *
     * @param \App\Repositories\ProjectRepository $projectRepo
     * @param $projectId
     * @return \Illuminate\View\View
     */
    public function index(ProjectRepository $projectRepo, $projectId)
    {
        $project = $projectRepo->find($projectId);

        return \View::make('admin.partials.import-modal-body', compact('project'));
    }

    /**
     * Upload DWC file.
     *
     * @param \App\Models\Import $import
     * @return \Illuminate\Http\RedirectResponse
     */
    public function dwcFile(Import $import)
    {
        try {
            $projectId = \Request::input('project_id');
            $path = \Request::file('dwc-file')->store(config('config.import_dir'), 'efs');

            $import = $import->create([
                'user_id'    => Auth::user()->id,
                'project_id' => $projectId,
                'file'       => $path
            ]);

            DwcFileImportJob::dispatch($import);

            \Flash::success(t('Upload was successful. You will receive an email when your import data have been processed.'));

            return back();
        }
        catch(\Throwable $throwable)
        {
            \Flash::error(t('Error uploading file. %', $throwable->getMessage()));

            return back();
        }
    }

    /**
     * Upload record set.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function recordSet()
    {
        try
        {
            $projectId = \Request::input('project_id');

            $data = [
                'id'         => \Request::input('recordset'),
                'user_id'    => Auth::user()->id,
                'project_id' => $projectId
            ];

            RecordsetImportJob::dispatch($data);

            \Flash::success(t('Upload was successful. You will receive an email when your import data have been processed.'));

            return back();
        }
        catch(Exception $e)
        {
            \Flash::error(t('Error uploading file'));

            return back();
        }
    }

    /**
     * Upload Dwc uri.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function dwcUri()
    {
        try
        {
            $projectId = \Request::input('project_id');

            $data = [
                'id'      => $projectId,
                'user_id' => Auth::user()->id,
                'url'     => \Request::input('dwc-url')
            ];

            DwcUriImportJob::dispatch($data);

            \Flash::success(t('Upload was successful. You will receive an email when your import data have been processed.'));

            return back();
        }
        catch(Exception $e)
        {
            \Flash::error(t('Error uploading file'));

            return back();
        }
    }
}
