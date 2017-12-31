<?php

namespace App\Repositories;

use App\Models\Header as Model;
use App\Interfaces\Header;

class HeaderRepository extends EloquentRepository implements Header
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
