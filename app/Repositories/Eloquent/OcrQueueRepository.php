<?php

namespace App\Repositories\Eloquent;

use App\Models\OcrQueue as Model;
use App\Repositories\Interfaces\OcrQueue;

class OcrQueueRepository extends EloquentRepository implements OcrQueue
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
     * @inheritdoc
     */
    public function getOcrQueuesForPollCommand()
    {
        $results = $this->model->with(['project.group', 'expedition'])
            ->where('error', 0)
            ->orderBy('id', 'asc')
            ->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getOcrQueueForOcrProcessCommand()
    {
        $results = $this->model->with('project.group.owner')
            ->where('error', '=', 0)
            ->orderBy('id', 'asc')
            ->first();

        $this->resetModel();

        return $results;
    }
}
