<?php namespace App\Repositories;

use App\Repositories\Contracts\OcrQueue;
use App\Models\OcrQueue as Model;

class OcrQueueRepository extends Repository implements OcrQueue
{
    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}
