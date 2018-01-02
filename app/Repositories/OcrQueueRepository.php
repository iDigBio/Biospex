<?php

namespace App\Repositories;

use App\Models\OcrQueue as Model;
use App\Interfaces\OcrQueue;

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
        $results = $this->model->where('error', 0)->orderBy('ocr_csv_id', 'asc')->orderBy('created_at', 'asc')->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getOcrQueueForOcrProcessCommand()
    {
        $results = $this->model->with(['project.group.owner', 'ocrCsv'])
            ->where('status', '<=', 1)
            ->where('error', '=', 0)
            ->orderBy('id', 'asc')
            ->first();

        $this->resetModel();

        return $results;
    }
}
