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
     * @param $userId
     * @return mixed
     */
    public function getUserGroupIds($userId);
}