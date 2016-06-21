<?php

namespace App\Policies;

use Illuminate\Support\Facades\Cache;

class GroupPolicy
{
    public function before($user)
    {
        $key = md5(__METHOD__ . $user->uuid);
        $isAdmin = Cache::remember($key, 60, function() use ($user) {
            return $user->isAdmin();
        });

        if ($isAdmin) {
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
    public function show($user, $group)
    {
        $key = md5(__METHOD__ . $user->uuid . $group->uuid);
        return Cache::remember($key, 60, function() use ($user, $group) {
            return $user->hasAccess($group, 'read-group');
        });
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
        return Cache::remember($key, 60, function() use ($user, $group) {
            return $user->hasAccess($group, 'update-group');
        });
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
        return Cache::remember($key, 60, function() use ($user, $group) {
            return $user->hasAccess($group, 'delete-group') && $user->id === $group->user_id;
        });
    }
}
