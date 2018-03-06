<?php

namespace App\Repositories\Eloquent;

use App\Facades\GeneralHelper;
use App\Models\EventGroup as Model;
use App\Repositories\Interfaces\EventGroup;

class EventGroupRepository extends EloquentRepository implements EventGroup
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

    /**
     * @param $uuid
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     * @throws \Exception
     */
    public function getGroupByUuid($uuid)
    {
        $results = $this->model->with(['event'])->where('uuid', GeneralHelper::uuidToBin($uuid))->first();

        $this->resetModel();

        return $results;
    }
}