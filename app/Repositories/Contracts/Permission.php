<?php namespace App\Repositories\Contracts;

interface Permission extends Repository
{
    public function getPermissionsGroupBy();

    public function setPermissions(array $data);
}
