<?php

namespace App\Policies;

use App\Repositories\Contracts\User;

class GroupPolicy
{
    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function create(User $user) {
        return $user->hasPermission('create-group');
    }

    public function read(User $user) {
        return $user->hasPermission('read-group');
    }

    public function update(User $user) {
        return $user->hasPermission('update-group');
    }

    public function delete(User $user) {
        return $user->hasPermission('delete-group');
    }
}
