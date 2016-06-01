<?php 

namespace App\Repositories;

use App\Repositories\Contracts\Invite;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class InviteRepository extends Repository implements Invite, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Invite::class;
    }
}
