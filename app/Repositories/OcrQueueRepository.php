<?php 

namespace App\Repositories;

use App\Repositories\Contracts\OcrQueue;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class OcrQueueRepository extends Repository implements OcrQueue, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\OcrQueue::class;
    }

    /**
     * Find by project id.
     * 
     * @param $id
     * @return mixed
     */
    public function findByProjectId($id){
        return $this->model->findByProjectId($id);
    }

    /**
     * Get subject remaining during ocr process
     * 
     * @param $id
     * @return int
     */
    public function getSubjectRemainingSum($id)
    {
        return $this->model->getSubjectRemainingSum($id);
    }

    /**
     * Find first record with relationships
     * 
     * @param array $with
     * @return mixed
     */
    public function findFirstWith(array $with)
    {
        return $this->model->findFirstWith($with);
    }

    /**
     * Override repository allWith to return using where queries
     * 
     * @param $with
     * @return mixed
     */
    public function allWith($with)
    {
        return $this->model->allWith($with);
    }

    /**
     * Update ocr error
     * 
     * @param $id
     * @return mixed
     */
    public function updateOcrError($id)
    {
        return $this->model->updateOcrError($id);
    }
}
