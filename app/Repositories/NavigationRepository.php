<?php namespace App\Repositories;

use App\Repositories\Contracts\Navigation;
use App\Models\Navigation as Model;

class NavigationRepository extends Repository implements Navigation
{
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    // This class only implements methods specific to the UserRepository
    public function getMenu($type)
    {
        return $this->model->getMenu($type);
    }
}
