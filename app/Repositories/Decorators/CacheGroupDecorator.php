<?php

namespace Biospex\Repositories\Decorators;

use Biospex\Repositories\Contracts\Group;

class CacheGroupDecorator extends CacheDecorator implements Group
{

    /**
     * Retrieve all groups.
     *
     * @return mixed
     */
    public function all()
    {
        $this->setKey(__METHOD__);

        return parent::all();
    }

    /**
     * Find by id.
     *
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        $this->setKey(__METHOD__, $id);

        return parent::find($id);
    }

    /**
     * Find with eager loading
     *
     * @param $id
     * @param array $with
     * @return mixed
     */
    public function findWith($id, $with)
    {
        $this->setKey(__METHOD__, $id . implode('.', $with));

        return parent::findWith($id, $with);
    }

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
