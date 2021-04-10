<?php
/*
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

namespace App\Services\Model;

use App\Models\Bingo;

/**
 * Class BaseModelService
 *
 * @mixin \Eloquent
 * @package App\Services\Model
 */
class BaseModelService
{
    /**
     * @var \Illuminate\Database\Eloquent\Model|\Jenssegers\Mongodb\Eloquent\Model
     */
    protected $model;

    /**
     * Get all.
     *
     * @param array|string[] $columns
     * @return mixed
     */
    public function all(array $columns = ['*'])
    {
        return $this->model->get($columns);
    }

    /**
     * Get all with relations.
     *
     * @param array $with
     * @param array|string[] $columns
     * @return mixed
     */
    public function allWith(array $with = [], array $columns = ['*'])
    {
        return $this->model->with($with)->get($columns);
    }

    /**
     * Find by id.
     *
     * @param $resourceId
     * @param array|string[] $columns
     * @return mixed
     */
    public function find($resourceId, array $columns = ['*'])
    {
        return $this->model->find($resourceId, $columns);
    }

    /**
     * Find by field.
     *
     * @param $attribute
     * @param $value
     * @param array|string[] $columns
     * @return mixed
     */
    public function findBy($attribute, $value, array $columns = ['*'])
    {
        return $this->model->where($attribute, '=', $value)->first($columns);
    }

    /**
     * Get all by attribute value.
     *
     * @param $attribute
     * @param $value
     * @param string $op
     * @param array|string[] $columns
     * @return mixed
     */
    public function getBy($attribute, $value, $op = '=', array $columns = ['*'])
    {
        return $this->model->where($attribute, $op, $value)->get($columns);
    }

    /**
     * Find with relations.
     *
     * @param $resourceId
     * @param array $with
     * @return mixed
     */
    public function findWith($resourceId, array $with = [])
    {
        return $this->model->with($with)->find($resourceId);
    }

    /**
     * Find whereIn.
     *
     * @param $field
     * @param array $values
     * @param array|string[] $columns
     * @return mixed
     */
    public function whereIn($field, array $values, array $columns = ['*'])
    {
        return $this->model->whereIn($field, $values)->get($columns);
    }

    /**
     * Create.
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * First or create.
     *
     * @param array $attributes
     * @param array $data
     * @return mixed
     */
    public function firstOrCreate(array $attributes, array $data = [])
    {
        return $this->model->firstOrCreate($attributes, $data);
    }

    /**
     * First or new returning instance.
     *
     * @param array $attributes
     * @param array $data
     * @return mixed
     */
    public function firstOrNew(array $attributes, array $data = [])
    {
        return $this->model->firstOrNew($attributes, $data);
    }

    /**
     * Update.
     *
     * @param array $data
     * @param $resourceId
     * @return false
     */
    public function update(array $data, $resourceId)
    {
        $model = $this->model->find($resourceId);
        $result = $model->fill($data)->save();

        return $result ? $model : false;
    }

    /**
     * Update or Create.
     *
     * @param array $attributes
     * @param array $values
     * @return mixed
     */
    public function updateOrCreate(array $attributes, array $values)
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    /**
     * Update many.
     *
     * @param array $attributes
     * @param string $column
     * @param string $value
     * @return mixed
     */
    public function updateMany(array $attributes, string $column, string $value)
    {
        return $this->model->where($column, $value)->update($attributes);
    }

    /**
     * Count.
     *
     * @param array $attributes
     * @return mixed
     */
    public function count(array $attributes = [])
    {
        return $this->model->where($attributes)->count();
    }

    /**
     * Truncate data in model.
     *
     * @return mixed
     */
    public function truncate()
    {
        return $this->model->truncate();
    }
}