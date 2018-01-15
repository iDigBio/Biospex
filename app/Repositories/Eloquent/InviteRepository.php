<?php

namespace App\Repositories\Eloquent;

use App\Models\Invite as Model;
use App\Repositories\Interfaces\Invite;

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
        $results = $this->model
            ->with('group')
            ->where('group_id', $groupId)
            ->get();

        $this->resetModel();

        return $results;
    }
}
