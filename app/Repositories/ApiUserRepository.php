<?php

namespace App\Repositories;

use App\Models\ApiUser as Model;
use App\Interfaces\ApiUser;

class ApiUserRepository extends EloquentRepository implements ApiUser
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