<?php

namespace App\Services\Model;

use App\Exceptions\Handler;
use App\Repositories\Contracts\Group;

class GroupService
{

    /**
     * @var Group
     */
    public $repository;

    /**
     * GroupService constructor.
     * @param Group $repository
     */
    public function __construct(Group $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get array of users as select.
     *
     * @param $groupId
     * @return array
     */
    public function getGroupUsersSelect($groupId)
    {
        $group = $this->repository->with(['users.profile'])->find($groupId);
        $select = [];
        foreach ($group->users as $user)
        {
            $select[$user->id] = $user->profile->full_name;
        }

        return $select;
    }

    /**
     * Get values for user's group select.
     *
     * @param $user
     * @return mixed
     */
    public function getUsersGroupsSelect($user)
    {
        return $this->repository->whereHas('users', ['user_id' => $user->id])
            ->pluck('title', 'id')
            ->toArray();
    }

}