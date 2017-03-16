<?php

namespace App\Policies;

use Illuminate\Support\Facades\Cache;

class ProjectPolicy
{

    public function before($user)
    {
        return true;
        $key = md5(__METHOD__ . $user->uuid);
        $access = Cache::remember($key, 60, function() use($user) {
            return $user->isAdmin();
        });

        return $access ? true : null;
    }

    public function create($user, $project, $group)
    {
        $key = md5(__METHOD__ . $user->uuid . $group->uuid);
        $access = Cache::remember($key, 60, function() use ($user, $group) {
            return $user->hasAccess($group, 'create-group');
        });

        return $access ? true : null;
    }

    public function read($user, $project)
    {
        
        if ($user->id === $project->group->user_id)
        {
            return true;
        }

        $key = md5(__METHOD__ . $user->uuid . $project->group->uuid);
        $access = Cache::remember($key, 60, function() use ($user, $project) {
            return $user->hasAccess($project->group, 'read-project');
        });

        return $access ? true : null;
    }

    public function update($user, $project)
    {
        $key = md5(__METHOD__ . $user->uuid . $project->group->uuid);
        $access = Cache::remember($key, 60, function() use ($user, $project) {
            return $user->hasAccess($project->group, 'update-project');
        });

        return $access ? true : null;
    }

    public function delete($user, $project)
    {
        return $user->id === $project->group->user_id;
    }
}
