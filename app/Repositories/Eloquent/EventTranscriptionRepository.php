<?php

namespace App\Repositories\Eloquent;

use App\Models\EventTranscription as Model;
use App\Repositories\Interfaces\EventTranscription;

class EventTranscriptionRepository extends EloquentRepository implements EventTranscription
{

    /**
     * Specify Model class name
     *
     * @return \Illuminate\Database\Eloquent\Model|string
     */
    public function model()
    {
        return Model::class;
    }

}