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

namespace App\Services\Export;

use App\Services\Model\ExportFormModelService;
use App\Models\ExportForm;
use App\Services\Model\RapidHeaderModelService;
use App\Services\Model\RapidRecordModelService;
use App\Services\Model\RapidVersionModelService;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use MongoDB\Driver\Cursor;

/**
 * Class RapidExportDbService
 *
 * @package App\Services
 */
class RapidExportDbService
{

    /**
     * @var \App\Services\Model\ExportFormModelService
     */
    private $exportFormModelService;

    /**
     * @var \App\Services\Model\RapidHeaderModelService
     */
    private $rapidHeaderModelService;

    /**
     * @var \App\Services\Model\RapidVersionModelService
     */
    private $rapidVersionModelService;

    /**
     * @var \App\Services\Model\RapidRecordModelService
     */
    private $rapidRecordModelService;

    /**
     * @var int
     */
    private $headerId;

    /**
     * RapidExportDbService constructor.
     *
     * @param \App\Services\Model\ExportFormModelService $exportFormModelService
     * @param \App\Services\Model\RapidHeaderModelService $rapidHeaderModelService
     * @param \App\Services\Model\RapidVersionModelService $rapidVersionModelService
     * @param \App\Services\Model\RapidRecordModelService $rapidRecordModelService
     */
    public function __construct(
        ExportFormModelService $exportFormModelService,
        RapidHeaderModelService $rapidHeaderModelService,
        RapidVersionModelService $rapidVersionModelService,
        RapidRecordModelService $rapidRecordModelService
    )
    {
        $this->exportFormModelService = $exportFormModelService;
        $this->rapidHeaderModelService = $rapidHeaderModelService;
        $this->rapidVersionModelService = $rapidVersionModelService;
        $this->rapidRecordModelService = $rapidRecordModelService;
    }

    /**
     * Find rapid form by id.
     *
     * @param int $id
     * @return \App\Models\ExportForm
     */
    public function findRapidFormById(int $id): ExportForm
    {
        return $this->exportFormModelService->findWith($id, ['user']);
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

        return $this->exportFormModelService->create($data);
    }

    /**
     * Get forms by destination.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRapidFormsSelect(): Collection
    {
        return $this->exportFormModelService->getFormsSelect();
    }

    /**
     * Get latest rapid header.
     *
     * @return mixed
     */
    public function getLatestHeader(): array
    {
        $header = $this->rapidHeaderModelService->getLatestHeader();
        $this->headerId = $header->id;

        return $header->data;
    }

    /**
     * Return last header id.
     *
     * @return int
     */
    public function getHeaderId(): int
    {
        return $this->headerId;
    }

    /**
     * Get cursor for looping rapid records.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    public function getCursorForRapidRecords(): LazyCollection
    {
        return $this->rapidRecordModelService->getCursor();
    }

    /**
     * Create version record in database.
     *
     * @param array $attributes
     */
    public function createVersionRecord(array $attributes)
    {
        $this->rapidVersionModelService->create($attributes);
    }
}