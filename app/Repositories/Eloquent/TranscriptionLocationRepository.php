<?php

namespace App\Repositories\Eloquent;

use App\Models\TranscriptionLocation as Model;
use App\Repositories\Interfaces\TranscriptionLocation;

class TranscriptionLocationRepository extends EloquentRepository implements TranscriptionLocation
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