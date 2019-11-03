<?php

namespace App\Repositories\MongoDb;

use App\Models\PusherTranscription as Model;
use App\Repositories\Interfaces\PusherTranscription;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PusherTranscriptionsRepository extends MongoDbRepository implements PusherTranscription
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
     * @param \Illuminate\Http\Request $request
     * @param bool $count
     * @return \Illuminate\Support\Collection|int|mixed
     * @throws \Exception
     */
    public function getWeDigBioDashboardApi(Request $request, $count = false)
    {
        if ($count)
        {
            return $this->model->where(function ($query) use ($request) {
                $this->buildQuery($query, $request);
            })->count();
        }

        $count = $request->has('rows') ? (int) $request->input('rows') : 200;
        $count = $count > 500 ? 200 : $count;                                              //count
        $current = $request->has('start') ? (int) $request->input('start') : 0; // current

        $results = $this->model->where(function ($query) use ($request) {
            $this->buildQuery($query, $request);
        })->limit($count)->offset($current)->orderBy('timestamp', 'desc')->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @param $query
     * @param $request
     */
    private function buildQuery(&$query, $request)
    {
        $request->has('project_uuid') ? $query->where('projectUuid', $request->input('project_uuid')) : false;
        $request->has('expedition_uuid') ? $query->where('expeditionUuid', $request->input('expedition_uuid')) : false;

        $date_start = is_numeric($request->input('date_start')) ? (int) $request->input('date_start') : $request->input('date_start');
        $date_end = is_numeric($request->input('date_end')) ? (int) $request->input('date_end') : $request->input('date_end');

        if ($date_start !== null && $date_end !== null)
        {
            $timestamps = [
                Carbon::parse($date_start),
                Carbon::parse($date_end)
            ];
            $query->whereBetween('timestamp', $timestamps);

            return;
        }

        $date_start !== null ? $query->where('timestamp', '>=', $date_start) : null;
        $date_end !== null ? $query->where('timestamp', '<=', $date_end) : null;

        return;
    }
}