<?php

namespace App\Repositories\Eloquent;

use App\Models\Expedition;
use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\SubjectContract;
use Illuminate\Contracts\Container\Container;


class ExpeditionRepository extends EloquentRepository implements ExpeditionContract
{

    /**
     * ExpeditionRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Expedition::class)
            ->setRepositoryId('biospex.repository.expedition');
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionsForNfnClassificationProcess(array $ids = [], array $attributes = ['*'])
    {
        $this->with(['nfnWorkflow', 'stat'])->has('nfnWorkflow')
            ->whereHas('actors', function ($query)
            {
                $query->where('completed', 0);
            }, '=');

        return empty($ids) ? $this->findAll($attributes) : $this->findWhereIn(['id', $ids]);
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionSubjectCounts($id)
    {
        return $this->find($id)->subjects()->count();
    }

    /**
     * @inheritdoc
     */
    public function expeditionsByUserId($userId, array $relations =[])
    {
        return $this->with($relations)
            ->findWhereHas(['project.group.users', function ($query) {
                $query->where('user_id', 1);
            }]);
    }

    /**
     * @inheritdoc
     */
    public function expeditionDownloadsByActor($expeditionId)
    {
        return $this->with(['project.group', 'actors.downloads' => function($query) use ($expeditionId){
            $query->where('expedition_id', $expeditionId);
        }])->find($expeditionId);
    }
}
