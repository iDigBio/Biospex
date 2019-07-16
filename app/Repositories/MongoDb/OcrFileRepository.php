<?php

namespace App\Repositories\MongoDb;

use App\Models\OcrFile as Model;
use App\Repositories\Interfaces\OcrFile;

class OcrFileRepository extends MongoDbRepository implements OcrFile
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
    public function getAllOcrQueueFiles($queueId)
    {
        $results = $this->model->where('queue_id', '=', $queueId)->get();

        $this->resetModel();

        return $results;
    }
}