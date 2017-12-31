<?php

namespace App\Policies;

use App\Interfaces\Group;
use Illuminate\Support\Facades\Cache;

class ProjectPolicy
{

    /**
     * @var Group
     */
    private $groupContract;

    /**
     * ProjectPolicy constructor.
     * @param Group $groupContract
     */
    public function __construct(Group $groupContract)
    {
        $this->groupContract = $groupContract;
    }

    /**
     * Check before permissions for admin.
     *
     * @param $user
     * @return bool|null
     */
    public function before($user)
    {
        $key = md5(__METHOD__ . $user->uuid);
        $access = Cache::remember($key, 60, function() use($user) {
            return $user->isAdmin();
        });

        return $access ? true : null;
    }

    /**
     * Check if user can create a project for this group.
     *
     * @param $user
     * @return bool|null
     */
    public function create($user)
    {
        $group = $this->groupContract->findWith(request()->get('group_id'), ['permissions']);

        $key = md5(__METHOD__ . $user->uuid . $group->uuid);
        $access = Cache::remember($key, 60, function() use ($user, $group) {
            return $user->hasGroup($group, 'create-group');
        });

        return $access ? true : null;
    }

    /**
     * Check if user can read project for this group.
     *
     * @param $user
     * @param $project
     * @return bool|null
     */
    public function read($user, $project)
    {
        if ($user->id === $project->group->user_id)
        {
            return true;
        }

        $key = md5(__METHOD__ . $user->uuid . $project->group->uuid);
        $access = Cache::remember($key, 60, function() use ($user, $project) {
            return $user->hasGroup($project->group);
        });

        return $access ? true : null;
    }

    /**
     * Check if user can update project for this group.
     *
     * @param $user
     * @return bool|null
     */
    public function update($user)
    {
        $group = $this->groupContract->findWith(request()->get('group_id'), ['permissions']);

        $key = md5(__METHOD__ . $user->uuid . $group->uuid);
        $access = Cache::remember($key, 60, function() use ($user, $group) {
            return $user->hasGroup($group);
        });

        return $access ? true : null;
    }

    /**
     * Check if user can delete project from this group.
     *
     * @param $user
     * @param $project
     * @return bool
     */
    public function delete($user, $project)
    {
        $group = $this->groupContract->find($project->group_id);

        return $user->id === $group->user_id;
    }
}
