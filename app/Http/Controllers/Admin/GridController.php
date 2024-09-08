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
use App\Jobs\GridExportCsvJob;
use App\Repositories\ExpeditionRepository;
use App\Repositories\SubjectRepository;
use App\Services\Csv\Csv;
use App\Services\Grid\JqGridEncoder;
use Auth;
use Exception;

/**
 * Class GridController
 */
class GridController extends Controller
{
    public $grid;

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
     * GridController constructor.
     */
    public function __construct(JqGridEncoder $grid, Csv $csv)
    {
        $this->grid = $grid;
        $this->csv = $csv;
    }

    /**
     * Load grid model and column names
     */
    public function load()
    {
        return $this->grid->loadGridModel($this->projectId);
    }

    /**
     * Load grid data.
     *
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function explore(int $projectId)
    {
        try {
            return $this->grid->encodeGridRequestedData(\Request::all(), 'explore', (int) $projectId);
        } catch (Exception $e) {
            return response($e->getMessage(), 404);
        }
    }

    /**
     * Show grid in expeditions.
     *
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function expeditionsShow(int $projectId, int $expeditionId)
    {
        try {
            return $this->grid->encodeGridRequestedData(\Request::all(), 'show', (int) $projectId, (int) $expeditionId);
        } catch (Exception $e) {
            return response($e->getMessage(), 404);
        }
    }

    /**
     * Show grid in expeditions edit.
     *
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function expeditionsEdit(int $projectId, int $expeditionId)
    {
        try {
            return $this->grid->encodeGridRequestedData(\Request::all(), 'edit', (int) $projectId, (int) $expeditionId);
        } catch (Exception $e) {
            return response($e->getMessage(), 404);
        }
    }

    /**
     * Show grid in expeditions create.
     *
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function expeditionsCreate(int $projectId)
    {
        try {
            return $this->grid->encodeGridRequestedData(\Request::all(), 'create', (int) $projectId);
        } catch (Exception $e) {
            return response($e->getMessage(), 404);
        }
    }

    /**
     * Export csv from grid button.
     *
     * @param  string|null  $expeditionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(int $projectId, ?int $expeditionId = null)
    {
        $attributes = [
            'projectId' => (int) $projectId,
            'expeditionId' => (int) $expeditionId,
            'postData' => ['filters' => \Request::exists('filters') ? \Request::get('filters') : null],
            'route' => \Request::get('route'),
        ];

        GridExportCsvJob::dispatch(Auth::user(), $attributes);

        return response()->json(['success' => true], 200);
    }

    /**
     * Delete subject if not part of expedition process.
     *
     * @note Removed from jqGrid but keep code in case we need it again.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(SubjectRepository $subjectRepo, ExpeditionRepository $expeditionRepo)
    {
        if (! \Request::ajax()) {
            return response()->json(['error' => 'Delete must be performed via ajax.'], 404);
        }

        if (! \Request::get('ids')) {
            return response()->json(['error' => 'Only delete operation allowed.'], 404);
        }

        $subjectIds = explode(',', \Request::get('ids'));

        $subjects = $subjectRepo->whereIn('_id', $subjectIds);

        $subjects->reject(function ($subject) use ($expeditionRepo) {
            foreach ($subject->expedition_ids as $expeditionId) {
                $expedition = $expeditionRepo->findExpeditionHavingWorkflowManager($expeditionId);
                if ($expedition !== null) {
                    return true;
                }
            }

            return false;
        })->each(function ($subject) {
            $subject->delete();
        });

        return response()->json(['success']);
    }
}
