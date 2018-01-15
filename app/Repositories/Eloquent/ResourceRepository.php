<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Interfaces\Resource;
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
        $results = $this->model->orderBy('order', 'asc')->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getTrashedResourcesOrdered()
    {
        $results = $this->model->onlyTrashed()->orderBy('order', 'asc')->get();

        $this->resetModel();

        return $results;
    }
}