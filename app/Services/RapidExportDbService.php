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

namespace App\Services;

use App\Repositories\Interfaces\ExportForm;
use App\Models\ExportForm as ExportFormModel;
use Illuminate\Support\Collection;

class RapidExportDbService
{

    /**
     * @var \App\Repositories\Interfaces\ExportForm
     */
    private $exportFormInterface;

    /**
     * RapidExportDbService constructor.
     *
     * @param \App\Repositories\Interfaces\ExportForm $exportFormInterface
     */
    public function __construct(
        ExportForm $exportFormInterface
    )
    {
        $this->exportFormInterface = $exportFormInterface;
    }

    /**
     * Find rapid form by id.
     *
     * @param int $id
     * @return \App\Models\ExportForm
     */
    public function findRapidFormById(int $id): ExportFormModel
    {
        return $this->exportFormInterface->findWith($id, ['user']);
    }

    /**
     * Save rapid export form.
     *
     * @param array $fields
     * @param int $userId
     * @return \App\Models\ExportForm
     */
    public function saveRapidForm(array $fields, int $userId): ExportFormModel
    {
        $data = [
            'user_id' => $userId,
            'destination' => $fields['exportDestination'],
            'data'        => $fields,
        ];

        return $this->exportFormInterface->create($data);
    }

    /**
     * Get forms by destination.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRapidFormsSelect(): Collection
    {
        return $this->exportFormInterface->getFormsSelect();
    }
}