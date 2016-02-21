<?php namespace App\Repositories\Contracts;

interface User extends Repository
{
    public function findByEmail($email);
}
