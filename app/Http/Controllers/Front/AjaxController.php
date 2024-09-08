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
use App\Jobs\BingoJob;
use App\Services\Chart\WeDigBioEventRateChartProcess;
use App\Services\Models\EventModel;
use App\Services\Models\WeDigBioEventDateModelService;

/**
 * Class AjaxController
 */
class AjaxController extends Controller
{
    /**
     * Load event scoreboard.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function scoreboard(EventModel $eventModel, string $eventId)
    {
        $event = $eventModel->getEventScoreboard($eventId, ['id']);

        if (! \Request::ajax() || is_null($event)) {
            return response()->json(['html' => 'Error retrieving the Event']);
        }

        return \View::make('common.scoreboard-content', ['event' => $event]);
    }

    /**
     * Trigger bingo winner.
     */
    public function bingoWinner(string $bingoId, string $mapId)
    {
        if (\Request::ajax()) {
            BingoJob::dispatch($bingoId, $mapId);
        }
    }

    /**
     * Show progress for wedigbio events.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function weDigBioProgress(WeDigBioEventDateModelService $weDigBioEventDateModelService, string $dateId)
    {
        if (! \Request::ajax()) {
            return response()->json(['html' => 'Error retrieving the WeDigBio Event']);
        }

        $weDigBioDate = $weDigBioEventDateModelService->getWeDigBioEventTranscriptions((int) $dateId);

        return \View::make('common.wedigbio-progress-content', compact('weDigBioDate'));
    }

    /**
     * Returns titles of projects that have transcriptions from WeDigBio.
     *
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function getProjectsForWeDigBioRateChart(WeDigBioEventDateModelService $weDigBioEventDateModelService, string $dateId)
    {
        if (! \Request::ajax()) {
            return response()->json(['html' => 'Request must be ajax.']);
        }

        return $weDigBioEventDateModelService->getProjectsForWeDigBioRateChart((int) $dateId);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function weDigBioRate(WeDigBioEventRateChartProcess $weDigBioEventRateChartProcess, string $dateId)
    {
        $result = $weDigBioEventRateChartProcess->getWeDigBioEventRateChart((int) $dateId);

        if (is_null($result)) {
            return response()->json(['html' => 'Error retrieving the WeDigBio Event']);
        }

        return response()->json($result);
    }
}
