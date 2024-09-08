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

use App\Models\EventUser;

/**
 * Class EventUserModelService
 */
readonly class EventUserModelService
{
    /**
     * EventUserModelService constructor.
     */
    public function __construct(private EventUser $model) {}

    /**
     * Update or Create.
     */
    public function updateOrCreate(array $attributes, array $values): mixed
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    /**
     * Find user by nfn_user name.
     */
    public function findByNfnUser(string $userName, array $columns = ['*']): ?EventUser
    {
        return $this->model->where('nfn_user', $userName)->first($columns);
    }
}
