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

use App\Repositories\PanoptesTranscriptionRepository;
use Illuminate\Support\Facades\Cache;

/**
 * Class CountHelper
 *
 * @package App\Services\Helpers
 */
class CountHelper
{
    /**
     * @var \App\Repositories\PanoptesTranscriptionRepository
     */
    private $panoptesTranscriptionRepo;

    /**
     * CountHelper constructor.
     *
     * @param \App\Repositories\PanoptesTranscriptionRepository $panoptesTranscriptionRepo
     */
    public function __construct(PanoptesTranscriptionRepository $panoptesTranscriptionRepo)
    {
        $this->panoptesTranscriptionRepo = $panoptesTranscriptionRepo;
    }

    /**
     * Return project transcription count.
     *
     * @TODO Change to get project total from expedition_stat
     * @param int $projectId
     * @return mixed
     */
    public function projectTranscriptionCount(int $projectId)
    {
        return $this->panoptesTranscriptionRepo->getProjectTranscriptionCount($projectId);
    }

    /**
     * Return expedition transcription count.
     *
     * @param int $expeditionId
     * @return mixed
     */
    public function expeditionTranscriptionCount(int $expeditionId)
    {
        return $this->panoptesTranscriptionRepo->getExpeditionTranscriptionCount($expeditionId);
    }

    /**
     * Return unique transcriber count for project.
     *
     * @param int $projectId
     * @return mixed
     */
    public function projectTranscriberCount(int $projectId)
    {
        return $this->panoptesTranscriptionRepo->getProjectTranscriberCount($projectId);
    }

    /**
     * Return user transcription count for stats.
     *
     * @param int $projectId
     * @return mixed
     */
    public function getTranscribersTranscriptionCount(int $projectId)
    {
        return $this->panoptesTranscriptionRepo->getTranscribersTranscriptionCount($projectId);
    }

    /**
     * Return transcriptions per transcribers
     *
     * @param int $projectId
     * @param $transcribers
     * @return mixed
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
     * @param int $projectId
     * @param string $transcriber
     * @return mixed
     */
    public function getTranscriptionCountForTranscriber(int $projectId, string $transcriber)
    {
        return Cache::remember(md5(__METHOD__.$projectId.$transcriber), 86400, function () use ($projectId, $transcriber) {
            return $this->panoptesTranscriptionRepo->getTranscriptionCountForTranscriber($projectId, $transcriber);
        });
    }
}