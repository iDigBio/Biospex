<?php namespace App\Repositories\Contracts;

interface Navigation extends Repository
{
    public function getMenu($type);
}
