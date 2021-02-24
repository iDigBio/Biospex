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

use App\Jobs\RapidIngestUnzipJob;
use App\Services\Model\RapidHeaderModelService;
use FlashHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\RapidImportFormRequest;
use App\Http\Requests\RapidUpdateFormRequest;
use Auth;
use Illuminate\Http\RedirectResponse;

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
     * @param \App\Services\Model\RapidHeaderModelService $service
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(RapidHeaderModelService $service)
    {
        $count = $service->count();
        return view('ingest.index', compact('count'));
    }

    /**
     * Import original rapid data.
     *
     * @param \App\Http\Requests\RapidImportFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(RapidImportFormRequest $request): RedirectResponse
    {
        $filePath = $request->file('import-file')->store(config('config.rapid_import_dir'));

        if (! $filePath) {
            FlashHelper::warning(t('The import failed to upload. Please contact the administration to determine the error.'));

            return redirect()->route('admin.ingest.index');
        }

        RapidIngestUnzipJob::dispatch(Auth::user(), $filePath, false);

        FlashHelper::success(t('The import uploaded successfully. You will be notified by email when it\'s completed.'));

        return redirect()->route('admin.ingest.index');
    }

    /**
     * Import an update file.
     *
     * @param \App\Http\Requests\RapidUpdateFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(RapidUpdateFormRequest $request): RedirectResponse
    {
        $filePath = $request->file('update-file')->store(config('config.rapid_import_dir'));

        if (! $filePath) {
            FlashHelper::warning(t('The update failed to upload. Please contact the administration to determine the error.'));

            return redirect()->route('admin.ingest.index');
        }

        RapidIngestUnzipJob::dispatch(Auth::user(), $filePath);

        FlashHelper::success(t('The update has been uploaded. You will receive an email when the process has been completed.'));

        return redirect()->route('admin.ingest.index');
    }
}
