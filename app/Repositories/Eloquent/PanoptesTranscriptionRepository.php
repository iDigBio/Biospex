<?php

namespace App\Repositories\Eloquent;

use App\Models\PanoptesTranscription;
use App\Repositories\Contracts\PanoptesTranscriptionContract;
use DB;
use Illuminate\Contracts\Container\Container;

class PanoptesTranscriptionRepository extends EloquentRepository implements PanoptesTranscriptionContract
{

    /**
     * PanoptesTranscriptionRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(PanoptesTranscription::class)
            ->setRepositoryId('biospex.repository.panoptesTranscription');

    }

    /**
     * Retrieve transcription count using workflow id.
     *
     * @param $workflowId
     * @param array $attributes
     * @return int
     */
    public function getTranscriptionCountByWorkflowId($workflowId, array $attributes = ['*'])
    {
        return $this->findWhere(['workflow_id', '=', $workflowId], $attributes)->count();
    }

    /**
     * Return total count of transcriptions.
     *
     * @return int|mixed
     */
    public function getTotalTranscriptions()
    {
        return $this->count();
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
        return $this->findWhere(['subject_expeditionId', '=', $expeditionId], $attributes)->count();
    }

    /**
     * Retrieve earliest date a transcription was finished for project.
     *
     * @param integer $projectId
     * @return mixed
     */
    public function getMinFinishedAtDateByProjectId($projectId)
    {
        $result = $this->where('subject_projectId', '=', $projectId)->min('classification_finished_at');

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
        $result = $this->where('subject_projectId', '=', $projectId)->max('classification_finished_at');

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
        return $this->groupBy('user_name')->findWhere(['user_name', 'not regexp', '/^not-logged-in.*/i'])->count();
    }

    /**
     * Get transcription counts per user.
     *
     * @param $projectId
     * @return mixed
     */
    public function getUserTranscriptionCount($projectId)
    {
        $result = $this->raw(function ($collection) use ($projectId) {
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

        return $result['result'];
    }
}