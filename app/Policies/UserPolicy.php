<?php

namespace App\Policies;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UserPolicy
{
    public function before($user)
    {
        return $user->isAdmin() ? true : null;
    }

    public function admin()
    {
        return false;
    }

    public function edit($user)
    {
        return $user === null ? false : Auth::id() === $user->id;
    }

    public function update($user)
    {
        return $user === null ? false : Auth::id() === $user->id;
    }

    public function pass($user)
    {
        return $user === null ? false : Auth::id() === $user->id;
    }

    public function delete($user)
    {
        return $user === null ? false : ($user->isAdmin('superuser') ? true : null);
    }
}
