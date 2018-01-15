<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface Invite extends RepositoryInterface
{

    /**
     * @param $groupId
     * @return mixed
     */
    public function getExistingInvitesByGroupId($groupId);
}
