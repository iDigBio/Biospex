<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserPolicy
{
    /**
     * @param \App\Models\User $user
     * @return bool|null
     */
    public function before(User $user)
    {
        return $user->isAdmin() ? true : null;
    }

    /**
     * @param \App\Models\User $user
     * @return bool|null
     */
    public function isAdmin(User $user)
    {
        return $user->isAdmin() ? true : null;
    }

    /**
     * @param \App\Models\User $user
     * @return bool
     */
    public function edit(User $user)
    {
        return $user === null ? false : Auth::id() === $user->id;
    }

    /**
     * @param \App\Models\User $user
     * @return bool
     */
    public function update(User $user)
    {
        return $user === null ? false : Auth::id() === $user->id;
    }

    /**
     * @param \App\Models\User $user
     * @return bool
     */
    public function pass(User $user)
    {
        return $user === null ? false : Auth::id() === $user->id;
    }

    /**
     * @param \App\Models\User $user
     * @return bool|null
     */
    public function delete(User $user)
    {
        return $user === null ? false : ($user->isAdmin('superuser') ? true : null);
    }
}
