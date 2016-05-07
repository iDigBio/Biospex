<?php

namespace App\Repositories\Decorators;

use App\Repositories\Contracts\Group;

class CacheGroupDecorator extends CacheDecorator implements Group
{
    /**
     * Return a specific group by a given name
     *
     * @param  string $name
     * @return Group
     */
    public function findByName($name)
    {
        if ( ! $this->cached) {
            return $this->repository->findByName($name);
        }

        $this->setKey(__METHOD__, $name);

        return $this->cache->tags($this->tag)->rememberForever($this->key, function () use ($name) {
            return $this->repository->findByName($name);
        });

    }
}
