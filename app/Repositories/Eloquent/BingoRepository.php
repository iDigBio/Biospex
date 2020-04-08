<?php

namespace App\Repositories\Eloquent;

use App\Models\Bingo as Model;
use App\Repositories\Interfaces\Bingo;
use Illuminate\Support\Collection;

class BingoRepository extends EloquentRepository implements Bingo
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
    public function getAdminIndex(int $userId): Collection
    {
        $results = $this->model->with(['user', 'project', 'words'])
            ->where('user_id', $userId)->get();

        $this->resetModel();

        return $results;
    }
}