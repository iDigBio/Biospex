<?php

namespace App\Services\Model;

use App\Repositories\Contracts\UserContract;

class UserService
{

    /**
     * @var UserContract
     */
    public $userContract;

    /**
     * UserService constructor.
     * @param UserContract $userContract
     */
    public function __construct(UserContract $userContract)
    {
        $this->userContract = $userContract;
    }

    /**
     * Return the logged in user.
     *
     * @return mixed
     */
    public function getLoggedInUser()
    {
        return $this->userContract->with('profile')->find(request()->user()->id);
    }
}