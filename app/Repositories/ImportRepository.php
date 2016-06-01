<?php 

namespace App\Repositories;

use App\Repositories\Contracts\Import;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class ImportRepository extends Repository implements Import, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Import::class;
    }
}
