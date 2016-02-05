<?php

namespace Biospex\Policies;

class GroupPolicy
{
    public function before($user)
    {
        if ($user->isAdmin('admins'))
        {
            return true;
        }
    }

    /**
     * Check if user can create group
     * @param $user
     * @return bool
     */
    public function create($user)
    {
        return true;
    }

    /**
     * Check if user can store group
     * @param $user
     * @return bool
     */
    public function store($user)
    {
        return true;
    }

    /**
     * Check if user can read group
     * @param $group
     * @return bool
     */
    public function read($user, $group)
    {
        return $user->hasAccess($group, 'read-group');
    }

    /**
     * Check if user can update group
     * @param $user
     * @param $group
     * @return mixed
     */
    public function update($user, $group)
    {
        return $user->hasAccess($group, 'update-group');
    }

    /**
     * Check if user can delete group
     * @param $user
     * @param $group
     * @return bool
     */
    public function delete($user, $group)
    {
        return $user->hasAccess($group, 'delete-group') && $user->id == $group->user_id;
    }
}
