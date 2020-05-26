<?php
/**
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

use App\Facades\FlashHelper;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Import;
use App\Jobs\DwcFileImportJob;
use App\Jobs\DwcUriImportJob;
use App\Jobs\RecordsetImportJob;
use App\Repositories\Interfaces\Project;

class ImportsController extends Controller
{
    /**
     * Add data to project
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param $projectId
     * @return \Illuminate\View\View
     */
    public function index(Project $projectContract, $projectId)
    {
        $project = $projectContract->find($projectId);

        return view('admin.partials.import-modal-body', compact('project'));
    }

    /**
     * Upload DWC file.
     *
     * @param \App\Repositories\Interfaces\Import $importContract
     * @return \Illuminate\Http\RedirectResponse
     */
    public function dwcFile(Import $importContract)
    {
        try {
            $projectId = request()->input('project_id');
            $path = request()->file('dwc-file')->store('imports/subjects');

            $import = $importContract->create([
                'user_id'    => \Auth::user()->id,
                'project_id' => $projectId,
                'file'       => $path
            ]);

            DwcFileImportJob::dispatch($import);

            FlashHelper::success(__('messages.upload_import_success'));

            return redirect()->back();
        }
        catch(\Exception $e)
        {
            FlashHelper::error(__('messages.upload_import_error'));

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
                'user_id'    => \Auth::user()->id,
                'project_id' => $projectId
            ];

            RecordsetImportJob::dispatch($data);

            FlashHelper::success(__('messages.upload_import_success'));

            return redirect()->back();
        }
        catch(\Exception $e)
        {
            FlashHelper::error(__('messages.upload_import_error'));

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
                'user_id' => \Auth::user()->id,
                'url'     => request()->input('dwc-url')
            ];

            DwcUriImportJob::dispatch($data);

            FlashHelper::success(__('messages.upload_import_success'));

            return redirect()->back();
        }
        catch(\Exception $e)
        {
            FlashHelper::error(__('messages.upload_import_error'));

            return redirect()->back();
        }
    }
}
