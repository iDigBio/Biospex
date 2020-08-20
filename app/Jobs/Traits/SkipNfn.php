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

namespace App\Jobs\Traits;

/**
 * Trait SkipNfn
 *
 * @package App\Jobs\Traits
 */
trait SkipNfn
{
    /**
     * Used to skip reconcile process.
     *
     * @param $expeditionId
     * @return bool
     */
    protected function skipReconcile($expeditionId)
    {
        $nfnSkipReconcile = explode(',', config('config.nfnSkipReconcile'));

        if (in_array($expeditionId, $nfnSkipReconcile, false)) {
            return true;
        }

        return false;
    }

    /**
     * Skip expedition for panoptes api.
     *
     * @param $expeditionId
     * @return bool
     */
    protected function skipApi($expeditionId)
    {
        $nfnSkipApi = explode(',', config('config.nfnSkipApi'));

        if (in_array($expeditionId, $nfnSkipApi, false)) {
            return true;
        }

        return false;
    }
}