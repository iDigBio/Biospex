<?php

namespace App\Repositories;

use App\Models\PanoptesTranscription as Model;
use App\Interfaces\PanoptesTranscription;
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
     * Return total count of transcriptions.
     *
     * @return int|mixed
     */
    public function getTotalTranscriptions()
    {
        return $this->model->count();
    }

    /**
     * Retrieve transcription count using expedition id.
     *
     * @param $expeditionId
     * @param array $attributes
     * @return int
     */
    public function getTranscriptionCountByExpeditionId($expeditionId, array $attributes = ['*'])
    {
        //return $this->findWhere(['subject_expeditionId', '=', $expeditionId], $attributes)->count();
        return $this->model->where('subject_expeditionId', $expeditionId)->count();
    }

    /**
     * Retrieve earliest date a transcription was finished for project.
     *
     * @param integer $projectId
     * @return mixed
     */
    public function getMinFinishedAtDateByProjectId($projectId)
    {
        $result = $this->model->where('subject_projectId', $projectId)->min('classification_finished_at');

        return null === $result ? null : $result->toDateTime()->format('Y-m-d');
    }

    /**
     * Retrieve amx date a transcription was finished for project.
     *
     * @param integer $projectId
     * @return mixed
     */
    public function getMaxFinishedAtDateByProjectId($projectId)
    {
        $result = $this->model->where('subject_projectId', '=', $projectId)->max('classification_finished_at');

        return null === $result ? null : $result->toDateTime()->format('Y-m-d');
    }

    /**
     * Retrieve transcription count grouped by date.
     *
     * @param $workflowId
     * @return mixed
     */
    public function getTranscriptionCountPerDate($workflowId)
    {
        return $this->raw(function ($collection) use ($workflowId) {
            return $collection->aggregate(
                [
                    ['$match' => ['workflow_id' => $workflowId]],
                    ['$project' =>
                         [
                             'yearMonthDay' => ['$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$classification_finished_at']],
                         ]
                    ],
                    ['$group' => ['_id' => '$yearMonthDay', 'count' => ['$sum' => 1]]],
                    ['$sort' => ['_id' => 1]]
                ]);
        });
    }

    /**
     * @return int|mixed
     */
    public function getContributorCount()
    {
        return Cache::tags('panoptesTranscriptions')->rememberForever(md5(__METHOD__), function () {
            return $this->model
                ->where('user_name', 'not regexp', '/^not-logged-in.*/i')
                ->groupBy('user_name')
                ->get()
                ->count();
        });
    }

    /**
     * Get transcription counts per user.
     *
     * @param $projectId
     * @return mixed
     */
    public function getUserTranscriptionCount($projectId)
    {
        return Cache::tags('panoptesTranscriptions')->rememberForever(md5(__METHOD__ . $projectId), function () use ($projectId) {
            return $this->model->raw(function ($collection) use ($projectId) {
                return $collection->aggregate(
                    [
                        [
                            '$match' => [
                                'subject_projectId' => (int) $projectId
                            ]
                        ],
                        [
                            '$sort' => [
                                'classification_finished_at' => -1
                            ]
                        ],
                        [
                            '$group' => [
                                '_id'                => '$user_name',
                                'transcriptionCount' => [
                                    '$sum' => 1
                                ],
                                'expedition'         => [
                                    '$addToSet' => '$subject_expeditionId'
                                ],
                                'last_date'          => [
                                    '$first' => '$classification_finished_at'
                                ]
                            ]
                        ],
                        [
                            '$project' => [
                                '_id'                => 0,
                                'user_name'          => '$_id',
                                'transcriptionCount' => 1,
                                'expeditionCount'    => [
                                    '$size' => '$expedition'
                                ],
                                'last_date'          => 1
                            ]
                        ]
                    ]);
            });
        });
    }

    /**
     * @inheritdoc
     */
    public function getTranscriptionForDashboardJob($expeditionId, $timestamp)
    {
        $this->model
            ->with(['subject' => function ($query) {
                $query->select('accessURI');
            }])
            ->where('subject_expeditionId', '=', $expeditionId);

        if ($timestamp !== null)
        {
            $this->model->where('classification_finished_at', '>=', $timestamp);
        }

        return $this->model->orderBy('classification_finished_at')->get();
    }
}