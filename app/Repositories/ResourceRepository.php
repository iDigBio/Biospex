<?php 

namespace App\Repositories;

use App\Repositories\Contracts\Resource;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class ResourceRepository extends Repository implements Resource, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Resource::class;
    }
}
