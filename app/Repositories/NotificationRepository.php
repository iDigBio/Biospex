<?php

namespace App\Repositories;

use App\Models\Notifications;
use App\Repositories\Contracts\Notification;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class NotificationRepository extends Repository implements Notification, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return Notifications::class;
    }
}