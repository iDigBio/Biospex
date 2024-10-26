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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Transcriptions;

use App\Models\PanoptesTranscription;
use Illuminate\Support\Facades\Cache;
use MongoDB\BSON\UTCDateTime;

class PanoptesTranscriptionService
{
    public function __construct(protected PanoptesTranscription $model) {}

    /**
     * Create.
     */
    public function create(array $data): mixed
    {
        return $this->model->create($data);
    }

    /**
     * Get first transcription by column value.
     */
    public function getFirst(string $column, $value): mixed
    {
        return $this->model->where($column, $value)->first();
    }

    /**
     * Get contributor count for all transcriptions.
     */
    public function getContributorCount(): mixed
    {
        // TODO: Eventually resolve Laravel issue with count.
        return Cache::remember(md5(__METHOD__), 14440, function () {
            return $this->model->select('user_name')
                ->where('user_name', 'not regexp', '/^not-logged-in.*/i')
                ->groupBy('user_name')->get()->count();
        });
    }

    /**
     * Get total transcriptions for site.
     */
    public function getTotalTranscriptions(): mixed
    {
        return Cache::remember(md5(__METHOD__), 14440, function () {
            return $this->model->count();
        });
    }

    /**
     * Get expedition transcription count.
     */
    public function getExpeditionTranscriptionCount(int $expeditionId): int
    {
        $result = Cache::remember(md5(__METHOD__.$expeditionId), 14440, function () use ($expeditionId) {
            return $this->model->raw(function ($collection) use ($expeditionId) {
                return $collection->aggregate([
                    ['$match' => ['subject_expeditionId' => $expeditionId]],
                    ['$count' => 'count'],
                ]);
            })->first();
        });

        return $result === null ? 0 : $result->count;
    }

    /**
     * Get transcriber count for expedition.
     */
    public function getExpeditionTranscriberCount(int $expeditionId): int
    {
        $result = Cache::remember(md5(__METHOD__.$expeditionId), 14440, function () use ($expeditionId) {
            return $this->model->raw(function ($collection) use ($expeditionId) {
                return $collection->aggregate([
                    [
                        '$match' => ['subject_expeditionId' => $expeditionId],
                    ],
                    [
                        '$group' => ['_id' => '$user_name'],
                    ],
                    ['$count' => 'count'],
                ]);
            })->first();
        });

        return $result === null ? 0 : $result->count;
    }

    /**
     * Get transcribers transcription count.
     */
    public function getTranscribersTranscriptionCount(int $projectId): mixed
    {
        return Cache::rememberForever(md5(__METHOD__.$projectId), function () use ($projectId) {
            return $this->model->raw(function ($collection) use ($projectId) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'subject_projectId' => (int) $projectId,
                        ],
                    ],
                    [
                        '$sort' => [
                            'classification_finished_at' => -1,
                        ],
                    ],
                    [
                        '$group' => [
                            '_id' => '$user_name',
                            'transcriptionCount' => [
                                '$sum' => 1,
                            ],
                            'expedition' => [
                                '$addToSet' => '$subject_expeditionId',
                            ],
                            'last_date' => [
                                '$max' => '$classification_finished_at',
                            ],
                        ],
                    ],
                    [
                        '$project' => [
                            '_id' => 0,
                            'user_name' => '$_id',
                            'transcriptionCount' => 1,
                            'expeditionCount' => [
                                '$size' => '$expedition',
                            ],
                            'last_date' => 1,
                        ],
                    ],
                ]);
            });
        });
    }

    /**
     * Get transcription for dashboard.
     */
    public function getTranscriptionsForDashboardJob(int $expeditionId, $timestamp = null): mixed
    {
        $model = $this->model->with([
            'subject' => function ($query) {
                $query->select('accessURI');
            },
        ])->where('subject_expeditionId', '=', $expeditionId);

        if ($timestamp !== null) {
            $model->where('classification_finished_at', '>=', $timestamp);
        }

        return $model->orderBy('classification_finished_at')->get();
    }

    /**
     * Get minimum finish date of transcriptions for project.
     */
    public function getMinFinishedAtDateByProjectId(int $projectId): mixed
    {
        $result = Cache::remember(md5(__METHOD__.$projectId), 14440, function () use ($projectId) {
            return $this->model->raw(function ($collection) use ($projectId) {
                return $collection->aggregate([
                    ['$match' => ['subject_projectId' => (int) $projectId]],
                    ['$sort' => ['classification_finished_at' => 1]],
                    ['$limit' => 1],
                ]);
            })->first();
        });

        return $result?->classification_finished_at->format('Y-m-d H:i:s');
    }

    /**
     * Get maximum finish date of transcriptions for project.
     * TODO check query because it gave error (projectId 62) could not send aggregate
     *
     * @return mixed|null
     */
    public function getMaxFinishedAtDateByProjectId(int $projectId)
    {
        $result = Cache::remember(md5(__METHOD__.$projectId), 14440, function () use ($projectId) {
            return $this->model->raw(function ($collection) use ($projectId) {
                return $collection->aggregate([
                    ['$match' => ['subject_projectId' => (int) $projectId]],
                    ['$sort' => ['classification_finished_at' => -1]],
                    ['$limit' => 1],
                ]);
            })->first();
        });

        return $result?->classification_finished_at->format('Y-m-d H:i:s');
    }

    /**
     * Get transcription count and group by date.
     */
    public function getTranscriptionCountPerDate(int $workflowId, $begin, $end): mixed
    {
        $key = $workflowId.$begin->__toString().$end->__toString();

        return Cache::rememberForever(md5(__METHOD__.$key), function () use ($workflowId, $begin, $end) {
            return $this->model->raw(function ($collection) use ($workflowId, $begin, $end) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'workflow_id' => $workflowId,
                            'classification_finished_at' => [
                                '$gte' => new UTCDateTime($begin),
                                '$lt' => new UTCDateTime($end),
                            ],
                        ],
                    ],
                    [
                        '$project' => [
                            'yearMonthDay' => [
                                '$dateToString' => [
                                    'format' => '%Y-%m-%d',
                                    'date' => '$classification_finished_at',
                                ],
                            ],
                        ],
                    ],
                    ['$group' => ['_id' => '$yearMonthDay', 'count' => ['$sum' => 1]]],
                    ['$sort' => ['_id' => 1]],
                ]);
            })->pluck('count', '_id');
        });
    }

    /**
     * Return count for transcriber.
     */
    public function getTranscriptionCountForTranscriber(int $projectId, string $transcriber): int
    {
        return $this->model->where('subject_projectId', $projectId)->where('user_name', $transcriber)->count();
    }
}
