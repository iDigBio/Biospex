<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface Group extends RepositoryInterface
{



    /**
     * Get select list of groups for a user.
     *
     * @param $user
     * @return mixed
     */
    public function getUsersGroupsSelect($user);

    /**
     * Return user group ids.
     *
     * @param $userId
     * @return mixed
     */
    public function getUserGroupIds($userId);

    /**
     * Return groups by user id.
     *
     * @param $userId
     * @return mixed
     */
    public function getGroupsByUserId($userId);

    /**
     * Get data for group show page in admin section.
     *
     * @param $groupId
     * @return mixed
     * @throws \Exception
     */
    public function getGroupShow($groupId);

    /**
     * Get count of groups user belongs to.
     *
     * @param $userId
     * @return mixed
     */
    public function getUserGroupCount($userId);
}