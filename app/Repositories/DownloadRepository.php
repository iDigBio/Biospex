<?php namespace App\Repositories;

use App\Repositories\Contracts\Download;
use App\Models\Download as Model;

class DownloadRepository extends Repository implements Download
{
    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Return expired downloads.
     *
     * @return mixed
     */
    public function getExpired()
    {
        return $this->model->getExpired();
    }
}
