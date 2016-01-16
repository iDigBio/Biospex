<?php

namespace App\Policies;

use Illuminate\Support\Facades\Auth;

class UserPolicy
{
    public function before($user)
    {
       return $user->isAdmin('admins');
    }

    public function edit($user)
    {
        return Auth::getUser()->id == $user->id;
    }

    public function update($user)
    {
        return Auth::getUser()->id == $user->id;
    }

    public function pass($user)
    {
        return Auth::getUser()->id == $user->id;
    }

    public function delete($user)
    {
        return $user->isAdmin('superuser');
    }
}
