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


use Illuminate\Support\Collection;

interface RepositoryInterface
{
    public function all(array $columns = ['*']);

    public function allWith(array $with = [], array $columns = ['*']);

    public function find($resourceId, array $columns = ['*']);

    public function findBy($field, $value, array $columns = ['*']);

    public function findWith($resourceId, array $with = []);

    public function whereIn($field, array $values, array $columns = ['*']);

    public function create(array $data);

    public function firstOrCreate(array $attributes, array $data = []);

    public function update(array $data, $resourceId);

    public function updateOrCreate(array $attributes, array $values);

    public function updateMany(array $attributes, string $column, string $value);

    public function delete($model);

    public function count(array $attributes = []);

    public function truncate();
}