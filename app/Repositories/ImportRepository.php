<?php namespace Biospex\Repositories;

use Biospex\Repositories\Contracts\Import;
use Biospex\Models\Import as Model;

class ImportRepository extends Repository implements Import
{
    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
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
