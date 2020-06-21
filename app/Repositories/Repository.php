<?php
/**
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Repositories;

use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Jenssegers\Mongodb\Eloquent\Model as MongdbModel;

abstract class Repository
{
    /**
     * Specify Model class name
     */
    abstract function model();

    /**
     * @var App
     */
    protected $app;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @param App $app
     * @throws \Exception
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * @param array $columns
     * @return mixed
     * @throws \Exception
     */
    public function all(array $columns = ['*'])
    {
        return $this->model->get($columns);
    }

    /**
     * @param array $with
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     * @throws \Exception
     */
    public function allWith(array $with = [], array $columns = ['*'])
    {
        return $this->model->with($with)->get($columns);
    }

    /**
     * @param $resourceId
     * @param array $columns
     * @return mixed
     * @throws \Exception
     */
    public function find($resourceId, array $columns = ['*'])
    {
        return $this->model->find($resourceId, $columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     * @throws \Exception
     */
    public function findBy($attribute, $value, array $columns = ['*'])
    {
        return $this->model->where($attribute, '=', $value)->first($columns);
    }

    /**
     * @param $resourceId
     * @param array $with
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function findWith($resourceId, array $with = [])
    {
        return $this->model->with($with)->find($resourceId);
    }

    /**
     * @param $field
     * @param array $values
     * @param array $columns
     * @return mixed
     * @throws \Exception
     */
    public function whereIn($field, array $values, array $columns = ['*'])
    {
        return $this->model->whereIn($field, $values)->get($columns);
    }

    /**
     * @param array $data
     * @return $this|\Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @param array $attributes
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function firstOrCreate(array $attributes, array $data = [])
    {
        return $this->model->firstOrCreate($attributes, $data);
    }

    /**
     * @param array $data
     * @param $resourceId
     * @return bool
     * @throws \Exception
     */
    public function update(array $data, $resourceId)
    {
        $model = $this->model->find($resourceId);
        $result = $model->fill($data)->save();

        return $result ? $model : false;
    }

    /**
     * @param array $attributes
     * @param array $values
     * @return mixed
     * @throws \Exception
     */
    public function updateOrCreate(array $attributes, array $values)
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    /**
     * @param array $attributes
     * @param string $column
     * @param string $value
     * @return mixed
     * @throws \Exception
     */
    public function updateMany(array $attributes, string $column, string $value)
    {
        return $this->model->where($column, $value)->update($attributes);
    }

    /**
     * @param $model
     * @return mixed
     * @throws \Exception
     */
    public function delete($model)
    {
        return $model instanceof EloquentModel || $model instanceof MongdbModel  ?
            $model->delete() :
            $this->model->destroy($model);
    }

    /**
     * @param array $attributes
     * @return mixed
     * @throws \Exception
     */
    public function count(array $attributes = [])
    {
        return $this->model->where($attributes)->count();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function truncate()
    {
        return $this->model->truncate();
    }
}