<?php

namespace App\Repositories\Eloquent;

use App\Models\Expedition;
use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Traits\EloquentRepositoryCommon;
use Illuminate\Contracts\Container\Container;

/**
 * Class ExpeditionRepository
 * @package App\Repositories\Eloquent
 */
class ExpeditionRepository extends EloquentRepository implements ExpeditionContract
{
    use EloquentRepositoryCommon;

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
        $this->with(['stat'])->has('nfnWorkflow')
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
}
