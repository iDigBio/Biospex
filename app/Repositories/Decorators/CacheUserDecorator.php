<?php

namespace Biospex\Repositories\Decorators;

use Biospex\Repositories\Contracts\User;

class CacheUserDecorator extends CacheDecorator implements User
{
    /**
     * All
     *
     * @param array $columns
     * @return mixed
     */
    public function all()
    {
        $this->setKey(__METHOD__);

        return parent::all();
    }

    /**
     * Find
     *
     * @param $id
     * @param array $columns
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
     * Find user by email.
     *
     * @param $email
     * @return mixed
     */
    public function findByEmail($email)
    {
        if ( ! $this->cached) {
            return $this->repository->findByEmail($email);
        }

        $this->setKey(__METHOD__, $email);

        return $this->cache->tags($this->tag)->rememberForever($this->key, function () use ($email) {
            return $this->repository->findByEmail($email);
        });
    }
}