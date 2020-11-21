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

use App\Services\Model\ExportFormService;
use App\Models\ExportForm;
use Illuminate\Support\Collection;

/**
 * Class RapidExportDbService
 *
 * @package App\Services
 */
class RapidExportDbService
{

    /**
     * @var \App\Services\Model\ExportFormService
     */
    private $exportFormService;

    /**
     * RapidExportDbService constructor.
     *
     * @param \App\Services\Model\ExportFormService $exportFormService
     */
    public function __construct(
        ExportFormService $exportFormService
    )
    {
        $this->exportFormService = $exportFormService;
    }

    /**
     * Find rapid form by id.
     *
     * @param int $id
     * @return \App\Models\ExportForm
     */
    public function findRapidFormById(int $id): ExportForm
    {
        return $this->exportFormService->findWith($id, ['user']);
    }

    /**
     * Save rapid export form.
     *
     * @param array $fields
     * @param int $userId
     * @return \App\Models\ExportForm
     */
    public function saveRapidForm(array $fields, int $userId): ExportForm
    {
        $data = [
            'user_id' => $userId,
            'destination' => $fields['exportDestination'],
            'data'        => $fields,
        ];

        return $this->exportFormService->create($data);
    }

    /**
     * Get forms by destination.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRapidFormsSelect(): Collection
    {
        return $this->exportFormService->getFormsSelect();
    }
}