<?php

namespace App\Repositories;

use App\Models\Invite as Model;
use App\Interfaces\Invite;

class InviteRepository extends EloquentRepository implements Invite
{

    /**
     * Specify Model class name
     *
     * @return \Illuminate\Database\Eloquent\Model|string
     */
    public function model()
    {
        return Model::class;
    }

    /**
     * @inheritdoc
     */
    public function getExistingInvitesByGroupId($groupId)
    {
        return $this->model
            ->with('group')
            ->where('group_id', $groupId)
            ->get();
    }
}
