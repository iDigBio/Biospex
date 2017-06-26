<?php

namespace App\Policies;

use Auth;
use Illuminate\Support\Facades\Cache;

class ExpeditionPolicy
{
    public function before($user)
    {
        $key = md5(__METHOD__ . $user->uuid);
        $access = Cache::remember($key, 60, function() use ($user) {
            return $user->isAdmin();
        });

        return $access ? true : null;
    }

    public function store($user, $expedition, $group)
    {
        $key = md5(__METHOD__ . $user->uuid . $group->uuid);
        $access = Cache::remember($key, 60, function() use ($user, $group) {
            return $user->hasAccess($group, 'create-project');
        });

        return $access ? true : null;
    }

    public function owner($project)
    {
        return $project->group->user_id == Auth::getUser()->id;
    }

    public function read($user, $expedition)
    {
        if ($user->id === $expedition->project->group->user_id)
        {
            return true;
        }

        $key = md5(__METHOD__ . $user->uuid . $expedition->project->group->uuid);
        $access = Cache::remember($key, 60, function() use ($user, $expedition) {
            return $user->hasAccess($expedition->project->group, 'read-project');
        });

        return $access ? true : null;
    }

    public function update($user, $expedition)
    {
        $key = md5(__METHOD__ . $user->uuid . $expedition->project->group->uuid);
        $access = Cache::remember($key, 60, function() use ($user, $expedition) {
            return $user->hasAccess($expedition->project->group, 'update-project');
        });

        return $access ? true : null;
    }

    public function delete($user, $expedition)
    {
        return $user->id === $expedition->project->group->user_id;
    }
}
