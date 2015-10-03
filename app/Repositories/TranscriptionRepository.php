<?php  namespace App\Repositories;

use App\Repositories\Contracts\Transcription;
use Transcription as Model;

class TranscriptionRepository extends Repository implements Transcription
{
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}
