<?php 

namespace App\Repositories;

use App\Repositories\Contracts\Invite;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class InviteRepository extends Repository implements Invite, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Invite::class;
    }

    /**
     * Find invite by code
     *
     * @param $code
     * @return mixed
     */
    public function findByCode($code)
    {
        return $this->model->findByCode($code);
    }

    /**
     * Find duplicate
     *
     * @param $id
     * @param $email
     * @return mixed
     */
    public function checkDuplicate($id, $email)
    {
        return $this->model->checkDuplicate($id, $email);
    }

    /**
     * Find invite by group id
     *
     * @param $id
     * @return mixed
     */
    public function findByGroupId($id)
    {
        return $this->model->findByGroupId($id);
    }
}
