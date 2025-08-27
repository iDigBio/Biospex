<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
use IDigAcademy\AutoCache\Helpers\AutoCacheHelper;
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
     * Get a contributor count for all transcriptions.
     */
    public function getContributorCount(): mixed
    {
        // TODO: Eventually resolve Laravel issue with count.
        return $this->model->select('user_name')
            ->where('user_name', 'not regexp', '/^not-logged-in.*/i')
            ->groupBy('user_name')
            ->setTtl(14440)
            ->get()
            ->count();
    }

    /**
     * Get total transcriptions for site.
     */
    public function getTotalTranscriptions(): mixed
    {
        return $this->model->setTtl(14440)->count();
    }

    /**
     * Get expedition transcription count.
     */
    public function getExpeditionTranscriptionCount(int $expeditionId): int
    {
        $aggregationQuery = [
            ['$match' => ['subject_expeditionId' => $expeditionId]],
            ['$count' => 'count'],
        ];

        $bindings = ['expedition_id' => $expeditionId];
        $key = AutoCacheHelper::generateKey($aggregationQuery, $bindings);
        $tags = AutoCacheHelper::generateTags(['panoptes_transcriptions']);

        $result = AutoCacheHelper::remember($key, 14440, function () use ($aggregationQuery) {
            return $this->model->raw(function ($collection) use ($aggregationQuery) {
                return $collection->aggregate($aggregationQuery);
            })->first();
        }, $tags);

        return $result === null ? 0 : $result->count;
    }

    /**
     * Get transcriber count for expedition.
     */
    public function getExpeditionTranscriberCount(int $expeditionId): int
    {
        $aggregationQuery = [
            [
                '$match' => ['subject_expeditionId' => $expeditionId],
            ],
            [
                '$group' => ['_id' => '$user_name'],
            ],
            ['$count' => 'count'],
        ];

        $bindings = ['expedition_id' => $expeditionId];
        $key = AutoCacheHelper::generateKey($aggregationQuery, $bindings);
        $tags = AutoCacheHelper::generateTags(['panoptes_transcriptions']);

        $result = AutoCacheHelper::remember($key, 14440, function () use ($aggregationQuery) {
            return $this->model->raw(function ($collection) use ($aggregationQuery) {
                return $collection->aggregate($aggregationQuery);
            })->first();
        }, $tags);

        return $result === null ? 0 : $result->count;
    }

    /**
     * Get transcribers transcription count.
     */
    public function getTranscribersTranscriptionCount(int $projectId): mixed
    {
        $aggregationQuery = [
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
        ];

        $bindings = ['project_id' => $projectId];
        $key = AutoCacheHelper::generateKey($aggregationQuery, $bindings);
        $tags = AutoCacheHelper::generateTags(['panoptes_transcriptions']);

        return AutoCacheHelper::remember($key, 0, function () use ($aggregationQuery) {
            return $this->model->raw(function ($collection) use ($aggregationQuery) {
                return $collection->aggregate($aggregationQuery);
            });
        }, $tags);
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
        $aggregationQuery = [
            ['$match' => ['subject_projectId' => (int) $projectId]],
            ['$sort' => ['classification_finished_at' => 1]],
            ['$limit' => 1],
        ];

        $bindings = ['project_id' => $projectId];
        $key = AutoCacheHelper::generateKey($aggregationQuery, $bindings);
        $tags = AutoCacheHelper::generateTags(['panoptes_transcriptions']);

        $result = AutoCacheHelper::remember($key, 14440, function () use ($aggregationQuery) {
            return $this->model->raw(function ($collection) use ($aggregationQuery) {
                return $collection->aggregate($aggregationQuery);
            })->first();
        }, $tags);

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
        $aggregationQuery = [
            ['$match' => ['subject_projectId' => (int) $projectId]],
            ['$sort' => ['classification_finished_at' => -1]],
            ['$limit' => 1],
        ];

        $bindings = ['project_id' => $projectId];
        $key = AutoCacheHelper::generateKey($aggregationQuery, $bindings);
        $tags = AutoCacheHelper::generateTags(['panoptes_transcriptions']);

        $result = AutoCacheHelper::remember($key, 14440, function () use ($aggregationQuery) {
            return $this->model->raw(function ($collection) use ($aggregationQuery) {
                return $collection->aggregate($aggregationQuery);
            })->first();
        }, $tags);

        return $result?->classification_finished_at->format('Y-m-d H:i:s');
    }

    /**
     * Get transcription count and group by date.
     */
    public function getTranscriptionCountPerDate(int $workflowId, $begin, $end): mixed
    {
        $aggregationQuery = [
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
        ];

        $bindings = [
            'workflow_id' => $workflowId,
            'begin' => $begin->__toString(),
            'end' => $end->__toString(),
        ];

        $key = AutoCacheHelper::generateKey($aggregationQuery, $bindings);
        $tags = AutoCacheHelper::generateTags(['panoptes_transcriptions']);

        return AutoCacheHelper::remember($key, 14400, function () use ($aggregationQuery) {
            return $this->model->raw(function ($collection) use ($aggregationQuery) {
                return $collection->aggregate($aggregationQuery);
            })->pluck('count', '_id');
        }, $tags);
    }

    /**
     * Return count for transcriber.
     */
    public function getTranscriptionCountForTranscriber(int $projectId, string $transcriber): int
    {
        return $this->model->where('subject_projectId', $projectId)->where('user_name', $transcriber)->count();
    }

    /**
     * Get transcriber count for project.
     */
    public function getProjectTranscriberCount(int $projectId): int
    {
        $aggregationQuery = [
            [
                '$match' => ['subject_projectId' => (int) $projectId],
            ],
            [
                '$group' => ['_id' => '$user_name'],
            ],
            ['$count' => 'count'],
        ];

        $bindings = ['project_id' => $projectId];
        $key = AutoCacheHelper::generateKey($aggregationQuery, $bindings);
        $tags = AutoCacheHelper::generateTags(['panoptes_transcriptions']);

        $result = AutoCacheHelper::remember($key, 14440, function () use ($aggregationQuery) {
            return $this->model->raw(function ($collection) use ($aggregationQuery) {
                return $collection->aggregate($aggregationQuery);
            })->first();
        }, $tags);

        return $result === null ? 0 : $result->count;
    }
}
