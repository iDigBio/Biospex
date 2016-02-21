<?php namespace App\Repositories;

use App\Repositories\Contracts\Header;
use App\Models\Header as Model;

class HeaderRepository extends Repository implements Header
{
    /**
     * @param Header $header
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Find by project Id
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function getByProjectId($id)
    {
        return $this->model->getByProjectId($id);
    }
}
