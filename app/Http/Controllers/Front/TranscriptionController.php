<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\AmChart;
use App\Models\Project;
use File;
use Response;
use Throwable;

/**
 * Class TranscriptionController
 */
class TranscriptionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected AmChart $amChart) {}

    /**
     * Return json data for transcription charts.
     */
    public function __invoke(Project $project, string $year): \Illuminate\Http\JsonResponse
    {
        try {
            $chart = $this->amChart->where('project_id', $project->id)->first();

            $file = json_decode(File::get(config('config.project_chart_config')), true);

            if (! isset($chart->series[$year])) {
                throw new \Exception('No data for this year');
            }
            $file['series'] = $chart->series[$year];

            if (! isset($chart->data[$year])) {
                throw new \Exception('No data for this year');
            }
            $file['data'] = $chart->data[$year];

            return Response::json($file);
        } catch (Throwable $throwable) {
            // TODO: Remove log error once the issue is fixed
            \Log::info('Project: '.$project->id.' Message: '.$throwable->getMessage());

            return Response::json(['error' => $throwable->getMessage()]);
        }
    }
}
