<?php

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
        $results = $this->model->count();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function getContributorCount()
    {
        $results = $result = Cache::remember(md5(__METHOD__), 240, function () {
            return $this->model
                ->where('user_name', 'not regexp', '/^not-logged-in.*/i')
                ->groupBy('user_name')
                ->get()
                ->count();
        });

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getProjectTranscriberCount($projectId)
    {
        $result = $this->model->raw(function ($collection) use ($projectId) {
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

        $this->resetModel();

        return $result === null ? 0 : $result->count;
    }

    /**
     * @inheritdoc
     */
    public function getProjectTranscriptionCount($projectId)
    {
        $result = $this->model->raw(function ($collection) use ($projectId) {
            return $collection->aggregate([
                ['$match' => ['subject_projectId' => $projectId]],
                ['$count' => 'count'],
            ]);
        })->first();

        $this->resetModel();

        return $result === null ? 0 : $result->count;
    }

    /**
     * Retrieve earliest date a transcription was finished for project.
     *
     * @param $projectId
     * @return mixed|null
     * @throws \Exception
     */
    public function getMinFinishedAtDateByProjectId($projectId)
    {
        $result = $this->model->raw(function ($collection) use ($projectId) {
            return $collection->aggregate([
                ['$match' => ['subject_projectId' => (int) $projectId]],
                ['$sort' => ['classification_finished_at' => 1]],
                ['$limit' => 1],
            ]);
        })->first();

        $this->resetModel();

        return null === $result ? null : DateHelper::formatStringDate($result->classification_finished_at);
    }

    /**
     * Retrieve amx date a transcription was finished for project.
     *
     * @param $projectId
     * @return mixed|null
     * @throws \Exception
     */
    public function getMaxFinishedAtDateByProjectId($projectId)
    {
        $result = $this->model->raw(function ($collection) use ($projectId) {
            return $collection->aggregate([
                ['$match' => ['subject_projectId' => (int) $projectId]],
                ['$sort' => ['classification_finished_at' => -1]],
                ['$limit' => 1],
            ]);
        })->first();

        $this->resetModel();

        return null === $result ? null : DateHelper::formatStringDate($result->classification_finished_at);
    }

    /**
     * Retrieve transcription count grouped by date.
     *
     * @param $workflowId
     * @return mixed
     * @throws \Exception
     */
    public function getTranscriptionCountPerDate($workflowId)
    {
        $results = $this->model->raw(function ($collection) use ($workflowId) {
            return $collection->aggregate([
                ['$match' => ['workflow_id' => $workflowId]],
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
        });

        $this->resetModel();

        return $results;
    }

    /**
     * Get transcription counts per user.
     *
     * @param $projectId
     * @return mixed
     * @throws \Exception
     */
    public function getUserTranscriptionCount($projectId)
    {
        $results = $this->model->raw(function ($collection) use ($projectId) {
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
                        'last_date'          => 1
                    ],
                ],
            ]);
        });

        $this->resetModel();

        return $results;
    }

    // not used?

    /**
     * @param $expeditionId
     * @param $timestamp
     * @return mixed
     * @throws \Exception
     */
    public function getTranscriptionForDashboardJob($expeditionId, $timestamp)
    {
        $model = $this->model->with([
            'subject' => function ($query) {
                $query->select('accessURI');
            },
        ])->where('subject_expeditionId', '=', $expeditionId);

        if ($timestamp !== null) {
            $model->where('classification_finished_at', '>=', $timestamp);
        }

        $results = $model->orderBy('classification_finished_at')->get();

        $this->resetModel();

        return $results;
    }
}
