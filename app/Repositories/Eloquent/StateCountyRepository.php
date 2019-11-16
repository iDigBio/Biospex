<?php

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
        $results = $this->model->truncate();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function findByCountyState($county, $stateAbbr)
    {
        $result = $this->model->where('county_name','like', '%'.$county.'%')->where('state_abbr', $stateAbbr)->first();

        $this->resetModel();

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getStateTranscriptCount($projectId)
    {
        $results = $this->model->withCount(['transcriptionLocations' => function($q) use($projectId) {
            $q->where('project_id', $projectId);
        }])->get();

        $this->resetModel();


        $states = $results->groupBy('state_num')->reject(function ($row, $key) {
            return empty($key);
        })->map(function ($row) {
            $stateAbbr = $row->first()->state_abbr_cap;
            $stateNum = $row->first()->state_num;
            $id = 'US-'.$stateAbbr;
            $count = (int) $row->sum('transcription_locations_count');

            return ['id' => $id, 'value' => $count ?: 0, 'name' => $stateAbbr, 'statenum' => $stateNum];
        })->values();

        return $states;
    }

    /**
     * @inheritDoc
     */
    public function getCountyTranscriptionCount($projectId, $stateId)
    {
        $results = $this->model->withCount(['transcriptionLocations' => function($q) use($projectId) {
            $q->where('project_id', $projectId);
        }])->where('state_num', $stateId)->get();

        $this->resetModel();

        return $results;
    }
}