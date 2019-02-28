<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface Event extends RepositoryInterface
{
    /**
     * Get Events public page.
     *
     * @param null $sort
     * @param null $order
     * @return mixed
     */
    public function getEventPublicIndex($sort = null, $order = null);

    /**
     * Get events for admin section by user id.
     *
     * @param $userId
     * @param null $sort
     * @param null $order
     * @return mixed
     */
    public function getEventAdminIndex($userId, $sort = null, $order = null);

    /**
     * @param array $attributes
     * @return mixed
     */
    public function createEvent(array $attributes);

    /**
     * @param array $attributes
     * @param $resourceId
     * @return mixed
     */
    public function updateEvent(array $attributes, $resourceId);

    /**
     * @param $userId
     * @return mixed
     */
    public function getUserEvents($userId);

    /**
     * @param $eventId
     * @return mixed
     */
    public function getEventShow($eventId);

    /**
     * @param $projectId
     * @param $user
     * @return mixed
     */
    public function checkEventExistsForClassificationUser($projectId, $user);

    /**
     * @param $projectId
     * @return mixed
     */
    public function getEventsByProjectId($projectId);

    /**
     * @param $eventId
     * @param array $columns
     * @return mixed
     */
    public function getEventScoreboard($eventId, array $columns = ['*']);
}