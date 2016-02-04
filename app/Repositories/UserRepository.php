<?php

namespace App\Repositories;

use App\Repositories\Contracts\User;
use App\Models\User as Model;

class UserRepository extends Repository implements User
{
    /**
     * Construct a new User Object
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function findByEmail($email)
    {
        return $this->model->findByEmail($email);
    }
}
