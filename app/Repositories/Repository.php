<?php

namespace App\Repositories;

abstract class Repository
{
    /**
     * @var
     */
    public $model;

    /**
     * Return all
     * 
     * @return mixed
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * Return all with relationships
     * 
     * @param $with
     * @return mixed
     */
    public function allWith($with)
    {
        return $this->model->with($with)->get();
    }

    /**
     * Find by id
     * 
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Create record
     *
     * @param array $data
     * @return mixed
     */
    public function create($data)
    {
        return $this->model->create($data);
    }

    /**
     * Update record
     * @param array $data
     * @return mixed
     */
    public function update($data)
    {
        $model = $this->find($data['id']);
        return $model->fill($data)->save();
    }

    /**
     * Destroy records
     * 
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        return $this->model->destroy($id);
    }

    /**
     * Find with eager loading
     *
     * @param $id
     * @param array $with
     * @return mixed
     */
    public function findWith($id, $with)
    {
        $query = $this->make($with);

        return $query->find($id);
    }

    /**
     * Make a new instance of the entity to query on
     *
     * @param array $with
     */
    public function make($with = [])
    {
        return $this->model->with($with);
    }

    public function save($record)
    {
        return $record->save();
    }

    public function lists($value, $index)
    {
        return $this->model->lists($value, $index)->all();
    }
}
