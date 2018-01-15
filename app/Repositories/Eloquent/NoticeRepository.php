<?php

namespace App\Repositories\Eloquent;

use App\Models\Notice as Model;
use App\Repositories\Interfaces\Notice;

class NoticeRepository extends EloquentRepository implements Notice
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
    public function getEnabledNotices()
    {
        $results = $this->model->where('enabled', 1)->get();

        $this->resetModel();

        return $results;
    }
}
