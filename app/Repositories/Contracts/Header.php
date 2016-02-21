<?php namespace App\Repositories\Contracts;

interface Header extends Repository
{
    public function getByProjectId($id);
}
