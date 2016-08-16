<?php 

namespace App\Repositories;

use App\Repositories\Contracts\NfnClassification;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class NfnClassificationRepository extends Repository implements NfnClassification, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\NfnClassification::class;
    }

    /**
     * Return classification count grouped by finished_at date.
     *
     * @param $workflow
     * @return mixed
     */
    public function getExpeditionsGroupByFinishedAt($workflow)
    {
        return $this->model->getExpeditionsGroupByFinishedAt($workflow);
    }
}
