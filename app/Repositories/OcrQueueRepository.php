<?php namespace Biospex\Repositories;

use Biospex\Repositories\Contracts\OcrQueue;
use Biospex\Models\OcrQueue as Model;

class OcrQueueRepository extends Repository implements OcrQueue
{
    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function findByProjectId($id){
        return $this->model->findByProjectId($id);
    }

    public function getSubjectCountSum($id)
    {
        return $this->model->getSubjectCountsum($id);
    }
}
