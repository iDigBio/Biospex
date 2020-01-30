<?php

namespace App\Repositories\Eloquent;

use App\Models\ExportQueueFile as Model;
use App\Repositories\Interfaces\ExportQueueFile;
use Illuminate\Support\Collection;

class ExportQueueFileRepository extends EloquentRepository implements ExportQueueFile
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
    public function getFilesByQueueId(string $queueId): Collection
    {
        $results = $this->model->where('queue_id', $queueId)->where('error', 0)->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function getFilesWithoutErrorByQueueId(string $queueId): Collection
    {
        $results = $this->model->with('subject')
            ->where('queue_id', $queueId)->where('error', 0)->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function getFilesWithErrorsByQueueId(string $queueId): Collection
    {
        $results = $this->model->where('queue_id', $queueId)->where('error', 1)->get();

        $this->resetModel();

        return $results;
    }
}