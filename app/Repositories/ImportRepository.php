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

    /**
     * Find by error value.
     *
     * @param int $error
     * @return Import
     */
    public function findByError($error = 0)
    {
        return $this->model->findByError($error);
    }
}
