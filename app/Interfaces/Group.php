<?php

namespace App\Interfaces;

interface Group extends Eloquent
{

    /**
     * Get project list by group using logged in user.
     *
     * @param $user
     * @param bool $trashed
     * @return mixed
     */
    public function getUserProjectListByGroup($user, $trashed = false);

    /**
     * Get select list of groups for a user.
     *
     * @param $user
     * @return mixed
     */
    public function getUsersGroupsSelect($user);

    /**
     * @param $userId
     * @return mixed
     */
    public function getUserGroupUuids($userId);
}