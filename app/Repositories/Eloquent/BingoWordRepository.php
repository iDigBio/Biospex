<?php

namespace App\Repositories\Eloquent;

use App\Models\BingoWord as Model;
use App\Repositories\Interfaces\BingoWord;

class BingoWordRepository extends EloquentRepository implements BingoWord
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

}