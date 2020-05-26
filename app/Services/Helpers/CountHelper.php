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
        $count = $this->panoptesTranscription->getProjectTranscriptionCount($projectId);

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
        $count = $this->panoptesTranscription->getProjectTranscriberCount($projectId);

        return $count;
    }

    /**
     * Return user transcription count for stats.
     *
     * @param $projectId
     * @return mixed
     */
    public function getTranscribersTranscriptionCount($projectId)
    {
        $transcribers = $this->panoptesTranscription->getTranscribersTranscriptionCount($projectId);

        return $transcribers;
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
        $count = Cache::tags('subjects'.$projectId)->remember(md5(__METHOD__.$projectId), 43200, function () use ($projectId) {
            return $this->subject->getSubjectAssignedCount($projectId);
        });

        return $count;
    }

}