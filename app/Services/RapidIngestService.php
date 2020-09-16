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

use App\Repositories\Interfaces\RapidHeader;
use App\Repositories\Interfaces\RapidRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RapidIngestService extends RapidServiceBase
{
    /**
     * @var \App\Services\CsvService
     */
    private $csvService;

    /**
     * @var \App\Repositories\Interfaces\RapidRecord
     */
    private $rapidInterface;

    /**
     * @var \App\Repositories\Interfaces\RapidHeader
     */
    private $rapidHeaderInterface;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var array
     */
    private $header = [];

    /**
     * @var
     */
    private $rows;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $validationFields;

    /**
     * @var int
     */
    private $updatedRecordsCount = 0;

    /**
     * RapidIngestService constructor.
     *
     * @param \App\Services\CsvService $csvService
     * @param \App\Repositories\Interfaces\RapidRecord $rapidInterface
     * @param \App\Repositories\Interfaces\RapidHeader $rapidHeaderInterface
     */
    public function __construct(CsvService $csvService, RapidRecord $rapidInterface, RapidHeader $rapidHeaderInterface)
    {

        $this->csvService = $csvService;
        $this->rapidInterface = $rapidInterface;
        $this->rapidHeaderInterface = $rapidHeaderInterface;
        $this->validationFields = config('config.validation_fields');
    }

    /**
     * Load csv file.
     *
     * @param $file
     * @throws \League\Csv\Exception
     */
    public function loadCsvFile($file)
    {
        $this->csvService->readerCreateFromPath($file);
        $this->csvService->setDelimiter();
        $this->csvService->setEnclosure();
        $this->csvService->setHeaderOffset();
    }

    /**
     * Set the header.
     *
     * @return array
     */
    public function setHeader(): array
    {
        return $this->header = $this->csvService->getHeader();
    }

    /**
     * Store the header in mongo database.
     */
    public function storeHeader()
    {
        $this->rapidHeaderInterface->create(['header' => $this->header]);
    }

    /**
     * Update the header with new values if they are different.
     */
    public function updateHeader()
    {
        $protected = config('config.protected_fields');

        $rapidHeader = $this->rapidHeaderInterface->first();
        $diff = collect($this->header)->diff($rapidHeader->header)->reject(function ($field) use($protected) {
            return in_array($field, $protected);
        });

        $rapidHeader->header = collect($rapidHeader->header)->concat($diff)->toArray();
        $rapidHeader->save();
    }

    /**
     * Set rows.
     */
    public function setRows()
    {
        $this->rows = $this->csvService->getRecords($this->header);
    }

    /**
     * Process rows when importing for first time.
     */
    public function processImportRows()
    {
        foreach ($this->rows as $offset => $row) {
            $this->createRecord($row);
        }
    }

    /**
     * Create rapid record.
     *
     * @param $row
     */
    private function createRecord($row)
    {
        if ($this->validateRow($row)) {
            return;
        }

        $this->rapidInterface->create($row);
    }

    /**
     * Validate imports being created.
     *
     * Fields frrom config.
     * @param $row
     * @return mixed
     */
    public function validateRow($row)
    {
        $attributes = [];
        foreach ($this->validationFields as $field) {
            $attributes[$field] = $row[$field];
        }

        $count = $this->rapidInterface->validateRecord($attributes);

        if ($count) {
            $this->errors[] = $row;
        }

        return $count;
    }

    /**
     * Update record using selected fields only.
     * @param \Illuminate\Support\Collection $fields
     */
    public function processUpdateRows(Collection $fields)
    {

        foreach ($this->rows as $offset => $row) {
            $intersect = collect($row)->intersectByKeys($fields->flip())->toArray();

            $this->updateRecord($intersect, $row['_id']);
        }
    }

    /**
     * Update Rapid record.
     *
     * @param array $attributes
     * @param string $id
     */
    public function updateRecord(array $attributes, string $id)
    {
        $result =$this->rapidInterface->update($attributes, $id);

        if (! $result) {
            $this->errors[] = array_merge(['_id' => $id], $attributes);
        }

        $this->updatedRecordsCount++;
    }

    /**
     * Create csv file.
     *
     * @return string
     * @throws \League\Csv\CannotInsertRecord
     */
    public function createCsv() {
        $errors = collect($this->errors)->recursive();
        $header = $errors->first()->keys();

        $fileName = Str::random() . '.csv';
        $filePath = Storage::path(config('config.reports_dir') . '/' . $fileName);

        $this->csvService->writerCreateFromPath($filePath);
        $this->csvService->insertOne($header);
        $this->csvService->insertAll($errors->toArray());


        return route('admin.download.report', ['fileName' => $fileName]);
    }

    /**
     * Get errors
     *
     * @return bool
     */
    public function checkErrors()
    {
        return ! empty($this->errors);
    }

    /**
     * Return the count of updated records.
     *
     * @return int
     */
    public function getUpdatedRecordsCount()
    {
        return $this->updatedRecordsCount;
    }
}