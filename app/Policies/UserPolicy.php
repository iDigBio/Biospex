<?php

namespace Biospex\Policies;

use Illuminate\Support\Facades\Auth;

class UserPolicy
{
    public function before($user)
    {
        if ($user->isAdmin('admins'))
        {
            return true;
        }
    }

    public function edit($user)
    {
        return is_null($user) ? false : Auth::getUser()->id == $user->id;
    }

    public function update($user)
    {
        return is_null($user) ? false : Auth::getUser()->id == $user->id;
    }

    public function pass($user)
    {
        return is_null($user) ? false : Auth::getUser()->id == $user->id;
    }

    public function delete($user)
    {
        return is_null($user) ? false : $user->isAdmin('superuser');
    }
}
