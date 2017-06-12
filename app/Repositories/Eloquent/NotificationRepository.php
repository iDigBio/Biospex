<?php

namespace App\Repositories\Eloquent;

use App\Models\Notification;
use App\Repositories\Contracts\NotificationContract;
use Illuminate\Contracts\Container\Container;

class NotificationRepository extends EloquentRepository implements NotificationContract
{

    /**
     * NotificationRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Notification::class)
            ->setRepositoryId('biospex.repository.notification');
    }
}