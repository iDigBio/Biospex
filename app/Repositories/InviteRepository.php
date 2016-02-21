<?php namespace App\Repositories;

use App\Repositories\Contracts\Invite;
use App\Models\Invite as Model;

class InviteRepository extends Repository implements Invite
{
    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
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
