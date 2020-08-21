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
use App\Services\JqGridJsonEncoderService;
use App\Services\CsvService;
use Exception;

class GridsController extends Controller
{
    /**
     * @var
     */
    public $grid;

    /**
     * @var
     */
    public $fields;

    /**
     * @var CsvService
     */
    public $csv;

    /**
     * GridsController constructor.
     *
     * @param \App\Services\JqGridJsonEncoderService $grid
     * @param \App\Services\CsvService $csv
     */
    public function __construct(
        JqGridJsonEncoderService $grid,
        CsvService $csv
    ) {
        $this->grid = $grid;
        $this->csv = $csv;
    }

    /**
     * Load grid model and column names
     */
    public function load()
    {
        try {
            $model = $this->grid->loadGridModel();
            return response()->json($model);
        } catch (Exception $e) {
            return response($e->getMessage(), 404);
        }
    }

    /**
     * Load grid data.
     *
     * @return string
     */
    public function read()
    {
        try {
            $rows = $this->grid->encodeGridRequestedData(request()->all());

            return response()->json($rows);
        } catch (Exception $e) {
            return response($e->getMessage(), 404);
        }
    }
}


