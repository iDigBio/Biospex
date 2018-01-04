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
        $results = $this->model
            ->where('project_id', $id)
            ->where('state_county', DB::raw('COUNT(*) as count'))
            ->groupBy('state_county')
            ->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection|mixed|static[]
     * @throws \Exception
     */
    public function getTranscriptionFusionTableData($id)
    {
        $results = $this->model
            ->with(['stateCounty' => function ($query)
            {
                $query->select('state_county','geometry');
            }])
            ->where('project_id', $id)
            ->where('state_county', DB::raw('COUNT(state_county) as count'))
            ->groupBy('state_county')
            ->get();

        $this->resetModel();

        return $results;
    }
}