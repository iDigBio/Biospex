<?php

namespace App\Repositories;

use App\Repositories\Contracts\User;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class UserRepository extends Repository implements User, CacheableInterface
{
    use CacheableRepository;
    
    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\User::class;
    }

    public function findByEmail($email)
    {
        return $this->model->findByEmail($email);
    }
}
