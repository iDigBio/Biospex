<?php namespace Biospex\Repositories\Contracts;

interface Expedition extends Repository
{
    public function findByUuid($uuid);
}
