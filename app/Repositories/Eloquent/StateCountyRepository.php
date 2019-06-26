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

        return $results;
    }
}