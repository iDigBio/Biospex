<?php  

namespace App\Repositories;

use App\Repositories\Contracts\Transcription;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class TranscriptionRepository extends Repository implements Transcription, CacheableInterface
{

    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Transcription::class;
    }

    /**
     * GEt count using expedition.
     * @param $expeditionId
     * @return mixed
     */
    public function getCountByExpeditionId($expeditionId)
    {
        return $this->model->getCountByExpeditionId($expeditionId);
    }

    /**
     * Return earliest date for transcriptions.
     * 
     * @param $project_id
     * @param $expedition_id
     * @return mixed
     */
    public function getEarliestDate($project_id, $expedition_id)
    {
        return $this->model->getEarliestDate($project_id, $expedition_id);
    }
}
