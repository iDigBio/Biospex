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
use App\Models\AmChart;
use App\Services\Models\WeDigBioEventDateModelService;
use App\Services\Chart\BiospexEventRateChartProcess;
use App\Services\Chart\WeDigBioEventRateChartProcess;
use App\Services\Models\EventModelService;
use Artisan;
use Illuminate\Http\JsonResponse;

/**
 * Class AjaxController
 *
 * @package App\Http\Controllers\Front
 */
class AjaxController extends Controller
{
    /**
     * Load amChart.
     *
     * @param \App\Models\AmChart $amChart
     * @param $projectId
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function loadAmChart(AmChart $amChart, $projectId)
    {
        if (! \Request::ajax() || $projectId === null) {
            return response()->json(['html' => 'hitting null']);
        }

        $record = $amChart->where('project_id', $projectId)->first();

        return json_decode($record->data);
    }

    /**
     * Load event scoreboard.
     * @param \App\Services\Models\EventModelService $eventModelService
     * @param string $eventId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function scoreboard(EventModelService $eventModelService, string $eventId)
    {
        $event = $eventModelService->getEventScoreboard($eventId, ['id']);

        if (! \Request::ajax() || is_null($event)) {
            return response()->json(['html' => 'Error retrieving the Event']);
        }

        return \View::make('common.scoreboard-content', ['event' => $event]);
    }

    /**
     * Display for event step charts.
     *
     * @param \App\Services\Chart\BiospexEventRateChartProcess $service
     * @param string $eventId
     * @param string|null $timestamp
     * @return \Illuminate\Http\JsonResponse
     */
    public function eventStepChart(BiospexEventRateChartProcess $service, string $eventId, string $timestamp = null): JsonResponse
    {
        $result = $service->eventStepChart($eventId, $timestamp);

        return response()->json($result);
    }

    /**
     * Trigger bingo winner.
     *
     * @param string $bingoId
     * @param string $mapId
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
     * @param \App\Services\Models\WeDigBioEventDateModelService $weDigBioEventDateModelService
     * @param string $dateId
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
     * @param \App\Services\Models\WeDigBioEventDateModelService $weDigBioEventDateModelService
     * @param string $dateId
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
     * @param \App\Services\Chart\WeDigBioEventRateChartProcess $weDigBioEventRateChartProcess
     * @param string $dateId
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
