<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface Invite extends RepositoryInterface
{

    /**
     * Return existing invites for group.
     *
     * @param $groupId
     * @return mixed
     */
    public function getExistingInvitesByGroupId($groupId);
}
