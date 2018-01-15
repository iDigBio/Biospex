<?php

namespace App\Repositories\Eloquent;

use App\Models\Faq as Model;
use App\Repositories\Interfaces\Faq;

class FaqRepository extends EloquentRepository implements Faq
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
