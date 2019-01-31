<?php

namespace App\Services\Helpers;

use App\Repositories\Interfaces\PanoptesTranscription;
use Illuminate\Support\Facades\Cache;

class CountHelper
{
    /**
     * @var \App\Repositories\Interfaces\PanoptesTranscription
     */
    private $panoptesTranscription;

    /**
     * CountHelper constructor.
     */
    public function __construct()
    {
        $this->panoptesTranscription = app(PanoptesTranscription::class);
    }

    /**
     * Return project transcription count.
     *
     * @param $projectId
     * @return mixed
     */
    public function projectTranscriptionCount($projectId)
    {
        $count = Cache::remember(md5(__METHOD__ . $projectId), 60, function () use ($projectId) {
            return $this->panoptesTranscription->getProjectTranscriptionCount($projectId);
        });

        return $count;
    }

    /**
     * Return unique transcriber count for project.
     *
     * @param $projectId
     * @return mixed
     */
    public function projectTranscriberCount($projectId)
    {
        $count = Cache::remember(md5(__METHOD__ . $projectId), 60, function () use ($projectId) {
            return $this->panoptesTranscription->getProjectTranscriberCount($projectId);
        });

        return $count;
    }

    /**
     * Return project transcription count.
     *
     * @param $expeditionId
     * @return mixed
     */
    public function expeditionTranscriptionCount($expeditionId)
    {
        $count = Cache::remember(md5(__METHOD__ . $expeditionId), 60, function () use ($expeditionId) {
            return $this->panoptesTranscription->getExpeditionTranscriptionCount($expeditionId);
        });

        return $count;
    }

    /**
     * @param $projectId
     * @return mixed
     */
    public function projectSubjectCount($projectId)
    {
        $result = Cache::remember('project_subject_count_' . $projectId, 60, function () {
            return;
        });

        return $result;
    }

    /**
     * @param $expeditionId
     * @return mixed
     */
    public function expeditionSubjectCount($expeditionId)
    {
        $result = Cache::remember('expedition_subject_count_' . $expeditionId, 60, function () {
            return;
        });

        return $result;
    }
}