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

namespace App\Services;

use App\Repositories\Interfaces\ExportForm;

class GeoLocateExportService
{
    /**
     * @var \App\Repositories\Interfaces\ExportForm
     */
    private $exportFormInterface;

    /**
     * GeoLocateExportService constructor.
     *
     * @param \App\Repositories\Interfaces\ExportForm $exportFormInterface
     */
    public function __construct(ExportForm $exportFormInterface)
    {

        $this->exportFormInterface = $exportFormInterface;
    }

    /**
     * @param int|null $frm
     */
    public function showForm(int $frm = null)
    {

    }
}