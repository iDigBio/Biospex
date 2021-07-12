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

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\Model\ProductModelService;
use App\Services\Model\RapidRecordModelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Storage;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class IndexController
 *
 * @package App\Http\Controllers\Front
 */
class IndexController extends Controller
{
    /**
     * Home page.
     *
     * @param \App\Services\Model\ProductModelService $productModelService
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(ProductModelService $productModelService)
    {
        $records = $productModelService->all();

        $products = $records->filter(function($record){
            return Storage::exists(config('config.rapid_product_dir') . '/' . $record->key . '.zip');
        });

        return view('home', compact('products'));
    }

    /**
     * Show rapid record.
     *
     * @param \App\Services\Model\RapidRecordModelService $rapidRecordModelService
     * @param string $view
     * @param string|null $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(RapidRecordModelService $rapidRecordModelService, string $view, string $id = null)
    {
        $newId = $id === null ? $view : $id;

        $record = $rapidRecordModelService->find($newId);

        $dataVars = isset($id) ? ['id' => $id, 'view' => $view] : ['id' => $view];

        return view('show', compact('record', 'dataVars'));
    }

    /**
     * @param \App\Services\Model\RapidRecordModelService $rapidRecordModelService
     * @param string $id
     * @param string|null $view
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     * "idigbio_flags_gbifP", // not available
     * "verbatimCoordinates_gbifP", // not available
     * "verbatimLatitude_gbifP", // not available
     * "verbatimLongitude_gbifP", // not available
     *
     */
    public function data(RapidRecordModelService $rapidRecordModelService, string $id, string $view = null): JsonResponse
    {
        if (! request()->ajax()) {
            return response()->json(['error' => t('Request must be ajax')]);
        }

        $record = $rapidRecordModelService->find($id);

        $mapped = collect($record->getAttributes())->map(function ($value, $field) {
            if ($field === '_id') {
                return [$field, (string) $value];
            }

            if ($field === 'updated_at' || $field === 'created_at') {
                return [$field, $value->toDateTime()->format('Y-m-d')];
            }

            return [$field, $value];
        });

        if (!isset($view)) {
            return DataTables::collection($mapped->values())->toJson();
        }

        $order = json_decode(File::get(config('config.'.$view.'_view_file')), true);

        $transformed = collect($order)->filter(function($item) use($mapped) {
            return isset($mapped[$item]);
        })->mapWithKeys(function($item) use ($mapped){
            return [$item => $mapped[$item]];
        });

        $merged = $transformed->merge($mapped);

        return DataTables::collection($merged->values())->toJson();
    }
}
