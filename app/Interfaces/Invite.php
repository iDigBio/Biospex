<?php

namespace App\Interfaces;

interface Invite extends Eloquent
{

    /**
     * @param $groupId
     * @return mixed
     */
    public function getExistingInvitesByGroupId($groupId);
}
