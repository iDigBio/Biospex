<?php

namespace Biospex\Repositories\Contracts;

interface Group extends Repository
{
    /**
     * Return a specific group by name
     *
     * @param  string $name
     * @return User
     */
    public function findByName($name);
}
