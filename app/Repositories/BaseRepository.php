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

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MongoDB\Laravel\Eloquent\Model as MongoModel;

/**
 * Class BaseRepository
 *
 * @mixin \Eloquent
 */
class BaseRepository
{
    protected MongoModel|EloquentModel $model;

    /**
     * Override __get().
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->{$key};
    }

    /**
     * Get all.
     *
     * @param  array|string[]  $columns
     * @return mixed
     */
    public function all(array $columns = ['*'])
    {
        return $this->model->get($columns);
    }

    /**
     * Get all with relations.
     *
     * @param  array|string[]  $columns
     * @return mixed
     */
    public function allWith(array $with = [], array $columns = ['*'])
    {
        return $this->model->with($with)->get($columns);
    }

    /**
     * Find by id.
     *
     * @param  array|string[]  $columns
     * @return mixed
     */
    public function find($resourceId, array $columns = ['*'])
    {
        return $this->model->find($resourceId, $columns);
    }

    /**
     * Find by field.
     *
     * @param  array|string[]  $columns
     * @return mixed
     */
    public function findBy($attribute, $value, array $columns = ['*'])
    {
        return $this->model->where($attribute, '=', $value)->first($columns);
    }

    /**
     * Find by field with relations.
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function findByWith($attribute, $value, array $with = [], array $columns = ['*']): \Illuminate\Database\Eloquent\Collection|array
    {
        return $this->model->with($with)->where($attribute, '=', $value)->get($columns);
    }

    /**
     * Get all by attribute value.
     *
     * @param  array|string[]  $columns
     * @return mixed
     */
    public function getBy($attribute, $value, string $op = '=', array $columns = ['*'])
    {
        return $this->model->where($attribute, $op, $value)->get($columns);
    }

    /**
     * Find with relations.
     *
     * @return mixed
     */
    public function findWith($resourceId, array $with = [])
    {
        return $this->model->with($with)->find($resourceId);
    }

    /**
     * Find whereIn.
     *
     * @param  array|string[]  $columns
     * @return mixed
     */
    public function whereIn($field, array $values, array $columns = ['*'])
    {
        return $this->model->whereIn($field, $values)->get($columns);
    }

    /**
     * Create.
     *
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * First or create.
     *
     * @return mixed
     */
    public function firstOrCreate(array $attributes, array $data = [])
    {
        return $this->model->firstOrCreate($attributes, $data);
    }

    /**
     * First or new returning instance.
     *
     * @return mixed
     */
    public function firstOrNew(array $attributes, array $data = [])
    {
        return $this->model->firstOrNew($attributes, $data);
    }

    /**
     * Update.
     *
     * @return false
     */
    public function update(array $data, $resourceId)
    {
        $model = $this->model->find($resourceId);
        $result = $model->fill($data)->save();

        return $result ? $model : false;
    }

    /**
     * Update record using attribute and value.
     *
     * @return false
     */
    public function updateBy(array $data, string $attribute, string $value)
    {
        $model = $this->findBy($attribute, $value);
        $result = $model->fill($data)->save();

        return $result ? $model : false;
    }

    /**
     * Update or Create.
     *
     * @return mixed
     */
    public function updateOrCreate(array $attributes, array $values)
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    /**
     * Update many.
     *
     * @return mixed
     */
    public function updateMany(array $attributes, string $column, string $value)
    {
        return $this->model->where($column, $value)->update($attributes);
    }

    /**
     * Count.
     *
     * @return mixed
     */
    public function count(array $attributes = [])
    {
        return $this->model->where($attributes)->count();
    }

    /**
     * Find by id with relation count.
     */
    public function findByIdWithRelationCount(int $id, string $relation): mixed
    {
        return $this->model->withCount($relation)->find($id);
    }

    /**
     * Truncate database table.
     *
     * @throws \Exception
     */
    public function truncate(): void
    {
        if (\App::isProduction()) {
            throw new \Exception('Cannot truncate database table in production.');
        }

        $this->model->truncate();
    }
}
