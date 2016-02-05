<?php namespace Biospex\Repositories;

use Biospex\Repositories\Contracts\OcrCsv;
use Biospex\Models\OcrCsv as Model;

class OcrCsvRepository extends Repository implements OcrCsv
{

    /**
     * OcrCsvRepository constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function createOrFirst($attributes)
    {
        return $this->model->createOrFirst($attributes);
    }
}


