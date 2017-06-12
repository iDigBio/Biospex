<?php

namespace App\Repositories\Eloquent;

use App\Models\Notice;
use App\Repositories\Contracts\NoticeContract;
use Illuminate\Contracts\Container\Container;

class NoticeRepository extends EloquentRepository implements NoticeContract
{

    /**
     * NoticeRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Notice::class)
            ->setRepositoryId('biospex.repository.notice');
    }
}
