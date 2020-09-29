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
use App\Jobs\RapidUpdateJob;
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
use Str;

class IngestController extends Controller
{
    /**
     * Show projects list for admin page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('ingest.index');
    }

    /**
     * Import original rapid data.
     *
     * @param \App\Http\Requests\RapidImportFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(RapidImportFormRequest $request)
    {
        $path = $request->file('import-file')->store(config('config.rapid_import_dir'));

        if (! $path) {
            Flash::warning(t('The import failed to upload. Please contact the administration to determine the error.'));

            return redirect()->route('admin.ingest.index');
        }

        RapidImportJob::dispatch(Auth::user(), $path);

        Flash::success(t('The import uploaded successfully. You will be notified by email when it\'s completed.'));

        return redirect()->route('admin.ingest.index');
    }

    /**
     * Import an update file.
     *
     * @param \App\Http\Requests\RapidUpdateFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(RapidUpdateFormRequest $request)
    {
        $file     = $request->file('update-file');
        $fileOrigName = $file->getClientOriginalName();
        $fileName = Str::random(10) .'-'. $fileOrigName;
        $filePath = $file->storeAs(config('config.rapid_import_dir'), $fileName);

        if (! $filePath) {
            Flash::warning(t('The update failed to upload. Please contact the administration to determine the error.'));

            return redirect()->route('admin.ingest.index');
        }

        Session::put(['filePath' => $filePath, 'fileName' => $fileName, 'fileOrigName' => $fileOrigName]);

        Flash::success(t('The update has been uploaded. Please select the fields you wish to update and click UPDATE'));

        return redirect()->route('admin.ingest.select');
    }

    /**
     * Show form for selecting which fields to update for rapid records.
     *
     * @param \App\Services\RapidIngestService $rapidIngestService
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function select(RapidIngestService $rapidIngestService)
    {
        try {
            if (! Session::exists('filePath') || ! Storage::exists(Session::get('filePath'))) {
                Flash::warning(t('The update file path does not exist. Please contact the administration to determine the error.'));

                return redirect()->route('admin.ingest.index');
            }

            $filePath = Session::get('filePath');
            $fileName = Session::get('fileName');
            $fileOrigName = Session::get('fileOrigName');

            $csvFilePath = $rapidIngestService->unzipFile($filePath);
            $rapidIngestService->loadCsvFile($csvFilePath);
            $headers = $rapidIngestService->setHeader();

            $tags = $rapidIngestService->mapColumns($headers);

            return view('ingest.update', compact('tags', 'filePath', 'fileName', 'fileOrigName'));
        } catch (Exception $e) {
            Flash::warning(t('An error occurred while loading the csv file. Please contact the administration to determine the error.'));

            return redirect()->route('admin.ingest.index');
        }
    }

    /**
     * Process selected fields for update and send to job.
     *
     * @param \App\Http\Requests\RapidUpdateSelectFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function selected(RapidUpdateSelectFormRequest $request)
    {
        $updateColumnTags = config('config.column_tags');

        $fields = collect($request->all())->filter(function($item, $key) use ($updateColumnTags){
            return in_array($key, $updateColumnTags);
        })->collapse();

        $fileInfo = [
            'filePath' => $request->get('filePath'),
            'fileName' => $request->get('fileName'),
            'fileOrigName' => $request->get('fileOrigName')
        ];

        RapidUpdateJob::dispatch(Auth::user(), $fileInfo, $fields);

        Flash::success(t('Your selections for the update are being processed. You will be notified by email when complete.'));

        Session::forget(['upload-path', 'filePath', 'fileName', 'fileOrigName']);

        return redirect()->route('admin.ingest.index');
    }
}
