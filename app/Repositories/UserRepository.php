<?php

namespace App\Repositories;

use App\Models\User as Model;
use App\Interfaces\User;

class UserRepository extends EloquentRepository implements User
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
    public function getAllUsersOrderByDate()
    {
        $results = $this->model->with('profile')->orderBy('created_at', 'asc')->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function findUsersByEmailAjax($email)
    {
        $results = $this->model
            ->where('email', 'like', $email . '%')
            ->get(['email as text'])->toArray();

        $this->resetModel();

        return $results;
    }
}