<?php  namespace App\Repositories;

use App\Repositories\Contracts\Transcription;
use App\Models\Transcription as Model;

class TranscriptionRepository extends Repository implements Transcription
{

    /**
     * TranscriptionRepository constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
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
}
