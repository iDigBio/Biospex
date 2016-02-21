<?php namespace App\Repositories;

use App\Repositories\Contracts\OcrCsv;
use App\Models\OcrCsv as Model;

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


