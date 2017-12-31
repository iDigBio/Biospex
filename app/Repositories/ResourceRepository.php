<?php

namespace app\Repositories;

use App\Interfaces\Resource;
use App\Models\Resource as Model;

class ResourceRepository extends EloquentRepository implements Resource
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
     * @inheritdoc
     */
    public function getResourcesOrdered()
    {
        return $this->model->orderBy('order', 'asc')->get();
    }

    /**
     * @inheritdoc
     */
    public function getTrashedResourcesOrdered()
    {
        return $this->model->onlyTrashed()->orderBy('order', 'asc')->get();
    }
}