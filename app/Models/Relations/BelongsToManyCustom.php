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

/**
 * Not using package until https://github.com/fico7489/laravel-pivot/issues/55 is fixed
 */

namespace App\Models\Relations;

use App\Models\Traits\FiresPivotEventsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BelongsToManyCustom extends BelongsToMany
{
    use FiresPivotEventsTrait;
}