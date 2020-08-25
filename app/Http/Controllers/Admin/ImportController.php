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

use App\Http\Requests\RapidUpdateSelectFormRequest;
use App\Services\RapidIngestService;
use Exception;
use Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\RapidImportFormRequest;
use App\Http\Requests\RapidUpdateFormRequest;
use App\Jobs\RapidImportJob;
use Auth;
use Session;
use Storage;

class ImportController extends Controller
{
    /**
     * Show projects list for admin page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('import');
    }

    /**
     * Import original rapid data.
     *
     * @param \App\Http\Requests\RapidImportFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(RapidImportFormRequest $request)
    {
        $path = $request->file('import-file')->store('imports/rapid');

        if (! $path) {
            Flash::warning(t('The import failed to upload. Please contact the administration to determine the error.'));

            return redirect()->route('admin.import.index');
        }

        RapidImportJob::dispatch(Auth::user(), $path);

        Flash::success(t('The import failed to upload. Please contact the administration to determine the error.'));

        return redirect()->route('admin.import.index');
    }

    /**
     * Import an update file.
     *
     * @param \App\Http\Requests\RapidUpdateFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(RapidUpdateFormRequest $request)
    {
        $path = $request->file('update-file')->store('imports/rapid');

        if (! $path) {
            Flash::warning(t('The update failed to upload. Please contact the administration to determine the error.'));

            return redirect()->route('admin.import.index');
        }

        Session::put('upload-path', $path);

        Flash::success(t('The update has been uploaded. Please select the fields you wish to update and click UPDATE'));

        return redirect()->route('admin.import.select');
    }

    public function select(RapidIngestService $rapidIngestService)
    {
        $headers = $rapidIngestService->testHeaders();
        $groupedHeaders = $rapidIngestService->mapColumns($headers);

        $path = 'storage/path/to/some/file/lmakig.csv';
        return view('update', compact('groupedHeaders','path'));


        if (! Session::exists('upload-path') || ! Storage::exists(Session::get('upload-path'))) {
            Flash::warning(t('The update file path does not exist. Please contact the administration to determine the error.'));

            return redirect()->route('admin.import.index');
        }

        $path = Storage::path(Session::get('upload-path'));

        try {
            $rapidIngestService->loadCsvFile($path);
            $headers = $rapidIngestService->setHeader();

            $groupedHeaders = collect($headers)->reject(function ($item) {
                return $item === '_id';
            })->mapToGroups(function ($item) {
                return $this->groupByColumnTag($item);
            });

            return view('update', compact('groupedHeaders'));
        } catch (Exception $e) {
            Flash::warning(t('The update file path does not exist. Please contact the administration to determine the error.'));

            return redirect()->route('admin.import.index');
        }
    }

    public function selected(RapidUpdateSelectFormRequest $request)
    {
        dd($request->all());
    }

    private function groupByColumnTag(string $item)
    {
        $match = null;

        if(preg_match('/_idbP/', $item, $matches)) {
            $match = $matches[0];
        } elseif(preg_match('/_gbif/', $item, $matches)) {
            $match = $matches[0];
        }
        elseif(preg_match('/_idbR/', $item, $matches)) {
            $match = $matches[0];
        }
        elseif(preg_match('/_rapid/', $item, $matches)) {
            $match = $matches[0];
        }
        else {
            $match = 'common';
        }
        return [$match => $item];
    }
}
