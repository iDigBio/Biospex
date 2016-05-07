<?php

namespace App\Repositories\Decorators;

use App\Repositories\Contracts\User;

class CacheUserDecorator extends CacheDecorator implements User
{

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