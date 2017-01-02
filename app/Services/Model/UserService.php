<?php

namespace App\Services\Model;


use App\Repositories\Contracts\User;

class UserService
{

    /**
     * @var User
     */
    public $repository;

    /**
     * UserService constructor.
     * @param User $repository
     */
    public function __construct(User $repository)
    {

        $this->repository = $repository;
    }
}