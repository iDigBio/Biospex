<?php namespace App\Repositories\Contracts;

interface Workflow extends Repository
{
    public function selectList($value, $id);
}


