<?php

namespace App\Repositories;

use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Contracts\Notice;
use App\Repositories\Traits\CacheableRepository;

class NoticeRepository extends Repository implements Notice, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Notice::class;
    }
}
