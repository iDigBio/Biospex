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
use App\Http\Requests\ProductFormRequest;
use App\Jobs\RapidExportDwcJob;
use App\Services\Model\ProductModelService;
use Auth;
use File;
use FlashHelper;
use Illuminate\Http\RedirectResponse;
use Storage;

/**
 * Class ProductController
 *
 * @package App\Http\Controllers\Admin
 */
class ProductController extends Controller
{
    /**
     * @var \App\Services\Model\ProductModelService
     */
    private $productModelService;

    /**
     * ExportController constructor.
     *
     * @param \App\Services\Model\ProductModelService $productModelService
     */
    public function __construct(ProductModelService $productModelService)
    {
        $this->productModelService = $productModelService;
    }

    /**
     * Show projects list for admin page.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Throwable
     */
    public function index()
    {
        $records = $this->productModelService->all();

        $products = $records->filter(function($record){
            return Storage::disk('s3')->exists(config('config.rapid_product_dir') . '/' . $record->key . '.zip');
        });

        return view('product.index', compact('products'));
    }

    /**
     * Dispatch the dwc to process.
     *
     * @param \App\Http\Requests\ProductFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(ProductFormRequest $request): RedirectResponse
    {
        RapidExportDwcJob::dispatch(Auth::user(), $request->all());

        FlashHelper::success(t('The request is processing. You will be notified by email when completed.'));

        return redirect()->route('admin.product.index');
    }
}
