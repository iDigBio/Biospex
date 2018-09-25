<?php

namespace App\Repositories\Eloquent;

use App\Models\EventUser as Model;
use App\Repositories\Interfaces\EventUser;

class EventUserRepository extends EloquentRepository implements EventUser
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
     * Get user by user name.
     *
     * @param $userName
     * @return mixed
     * @throws \Exception
     */
    public function getUserByName($userName)
    {
        $user = $this->model->where('nfn_user', $userName)->first();

        $this->resetModel();

        return $user;
    }
}