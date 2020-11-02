<?php
/**
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Helpers;

use App\Repositories\Interfaces\PanoptesTranscription;
use App\Services\Model\SubjectService;
use Illuminate\Support\Facades\Cache;

class CountHelper
{
    /**
     * @var \App\Repositories\Interfaces\PanoptesTranscription
     */
    private $panoptesTranscription;

    /**
     * @var \App\Services\Model\SubjectService|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $subjectService;

    /**
     * CountHelper constructor.
     *
     * @param \App\Services\Model\SubjectService $subjectService
     * @param \App\Repositories\Interfaces\PanoptesTranscription $panoptesTranscription
     */
    public function __construct(SubjectService $subjectService, PanoptesTranscription $panoptesTranscription)
    {
        $this->subjectService = $subjectService;
        $this->panoptesTranscription = $panoptesTranscription;
    }

    /**
     * Return project transcription count.
     * @TODO Change to get project total from expedition_stat
     * @param $projectId
     * @return mixed
     */
    public function projectTranscriptionCount($projectId)
    {
        return $this->panoptesTranscription->getProjectTranscriptionCount($projectId);
    }

    /**
     * Return expedition transcription count.
     *
     * @param int $expeditionId
     * @return mixed
     */
    public function expeditionTranscriptionCount(int $expeditionId)
    {
        return $this->panoptesTranscription->getExpeditionTranscriptionCount($expeditionId);
    }

    /**
     * Return unique transcriber count for project.
     *
     * @param $projectId
     * @return mixed
     */
    public function projectTranscriberCount($projectId)
    {
        return $this->panoptesTranscription->getProjectTranscriberCount($projectId);
    }

    /**
     * Return user transcription count for stats.
     *
     * @param $projectId
     * @return mixed
     */
    public function getTranscribersTranscriptionCount($projectId)
    {
        return $this->panoptesTranscription->getTranscribersTranscriptionCount($projectId);
    }

    /**
     * Return transcriptions per transcribers
     *
     * @param $projectId
     * @param $transcribers
     * @return mixed
     * @static
     */
    public function getTranscriptionsPerTranscribers($projectId, $transcribers)
    {
        return Cache::rememberForever(md5(__METHOD__.$projectId), function () use ($transcribers) {
            return $transcribers->isEmpty() ? null :
                $transcribers->pluck('transcriptionCount')->pipe(function ($transcribers) {
                    return collect(array_count_values($transcribers->sort()->toArray()));
                })->flatMap(function ($users, $count) {
                    return [['transcriptions' => $count, 'transcribers' => $users]];
                })->toJson();
        });
    }

    /**
     * Get assigned subject count for project.
     *
     * @param $projectId
     * @return mixed
     */
    public function getProjectSubjectAssignedCount($projectId)
    {
        return Cache::tags('subjects'.$projectId)->remember(md5(__METHOD__.$projectId), 43200, function () use ($projectId) {
            return $this->subjectService->getAssignedCountByProject($projectId);
        });
    }

}