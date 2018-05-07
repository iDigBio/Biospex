<?php

namespace App\Repositories\Eloquent;

use App\Models\Import as Model;
use App\Repositories\Interfaces\Import;

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
    public function getFirstImportWithoutError()
    {
        $results = $this->model->where('error', 0)->first();

        $this->resetModel();

        return $results;
    }
}