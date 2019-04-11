<?php

namespace App\Models\Traits;

use App\Models\Group;
use App\Models\Permission;

trait HasGroup
{
    /**
     * Assign the given group to the user.
     * 
     * @param $group
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function assignGroup($group)
    {
        return $this->groups()->save($group);
    }

    /**
     * Detach Group.
     *
     * @param $groupId
     * @return int
     */
    public function detachGroup($groupId)
    {
        return $this->groups()->detach($groupId);
    }

    /**
     * Determine if the user has the given group.
     *
     * @param  mixed $group
     * @return boolean
     */
    public function hasGroup($group)
    {
        if (is_string($group)) {
            return $this->groups->contains('title', $group);
        }

        return !! $this->groups->intersect(collect([$group]))->count();
    }

    /**
     * Check if user is admin group.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasGroup(config('config.admin_group'));
    }

}