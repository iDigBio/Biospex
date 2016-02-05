<?php namespace Biospex\Repositories;

use Biospex\Repositories\Contracts\UserGridField;
use Biospex\Models\UserGridField as Model;

class UserGridFieldRepository extends Repository implements UserGridField
{
    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function findByUserProjectExpedition($userId, $projectId, $expeditionId)
    {
        return $this->model->findByUserProjectExpedition($userId, $projectId, $expeditionId);
    }
}
