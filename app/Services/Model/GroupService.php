<?php

namespace App\Services\Model;

use App\Repositories\Contracts\GroupContract;

class GroupService
{

    /**
     * @var GroupContract
     */
    public $groupContract;

    /**
     * GroupService constructor.
     * @param GroupContract $groupContract
     */
    public function __construct(GroupContract $groupContract)
    {
        $this->groupContract = $groupContract;
    }

    /**
     * Get array of users as select.
     *
     * @param $groupId
     * @return array
     */
    public function getGroupUsersSelect($groupId)
    {
        $group = $this->groupContract->with('users.profile')->find($groupId);
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
        return $this->groupContract
            ->whereHas('users', function ($query) use($user) {
                $query->where('user_id', $user->id);
            })
            ->pluck('title', 'id')
            ->toArray();
    }

}