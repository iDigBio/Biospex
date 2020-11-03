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
use App\Services\Model\ExpeditionService;

class ExpeditionsController extends Controller
{
    /**
     * Displays Expeditions on public page.
     *
     * @param \App\Services\Model\ExpeditionService $expeditionService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(ExpeditionService $expeditionService)
    {
        $results = $expeditionService->getExpeditionPublicIndex();

        list($expeditions, $expeditionsCompleted) = $results->partition(function($expedition) {
            return $expedition->nfnActor->pivot->completed === 0;
        });

        return view('front.expedition.index', compact('expeditions', 'expeditionsCompleted'));
    }

    /**
     * Displays Completed Expeditions on public page.
     *
     * @param \App\Services\Model\ExpeditionService $expeditionService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sort(ExpeditionService $expeditionService)
    {
        if ( ! request()->ajax()) {
            return null;
        }

        $type = request()->get('type');
        $sort = request()->get('sort');
        $order = request()->get('order');
        $projectId = request()->get('id');

        list($active, $completed) = $expeditionService->getExpeditionPublicIndex($sort, $order, $projectId)
            ->partition(function($expedition) {
                return $expedition->nfnActor->pivot->completed === 0;
        });

        $expeditions = $type === 'active' ? $active : $completed;

        return view('front.expedition.partials.expedition', compact('expeditions'));
    }
}
