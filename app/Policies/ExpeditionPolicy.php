<?php

namespace Biospex\Policies;

class ExpeditionPolicy
{
    public function before($user)
    {
        if ($user->isAdmin('admins')) {
            return;
        }
    }

    public function store($user, $expedition, $group)
    {
        return $user->hasAccess($group, 'create-project');
    }

    public function read($user, $expedition)
    {
        if ($user->id == $expedition->project->group->user_id)
        {
            return true;
        }

        return $user->hasAccess($expedition->project->group, 'read-project');
    }

    public function update($user, $expedition)
    {
        return $user->hasAccess($expedition->project->group, 'update-project');
    }

    public function delete($user, $expedition)
    {
        return $user->id == $expedition->project->group->user_id;
    }
}
