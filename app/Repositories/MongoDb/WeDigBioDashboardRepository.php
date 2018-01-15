<?php

namespace App\Repositories\MongoDb;

use App\Facades\DateHelper;
use App\Models\WeDigBioDashboard as Model;
use App\Repositories\Interfaces\WeDigBioDashboard;
use Illuminate\Http\Request;

class WeDigBioDashboardRepository extends MongoDbRepository implements WeDigBioDashboard
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
     * Get API WeDigBioDashboard record count.
     *
     * @inheritdoc
     * @return int|mixed
     */
    public function getWeDigBioDashboardApi(Request $request, $count = false)
    {
        if ($count)
        {
            return $this->model->where(function ($query) use ($request) {
                $this->buildQuery($query, $request);
            })->count();
        }

        $rows = $request->has('rows') ? (int) $request->input('rows') : 200;
        $rows = $rows > 500 ? 200 : $rows;
        $start = $request->has('start') ? (int) $request->input('start') : 0;

        $results = $this->model->where(function ($query) use ($request) {
            $this->buildQuery($query, $request);
        })->limit($rows)->offset($start)->orderBy('timestamp', 'desc')->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @param $query
     * @param Request $request
     */
    private function buildQuery(&$query, $request)
    {
        $request->has('project_uuid') ? $query->where('projectUuid', $request->input('project_uuid')) : false;
        $request->has('expedition_uuid') ? $query->where('expeditionUuid', $request->input('expedition_uuid')) : false;

        if ($request->has('date_start') && $request->has('date_end'))
        {
            $timestamps = [
                DateHelper::toMongoDbTimestamp($request->input('date_start')),
                DateHelper::toMongoDbTimestamp($request->input('date_end'))
            ];
            $query->whereBetween('timestamp', $timestamps);
        }
        else
        {
            $request->has('date_start') ? $query->where('timestamp', '>=', DateHelper::toMongoDbTimestamp($request->input('date_start'))) : false;
            $request->has('date_end') ? $query->where('timestamp', '<=', DateHelper::toMongoDbTimestamp($request->input('date_end'))) : false;
        }
    }
}