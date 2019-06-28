<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface StateCounty extends RepositoryInterface
{
    /**
     * @return mixed
     */
    public function truncateTable();

    /**
     * Find record by county and state.
     *
     * @param $county
     * @param $stateAbbr
     * @return mixed
     */
    public function findByCountyState($county, $stateAbbr);

    /**
     * Return state transcription count.
     *
     * @param $projectId
     * @return mixed
     */
    public function getStateTranscriptCount($projectId);

    /**
     * Return county transcription count.
     * @param $projectId
     * @param $stateId
     * @return mixed
     */
    public function getCountyTranscriptionCount($projectId, $stateId);
}