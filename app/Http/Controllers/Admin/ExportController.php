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
use App\Jobs\RapidExportJob;
use App\Services\RapidExportService;
use FlashHelper;
use Auth;
use Illuminate\Http\RedirectResponse;

/**
 * Class ExportController
 *
 * @package App\Http\Controllers\Admin
 */
class ExportController extends Controller
{
    /**
     * @var \App\Services\RapidExportService
     */
    private $rapidExportService;

    /**
     * DashboardController constructor.
     *
     * @param \App\Services\RapidExportService $rapidExportService
     */
    public function __construct(RapidExportService $rapidExportService)
    {
        $this->rapidExportService = $rapidExportService;
    }

    /**
     * Show projects list for admin page.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Throwable
     */
    public function index()
    {
        $forms = $this->rapidExportService->getFormsSelect();

        return view('export.index', compact('forms'));
    }

    /**
     * Show exports forms.
     *
     * @param string|null $destination
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function show(string $destination = null)
    {
        if (! request()->ajax()) {
            return response()->json([t('Request must be ajax')]);
        }

        try {
            $data = $this->rapidExportService->getForm($destination, request()->get('frm'));

            return view('export.partials.export-form', compact('data', 'destination'));
        }
        catch (\Exception $e) {
            return response()->json([$e->getMessage()]);
        }
    }

    /**
     * Dispatch the export to process.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(): RedirectResponse
    {
        RapidExportJob::dispatch(Auth::user(), request()->all());

        FlashHelper::success(t('The export is processing. You will be notified by email when completed.'));

        return redirect()->route('admin.get.index');
    }

    /**
     * Delete export.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(int $id): RedirectResponse
    {
        $form = $this->rapidExportService->findFormById($id);

        $this->rapidExportService->deleteExport($form, Auth::id());

        return redirect()->route('admin.export.index');
    }
}
