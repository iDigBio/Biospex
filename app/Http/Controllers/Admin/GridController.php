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
 *
 * @package App\Http\Controllers\Admin
 */
class GridController extends Controller
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
     * GridController constructor.
     *
     * @param JqGridEncoder $grid
     * @param Csv $csv
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
     * @param string $projectId
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function explore(string $projectId)
    {
        try {
            return $this->grid->encodeGridRequestedData(request()->all(), 'explore', (int) $projectId);
        } catch (Exception $e) {
            return response($e->getMessage(), 404);
        }
    }

    /**
     * Show grid in expeditions.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function expeditionsShow(string $projectId, string $expeditionId)
    {
        try {
            return $this->grid->encodeGridRequestedData(request()->all(), 'show', (int) $projectId, (int) $expeditionId);
        } catch (Exception $e) {
            return response($e->getMessage(), 404);
        }
    }

    /**
     * Show grid in expeditions edit.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function expeditionsEdit(string $projectId, string $expeditionId)
    {
        try {
            return $this->grid->encodeGridRequestedData(request()->all(), 'edit', (int) $projectId, (int) $expeditionId);
        } catch (Exception $e) {
            return response($e->getMessage(), 404);
        }
    }

    /**
     * Show grid in expeditions create.
     *
     * @param string $projectId
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function expeditionsCreate(string $projectId)
    {
        try {
            return $this->grid->encodeGridRequestedData(request()->all(), 'create', (int) $projectId);
        } catch (Exception $e) {
            return response($e->getMessage(), 404);
        }
    }

    /**
     * Export csv from grid button.
     *
     * @param string $projectId
     * @param string|null $expeditionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(string $projectId, string $expeditionId = null)
    {
        $attributes = [
            'projectId' => (int) $projectId,
            'expeditionId' => (int) $expeditionId,
            'postData' => ['filters' => request()->exists('filters') ? request()->get('filters') : null],
            'route' => request()->get('route')
        ];

        GridExportCsvJob::dispatch(Auth::user(), $attributes);

        return response()->json(['success' => true], 200);
    }

    /**
     * Delete subject if not part of expedition process.
     *
     * @note Removed from jqGrid but keep code in case we need it again.
     * @param \App\Repositories\SubjectRepository $subjectRepo
     * @param \App\Repositories\ExpeditionRepository $expeditionRepo
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(SubjectRepository $subjectRepo, ExpeditionRepository $expeditionRepo)
    {
        if (! request()->ajax()) {
            return response()->json(['error' => 'Delete must be performed via ajax.'], 404);
        }

        if (! request()->get('ids')) {
            return response()->json(['error' => 'Only delete operation allowed.'], 404);
        }

        $subjectIds = explode(',', request()->get('ids'));

        $subjects = $subjectRepo->whereIn('_id', $subjectIds);

        $subjects->reject(function ($subject) use($expeditionRepo) {
            foreach ($subject->expedition_ids as $expeditionId) {
                $expedition = $expeditionRepo->findExpeditionHavingWorkflowManager($expeditionId);
                if ($expedition !== null) {
                    return true;
                }
            }

            return false;
        })->each(function ($subject) use ($subjectRepo) {
            $subject->delete();
        });

        return response()->json(['success']);
    }
}


