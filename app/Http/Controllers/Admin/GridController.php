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
use App\Services\Csv\Csv;
use App\Services\Expedition\ExpeditionService;
use App\Services\Grid\JqGridEncoder;
use App\Services\Models\SubjectModelService;
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
     * Delete subject if not part of expedition process.
     *
     * @note Removed from jqGrid but keep code in case we need it again.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(SubjectModelService $subjectModelService, ExpeditionService $expeditionService)
    {
        if (! \Request::ajax()) {
            return response()->json(['error' => 'Delete must be performed via ajax.'], 404);
        }

        if (! \Request::get('ids')) {
            return response()->json(['error' => 'Only delete operation allowed.'], 404);
        }

        $subjectIds = explode(',', \Request::get('ids'));

        $subjects = $subjectModelService->getWhereIn('_id', $subjectIds);

        $subjects->reject(function ($subject) use ($expeditionService) {
            foreach ($subject->expedition_ids as $expeditionId) {
                $expedition = $expeditionService->findExpeditionHavingWorkflowManager($expeditionId);
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
