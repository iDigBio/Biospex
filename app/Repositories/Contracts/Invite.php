<?php namespace App\Repositories\Contracts;

interface Invite extends Repository
{
    public function findByCode($code);

    public function checkDuplicate($id, $email);

    public function findByGroupId($id);
}
