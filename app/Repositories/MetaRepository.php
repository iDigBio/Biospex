<?php 

namespace App\Repositories;

use App\Repositories\Contracts\Meta;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class MetaRepository extends Repository implements Meta, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Meta::class;
    }
}
