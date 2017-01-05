<?php

namespace App\Services\Model;


use App\Exceptions\BiospexException;
use App\Repositories\Contracts\Notification;
use App\Exceptions\Handler;

class NotificationsService
{

    /**
     * @var Notification
     */
    public $repo;

    /**
     * @var Handler
     */
    private $handler;

    /**
     * NotificationsService constructor.
     * @param Notification $repo
     * @param Handler $handler
     */
    public function __construct(Notification $repo, Handler $handler)
    {
        $this->repo = $repo;
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
            return $this->repo->delete($id);
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
            return $this->repo->restore($id);
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
            return $this->repo->forceDelete($id);
        }
        catch (BiospexException $e)
        {
            $this->handler->report($e);

            return false;
        }
    }
}