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
use App\Repositories\Interfaces\RapidHeader;
use App\Models\ExportForm as ExportFormModel;
use Illuminate\Support\Collection;

class RapidExportDbService
{
    /**
     * @var \App\Repositories\Interfaces\RapidHeader
     */
    private $rapidHeaderInterface;

    /**
     * @var \App\Repositories\Interfaces\ExportForm
     */
    private $exportFormInterface;

    /**
     * RapidExportDbService constructor.
     *
     * @param \App\Repositories\Interfaces\RapidHeader $rapidHeaderInterface
     * @param \App\Repositories\Interfaces\ExportForm $exportFormInterface
     */
    public function __construct(
        RapidHeader $rapidHeaderInterface,
        ExportForm $exportFormInterface
    )
    {
        $this->rapidHeaderInterface = $rapidHeaderInterface;
        $this->exportFormInterface = $exportFormInterface;
    }

    /**
     * Get RAPID header.
     *
     * @return \App\Models\RapidHeader
     */
    public function getFirstRapidHeader(): \App\Models\RapidHeader
    {
        return $rapidHeader = $this->rapidHeaderInterface->first();
    }

    /**
     * Find rapid form by id.
     *
     * @param int $id
     * @return \App\Models\ExportForm
     */
    public function findRapidFormById(int $id): ExportFormModel
    {
        return $this->exportFormInterface->find($id);
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
     * @param string $destination
     * @return \Illuminate\Support\Collection
     */
    public function getRapidFormsByDestination(string $destination): Collection
    {
        return $this->exportFormInterface->getFormsByDestination($destination);
    }
}