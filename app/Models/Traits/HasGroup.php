<?php

namespace App\Models\Traits;

use App\Models\Group;
use App\Models\Permission;

trait HasGroup
{
    /**
     * Group relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

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
     * @param $id
     * @return int
     */
    public function detachGroup($id)
    {
        return $this->groups()->detach($id);
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
            return $this->groups->contains('name', $group);
        }

        return !! $this->groups->intersect(collect([$group]))->count();
    }

    /**
     * Determine if the user may perform the given permission.
     *
     * @param  Permission $permission
     * @param $group
     * @return bool
     */
    public function hasPermission($group, $permission)
    {
        if ( ! $this->hasGroup($group))
        {
            return false;
        }
        
        return $group->permissions->contains('name', $permission);
    }
}