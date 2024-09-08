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
use App\Repositories\AmChartRepository;
use App\Repositories\EventRepository;
use App\Repositories\WeDigBioEventDateRepository;
use App\Services\Chart\BiospexEventRateChartProcess;
use App\Services\Chart\WeDigBioEventRateChartProcess;
use Artisan;
use Illuminate\Http\JsonResponse;

/**
 * Class AjaxController
 */
class AjaxController extends Controller
{
    /**
     * Call polling command when process modal opened. Trigger inside biospex.js
     */
    public function poll()
    {
        if (\Request::ajax()) {
            Artisan::call('ocr:poll');
            Artisan::call('export:poll');
        }
    }

    /**
     * Load amChart.
     *
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function loadAmChart(AmChartRepository $amChartRepo, $projectId)
    {
        if (! \Request::ajax() || $projectId === null) {
            return response()->json(['html' => 'hitting null']);
        }

        $record = $amChartRepo->findBy('project_id', $projectId);

        return json_decode($record->data);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function scoreboard(EventRepository $eventRepo, string $eventId)
    {
        $event = $eventRepo->getEventScoreboard($eventId, ['id']);

        if (! \Request::ajax() || is_null($event)) {
            return response()->json(['html' => 'Error retrieving the Event']);
        }

        return \View::make('common.scoreboard-content', ['event' => $event]);
    }

    /**
     * Display for event step charts.
     */
    public function eventStepChart(BiospexEventRateChartProcess $service, string $eventId, ?string $timestamp = null): JsonResponse
    {
        $result = $service->eventStepChart($eventId, $timestamp);

        return response()->json($result);
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
    public function weDigBioProgress(WeDigBioEventDateRepository $weDigBioEventDateRepository, string $dateId)
    {
        if (! \Request::ajax()) {
            return response()->json(['html' => 'Error retrieving the WeDigBio Event']);
        }

        $weDigBioDate = $weDigBioEventDateRepository->getWeDigBioEventTranscriptions((int) $dateId);

        return \View::make('common.wedigbio-progress-content', compact('weDigBioDate'));
    }

    /**
     * Returns titles of projects that have transcriptions from WeDigBio.
     *
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function getProjectsForWeDigBioRateChart(WeDigBioEventDateRepository $weDigBioEventDateRepository, string $dateId)
    {
        if (! \Request::ajax()) {
            return response()->json(['html' => 'Request must be ajax.']);
        }

        return $weDigBioEventDateRepository->getProjectsForWeDigBioRateChart((int) $dateId);
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
