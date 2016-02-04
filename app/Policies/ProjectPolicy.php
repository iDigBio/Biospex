<?php

namespace App\Policies;

class ProjectPolicy
{
    public function before($user)
    {
        if ($user->isAdmin('admins')) {
            return;
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
        return $user->id == $project->group->user_id;
    }
}
