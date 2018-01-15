<?php

namespace App\Repositories\Eloquent;

use App\Models\ApiUser as Model;
use App\Repositories\Interfaces\ApiUser;

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