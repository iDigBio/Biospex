<?php

namespace App\Repositories\Eloquent;

use App\Models\TranscriptionLocation as Model;
use App\Repositories\Interfaces\TranscriptionLocation;

class TranscriptionLocationRepository extends EloquentRepository implements TranscriptionLocation
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
     * @inheritDoc
     */
    public function getCountyData($projectId, $stateId)
    {
        $results = $this->model->with(['stateCounty' => function($q) use ($stateId) {
            $q->select('id', 'state_county','geo_id_2')->where('state_num', $stateId);
        }])->whereHas('stateCounty', function($query) use ($stateId) {
           $query->where('state_num', $stateId);
        })
            ->selectRaw('count(*) as count, state_county_id')
            ->groupBy('state_county_id')
            ->where('project_id', $projectId)
            ->get();

        $this->resetModel();

        return $results;
    }
}