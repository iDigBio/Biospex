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
     * @param array $columns
     * @return mixed
     */
    public function all(array $columns = ['*'])
    {
        return $this->model->get($columns);
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, array $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findBy($attribute, $value, array $columns = ['*'])
    {
        return $this->model->where($attribute, '=', $value)->first($columns);
    }

    /**
     * @param $id
     * @param array $with
     * @return \Illuminate\Database\Eloquent\Collection|Model|null|static|static[]
     */
    public function findWith($id, array $with = [])
    {
        return $this->model->with($with)->find($id);
    }

    /**
     * @inheritdoc
     */
    public function findOnlyTrashed($id, array $with = [])
    {
        return $this->model->with($with)->onlyTrashed()->find($id);
    }

    /**
     * @inheritdoc
     */
    public function getWhereIn($field, array $values, array $columns = ['*'])
    {
        return $this->model->whereIn($field, $values)->get($columns);
    }

    /**
     * @inheritdoc
     */
    public function getOnlyTrashed(array $columns = ['*'])
    {
        return $this->model->onlyTrashed()->get($columns);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @param array $attributes
     * @return mixed
     */
    public function firstOrCreate(array $attributes)
    {
        return $this->model->firstOrCreate($attributes);
    }

    /**
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function update(array $data, $id)
    {
        return $this->model->find($id)->fill($data)->save();
    }

    /**
     * @param array $attributes
     * @param array $values
     * @return mixed
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
            $this->model->find($model)->restore();
    }

    /**
     * Truncate table.
     *
     * @return mixed
     */
    public function truncate()
    {
        return $this->model->truncate();
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