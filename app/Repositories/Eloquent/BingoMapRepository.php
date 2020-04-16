<?php

namespace App\Repositories\Eloquent;

use App\Models\BingoMap as Model;
use App\Repositories\Interfaces\BingoMap;
use Illuminate\Support\Collection;

class BingoMapRepository extends EloquentRepository implements BingoMap
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
    public function getBingoMapsByBingoId(string $bingoId): Collection
    {
        $results = $this->model->where('bingo_id', $bingoId)->get();
        $this->resetModel();

        return $results;
    }

}