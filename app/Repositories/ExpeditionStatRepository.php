<?php 

namespace App\Repositories;

use App\Repositories\Contracts\ExpeditionStat;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class ExpeditionStatRepository extends Repository implements ExpeditionStat, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\ExpeditionStat::class;
    }
}

