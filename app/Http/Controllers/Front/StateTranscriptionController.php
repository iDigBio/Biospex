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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Transcriptions\StateCountyService;
use Request;

class StateTranscriptionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected StateCountyService $stateCountyService) {}

    /**
     * Display the specified resource.
     */
    public function __invoke(Project $project, ?string $stateId = null)
    {
        if (! Request::ajax()) {
            return response()->json(['html' => 'Error retrieving the counties.']);
        }

        $counties = $this->stateCountyService->getCountyTranscriptionCount($project->id, $stateId)->map(function ($item) {
            return [
                'id' => str_pad($item->geo_id_2, 5, '0', STR_PAD_LEFT),
                'value' => $item->transcription_locations_count,
                'name' => $item->state_county,
            ];
        });

        return [
            'max' => abs(round(($counties->max('value') + 500), -3)),
            'counties' => $counties->toJson(),
        ];
    }
}
