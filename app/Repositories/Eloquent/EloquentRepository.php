<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Model;

abstract class EloquentRepository extends Repository
{
    /**
     * @throws \Exception
     */
    public function makeModel()
    {
        $this->model = $this->app->make($this->model());

        if ( ! $this->model instanceof Model)
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");

        return;
    }
}