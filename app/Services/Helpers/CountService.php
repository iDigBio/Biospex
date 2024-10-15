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

use App\Services\Transcriptions\PanoptesTranscriptionService;
use Illuminate\Support\Facades\Cache;

/**
 * Class CountService
 */
class CountService
{
    /**
     * CountService constructor.
     */
    public function __construct(public PanoptesTranscriptionService $panoptesTranscriptionService) {}

    /**
     * Return project transcription count.
     *
     * @TODO Change to get project total from expedition_stat
     *
     * @return mixed
     */
    public function projectTranscriptionCount(int $projectId)
    {
        return $this->panoptesTranscriptionService->getProjectTranscriptionCount($projectId);
    }

    /**
     * Return expedition transcription count.
     *
     * @return mixed
     */
    public function expeditionTranscriptionCount(int $expeditionId)
    {
        return $this->panoptesTranscriptionService->getExpeditionTranscriptionCount($expeditionId);
    }

    /**
     * Return unique transcriber count for project.
     *
     * @return mixed
     */
    public function projectTranscriberCount(int $projectId)
    {
        return $this->panoptesTranscriptionService->getProjectTranscriberCount($projectId);
    }

    /**
     * Return user transcription count for stats.
     *
     * @return mixed
     */
    public function getTranscribersTranscriptionCount(int $projectId)
    {
        return $this->panoptesTranscriptionService->getTranscribersTranscriptionCount($projectId);
    }

    /**
     * Return transcriptions per transcribers
     *
     * @return mixed
     *
     * @static
     */
    public function getTranscriptionsPerTranscribers(int $projectId, $transcribers)
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
     * Return transcription count per transcriber
     *
     * @return mixed
     */
    public function getTranscriptionCountForTranscriber(int $projectId, string $transcriber)
    {
        return Cache::remember(md5(__METHOD__.$projectId.$transcriber), 86400, function () use ($projectId, $transcriber) {
            return $this->panoptesTranscriptionService->getTranscriptionCountForTranscriber($projectId, $transcriber);
        });
    }
}
