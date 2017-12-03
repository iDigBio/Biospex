<?php

namespace App\Repositories\Eloquent;

use App\Helpers\DateHelper;
use App\Models\WeDigBioDashboard;
use App\Repositories\Contracts\WeDigBioDashboardContract;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Rinvex\Repository\Repositories\EloquentRepository;

class WeDigBioDashboardRepository extends EloquentRepository implements WeDigBioDashboardContract
{

    /**
     * PanoptesTranscriptionRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(WeDigBioDashboard::class)
            ->setRepositoryId('biospex.repository.wedigbioDashboard');

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
            return $this->setCacheLifetime(0)->where(function ($query) use ($request) {
                $this->buildQuery($query, $request);
            })->count();
        }

        $rows = $request->has('rows') ? (int) $request->input('rows') : 200;
        $rows = $rows > 500 ? 200 : $rows;
        $start = $request->has('start') ? (int) $request->input('start') : 0;

        return $this->setCacheLifetime(0)->where(function ($query) use ($request) {
            $this->buildQuery($query, $request);
        })->limit($rows)->offset($start)->orderBy('timestamp', 'desc')->findAll();
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