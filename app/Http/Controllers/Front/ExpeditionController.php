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
use App\Repositories\ExpeditionRepository;

/**
 * Class ExpeditionController
 *
 * @package App\Http\Controllers\Front
 */
class ExpeditionController extends Controller
{
    /**
     * Displays Expeditions on public page.
     *
     * @param \App\Repositories\ExpeditionRepository $expeditionRepo
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(ExpeditionRepository $expeditionRepo)
    {
        $results = $expeditionRepo->getExpeditionPublicIndex();
        $project = $results->first()->project;

        [$expeditions, $expeditionsCompleted] = $results->partition(function($expedition) {
            return $expedition->completed === 0;
        });

        return \View::make('front.expedition.index', compact('expeditions', 'project', 'expeditionsCompleted'));
    }

    /**
     * Displays Completed Expeditions on public page.
     *
     * @param \App\Repositories\ExpeditionRepository $expeditionRepo
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sort(ExpeditionRepository $expeditionRepo)
    {
        if ( ! \Request::ajax()) {
            return null;
        }

        $type = \Request::get('type');
        $sort = \Request::get('sort');
        $order = \Request::get('order');
        $projectId = \Request::get('id');

        [$active, $completed] = $expeditionRepo->getExpeditionPublicIndex($sort, $order, $projectId)
            ->partition(function($expedition) {
                return $expedition->completed;
        });

        $expeditions = $type === 'active' ? $active : $completed;

        return \View::make('front.expedition.partials.expedition', compact('expeditions'));
    }
}
