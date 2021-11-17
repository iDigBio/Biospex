<?php
/*
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

use App\Services\Model\PanoptesTranscriptionService;
use Illuminate\Support\Facades\Cache;

/**
 * Class CountHelper
 *
 * @package App\Services\Helpers
 */
class CountHelper
{
    /**
     * @var \App\Services\Model\PanoptesTranscriptionService
     */
    private $panoptesTranscriptionService;

    /**
     * CountHelper constructor.
     *
     * @param \App\Services\Model\PanoptesTranscriptionService $panoptesTranscriptionService
     */
    public function __construct(PanoptesTranscriptionService $panoptesTranscriptionService)
    {
        $this->panoptesTranscriptionService = $panoptesTranscriptionService;
    }

    /**
     * Return project transcription count.
     * @TODO Change to get project total from expedition_stat
     * @param $projectId
     * @return mixed
     */
    public function projectTranscriptionCount($projectId)
    {
        return $this->panoptesTranscriptionService->getProjectTranscriptionCount($projectId);
    }

    /**
     * Return expedition transcription count.
     *
     * @param int $expeditionId
     * @return mixed
     */
    public function expeditionTranscriptionCount(int $expeditionId)
    {
        return $this->panoptesTranscriptionService->getExpeditionTranscriptionCount($expeditionId);
    }

    /**
     * Return unique transcriber count for project.
     *
     * @param $projectId
     * @return mixed
     */
    public function projectTranscriberCount($projectId)
    {
        return $this->panoptesTranscriptionService->getProjectTranscriberCount($projectId);
    }

    /**
     * Return user transcription count for stats.
     *
     * @param $projectId
     * @return mixed
     */
    public function getTranscribersTranscriptionCount($projectId)
    {
        return $this->panoptesTranscriptionService->getTranscribersTranscriptionCount($projectId);
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
}