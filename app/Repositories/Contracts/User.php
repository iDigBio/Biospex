<?php namespace Biospex\Repositories\Contracts;

interface User extends Repository
{
    public function findByEmail($email);
}
