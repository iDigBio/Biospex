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
use App\Repositories\WeDigBioEventTranscriptionRepository;
use App\Services\Chart\BiospexEventStepChartProcess;
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
     * Call polling command when process modal opened. Trigger inside biospex.js
     */
    public function poll()
    {
        if (request()->ajax()) {
            Artisan::call('ocr:poll');
            Artisan::call('export:poll');
        }
    }

    /**
     * Load amChart.
     *
     * @param \App\Repositories\AmChartRepository $amChartRepo
     * @param $projectId
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function loadAmChart(AmChartRepository $amChartRepo, $projectId)
    {
        if (! request()->ajax() || $projectId === null) {
            return response()->json(['html' => 'hitting null']);
        }

        $record = $amChartRepo->findBy('project_id', $projectId);

        return json_decode($record->data);
    }

    /**
     * @param \App\Repositories\EventRepository $eventRepo
     * @param string $eventId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function scoreboard(EventRepository $eventRepo, string $eventId)
    {
        $event = $eventRepo->getEventScoreboard($eventId, ['id']);

        if (! request()->ajax() || is_null($event)) {
            return response()->json(['html' => 'Error retrieving the Event']);
        }

        return view('common.scoreboard-content', ['event' => $event]);
    }

    /**
     * Display for event step charts.
     *
     * @param \App\Services\Chart\BiospexEventStepChartProcess $service
     * @param string $eventId
     * @param string|null $timestamp
     * @return \Illuminate\Http\JsonResponse
     */
    public function eventStepChart(BiospexEventStepChartProcess $service, string $eventId, string $timestamp = null): JsonResponse
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
        if (request()->ajax()) {
            BingoJob::dispatch($bingoId, $mapId);
        }
    }

    /**
     * @param \App\Repositories\EventRepository $eventRepo
     * @param string $eventId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function weDigBioProgress(
        WeDigBioEventDateRepository $weDigBioEventDateRepository,
        WeDigBioEventTranscriptionRepository $weDigBioEventTranscriptionRepository)
    {
        if (! request()->ajax()) {
            return response()->json(['html' => 'Error retrieving the Event']);
        }

        $weDigBioDate = $weDigBioEventDateRepository->findBy('active', 1);
        $total = $weDigBioDate === null ? 0 : $weDigBioEventTranscriptionRepository->getTotal($weDigBioDate->id);
        $transcriptions = $weDigBioDate === null ? null : $weDigBioEventTranscriptionRepository->getTranscriptionsByDateId($weDigBioDate->id);

        return view('common.wedigbio-progress-content', compact('weDigBioDate', 'transcriptions', 'total'));
    }

    /**
     * @param \App\Repositories\EventRepository $eventRepo
     * @param string $eventId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function weDigBioRate(EventRepository $eventRepo, string $eventId)
    {
        $event = $eventRepo->getEventScoreboard($eventId, ['id']);

        if (! request()->ajax() || is_null($event)) {
            return response()->json(['html' => 'Error retrieving the Event']);
        }

        return view('common.scoreboard-content', ['event' => $event]);
    }

}
