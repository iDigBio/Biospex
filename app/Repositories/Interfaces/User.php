<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface User extends RepositoryInterface
{

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllUsersOrderByDate();

    /**
     * @param $email
     * @return mixed
     */
    public function findUsersByEmailAjax($email);
}