<?php namespace App\Repositories;

use App\Repositories\Contracts\Header;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class HeaderRepository extends Repository implements Header, CacheableInterface
{

    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Header::class;
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
