<?php

namespace App\Repositories;

use App\Interfaces\Eloquent;
use Illuminate\Container\Container as App;
use Jenssegers\Mongodb\Eloquent\Builder;
use Jenssegers\Mongodb\Eloquent\Model;

abstract class MongoDbRepository implements Eloquent
{

    /**
     * @var App
     */
    private $app;

    /**
     * @var Model
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
     * Specify Model class name
     *
     * @return Model
     */
    abstract function model();

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
     * @param $id
     * @param array $columns
     * @return mixed
     * @throws \Exception
     */
    public function find($id, array $columns = ['*'])
    {
        $results = $this->model->find($id, $columns);

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
     * @param $id
     * @param array $with
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null|static|static[]
     * @throws \Exception
     */
    public function findWith($id, array $with = [])
    {
        $results = $this->model->with($with)->find($id);

        $this->resetModel();

        return $results;
    }

    /**
     * @param $id
     * @param array $with
     * @return mixed
     * @throws \Exception
     */
    public function findOnlyTrashed($id, array $with = [])
    {
        $results = $this->model->with($with)->onlyTrashed()->find($id);

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
    public function getWhereIn($field, array $values, array $columns = ['*'])
    {
        $results = $this->model->whereIn($field, $values)->get($columns);

        $this->resetModel();

        return $results;
    }

    /**
     * @param array $columns
     * @return mixed
     * @throws \Exception
     */
    public function getOnlyTrashed(array $columns = ['*'])
    {
        $results = $this->model->onlyTrashed()->get($columns);

        $this->resetModel();

        return $results;
    }

    /**
     * @param array $data
     * @return mixed
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
     * @return mixed
     * @throws \Exception
     */
    public function firstOrCreate(array $attributes)
    {
        $results = $this->model->firstOrCreate($attributes);

        $this->resetModel();

        return $results;
    }

    /**
     * @param array $data
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function update(array $data, $id)
    {
        $results = $this->model->find($id)->fill($data)->save();

        $this->resetModel();

        return $results;
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
     * @param $model
     * @return bool|int|null
     * @throws \Exception
     */
    public function delete($model)
    {
        $results = $model instanceof Model ?
            $model->delete() :
            $this->model->destroy($model);

        $this->resetModel();

        return $results;
    }

    /**
     * @param $model
     * @return bool|null
     * @throws \Exception
     */
    public function destroy($model)
    {
        $results = $model instanceof Model ?
            $model->forceDelete() :
            $this->model->find($model)->forceDelete();

        $this->resetModel();

        return $results;
    }

    /**
     * @param $model
     * @return mixed
     * @throws \Exception
     */
    public function restore($model)
    {
        $results = $model instanceof Model ?
            $model->restore() :
            $this->model->find($model)->restore();

        $this->resetModel();

        return $results;
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

    /**
     * @return Builder
     * @throws \Exception
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if ( ! $model instanceof Model)
            throw new \Exception("Class {$this->model()} must be an instance of Jenssegers\Mongodb\Eloquent\Model\\");

        return $this->model = $model->newQuery();
    }
}