<?php

namespace App\Repositories;

use App\Models\Import as Model;
use App\Interfaces\Import;

class ImportRepository extends EloquentRepository implements Import
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
    public function getImportsWithoutError()
    {
        return $this->model->where('error', 0)->get();
    }
}