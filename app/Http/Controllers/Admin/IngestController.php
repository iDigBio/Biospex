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

use App\Jobs\RapidUpdateJob;
use App\Services\RapidIngestService;
use FlashHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\RapidImportFormRequest;
use App\Http\Requests\RapidUpdateFormRequest;
use App\Jobs\RapidImportJob;
use Auth;
use Str;

/**
 * Class IngestController
 *
 * @package App\Http\Controllers\Admin
 */
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
            FlashHelper::warning(t('The import failed to upload. Please contact the administration to determine the error.'));

            return redirect()->route('admin.ingest.index');
        }

        RapidImportJob::dispatch(Auth::user(), $path);

        FlashHelper::success(t('The import uploaded successfully. You will be notified by email when it\'s completed.'));

        return redirect()->route('admin.ingest.index');
    }

    /**
     * Import an update file.
     *
     * @param \App\Http\Requests\RapidUpdateFormRequest $request
     * @param \App\Services\RapidIngestService $rapidIngestService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(RapidUpdateFormRequest $request, RapidIngestService $rapidIngestService)
    {
        $file = $request->file('update-file');
        $fileOrigName = $file->getClientOriginalName();
        $filePath = $file->storeAs(config('config.rapid_import_dir'), Str::random(10) .'-'. $fileOrigName);

        if (! $filePath) {
            FlashHelper::warning(t('The update failed to upload. Please contact the administration to determine the error.'));

            return redirect()->route('admin.ingest.index');
        }

        RapidUpdateJob::dispatch(Auth::user(), $filePath, $fileOrigName);

        FlashHelper::success(t('The update has been uploaded. You will receive an email when the process has been completed.'));

        return redirect()->route('admin.ingest.index');
    }
}
