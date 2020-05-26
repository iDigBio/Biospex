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
use App\Jobs\GridExportCsvJob;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\Subject;
use App\Services\Grid\JqGridJsonEncoder;
use App\Services\Csv\Csv;

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
     * @var int
     */
    public $projectId;

    /**
     * @var int
     */
    public $expeditionId;

    /**
     * @var string
     */
    public $route;

    /**
     * @var Csv
     */
    public $csv;

    /**
     * @var \App\Repositories\Interfaces\Expedition
     */
    private $expeditionContract;

    /**
     * GridsController constructor.
     *
     * @param JqGridJsonEncoder $grid
     * @param Csv $csv
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     */
    public function __construct(
        JqGridJsonEncoder $grid,
        Csv $csv,
        Expedition $expeditionContract
    )
    {
        $this->grid = $grid;
        $this->csv = $csv;

        $this->projectId = (int) request()->route('projects');
        $this->expeditionId = (int) request()->route('expeditions');
        $this->expeditionContract = $expeditionContract;
    }

    /**
     * Load grid model and column names
     */
    public function load()
    {
        return $this->grid->loadGridModel($this->projectId, request()->route()->getName());
    }

    /**
     * Load grid data.
     *
     * @return string
     */
    public function explore()
    {
        try
        {
            return $this->grid->encodeGridRequestedData(request()->all(), request()->route()->getName(), $this->projectId, $this->expeditionId);
        }
        catch (\Exception $e)
        {
            return response($e->getMessage(), 404);
        }
    }

    /**
     * Show grid in expeditions.
     *
     * @return string
     */
    public function expeditionsShow()
    {
        try
        {
            return $this->grid->encodeGridRequestedData(request()->all(), request()->route()->getName(), $this->projectId, $this->expeditionId);
        }
        catch (\Exception $e)
        {
            return response($e->getMessage(), 404);
        }
    }

    /**
     * Show grid in expeditions edit.
     *
     * @return string
     */
    public function expeditionsEdit()
    {
        try
        {
            return $this->grid->encodeGridRequestedData(request()->all(), request()->route()->getName(), $this->projectId, $this->expeditionId);
        }
        catch (\Exception $e)
        {
            return response($e->getMessage(), 404);
        }
    }

    /**
     * Show grid in expeditions create.
     *
     * @return string
     */
    public function expeditionsCreate()
    {
        try
        {
            return $this->grid->encodeGridRequestedData(request()->all(), request()->route()->getName(), $this->projectId, $this->expeditionId);
        }
        catch (\Exception $e)
        {
            return response($e->getMessage(), 404);
        }
    }

    /**
     * Export csv from grid button.
     *
     * @param $projectId
     * @param null $expeditionId
     */
    public function export($projectId, $expeditionId = null)
    {
        GridExportCsvJob::dispatch(\Auth::user(), $projectId, $expeditionId);

        return;
    }

    /**
     * Delete subject if not part of expedition process.
     *
     * @param \App\Repositories\Interfaces\Subject $subjectContract
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Subject $subjectContract)
    {
        if ( ! request()->ajax())
        {
            return response()->json(['error' => 'Delete must be performed via ajax.'], 404);
        }

        if ( ! request()->get('oper'))
        {
            return response()->json(['error' => 'Only delete operation allowed.'], 404);
        }

        $subjectIds = explode(',', request()->get('id'));

        $subjects = $subjectContract->whereIn('_id', $subjectIds);

        $subjects->reject(function ($subject) {
            foreach ($subject->expedition_ids as $expeditionId)
            {
                $expedition = $this->expeditionContract->findExpeditionHavingWorkflowManager($expeditionId);
                if ($expedition !== null)
                    return true;
            }

            return false;
        })->each(function ($subject) use($subjectContract) {
            $subjectContract->delete($subject->_id);
        });

        return response()->json(['success']);

    }
}


