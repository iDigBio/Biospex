<?php 

namespace App\Repositories;

use App\Repositories\Contracts\Property;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class PropertyRepository extends Repository implements Property, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Property::class;
    }
}
