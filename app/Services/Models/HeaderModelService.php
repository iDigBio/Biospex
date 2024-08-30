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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Models;

use App\Models\Header;

readonly class HeaderModelService
{
    /**
     * HeaderModelService constructor.
     *
     * @param \App\Models\Header $model
     */
    public function __construct(private Header $model)
    {}

    /**
     * Find with relations.
     *
     * @param int $id
     * @param array $relations
     * @return \App\Models\Header|null
     */
    public function findWithRelations(int $id, array $relations = []): ?Header
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * Get all.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->all();
    }

    /**
     * Get first by column.
     *
     * @param string $column
     * @param string $value
     * @return \App\Models\Header|null
     */
    public function getFirst(string $column, string $value): ?Header
    {
        return $this->model->where($column, $value)->first();
    }

    /**
     * Create a new header.
     *
     * @param array $data
     * @return \App\Models\Header
     */
    public function create(array $data): Header
    {
        return $this->model->create($data);
    }
}