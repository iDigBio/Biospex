<?php

namespace App\Models\Traits;

use App\Models\Group;
use App\Models\Permission;

trait HasGroup
{
    /**
     * A user may have multiple groups.
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
     * @param  string $group
     * @return mixed
     */
    public function assignGroup($group)
    {
        return $this->groups()->save(
            Group::whereName($group)->firstOrFail()
        );
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
        return !! $group->intersect($this->groups)->count();
    }
    /**
     * Determine if the user may perform the given permission.
     *
     * @param  Permission $permission
     * @return boolean
     */
    public function hasPermission(Permission $permission)
    {
        return $this->hasGroup($permission->groups);
    }
}