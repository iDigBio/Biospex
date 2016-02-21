<?php namespace App\Repositories\Contracts;

interface Download extends Repository
{
    public function getExpired();
}
