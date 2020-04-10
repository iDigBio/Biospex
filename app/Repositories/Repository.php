<?php

namespace App\Repositories;

use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Collection;
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
     * @throws \Exception
     */
    public function resetModel()
    {
        $this->makeModel();
    }

    /**
     * @param array $columns
     * @return mixed
     * @throws \Exception
     */
    public function all(array $columns = ['*'])
    {
        $results = $this->model->get($columns);

        $this->resetModel();

        return $results;
    }

    /**
     * @param array $with
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     * @throws \Exception
     */
    public function allWith(array $with = [], array $columns = ['*'])
    {
        $results = $this->model->with($with)->get($columns);

        $this->resetModel();

        return $results;
    }

    /**
     * @param $resourceId
     * @param array $columns
     * @return mixed
     * @throws \Exception
     */
    public function find($resourceId, array $columns = ['*'])
    {
        $results = $this->model->find($resourceId, $columns);

        $this->resetModel();

        return $results;
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
        $results = $this->model->where($attribute, '=', $value)->first($columns);

        $this->resetModel();

        return $results;
    }

    /**
     * @param $resourceId
     * @param array $with
     * @return \Illuminate\Database\Eloquent\Collection|Model|null|static|static[]
     * @throws \Exception
     */
    public function findWith($resourceId, array $with = [])
    {
        $results = $this->model->with($with)->find($resourceId);

        $this->resetModel();

        return $results;
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
        $results = $this->model->whereIn($field, $values)->get($columns);

        $this->resetModel();

        return $results;
    }

    /**
     * @param array $data
     * @return $this|\Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    public function create(array $data)
    {
        $results = $this->model->create($data);

        $this->resetModel();

        return $results;
    }

    /**
     * @param array $attributes
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function firstOrCreate(array $attributes, array $data = [])
    {
        $result = $this->model->firstOrCreate($attributes, $data);

        $this->resetModel();

        return $result;
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

        $this->resetModel();

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
        $results = $this->model->updateOrCreate($attributes, $values);

        $this->resetModel();

        return $results;
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
        $results = $this->model->where($column, $value)->update($attributes);

        $this->resetModel();

        return $results;
    }

    /**
     * @param $model
     * @return mixed
     * @throws \Exception
     */
    public function delete($model)
    {
        $results = $model instanceof EloquentModel || $model instanceof MongdbModel  ?
            $model->delete() :
            $this->model->destroy($model);

        $this->resetModel();

        return $results;
    }

    /**
     * @param array $attributes
     * @return mixed
     * @throws \Exception
     */
    public function count(array $attributes = [])
    {
        $result = $this->model->where($attributes)->count();

        $this->resetModel();

        return $result;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function truncate()
    {
        $results = $this->model->truncate();

        $this->resetModel();

        return $results;
    }
}