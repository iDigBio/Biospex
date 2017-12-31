<?php

namespace App\Repositories;

use App\Interfaces\Eloquent;
use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model;

abstract class EloquentRepository implements Eloquent
{

    /**
     * @var App
     */
    private $app;

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
     * Specify Model class name
     *
     * @return Model
     */
    abstract function model();

    /**
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(array $columns = ['*'])
    {
        return $this->model->get($columns);
    }

    /**
     * @param $id
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function find($id, array $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findBy($attribute, $value, array $columns = ['*'])
    {
        return $this->model->where($attribute, '=', $value)->first($columns);
    }

    /**
     * @param $id
     * @param array $with
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findWith($id, array $with = [])
    {
        return $this->model->with($with)->find($id);
    }

    /**
     * @param $id
     * @param array $with
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findOnlyTrashed($id, array $with = [])
    {
        return $this->model->with($with)->onlyTrashed()->find($id);
    }

    /**
     * @param $field
     * @param array $values
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getWhereIn($field, array $values, array $columns = ['*'])
    {
        return $this->model->whereIn($field, $values)->get($columns);
    }

    /**
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOnlyTrashed(array $columns = ['*'])
    {
        return $this->model->onlyTrashed()->get($columns);
    }

    /**
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrCreate(array $attributes)
    {
        return $this->model->firstOrCreate($attributes);
    }

    /**
     * @param array $data
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|bool
     */
    public function update(array $data, $id)
    {
        $model = $this->model->find($id);
        $result = $model->fill($data)->save();

        return $result ? $model : false;
    }

    /**
     * @param array $attributes
     * @param array $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateOrCreate(array $attributes, array $values)
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    /**
     * @param $model
     * @return bool
     */
    public function delete($model)
    {
        return $model instanceof Model ?
            $model->delete() :
            $this->model->destroy($model);
    }

    /**
     * @param $model
     * @return bool
     */
    public function destroy($model)
    {
        return $model instanceof Model ?
            $model->forceDelete() :
            $this->model->find($model)->forceDelete();
    }

    /**
     * @param $model
     * @return mixed
     */
    public function restore($model)
    {
        return $model instanceof Model ?
            $model->restore() :
            $this->model->onlyTrashed()->find($model)->restore();
    }

    /**
     * Truncate table.
     *
     * @return bool
     */
    public function truncate()
    {
        return $this->model->truncate();
    }

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