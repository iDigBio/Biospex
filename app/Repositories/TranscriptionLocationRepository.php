<?php

namespace App\Repositories;

use App\Models\TranscriptionLocation as Model;
use App\Interfaces\TranscriptionLocation;
use Illuminate\Support\Facades\DB;

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
     * @inheritdoc
     */
    public function getStateGroupByCountByProjectId($id)
    {
        return $this->model
            ->where('project_id', $id)
            ->where('state_county', DB::raw('COUNT(*) as count'))
            ->groupBy('state_county')
            ->get();
    }

    public function getTranscriptionFusionTableData($id)
    {
        $with = ['stateCounty' => function ($query)
        {
            $query->select('state_county','geometry');
        }];
        return $this->model
            ->with($with)
            ->where('project_id', $id)
            ->where('state_county', DB::raw('COUNT(state_county) as count'))
            ->groupBy('state_county')
            ->get();
    }
}