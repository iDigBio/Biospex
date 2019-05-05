<?php

namespace App\Repositories\Eloquent;

use App\Models\TranscriptionLocation as Model;
use App\Repositories\Interfaces\TranscriptionLocation;
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
    public function getStateGroupByCountByProjectId($projectId)
    {
        $results = $this->model->where('project_id', $projectId)->where('state_county', DB::raw('COUNT(*) as count'))->groupBy('state_county')->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @param $projectId
     * @return \Illuminate\Database\Eloquent\Collection|mixed|static[]
     * @throws \Exception
     */
    public function getTranscriptionFusionTableData($projectId)
    {
        $results = $this->model->selectRaw('state_county, count(*) as count')
            ->with('stateCounty')
            ->where('project_id', $projectId)
            ->groupBy('state_county')
            ->get();

        $this->resetModel();

        return $results;
    }
}