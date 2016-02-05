<?php  namespace Biospex\Repositories;

use Biospex\Repositories\Contracts\Transcription;
use Biospex\Models\Transcription as Model;

class TranscriptionRepository extends Repository implements Transcription
{
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getCountByExpeditionId($expeditionId)
    {
        return $this->model->getCountByExpeditionId($expeditionId);
    }
}
