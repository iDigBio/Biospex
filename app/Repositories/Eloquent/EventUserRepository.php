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
     * Get nfn user by name.
     *
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function getEventUserByName($name)
    {
        $user = $this->model->where('nfn_user', $name)->first(['id']);

        $this->resetModel();

        return $user;
    }
}