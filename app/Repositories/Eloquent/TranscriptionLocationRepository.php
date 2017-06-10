<?php

namespace App\Repositories\Eloquent;

use App\Models\TranscriptionLocation;
use App\Repositories\Contracts\TranscriptionLocationContract;
use DB;
use Illuminate\Contracts\Container\Container;

class TranscriptionLocationRepository extends EloquentRepository implements TranscriptionLocationContract
{
    /**
     * PanoptesTranscriptionLocationRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(TranscriptionLocation::class)
            ->setRepositoryId('biospex.repository.transcriptionLocation');

    }

    /**
     * @inheritdoc
     */
    public function updateOrCreateTranscriptionLocation(array $attributes = [], array $values = [])
    {
        $entity = $this->updateOrCreate($attributes, $values);
        $this->flushCacheKeys();

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function getStateCountyGroupByCountByProjectId($id)
    {
        return $this
            ->groupBy('state_county')
            ->findWhere(['project_id', '=', $id], ['state_county', DB::raw('COUNT(*) as count')]);
    }

    public function getTranscriptionFusionTableData($id)
    {
        $with = ['stateCounty' => function ($query)
        {
            $query->select('state_county','geometry');
        }];
        return $this->with($with)->groupBy('state_county')
            ->findWhere(['project_id', '=', $id], ['state_county', DB::raw('COUNT(state_county) as count')]);
    }
}