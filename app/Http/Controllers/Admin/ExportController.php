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

use App\Http\Controllers\Controller;
use App\Services\RapidExportService;

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
    public function __construct(RapidExportService $rapidExportService) {

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
        return view('export.index');
    }

    public function geolocate()
    {
        if (! request()->ajax()) {
            return response()->json([t('Request must be ajax')]);
        }

        $count = old('entries', 1);

        $exportSelect = $this->rapidExportService->createExportFieldSelect($count);

        $headers = $this->rapidExportService->getHeader();
        $mapped =  $this->rapidExportService->mapColumns($headers);
        $groupedHeaders = view('partials.grouped-headers', compact('mapped', 'count'))->render();

        return view('export.partials.geolocate', compact('exportSelect', 'groupedHeaders', 'count'));
    }


    public function geolocateCreate()
    {
        dd(request()->all());
    }
}
