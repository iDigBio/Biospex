<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface EventTranscription extends RepositoryInterface
{
    /**
     * Get classification ids by event id.
     *
     * @param $eventId
     * @return mixed
     */
    public function getEventClassificationIds($eventId);
}