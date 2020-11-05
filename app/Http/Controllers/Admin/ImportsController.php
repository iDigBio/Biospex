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

use Flash;
use App\Http\Controllers\Controller;
use App\Services\Model\ImportService;
use App\Jobs\DwcFileImportJob;
use App\Jobs\DwcUriImportJob;
use App\Jobs\RecordsetImportJob;
use App\Services\Model\ProjectService;
use Auth;
use Exception;

/**
 * Class ImportsController
 *
 * @package App\Http\Controllers\Admin
 */
class ImportsController extends Controller
{
    /**
     * Add data to project
     *
     * @param \App\Services\Model\ProjectService $projectService
     * @param $projectId
     * @return \Illuminate\View\View
     */
    public function index(ProjectService $projectService, $projectId)
    {
        $project = $projectService->find($projectId);

        return view('admin.partials.import-modal-body', compact('project'));
    }

    /**
     * Upload DWC file.
     *
     * @param \App\Services\Model\ImportService $importService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function dwcFile(ImportService $importService)
    {
        try {
            $projectId = request()->input('project_id');
            $path = request()->file('dwc-file')->store('imports/subjects');

            $import = $importService->create([
                'user_id'    => Auth::user()->id,
                'project_id' => $projectId,
                'file'       => $path
            ]);

            DwcFileImportJob::dispatch($import);

            Flash::success(t('Upload was successful. You will receive an email when your import data have been processed.'));

            return redirect()->back();
        }
        catch(Exception $e)
        {
            Flash::error(t('Error uploading file'));

            return redirect()->back();
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
            $projectId = request()->input('project_id');

            $data = [
                'id'         => request()->input('recordset'),
                'user_id'    => Auth::user()->id,
                'project_id' => $projectId
            ];

            RecordsetImportJob::dispatch($data);

            Flash::success(t('Upload was successful. You will receive an email when your import data have been processed.'));

            return redirect()->back();
        }
        catch(Exception $e)
        {
            Flash::error(t('Error uploading file'));

            return redirect()->back();
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
            $projectId = request()->input('project_id');

            $data = [
                'id'      => $projectId,
                'user_id' => Auth::user()->id,
                'url'     => request()->input('dwc-url')
            ];

            DwcUriImportJob::dispatch($data);

            Flash::success(t('Upload was successful. You will receive an email when your import data have been processed.'));

            return redirect()->back();
        }
        catch(Exception $e)
        {
            Flash::error(t('Error uploading file'));

            return redirect()->back();
        }
    }
}
