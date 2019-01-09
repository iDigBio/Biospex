<?php

namespace App\Repositories\Eloquent;

use App\Models\Group as Model;
use App\Repositories\Interfaces\Group;

class GroupRepository extends EloquentRepository implements Group
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
     * @param $user
     * @return mixed
     * @throws \Exception
     */
    public function getUsersGroupsSelect($user)
    {
        $results = $this->model->whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->pluck('title', 'id')
            ->toArray();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getUserGroupIds($userId)
    {
        $groupIds = $this->model
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->get()->map(function ($item) {
            return $item['id'];
        });

        $this->resetModel();

        return $groupIds;
    }
}