<?php

namespace App\Policies;

use Illuminate\Support\Facades\Cache;

class GroupPolicy
{

    /**
     * Allow admins.
     *
     * @param $user
     * @return bool|null
     */
    public function before($user)
    {
        $key = md5(__METHOD__ . $user->uuid);
        $access = Cache::remember($key, 60, function() use ($user) {
            return $user->isAdmin();
        });
        
        return $access ? true : null;
    }

    /**
     * Is group owner.
     *
     * @param $user
     * @param $group
     * @return bool
     */
    public function isOwner($user,$group)
    {
        return $user->id === $group->user_id;
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
     * Check if user can read group.
     *
     * @param $user
     * @param $group
     * @return bool|string
     */
    public function show($user, $group)
    {
        $key = md5(__METHOD__ . $user->uuid . $group->uuid);
        $access = Cache::remember($key, 60, function() use ($user, $group) {
            return $user->hasAccess($group, 'read-group');
        });

        return $access ? true : null;
    }

    /**
     * Check if user can update group
     * @param $user
     * @param $group
     * @return mixed
     */
    public function update($user, $group)
    {
        $key = md5(__METHOD__ . $user->uuid . $group->uuid);
        $access = Cache::remember($key, 60, function() use ($user, $group) {
            return $user->hasAccess($group, 'update-group');
        });

        return $access ? true : null;
    }

    /**
     * Check if user can delete group
     * @param $user
     * @param $group
     * @return bool
     */
    public function delete($user, $group)
    {
        $key = md5(__METHOD__ . $user->uuid . $group->uuid);
        $access = Cache::remember($key, 60, function() use ($user, $group) {
            return $user->id === $group->user_id;
        });

        return $access ? true : null;
    }
}
