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
use Flash;

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
        $geolocateFrms = $this->rapidExportService->getFormsByDestination('geolocate');

        return view('export.index', compact('geolocateFrms'));
    }

    /**
     * Show geolocate forms.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function geolocate()
    {
        /*
        if (! request()->ajax()) {
            return response()->json([t('Request must be ajax')]);
        }
        */

        $data = $this->rapidExportService->showGeoLocateFrm(request()->get('frm')); // request()->get('frm')

        return view('export.partials.geolocate', compact('data'));
    }

    /**
     * Dispatch the export to process.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function geolocateCreate()
    {
        $fields = $this->rapidExportService->mapExportFields(request()->all());

        $this->rapidExportService->saveForm($fields);

        //RapidExportJob::dispatch(Auth::user(), $this->example());

        Flash::success(t('The export is processing. You will be notified by email when it\'s complete.'));

        return redirect()->route('admin.export.index');
    }

    public function example()
    {
        return [
            "_token"            => "mZiAuiRMGz91yza9fx9tfP3MI2X81r5dfI7mDsSI",
            "exportDestination" => "geolocate",
            "exportType"        => "csv",
            "entries"           => "4",
            "exportFields"      => [
                [
                    "order"  => null,
                    "field"  => "CatalogNumber",
                    "_gbifP" => "catalogNumber_gbifP",
                    "_gbifR" => null,
                    "_idbR"  => null,
                    "_idbP"  => null,
                    "_rapid" => null,
                ],
                [
                    "order"  => "_gbifR,_gbifP,_idbR,_idbP,_rapid",
                    "field"  => "ScientificName",
                    "_gbifR" => "scientificName_gbifR",
                    "_gbifP" => "scientificName_gbifP",
                    "_idbR"  => null,
                    "_idbP"  => null,
                    "_rapid" => null,
                ],
                [
                    "order"  => "_idbR,_idbP,_gbifP,_gbifR,_rapid",
                    "field"  => "Country",
                    "_idbR"  => "country_idbR",
                    "_idbP"  => "country_idbP",
                    "_gbifP" => null,
                    "_gbifR" => null,
                    "_rapid" => null,
                ],
                [
                    "order"  => "_rapid,_gbifP,_gbifR,_idbR,_idbP",
                    "field"  => "Locality",
                    "_rapid" => "country_rapid",
                    "_gbifP" => "locality_gbifP",
                    "_gbifR" => "locality_gbifR",
                    "_idbR"  => null,
                    "_idbP"  => null,
                ],
            ],
        ];
    }
}
