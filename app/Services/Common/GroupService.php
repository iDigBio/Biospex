<?php

namespace App\Services\Common;

use App\Repositories\Contracts\Group;

class GroupService
{

    /**
     * @var Group
     */
    private $group;

    /**
     * GroupService constructor.
     * @param Group $group
     */
    public function __construct(Group $group)
    {

        $this->group = $group;
    }

    /**
     * Get array of users as select.
     * 
     * @param $groupId
     * @return array
     */
    public function getGroupUsersSelect($groupId)
    {
        $group = $this->group->with(['users.profile'])->find($groupId);
        $select = [];
        foreach ($group->users as $user)
        {
            $select[$user->id] = $user->profile->full_name; 
        }
        
        return $select;
    }
}