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

namespace App\Traits;

/**
 * Trait SkipZooniverse
 */
trait SkipZooniverse
{
    /**
     * Used to skip reconcile process.
     *
     * @return bool
     */
    protected function skipReconcile($expeditionId)
    {
        if (in_array($expeditionId, config('zooniverse.skip_reconcile'), false)) {
            return true;
        }

        return false;
    }

    /**
     * Skip expedition for panoptes api.
     *
     * @return bool
     */
    protected function skipApi($expeditionId)
    {
        if (in_array($expeditionId, config('zooniverse.skip_api'), false)) {
            return true;
        }

        return false;
    }
}
