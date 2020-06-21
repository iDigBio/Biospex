<?php
/**
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

namespace App\Repositories\Eloquent;

use App\Models\StateCounty as Model;
use App\Repositories\Interfaces\StateCounty;

class StateCountyRepository extends EloquentRepository implements StateCounty
{
    /**
     * Specify Model class name
     *
     * @return \Illuminate\Database\Eloquent\Model|string
     */
    public function model()
    {
        return Model::class;
    }

    /**
     * @inheritdoc
     */
    public function truncateTable()
    {
        return $this->model->truncate();
    }

    /**
     * @inheritDoc
     */
    public function findByCountyState($county, $stateAbbr)
    {
        return $this->model->where('county_name','like', '%'.$county.'%')->where('state_abbr', $stateAbbr)->first();
    }

    /**
     * @inheritDoc
     */
    public function getStateTranscriptCount($projectId)
    {
        $results = $this->model->withCount(['transcriptionLocations' => function($q) use($projectId) {
            $q->where('project_id', $projectId);
        }])->get();

        return $results->groupBy('state_num')->reject(function ($row, $key) {
            return empty($key);
        })->map(function ($row) {
            $stateAbbr = $row->first()->state_abbr_cap;
            $stateNum = $row->first()->state_num;
            $id = 'US-'.$stateAbbr;
            $count = (int) $row->sum('transcription_locations_count');

            return ['id' => $id, 'value' => $count ?: 0, 'name' => $stateAbbr, 'statenum' => $stateNum];
        })->values();
    }

    /**
     * @inheritDoc
     */
    public function getCountyTranscriptionCount($projectId, $stateId)
    {
        return $this->model->withCount(['transcriptionLocations' => function($q) use($projectId) {
            $q->where('project_id', $projectId);
        }])->where('state_num', $stateId)->get();
    }
}