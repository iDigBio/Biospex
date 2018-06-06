<?php

namespace App\Services\Model;

use App\Facades\Flash;
use App\Repositories\Interfaces\Group;

class GroupService
{

    /**
     * @var Group
     */
    public $groupContract;

    /**
     * GroupService constructor.
     * @param Group $groupContract
     */
    public function __construct(Group $groupContract)
    {
        $this->groupContract = $groupContract;
    }

    /**
     * @return mixed
     */
    public function getAllGroups()
    {
        return $this->groupContract->all();
    }

    /**
     * Find a group by id with relationships if required.
     *
     * @param $groupId
     * @return mixed
     */
    public function findGroup($groupId)
    {
        return $this->groupContract->find($groupId);
    }

    /**
     * Find a group by id with relationships.
     *
     * @param $groupId
     * @param array $with
     * @return mixed
     */
    public function findGroupWith($groupId, array $with = [])
    {
        return $this->groupContract->findWith($groupId, $with);
    }

    /**
     * Get user project list by group for logged in user.
     *
     * @param $user
     * @return mixed
     */
    public function getUserProjectListByGroup($user)
    {
        return $this->groupContract->getUserProjectListByGroup($user);
    }

    /**
     * Get array of users as select.
     *
     * @param $groupId
     * @return array
     */
    public function getGroupUsersSelect($groupId)
    {
        $group = $this->groupContract->findWith($groupId, ['users.profile']);
        $select = [];
        foreach ($group->users as $user)
        {
            $select[$user->id] = $user->profile->full_name;
        }

        return $select;
    }

    /**
     * Create a group.
     *
     * @param $user
     * @param $title
     * @return bool
     */
    public function createGroup($user, $title)
    {
        $group = $this->groupContract->create(['user_id' => $user->id, 'title' => $title]);

        if ($group)
        {
            $user->assignGroup($group);

            event('group.saved');

            Flash::success(trans('messages.record_created'));

            return true;
        }

        Flash::warning(trans('messages.loginreq'));

        return false;
    }

    /**
     * Update Group.
     *
     * @param array $attributes
     * @param $groupId
     */
    public function updateGroup(array $attributes = [], $groupId)
    {
        $this->groupContract->update($attributes, $groupId) ?
            Flash::success(trans('messages.record_updated')) :
            Flash::error('messages.record_updated_error');

        return;
    }

    /**
     * Delete Group.
     *
     * @param $group
     * @return bool
     */
    public function deleteGroup($group)
    {
        try
        {
            foreach ($group->projects as $project)
            {
                if ( ! $project->nfnWorkflows->isEmpty())
                {
                    Flash::error(trans('messages.expedition_process_exists'));

                    return false;
                }
            }

            $this->groupContract->delete($group);

            event('group.deleted');

            Flash::success(trans('messages.record_deleted'));

            return true;
        }
        catch (\Exception $e)
        {
            Flash::error(trans('messages.record_delete_error'));

            return false;
        }
    }
}