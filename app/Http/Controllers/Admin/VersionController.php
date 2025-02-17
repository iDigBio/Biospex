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

use App\Facades\FlashHelper;
use App\Http\Controllers\Controller;
use App\Jobs\RapidVersionJob;
use App\Services\Model\RapidVersionModelService;
use Auth;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class VersionController
 *
 * @package App\Http\Controllers\Admin
 */
class VersionController extends Controller
{
    /**
     * Show rapid version list of files.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('version.index');
    }

    /**
     * Return data for data tables.
     *
     * @param  \App\Services\Model\RapidVersionModelService  $rapidVersionModelService
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(RapidVersionModelService $rapidVersionModelService): JsonResponse
    {
        /*
        if (! request()->ajax()) {
            return response()->json(['error' => t('Request must be ajax')]);
        }
        */

        $versions = $rapidVersionModelService->allWith(['user']);

        $mapped = $versions->filter(function ($version) {
            return \Storage::disk('s3')->exists(config('config.rapid_version_dir').'/'.$version->file_name);
        })->map(function ($version) {
            return [
                'id'       => $version->id,
                'user'     => $version->user->email,
                'file'     => $version->file_name,
                'created'  => $version->created_at->toDateTime()->format('Y-m-d h:m:s'),
                'download' => $version->present()->download,
            ];
        });

        return DataTables::collection($mapped)->toJson();
    }

    /**
     * Create version file.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        RapidVersionJob::dispatch(Auth::user());

        FlashHelper::success(t('Your request has been submitted and will start in 10 minutes. You will be notified by email when complete.'));

        return redirect()->route('admin.version.index');
    }
}
