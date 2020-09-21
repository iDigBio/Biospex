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

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\RapidRecord;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class IndexController extends Controller
{
    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('admin.get.index');
        }

        return redirect()->route('app.get.login');
    }

    /**
     * Show rapid record
     *
     * @param string $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(string $id)
    {
        return view('show', compact('id'));
    }

    /**
     *
     * @param \App\Repositories\Interfaces\RapidRecord $rapidRecordInterface
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(RapidRecord $rapidRecordInterface, string $id)
    {
        if (! request()->ajax()) {
            return response()->json(['error' => t('Request must be ajax')]);
        }

        $record = $rapidRecordInterface->find($id);

        $mapped = collect($record->getAttributes())->map(function($value, $field){
            if ($field === '_id') {
                return [$field, (string)$value];
            }

            if ($field === 'updated_at' || $field === 'created_at'){
                return [$field, $value->toDateTime()->format('Y-m-d')];
            }

            return [$field, $value];
        })->values();

        return DataTables::collection($mapped)->toJson();
    }
}
