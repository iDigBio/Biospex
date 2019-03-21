<?php

namespace App\Services\Helpers;

use App\Repositories\Interfaces\PanoptesTranscription;
use App\Repositories\Interfaces\Subject;
use Illuminate\Support\Facades\Cache;

class CountHelper
{
    /**
     * @var \App\Repositories\Interfaces\PanoptesTranscription
     */
    private $panoptesTranscription;

    /**
     * @var \App\Repositories\Interfaces\Subject
     */
    private $subject;

    /**
     * CountHelper constructor.
     */
    public function __construct()
    {
        $this->panoptesTranscription = app(PanoptesTranscription::class);
        $this->subject = app(Subject::class);
    }

    /**
     * Return project transcription count.
     *
     * @param $projectId
     * @return mixed
     */
    public function projectTranscriptionCount($projectId)
    {
        $count = Cache::tags('panoptes'.$projectId)->remember(md5(__METHOD__.$projectId), 43200, function () use (
                $projectId
            ) {
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
        $count = Cache::tags('panoptes'.$projectId)->remember(md5(__METHOD__.$projectId), 43200, function () use ($projectId) {
            return $this->panoptesTranscription->getProjectTranscriberCount($projectId);
        });

        return $count;
    }

    /**
     * Return user transcription count for stats.
     *
     * @param $projectId
     * @return mixed
     */
    public function getUserTranscriptionCount($projectId)
    {
        $count = Cache::tags('panoptes'.$projectId)->remember(md5(__METHOD__.$projectId), 43200, function () use ($projectId) {
            return $this->panoptesTranscription->getUserTranscriptionCount($projectId);
        });

        return $count;
    }

    /**
     * Get assigned subject count for project.
     *
     * @param $projectId
     * @return mixed
     */
    public function getProjectSubjectAssignedCount($projectId)
    {
        $count = Cache::tags('subjects'.$projectId)->remember(md5(__METHOD__.$projectId), 43200, function () use ($projectId) {
            return $this->subject->getSubjectAssignedCount($projectId);
        });

        return $count;
    }

}