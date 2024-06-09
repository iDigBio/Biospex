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

namespace App\Repositories;

use App\Models\PanoptesTranscription;
use Illuminate\Support\Facades\Cache;
use MongoDB\BSON\UTCDateTime;

/**
 * Class PanoptesTranscriptionRepository
 *
 * @package App\Repositories
 */
class PanoptesTranscriptionRepository extends BaseRepository
{
    /**
     * PanoptesTranscriptionRepository constructor.
     *
     * @param \App\Models\PanoptesTranscription $panoptesTranscription
     */
    public function __construct(PanoptesTranscription $panoptesTranscription)
    {

        $this->model = $panoptesTranscription;
    }

    /**
     * Get contributor count for all transcriptions.
     * @return mixed
     */
    public function getContributorCount()
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
     *
     * @TODO Use expedition_stat table to get sum
     * @return mixed
     */
    public function getTotalTranscriptions()
    {
        return Cache::remember(md5(__METHOD__), 14440, function () {
            return $this->model->count();
        });
    }

    /**
     * Get project transcription count.
     *
     * TODO Change to sum expedition stat table
     * @param $projectId
     * @return int
     */
    public function getProjectTranscriptionCount(int $projectId): int
    {
        $result = Cache::remember(md5(__METHOD__.$projectId), 14440, function () use ($projectId) {
            return $this->model->raw(function ($collection) use ($projectId) {
                return $collection->aggregate([
                    ['$match' => ['subject_projectId' => $projectId]],
                    ['$count' => 'count'],
                ]);
            })->first();
        });

        return $result === null ? 0 : $result->count;
    }

    /**
     * Get expedition transcription count.
     *
     * @param int $expeditionId
     * @return int
     */
    public function getExpeditionTranscriptionCount(int $expeditionId)
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
     * Get transcriber count for project.
     *
     * @param int $projectId
     * @return int
     */
    public function getProjectTranscriberCount(int $projectId)
    {
        $result = Cache::remember(md5(__METHOD__.$projectId), 14440, function () use ($projectId) {
            return $this->model->raw(function ($collection) use ($projectId) {
                return $collection->aggregate([
                    [
                        '$match' => ['subject_projectId' => (int) $projectId],
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
     *
     * @param int $projectId
     * @return mixed
     */
    public function getTranscribersTranscriptionCount(int $projectId)
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
                            '_id'                => '$user_name',
                            'transcriptionCount' => [
                                '$sum' => 1,
                            ],
                            'expedition'         => [
                                '$addToSet' => '$subject_expeditionId',
                            ],
                            'last_date'          => [
                                '$max' => '$classification_finished_at',
                            ],
                        ],
                    ],
                    [
                        '$project' => [
                            '_id'                => 0,
                            'user_name'          => '$_id',
                            'transcriptionCount' => 1,
                            'expeditionCount'    => [
                                '$size' => '$expedition',
                            ],
                            'last_date'          => 1,
                        ],
                    ],
                ]);
            });
        });
    }

    /**
     * Get transcription for dashboard.
     *
     * @param int $expeditionId
     * @param null $timestamp
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getTranscriptionForDashboardJob(int $expeditionId, $timestamp = null)
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
     *
     * @param int $projectId
     * @return mixed|null
     */
    public function getMinFinishedAtDateByProjectId(int $projectId)
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
     * @param $projectId
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
     *
     * @param int $workflowId
     * @param $begin
     * @param $end
     * @return mixed
     */
    public function getTranscriptionCountPerDate(int $workflowId, $begin, $end)
    {
        $key = $workflowId . $begin->__toString() . $end->__toString();

        return Cache::rememberForever(md5(__METHOD__.$key), function () use ($workflowId, $begin, $end) {
            return $this->model->raw(function ($collection) use ($workflowId, $begin, $end) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'workflow_id'                => $workflowId,
                            'classification_finished_at' => [
                                '$gte' => new UTCDateTime($begin),
                                '$lt'  => new UTCDateTime($end),
                            ],
                        ],
                    ],
                    [
                        '$project' => [
                            'yearMonthDay' => [
                                '$dateToString' => [
                                    'format' => '%Y-%m-%d',
                                    'date'   => '$classification_finished_at',
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
     *
     * @param int $projectId
     * @param string $transcriber
     * @return int
     */
    public function getTranscriptionCountForTranscriber(int $projectId, string $transcriber): int
    {
        return $this->model->where('subject_projectId', $projectId)->where('user_name', $transcriber)->count();
    }
}