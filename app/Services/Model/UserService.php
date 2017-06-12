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
}