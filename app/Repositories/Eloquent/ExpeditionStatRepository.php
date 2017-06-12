<?php 

namespace App\Repositories\Eloquent;

use App\Models\ExpeditionStat;
use App\Repositories\Contracts\ExpeditionStatContract;
use Illuminate\Contracts\Container\Container;

class ExpeditionStatRepository extends EloquentRepository implements ExpeditionStatContract
{

    /**
     * ExpeditionStatRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(ExpeditionStat::class)
            ->setRepositoryId('biospex.repository.expeditionStat');
    }
}

