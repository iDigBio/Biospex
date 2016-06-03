<?php

namespace App\Policies;

use Illuminate\Support\Facades\Cache;

class ExpeditionPolicy
{
    public function before($user)
    {
        $key = md5(__METHOD__ . $user->uuid);
        return Cache::remember($key, 60, function() use ($user) {
            return $user->isAdmin();
        });
    }

    public function store($user, $expedition, $group)
    {
        $key = md5(__METHOD__ . $user->uuid . $group->uuid);
        return Cache::remember($key, 60, function() use ($user, $group) {
            return $user->hasAccess($group, 'create-project');
        });
    }

    public function read($user, $expedition)
    {
        if ($user->id === $expedition->project->group->user_id)
        {
            return true;
        }

        $key = md5(__METHOD__ . $user->uuid . $expedition->project->group->uuid);
        return Cache::remember($key, 60, function() use ($user, $expedition) {
            return $user->hasAccess($expedition->project->group, 'read-project');
        });
    }

    public function update($user, $expedition)
    {
        $key = md5(__METHOD__ . $user->uuid . $expedition->project->group->uuid);
        return Cache::remember($key, 60, function() use ($user, $expedition) {
            return $user->hasAccess($expedition->project->group, 'update-project');
        });
    }

    public function delete($user, $expedition)
    {
        return $user->id === $expedition->project->group->user_id;
    }
}
