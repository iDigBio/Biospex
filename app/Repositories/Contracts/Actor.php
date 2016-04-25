<?php namespace App\Repositories\Contracts;

interface Actor extends Repository
{
    public function findByTitle($value);
}
