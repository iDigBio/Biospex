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

use App\Models\ExportForm;
use Illuminate\Support\Collection;

/**
 * Class ExportFormModelService
 *
 * @package App\Services\Model
 */
class ExportFormModelService extends BaseModelService
{
    /**
     * ExportFormModelService constructor.
     *
     * @param \App\Models\ExportForm $exportForm
     */
    public function __construct(ExportForm $exportForm)
    {
        $this->model = $exportForm;
    }

    /**
     * Return form select for export.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFormsSelect(): Collection
    {
        return $this->model->with('user')
            ->orderBy('created_at')
            ->get(['id', 'user_id', 'destination', 'created_at']);
    }
}