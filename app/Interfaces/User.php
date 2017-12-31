<?php

namespace App\Interfaces;


use Illuminate\Database\Eloquent\Collection;

interface User extends Eloquent
{

    /**
     * @return Collection
     */
    public function getAllUsersOrderByDate();

    /**
     * @param $email
     * @return mixed
     */
    public function findUsersByEmailAjax($email);
}