<?php namespace Biospex\Repositories\Contracts;

interface Download extends Repository
{
    public function getExpired();
}
