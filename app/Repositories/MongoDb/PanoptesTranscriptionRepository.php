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

namespace App\Repositories\MongoDb;

use App\Facades\DateHelper;
use App\Models\PanoptesTranscription as Model;
use App\Repositories\Interfaces\PanoptesTranscription;
use Cache;

class PanoptesTranscriptionRepository extends MongoDbRepository implements PanoptesTranscription
{
    /**
     * Specify Model class name
     *
     * @return \Illuminate\Database\Eloquent\Model|string
     */
    public function model()
    {
        return Model::class;
    }

    /**
     * @inheritDoc
     */
    public function getTotalTranscriptions()
    {
        return Cache::remember(md5(__METHOD__), 14440, function () {
            return $this->model->count();
        });
    }

    /**
     * @inheritDoc
     */
    public function getContributorCount()
    {
        return Cache::remember(md5(__METHOD__), 14440, function () {
            return $this->model->where('user_name', 'not regexp', '/^not-logged-in.*/i')
                ->groupBy('user_name')
                ->get()
                ->count();
        });
    }

    /**
     * @inheritdoc
     */
    public function getProjectTranscriberCount($projectId)
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
     * @inheritdoc
     */
    public function getProjectTranscriptionCount($projectId)
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
     * @inheritDoc
     */
    public function getMinFinishedAtDateByProjectId($projectId)
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

        return null === $result ? null : DateHelper::formatMongoDbDate($result->classification_finished_at, 'Y-m-d H:i:s');
    }

    /**
     * @inheritDoc
     */
    public function getMaxFinishedAtDateByProjectId($projectId)
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

        return null === $result ? null : DateHelper::formatMongoDbDate($result->classification_finished_at, 'Y-m-d H:i:s');
    }

    /**
     * @inheritDoc
     */
    public function getTranscriptionCountPerDate($workflowId, $begin, $end)
    {
        $key = $workflowId . $begin->__toString() . $end->__toString();

        return Cache::rememberForever(md5(__METHOD__.$key), function () use ($workflowId, $begin, $end) {
                return $this->model->raw(function ($collection) use ($workflowId, $begin, $end) {
                    return $collection->aggregate([
                        [
                            '$match' => [
                                'workflow_id'                => $workflowId,
                                'classification_finished_at' => [
                                    '$gte' => DateHelper::formatDateToUtcTimestamp($begin),
                                    '$lt'  => DateHelper::formatDateToUtcTimestamp($end),
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
     * @inheritDoc
     */
    public function getTranscribersTranscriptionCount($projectId)
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
     * @inheritDoc
     */
    public function getTranscriptionForDashboardJob($expeditionId, $timestamp = null)
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
}
