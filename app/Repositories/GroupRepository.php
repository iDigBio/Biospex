<?php

namespace App\Repositories;

use App\Models\Group as Model;
use App\Interfaces\Group;

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
     * @inheritdoc
     */
    public function getUserProjectListByGroup($user, $trashed = false)
    {
        $with = ! $trashed ? 'projects' : 'trashedProjects';

        $results = $this->model->with($with)
            ->whereHas('users', function ($query) use ($user) {
                $query->where('id', $user->id);
            })->get();

        $this->resetModel();

        return $results;
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
    public function getUserGroupUuids($userId)
    {
        $uuids = $this->model
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->get()->map(function ($item) {
            return $item['uuid'];
        });

        $this->resetModel();

        return $uuids;
    }
}