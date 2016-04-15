<?php

namespace App\Policies;

use Illuminate\Support\Facades\Cache;

class ProjectPolicy
{

    public function before($user)
    {
        $key = md5(__METHOD__ . $user->uuid);
        $isAdmin = Cache::remember($key, 60, function() use($user) {
            return $user->isAdmin();
        });

        if ($isAdmin) {
            return true;
        }
    }

    public function create($user, $project, $group)
    {

        return $user->hasAccess($group, 'create-project');
    }

    public function read($user, $project)
    {
        if ($user->id == $project->group->user_id)
        {
            return true;
        }

        return $user->hasAccess($project->group, 'read-project');
    }

    public function update($user, $project)
    {
        return $user->hasAccess($project->group, 'update-project');
    }

    public function delete($user, $project)
    {
        return $user->id === $project->group->user_id;
    }
}
