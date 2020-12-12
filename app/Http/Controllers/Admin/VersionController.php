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
use App\Services\Model\RapidVersionService;
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

    public function show(RapidVersionService $rapidVersionService)
    {
        if (! request()->ajax()) {
            return response()->json(['error' => t('Request must be ajax')]);
        }

        $versions = $rapidVersionService->allWith(['user']);
        $mapped = $versions->map(function($version){
            return [
                $version->id,
                $version->user->email,
                $version->file_name,
                $version->created_at->toDateTime()->format('Y-m-d h:m:s'),
                route('admin.download.version', [base64_encode($version->file_name)])
            ];
        });

        return DataTables::collection($mapped)->toJson();
    }
}
