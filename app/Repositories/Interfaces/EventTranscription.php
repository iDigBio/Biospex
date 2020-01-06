<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;
use Illuminate\Support\Collection;

interface EventTranscription extends RepositoryInterface
{
    /**
     * Get classification ids by event id.
     *
     * @param $eventId
     * @return mixed
     */
    public function getEventClassificationIds($eventId);

    /**
     * Retrieve transcriptions for step chart.
     *
     * @param string $eventId
     * @param string $startLoad
     * @param string $endLoad
     * @return \Illuminate\Support\Collection|null
     */
    public function getEventStepChartTranscriptions(string $eventId, string $startLoad, string $endLoad): ?Collection;
}