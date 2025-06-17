<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

/**
 * Class BaseEloquentModel
 *
 * @mixin Eloquent
 */
class BaseEloquentModel extends Model
{
    use LadaCacheTrait;

    /**
     * {@inheritDoc}
     */
    protected $connection = 'mysql';

    /**
     * {@inheritDoc}
     */
    protected $primaryKey = 'id';

    /**
     * {@inheritDoc}
     */
    public function __construct(array $attributes = [])
    {
        if (\App::environment('testing')) {
            $this->connection = 'sqlite';
        }

        parent::__construct($attributes);
    }
}
