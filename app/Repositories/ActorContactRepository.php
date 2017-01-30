<?php 

namespace App\Repositories;

use App\Repositories\Contracts\ActorContact;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class ActorContactRepository extends Repository implements ActorContact, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\ActorContact::class;
    }
}
