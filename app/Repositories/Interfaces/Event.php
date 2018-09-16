<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface Event extends RepositoryInterface
{
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
     * @param $eventId
     * @return mixed
     */
    public function getEventClassificationIds($eventId);

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