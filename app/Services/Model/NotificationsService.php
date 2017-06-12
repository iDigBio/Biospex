<?php

namespace App\Services\Model;

use App\Exceptions\BiospexException;
use App\Repositories\Contracts\NotificationContract;
use App\Exceptions\Handler;

class NotificationsService
{

    /**
     * @var NotificationContract
     */
    public $notificationContract;

    /**
     * @var Handler
     */
    private $handler;

    /**
     * NotificationsService constructor.
     * @param NotificationContract $notificationContract
     * @param Handler $handler
     */
    public function __construct(NotificationContract $notificationContract, Handler $handler)
    {
        $this->notificationContract = $notificationContract;
        $this->handler = $handler;
    }

    /**
     * Delete notification.
     *
     * @param $id
     * @return bool
     */
    public function delete($id)
    {
        try
        {
            return $this->notificationContract->delete($id);
        }
        catch (BiospexException $e)
        {
            $this->handler->report($e);

            return false;
        }
    }

    /**
     * Restore notification.
     *
     * @param $id
     * @return bool
     */
    public function restore($id)
    {
        try
        {
            return $this->notificationContract->restore($id);
        }
        catch (BiospexException $e)
        {
            $this->handler->report($e);

            return false;
        }
    }

    /**
     * Destroy notification.
     *
     * @param $id
     * @return bool
     */
    public function destroy($id)
    {
        try
        {
            return $this->notificationContract->forceDelete($id);
        }
        catch (BiospexException $e)
        {
            $this->handler->report($e);

            return false;
        }
    }
}