<?php declare(strict_types=1);
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
use App\Services\Model\AmChartService;
use File;

/**
 * Class TranscriptionController
 *
 * @package App\Http\Controllers\Front
 */
class TranscriptionController extends Controller
{
    /**
     * Return json data for transcription charts.
     *
     * @param \App\Services\Model\AmChartService $amChartService
     * @param string $projectId
     * @param string $year
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(AmChartService $amChartService, string $projectId, string $year)
    {
        $chart = $amChartService->findBy('project_id', $projectId);

        $file = json_decode(File::get(config('config.project_chart_config')), true);
        $file['series'] = $chart->series[$year];
        $file['data'] = $chart->data[$year];

        return response()->json($file);
    }
}
